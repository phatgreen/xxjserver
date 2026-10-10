-- =====================================================
-- MANG MACH HUYET — schema cho Huyết Mạch rework (2026-10-09)
-- MySQL 5.5 compatible: khong DEFAULT CURRENT_TIMESTAMP, sid VARCHAR(64)
-- Chay: mysql -u root -p <dbname> < sql/sql_huyetmach_vessel.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS player_huyetmach_vessel (
  sid        VARCHAR(64) NOT NULL,
  vessel_id  TINYINT(4)  NOT NULL,
  level      INT(11)     DEFAULT 0,
  updated_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, vessel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS huyetmach_boss (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL DEFAULT '',
  cfg_key    VARCHAR(64) NOT NULL,
  name       VARCHAR(100) NOT NULL,
  element    VARCHAR(20) NOT NULL,
  level      INT(11) DEFAULT 1,
  hp         INT(11) DEFAULT 0,
  maxhp      INT(11) DEFAULT 0,
  atk        INT(11) DEFAULT 0,
  def        INT(11) DEFAULT 0,
  crit       INT(11) DEFAULT 0,
  spawned_at DATETIME DEFAULT NULL,
  killed_at  DATETIME DEFAULT NULL,
  status     VARCHAR(20) DEFAULT 'active',
  PRIMARY KEY (id),
  KEY idx_sid (sid),
  KEY idx_key (cfg_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_huyetmach_upgrade_log (
  id           INT(11) NOT NULL AUTO_INCREMENT,
  sid          VARCHAR(64) NOT NULL,
  from_quality INT(11) NOT NULL,
  to_quality   INT(11) NOT NULL,
  success      TINYINT(1) DEFAULT 0,
  created_at   DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
