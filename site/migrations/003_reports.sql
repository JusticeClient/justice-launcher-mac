-- ============================================================
--  JUSTICE LAUNCHER — User Reports Table
--  Run this migration against `justguye_mishka`
-- ============================================================

CREATE TABLE IF NOT EXISTS reports (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL COMMENT 'Who submitted the report',
  category      ENUM('player','bug','general') NOT NULL DEFAULT 'general',
  subject       VARCHAR(200) NOT NULL,
  description   TEXT NOT NULL,
  reported_user VARCHAR(100) DEFAULT NULL COMMENT 'Username of reported player (if category=player)',
  status        ENUM('open','in_progress','resolved','dismissed') NOT NULL DEFAULT 'open',
  staff_note    TEXT DEFAULT NULL,
  handled_by    INT UNSIGNED DEFAULT NULL COMMENT 'Staff user ID who handled it',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT NULL,
  INDEX idx_reports_user (user_id),
  INDEX idx_reports_status (status),
  INDEX idx_reports_category (category),
  INDEX idx_reports_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
