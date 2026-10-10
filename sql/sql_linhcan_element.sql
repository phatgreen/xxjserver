-- =====================================================
-- VONG NGU HANH — schema cho Linh Can rework (2026-10-10)
-- MySQL 5.5 compatible, sid VARCHAR(64). Chay:
--   mysql -u root -p <dbname> < sql/sql_linhcan_element.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS player_linhcan_node (
  sid        VARCHAR(64) NOT NULL,
  element    VARCHAR(10) NOT NULL,
  level      INT(11)     DEFAULT 0,
  stage      TINYINT(4)  DEFAULT 0,
  updated_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, element)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS linhcan_boss_personal (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL DEFAULT '',
  element    VARCHAR(10) NOT NULL,
  to_stage   TINYINT(4)  NOT NULL,
  name       VARCHAR(100) NOT NULL,
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
  KEY idx_elem (element, to_stage)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_linhcan_break_log (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL,
  element    VARCHAR(10) NOT NULL,
  from_stage TINYINT(4) NOT NULL,
  to_stage   TINYINT(4) NOT NULL,
  created_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Bang cooldown skill. DA TON TAI trong code cu (linhcan_skill.php)
-- nhung CHUA tung duoc tao trong DB (do information_schema 10/10).
CREATE TABLE IF NOT EXISTS player_linhcan_skill (
  sid         VARCHAR(64) NOT NULL,
  skill_id    VARCHAR(40) NOT NULL,
  last_used   DATETIME DEFAULT NULL,
  PRIMARY KEY (sid, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
