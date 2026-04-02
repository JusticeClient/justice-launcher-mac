<?php
/**
 * JUSTICE LAUNCHER — Rewards API
 * Handles daily login claims (flat bonus + streak) and playtime tracking.
 *
 * Endpoints:
 *   GET  ?action=daily-status    → streak info & whether today is claimed
 *   POST ?action=daily-claim     → claim today's daily bonus
 *   POST ?action=playtime-start  → start a play session
 *   POST ?action=playtime-heartbeat → heartbeat (every 5 min) + auto-award points
 *   POST ?action=playtime-stop   → end a play session
 *   GET  ?action=playtime-stats  → total playtime & points earned from playtime
 */
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();
$user   = requireAuth();
$uid    = $user['id'];

// ── helpers ────────────────────────────────────────────────────────

function awardPoints(PDO $db, int $uid, int $amount, string $reason): void {
    $db->prepare("UPDATE users SET points = points + ? WHERE id = ?")
       ->execute([$amount, $uid]);
    $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?, ?, ?)")
       ->execute([$uid, $amount, $reason]);
}

// ═══════════════════════════════════════════════════════════════════
//  DAILY LOGIN — STATUS
// ═══════════════════════════════════════════════════════════════════
if ($action === 'daily-status' && $method === 'GET') {
    try {
        // Auto-create tables if they don't exist (first-run safety)
        $db->exec("CREATE TABLE IF NOT EXISTS daily_claims (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            points_earned INT NOT NULL DEFAULT 0,
            streak_day INT UNSIGNED NOT NULL DEFAULT 1,
            claimed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_daily_user (user_id),
            INDEX idx_daily_claimed (user_id, claimed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Check if already claimed today (all date math in MySQL to avoid timezone mismatch)
        $stmt = $db->prepare(
            "SELECT id, streak_day, points_earned, claimed_at FROM daily_claims
             WHERE user_id = ? AND DATE(claimed_at) = CURDATE()
             ORDER BY claimed_at DESC LIMIT 1"
        );
        $stmt->execute([$uid]);
        $todayClaim = $stmt->fetch();

        // Get last claim (for streak calculation)
        $stmt2 = $db->prepare(
            "SELECT streak_day, DATE(claimed_at) as claim_date,
                    DATE(claimed_at) = CURDATE() as is_today,
                    DATE(claimed_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) as is_yesterday
             FROM daily_claims
             WHERE user_id = ? ORDER BY claimed_at DESC LIMIT 1"
        );
        $stmt2->execute([$uid]);
        $lastClaim = $stmt2->fetch();

        $currentStreak = 0;
        if ($lastClaim) {
            if ($lastClaim['is_today']) {
                $currentStreak = (int)$lastClaim['streak_day'];
            } elseif ($lastClaim['is_yesterday']) {
                $currentStreak = (int)$lastClaim['streak_day'];
            } else {
                $currentStreak = 0; // streak broken
            }
        }

        // Calculate next reward preview
        $nextStreakDay = $todayClaim ? $currentStreak : min($currentStreak + 1, 7);
        $nextReward = calculateDailyReward($nextStreakDay);

        respond([
            'claimed_today'  => (bool)$todayClaim,
            'current_streak' => $currentStreak,
            'today_reward'   => $todayClaim ? (int)$todayClaim['points_earned'] : $nextReward,
            'next_reward'    => $nextReward,
            'streak_day'     => $todayClaim ? (int)$todayClaim['streak_day'] : $nextStreakDay,
            'streak_rewards' => getStreakRewardsTable(),
        ]);
    } catch (Exception $e) {
        respond([
            'claimed_today' => false, 'current_streak' => 0,
            'today_reward' => 2, 'next_reward' => 2,
            'streak_day' => 1, 'streak_rewards' => getStreakRewardsTable(),
        ]);
    }
}

// ═══════════════════════════════════════════════════════════════════
//  DAILY LOGIN — CLAIM
// ═══════════════════════════════════════════════════════════════════
if ($action === 'daily-claim' && $method === 'POST') {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS daily_claims (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            points_earned INT NOT NULL DEFAULT 0,
            streak_day INT UNSIGNED NOT NULL DEFAULT 1,
            claimed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_daily_user (user_id),
            INDEX idx_daily_claimed (user_id, claimed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Check if already claimed today (all date math in MySQL to avoid timezone mismatch)
        $stmt = $db->prepare(
            "SELECT id FROM daily_claims WHERE user_id = ? AND DATE(claimed_at) = CURDATE()"
        );
        $stmt->execute([$uid]);
        if ($stmt->fetch()) error('Already claimed today! Come back tomorrow.');

        // Calculate streak
        $stmt2 = $db->prepare(
            "SELECT streak_day,
                    DATE(claimed_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) as is_yesterday
             FROM daily_claims
             WHERE user_id = ? ORDER BY claimed_at DESC LIMIT 1"
        );
        $stmt2->execute([$uid]);
        $lastClaim = $stmt2->fetch();

        $streakDay = 1;
        if ($lastClaim && $lastClaim['is_yesterday']) {
            $streakDay = min((int)$lastClaim['streak_day'] + 1, 7);
        }

        $reward = calculateDailyReward($streakDay);

        $db->beginTransaction();
        try {
            // Insert claim record
            $db->prepare(
                "INSERT INTO daily_claims (user_id, points_earned, streak_day, claimed_at)
                 VALUES (?, ?, ?, NOW())"
            )->execute([$uid, $reward, $streakDay]);

            // Award points
            awardPoints($db, $uid, $reward, "Daily login bonus (Day $streakDay streak)");

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Fetch updated balance
        $stmt = $db->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $newBalance = (int)$stmt->fetchColumn();

        respond([
            'success'        => true,
            'points_earned'  => $reward,
            'streak_day'     => $streakDay,
            'new_balance'    => $newBalance,
            'message'        => "Day $streakDay streak! +$reward points",
        ]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Already claimed') !== false) {
            error($e->getMessage());
        }
        error('Failed to claim daily reward: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════
//  PLAYTIME — START SESSION
// ═══════════════════════════════════════════════════════════════════
if ($action === 'playtime-start' && $method === 'POST') {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS playtime_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            session_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_heartbeat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            total_minutes INT UNSIGNED NOT NULL DEFAULT 0,
            points_awarded INT UNSIGNED NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            INDEX idx_pt_user (user_id),
            INDEX idx_pt_active (user_id, active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->prepare(
            "UPDATE playtime_sessions SET active = 0
             WHERE user_id = ? AND active = 1"
        )->execute([$uid]);

        // Create new session
        $db->prepare(
            "INSERT INTO playtime_sessions (user_id, session_start, last_heartbeat, active)
             VALUES (?, NOW(), NOW(), 1)"
        )->execute([$uid]);

        $sessionId = $db->lastInsertId();

        respond([
            'success'    => true,
            'session_id' => (int)$sessionId,
        ]);
    } catch (Exception $e) {
        error('Failed to start playtime session: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════
//  PLAYTIME — HEARTBEAT (every 5 minutes from launcher)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'playtime-heartbeat' && $method === 'POST') {
    try {
        $data = body();
        $sessionId = (int)($data['session_id'] ?? 0);
        if (!$sessionId) error('Missing session_id');

        $stmt = $db->prepare(
            "SELECT *, TIMESTAMPDIFF(MINUTE, session_start, NOW()) AS elapsed_minutes FROM playtime_sessions WHERE id = ? AND user_id = ? AND active = 1"
        );
        $stmt->execute([$sessionId, $uid]);
        $session = $stmt->fetch();
        if (!$session) error('No active session found');

        $totalMinutes = max(0, (int)$session['elapsed_minutes']);
        $prevMinutes = (int)$session['total_minutes'];
        if ($totalMinutes - $prevMinutes > 15) $totalMinutes = $prevMinutes + 10;

        $rewardableSlots = (int)floor($totalMinutes / 30);
        $alreadyAwarded = (int)$session['points_awarded'];
        $newSlots = max(0, $rewardableSlots - $alreadyAwarded);
        if ($newSlots > 1) $newSlots = 1;
        $newPoints = $newSlots;

        $db->beginTransaction();
        try {
            // Update session
            $db->prepare(
                "UPDATE playtime_sessions
                 SET last_heartbeat = NOW(), total_minutes = ?, points_awarded = ?
                 WHERE id = ?"
            )->execute([$totalMinutes, $alreadyAwarded + $newPoints, $sessionId]);

            // Award any new points
            if ($newPoints > 0) {
                awardPoints($db, $uid, $newPoints, "Playtime reward ({$totalMinutes} min played)");
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Fetch updated balance
        $stmt = $db->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $newBalance = (int)$stmt->fetchColumn();

        respond([
            'success'          => true,
            'total_minutes'    => $totalMinutes,
            'points_this_session' => $alreadyAwarded + $newPoints,
            'points_just_earned'  => $newPoints,
            'new_balance'      => $newBalance,
            'next_reward_at'   => ($rewardableSlots + 1) * 30, // minutes until next point
        ]);
    } catch (Exception $e) {
        error('Heartbeat failed: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════
//  PLAYTIME — STOP SESSION
// ═══════════════════════════════════════════════════════════════════
if ($action === 'playtime-stop' && $method === 'POST') {
    try {
        $data = body();
        $sessionId = (int)($data['session_id'] ?? 0);

        if ($sessionId) {
            // Stop specific session
            $db->prepare(
                "UPDATE playtime_sessions SET active = 0, last_heartbeat = NOW()
                 WHERE id = ? AND user_id = ? AND active = 1"
            )->execute([$sessionId, $uid]);
        } else {
            // Stop all active sessions for this user
            $db->prepare(
                "UPDATE playtime_sessions SET active = 0, last_heartbeat = NOW()
                 WHERE user_id = ? AND active = 1"
            )->execute([$uid]);
        }

        respond(['success' => true]);
    } catch (Exception $e) {
        error('Failed to stop session: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════
//  PLAYTIME — STATS
// ═══════════════════════════════════════════════════════════════════
if ($action === 'playtime-stats' && $method === 'GET') {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS playtime_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            session_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_heartbeat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            total_minutes INT UNSIGNED NOT NULL DEFAULT 0,
            points_awarded INT UNSIGNED NOT NULL DEFAULT 0,
            active TINYINT(1) NOT NULL DEFAULT 1,
            INDEX idx_pt_user (user_id),
            INDEX idx_pt_active (user_id, active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Total playtime & points
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(total_minutes), 0) as total_minutes,
                    COALESCE(SUM(points_awarded), 0) as total_points,
                    COUNT(*) as total_sessions
             FROM playtime_sessions WHERE user_id = ?"
        );
        $stmt->execute([$uid]);
        $totals = $stmt->fetch();

        // Today's playtime
        $stmt2 = $db->prepare(
            "SELECT COALESCE(SUM(total_minutes), 0) as today_minutes,
                    COALESCE(SUM(points_awarded), 0) as today_points
             FROM playtime_sessions
             WHERE user_id = ? AND DATE(session_start) = CURDATE()"
        );
        $stmt2->execute([$uid]);
        $today = $stmt2->fetch();

        // Active session?
        $stmt3 = $db->prepare(
            "SELECT id, session_start, total_minutes, points_awarded
             FROM playtime_sessions WHERE user_id = ? AND active = 1 LIMIT 1"
        );
        $stmt3->execute([$uid]);
        $activeSession = $stmt3->fetch();

        $hours = (int)floor($totals['total_minutes'] / 60);
        $mins  = $totals['total_minutes'] % 60;

        respond([
            'total_minutes'  => (int)$totals['total_minutes'],
            'total_hours'    => $hours . 'h ' . $mins . 'm',
            'total_points'   => (int)$totals['total_points'],
            'total_sessions' => (int)$totals['total_sessions'],
            'today_minutes'  => (int)$today['today_minutes'],
            'today_points'   => (int)$today['today_points'],
            'active_session' => $activeSession ? [
                'id'             => (int)$activeSession['id'],
                'started'        => $activeSession['session_start'],
                'minutes'        => (int)$activeSession['total_minutes'],
                'points_earned'  => (int)$activeSession['points_awarded'],
            ] : null,
        ]);
    } catch (Exception $e) {
        respond([
            'total_minutes' => 0, 'total_hours' => '0h 0m',
            'total_points' => 0, 'total_sessions' => 0,
            'today_minutes' => 0, 'today_points' => 0,
            'active_session' => null,
        ]);
    }
}

if ($action === 'fix-playtime-points' && $method === 'POST') {
    requireAdmin();

    $db->beginTransaction();
    try {
        $rows = $db->query("SELECT id, user_id, amount FROM points_log WHERE reason LIKE 'Playtime reward%' AND amount > 1")->fetchAll();
        $fixed = 0;
        foreach ($rows as $row) {
            $excess = (int)$row['amount'] - 1;
            $db->prepare("UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?")->execute([$excess, $row['user_id']]);
            $db->prepare("UPDATE points_log SET amount = 1 WHERE id = ?")->execute([$row['id']]);
            $fixed++;
        }

        $bogus = $db->query("SELECT id, user_id, amount FROM points_log WHERE reason LIKE 'Playtime reward (300 min%')")->fetchAll();
        foreach ($bogus as $row) {
            $db->prepare("UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?")->execute([$row['amount'], $row['user_id']]);
            $db->prepare("DELETE FROM points_log WHERE id = ?")->execute([$row['id']]);
        }

        $db->prepare("UPDATE playtime_sessions SET active = 0 WHERE active = 1")->execute();

        $db->commit();
        respond(['ok' => true, 'fixed_entries' => $fixed, 'removed_bogus' => count($bogus)]);
    } catch (Exception $e) {
        $db->rollBack();
        error('Fix failed: ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════════════════════════════

/**
 * Daily reward = base 2 pts + streak bonus
 * Day 1: 2pts, Day 2: 3pts, Day 3: 4pts, Day 4: 5pts, Day 5: 6pts, Day 6: 7pts, Day 7: 10pts
 */
function calculateDailyReward(int $streakDay): int {
    $rewards = [1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 10];
    return $rewards[min($streakDay, 7)] ?? 2;
}

function getStreakRewardsTable(): array {
    return [
        ['day' => 1, 'points' => 2,  'label' => 'Day 1'],
        ['day' => 2, 'points' => 3,  'label' => 'Day 2'],
        ['day' => 3, 'points' => 4,  'label' => 'Day 3'],
        ['day' => 4, 'points' => 5,  'label' => 'Day 4'],
        ['day' => 5, 'points' => 6,  'label' => 'Day 5'],
        ['day' => 6, 'points' => 7,  'label' => 'Day 6'],
        ['day' => 7, 'points' => 10, 'label' => 'Day 7 🔥'],
    ];
}

error('Unknown action', 404);
