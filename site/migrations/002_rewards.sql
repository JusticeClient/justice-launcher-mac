-- ============================================================
--  JUSTICE LAUNCHER — Daily Login & Playtime Rewards Tables
--  Run this migration against `justguye_mishka`
-- ============================================================

-- Daily login claims (flat bonus + streak multiplier)
CREATE TABLE IF NOT EXISTS daily_claims (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  points_earned INT NOT NULL DEFAULT 0,
  streak_day  INT UNSIGNED NOT NULL DEFAULT 1,
  claimed_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_daily_user (user_id),
  INDEX idx_daily_claimed (user_id, claimed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Playtime tracking sessions
CREATE TABLE IF NOT EXISTS playtime_sessions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  session_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_heartbeat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_minutes INT UNSIGNED NOT NULL DEFAULT 0,
  points_awarded INT UNSIGNED NOT NULL DEFAULT 0,
  active        TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_pt_user (user_id),
  INDEX idx_pt_active (user_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
