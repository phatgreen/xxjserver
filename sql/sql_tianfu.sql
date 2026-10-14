-- =====================================================
-- SQL Script: Thien Phu — Phu Cac suu tam
-- MySQL 5.5 compatible — NO DEFAULT CURRENT_TIMESTAMP
-- 4 cot game1 do `sql/tianfu_columns.php` them (guard information_schema)
-- =====================================================

CREATE TABLE IF NOT EXISTS player_tianfu (
  sid         VARCHAR(64) NOT NULL,
  tianfu_id   TINYINT(4)  NOT NULL,
  quality     TINYINT(4)  NOT NULL DEFAULT 1,
  lv          INT(11)     NOT NULL DEFAULT 0,
  slot        TINYINT(4)  NOT NULL DEFAULT 0,
  obtained_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, tianfu_id),
  KEY idx_sid_slot (sid, slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_tianfu_log (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL,
  kind       VARCHAR(20) NOT NULL,
  tianfu_id  TINYINT(4)  NOT NULL,
  from_q     TINYINT(4)  DEFAULT 0,
  to_q       TINYINT(4)  DEFAULT 0,
  detail     VARCHAR(200) DEFAULT NULL,
  created_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
