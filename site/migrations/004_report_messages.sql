-- ============================================================
--  JUSTICE LAUNCHER — Report Messages (live chat) Table
--  Run this migration against `justguye_mishka`
-- ============================================================

CREATE TABLE IF NOT EXISTS report_messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id   INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  is_staff    TINYINT(1) NOT NULL DEFAULT 0,
  message     TEXT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rm_report (report_id),
  INDEX idx_rm_created (report_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
