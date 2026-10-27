<?php
require_once __DIR__ . '/pdo.php';

$tables = array(
    "CREATE TABLE IF NOT EXISTS `player_toayeuthap` (
        `sid` varchar(64) NOT NULL,
        `floor` int(11) NOT NULL DEFAULT 1,
        `best_floor` int(11) NOT NULL DEFAULT 0,
        `wins` int(11) NOT NULL DEFAULT 0,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`sid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `player_sauthu` (
        `sid` varchar(64) NOT NULL,
        `current_map` int(11) NOT NULL DEFAULT 1,
        `best_map` int(11) NOT NULL DEFAULT 0,
        `total_fights` int(11) NOT NULL DEFAULT 0,
        `total_captures` int(11) NOT NULL DEFAULT 0,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`sid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "ALTER TABLE `playerchongwu` ADD COLUMN `pet_config_id` int(11) NOT NULL DEFAULT 0 AFTER `cwpz`",

    "CREATE TABLE IF NOT EXISTS `arena_player` (
        `sid` varchar(64) NOT NULL,
        `score` int(11) NOT NULL DEFAULT 1000,
        `wins` int(11) NOT NULL DEFAULT 0,
        `losses` int(11) NOT NULL DEFAULT 0,
        `today_fights` int(11) NOT NULL DEFAULT 0,
        `last_fight_date` date DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`sid`),
        KEY `score` (`score`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `arena_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `attacker_sid` varchar(64) NOT NULL,
        `defender_sid` varchar(64) NOT NULL,
        `winner_sid` varchar(64) NOT NULL,
        `score_change` int(11) NOT NULL DEFAULT 0,
        `exp_reward` int(11) NOT NULL DEFAULT 0,
        `wg_reward` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `attacker_sid` (`attacker_sid`),
        KEY `created_at` (`created_at`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `arena_daily_reward` (
        `reward_date` date NOT NULL,
        `sid` varchar(64) NOT NULL,
        `rank_no` int(11) NOT NULL DEFAULT 0,
        `score` int(11) NOT NULL DEFAULT 0,
        `yxb_reward` int(11) NOT NULL DEFAULT 0,
        `czb_reward` int(11) NOT NULL DEFAULT 0,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`reward_date`,`sid`),
        KEY `rank_no` (`rank_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `mine_player` (
        `sid` varchar(64) NOT NULL,
        `today_digs` int(11) NOT NULL DEFAULT 0,
        `last_dig_date` date DEFAULT NULL,
        `total_digs` int(11) NOT NULL DEFAULT 0,
        `total_yxb` int(11) NOT NULL DEFAULT 0,
        `total_czb` int(11) NOT NULL DEFAULT 0,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`sid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `mine_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sid` varchar(64) NOT NULL,
        `yxb_reward` int(11) NOT NULL DEFAULT 0,
        `czb_reward` int(11) NOT NULL DEFAULT 0,
        `note` varchar(120) DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `sid` (`sid`),
        KEY `created_at` (`created_at`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
);

$ok = 0;
foreach ($tables as $sql) {
    $dblj->exec($sql);
    $ok++;
}

if (PHP_SAPI == 'cli') {
    echo "Database setup complete. Tables checked: ".$ok.PHP_EOL;
} else {
    echo '<meta charset="utf-8">';
    echo 'Database setup complete. Tables checked: '.$ok;
}
?>
