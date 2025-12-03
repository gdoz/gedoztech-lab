CREATE TABLE visit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_path VARCHAR(255) NOT NULL,
  lang CHAR(2) NOT NULL DEFAULT 'en',
  theme VARCHAR(16) NOT NULL DEFAULT 'auto',
  referrer VARCHAR(512) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_page_path (page_path),
  INDEX idx_created_at (created_at),
  INDEX idx_lang (lang),
  INDEX idx_theme (theme)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
