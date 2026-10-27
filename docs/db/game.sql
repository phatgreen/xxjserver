/*
Navicat MySQL Data Transfer

Source Server         : tamtienkiemtext
Source Server Version : 50529
Source Host           : localhost:3306
Source Database       : game

Target Server Type    : MYSQL
Target Server Version : 50529
File Encoding         : 65001

Date: 2026-10-27 19:54:58
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `addim`
-- ----------------------------
DROP TABLE IF EXISTS `addim`;
CREATE TABLE `addim` (
  `imuid` int(11) NOT NULL DEFAULT '0',
  `sid` text NOT NULL,
  `uid` int(11) NOT NULL,
  `msg` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of addim
-- ----------------------------

-- ----------------------------
-- Table structure for `alchemy_location`
-- ----------------------------
DROP TABLE IF EXISTS `alchemy_location`;
CREATE TABLE `alchemy_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location_type` int(11) DEFAULT '1' COMMENT '1-Nh?? ri??ng, 2-Th???o ???????ng, 3-??an Th??p, 4-C???c Luy???n ??an, 5-N??i Th???n, 6-Ti??n Gi???i',
  `level_required` int(11) DEFAULT '1',
  `success_bonus` int(11) DEFAULT '0' COMMENT 'T??ng th??nh c??ng %',
  `van_bonus` int(11) DEFAULT '0' COMMENT 'T??ng ??an v??n',
  `cost_per_hour` int(11) DEFAULT '0' COMMENT 'Ma Th???ch/gi???',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of alchemy_location
-- ----------------------------
INSERT INTO `alchemy_location` VALUES ('1', 'Nh?? Ri??ng', '1', '1', '0', '0', '0', '1');
INSERT INTO `alchemy_location` VALUES ('2', 'Th???o ???????ng', '2', '10', '5', '0', '0', '1');
INSERT INTO `alchemy_location` VALUES ('3', '??an Th??p', '3', '30', '15', '1', '10', '1');
INSERT INTO `alchemy_location` VALUES ('4', 'C???c Luy???n ??an', '4', '50', '25', '2', '20', '1');
INSERT INTO `alchemy_location` VALUES ('5', 'N??i Th???n', '5', '70', '35', '3', '50', '1');
INSERT INTO `alchemy_location` VALUES ('6', 'Ti??n Gi???i', '6', '90', '50', '5', '100', '1');

-- ----------------------------
-- Table structure for `alchemy_recipe`
-- ----------------------------
DROP TABLE IF EXISTS `alchemy_recipe`;
CREATE TABLE `alchemy_recipe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `grade` int(11) DEFAULT '1' COMMENT 'Ph???m c???p 1-12',
  `type` varchar(20) DEFAULT 'yaodan' COMMENT 'yaodan/yaopin/daoju',
  `result_id` int(11) NOT NULL,
  `result_amount` int(11) DEFAULT '1',
  `materials` text COMMENT 'JSON: [{id, amount, element}]',
  `level_required` int(11) DEFAULT '1',
  `alch_rank_required` int(11) DEFAULT '1',
  `base_success_rate` int(11) DEFAULT '50',
  `difficulty` int(11) DEFAULT '1' COMMENT '1-10',
  `toxin_base` int(11) DEFAULT '0',
  `sell_price` int(11) DEFAULT '100',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of alchemy_recipe
-- ----------------------------

-- ----------------------------
-- Table structure for `arena_daily_reward`
-- ----------------------------
DROP TABLE IF EXISTS `arena_daily_reward`;
CREATE TABLE `arena_daily_reward` (
  `reward_date` date NOT NULL,
  `sid` varchar(64) NOT NULL,
  `rank_no` int(11) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `yxb_reward` int(11) NOT NULL DEFAULT '0',
  `czb_reward` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`reward_date`,`sid`),
  KEY `rank_no` (`rank_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of arena_daily_reward
-- ----------------------------
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', '8d0329755724609ccb9713953eee47f4', '1', '1018', '10000', '100', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', '40e5df6609a8fd1b15904b9aa798a877', '2', '1000', '8500', '85', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', '4c149ae65f7dab8474cd47dbb3b17677', '3', '1000', '7200', '72', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', 'dc4996fe7860595b1bb92db3c2af9fab', '4', '1000', '6000', '60', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', 'fb36ea13a56ad413d9cccebca56bb546', '5', '1000', '5000', '50', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', 'c8c35537f4a2bfa618801654b9cd1fc5', '6', '1000', '4200', '42', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-09', '7841cebe57141c476b1d1d2272b65e53', '7', '990', '3500', '35', '2026-07-09 20:39:22');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', '40e5df6609a8fd1b15904b9aa798a877', '2', '1000', '8500', '85', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', '4c149ae65f7dab8474cd47dbb3b17677', '3', '1000', '7200', '72', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', 'dc4996fe7860595b1bb92db3c2af9fab', '4', '1000', '6000', '60', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', 'fb36ea13a56ad413d9cccebca56bb546', '5', '1000', '5000', '50', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', 'c8c35537f4a2bfa618801654b9cd1fc5', '6', '1000', '4200', '42', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-13', '7841cebe57141c476b1d1d2272b65e53', '7', '913', '3500', '35', '2026-07-13 22:43:05');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', 'fb36ea13a56ad413d9cccebca56bb546', '2', '1000', '8500', '85', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', 'c8c35537f4a2bfa618801654b9cd1fc5', '3', '1000', '7200', '72', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', '40e5df6609a8fd1b15904b9aa798a877', '4', '1000', '6000', '60', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', '4c149ae65f7dab8474cd47dbb3b17677', '5', '1000', '5000', '50', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '1000', '4200', '42', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-19', '7841cebe57141c476b1d1d2272b65e53', '7', '913', '3500', '35', '2026-07-19 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1000', '8500', '85', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', '40e5df6609a8fd1b15904b9aa798a877', '3', '1000', '7200', '72', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', '4c149ae65f7dab8474cd47dbb3b17677', '4', '1000', '6000', '60', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', 'dc4996fe7860595b1bb92db3c2af9fab', '5', '1000', '5000', '50', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', '1b3246e8f45e4276967d00ad79cab118', '6', '1000', '4200', '42', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', 'fb36ea13a56ad413d9cccebca56bb546', '7', '1000', '3500', '35', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-20', '7841cebe57141c476b1d1d2272b65e53', '8', '913', '2800', '28', '2026-07-20 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1000', '8500', '85', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', '40e5df6609a8fd1b15904b9aa798a877', '3', '1000', '7200', '72', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', '4c149ae65f7dab8474cd47dbb3b17677', '4', '1000', '6000', '60', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', 'dc4996fe7860595b1bb92db3c2af9fab', '5', '1000', '5000', '50', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', '1b3246e8f45e4276967d00ad79cab118', '6', '1000', '4200', '42', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', 'fb36ea13a56ad413d9cccebca56bb546', '7', '1000', '3500', '35', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-21', '7841cebe57141c476b1d1d2272b65e53', '8', '913', '2800', '28', '2026-07-21 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1000', '8500', '85', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', '40e5df6609a8fd1b15904b9aa798a877', '3', '1000', '7200', '72', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', '4c149ae65f7dab8474cd47dbb3b17677', '4', '1000', '6000', '60', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', 'dc4996fe7860595b1bb92db3c2af9fab', '5', '1000', '5000', '50', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', '1b3246e8f45e4276967d00ad79cab118', '6', '1000', '4200', '42', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', 'fb36ea13a56ad413d9cccebca56bb546', '7', '1000', '3500', '35', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-07-22', '7841cebe57141c476b1d1d2272b65e53', '8', '913', '2800', '28', '2026-07-22 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1000', '8500', '85', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '40e5df6609a8fd1b15904b9aa798a877', '3', '1000', '7200', '72', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '4c149ae65f7dab8474cd47dbb3b17677', '4', '1000', '6000', '60', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '1000', '5000', '50', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '1000', '4200', '42', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '1b3246e8f45e4276967d00ad79cab118', '7', '1000', '3500', '35', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', 'fb36ea13a56ad413d9cccebca56bb546', '8', '1000', '2800', '28', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-04', '7841cebe57141c476b1d1d2272b65e53', '9', '913', '2200', '22', '2026-08-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '8d0329755724609ccb9713953eee47f4', '1', '1159', '10000', '100', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '40e5df6609a8fd1b15904b9aa798a877', '2', '1000', '8500', '85', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '4c149ae65f7dab8474cd47dbb3b17677', '3', '1000', '7200', '72', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '378bf7fdd60f2d81207e58ad6da4aaca', '4', '1000', '6000', '60', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', 'dc4996fe7860595b1bb92db3c2af9fab', '5', '1000', '5000', '50', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '1b3246e8f45e4276967d00ad79cab118', '6', '1000', '4200', '42', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', 'fb36ea13a56ad413d9cccebca56bb546', '7', '1000', '3500', '35', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', 'c8c35537f4a2bfa618801654b9cd1fc5', '8', '1000', '2800', '28', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-08-05', '7841cebe57141c476b1d1d2272b65e53', '9', '913', '2200', '22', '2026-08-05 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '6c4666ee50d00945c0251d14ca2275f2', '3', '1000', '7200', '72', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', 'fb36ea13a56ad413d9cccebca56bb546', '4', '1000', '6000', '60', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '40e5df6609a8fd1b15904b9aa798a877', '5', '986', '5000', '50', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '378bf7fdd60f2d81207e58ad6da4aaca', '6', '986', '4200', '42', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', 'dc4996fe7860595b1bb92db3c2af9fab', '7', '986', '3500', '35', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '1b3246e8f45e4276967d00ad79cab118', '8', '986', '2800', '28', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-03', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-03 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '986', '5000', '50', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '986', '4200', '42', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '1b3246e8f45e4276967d00ad79cab118', '7', '986', '3500', '35', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '40e5df6609a8fd1b15904b9aa798a877', '8', '986', '2800', '28', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-04', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-04 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '6c4666ee50d00945c0251d14ca2275f2', '3', '1000', '7200', '72', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', 'fb36ea13a56ad413d9cccebca56bb546', '4', '1000', '6000', '60', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '986', '5000', '50', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '986', '4200', '42', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '1b3246e8f45e4276967d00ad79cab118', '7', '986', '3500', '35', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '40e5df6609a8fd1b15904b9aa798a877', '8', '986', '2800', '28', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-05', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-05 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', 'dc4996fe7860595b1bb92db3c2af9fab', '5', '986', '5000', '50', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '1b3246e8f45e4276967d00ad79cab118', '6', '986', '4200', '42', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '40e5df6609a8fd1b15904b9aa798a877', '7', '986', '3500', '35', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '378bf7fdd60f2d81207e58ad6da4aaca', '8', '986', '2800', '28', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-06', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-06 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '986', '5000', '50', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '986', '4200', '42', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '1b3246e8f45e4276967d00ad79cab118', '7', '986', '3500', '35', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '40e5df6609a8fd1b15904b9aa798a877', '8', '986', '2800', '28', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-07', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-07 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '6c4666ee50d00945c0251d14ca2275f2', '3', '1000', '7200', '72', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', 'fb36ea13a56ad413d9cccebca56bb546', '4', '1000', '6000', '60', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '986', '5000', '50', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '986', '4200', '42', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '1b3246e8f45e4276967d00ad79cab118', '7', '986', '3500', '35', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '40e5df6609a8fd1b15904b9aa798a877', '8', '986', '2800', '28', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-09', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-09 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '378bf7fdd60f2d81207e58ad6da4aaca', '5', '986', '5000', '50', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', 'dc4996fe7860595b1bb92db3c2af9fab', '6', '986', '4200', '42', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '1b3246e8f45e4276967d00ad79cab118', '7', '986', '3500', '35', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '40e5df6609a8fd1b15904b9aa798a877', '8', '986', '2800', '28', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-10', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-10 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '40e5df6609a8fd1b15904b9aa798a877', '5', '986', '5000', '50', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '378bf7fdd60f2d81207e58ad6da4aaca', '6', '986', '4200', '42', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', 'dc4996fe7860595b1bb92db3c2af9fab', '7', '986', '3500', '35', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '1b3246e8f45e4276967d00ad79cab118', '8', '986', '2800', '28', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-14', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-14 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', 'fb36ea13a56ad413d9cccebca56bb546', '3', '1000', '7200', '72', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '6c4666ee50d00945c0251d14ca2275f2', '4', '1000', '6000', '60', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '40e5df6609a8fd1b15904b9aa798a877', '5', '986', '5000', '50', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '378bf7fdd60f2d81207e58ad6da4aaca', '6', '986', '4200', '42', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', 'dc4996fe7860595b1bb92db3c2af9fab', '7', '986', '3500', '35', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '1b3246e8f45e4276967d00ad79cab118', '8', '986', '2800', '28', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '4c149ae65f7dab8474cd47dbb3b17677', '9', '912', '2200', '22', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-15', '7841cebe57141c476b1d1d2272b65e53', '10', '899', '1600', '16', '2026-10-15 21:00:04');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '8d0329755724609ccb9713953eee47f4', '1', '1423', '10000', '100', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '1007', '8500', '85', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '2b06d0ff01b346e5f2a0666ab83dc044', '3', '1000', '7200', '72', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', 'fb36ea13a56ad413d9cccebca56bb546', '4', '1000', '6000', '60', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '5', '1000', '5000', '50', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '6', '1000', '4200', '42', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '6c4666ee50d00945c0251d14ca2275f2', '7', '1000', '3500', '35', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '03bb973718e9c9e68ad90317c5efdf68', '8', '1000', '2800', '28', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '1b3246e8f45e4276967d00ad79cab118', '9', '986', '2200', '22', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '40e5df6609a8fd1b15904b9aa798a877', '10', '986', '1600', '16', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '378bf7fdd60f2d81207e58ad6da4aaca', '11', '986', '800', '0', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', 'dc4996fe7860595b1bb92db3c2af9fab', '12', '986', '760', '0', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '4c149ae65f7dab8474cd47dbb3b17677', '13', '912', '720', '0', '2026-10-16 21:00:03');
INSERT INTO `arena_daily_reward` VALUES ('2026-10-16', '7841cebe57141c476b1d1d2272b65e53', '14', '899', '680', '0', '2026-10-16 21:00:03');

-- ----------------------------
-- Table structure for `arena_log`
-- ----------------------------
DROP TABLE IF EXISTS `arena_log`;
CREATE TABLE `arena_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attacker_sid` varchar(64) NOT NULL,
  `defender_sid` varchar(64) NOT NULL,
  `winner_sid` varchar(64) NOT NULL,
  `score_change` int(11) NOT NULL DEFAULT '0',
  `exp_reward` int(11) NOT NULL DEFAULT '0',
  `wg_reward` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attacker_sid` (`attacker_sid`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of arena_log
-- ----------------------------
INSERT INTO `arena_log` VALUES ('1', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '18', '88', '6', '2026-07-09 20:35:37');
INSERT INTO `arena_log` VALUES ('2', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '17', '88', '6', '2026-07-09 20:39:22');
INSERT INTO `arena_log` VALUES ('3', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '17', '88', '6', '2026-07-09 20:39:29');
INSERT INTO `arena_log` VALUES ('4', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '17', '88', '6', '2026-07-09 20:39:31');
INSERT INTO `arena_log` VALUES ('5', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '16', '88', '6', '2026-07-09 20:39:41');
INSERT INTO `arena_log` VALUES ('6', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '16', '88', '6', '2026-07-09 20:39:51');
INSERT INTO `arena_log` VALUES ('7', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '15', '88', '6', '2026-07-09 20:39:55');
INSERT INTO `arena_log` VALUES ('8', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '15', '88', '6', '2026-07-09 20:39:58');
INSERT INTO `arena_log` VALUES ('9', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '14', '88', '6', '2026-07-09 20:39:59');
INSERT INTO `arena_log` VALUES ('10', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '14', '88', '6', '2026-07-09 20:40:10');
INSERT INTO `arena_log` VALUES ('11', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '15', '88', '6', '2026-10-03 14:23:36');
INSERT INTO `arena_log` VALUES ('12', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '15', '88', '6', '2026-10-03 14:23:37');
INSERT INTO `arena_log` VALUES ('13', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '14', '88', '6', '2026-10-03 14:23:38');
INSERT INTO `arena_log` VALUES ('14', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '14', '88', '6', '2026-10-03 14:58:37');
INSERT INTO `arena_log` VALUES ('15', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '13', '88', '6', '2026-10-03 14:58:38');
INSERT INTO `arena_log` VALUES ('16', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '13', '88', '6', '2026-10-03 14:58:39');
INSERT INTO `arena_log` VALUES ('17', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '13', '88', '6', '2026-10-03 14:58:40');
INSERT INTO `arena_log` VALUES ('18', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 14:58:42');
INSERT INTO `arena_log` VALUES ('19', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 14:58:43');
INSERT INTO `arena_log` VALUES ('20', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 14:58:44');
INSERT INTO `arena_log` VALUES ('21', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('22', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('23', '8d0329755724609ccb9713953eee47f4', '378bf7fdd60f2d81207e58ad6da4aaca', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('24', '8d0329755724609ccb9713953eee47f4', '1b3246e8f45e4276967d00ad79cab118', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('25', '8d0329755724609ccb9713953eee47f4', '40e5df6609a8fd1b15904b9aa798a877', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('26', '8d0329755724609ccb9713953eee47f4', 'dc4996fe7860595b1bb92db3c2af9fab', '8d0329755724609ccb9713953eee47f4', '12', '96', '6', '2026-10-03 16:18:55');
INSERT INTO `arena_log` VALUES ('27', '8d0329755724609ccb9713953eee47f4', 'c8c35537f4a2bfa618801654b9cd1fc5', 'c8c35537f4a2bfa618801654b9cd1fc5', '-13', '2030', '100', '2026-10-03 16:20:48');
INSERT INTO `arena_log` VALUES ('28', '8d0329755724609ccb9713953eee47f4', '7841cebe57141c476b1d1d2272b65e53', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:21:01');
INSERT INTO `arena_log` VALUES ('29', '8d0329755724609ccb9713953eee47f4', '4c149ae65f7dab8474cd47dbb3b17677', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:21:01');
INSERT INTO `arena_log` VALUES ('30', '8d0329755724609ccb9713953eee47f4', '378bf7fdd60f2d81207e58ad6da4aaca', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:21:01');
INSERT INTO `arena_log` VALUES ('31', '8d0329755724609ccb9713953eee47f4', '1b3246e8f45e4276967d00ad79cab118', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:21:01');
INSERT INTO `arena_log` VALUES ('32', '8d0329755724609ccb9713953eee47f4', '40e5df6609a8fd1b15904b9aa798a877', '8d0329755724609ccb9713953eee47f4', '12', '88', '6', '2026-10-03 16:21:01');
INSERT INTO `arena_log` VALUES ('33', '8d0329755724609ccb9713953eee47f4', 'dc4996fe7860595b1bb92db3c2af9fab', '8d0329755724609ccb9713953eee47f4', '12', '96', '6', '2026-10-03 16:21:01');

-- ----------------------------
-- Table structure for `arena_player`
-- ----------------------------
DROP TABLE IF EXISTS `arena_player`;
CREATE TABLE `arena_player` (
  `sid` varchar(64) NOT NULL,
  `score` int(11) NOT NULL DEFAULT '1000',
  `wins` int(11) NOT NULL DEFAULT '0',
  `losses` int(11) NOT NULL DEFAULT '0',
  `today_fights` int(11) NOT NULL DEFAULT '0',
  `last_fight_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`),
  KEY `score` (`score`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of arena_player
-- ----------------------------
INSERT INTO `arena_player` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', '1007', '1', '0', '0', '2026-10-03', '2026-10-03 16:20:48');
INSERT INTO `arena_player` VALUES ('fb36ea13a56ad413d9cccebca56bb546', '1000', '0', '0', '0', '2026-07-09', '2026-07-09 20:35:10');
INSERT INTO `arena_player` VALUES ('dc4996fe7860595b1bb92db3c2af9fab', '986', '0', '2', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('4c149ae65f7dab8474cd47dbb3b17677', '912', '0', '12', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('8d0329755724609ccb9713953eee47f4', '1423', '32', '1', '6', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('40e5df6609a8fd1b15904b9aa798a877', '986', '0', '2', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('7841cebe57141c476b1d1d2272b65e53', '899', '0', '12', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('1b3246e8f45e4276967d00ad79cab118', '986', '0', '2', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('378bf7fdd60f2d81207e58ad6da4aaca', '986', '0', '2', '0', '2026-10-03', '2026-10-03 16:21:01');
INSERT INTO `arena_player` VALUES ('6c4666ee50d00945c0251d14ca2275f2', '1000', '0', '0', '0', '2026-10-03', '2026-10-03 16:47:21');
INSERT INTO `arena_player` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1000', '0', '0', '0', '2026-10-16', '2026-10-16 08:37:35');
INSERT INTO `arena_player` VALUES ('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '1000', '0', '0', '0', '2026-10-16', '2026-10-16 08:37:35');
INSERT INTO `arena_player` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1000', '0', '0', '0', '2026-10-16', '2026-10-16 08:37:35');
INSERT INTO `arena_player` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '1000', '0', '0', '0', '2026-10-16', '2026-10-16 08:37:35');

-- ----------------------------
-- Table structure for `boss`
-- ----------------------------
DROP TABLE IF EXISTS `boss`;
CREATE TABLE `boss` (
  `bossid` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `bossname` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `bossinfo` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `bosslv` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `bosshp` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bossmaxhp` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `bossgj` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bossfy` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bossbj` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bossxx` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `bosszb` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `dljv` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '1',
  `bossdj` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `djjv` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '1',
  `bs` int(8) NOT NULL DEFAULT '0',
  `bosstime` datetime NOT NULL,
  `sid` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `bossyp` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '6',
  `ypjv` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '1',
  `bossexp` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  PRIMARY KEY (`bossid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9003 DEFAULT CHARSET=gb2312 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of boss
-- ----------------------------
INSERT INTO `boss` VALUES ('1', 'Boss·Ma Thú', 'Thú cưng của Vương mẫu nương nương, bị phong ấn lâu năm, bản tính tàn bạo khó thuần. Sức mạnh cuồng nộ, một khi nổi điên sẽ xé nát mọi thứ xung quanh.', '30', '-2908', '29600', '1480', '470', '20', '10', '11,53,54,55,56,57,58,59', '10', '1,20', '10', '0', '2022-03-22 19:02:50', '', '9', '0', '0');
INSERT INTO `boss` VALUES ('2', 'Boss·Địa Ngục Chi Nhãn', 'Quái vật canh giữ vực sâu địa ngục. Sở hữu nhãn lực tà dị, mỗi đòn đánh đều mang theo sát thương chí mạng, khiến kẻ địch khó lòng sống sót.', '60', '36194', '72200', '2623', '980', '60', '20', '4,7', '10', '1,22', '10', '0', '2022-03-22 14:01:20', '', '6', '1', '0');
INSERT INTO `boss` VALUES ('3', 'Boss·Thiên Linh Lục Nhĩ', 'Yêu hầu sáu tai tu luyện ngàn năm, hấp thụ vô số linh hồn. Thân pháp quỷ dị, võ công thâm sâu, ra tay nhanh như điện, cực kỳ nguy hiểm.', '70', '77378', '95700', '3698', '1213', '30', '15', '71,72,73,74,75,76,77', '10', '1,22', '10', '0', '2022-03-22 16:20:48', '', '9', '0', '0');
INSERT INTO `boss` VALUES ('11', 'Boss·Thương Khung Cổ Thú', 'Cổ thú được trưởng thôn bí mật nuôi dưỡng để trấn giữ bảo vật. Sức mạnh vượt trội, từng tiêu diệt mọi kẻ xâm phạm, là kẻ canh giữ không thể xem thường.', '45', '50700', '50700', '1725', '1562', '40', '10', '10,53,54,55,56,57,58,59', '10', '1,21', '10', '0', '2022-03-22 19:04:10', '', '9', '0', '0');

-- ----------------------------
-- Table structure for `bossinfo`
-- ----------------------------
DROP TABLE IF EXISTS `bossinfo`;
CREATE TABLE `bossinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bossid` varchar(11) NOT NULL DEFAULT '',
  `bossname` varchar(11) NOT NULL DEFAULT '',
  `uname` varchar(11) NOT NULL DEFAULT '',
  `msg` varchar(128) NOT NULL DEFAULT '',
  `shijian` varchar(64) NOT NULL DEFAULT '',
  `umsg` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=13713 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='打boss时候的聊天';

-- ----------------------------
-- Records of bossinfo
-- ----------------------------

-- ----------------------------
-- Table structure for `club`
-- ----------------------------
DROP TABLE IF EXISTS `club`;
CREATE TABLE `club` (
  `clubname` varchar(255) NOT NULL,
  `clubinfo` varchar(255) NOT NULL,
  `clublv` int(11) NOT NULL DEFAULT '1',
  `clubid` int(11) NOT NULL AUTO_INCREMENT,
  `clubno1` int(11) NOT NULL,
  `clubexp` int(11) NOT NULL,
  `clubyxb` int(11) NOT NULL,
  `clubczb` int(11) NOT NULL,
  `clubdiamond` int(11) NOT NULL DEFAULT '0',
  `clubskill` text,
  `zdsp` varchar(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`clubid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of club
-- ----------------------------
INSERT INTO `club` VALUES ('Giang Hồ', 'Giang hồ đại đạo, nhân tài xuất hiện lớp lớp!', '1', '3', '426', '0', '0', '0', '0', null, '1');
INSERT INTO `club` VALUES ('Huyền Nữ Phong', 'Nhất lộ tây hành luyện bản lĩnh, bây giờ đại thành, đúng lúc gặp nữ phong sơn hạ nữ nhi thành, cản sơn ta làm thần tiên sống.', '1', '4', '457', '0', '0', '0', '0', null, '0');
INSERT INTO `club` VALUES ('Quan Ninh', '', '1', '5', '479', '0', '0', '0', '0', null, '0');
INSERT INTO `club` VALUES ('Thiên Môn', '', '1', '6', '481', '0', '0', '0', '0', null, '0');
INSERT INTO `club` VALUES ('Hoa Quả Sơn', 'Đậu đậu..moa! Ta là Hầu gia gia!', '1', '9', '517', '0', '0', '0', '0', null, '0');
INSERT INTO `club` VALUES ('Giác Hoàng Cốc', 'Vô vị sinh tử, chấp nhất sứ mệnh, nhất trí đối ngoại, này chi vị hoàng giả!', '1', '10', '520', '0', '0', '0', '0', null, '1');
INSERT INTO `club` VALUES ('Cửu Tông Thập Tam Môn', '', '1', '20', '547', '24817310', '50', '0', '0', null, '0');

-- ----------------------------
-- Table structure for `club_boss`
-- ----------------------------
DROP TABLE IF EXISTS `club_boss`;
CREATE TABLE `club_boss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clubid` int(11) NOT NULL,
  `boss_name` varchar(255) NOT NULL DEFAULT 'Boss Tông Môn',
  `boss_lv` int(11) NOT NULL DEFAULT '1',
  `boss_hp` bigint(20) NOT NULL,
  `boss_maxhp` bigint(20) NOT NULL,
  `boss_atk` int(11) NOT NULL DEFAULT '50',
  `boss_def` int(11) NOT NULL DEFAULT '10',
  `boss_crit` int(11) NOT NULL DEFAULT '10',
  `boss_date` date NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=chua mo, 1=dang mo, 2=da chet, 3=het gio',
  `killed_by` int(11) DEFAULT NULL COMMENT 'uid nguoi ket lieu',
  `killed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_club_date` (`clubid`,`boss_date`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_boss
-- ----------------------------
INSERT INTO `club_boss` VALUES ('6', '20', 'Boss Tông Môn', '1', '0', '100000', '800', '1500', '12', '2026-07-24', '00:00:00', '00:00:00', '2', '547', '2026-07-24 00:27:03', null);
INSERT INTO `club_boss` VALUES ('7', '20', 'Boss Tông Môn', '1', '0', '100000', '800', '1500', '12', '2026-07-25', '00:00:00', '00:00:00', '2', '547', '2026-07-24 23:42:10', null);
INSERT INTO `club_boss` VALUES ('8', '20', 'Boss Tông Môn', '1', '1820', '100000', '800', '1500', '12', '2026-07-28', '00:00:00', '00:00:00', '0', null, null, null);
INSERT INTO `club_boss` VALUES ('9', '20', 'Boss Tông Môn', '1', '100000', '100000', '800', '1500', '12', '2026-07-29', '00:00:00', '00:00:00', '0', null, null, null);

-- ----------------------------
-- Table structure for `club_boss_damage`
-- ----------------------------
DROP TABLE IF EXISTS `club_boss_damage`;
CREATE TABLE `club_boss_damage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boss_id` int(11) NOT NULL,
  `clubid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` varchar(255) NOT NULL,
  `total_damage` bigint(20) NOT NULL DEFAULT '0',
  `hit_count` int(11) NOT NULL DEFAULT '0',
  `crit_count` int(11) NOT NULL DEFAULT '0',
  `dodge_count` int(11) NOT NULL DEFAULT '0',
  `pet_damage` bigint(20) NOT NULL DEFAULT '0',
  `last_hit_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_boss_player` (`boss_id`,`uid`),
  KEY `idx_club_id` (`clubid`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_boss_damage
-- ----------------------------
INSERT INTO `club_boss_damage` VALUES ('58', '6', '20', '547', '8d0329755724609ccb9713953eee47f4', '106896', '18', '10', '5', '18', '2026-07-24 00:27:03', null);
INSERT INTO `club_boss_damage` VALUES ('59', '7', '20', '547', '8d0329755724609ccb9713953eee47f4', '100818', '13', '7', '1', '13', '2026-07-24 23:42:10', null);
INSERT INTO `club_boss_damage` VALUES ('60', '8', '20', '547', '8d0329755724609ccb9713953eee47f4', '98180', '14', '6', '2', '14', '2026-07-28 01:29:28', null);

-- ----------------------------
-- Table structure for `club_boss_log`
-- ----------------------------
DROP TABLE IF EXISTS `club_boss_log`;
CREATE TABLE `club_boss_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boss_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `damage` bigint(20) NOT NULL DEFAULT '0',
  `is_crit` tinyint(4) NOT NULL DEFAULT '0',
  `is_dodge` tinyint(4) NOT NULL DEFAULT '0',
  `pet_damage` bigint(20) NOT NULL DEFAULT '0',
  `boss_hp_after` bigint(20) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_boss_id` (`boss_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_boss_log
-- ----------------------------
INSERT INTO `club_boss_log` VALUES ('44', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '89950', null);
INSERT INTO `club_boss_log` VALUES ('45', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '79900', null);
INSERT INTO `club_boss_log` VALUES ('46', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '69850', null);
INSERT INTO `club_boss_log` VALUES ('47', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '59800', null);
INSERT INTO `club_boss_log` VALUES ('48', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '49750', null);
INSERT INTO `club_boss_log` VALUES ('49', '6', '547', 'PhátĐây', '0', '1', '1', '1', '49750', null);
INSERT INTO `club_boss_log` VALUES ('50', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '39700', null);
INSERT INTO `club_boss_log` VALUES ('51', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '33609', null);
INSERT INTO `club_boss_log` VALUES ('52', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '23559', null);
INSERT INTO `club_boss_log` VALUES ('53', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '13509', null);
INSERT INTO `club_boss_log` VALUES ('54', '6', '547', 'PhátĐây', '0', '0', '1', '1', '13509', null);
INSERT INTO `club_boss_log` VALUES ('55', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '7418', null);
INSERT INTO `club_boss_log` VALUES ('56', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '1327', null);
INSERT INTO `club_boss_log` VALUES ('57', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '0', null);
INSERT INTO `club_boss_log` VALUES ('58', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '89950', null);
INSERT INTO `club_boss_log` VALUES ('59', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '83859', null);
INSERT INTO `club_boss_log` VALUES ('60', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '73809', null);
INSERT INTO `club_boss_log` VALUES ('61', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '63759', null);
INSERT INTO `club_boss_log` VALUES ('62', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '57668', null);
INSERT INTO `club_boss_log` VALUES ('63', '6', '547', 'PhátĐây', '0', '1', '1', '1', '57668', null);
INSERT INTO `club_boss_log` VALUES ('64', '6', '547', 'PhátĐây', '0', '1', '1', '1', '57668', null);
INSERT INTO `club_boss_log` VALUES ('65', '6', '547', 'PhátĐây', '0', '0', '1', '1', '57668', null);
INSERT INTO `club_boss_log` VALUES ('66', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '47618', null);
INSERT INTO `club_boss_log` VALUES ('67', '6', '547', 'PhátĐây', '0', '1', '1', '1', '47618', null);
INSERT INTO `club_boss_log` VALUES ('68', '6', '547', 'PhátĐây', '0', '0', '1', '1', '47618', null);
INSERT INTO `club_boss_log` VALUES ('69', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '41527', null);
INSERT INTO `club_boss_log` VALUES ('70', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '31477', null);
INSERT INTO `club_boss_log` VALUES ('71', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '21427', null);
INSERT INTO `club_boss_log` VALUES ('72', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '15336', null);
INSERT INTO `club_boss_log` VALUES ('73', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '9245', null);
INSERT INTO `club_boss_log` VALUES ('74', '6', '547', 'PhátĐây', '6091', '0', '0', '1', '3154', null);
INSERT INTO `club_boss_log` VALUES ('75', '6', '547', 'PhátĐây', '10050', '1', '0', '1', '0', null);
INSERT INTO `club_boss_log` VALUES ('76', '8', '547', 'PhátĐây', '6175', '0', '0', '1', '77461', null);
INSERT INTO `club_boss_log` VALUES ('77', '8', '547', 'PhátĐây', '6175', '0', '0', '1', '71286', null);
INSERT INTO `club_boss_log` VALUES ('78', '8', '547', 'PhátĐây', '10188', '1', '0', '1', '61098', null);
INSERT INTO `club_boss_log` VALUES ('79', '8', '547', 'PhátĐây', '6175', '0', '0', '1', '54923', null);
INSERT INTO `club_boss_log` VALUES ('80', '8', '547', 'PhátĐây', '6175', '0', '0', '1', '48748', null);
INSERT INTO `club_boss_log` VALUES ('81', '8', '547', 'PhátĐây', '10188', '1', '0', '1', '38560', null);
INSERT INTO `club_boss_log` VALUES ('82', '8', '547', 'PhátĐây', '10188', '1', '0', '1', '28372', null);
INSERT INTO `club_boss_log` VALUES ('83', '8', '547', 'PhátĐây', '6175', '0', '0', '1', '22197', null);
INSERT INTO `club_boss_log` VALUES ('84', '8', '547', 'PhátĐây', '1', '0', '1', '1', '22196', null);
INSERT INTO `club_boss_log` VALUES ('85', '8', '547', 'PhátĐây', '10188', '1', '0', '1', '12008', null);
INSERT INTO `club_boss_log` VALUES ('86', '8', '547', 'PhátĐây', '10188', '1', '0', '1', '1820', null);

-- ----------------------------
-- Table structure for `club_player_quest`
-- ----------------------------
DROP TABLE IF EXISTS `club_player_quest`;
CREATE TABLE `club_player_quest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `clubid` int(11) NOT NULL,
  `quest_config_id` int(11) NOT NULL,
  `quest_date` date NOT NULL,
  `current_value` int(11) NOT NULL DEFAULT '0',
  `claimed` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sid_date_config` (`sid`,`quest_date`,`quest_config_id`),
  KEY `idx_club_date` (`clubid`,`quest_date`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_player_quest
-- ----------------------------
INSERT INTO `club_player_quest` VALUES ('8', '8d0329755724609ccb9713953eee47f4', '20', '4', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('9', '8d0329755724609ccb9713953eee47f4', '20', '5', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('10', '8d0329755724609ccb9713953eee47f4', '20', '11', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('11', '8d0329755724609ccb9713953eee47f4', '20', '21', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('12', '8d0329755724609ccb9713953eee47f4', '20', '16', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('13', '8d0329755724609ccb9713953eee47f4', '20', '8', '2026-07-25', '0', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('14', '8d0329755724609ccb9713953eee47f4', '20', '19', '2026-07-25', '1', '0', '2026-07-25 06:00:10');
INSERT INTO `club_player_quest` VALUES ('15', '8d0329755724609ccb9713953eee47f4', '20', '21', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('16', '8d0329755724609ccb9713953eee47f4', '20', '2', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('17', '8d0329755724609ccb9713953eee47f4', '20', '18', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('18', '8d0329755724609ccb9713953eee47f4', '20', '4', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('19', '8d0329755724609ccb9713953eee47f4', '20', '14', '2026-07-27', '1', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('20', '8d0329755724609ccb9713953eee47f4', '20', '12', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('21', '8d0329755724609ccb9713953eee47f4', '20', '10', '2026-07-27', '0', '0', '2026-07-27 22:57:03');
INSERT INTO `club_player_quest` VALUES ('22', '8d0329755724609ccb9713953eee47f4', '20', '6', '2026-07-28', '46928', '1', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('23', '8d0329755724609ccb9713953eee47f4', '20', '14', '2026-07-28', '1', '1', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('24', '8d0329755724609ccb9713953eee47f4', '20', '19', '2026-07-28', '0', '0', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('25', '8d0329755724609ccb9713953eee47f4', '20', '8', '2026-07-28', '11', '0', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('26', '8d0329755724609ccb9713953eee47f4', '20', '12', '2026-07-28', '0', '0', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('27', '8d0329755724609ccb9713953eee47f4', '20', '4', '2026-07-28', '1', '0', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('28', '8d0329755724609ccb9713953eee47f4', '20', '20', '2026-07-28', '0', '0', '2026-07-28 00:22:45');
INSERT INTO `club_player_quest` VALUES ('29', '8d0329755724609ccb9713953eee47f4', '20', '11', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('30', '8d0329755724609ccb9713953eee47f4', '20', '25', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('31', '8d0329755724609ccb9713953eee47f4', '20', '7', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('32', '8d0329755724609ccb9713953eee47f4', '20', '9', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('33', '8d0329755724609ccb9713953eee47f4', '20', '19', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('34', '8d0329755724609ccb9713953eee47f4', '20', '22', '2026-07-29', '0', '0', '2026-07-29 09:29:43');
INSERT INTO `club_player_quest` VALUES ('35', '8d0329755724609ccb9713953eee47f4', '20', '14', '2026-07-29', '0', '0', '2026-07-29 09:29:43');

-- ----------------------------
-- Table structure for `club_quest_config`
-- ----------------------------
DROP TABLE IF EXISTS `club_quest_config`;
CREATE TABLE `club_quest_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quest_type` varchar(30) NOT NULL,
  `quest_tier` tinyint(4) NOT NULL DEFAULT '1',
  `quest_name` varchar(100) NOT NULL,
  `quest_desc` varchar(200) NOT NULL,
  `target_value` int(11) NOT NULL DEFAULT '0',
  `reward_exp` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `donate_item_type` varchar(20) DEFAULT NULL COMMENT 'Loại item: yaodan/daoju/yaopin',
  `donate_item_id` int(11) DEFAULT NULL COMMENT 'ID vật phẩm cần đóng góp',
  `donate_item_amount` int(11) DEFAULT NULL COMMENT 'Số lượng vật phẩm cần đóng góp',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`quest_type`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_quest_config
-- ----------------------------
INSERT INTO `club_quest_config` VALUES ('1', 'donate_lt', '1', 'Quyên góp Linh Thạch (Dễ)', 'Quyên góp 50 Linh Thạch cho tông môn', '50', '200', '1', null, null, null);
INSERT INTO `club_quest_config` VALUES ('2', 'donate_lt', '2', 'Quyên góp Linh Thạch (Trung)', 'Quyên góp 100 Linh Thạch cho tông môn', '100', '400', '2', null, null, null);
INSERT INTO `club_quest_config` VALUES ('3', 'donate_lt', '3', 'Quyên góp Linh Thạch (Khó)', 'Quyên góp 200 Linh Thạch cho tông môn', '200', '800', '3', null, null, null);
INSERT INTO `club_quest_config` VALUES ('5', 'boss_damage', '1', 'Gây Sát Thương Boss (Dễ)', 'Gây 50,000 sát thương lên Boss', '20000', '300', '5', null, null, null);
INSERT INTO `club_quest_config` VALUES ('6', 'boss_damage', '2', 'Gây Sát Thương Boss (Trung)', 'Gây 100,000 sát thương lên Boss', '30000', '600', '6', null, null, null);
INSERT INTO `club_quest_config` VALUES ('7', 'boss_damage', '3', 'Gây Sát Thương Boss (Khó)', 'Gây 200,000 sát thương lên Boss', '50000', '1200', '7', null, null, null);
INSERT INTO `club_quest_config` VALUES ('8', 'meditate', '1', 'Tu Luyện (Dễ)', 'Tu luyện 30 phút', '30', '150', '8', null, null, null);
INSERT INTO `club_quest_config` VALUES ('9', 'meditate', '2', 'Tu Luyện (Trung)', 'Tu luyện 60 phút', '60', '300', '9', null, null, null);
INSERT INTO `club_quest_config` VALUES ('10', 'meditate', '3', 'Tu Luyện (Khó)', 'Tu luyện 120 phút', '120', '600', '10', null, null, null);
INSERT INTO `club_quest_config` VALUES ('11', 'pve_win', '1', 'Chiến Đấu PVE (Dễ)', 'Thắng 5 trận PVE', '5', '100', '11', null, null, null);
INSERT INTO `club_quest_config` VALUES ('12', 'pve_win', '2', 'Chiến Đấu PVE (Trung)', 'Thắng 10 trận PVE', '10', '200', '12', null, null, null);
INSERT INTO `club_quest_config` VALUES ('13', 'pve_win', '3', 'Chiến Đấu PVE (Khó)', 'Thắng 20 trận PVE', '20', '400', '13', null, null, null);
INSERT INTO `club_quest_config` VALUES ('14', 'online', '1', 'Online (Dễ)', 'Online 1 giờ', '1', '100', '14', null, null, null);
INSERT INTO `club_quest_config` VALUES ('15', 'online', '2', 'Online (Trung)', 'Online 2 giờ', '2', '200', '15', null, null, null);
INSERT INTO `club_quest_config` VALUES ('16', 'online', '3', 'Online (Khó)', 'Online 4 giờ', '4', '400', '16', null, null, null);
INSERT INTO `club_quest_config` VALUES ('17', 'alchemy', '1', 'Luyện Đan (Dễ)', 'Luyện đan thành công 1 lần', '1', '150', '17', null, null, null);
INSERT INTO `club_quest_config` VALUES ('18', 'alchemy', '2', 'Luyện Đan (Trung)', 'Luyện đan thành công 3 lần', '3', '350', '18', null, null, null);
INSERT INTO `club_quest_config` VALUES ('19', 'alchemy', '3', 'Luyện Đan (Khó)', 'Luyện đan thành công 5 lần', '5', '700', '19', null, null, null);
INSERT INTO `club_quest_config` VALUES ('20', 'alchemy_donate', '1', 'Luyện Đan Đóng Góp (Dễ)', 'Luyện 1 lần và đóng góp 5 đan dược cho tông môn', '1', '200', '50', 'yaodan', '1', '5');
INSERT INTO `club_quest_config` VALUES ('21', 'alchemy_donate', '2', 'Luyện Đan Đóng Góp (Trung)', 'Luyện 3 lần và đóng góp 10 đan dược cho tông môn', '3', '500', '51', 'yaodan', '1', '10');
INSERT INTO `club_quest_config` VALUES ('22', 'alchemy_donate', '3', 'Luyện Đan Đóng Góp (Khó)', 'Luyện 5 lần và đóng góp 20 đan dược cao cấp cho tông môn', '5', '1000', '52', 'yaodan', '112', '20');
INSERT INTO `club_quest_config` VALUES ('25', 'boss_attack', '1', 'Tấn Công Boss (Dễ)', 'Tấn công Boss Tông Môn 1 lần', '1', '200', '4', null, null, null);
INSERT INTO `club_quest_config` VALUES ('26', 'boss_attack', '2', 'Tấn Công Boss (Trung)', 'Tấn công Boss Tông Môn 3 lần', '3', '400', '41', null, null, null);
INSERT INTO `club_quest_config` VALUES ('27', 'boss_attack', '3', 'Tấn Công Boss (Khó)', 'Tấn công Boss Tông Môn 5 lần', '5', '800', '42', null, null, null);

-- ----------------------------
-- Table structure for `club_treasury`
-- ----------------------------
DROP TABLE IF EXISTS `club_treasury`;
CREATE TABLE `club_treasury` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clubid` int(11) NOT NULL,
  `item_type` varchar(20) NOT NULL COMMENT 'yaodan|yaopin|daoju',
  `item_id` int(11) NOT NULL,
  `item_amount` int(11) NOT NULL DEFAULT '1',
  `donated_by` int(11) NOT NULL COMMENT 'sid of crafter',
  `claimed_by` int(11) DEFAULT NULL COMMENT 'sid of claimer (NULL=unclaimed)',
  `created_at` datetime NOT NULL,
  `claimed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clubid` (`clubid`),
  KEY `idx_unclaimed` (`clubid`,`claimed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of club_treasury
-- ----------------------------
INSERT INTO `club_treasury` VALUES ('1', '20', 'yaopin', '6', '2', '8', null, '2026-07-24 09:29:29', null);
INSERT INTO `club_treasury` VALUES ('2', '20', 'yaopin', '6', '2', '8', null, '2026-07-24 09:30:41', null);

-- ----------------------------
-- Table structure for `clubplayer`
-- ----------------------------
DROP TABLE IF EXISTS `clubplayer`;
CREATE TABLE `clubplayer` (
  `clubid` int(11) NOT NULL,
  `sid` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `uclv` int(11) NOT NULL,
  `bpsp` varchar(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of clubplayer
-- ----------------------------
INSERT INTO `clubplayer` VALUES ('0', '', '0', '0', '0', '2');
INSERT INTO `clubplayer` VALUES ('3', 'a2377a650da6bb0ad4fe0514f971f858', '426', '1', '0', '3');
INSERT INTO `clubplayer` VALUES ('3', '08b46397b9704ed9b6230e3864dfde52', '442', '2', '0', '9');
INSERT INTO `clubplayer` VALUES ('4', 'e8d7c6fb768be55653f7cb2f7164bd1d', '457', '1', '0', '15');
INSERT INTO `clubplayer` VALUES ('4', '39f73d46e13bed758f42d5695f3f43f0', '459', '6', '0', '16');
INSERT INTO `clubplayer` VALUES ('4', '38171773e6842eb588ccc4ec4a7f48a8', '465', '6', '0', '17');
INSERT INTO `clubplayer` VALUES ('4', 'b8a188334584e024060c33c6b3a9bbc5', '468', '6', '0', '19');
INSERT INTO `clubplayer` VALUES ('4', '0b82a8fd07dfafc237a5dcc15b332ce8', '476', '6', '0', '20');
INSERT INTO `clubplayer` VALUES ('5', 'a1cf65d31ba46d90e465db5279329470', '479', '1', '0', '21');
INSERT INTO `clubplayer` VALUES ('6', 'f52f24af276734367a122e13b0af67d2', '481', '1', '0', '22');
INSERT INTO `clubplayer` VALUES ('5', 'b6d9f0bb1852a15b241410b3b07a21f8', '483', '6', '0', '23');
INSERT INTO `clubplayer` VALUES ('6', '2584819298592ea557f2c1d6ae08bbfe', '484', '6', '0', '24');
INSERT INTO `clubplayer` VALUES ('3', '7660cbcbcfec945cbdac430361da363f', '505', '6', '0', '25');
INSERT INTO `clubplayer` VALUES ('3', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '2', '0', '28');
INSERT INTO `clubplayer` VALUES ('3', '111111', '441', '6', '0', '31');
INSERT INTO `clubplayer` VALUES ('3', '043c4eac8ffaa70af1ccb3e0eef0a364', '467', '6', '0', '32');
INSERT INTO `clubplayer` VALUES ('3', '087efeb819f49c1789df1f599ec15388', '419', '6', '0', '36');
INSERT INTO `clubplayer` VALUES ('9', 'acea4cf5bfd750233ebc90c1361cd568', '517', '1', '0', '40');
INSERT INTO `clubplayer` VALUES ('9', 'd9bc42eda8a25d32618518f4ae68b92a', '518', '2', '0', '45');
INSERT INTO `clubplayer` VALUES ('10', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '1', '0', '46');
INSERT INTO `clubplayer` VALUES ('10', '1266218ff94820df0b66c6ef60d0541f', '525', '4', '0', '47');
INSERT INTO `clubplayer` VALUES ('10', '454ac9cbdb63df840643655755078a94', '521', '2', '0', '49');
INSERT INTO `clubplayer` VALUES ('9', '0e975728648b4af97a3eb0dfaf7e303d', '522', '3', '0', '50');
INSERT INTO `clubplayer` VALUES ('3', 'b624121f7f5c40f8c6ac507f6cab45f5', '438', '6', '0', '51');
INSERT INTO `clubplayer` VALUES ('10', '1ac81512a158e2622c17a49ba9b7eaf8', '538', '6', '0', '52');
INSERT INTO `clubplayer` VALUES ('9', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '3', '0', '54');
INSERT INTO `clubplayer` VALUES ('11', 'e17309aac194beac068b4383e5575918', '541', '1', '0', '55');
INSERT INTO `clubplayer` VALUES ('3', 'fb36ea13a56ad413d9cccebca56bb546', '544', '6', '0', '57');
INSERT INTO `clubplayer` VALUES ('20', '8d0329755724609ccb9713953eee47f4', '547', '1', '0', '68');

-- ----------------------------
-- Table structure for `clubsp`
-- ----------------------------
DROP TABLE IF EXISTS `clubsp`;
CREATE TABLE `clubsp` (
  `clubid` int(11) NOT NULL,
  `sid` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `uclv` int(11) NOT NULL,
  `bpsp` varchar(11) NOT NULL DEFAULT '0' COMMENT '帮派审批',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='帮派审批';

-- ----------------------------
-- Records of clubsp
-- ----------------------------

-- ----------------------------
-- Table structure for `congphap`
-- ----------------------------
DROP TABLE IF EXISTS `congphap`;
CREATE TABLE `congphap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wgname` varchar(255) NOT NULL DEFAULT '',
  `wgys` varchar(255) NOT NULL DEFAULT '',
  `wginfo` longtext NOT NULL,
  `wglx` varchar(255) NOT NULL DEFAULT '',
  `wgsum` varchar(255) NOT NULL DEFAULT '1',
  `wgid` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of congphap
-- ----------------------------
INSERT INTO `congphap` VALUES ('1', 'Không có có võ công', '#fff', 'Không có bí tịch', '0', '1', '0');
INSERT INTO `congphap` VALUES ('2', 'Tát thạch hôi', '#10bf61', 'Chạy trốn chuyên dụng 99% thành công', '1', '1', '1');
INSERT INTO `congphap` VALUES ('3', 'Thuận thủ khiên dương', '#288dc7', 'Hâm mộ người khác không bằng thử nghiệm pháp, trán, đầu tiên ngươi trước tiên cần phải học được bị đánh, nếu không mạng nhỏ khó. . .', '0', '1', '2');
INSERT INTO `congphap` VALUES ('4', 'Bách độc bất xâm', '#f9c508', 'Nghe nói cai võ công đối ăn đan dược có hiệu quả', '0', '1', '3');
INSERT INTO `congphap` VALUES ('5', 'Thủ hạ lưu tình', '#f9c508', 'Cai võ công có thể để boss lưu lại một kích trí mạng, đối hữu hảo thiết lập quan hệ ngoại giao có chỗ tốt cực lớn.', '1', '1', '4');
INSERT INTO `congphap` VALUES ('6', 'Quyết trương tâm pháp', '#f9c508', 'Võ thuật cổ tịch, quyết trương tâm pháp (nỏ pháp)< quyết trương tâm pháp > lại xưng < nỏ pháp >, là < cày dư thừa kỹ > tạo thành bộ phận một trong', '1', '1', '5');
INSERT INTO `congphap` VALUES ('7', 'Trường thương pháp tuyển', '#f9c508', 'Thương / Côn|Thương côn hợp nhất|Tăng sát thương vật lý, mở rộng phạm vi công kích, xác suất kích hoạt liên chiêu|Cương mãnh – liên tục – áp chế đối phương|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy côn pháp Thiếu Lâm làm căn cơ, dung hợp tinh hoa của các gia thương pháp trong thiên hạ, sáng tạo nên hệ võ học lấy thương làm chủ, côn làm phụ. Chiêu thức cương mãnh, biến hóa linh hoạt, thương – côn hợp nhất, công thủ toàn diện, uy lực vượt trội.', '1', '1', '6');
INSERT INTO `congphap` VALUES ('8', 'Thiện đao pháp tuyển', '#f9c508', 'Cày dư thừa kỹ một trong, khí danh sách dao, dùng hai tay dùng một đao cũng. Kỳ kỹ tự tiện uy nô. Nung tinh kiên, chế độ nhẹ lợi, cái bia vỏ (kiếm, đao) những vật này, các các như pháp, không phải hắn phương chi dao có thể đồng thời. Lại thiện mài cả, ', '1', '1', '7');
INSERT INTO `congphap` VALUES ('9', 'Triêm y thập bát điệt', '#f9c508', 'Dính áo mười tám ngã cũng gọi là Ngã quyền, Cầm ngã quyền, tục xưng Ngã pháp, Nhanh đến ngã pháp hoặc Ngã xuống pháp, là một loại rất chú trọng thực dụng, dùng thiện thức luyện tập làm chủ, đồng thời phú dùng Văn, Võ hai loại phương pháp là tu luyện hình ', '1', '1', '8');
INSERT INTO `congphap` VALUES ('10', 'Dịch cân kinh', '#f9c508', 'Hợp tác < dịch cân kinh > vật lộn thuật đồng thời luyện tập Kodak đến vô kiên bất tồi thần công uy lực', '1', '1', '9');
INSERT INTO `congphap` VALUES ('11', 'Cửu âm chân kinh', '#f9c508', 'Âm cực ở sáu, dùng cái gì nói chín. Thái cực sinh lưỡng nghi, thiên địa sơ đào phán. Sáu âm đã cực, gặp mặt bảy quy nguyên quá làm, quá làm phương Tây kim đức, âm chi ngây thơ, lạnh chi nguồn gốc.', '1', '1', '10');
INSERT INTO `congphap` VALUES ('12', 'Canh dư thặng kỹ', '#f9c508', '< cày dư thừa kỹ > dừng tại thực dụng, giản lược hình tượng, cho nên trình hầu dân tự trong hủ là Là sách vừa ra, dùng chế khí người còn dùng, dùng dùng biến người còn nói . Trình thị tộc người cũng nhiều tập võ, từng dùng bộ đội con em hiệp trợ phòng giữ', '1', '1', '11');

-- ----------------------------
-- Table structure for `copy_of_addim`
-- ----------------------------
DROP TABLE IF EXISTS `copy_of_addim`;
CREATE TABLE `copy_of_addim` (
  `imuid` int(11) NOT NULL,
  `sid` text NOT NULL,
  `id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`(10)) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of copy_of_addim
-- ----------------------------
INSERT INTO `copy_of_addim` VALUES ('422', '111111', '1');
INSERT INTO `copy_of_addim` VALUES ('0', '771086b453fb6d53d76bf46880cf4b41', '2');

-- ----------------------------
-- Table structure for `dlip`
-- ----------------------------
DROP TABLE IF EXISTS `dlip`;
CREATE TABLE `dlip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(255) NOT NULL DEFAULT '',
  `dlip` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='登陆ip';

-- ----------------------------
-- Records of dlip
-- ----------------------------

-- ----------------------------
-- Table structure for `duihuan`
-- ----------------------------
DROP TABLE IF EXISTS `duihuan`;
CREATE TABLE `duihuan` (
  `dhm` varchar(255) NOT NULL,
  `dhzb` varchar(255) DEFAULT NULL,
  `dhdj` varchar(255) DEFAULT NULL,
  `dhyp` varchar(255) DEFAULT NULL,
  `dhyxb` int(11) NOT NULL,
  `dhczb` int(11) NOT NULL,
  `dhname` varchar(255) DEFAULT NULL,
  `dhexp` int(11) NOT NULL,
  `dhid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`dhid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of duihuan
-- ----------------------------
INSERT INTO `duihuan` VALUES ('vip666sss', '67,69', '0', '0', '0', '8888', 'Gói quà tặng Ma Linh beta', '0', '1');
INSERT INTO `duihuan` VALUES ('vip666888', '33', '1|100', '9|100', '0', '0', 'Gói nâng cấp dành cho người mới', '8888', '2');
INSERT INTO `duihuan` VALUES ('vip666', '53,54,55,56,57,58,59', '0', '0', '0', '0', 'Thiết bị mã kiểm tra nội bộ', '0', '3');
INSERT INTO `duihuan` VALUES ('vip888', '60,61,62,63,64,65,66,67,68', '0', '0', '0', '0', 'Thiết bị mã kiểm tra nội bộ', '0', '5');
INSERT INTO `duihuan` VALUES ('vip999', '53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68', '0', '0', '0', '0', 'Thiết bị mã kiểm tra nội bộ', '0', '6');

-- ----------------------------
-- Table structure for `duihuan_log`
-- ----------------------------
DROP TABLE IF EXISTS `duihuan_log`;
CREATE TABLE `duihuan_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `dhm` varchar(64) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sid_dhm` (`sid`,`dhm`),
  KEY `idx_dhm` (`dhm`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of duihuan_log
-- ----------------------------
INSERT INTO `duihuan_log` VALUES ('1', '8d0329755724609ccb9713953eee47f4', 'vip666sss', '1782801702');
INSERT INTO `duihuan_log` VALUES ('2', '8d0329755724609ccb9713953eee47f4', 'vip666888', '1782802124');
INSERT INTO `duihuan_log` VALUES ('3', '8d0329755724609ccb9713953eee47f4', 'vip666', '1783530296');
INSERT INTO `duihuan_log` VALUES ('4', '8d0329755724609ccb9713953eee47f4', 'vip888', '1783530303');
INSERT INTO `duihuan_log` VALUES ('5', '8d0329755724609ccb9713953eee47f4', 'vip999', '1783530311');

-- ----------------------------
-- Table structure for `exchange_log`
-- ----------------------------
DROP TABLE IF EXISTS `exchange_log`;
CREATE TABLE `exchange_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(50) NOT NULL,
  `mt_amount` int(11) NOT NULL,
  `lt_amount` int(11) NOT NULL,
  `exchange_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid_date` (`sid`,`exchange_date`),
  KEY `idx_exchange_date` (`exchange_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of exchange_log
-- ----------------------------

-- ----------------------------
-- Table structure for `exp`
-- ----------------------------
DROP TABLE IF EXISTS `exp`;
CREATE TABLE `exp` (
  `ulv` text CHARACTER SET utf8mb4 NOT NULL,
  `uexp` text CHARACTER SET utf8mb4 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=gb2312 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of exp
-- ----------------------------
INSERT INTO `exp` VALUES ('', '');
INSERT INTO `exp` VALUES ('', '');

-- ----------------------------
-- Table structure for `fangshi_dj`
-- ----------------------------
DROP TABLE IF EXISTS `fangshi_dj`;
CREATE TABLE `fangshi_dj` (
  `djid` int(11) NOT NULL,
  `djcount` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `pay` int(6) NOT NULL DEFAULT '0',
  `payid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `djname` varchar(255) NOT NULL,
  `djinfo` varchar(255) NOT NULL DEFAULT '',
  `djzl` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`payid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of fangshi_dj
-- ----------------------------

-- ----------------------------
-- Table structure for `fangshi_sd`
-- ----------------------------
DROP TABLE IF EXISTS `fangshi_sd`;
CREATE TABLE `fangshi_sd` (
  `zbnowid` int(11) NOT NULL,
  `zbname` varchar(255) NOT NULL,
  `qianghua` int(11) NOT NULL,
  `pay` int(9) NOT NULL DEFAULT '0',
  `payid` int(11) NOT NULL AUTO_INCREMENT,
  `zbinfo` varchar(255) NOT NULL,
  `zbgj` int(11) NOT NULL,
  `zbfy` int(11) NOT NULL,
  `zbbj` int(11) NOT NULL,
  `zbxx` int(11) NOT NULL,
  `zbid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `zbhp` int(11) NOT NULL,
  `zblv` int(11) NOT NULL,
  `uname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`payid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of fangshi_sd
-- ----------------------------

-- ----------------------------
-- Table structure for `fangshi_zb`
-- ----------------------------
DROP TABLE IF EXISTS `fangshi_zb`;
CREATE TABLE `fangshi_zb` (
  `zbnowid` int(11) NOT NULL,
  `zbname` varchar(255) NOT NULL,
  `qianghua` int(11) NOT NULL,
  `pay` int(9) NOT NULL DEFAULT '0',
  `payid` int(11) NOT NULL AUTO_INCREMENT,
  `zbinfo` varchar(255) NOT NULL,
  `zbgj` int(11) NOT NULL,
  `zbfy` int(11) NOT NULL,
  `zbbj` int(11) NOT NULL,
  `zbxx` int(11) NOT NULL,
  `tuposjs` int(11) DEFAULT '0',
  `tupoexp` int(11) DEFAULT '0',
  `zbid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `zbhp` int(11) NOT NULL,
  `zblv` int(11) NOT NULL,
  `uname` varchar(255) DEFAULT NULL,
  `zbys` varchar(255) NOT NULL DEFAULT '#9EF18D',
  `element` varchar(24) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`payid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=219 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of fangshi_zb
-- ----------------------------

-- ----------------------------
-- Table structure for `fb`
-- ----------------------------
DROP TABLE IF EXISTS `fb`;
CREATE TABLE `fb` (
  `fbname` varchar(255) NOT NULL,
  `fbid` int(11) NOT NULL,
  `fbinfo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`fbid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of fb
-- ----------------------------
INSERT INTO `fb` VALUES ('Lão Vương Thôn', '11', 'Phó bản');

-- ----------------------------
-- Table structure for `fbmid`
-- ----------------------------
DROP TABLE IF EXISTS `fbmid`;
CREATE TABLE `fbmid` (
  `fmname` varchar(255) NOT NULL,
  `fmid` int(11) NOT NULL,
  `fminfo` varchar(255) NOT NULL,
  `fmnpc` varchar(255) NOT NULL,
  `fmgw` varchar(255) NOT NULL,
  PRIMARY KEY (`fmid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of fbmid
-- ----------------------------
INSERT INTO `fbmid` VALUES ('Trình tự cẩu', '255', 'Niệu sàng cẩu', '11', '11');

-- ----------------------------
-- Table structure for `game1`
-- ----------------------------
DROP TABLE IF EXISTS `game1`;
CREATE TABLE `game1` (
  `uid` int(6) NOT NULL AUTO_INCREMENT COMMENT '游戏ID',
  `sid` text CHARACTER SET utf8 NOT NULL COMMENT '数据库ID',
  `token` text CHARACTER SET utf8 NOT NULL,
  `uname` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `shenfen` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '1',
  `ulv` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '初等级',
  `uyxb` int(11) NOT NULL DEFAULT '2000' COMMENT '初始钱',
  `uczb` int(11) NOT NULL DEFAULT '100' COMMENT '高级钱',
  `uexp` int(11) NOT NULL DEFAULT '0' COMMENT '经验',
  `vip` int(11) NOT NULL DEFAULT '0' COMMENT 'VIP等级',
  `uhp` int(11) NOT NULL DEFAULT '35' COMMENT 'HP',
  `umaxhp` int(11) NOT NULL DEFAULT '35' COMMENT '最高血量',
  `ustamina` int(11) NOT NULL DEFAULT '100',
  `umaxstamina` int(11) NOT NULL DEFAULT '100',
  `ugj` int(11) NOT NULL DEFAULT '12' COMMENT '攻击力',
  `ufy` int(11) NOT NULL DEFAULT '5' COMMENT '防御',
  `usex` int(11) NOT NULL DEFAULT '1' COMMENT '男女',
  `endtime` datetime NOT NULL,
  `nowmid` int(11) NOT NULL DEFAULT '225',
  `uwx` int(11) NOT NULL DEFAULT '0',
  `nowguaiwu` int(11) NOT NULL,
  `tool1` int(11) NOT NULL,
  `tool2` int(11) NOT NULL,
  `tool3` int(11) NOT NULL,
  `tool4` int(11) NOT NULL,
  `tool5` int(11) NOT NULL,
  `tool6` int(11) NOT NULL,
  `tool7` int(11) NOT NULL DEFAULT '0',
  `tool8` int(11) NOT NULL,
  `tool9` int(11) NOT NULL,
  `tool10` int(11) NOT NULL,
  `tool11` int(11) NOT NULL,
  `tool12` int(11) NOT NULL,
  `tool13` int(11) NOT NULL,
  `tool14` int(11) NOT NULL,
  `tool15` int(11) NOT NULL,
  `tool16` int(11) NOT NULL,
  `ubj` int(11) NOT NULL DEFAULT '0',
  `uxx` int(11) NOT NULL DEFAULT '0',
  `sfzx` int(11) NOT NULL DEFAULT '0',
  `qandaotime` datetime DEFAULT NULL,
  `xiuliantime` datetime DEFAULT '0000-00-00 00:00:00',
  `sfxl` int(11) NOT NULL DEFAULT '0',
  `yp1` int(11) NOT NULL,
  `yp2` int(11) NOT NULL,
  `yp3` int(11) NOT NULL,
  `cw` int(11) NOT NULL,
  `jn1` int(11) NOT NULL,
  `jn2` int(11) NOT NULL,
  `jn3` int(11) NOT NULL,
  `ispvp` int(11) NOT NULL DEFAULT '0',
  `nn` int(11) NOT NULL DEFAULT '0',
  `dhvip` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `dhvip1` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tf` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfgj` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfxy` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfsb` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfxx` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tffy` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfhp` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `tfbj` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `wugong` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `congphap` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `ljczczb` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `ljczyxb` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `taozhuang` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '套装',
  `linhcan_id` int(11) DEFAULT '0',
  `linhcan_quality` int(1) NOT NULL DEFAULT '1',
  `thechat_id` int(11) DEFAULT '0',
  `thechat_quality` int(11) DEFAULT '1',
  `thechat_awaken_stage` tinyint(4) DEFAULT '0',
  `huyetmach_id` int(11) DEFAULT '0',
  `huyetmach_quality` int(11) DEFAULT '1',
  `huyetmach_awaken_stage` tinyint(4) DEFAULT '0',
  `huyetmach_transform_cd` datetime DEFAULT NULL,
  `stamina_time` datetime DEFAULT NULL,
  `tianfu_id` tinyint(4) NOT NULL DEFAULT '0',
  `tianfu_quality` tinyint(4) NOT NULL DEFAULT '0',
  `tianfu_lv` int(11) NOT NULL DEFAULT '0',
  `tianfu_power` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=990146 DEFAULT CHARSET=gb2312 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of game1
-- ----------------------------
INSERT INTO `game1` VALUES ('543', 'c8c35537f4a2bfa618801654b9cd1fc5', 'a98fce18aa73bc9370a7b4c9e20bea16', '艾尔资源', '1', '1000', '100134199', '100001491', '239741186', '0', '109004611', '100000034', '66', '100', '100000021', '100000004', '1', '2026-10-14 10:54:44', '296', '0', '0', '77174', '77194', '77191', '77177', '77203', '77180', '77206', '77183', '77186', '77189', '77197', '77201', '77215', '77210', '77213', '0', '0', '0', '1', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '16', '1', '1', '3', '1', '1', '3', '1', null, '2026-10-03 22:26:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('544', 'fb36ea13a56ad413d9cccebca56bb546', '84043e67a4daec1362701e388c0d8313', 'Tiên Nhân', '3', '9', '121192', '555575', '3423000', '0', '338', '338', '100', '100', '66', '73', '1', '2026-10-14 10:54:44', '243', '16', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', null, '2024-08-31 00:42:37', '1', '0', '0', '0', '525', '0', '0', '0', '0', '0', '0', '1', '64', '0', '0', '0', '0', '0', '0', '0', '3', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('545', 'dc4996fe7860595b1bb92db3c2af9fab', 'd5bb281d7d6286a2a1a57e94bc289412', 'LouLxGM', '1', '2', '79024', '869', '62', '0', '70', '70', '100', '100', '17', '12', '2', '2026-10-14 10:54:44', '226', '2', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', null, '2024-08-30 18:36:02', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '7', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('546', '4c149ae65f7dab8474cd47dbb3b17677', '3c92b5e0037652f194e9c2a364222def', '1231231', '1', '1', '73120', '804', '0', '0', '35', '35', '100', '100', '12', '5', '1', '2026-10-14 10:54:44', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('547', '8d0329755724609ccb9713953eee47f4', 'bc338cd96ee638e68be2a3ffedab7431', 'PhátĐây', '1', '93', '542559', '9519', '343081', '0', '35290', '34964', '100', '100', '7153', '4898', '1', '2026-10-27 19:16:57', '225', '544', '0', '0', '0', '0', '0', '0', '77299', '0', '77300', '0', '0', '0', '0', '0', '0', '0', '0', '7', '9', '1', null, '2026-10-27 18:50:44', '0', '9', '9', '9', '0', '0', '0', '0', '0', '0', '1', '1', '518', '0', '0', '0', '0', '0', '0', '0', '6', '', '0', '0', '', '1', '1', '1', '1', '0', '0', '1', '0', null, '2026-10-04 01:28:14', '4', '4', '24', '792');
INSERT INTO `game1` VALUES ('548', '40e5df6609a8fd1b15904b9aa798a877', 'd541d7e457db6729a0bdcd1b7b421a4e', 'tttttt', '2', '12', '95287', '1044', '12082', '0', '35', '417', '100', '100', '60', '56', '1', '2026-10-14 10:54:44', '228', '22', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '77', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('549', '7841cebe57141c476b1d1d2272b65e53', '949fc0cbcd7915802f0c5e6402c5b9cd', '444444', '3', '1', '40316', '477', '0', '0', '35', '35', '100', '100', '12', '5', '1', '2026-10-14 10:54:44', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-01-09 01:33:24', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('550', '1b3246e8f45e4276967d00ad79cab118', '8b00b2b80ad7887e7b5aaf90f7a03652', 'CổĐế', '3', '1', '54600', '626', '0', '0', '35', '35', '95', '100', '12', '5', '1', '2026-10-14 10:54:44', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '6', '1', '0', '1', '0', '0', '1', '0', null, '2026-08-04 20:21:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('551', '378bf7fdd60f2d81207e58ad6da4aaca', '8722a6c0cf610640fade4758ca9a4ebe', 'TônGiả', '1', '1', '54200', '614', '0', '0', '935', '35', '95', '100', '12', '5', '1', '2026-10-14 10:54:44', '299', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '4', '1', '1', '6', '0', '4', '1', '0', null, '2026-10-03 22:06:51', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('552', '6c4666ee50d00945c0251d14ca2275f2', '28a78cf8113ac99ff2595d55aad2ec14', 'TT173659', '1', '1', '63100', '711', '0', '0', '1235', '35', '100', '100', '12', '5', '1', '2026-10-14 10:54:44', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '0', '0', '6', '1', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('987650', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'e2etest', 'E2E Test', '1', '1000', '5004200', '500042', '100000', '10', '35', '35', '80', '100', '12', '5', '1', '2026-10-14 17:08:12', '225', '0', '0', '77563', '77569', '77568', '77564', '77572', '77565', '77573', '77562', '77566', '77567', '77570', '77571', '77576', '77574', '77575', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '500', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '1', '5', '0', '1', '0', '0', '1', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990113', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '', 'LCPASSIVE', '1', '30', '105000', '150', '10000', '0', '5000', '5000', '100', '100', '3000', '1000', '1', '2026-10-14 16:07:07', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '20', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '1', '0', '1', '0', '0', '1', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990125', '2b06d0ff01b346e5f2a0666ab83dc044', '2492b79553ecb631836d193230172d01', 'QATuChan', '1', '200', '598470', '172', '1000', '0', '30000', '30000', '100', '100', '500', '5', '1', '2026-10-14 10:54:44', '225', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '20', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '0', '18', '3', '1', '10', '3', '3', '2026-10-11 05:40:32', null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990136', '03bb973718e9c9e68ad90317c5efdf68', 'simtoken001', 'XinChao1', '2', '1', '4800', '128', '0', '0', '120101', '578995', '100', '100', '453', '441', '1', '2026-10-16 10:46:59', '228', '212', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, null, '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1260', '10', '10', '0', '95', '95', '10', '10', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 10:43:14', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990140', 'f91190a62d0f3267f85d972e4ba496d3', '7c6bd4b38711cc768b03f88ae33bbfb0', 'NhatKy01', '2', '16', '242', '100', '54798', '0', '303', '674', '100', '100', '76', '72', '1', '2026-10-16 10:33:01', '228', '30', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 09:32:31', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '75', '0', '0', '0', '15', '15', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 00:32:31', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990141', '062aefa38dfc651e9590f98b7f7363a8', '8326ea540a60936bb47864afe233c2b2', 'DoLuc7813', '2', '16', '61', '100', '59280', '0', '486', '710', '95', '100', '76', '72', '1', '2026-10-16 10:37:16', '228', '30', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 09:37:15', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '75', '0', '0', '0', '15', '15', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990142', 'f3829669e2fdc04aaf5c9725725fe022', '7ea63def11f6c2091340ee54aa7ebacd', 'DoNV42714', '2', '1', '2000', '100', '0', '0', '34', '35', '95', '100', '12', '5', '1', '2026-10-16 10:39:23', '228', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990143', 'a49a6c4337615603acc2f3902e48e280', 'ea86c35a21886c419ea78762d131b8a9', 'SonTri244', '2', '1', '2112', '100', '108', '0', '35', '35', '95', '100', '12', '5', '1', '2026-10-16 10:42:23', '228', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, null, '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990144', '6b7766a40825443262489376feafd063', 'a5d974fb180341d7d93bc85348974cca', 'NhatKyC1', '2', '2', '2095', '100', '104', '0', '68', '69', '7', '100', '15', '9', '1', '2026-10-16 16:47:02', '228', '2', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', null, '2026-10-16 16:14:54', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '5', '0', '0', '0', '1', '1', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 16:44:54', '0', '0', '0', '0');
INSERT INTO `game1` VALUES ('990145', 'f6858a418360c29a5a4d5c2f1b622228', '6d671d1dbc2712c31b3a29f8f16f3ed7', 'TuLuyen213', '2', '12', '523', '100', '0', '0', '35', '417', '100', '100', '60', '56', '1', '2026-10-16 16:49:16', '225', '22', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', null, '2026-10-16 15:49:16', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '55', '0', '0', '0', '11', '11', '0', '0', '0', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', null, null, '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `gameconfig`
-- ----------------------------
DROP TABLE IF EXISTS `gameconfig`;
CREATE TABLE `gameconfig` (
  `firstmid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=FIXED;

-- ----------------------------
-- Records of gameconfig
-- ----------------------------
INSERT INTO `gameconfig` VALUES ('225');

-- ----------------------------
-- Table structure for `getplayerinfo`
-- ----------------------------
DROP TABLE IF EXISTS `getplayerinfo`;
CREATE TABLE `getplayerinfo` (
  `imuid` int(11) DEFAULT NULL,
  `sid` text,
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of getplayerinfo
-- ----------------------------
INSERT INTO `getplayerinfo` VALUES ('425', '771086b453fb6d53d76bf46880cf4b41', '1');
INSERT INTO `getplayerinfo` VALUES ('422', '111111', '2');
INSERT INTO `getplayerinfo` VALUES ('0', '771086b453fb6d53d76bf46880cf4b41', '3');

-- ----------------------------
-- Table structure for `ggliaotian`
-- ----------------------------
DROP TABLE IF EXISTS `ggliaotian`;
CREATE TABLE `ggliaotian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `msg` longtext NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` text NOT NULL,
  `zbqk` varchar(11) NOT NULL DEFAULT '' COMMENT '装备id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=229 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of ggliaotian
-- ----------------------------
INSERT INTO `ggliaotian` VALUES ('1', '【Phụ】', '\n					[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Boss·Ma Thú] Nhận được <a href=\'?cmd=zbinfo_sys&zbid=57&sid=8d0329755724609ccb9713953eee47f4\' style=\"background-color: #f6f7f7;padding: 0px 0px;\"><font style=\"color:#09e0b0\">[Bạo Vũ Phệ Hồn Liên]</font></a>', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('2', 'PhátĐây', 'hi', '547', '', '');
INSERT INTO `ggliaotian` VALUES ('3', '【Hệ thống】', 'Chào mừng CổĐế bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('4', '【Hệ thống】', 'Chào mừng TônGiả bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('5', '【Hệ Thống】', '[PhátĐây] đã tiêu diệt Boss Tông Môn!', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('6', '【Hệ Thống】', '[PhátĐây] đã tiêu diệt Boss Tông Môn!', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('7', '【Hệ Thống】', '[PhátĐây] đã tiêu diệt Boss Tông Môn!', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('8', '【Hệ Thống】', '[PhátĐây] đã tiêu diệt Boss Tông Môn! Tông môn nhận +550 EXP', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('9', '【Hệ Thống】', '[PhátĐây] đã tiêu diệt Boss Tông Môn! Tông môn nhận +550 EXP', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('10', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Triết Nhân Phong] nhận được [Thien Lam Tinh Kiem] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('11', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Sơn Trư] nhận được [Thien Huyet Diem Kiem] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('12', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('13', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('14', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('15', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('16', '【Mới nhất】', 'Đoạt BOSS, [] sắp tiêu diệt , sẽ rơi xuống bảo vật gì đây?', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('17', '【Hệ Thống】', '\n					[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Boss·Ma Thú] Nhận được <a href=\'?cmd=zbinfo_sys&zbid=53&sid=8d0329755724609ccb9713953eee47f4\' style=\"background-color: #f6f7f7;padding: 0px 0px;\"><font style=\"color:#2196F3\">[Thanh Thuy Co Ngoc]</font></a> làm cho nhiều tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('18', '【Mới nhất】', 'Có người phát hiện [] đang đánh ，hãy cùng chờ xem ai sẽ chiến thắng!', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('19', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('20', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('21', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('22', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('23', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('24', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('25', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('26', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('27', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('28', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('29', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('30', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('31', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('32', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('33', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('34', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('35', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('36', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('37', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('38', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('39', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('40', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('41', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('42', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('43', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('44', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('45', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('46', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('47', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('48', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('49', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('50', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('51', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('52', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('53', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('54', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('55', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('56', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('57', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('58', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('59', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('60', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('61', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('62', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('63', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('64', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('65', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('66', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('67', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('68', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('69', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('70', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('71', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('72', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('73', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('74', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('75', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('76', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('77', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('78', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('79', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('80', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('81', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('82', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('83', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('84', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('85', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('86', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('87', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('88', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('89', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('90', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('91', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('92', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('93', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('94', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('95', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('96', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('97', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('98', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('99', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('100', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('101', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('102', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('103', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('104', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('105', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('106', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('107', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('108', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('109', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('110', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('111', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('112', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('113', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('114', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('115', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('116', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('117', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('118', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('119', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('120', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('121', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('122', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('123', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('124', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('125', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('126', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('127', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('128', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('129', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('130', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('131', '【Hệ Thống】', '[] chọc ghẹo []...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('132', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('133', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('134', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('135', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('136', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('137', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('138', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('139', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('140', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('141', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('142', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('143', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('144', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('145', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('146', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('147', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('148', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('149', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('150', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('151', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('152', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('153', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('154', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('155', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('156', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('157', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('158', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('159', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('160', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('161', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('162', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('163', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('164', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('165', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('166', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('167', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('168', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('169', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('170', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('171', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('172', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('173', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('174', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('175', '【Hệ Thống】', '[Boss·Thiên Linh Lục Nhĩ] chọc ghẹo [PhátĐây]...', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('176', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('177', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('178', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('179', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('180', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Thien Thuy Linh Phu] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('181', '【Hệ thống】', 'Chào mừng TT173659 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('182', '【Tin đồn】', ' PhátĐây đem TônGiả đánh cho sinh hoạt không thể tự lo liệu', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('183', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Phàm Mộc Thiên Cô Hàn Vân] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('184', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Phàm Mộc Thiên Cô Hàn Vân] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('185', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Phàm Hỏa Thôn Trường Lệnh Bài] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('186', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Thị Huyết Dã Lang] nhận được [Phàm Thổ Hoàng Kiếm] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('187', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Linh Hỏa Chiêm Điểm Giáp] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('188', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Linh Hỏa Chiêm Điểm Giáp] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('189', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Linh Hỏa Chiêm Điểm Giáp] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('190', '【Phụ】', '[艾尔资源]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Cuồng Bạo Dã Lang] nhận được [Linh Kim Hộ Thân Giáp] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('191', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Kim Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('192', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Hỏa Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('193', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Thủy Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('194', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Kim Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('195', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Hỏa Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('196', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Kim Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('197', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Mộc Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('198', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Thủy Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('199', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Mộc Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('200', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Thổ Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('201', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Hỏa Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('202', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Mộc Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('203', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Kim Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('204', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Hỏa Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('205', '【Phụ】', '[PhátĐây]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Dao Dao Thảo] nhận được [Thiên Thủy Ngọc Nhãn] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('206', '【Hệ thống】', 'Chào mừng testnotrait1 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('207', '【Hệ thống】', 'Chào mừng testbai1 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('208', '【Hệ thống】', 'Chào mừng testbai2 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('209', '【Hệ thống】', 'Chào mừng testbai3 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('212', '【Hệ thống】', 'Chào mừng QATuChan bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('213', '【Hệ Thống】', 'QATuChan đã hạ Boss mạch huyết [Hỗn Độn Thạch]', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('214', '【Hệ Thống】', 'QATuChan đã hạ Boss mạch huyết [Hỗn Độn Thú]', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('215', '【Hệ Thống】', 'QATuChan đã hạ Boss mạch huyết [Hỗn Độn Thú]', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('216', '【Hệ Thống】', 'QATuChan đã hạ Boss mạch huyết [Hỗn Độn Thú]', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('217', '【Hệ Thống】', 'QATuChan đã hạ Boss mạch huyết [Hỗn Độn Thú]', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('218', '【Phụ】', '[TFGATE]\n<IMG width=\'15\' height=\'15\' src=\'./images/jisha.png\' >\n[Hồ Yêu] nhận được [Linh Mặc Ảnh Kiếm] làm cho mấy tiện nhân hâm mộ.', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('219', '【Hệ thống】', 'Chào mừng XinChao1 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('220', '【Hệ thống】', 'Chào mừng NhatKy01 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('221', '【Hệ thống】', 'Chào mừng NhatKy01 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('222', '【Hệ thống】', 'Chào mừng NhatKy01 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('223', '【Hệ thống】', 'Chào mừng NhatKy01 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('224', '【Hệ thống】', 'Chào mừng DoLuc7813 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('225', '【Hệ thống】', 'Chào mừng DoNV42714 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('226', '【Hệ thống】', 'Chào mừng SonTri244 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('227', '【Hệ thống】', 'Chào mừng NhatKyC1 bước lên tiên đồ', '0', '', '');
INSERT INTO `ggliaotian` VALUES ('228', '【Hệ thống】', 'Chào mừng TuLuyen213 bước lên tiên đồ', '0', '', '');

-- ----------------------------
-- Table structure for `guanli`
-- ----------------------------
DROP TABLE IF EXISTS `guanli`;
CREATE TABLE `guanli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zyinfo` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业介绍',
  `zyname` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业名称',
  `sid` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业玩家',
  `zygj` varchar(255) NOT NULL DEFAULT '0',
  `zyfy` varchar(255) NOT NULL DEFAULT '0',
  `zybj` varchar(255) NOT NULL DEFAULT '0',
  `zyxy` varchar(255) NOT NULL DEFAULT '0',
  `zyxx` varchar(255) NOT NULL DEFAULT '0',
  `zysb` varchar(255) NOT NULL DEFAULT '0',
  `zyhp` varchar(255) NOT NULL DEFAULT '0',
  `zygn` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业功能，index.php',
  `zyyc` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业隐藏0开1隐藏',
  `zyys` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业颜色',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理或者职业';

-- ----------------------------
-- Records of guanli
-- ----------------------------
INSERT INTO `guanli` VALUES ('1', 'Thích Già Ma Ni Kỳ Hạ Thượng Cấp Giai Thừa', 'Nhất Lưu Phật', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('2', 'Nguyên Thủy Thiên Tôn', 'Nhị Lưu Tiên', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('3', 'Hoàng Cung Quý Tộc', 'Tam Lưu Quân', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('4', 'Quan Liêu', 'Tứ Lưu Quan', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('5', 'Tửu Hán', 'Ngũ Lưu Thiêu', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('6', 'Đương Phô', 'Lục Lưu Đương', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('7', 'Thương Nhân', 'Thất Lưu Thương', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('8', 'Trang Viên Chủ', 'Bát Lưu Khách', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('9', 'Nông Phu', 'Cửu Trang Điền', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `guanli` VALUES ('10', 'Thông Quá Hương Thí Nhân', 'Trung Lưu Cử Tử', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('11', 'Lang Trung, Đại Phu, Dược Phòng Tiên Sinh', 'Trung Lưu Y', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('12', 'Phong Thủy Tiên Sinh Hòa Âm Dương Tiên Sinh', 'Trung Lưu Phong', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('13', 'Toán Mệnh Tiên Sinh', 'Trung Lưu Phê', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('14', 'Mại Họa Nhân', 'Trung Lưu Đan Thanh', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('15', 'Khán Tướng', 'Trung Lưu Tướng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('16', 'Hòa Thượng', 'Trung Lưu Tăng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('17', 'Đạo Sĩ', 'Trung Lưu Đạo', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `guanli` VALUES ('18', 'Hỉ Hoan Cầm Kỳ Thi Thư Họa Văn Nhân', 'Trung Lưu Cầm Kỳ', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('19', 'Họa Phù Niệm Chú Vu Sư', 'Lưu Vu', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('20', 'Ca Kỹ Môn', 'Lưu Xướng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('21', 'Trị Bệnh Thần Vu', 'Đại Thần', 'acea4cf5bfd750233ebc90c1361cd568', '0', '0', '0', '0', '0', '0', '0', 'zhiliao.php', '0', '#379082');
INSERT INTO `guanli` VALUES ('22', 'Vãn Thượng Đả Canh Canh Phu', 'Lưu Bang', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('23', 'Cổ Đại Lý Phát Sư', 'Lưu Thế', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('24', 'Xuy Cổ Thủ, Lạt Bá Tượng', 'Lưu Xuy Thủ', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('25', 'Các Loại Diễn Viên', 'Lưu Hí Tử', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('26', 'Khất Cái', 'Lưu Khiếu Nhai', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('27', 'Đại Nhai Thượng Xuy Đường Mại Đường Nhân', 'Lưu Mại Đường', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `guanli` VALUES ('28', 'Pdd Khảm Nhất Đao? Ức Đao!', 'Tướng Thanh', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#81799f');
INSERT INTO `guanli` VALUES ('29', 'Khảm Giới', 'Khảm Giới', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#81799f');
INSERT INTO `guanli` VALUES ('30', 'Gm Trắc Thí Dụng', 'Trắc Thí', 'a2377a650da6bb0ad4fe0514f971f858', '0', '0', '0', '0', '0', '0', '0', 'zhiliao.php', '0', '#81799f');

-- ----------------------------
-- Table structure for `huyetmach_boss`
-- ----------------------------
DROP TABLE IF EXISTS `huyetmach_boss`;
CREATE TABLE `huyetmach_boss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL DEFAULT '',
  `cfg_key` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `element` varchar(20) NOT NULL,
  `level` int(11) DEFAULT '1',
  `hp` int(11) DEFAULT '0',
  `maxhp` int(11) DEFAULT '0',
  `atk` int(11) DEFAULT '0',
  `def` int(11) DEFAULT '0',
  `crit` int(11) DEFAULT '0',
  `spawned_at` datetime DEFAULT NULL,
  `killed_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`),
  KEY `idx_key` (`cfg_key`)
) ENGINE=MyISAM AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of huyetmach_boss
-- ----------------------------
INSERT INTO `huyetmach_boss` VALUES ('114', '2b06d0ff01b346e5f2a0666ab83dc044', 'hỗn_độ_3', 'Hỗn Độn Thú', 'all', '200', '0', '70400', '4480', '2880', '15', '2026-10-11 04:28:00', '2026-10-11 04:28:01', 'killed');
INSERT INTO `huyetmach_boss` VALUES ('112', '2b06d0ff01b346e5f2a0666ab83dc044', 'hỗn_độ_3', 'Hỗn Độn Thú', 'all', '200', '0', '70400', '4480', '2880', '15', '2026-10-11 04:27:48', '2026-10-11 04:27:58', 'killed');
INSERT INTO `huyetmach_boss` VALUES ('113', '2b06d0ff01b346e5f2a0666ab83dc044', 'hỗn_độ_3', 'Hỗn Độn Thú', 'all', '200', '0', '70400', '4480', '2880', '15', '2026-10-11 04:27:59', '2026-10-11 04:27:59', 'killed');
INSERT INTO `huyetmach_boss` VALUES ('111', '2b06d0ff01b346e5f2a0666ab83dc044', 'hỗn_độ_2', 'Hỗn Độn Thạch', 'all', '150', '0', '33000', '2100', '1350', '15', '2026-10-11 04:25:50', '2026-10-11 04:26:13', 'killed');
INSERT INTO `huyetmach_boss` VALUES ('115', '2b06d0ff01b346e5f2a0666ab83dc044', 'hỗn_độ_3', 'Hỗn Độn Thú', 'all', '200', '0', '70400', '4480', '2880', '15', '2026-10-11 04:28:01', '2026-10-11 04:28:02', 'killed');

-- ----------------------------
-- Table structure for `igm`
-- ----------------------------
DROP TABLE IF EXISTS `igm`;
CREATE TABLE `igm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `glid` varchar(64) NOT NULL DEFAULT '' COMMENT '管理id',
  `uname` varchar(64) NOT NULL DEFAULT '',
  `sid` text NOT NULL,
  `msg` varchar(32) NOT NULL DEFAULT '',
  `shijian` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `huifu` varchar(62) NOT NULL DEFAULT '',
  `glsid` varchar(64) NOT NULL DEFAULT '' COMMENT '管理sid',
  `uid` varchar(32) NOT NULL DEFAULT '',
  `gluid` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='玩家反馈';

-- ----------------------------
-- Records of igm
-- ----------------------------
INSERT INTO `igm` VALUES ('1', '', 'Cá nướng nha', 'b624121f7f5c40f8c6ac507f6cab45f5', 'Trước đây dự định tối nay làm lê', '0000-00-00 00:00:00', 'Tốt nhận được.', 'a2377a650da6bb0ad4fe0514f971f858', '438', '426');
INSERT INTO `igm` VALUES ('2', '', 'Lâu rồi không gặp', '8185f290abecda314a9e53304c84e057', 'Vương đại chùy ở đây', '0000-00-00 00:00:00', 'Tốt nhận được.', 'a2377a650da6bb0ad4fe0514f971f858', '435', '426');
INSERT INTO `igm` VALUES ('3', '', 'Lâu rồi không gặp', '8185f290abecda314a9e53304c84e057', 'Vương đại chùy ở đây', '0000-00-00 00:00:00', 'Tốt nhận được.', 'a2377a650da6bb0ad4fe0514f971f858', '435', '426');
INSERT INTO `igm` VALUES ('4', '', 'Lâu rồi không gặp', '8185f290abecda314a9e53304c84e057', 'Trước đây dự định tối nay làm lê', '0000-00-00 00:00:00', 'Tốt nhận được.', 'a2377a650da6bb0ad4fe0514f971f858', '435', '426');
INSERT INTO `igm` VALUES ('5', '', 'Lâu rồi không gặp', '8185f290abecda314a9e53304c84e057', 'Vương đại chùy ở đây', '0000-00-00 00:00:00', 'Cường cường cường', 'a2377a650da6bb0ad4fe0514f971f858', '435', '426');
INSERT INTO `igm` VALUES ('6', '', 'Giang hồ truyền văn', '111111', 'Vương đại chùy ở đây', '0000-00-00 00:00:00', 'Cường cường cường', 'a2377a650da6bb0ad4fe0514f971f858', '441', '426');
INSERT INTO `igm` VALUES ('7', '', 'Giang hồ truyền văn', '111111', 'Cũng có chỗ tốt siêu ăn ngon', '0000-00-00 00:00:00', 'Tốt nhận được.', 'a2377a650da6bb0ad4fe0514f971f858', '441', '426');
INSERT INTO `igm` VALUES ('8', '', 'Lý thiết đản', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Thiết đản từng du lịch qua đây', '0000-00-00 00:00:00', 'Cường cường cường', '', '510', '426');
INSERT INTO `igm` VALUES ('9', '', 'Lý thiết đản', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Thiết đản đến đây hai du lịch', '0000-00-00 00:00:00', 'Tốt nhận được.', '', '510', '426');
INSERT INTO `igm` VALUES ('10', '', 'Lý thiết đản', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Thiết đản đến đây ba du lịch', '0000-00-00 00:00:00', 'Tốt nhận được.', '', '510', '426');
INSERT INTO `igm` VALUES ('11', '', 'Lý thiết đản', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Thiết đản đến đây ngao du lịch', '0000-00-00 00:00:00', '', '', '510', '426');
INSERT INTO `igm` VALUES ('12', '', 'Quỷ tử', 'd3cd0c8382ef5e102996dfdf2b3a7a48', 'Ha ha', '0000-00-00 00:00:00', '', '', '431', '426');
INSERT INTO `igm` VALUES ('13', '', 'Quỷ tử', 'd3cd0c8382ef5e102996dfdf2b3a7a48', 'Ha ha', '0000-00-00 00:00:00', '', '', '431', '426');
INSERT INTO `igm` VALUES ('14', '', 'Quỷ tử', 'd3cd0c8382ef5e102996dfdf2b3a7a48', 'Không nhốt được', '0000-00-00 00:00:00', '', '', '431', '426');
INSERT INTO `igm` VALUES ('15', '', 'Chương trình cẩu', '087efeb819f49c1789df1f599ec15388', 'Nhanh đến đổi mới', '0000-00-00 00:00:00', '', '', '419', '426');
INSERT INTO `igm` VALUES ('16', '', 'Giác hoàng', 'c1a90c4939ae6e0353f41d09c2b62421', 'Yêu vương kiếm mảnh xoát không đ', '0000-00-00 00:00:00', '', '', '520', '426');
INSERT INTO `igm` VALUES ('17', '', 'Tuần giác con hắn', '454ac9cbdb63df840643655755078a94', 'Hầu gia gia luôn luôn trộm ta li', '0000-00-00 00:00:00', '', '', '521', '426');
INSERT INTO `igm` VALUES ('18', '', 'Thí - giác hoàng', 'c1a90c4939ae6e0353f41d09c2b62421', 'Thêm điểm số ở nghịch thiên cải ', '0000-00-00 00:00:00', '', '', '520', '426');
INSERT INTO `igm` VALUES ('19', '', 'Hầu gia gia', 'acea4cf5bfd750233ebc90c1361cd568', 'Sáo trang kỹ năng', '0000-00-00 00:00:00', '', '', '517', '426');
INSERT INTO `igm` VALUES ('20', '', 'Hầu gia gia', 'acea4cf5bfd750233ebc90c1361cd568', 'Mỗi cái sáo trang có một cái kỹ ', '0000-00-00 00:00:00', '', '', '517', '426');
INSERT INTO `igm` VALUES ('21', '', 'Hầu gia gia', 'acea4cf5bfd750233ebc90c1361cd568', 'Môn phái chiến, một tuần lễ khai', '0000-00-00 00:00:00', '', '', '517', '426');
INSERT INTO `igm` VALUES ('22', '', 'Di hương vận phong', 'be7f41af7f2b397af5129fe5dec95e1a', 'Không đột phá nổi cảnh giới, như', '0000-00-00 00:00:00', '', '', '539', '426');

-- ----------------------------
-- Table structure for `igmxz`
-- ----------------------------
DROP TABLE IF EXISTS `igmxz`;
CREATE TABLE `igmxz` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` text NOT NULL,
  `uname` varchar(32) NOT NULL DEFAULT '',
  `shijian` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `tjcs` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='限制反馈';

-- ----------------------------
-- Records of igmxz
-- ----------------------------
INSERT INTO `igmxz` VALUES ('1', '704825b240505b4599c32a64fb882c2c', '? ? ? ? ? ? ? ? ? ? ? ?', '2022-03-04 23:41:00', '4');
INSERT INTO `igmxz` VALUES ('2', 'a2377a650da6bb0ad4fe0514f971f858', 'GM', '2022-03-05 01:40:00', '4');
INSERT INTO `igmxz` VALUES ('3', 'b624121f7f5c40f8c6ac507f6cab45f5', 'Cá nướng nha', '2022-03-05 22:55:51', '4');
INSERT INTO `igmxz` VALUES ('4', '087efeb819f49c1789df1f599ec15388', 'Chương trình cẩu', '2022-03-12 08:00:07', '2');
INSERT INTO `igmxz` VALUES ('5', '8185f290abecda314a9e53304c84e057', 'Lâu rồi không gặp', '2022-03-05 23:49:36', '4');
INSERT INTO `igmxz` VALUES ('6', '111111', 'Giang hồ truyền văn', '2022-03-06 11:34:25', '2');
INSERT INTO `igmxz` VALUES ('7', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Lý thiết đản', '2022-03-08 11:33:34', '4');
INSERT INTO `igmxz` VALUES ('8', 'd3cd0c8382ef5e102996dfdf2b3a7a48', 'Quỷ tử', '2022-03-08 18:56:55', '3');
INSERT INTO `igmxz` VALUES ('9', 'c1a90c4939ae6e0353f41d09c2b62421', 'Giác hoàng', '2022-03-20 16:17:37', '1');
INSERT INTO `igmxz` VALUES ('10', '454ac9cbdb63df840643655755078a94', 'Tuần giác con hắn', '2022-03-18 15:27:48', '1');
INSERT INTO `igmxz` VALUES ('11', 'acea4cf5bfd750233ebc90c1361cd568', 'Hầu gia gia', '2022-03-21 15:45:04', '2');
INSERT INTO `igmxz` VALUES ('12', 'be7f41af7f2b397af5129fe5dec95e1a', 'Di hương vận phong', '2022-03-22 10:18:23', '1');

-- ----------------------------
-- Table structure for `im`
-- ----------------------------
DROP TABLE IF EXISTS `im`;
CREATE TABLE `im` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imuid` int(11) NOT NULL DEFAULT '0',
  `sid` text NOT NULL,
  `uId` int(11) unsigned NOT NULL,
  `msg` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of im
-- ----------------------------
INSERT INTO `im` VALUES ('1', '426', 'a0f5700982d69976ce54c08cb1c2bbb0', '448', '');
INSERT INTO `im` VALUES ('2', '422', 'a0f5700982d69976ce54c08cb1c2bbb0', '448', '');
INSERT INTO `im` VALUES ('3', '441', 'a0f5700982d69976ce54c08cb1c2bbb0', '448', '');
INSERT INTO `im` VALUES ('4', '431', '70619bdc2bdd92dcc83489657743848d', '427', '');
INSERT INTO `im` VALUES ('5', '448', '70619bdc2bdd92dcc83489657743848d', '427', '');
INSERT INTO `im` VALUES ('6', '426', '70619bdc2bdd92dcc83489657743848d', '427', '');
INSERT INTO `im` VALUES ('7', '427', '111111', '441', '');
INSERT INTO `im` VALUES ('8', '455', '111111', '441', '');
INSERT INTO `im` VALUES ('9', '422', 'e8d7c6fb768be55653f7cb2f7164bd1d', '457', '');
INSERT INTO `im` VALUES ('10', '441', 'e8d7c6fb768be55653f7cb2f7164bd1d', '457', '');
INSERT INTO `im` VALUES ('11', '457', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '431', '');
INSERT INTO `im` VALUES ('12', '441', '39f73d46e13bed758f42d5695f3f43f0', '459', '');
INSERT INTO `im` VALUES ('13', '462', '7083fa1a1d41824363cd4df0ef5e5d90', '463', '');
INSERT INTO `im` VALUES ('14', '422', '111111', '441', '');
INSERT INTO `im` VALUES ('15', '457', '70619bdc2bdd92dcc83489657743848d', '427', '');
INSERT INTO `im` VALUES ('16', '422', '8361f38084bda036106506df54fcd779', '482', '');
INSERT INTO `im` VALUES ('17', '422', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('18', '481', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('19', '457', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('20', '468', 'b624121f7f5c40f8c6ac507f6cab45f5', '438', '');
INSERT INTO `im` VALUES ('21', '438', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('22', '426', '8185f290abecda314a9e53304c84e057', '435', '');
INSERT INTO `im` VALUES ('23', '426', 'b624121f7f5c40f8c6ac507f6cab45f5', '438', '');
INSERT INTO `im` VALUES ('24', '468', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('25', '441', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('26', '426', '111111', '441', '');
INSERT INTO `im` VALUES ('27', '426', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('28', '430', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '431', '');
INSERT INTO `im` VALUES ('29', '512', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '431', '');
INSERT INTO `im` VALUES ('30', '510', 'af976a314459ca05fc3563f0d6fd7e39', '514', '');
INSERT INTO `im` VALUES ('31', '510', 'af976a314459ca05fc3563f0d6fd7e39', '514', '');
INSERT INTO `im` VALUES ('32', '510', '111111', '441', '');
INSERT INTO `im` VALUES ('33', '510', '0d471de3efe5dcf749e6fa60fde8ae76', '515', '');
INSERT INTO `im` VALUES ('34', '422', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('35', '426', 'af976a314459ca05fc3563f0d6fd7e39', '514', '');
INSERT INTO `im` VALUES ('36', '426', 'af976a314459ca05fc3563f0d6fd7e39', '514', '');
INSERT INTO `im` VALUES ('37', '514', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('38', '426', '043c4eac8ffaa70af1ccb3e0eef0a364', '467', '');
INSERT INTO `im` VALUES ('39', '426', '087efeb819f49c1789df1f599ec15388', '419', '');
INSERT INTO `im` VALUES ('40', '515', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('41', '510', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('42', '517', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('43', '517', '771086b453fb6d53d76bf46880cf4b41', '422', '');
INSERT INTO `im` VALUES ('44', '422', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('45', '426', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('46', '427', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('47', '510', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('48', '510', 'd9bc42eda8a25d32618518f4ae68b92a', '518', '');
INSERT INTO `im` VALUES ('49', '518', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('50', '517', 'd9bc42eda8a25d32618518f4ae68b92a', '518', '');
INSERT INTO `im` VALUES ('51', '519', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('52', '517', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('53', '520', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('54', '521', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('56', '422', '454ac9cbdb63df840643655755078a94', '521', '');
INSERT INTO `im` VALUES ('57', '520', '454ac9cbdb63df840643655755078a94', '521', '');
INSERT INTO `im` VALUES ('58', '517', '454ac9cbdb63df840643655755078a94', '521', '');
INSERT INTO `im` VALUES ('59', '426', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('60', '519', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('61', '518', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('62', '523', 'a1cf65d31ba46d90e465db5279329470', '479', '');
INSERT INTO `im` VALUES ('67', '520', '1266218ff94820df0b66c6ef60d0541f', '525', '');
INSERT INTO `im` VALUES ('68', '525', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('69', '522', 'c1a90c4939ae6e0353f41d09c2b62421', '520', '');
INSERT INTO `im` VALUES ('70', '521', '1266218ff94820df0b66c6ef60d0541f', '525', '');
INSERT INTO `im` VALUES ('71', '525', '454ac9cbdb63df840643655755078a94', '521', '');
INSERT INTO `im` VALUES ('72', '517', '0e975728648b4af97a3eb0dfaf7e303d', '522', '');
INSERT INTO `im` VALUES ('73', '522', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('74', '526', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('75', '517', '6dd9bd98618400f882c43aa6444d4948', '526', '');
INSERT INTO `im` VALUES ('76', '529', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('77', '520', '1ac81512a158e2622c17a49ba9b7eaf8', '538', '');
INSERT INTO `im` VALUES ('78', '510', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '');
INSERT INTO `im` VALUES ('79', '517', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '');
INSERT INTO `im` VALUES ('80', '539', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('81', '422', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '');
INSERT INTO `im` VALUES ('82', '521', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '');
INSERT INTO `im` VALUES ('83', '539', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '510', '');
INSERT INTO `im` VALUES ('84', '538', 'acea4cf5bfd750233ebc90c1361cd568', '517', '');
INSERT INTO `im` VALUES ('85', '426', 'be7f41af7f2b397af5129fe5dec95e1a', '539', '');
INSERT INTO `im` VALUES ('86', '517', 'a2377a650da6bb0ad4fe0514f971f858', '426', '');
INSERT INTO `im` VALUES ('87', '543', '8d0329755724609ccb9713953eee47f4', '547', '');

-- ----------------------------
-- Table structure for `imliaotian`
-- ----------------------------
DROP TABLE IF EXISTS `imliaotian`;
CREATE TABLE `imliaotian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `msg` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL,
  `imuid` int(11) NOT NULL,
  `ckqk` varchar(12) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '查看情况默认0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of imliaotian
-- ----------------------------
INSERT INTO `imliaotian` VALUES ('1', 'Không Quên Suy Nghĩ Ban Đầu', 'Còn phải sửa đổi thật nhiều a', '427', '448', '0');
INSERT INTO `imliaotian` VALUES ('5', 'Nha Đản', 'Cạc cạc cạc', '482', '422', '1');
INSERT INTO `imliaotian` VALUES ('15', 'Giang Hồ Truyền Văn', '. . .', '441', '427', '0');
INSERT INTO `imliaotian` VALUES ('18', 'Cá Nướng Nha', 'Vương đại chùy ở đây', '438', '426', '1');
INSERT INTO `imliaotian` VALUES ('20', 'Lâu Rồi Không Gặp', 'Đại ca hảo', '435', '426', '1');
INSERT INTO `imliaotian` VALUES ('24', 'Giang Hồ Truyền Văn', 'Chậc chậc chậc', '441', '426', '1');
INSERT INTO `imliaotian` VALUES ('25', 'Giang Hồ Truyền Văn', '&lt;span style=&quot;background-color: red; color: white; border', '441', '426', '1');
INSERT INTO `imliaotian` VALUES ('28', 'Quỷ Tử', 'Ngươi không có', '431', '430', '0');
INSERT INTO `imliaotian` VALUES ('33', 'Gm', 'Trâu bút', '426', '438', '1');
INSERT INTO `imliaotian` VALUES ('36', 'Я Твой', 'Xin chào a', '515', '510', '1');
INSERT INTO `imliaotian` VALUES ('37', 'Gm', '. . .', '426', '438', '1');
INSERT INTO `imliaotian` VALUES ('38', 'Gm', 'Còn phải sửa đổi thật nhiều a', '426', '438', '1');
INSERT INTO `imliaotian` VALUES ('39', 'Phi·Thiên Linh', 'Huynh đệ xin chào', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('41', 'Lý Thiết Đản', '. . .', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('43', 'Lý Thiết Đản', 'Còn nhớ đi nhận gói quà mã vip666', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('44', 'Lý Thiết Đản', 'vip666888', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('45', 'Phi·Thiên Linh', 'Huyết lượng biến số âm làm sao bây giờ', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('46', 'Phi·Thiên Linh', 'Không cẩn thận bán cái sách', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('47', 'Phi·Thiên Linh', 'Xin hỏi', '517', '426', '1');
INSERT INTO `imliaotian` VALUES ('48', 'Phi·Thiên Linh', 'Duyên phong sáo trang như thế kiếm đủ', '517', '426', '1');
INSERT INTO `imliaotian` VALUES ('49', 'Lý Thiết Đản', 'Oa, ngươi mạnh như vậy sao, ta không có dựng hảo a', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('50', 'Lý Thiết Đản', 'Ngươi mài thạch. . Thế nào nhiều như vậy', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('51', 'Lý Thiết Đản', 'Ngươi đây là vận độngbug a? Ta còn đang ở chuẩn bị đột phá cần l', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('52', 'Phi·Thiên Linh', 'Tạm được, ta nghĩ', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('53', 'Phi·Thiên Linh', 'Thêm WeChat, ta cũng học website lập trìnhyhc13501827004', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('54', 'Lý Thiết Đản', 'PHP. Rất đơn giản. Cơ bản xem xét thì hiểu', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('55', 'Phi·Thiên Linh', 'Ngươi nhìn ta cho ngươi phát thông tin', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('56', 'Lý Thiết Đản', 'Nhìn thấy tăng thêm ha ha ta cái này thật nhiều chưa xem xét', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('58', 'Phi·Thiên Linh', 'pk?', '517', '426', '1');
INSERT INTO `imliaotian` VALUES ('59', 'Gm', 'Không có thời gian không có thời gian có một đại lỗ thủng', '426', '517', '1');
INSERT INTO `imliaotian` VALUES ('60', 'Phi·Thiên Linh', 'Gì lỗ thủng?', '517', '426', '1');
INSERT INTO `imliaotian` VALUES ('61', 'Phi·Thiên Linh', 'hi', '517', '519', '0');
INSERT INTO `imliaotian` VALUES ('62', 'Phi·Thiên Linh', 'Đại ca! Website còn đang ở khai phát, có chức năng không dùng', '517', '519', '0');
INSERT INTO `imliaotian` VALUES ('63', 'Tiểu Giác233', 'Tên ta là giác hoàng', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('64', 'Phi·Thiên Linh', 'Cỏ', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('65', 'Tiểu Giác233', 'Hảo', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('66', 'Tiểu Giác233', 'Tất cả đều tiếp', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('67', 'Phi·Thiên Linh', 'Chậm rãi thăm dò, rất đơn giản', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('68', 'Phi·Thiên Linh', 'Đổi mã: vip666888', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('69', 'Phi·Thiên Linh', 'Đổi mã: vip666', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('70', 'Tiểu Giác233', 'Tìm không thấy hoa quả sơn', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('71', 'Phi·Thiên Linh', 'Xem xét thế giới', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('72', 'Tiểu Giác233', 'Vì thôn trưởng lệnh bài', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('73', 'Phi·Thiên Linh', 'Tiểu giác là đồng học', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('74', 'Tiểu Giác233', 'Khang khang ta tân sủng vật', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('76', 'Tiểu Giác233', 'Thế nào sao nhận được yêu vương kiếm, ta một mảnh cũng không lấy', '520', '422', '0');
INSERT INTO `imliaotian` VALUES ('77', 'Giác Hoàng', 'Cho một cái kim cô bổng a', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('78', 'Giác Hoàng', 'Đi học', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('79', 'Giác Hoàng', 'Toán học cuốn thứ8 đề đáp án là gì', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('80', 'Hầu Gia Gia', 'Tuần giác, lăn ra đến, giải thích một chút, trương chí kiệt mẹ h', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('81', 'Giác Hoàng', 'Được rồi', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('82', 'Giác Hoàng', 'Hắn tìm ngươi?', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('83', 'Hầu Gia Gia', 'Tại đây điên cuồng khiêu khích, mắng ta', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('84', 'Giác Hoàng', 'Cái đó chết cô nhi, không cần phải để ý đến hắn, ta đi tìm cô ấy', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('85', 'Giác Hoàng', 'Ngươi xong rồi, dương muốn đánh ngươi, đi cho hắn nói xin lỗi, b', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('86', 'Hầu Gia Gia', 'Ta trộm hắn 0 thạch, hắn cho ta toàn bộ thông sáng, cạo chết hắn', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('87', 'Giác Hoàng', 'Ta vừa mới trộm hắn rất nhiều đồ ăn vặt, bị hắn đánh tới gần chế', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('88', 'Giác Hoàng', 'Ngươi bán cái gì nha, ta muốn mua', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('89', 'Giác Hoàng', 'Ngươi là hầu tử?', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('90', 'Hầu Gia Gia', 'Đừng nói cho cô ấy đổi mã và bạch chơi phương pháp', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('91', 'Lý Thiết Đản', 'Module tương đối lớn, ngày mai đang lộng, được đi làm', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('92', 'Hầu Gia Gia', 'Hảo, ta vừa mới cũng nhìn một chútphp, chính làhtml, ta cũng sẽ', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('93', 'Giác Hoàng', 'Ngươi là lão Khỉ tử sao? Dương', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('94', 'Giác Hoàng', 'Tốt', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('95', 'Giác Hoàng', 'Chỗ nào đổi tên tự', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('96', 'Đông Hải Long Tôn', 'Hầu tử? Ngươi là nói hầu gia gia sao?', '518', '520', '1');
INSERT INTO `imliaotian` VALUES ('97', 'Giác Hoàng', 'Đúng thế', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('98', 'Đông Hải Long Tôn', 'Ta là hắn tiểu đệ', '518', '520', '1');
INSERT INTO `imliaotian` VALUES ('99', 'Giác Hoàng', 'Ngươi biết chỗ nào đổi tên tự sao? Ta quên', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('100', 'Đông Hải Long Tôn', 'Không biết, ta đi tới đi tới tìm đến, không có nhớ ở nơi nào, ch', '518', '520', '1');
INSERT INTO `imliaotian` VALUES ('101', 'Giác Hoàng', 'Ngươi muốn trang bị a, ta cho ngươi chút ta không muốn', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('102', 'Đông Hải Long Tôn', 'Được, chẳng qua khỉ ca đều đã cho ta một chút', '518', '520', '1');
INSERT INTO `imliaotian` VALUES ('103', 'Giác Hoàng', 'Chờ một chút, ta giết mộtboss', '520', '518', '1');
INSERT INTO `imliaotian` VALUES ('104', 'Hầu Gia Gia', 'Đến hoa quả sơn', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('105', 'Tuần Giác Con Hắn', 'Hầu gia gia luôn luôn trộm ta linh thạch, chút trò chơi trải ngh', '521', '422', '0');
INSERT INTO `imliaotian` VALUES ('106', 'Dương Tiển Bài Hát Cũ', 'Nghề nghiệp của ngươi thế nào sao nhận được nha', '520', '426', '1');
INSERT INTO `imliaotian` VALUES ('107', 'Dương Tiển Bài Hát Cũ', '', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('108', 'Dương Tiển Bài Hát Cũ', '', '520', '426', '1');
INSERT INTO `imliaotian` VALUES ('109', 'Hầu Gia Gia', '', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('110', 'Dương Tiển Bài Hát Cũ', 'Đồng thời trộm trương chí kiệt 0 thạch, hắn vừa có ngươi thì trộ', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('111', 'Dương Tiển Bài Hát Cũ', 'Tốt, hắn trộm ta tốt hơn nhiều', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('113', 'Hầu Gia Gia', 'Nhưng như vậy đúng hay không không tốt lắm nha, ta ngày mai khiế', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('114', 'Hầu Gia Gia', 'Giang hồ truyền văn thế nào làm?', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('115', 'Lý Thiết Đản', 'Chúng ta lẫn nhau trộm linh thạch a', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('117', 'Dương Tiển Bài Hát Cũ', 'Trước kia uống thuốc ăn', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('118', 'Dương Tiển Bài Hát Cũ', 'Trộm ta46 vạn phải không, ngươi xong rồi', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('119', 'Dương Tiển Bài Hát Cũ', 'Còn muốn trộm sao? Giả một phạt trăm', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('120', 'Tuần Giác Con Hắn', 'Xin chào nha', '521', '520', '1');
INSERT INTO `imliaotian` VALUES ('121', 'Dương Tiển Bài Hát Cũ', 'Tiếp tục sao', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('122', 'Dương Tiển Bài Hát Cũ', '', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('123', 'Dương Tiển Bài Hát Cũ', '', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('124', 'Dương Tiển Bài Hát Cũ', 'Không cần, ta bây giờ không có tiền, ta muốn cày quái', '520', '521', '1');
INSERT INTO `imliaotian` VALUES ('125', 'Hầu Gia Gia', '', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('126', 'Dương Tiển Bài Hát Cũ', 'Thêm ta tông môn', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('127', 'Dương Tiển Bài Hát Cũ', 'Có phải ngươi ngốc, ngươi trộm 2, ta thua thiệt 1, hai ta lẫn nh', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('128', 'Hầu Gia Gia', 'Tôi biết nha, ngươi trộm ta một chút30 vạn mất rồi, ta trộm ngươ', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('129', 'Thí - Giác Hoàng', 'Mà nên ta tiền nhiều hơn ngươi rất nhiều lúc, ta trộm1 chụp1, só', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('130', 'Thí - Giác Hoàng', 'Như vậy, ta trộm ngươi, ngươi treo trang bị ta mua', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('131', 'Lý Thiết Đản', 'Ta mệt chết, ta đây chỉ có1 ngàn vạn', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('132', 'Thí - Giác Hoàng', 'Ta cũng không có cái gì trang bị, nếu không ta trộm ngươi, 2-3 ', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('133', 'Hầu Gia Gia', 'Ngươi bây giờ hình như có thể chia sẻ trang bị a', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('134', 'Giác Kho', 'Ta chữa trị một chút', '525', '521', '0');
INSERT INTO `imliaotian` VALUES ('135', 'Hầu Gia Gia', 'A đúng đúng đúng', '517', '522', '1');
INSERT INTO `imliaotian` VALUES ('136', 'Thí - Giác Hoàng', 'Ta là cha ngươi', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('137', 'Rừng Lâm', 'Cho ngươi điểm trang bị, mua một chút', '522', '517', '1');
INSERT INTO `imliaotian` VALUES ('138', 'Rừng Lâm', '', '522', '517', '1');
INSERT INTO `imliaotian` VALUES ('139', 'Hầu Gia Gia', '', '517', '522', '1');
INSERT INTO `imliaotian` VALUES ('140', 'Hầu Gia Gia', 'Chưởng môn có việc sao', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('141', 'Hầu Gia Gia', 'Ừ', '517', '526', '1');
INSERT INTO `imliaotian` VALUES ('142', 'Thí - Giác Hoàng', 'Nhìn thấy ta phát thông tin sao', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('143', 'Hầu Gia Gia', 'Tra hỏi ngươi đâu', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('144', 'Thí - Giác Hoàng', 'Xin chào', '520', '517', '1');
INSERT INTO `imliaotian` VALUES ('145', 'Hầu Gia Gia', 'Lời gì nha', '517', '520', '1');
INSERT INTO `imliaotian` VALUES ('146', 'Hầu Gia Gia', 'Uy uy', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('147', 'Hầu Gia Gia', 'Thế nào?', '517', '539', '1');
INSERT INTO `imliaotian` VALUES ('148', 'Hầu Gia Gia', 'Đột phá đan dùng như thế nào a', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('149', 'Lý Thiết Đản', 'Hai ngày này WeChat không dùng được', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('150', 'Hầu Gia Gia', 'Nhìn xem đấu giá', '517', '510', '1');
INSERT INTO `imliaotian` VALUES ('151', 'Lý Thiết Đản', '', '510', '517', '1');
INSERT INTO `imliaotian` VALUES ('152', 'Di Hương Vận Phong', '', '539', '517', '1');
INSERT INTO `imliaotian` VALUES ('153', 'Hầu Gia Gia', 'Tốt tốt', '517', '539', '1');
INSERT INTO `imliaotian` VALUES ('154', 'Di Hương Vận Phong', 'Ngươi không đi làm?', '539', '517', '1');
INSERT INTO `imliaotian` VALUES ('155', 'Hầu Gia Gia', 'Tăng thêm cái hạ thủ lưu tình, đánhboss lưu một tia máu,', '517', '539', '1');
INSERT INTO `imliaotian` VALUES ('156', 'Hầu Gia Gia', 'Ngươi có đột phá đan sao', '517', '539', '1');
INSERT INTO `imliaotian` VALUES ('157', 'Lý Thiết Đản', 'Có', '510', '426', '1');

-- ----------------------------
-- Table structure for `linhcan_boss`
-- ----------------------------
DROP TABLE IF EXISTS `linhcan_boss`;
CREATE TABLE `linhcan_boss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `element` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int(11) DEFAULT '1',
  `hp` int(11) DEFAULT '0',
  `maxhp` int(11) DEFAULT '0',
  `atk` int(11) DEFAULT '0',
  `def` int(11) DEFAULT '0',
  `crit` int(11) DEFAULT '0',
  `lifesteal` int(11) DEFAULT '0',
  `spawned_at` datetime DEFAULT NULL,
  `killed_at` datetime DEFAULT NULL,
  `killer_sid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of linhcan_boss
-- ----------------------------

-- ----------------------------
-- Table structure for `linhcan_boss_personal`
-- ----------------------------
DROP TABLE IF EXISTS `linhcan_boss_personal`;
CREATE TABLE `linhcan_boss_personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL DEFAULT '',
  `element` varchar(10) NOT NULL,
  `to_stage` tinyint(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` int(11) DEFAULT '1',
  `hp` int(11) DEFAULT '0',
  `maxhp` int(11) DEFAULT '0',
  `atk` int(11) DEFAULT '0',
  `def` int(11) DEFAULT '0',
  `crit` int(11) DEFAULT '0',
  `spawned_at` datetime DEFAULT NULL,
  `killed_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`),
  KEY `idx_elem` (`element`,`to_stage`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of linhcan_boss_personal
-- ----------------------------
INSERT INTO `linhcan_boss_personal` VALUES ('27', '2b06d0ff01b346e5f2a0666ab83dc044', 'hoa', '1', 'Hỏa Phượng Hoàng', '1', '0', '8220', '267', '110', '12', '2026-10-11 03:46:26', '2026-10-11 03:49:10', 'killed');
INSERT INTO `linhcan_boss_personal` VALUES ('28', '2b06d0ff01b346e5f2a0666ab83dc044', 'hoa', '2', 'Hỏa Phượng Hoàng', '1', '0', '8220', '267', '110', '14', '2026-10-11 03:50:14', '2026-10-11 03:50:24', 'killed');

-- ----------------------------
-- Table structure for `mid`
-- ----------------------------
DROP TABLE IF EXISTS `mid`;
CREATE TABLE `mid` (
  `mname` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `mid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `mgid` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `mnpc` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `mgtime` datetime NOT NULL,
  `ms` varchar(4) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `midinfo` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `midboss` int(11) NOT NULL,
  `mup` int(11) NOT NULL,
  `mdown` int(11) NOT NULL,
  `mleft` int(11) NOT NULL,
  `mright` int(11) NOT NULL,
  `mqy` int(11) NOT NULL,
  `playerinfo` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `ispvp` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=369 DEFAULT CHARSET=gb2312 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of mid
-- ----------------------------
INSERT INTO `mid` VALUES ('Thôn Quảng Tràng', '225', '53|2,52|1', '11,17', '2026-10-16 09:44:23', '0', 'Đồng cỏ xanh tươi và lão thôn trưởng nhàn nhã vẫn còn là mùi hương quen thuộc của Chiến binh, chào mừng trở lại!', '0', '0', '0', '0', '226', '14', 'NhatKyC1Hướng Thôn Khẩu [Chiến Đấu]. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thôn Đông', '226', '', '18,10', '2026-06-30 15:55:08', '180', 'Các cô ở phía đông làng đang ăn hạt dưa', '1', '0', '0', '225', '228', '14', 'TônGiảHướng Thôn Khẩu [Chiến Đấu]. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thôn Khẩu [Chiến Đấu]', '228', '55|3,56|4', '9', '2026-10-16 16:47:01', '0', 'Người đến người đi ở cổng thôn, quan viên đặc biệt chú ý.', '0', '0', '0', '226', '229', '14', 'PhátĐâyHướng Sơn Lâm Ngoại Vi. Đi đến', '1');
INSERT INTO `mid` VALUES ('Tiểu Thụ Lâm', '229', '56|2,57|5', '7', '2026-07-08 03:30:37', '0', 'Trong rừng có rất nhiều cây xanh, có vẻ như vợ trưởng làng rất thích nơi này.', '0', '0', '0', '228', '230', '14', 'PhátĐâyHướng Thôn Khẩu [Chiến Đấu]. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thụ Lâm Thâm Xử', '230', '58|4', '', '2026-07-08 02:05:52', '0', 'Sâu trong rừng, quái vật ngày càng nhiều.', '0', '0', '0', '229', '231', '14', 'PhátĐâyHướng Tiểu Thụ Lâm. Đi đến', '0');
INSERT INTO `mid` VALUES ('Sơn Lâm Ngoại Vi', '231', '62|3,61|2', '', '2026-10-03 22:29:00', '0', 'Ở vùng ngoại ô của rừng núi, hình như ở đây có sói', '0', '0', '0', '230', '232', '14', '艾尔资源Hướng Viêm Dương Giao Ngoại. Đi đến', '0');
INSERT INTO `mid` VALUES ('Sơn Trung Hồ Bạc', '232', '63|2,64|5', '', '2024-08-30 23:56:56', '0', 'Hồ núi, nơi sinh sống của chim rồng', '0', '0', '0', '231', '233', '14', 'Phát ĐâyHướng Sơn Lâm Ngoại Vi. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hồ Trung Tiểu Đảo', '233', '65|6', '', '2022-03-21 15:00:25', '0', 'Một hòn đảo nhỏ trong hồ, nơi người oiran thiết lập lãnh thổ của mình', '0', '0', '0', '232', '235', '14', 'Phát ĐâyHướng Sơn Trung Hồ Bạc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Sơn Mạch Đông Xuất Khẩu', '235', '66|3,67|4', '', '2026-01-06 19:41:29', '0', 'Môi trường sống của đại bàng', '0', '0', '0', '233', '236', '14', 'Phát ĐâyHướng Thôn Quảng Tràng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thành Tây Giao', '236', '69|5', '', '2026-01-04 16:13:03', '0', 'Nhà sư bị quỷ ám', '0', '0', '0', '235', '237', '16', 'Phát ĐâyHướng Sơn Mạch Đông Xuất Khẩu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tụ Tiên Thành Tây', '237', '', '13,14', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '236', '238', '16', 'PhátĐâyHướng Tụ Tiên Tây Nhai. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tụ Tiên Tây Nhai', '238', '', '15', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '237', '239', '16', 'PhátĐâyHướng Tụ Tiên Thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tụ Tiên Thành', '239', '', '16,17', '0000-00-00 00:00:00', '0', '', '0', '0', '273', '238', '240', '16', 'PhátĐâyHướng Bộ Lạc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tụ Tiên Đông Nhai', '240', '', '23', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '239', '241', '16', 'Hầu gia gia hướng tụ tiên thành đông. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tụ Tiên Thành Đông', '241', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '240', '242', '16', 'Hầu gia gia hướng thành đông giao. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thành Đông Giao', '242', '70|3,71|2', '', '2022-03-21 15:47:23', '0', '', '0', '0', '0', '241', '243', '16', 'Hầu gia gia hướng ma hóa động quật. Đi đến', '0');
INSERT INTO `mid` VALUES ('Ma Hóa Động Quật', '243', '72|6', '', '2022-03-19 20:17:50', '0', '', '0', '246', '0', '242', '244', '16', 'Hầu gia gia hướng động quật xuất khẩu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Động Quật Xuất Khẩu', '244', '74|5', '', '2022-03-17 16:13:56', '0', '', '0', '0', '0', '243', '245', '17', 'Tiên NhânHướng Ma Hóa Động Quật. Đi đến', '0');
INSERT INTO `mid` VALUES ('Ma Hóa Bình Nguyên', '245', '73|4', '', '2022-03-21 15:51:07', '0', '', '0', '0', '0', '244', '247', '17', 'Tiên NhânHướng Động Quật Xuất Khẩu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Động Quật Thâm Xử', '246', '75|3', '', '2022-03-17 20:32:44', '60', '', '0', '0', '243', '0', '0', '17', 'Hầu gia gia hướng ma hóa động quật. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bộ Lạc Tây Giao', '247', '76|4,77|2', '', '2022-03-21 16:50:06', '0', '', '0', '0', '0', '245', '248', '17', 'Tiên NhânHướng Ma Hóa Bình Nguyên. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bộ Lạc Tây', '248', '', '20', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '247', '249', '17', 'Hầu gia gia hướng bộ lạc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bộ Lạc', '249', '', '15,17,21', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '248', '250', '17', 'PhátĐâyHướng Tiểu Thụ Lâm. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bộ Lạc Đông', '250', '', '19,22', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '249', '251', '17', 'Hầu gia gia hướng bộ lạc đông giao. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bộ Lạc Đông Giao', '251', '78|6', '', '2022-03-10 01:18:00', '0', '', '0', '0', '0', '250', '252', '17', 'Hầu gia gia hướng man hà ngạn biên. Đi đến', '0');
INSERT INTO `mid` VALUES ('Man Hà Ngạn Biên', '252', '78|2,79|4', '', '2022-03-10 01:18:23', '0', '', '0', '0', '0', '251', '253', '17', 'Hầu gia gia hướng độ hà cảng khẩu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Độ Hà Cảng Khẩu', '253', '80|5', '', '2022-03-17 17:18:22', '0', '', '0', '0', '0', '252', '254', '17', 'Hầu gia gia hướng man hà đối ngạn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Man Hà Đối Ngạn', '254', '81|6', '', '2022-03-15 10:36:08', '0', '', '0', '0', '0', '253', '255', '17', 'Hầu gia gia hướng vị tri sơn lĩnh1. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vị Tri Sơn Lĩnh1', '255', '82|7', '', '2022-03-21 17:27:29', '0', '', '0', '0', '0', '254', '256', '18', 'Hầu gia gia hướng vị tri sơn lĩnh2. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vị Tri Sơn Lĩnh2', '256', '83|5', '', '2022-03-22 09:44:25', '30', '', '0', '257', '0', '255', '258', '18', 'Hầu gia gia hướng vô nhân sơn động. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vô Nhân Sơn Động', '257', '84|6', '', '2022-03-22 10:08:54', '15', 'Nghe nói hang động này tràn đầy linh lực, nhưng là quái vật dường như rất mạnh', '0', '0', '256', '0', '0', '18', 'Hầu gia gia hướng ba tiêu lâm. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vị Tri Sơn Lĩnh3', '258', '85|6', '', '2022-03-21 13:52:27', '200', '', '0', '0', '0', '256', '259', '18', 'Hầu gia gia hướng vị tri sơn lĩnh2. Đi đến', '0');
INSERT INTO `mid` VALUES ('Phong Linh Cổ Trấn Tây', '259', '', '15', '2021-12-22 22:45:06', '0', '', '0', '0', '0', '258', '260', '18', 'Hầu gia gia hướng vị tri sơn lĩnh3. Đi đến', '0');
INSERT INTO `mid` VALUES ('Phong Linh Trấn', '260', '', '24', '2021-12-22 22:45:05', '0', '', '0', '0', '0', '259', '261', '18', 'PhátĐâyHướng Phong Linh Cổ Trấn Đông. Đi đến', '0');
INSERT INTO `mid` VALUES ('Phong Linh Cổ Trấn Đông', '261', '', '19', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '260', '262', '18', 'PhátĐâyHướng Yêu Thú Tùng Lâm Ngoại Bộ1. Đi đến', '0');
INSERT INTO `mid` VALUES ('Yêu Thú Tùng Lâm Ngoại Bộ1', '262', '86|3', '', '2026-06-22 14:40:50', '200', '', '0', '0', '0', '261', '263', '18', 'PhátĐâyHướng Thôn Quảng Tràng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Yêu Thú Tùng Lâm Ngoại Bộ2', '263', '87|7', '', '2022-03-21 17:02:51', '0', '', '0', '0', '0', '262', '264', '18', 'Di hương vận phong hướng tùng lâm thâm xử yêu thú sơn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tùng Lâm Thâm Xử Yêu Thú Sơn', '264', '88|5', '', '2022-03-21 17:12:31', '100', '', '0', '0', '0', '263', '265', '18', 'Di hương vận phong hướng vạn yêu sơn đê cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vạn Yêu Sơn Đê Cốc', '265', '89|2,90|2,100|1', '', '2022-03-21 17:15:53', '0', '', '0', '0', '0', '264', '266', '18', 'Di hương vận phong hướng vạn yêu sơn yêu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vạn Yêu Sơn Yêu', '266', '91|3,92|5', '', '2022-03-21 18:01:06', '0', '', '0', '268', '267', '265', '269', '18', 'Di hương vận phong hướng viêm dương giao ngoại. Đi đến', '0');
INSERT INTO `mid` VALUES ('Yêu Vương Điện', '267', '94|3,95|3,99|2', '24', '2026-06-26 19:34:00', '0', '', '0', '266', '0', '0', '0', '20', 'PhátĐâyHướng Thiên Tuyệt Cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vạn Yêu Sơn Đỉnh', '268', '93|7,100|1', '', '2022-03-21 18:52:52', '0', '', '0', '0', '266', '0', '0', '18', 'Di hương vận phong hướng vạn yêu sơn yêu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Viêm Dương Giao Ngoại', '269', '96|4,97|4', '27,29', '2026-07-08 15:42:33', '0', '', '0', '0', '0', '266', '270', '21', 'PhátĐâyHướng Viêm Dương Thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Viêm Dương Tây Giao', '270', '54|5,100|1', '', '2026-08-03 23:33:50', '0', '', '0', '0', '0', '269', '271', '21', 'PhátĐâyHướng Viêm Dương Tây Nhai. Đi đến', '0');
INSERT INTO `mid` VALUES ('Viêm Dương Tây Nhai', '271', '99|4', '29', '2026-08-03 23:33:49', '0', '', '0', '0', '0', '270', '272', '21', 'PhátĐâyHướng Viêm Dương Thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Viêm Dương Thành', '272', '98|3', '24,28,31', '2026-08-05 20:27:18', '0', '', '0', '0', '312', '271', '311', '21', 'PhátĐâyHướng Thôn Khẩu [Chiến Đấu]. Đi đến', '0');
INSERT INTO `mid` VALUES ('Môn Phái Sang Kiến Xử', '273', '', '26', '0000-00-00 00:00:00', '0', '', '0', '239', '0', '0', '0', '16', 'PhátĐâyHướng Tụ Tiên Thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thông Thiên Tiểu Lộ', '274', '100|3', '', '2022-03-21 19:47:23', '0', '', '0', '275', '0', '280', '0', '22', 'GM hướng thông thiên vân thê. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thông Thiên Vân Thê', '275', '', '', '0000-00-00 00:00:00', '0', '', '0', '276', '0', '0', '0', '22', 'Di hương vận phong hướng đăng thiên đài. Đi đến', '0');
INSERT INTO `mid` VALUES ('Đăng Thiên Đài', '276', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '275', '277', '279', '22', 'Di hương vận phong hướng long trì. Đi đến', '0');
INSERT INTO `mid` VALUES ('Quan Vân Tự', '277', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '278', '276', '22', 'Di hương vận phong hướng đăng thiên đài. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vân Thành', '278', '120|1,121|1', '', '2022-03-22 10:37:18', '0', '', '0', '0', '0', '0', '277', '22', 'Di hương vận phong hướng quan vân tự. Đi đến', '0');
INSERT INTO `mid` VALUES ('Long Trì', '279', '123|5', '', '2022-03-22 08:55:27', '0', 'Nghe nói cái này có đột phá đan tồn tại.', '0', '0', '0', '276', '0', '22', 'Di hương vận phong hướng vũ thiên đô. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tam Giới Biên', '280', '', '6', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '281', '274', '22', 'GM hướng thông thiên tiểu lộ. Đi đến', '0');
INSERT INTO `mid` VALUES ('Chiểu Trạch Địa', '281', '119|2', '', '2022-03-21 19:48:14', '0', '', '0', '282', '0', '0', '280', '22', 'GM hướng tam giới biên. Đi đến', '0');
INSERT INTO `mid` VALUES ('Lưu Vực', '282', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '281', '0', '0', '22', 'GM hướng chiểu trạch địa. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vũ Thiên Đô', '283', '', '6', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '284', '23', 'Di hương vận phong hướng lăng tiêu thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Lăng Tiêu Thành', '284', '120|3', '', '2022-03-17 20:52:00', '0', '', '0', '0', '0', '283', '285', '23', 'Di hương vận phong hướng thánh kinh thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thánh Kinh Thành', '285', '', '', '2021-12-22 23:35:56', '0', '', '0', '286', '287', '284', '0', '23', 'Di hương vận phong hướng thiên đế thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thiên Đế Thành', '286', '', '', '2021-12-22 23:36:04', '0', '', '0', '288', '285', '0', '0', '23', 'Di hương vận phong hướng bồng lai đỉnh. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thanh Vân Thành', '287', '', '', '0000-00-00 00:00:00', '0', '', '0', '285', '0', '0', '0', '23', 'Di hương vận phong hướng thánh kinh thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bồng Lai Đỉnh', '288', '', '', '2021-12-22 23:36:10', '0', '', '0', '289', '286', '0', '0', '23', 'Di hương vận phong hướng vân tiêu bảo đô. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vân Tiêu Bảo Đô', '289', '122|2', '', '2022-03-15 16:26:03', '0', '', '0', '0', '288', '0', '0', '23', 'Di hương vận phong hướng hoa quả sơn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hoa Quả Sơn', '290', '', '', '2022-03-22 14:32:13', '600', '', '3', '0', '291', '0', '0', '24', 'Hầu gia gia hướng thôn quảng tràng . Đi đến', '0');
INSERT INTO `mid` VALUES ('Thủy Liêm Động', '291', '105|3,101|1', '', '2026-10-04 00:27:04', '0', '', '0', '290', '0', '0', '292', '24', 'PhátĐâyHướng Thôn Quảng Tràng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Ba Tiêu Lâm', '292', '', '15,24', '2021-12-22 23:37:50', '0', '', '0', '0', '0', '291', '301', '24', 'Hầu gia gia hướng thất thập nhị động khẩu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tịch Dương Cổ Đô', '293', '', '6', '0000-00-00 00:00:00', '0', '', '0', '294', '0', '0', '0', '25', 'Thái điểu các hướng huyết sắc bảo lũy. Đi đến', '0');
INSERT INTO `mid` VALUES ('Huyết Sắc Bảo Lũy', '294', '124|2', '', '2022-03-20 10:34:18', '0', '', '0', '317', '293', '318', '319', '25', 'Thái điểu các hướng tân tiên giới. Đi đến', '0');
INSERT INTO `mid` VALUES ('Lạc Nham Thôn', '295', '', '6', '0000-00-00 00:00:00', '0', '', '0', '296', '0', '0', '0', '26', 'PhátĐâyHướng Long Đàm Huyệt. Đi đến', '0');
INSERT INTO `mid` VALUES ('Long Đàm Huyệt', '296', '136|2', '', '2026-10-03 11:57:01', '0', '', '0', '326', '295', '324', '325', '26', '艾尔资源Hướng Thôn Quảng Tràng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bát Hoang Thành', '297', '', '', '0000-00-00 00:00:00', '0', '', '0', '334', '0', '332', '333', '27', 'PhátĐâyHướng Lạc Nham Thôn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Cương Thiết Các', '298', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '341', '28', 'Phát ĐâyHướng Thiên Tuyệt Cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thiên Tuyệt Cốc', '299', '54|1,54|1,54|1', '17', '2026-07-07 14:37:08', '600', 'Trong nước linh hồn trải rộng, người bình thường không dám tới gần.', '11', '0', '0', '0', '0', '29', 'PhátĐâyHướng . Đi đến', '1');
INSERT INTO `mid` VALUES ('Bỉ Ngạn Hoa', '300', '', '24', '0000-00-00 00:00:00', '0', '', '0', '0', '351', '348', '0', '30', 'PhátĐâyHướng Thiên Tuyệt Cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thất Thập Nhị Động Khẩu', '301', '', '', '0000-00-00 00:00:00', '0', '', '0', '305', '306', '292', '302', '24', 'Hầu gia gia hướng thất thập nhị động trung. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thất Thập Nhị Động Trung', '302', '', '6', '2022-03-22 14:02:25', '1200', 'Trong hang động truyền ra địa vực u minh bào hao, xin lưu ý chung quanh.', '2', '0', '0', '301', '303', '24', 'Phát ĐâyHướng Tụ Tiên Thành. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thất Thập Nhị Động Vĩ', '303', '103|1', '', '2022-03-22 10:38:35', '0', '', '0', '308', '309', '302', '307', '24', 'Hầu gia gia hướng linh hạc trì. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thải Phượng Cư', '304', '110|1', '', '2022-03-20 16:05:18', '0', '', '0', '0', '0', '0', '309', '24', 'Hầu gia gia hướng thâm cốc thủy đàm. Đi đến', '1');
INSERT INTO `mid` VALUES ('Phong Đầu Cư', '305', '104|3', '', '2022-03-22 13:57:35', '0', '', '0', '0', '301', '0', '0', '24', 'Hầu gia gia hướng thất thập nhị động khẩu. Đi đến', '1');
INSERT INTO `mid` VALUES ('Kỳ Hoa Dị Thảo Cốc', '306', '105|1,108|1', '', '2022-03-22 15:42:51', '0', '', '0', '301', '0', '0', '0', '24', 'GM hướng thôn quảng tràng . Đi đến', '1');
INSERT INTO `mid` VALUES ('Linh Hạc Trì', '307', '106|1,113|2', '', '2022-03-22 14:10:49', '0', '', '0', '0', '0', '303', '0', '24', 'Hầu gia gia hướng thất thập nhị động vĩ. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thương Thiên Cổ Thụ', '308', '104|1,111|1', '', '2022-03-22 14:05:54', '0', '', '0', '0', '303', '0', '0', '24', 'Hầu gia gia hướng thất thập nhị động vĩ. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thâm Cốc Thủy Đàm', '309', '102|1', '', '2022-03-22 14:03:44', '0', '', '0', '303', '310', '304', '0', '24', 'Hầu gia gia hướng thất thập nhị động vĩ. Đi đến', '1');
INSERT INTO `mid` VALUES ('Thâm Cốc Đào Viên', '310', '112|3', '5', '2022-03-20 10:38:07', '0', '', '0', '309', '0', '0', '0', '24', 'Hầu gia gia hướng thâm cốc thủy đàm. Đi đến', '1');
INSERT INTO `mid` VALUES ('Bích Sơn', '311', '114|2', '', '2022-03-18 20:48:36', '0', '', '0', '0', '0', '272', '0', '21', 'Di hương vận phong hướng viêm dương thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Cửu Long Pha', '312', '115|3', '', '2022-03-17 20:25:35', '0', '', '0', '272', '0', '0', '313', '21', 'Di hương vận phong hướng viêm dương thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Cổ Đằng Lâm', '313', '116|2', '', '2022-03-16 10:26:45', '0', '', '0', '314', '0', '312', '0', '21', 'Di hương vận phong hướng cửu long pha. Đi đến', '0');
INSERT INTO `mid` VALUES ('Cửu Đỉnh Sơn', '314', '117|2', '6', '2022-03-21 21:28:15', '0', '', '0', '316', '313', '0', '315', '21', 'Di hương vận phong hướng cổ đằng lâm. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thần Ma Chi Tỉnh', '315', '118|2,119|1', '', '2022-03-17 20:34:49', '0', '', '0', '0', '0', '314', '0', '21', 'Di hương vận phong hướng cửu đỉnh sơn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Băng Phong Cốc', '316', '121|3', '', '2022-03-15 10:05:13', '0', '', '0', '0', '314', '0', '0', '21', 'Di hương vận phong hướng cửu đỉnh sơn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hoàng Tuyền Động', '317', '125|3', '', '2022-03-15 10:53:27', '0', '', '0', '320', '294', '321', '0', '25', 'Hầu gia gia hướng bồng lai mê cung. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thảo Hải', '318', '126|3', '', '2021-12-15 23:52:02', '0', '', '0', '0', '0', '0', '294', '25', 'Hầu gia gia hướng huyết sắc bảo lũy. Đi đến', '0');
INSERT INTO `mid` VALUES ('Giang Đô', '319', '127|2', '', '2021-12-15 23:51:41', '0', '', '0', '0', '0', '294', '0', '25', 'Lâm lâm hướng huyết sắc bảo lũy. Đi đến', '0');
INSERT INTO `mid` VALUES ('Phích Lịch Đường', '320', '128|2,129|2,130|2', '', '2021-12-24 16:25:18', '0', '', '0', '0', '317', '0', '0', '25', 'Lâm lâm hướng hoàng tuyền động. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bồng Lai Mê Cung', '321', '131|2,132|2', '', '2021-12-15 23:52:14', '0', '', '0', '322', '0', '0', '317', '25', 'Hầu gia gia hướng thục sơn cổ đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thục Sơn Cổ Đạo', '322', '133|2', '24', '2022-03-21 08:53:57', '0', '', '0', '323', '321', '0', '0', '25', 'Hầu gia gia hướng bỉ ngạn hoa. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thần Thụ', '323', '133|1,134|1', '', '2022-03-16 11:56:47', '0', '', '0', '0', '322', '0', '324', '25', 'Lâm lâm hướng thục sơn cổ đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Linh Nhi Tiên Nhân Động', '324', '133|1,134|1,135|2', '', '2021-12-15 23:53:03', '0', '', '0', '0', '0', '323', '296', '25', 'Lâm lâm hướng thần thụ. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tỏa Yêu Tháp', '325', '120|1,121|1,122|1,123|1,124|1,125|1,126|1', '', '2026-06-26 07:45:44', '0', '', '0', '0', '327', '296', '328', '26', 'PhátĐâyHướng Long Đàm Huyệt. Đi đến', '0');
INSERT INTO `mid` VALUES ('Kiếm Trủng Thượng Tằng Tây Bộ', '326', '136|2,137|2', '', '2026-06-26 08:07:47', '0', '', '0', '0', '296', '0', '0', '26', 'PhátĐâyHướng Thôn Quảng Tràng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hải Để Thành', '327', '129|1,130|1,138|2', '', '2021-12-24 16:27:50', '0', '', '0', '325', '0', '0', '0', '26', 'Lâm lâm hướng tỏa yêu tháp. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bồng Lai Địa Hạ Mê Cung', '328', '139|2', '6', '2021-12-15 23:53:28', '0', '', '0', '0', '329', '325', '330', '26', 'Lâm lâm hướng tỏa yêu tháp. Đi đến', '0');
INSERT INTO `mid` VALUES ('Đại Độ Khẩu Mê Cung', '329', '136|1,137|1', '', '2021-12-15 23:53:30', '0', '', '0', '328', '0', '0', '0', '26', 'Lâm lâm hướng bồng lai địa hạ mê cung. Đi đến', '0');
INSERT INTO `mid` VALUES ('Dung Nham Địa Ngục Địa', '330', '140|3', '', '2022-03-15 15:51:05', '0', '', '0', '0', '331', '328', '332', '26', 'Lâm lâm hướng thông thiên tiểu lộ. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tân Tiên Giới', '331', '133|2,135|1,138|1', '', '2021-12-15 23:53:36', '0', '', '0', '330', '0', '0', '0', '26', 'Lâm lâm hướng dung nham địa ngục địa. Đi đến', '0');
INSERT INTO `mid` VALUES ('Đường Gia Bảo', '332', '141|3', '', '2022-03-15 15:50:40', '0', '', '0', '0', '0', '330', '297', '26', 'Lâm lâm hướng dung nham địa ngục địa. Đi đến', '0');
INSERT INTO `mid` VALUES ('Đăng Vân Lộc', '333', '', '', '0000-00-00 00:00:00', '0', '', '0', '338', '0', '297', '335', '27', 'Hầu gia gia hướng lục la chướng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thục Sơn Phái', '334', '', '', '0000-00-00 00:00:00', '0', '', '0', '341', '297', '0', '0', '27', 'Hầu gia gia hướng bát hoang thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Lục La Chướng', '335', '', '', '0000-00-00 00:00:00', '0', '', '0', '336', '0', '333', '337', '27', 'Hầu gia gia hướng thắng châu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thục Sơn Ngoại Thành', '336', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '335', '0', '0', '27', 'Lâm lâm hướng lục la chướng. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thắng Châu', '337', '', '', '0000-00-00 00:00:00', '0', '', '0', '340', '0', '335', '339', '27', 'Hầu gia gia hướng cương thiết các. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hà Nguyên', '338', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '333', '0', '0', '27', 'Lâm lâm hướng đăng vân lộc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Dương Danh Bách Nạp', '339', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '337', '0', '27', 'Hầu gia gia hướng thắng châu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thạch Thôn', '340', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '337', '0', '0', '27', 'Chu giác tha nhi hướng thắng châu. Đi đến', '0');
INSERT INTO `mid` VALUES ('Song Khê', '341', '', '', '0000-00-00 00:00:00', '0', '', '0', '343', '334', '298', '342', '28', 'Hầu gia gia hướng tỏa yêu tháp. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thục Sơn Cố Đạo', '342', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '341', '0', '28', 'GM hướng bát hoang thành. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tỏa Yêu Tháp', '343', '', '', '0000-00-00 00:00:00', '0', '', '0', '344', '341', '0', '345', '28', 'Hầu gia gia hướng thạch trầm khê động. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bàn Cổ Chi Tâm', '344', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '343', '0', '0', '28', 'Hầu gia gia hướng tỏa yêu tháp. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thạch Trầm Khê Động', '345', '', '', '0000-00-00 00:00:00', '0', '', '0', '347', '0', '343', '346', '28', 'Phát ĐâyHướng Thiên Tuyệt Cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tử Vân Giá', '346', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '349', '345', '348', '28', 'Phát ĐâyHướng Thạch Trầm Khê Động. Đi đến', '0');
INSERT INTO `mid` VALUES ('Sào Hồ', '347', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '345', '0', '0', '28', 'Hầu gia gia hướng thạch trầm khê động. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bát Công Sơn', '348', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '346', '300', '28', 'Phát ĐâyHướng Tử Vân Giá. Đi đến', '0');
INSERT INTO `mid` VALUES ('Nữ La Nham', '349', '', '', '0000-00-00 00:00:00', '0', '', '0', '346', '0', '0', '350', '28', 'Lâm lâm hướng tử vân giá. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hoài Nam Vương Lăng', '350', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '349', '0', '28', 'Lâm lâm hướng nữ la nham. Đi đến', '0');
INSERT INTO `mid` VALUES ('Oản Khâu Sơn', '351', '', '', '0000-00-00 00:00:00', '0', '', '0', '300', '352', '0', '0', '30', 'GM hướng thiên phật tháp. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thiên Phật Tháp', '352', '', '', '0000-00-00 00:00:00', '0', '', '0', '351', '353', '0', '0', '30', 'GM hướng tử vi đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tử Vi Đạo', '353', '', '', '0000-00-00 00:00:00', '0', '', '0', '352', '0', '0', '354', '30', 'GM hướng bạch hạo đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bạch Hạo Đạo', '354', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '353', '355', '30', 'GM hướng tịch huyền đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tịch Huyền Đạo', '355', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '354', '356', '30', 'GM hướng nguyệt nha hà cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Nguyệt Nha Hà Cốc', '356', '', '', '0000-00-00 00:00:00', '0', '', '0', '358', '359', '355', '357', '30', 'GM hướng tiên hồ cư. Đi đến', '0');
INSERT INTO `mid` VALUES ('Túy Hoa Ấm', '357', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '356', '0', '30', 'Lâm lâm hướng nguyệt nha hà cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Thanh Phong Giản', '358', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '356', '0', '0', '30', 'Lâm lâm hướng nguyệt nha hà cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Tiên Hồ Cư', '359', '', '', '0000-00-00 00:00:00', '0', '', '0', '356', '0', '0', '0', '30', 'GM hướng thiên tuyệt cốc. Đi đến', '0');
INSERT INTO `mid` VALUES ('Viêm Đế Thần Nông Động', '360', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng yêu vương điện. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bách Linh Châu', '361', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Tiểu giác233 hướng cửu long pha. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bất Chu Sơn', '362', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng lưu vực. Đi đến', '0');
INSERT INTO `mid` VALUES ('Bàn Long Trấn Trụ', '363', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng bất chu sơn. Đi đến', '0');
INSERT INTO `mid` VALUES ('Vô Thường Điện', '364', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng bàn long trấn trụ. Đi đến', '0');
INSERT INTO `mid` VALUES ('Hoàng Tuyền Quỷ Đạo', '365', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng vô thường điện. Đi đến', '0');
INSERT INTO `mid` VALUES ('Phong Thần Lăng', '366', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'Lâm lâm hướng hoàng tuyền quỷ đạo. Đi đến', '0');
INSERT INTO `mid` VALUES ('Toàn Mộng', '367', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'PhátĐâyHướng Thôn Đông. Đi đến', '0');
INSERT INTO `mid` VALUES ('Quỳnh Hoa Phái', '368', '', '', '0000-00-00 00:00:00', '0', '', '0', '0', '0', '0', '0', '31', 'PhátĐâyHướng Thôn Quảng Tràng. Đi đến', '0');

-- ----------------------------
-- Table structure for `midguaiwu`
-- ----------------------------
DROP TABLE IF EXISTS `midguaiwu`;
CREATE TABLE `midguaiwu` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `gname` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghp` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `ggj` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `gfy` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `glv` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mid` int(11) NOT NULL,
  `gyid` int(11) NOT NULL,
  `gexp` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `sid` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `gmaxhp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gbj` int(11) NOT NULL,
  `gxx` int(11) NOT NULL,
  `ms` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1765 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of midguaiwu
-- ----------------------------
INSERT INTO `midguaiwu` VALUES ('00000000025', 'Thái Hư Cổ Long', '7900', '684', '450', '59', '325', '122', '6313', '', '7900', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000001764', 'Sơn Trư', '240', '10', '14', '1', '228', '56', '106', '', '240', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000001362', 'Giả Thôn Trường', '500', '5', '2', '3', '225', '52', '324', '', '500', '1', '1', '0');
INSERT INTO `midguaiwu` VALUES ('00000000021', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '296', '136', '7704', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000026', 'Thanh Loan', '8000', '1869', '1690', '60', '325', '123', '6360', '', '8000', '15', '15', '0');
INSERT INTO `midguaiwu` VALUES ('00000000027', 'Mặc Giao', '8540', '860', '469', '61', '325', '124', '6466', '', '8540', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000028', 'Huyết Ngọc Tri Chu', '9600', '680', '470', '62', '325', '125', '6572', '', '9600', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000029', 'Đề Hồn Thú', '8800', '811', '480', '63', '325', '126', '6804', '', '8800', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000031', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '326', '136', '7704', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000032', 'Kim Giác Cự Thú', '10200', '769', '681', '73', '326', '137', '7738', '', '10200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000033', 'Kim Giác Cự Thú', '10200', '769', '681', '73', '326', '137', '7738', '', '10200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000034', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '326', '136', '7776', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000035', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '326', '136', '7776', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000036', 'Kim Giác Cự Thú', '10200', '769', '681', '73', '326', '137', '7884', '', '10200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000037', 'Kim Giác Cự Thú', '10200', '769', '681', '73', '326', '137', '7884', '', '10200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000085', 'Kiếp Sát Giả', '4810', '306', '275', '45', '269', '96', '4860', '', '4810', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000001363', 'Hồ Yêu', '500', '1', '1', '1', '225', '53', '108', '', '500', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000045', 'Thủy U Linh', '1772', '500', '260', '48', '299', '54', '5088', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000046', 'Thủy U Linh', '1800', '500', '260', '48', '299', '54', '5088', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000047', 'Huyết Nha Vương', '5860', '594', '558', '55', '267', '94', '5940', '', '5860', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000048', 'Huyết Nha Vương', '5860', '594', '558', '55', '267', '94', '5940', '', '5860', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000049', 'Huyết Nha Vương', '5860', '594', '558', '55', '267', '94', '5940', '', '5860', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000050', 'Hắc Diễm Yêu Vương', '4530', '653', '454', '56', '267', '95', '6048', '', '4530', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000051', 'Hắc Diễm Yêu Vương', '4530', '653', '454', '56', '267', '95', '6048', '', '4530', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000052', 'Hắc Diễm Yêu Vương', '4530', '653', '454', '56', '267', '95', '6048', '', '4530', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000053', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '267', '99', '5350', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000054', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '267', '99', '5350', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000055', 'Thủy U Linh', '1800', '500', '260', '48', '299', '54', '5088', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000078', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '216', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000057', 'Thủy U Linh', '1800', '500', '260', '48', '299', '54', '5088', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000141', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '296', '136', '7632', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000001365', 'Giả Thôn Trường', '500', '5', '2', '3', '225', '52', '321', '', '500', '1', '1', '0');
INSERT INTO `midguaiwu` VALUES ('00000001364', 'Hồ Yêu', '500', '1', '1', '1', '225', '53', '108', '', '500', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000083', 'Kiếp Sát Giả', '4810', '306', '275', '45', '269', '96', '4860', '', '4810', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000084', 'Kiếp Sát Giả', '4810', '306', '275', '45', '269', '96', '4860', '', '4810', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000070', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '212', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000071', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '212', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000072', 'Hoa Yêu', '200', '25', '17', '3', '230', '58', '318', '', '200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000073', 'Hoa Yêu', '200', '25', '17', '3', '230', '58', '318', '', '200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000074', 'Hoa Yêu', '200', '25', '17', '3', '230', '58', '318', '', '200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000075', 'Hoa Yêu', '200', '25', '17', '3', '230', '58', '318', '', '200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000086', 'Kiếp Sát Giả', '4810', '306', '275', '45', '269', '96', '4860', '', '4810', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000080', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '216', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000081', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '216', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000082', 'Lão Hổ', '170', '18', '11', '2', '229', '57', '216', '', '170', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000087', 'Tinh Duệ Bạn Binh', '4600', '320', '287', '47', '269', '97', '4982', '', '4600', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000088', 'Tinh Duệ Bạn Binh', '4600', '320', '287', '47', '269', '97', '4982', '', '4600', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000089', 'Tinh Duệ Bạn Binh', '4600', '320', '287', '47', '269', '97', '4982', '', '4600', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000090', 'Tinh Duệ Bạn Binh', '4600', '320', '287', '47', '269', '97', '4982', '', '4600', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000097', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '271', '99', '5400', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000098', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '271', '99', '5400', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000099', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '271', '99', '5400', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000100', 'Tây Môn Quan Nhân', '6200', '480', '400', '50', '271', '99', '5400', '', '6200', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000101', 'Thủy U Linh', '1800', '500', '260', '48', '270', '54', '5136', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000102', 'Thủy U Linh', '1800', '500', '260', '48', '270', '54', '5136', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000103', 'Thủy U Linh', '1800', '500', '260', '48', '270', '54', '5136', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000104', 'Thủy U Linh', '1800', '500', '260', '48', '270', '54', '5136', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000105', 'Thủy U Linh', '1800', '500', '260', '48', '270', '54', '5136', '', '1800', '10', '10', '0');
INSERT INTO `midguaiwu` VALUES ('00000000106', 'Tầm Bảo Thử', '6000', '450', '350', '51', '270', '100', '5406', '', '6000', '10', '50', '0');
INSERT INTO `midguaiwu` VALUES ('00000000138', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '296', '136', '7704', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000139', 'La Hầu Hắc Phượng', '12200', '781', '459', '72', '296', '136', '7704', '', '12200', '0', '5', '0');
INSERT INTO `midguaiwu` VALUES ('00000000135', 'Đại Đội Trường', '5800', '440', '405', '50', '272', '98', '5350', '', '5800', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000121', 'Đại Đội Trường', '5800', '440', '405', '50', '272', '98', '5400', '', '5800', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000122', 'Đại Đội Trường', '5800', '440', '405', '50', '272', '98', '5350', '', '5800', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000129', 'Đại Đội Trường', '5800', '440', '405', '50', '272', '98', '5400', '', '5800', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000001761', 'Sơn Trư', '240', '10', '14', '1', '228', '56', '106', '', '240', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000001762', 'Sơn Trư', '240', '10', '14', '1', '228', '56', '106', '', '240', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000816', 'Thị Huyết Dã Lang', '260', '22', '32', '5', '231', '61', '535', '', '260', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000817', 'Thị Huyết Dã Lang', '260', '22', '32', '5', '231', '61', '535', '', '260', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000001763', 'Sơn Trư', '240', '10', '14', '1', '228', '56', '106', '', '240', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000814', 'Cuồng Bạo Dã Lang', '310', '23', '38', '6', '231', '62', '648', '', '310', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000815', 'Cuồng Bạo Dã Lang', '310', '23', '38', '6', '231', '62', '648', '', '310', '0', '0', '0');
INSERT INTO `midguaiwu` VALUES ('00000000813', 'Cuồng Bạo Dã Lang', '310', '23', '38', '6', '231', '62', '648', '', '310', '0', '0', '0');

-- ----------------------------
-- Table structure for `mine_log`
-- ----------------------------
DROP TABLE IF EXISTS `mine_log`;
CREATE TABLE `mine_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `yxb_reward` int(11) NOT NULL DEFAULT '0',
  `czb_reward` int(11) NOT NULL DEFAULT '0',
  `note` varchar(120) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mine_log
-- ----------------------------
INSERT INTO `mine_log` VALUES ('31', '8d0329755724609ccb9713953eee47f4', '10', '0', 'Mảnh linh quặng', '2026-07-09 21:06:15');
INSERT INTO `mine_log` VALUES ('32', '8d0329755724609ccb9713953eee47f4', '19', '0', 'Linh thạch thô', '2026-07-09 21:06:17');
INSERT INTO `mine_log` VALUES ('33', '8d0329755724609ccb9713953eee47f4', '27', '0', 'Linh thạch thô', '2026-07-09 21:06:18');
INSERT INTO `mine_log` VALUES ('34', '8d0329755724609ccb9713953eee47f4', '27', '0', 'Linh thạch thô', '2026-07-09 21:06:19');
INSERT INTO `mine_log` VALUES ('35', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:06:19');
INSERT INTO `mine_log` VALUES ('36', '8d0329755724609ccb9713953eee47f4', '8', '0', 'Mảnh linh quặng', '2026-07-09 21:06:20');
INSERT INTO `mine_log` VALUES ('37', '8d0329755724609ccb9713953eee47f4', '19', '0', 'Linh thạch thô', '2026-07-09 21:06:21');
INSERT INTO `mine_log` VALUES ('38', '8d0329755724609ccb9713953eee47f4', '21', '0', 'Linh thạch thô', '2026-07-09 21:06:21');
INSERT INTO `mine_log` VALUES ('39', '8d0329755724609ccb9713953eee47f4', '30', '0', 'Linh thạch thô', '2026-07-09 21:06:22');
INSERT INTO `mine_log` VALUES ('40', '8d0329755724609ccb9713953eee47f4', '8', '0', 'Mảnh linh quặng', '2026-07-09 21:06:23');
INSERT INTO `mine_log` VALUES ('41', '8d0329755724609ccb9713953eee47f4', '6', '0', 'Mảnh linh quặng', '2026-07-09 21:06:23');
INSERT INTO `mine_log` VALUES ('42', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:06:24');
INSERT INTO `mine_log` VALUES ('43', '8d0329755724609ccb9713953eee47f4', '25', '0', 'Linh thạch thô', '2026-07-09 21:06:25');
INSERT INTO `mine_log` VALUES ('44', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:06:30');
INSERT INTO `mine_log` VALUES ('45', '8d0329755724609ccb9713953eee47f4', '11', '0', 'Mảnh linh quặng', '2026-07-09 21:06:34');
INSERT INTO `mine_log` VALUES ('46', '8d0329755724609ccb9713953eee47f4', '7', '0', 'Mảnh linh quặng', '2026-07-09 21:06:37');
INSERT INTO `mine_log` VALUES ('47', '8d0329755724609ccb9713953eee47f4', '47', '0', 'Linh thạch nguyên khối', '2026-07-09 21:06:38');
INSERT INTO `mine_log` VALUES ('48', '8d0329755724609ccb9713953eee47f4', '23', '0', 'Linh thạch thô', '2026-07-09 21:06:46');
INSERT INTO `mine_log` VALUES ('49', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:06:47');
INSERT INTO `mine_log` VALUES ('50', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:06:48');
INSERT INTO `mine_log` VALUES ('51', '8d0329755724609ccb9713953eee47f4', '22', '0', 'Linh thạch thô', '2026-07-09 21:06:48');
INSERT INTO `mine_log` VALUES ('52', '8d0329755724609ccb9713953eee47f4', '6', '0', 'Mảnh linh quặng', '2026-07-09 21:06:50');
INSERT INTO `mine_log` VALUES ('53', '8d0329755724609ccb9713953eee47f4', '9', '0', 'Mảnh linh quặng', '2026-07-09 21:06:50');
INSERT INTO `mine_log` VALUES ('54', '8d0329755724609ccb9713953eee47f4', '186', '0', 'Linh thạch thô', '2026-07-09 21:08:48');
INSERT INTO `mine_log` VALUES ('55', '8d0329755724609ccb9713953eee47f4', '142', '0', 'Linh thạch thô', '2026-07-09 21:08:50');
INSERT INTO `mine_log` VALUES ('56', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-09 21:08:50');
INSERT INTO `mine_log` VALUES ('57', '8d0329755724609ccb9713953eee47f4', '169', '0', 'Linh thạch thô', '2026-07-09 21:08:51');
INSERT INTO `mine_log` VALUES ('58', '8d0329755724609ccb9713953eee47f4', '46', '0', 'Mảnh linh quặng', '2026-07-09 21:08:51');
INSERT INTO `mine_log` VALUES ('59', '8d0329755724609ccb9713953eee47f4', '155', '0', 'Linh thạch thô', '2026-07-09 21:09:04');
INSERT INTO `mine_log` VALUES ('60', '8d0329755724609ccb9713953eee47f4', '80', '0', 'Mảnh linh quặng', '2026-07-09 21:09:05');
INSERT INTO `mine_log` VALUES ('61', '8d0329755724609ccb9713953eee47f4', '214', '0', 'Linh thạch thô', '2026-07-20 14:15:09');
INSERT INTO `mine_log` VALUES ('62', '8d0329755724609ccb9713953eee47f4', '172', '0', 'Linh thạch thô', '2026-07-20 14:15:13');
INSERT INTO `mine_log` VALUES ('63', '8d0329755724609ccb9713953eee47f4', '81', '0', 'Mảnh linh quặng', '2026-07-20 14:15:13');
INSERT INTO `mine_log` VALUES ('64', '8d0329755724609ccb9713953eee47f4', '165', '0', 'Linh thạch thô', '2026-07-20 14:15:13');
INSERT INTO `mine_log` VALUES ('65', '8d0329755724609ccb9713953eee47f4', '220', '0', 'Linh thạch thô', '2026-07-20 14:15:13');
INSERT INTO `mine_log` VALUES ('66', '8d0329755724609ccb9713953eee47f4', '38', '0', 'Mảnh linh quặng', '2026-07-20 14:15:13');
INSERT INTO `mine_log` VALUES ('67', '8d0329755724609ccb9713953eee47f4', '401', '0', 'Linh thạch nguyên khối', '2026-07-20 14:15:18');
INSERT INTO `mine_log` VALUES ('68', '8d0329755724609ccb9713953eee47f4', '223', '0', 'Linh thạch thô', '2026-07-20 14:15:21');
INSERT INTO `mine_log` VALUES ('69', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-20 14:15:24');
INSERT INTO `mine_log` VALUES ('70', '8d0329755724609ccb9713953eee47f4', '71', '0', 'Mảnh linh quặng', '2026-07-20 14:15:30');
INSERT INTO `mine_log` VALUES ('71', '8d0329755724609ccb9713953eee47f4', '209', '0', 'Linh thạch thô', '2026-07-20 14:15:32');
INSERT INTO `mine_log` VALUES ('72', '8d0329755724609ccb9713953eee47f4', '129', '0', 'Linh thạch thô', '2026-07-20 14:15:33');
INSERT INTO `mine_log` VALUES ('73', '8d0329755724609ccb9713953eee47f4', '98', '0', 'Linh thạch thô', '2026-07-20 14:15:34');
INSERT INTO `mine_log` VALUES ('74', '8d0329755724609ccb9713953eee47f4', '371', '0', 'Linh thạch nguyên khối', '2026-07-20 14:19:29');
INSERT INTO `mine_log` VALUES ('75', '8d0329755724609ccb9713953eee47f4', '24', '0', 'Thảo dược phổ thông x3 · +3 Thảo Dược Phổ Thông', '2026-07-22 16:34:34');
INSERT INTO `mine_log` VALUES ('76', '8d0329755724609ccb9713953eee47f4', '10', '0', 'Thảo dược phổ thông x3 · +3 Thảo Dược Phổ Thông', '2026-07-22 16:34:37');
INSERT INTO `mine_log` VALUES ('77', '8d0329755724609ccb9713953eee47f4', '31', '0', 'Mảnh linh quặng + Thảo Dược Phổ Thông · +1 Thảo Dược Phổ Thông', '2026-07-22 16:34:38');
INSERT INTO `mine_log` VALUES ('78', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá nguyên tố · +1 Thủy Tinh Thạch', '2026-07-22 16:34:38');
INSERT INTO `mine_log` VALUES ('79', '8d0329755724609ccb9713953eee47f4', '16', '0', 'Thảo dược phổ thông x3 · +3 Thảo Dược Phổ Thông', '2026-07-22 16:34:40');
INSERT INTO `mine_log` VALUES ('80', '8d0329755724609ccb9713953eee47f4', '152', '0', 'Linh thạch thô', '2026-07-22 17:02:14');
INSERT INTO `mine_log` VALUES ('81', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Cường hóa thạch x3 · +3 Cường Hóa Thạch', '2026-07-22 17:02:16');
INSERT INTO `mine_log` VALUES ('82', '8d0329755724609ccb9713953eee47f4', '17', '0', 'Cường hóa thạch x2 · +2 Cường Hóa Thạch', '2026-07-22 17:02:18');
INSERT INTO `mine_log` VALUES ('83', '8d0329755724609ccb9713953eee47f4', '101', '0', 'Linh thạch thô', '2026-07-22 17:02:31');
INSERT INTO `mine_log` VALUES ('84', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Cường hóa thạch x3 · +3 Cường Hóa Thạch', '2026-07-22 17:02:35');
INSERT INTO `mine_log` VALUES ('85', '8d0329755724609ccb9713953eee47f4', '10', '0', 'Băng Tuyết Liên x2 · +2 Băng Tuyết Liên', '2026-07-22 17:02:37');
INSERT INTO `mine_log` VALUES ('86', '8d0329755724609ccb9713953eee47f4', '42', '0', 'Mảnh linh quặng + Băng Tuyết Liên · +1 Băng Tuyết Liên', '2026-07-22 17:06:22');
INSERT INTO `mine_log` VALUES ('87', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-22 17:06:30');
INSERT INTO `mine_log` VALUES ('88', '8d0329755724609ccb9713953eee47f4', '197', '0', 'Linh thạch thô', '2026-07-22 17:06:32');
INSERT INTO `mine_log` VALUES ('89', '8d0329755724609ccb9713953eee47f4', '191', '0', 'Linh thạch thô', '2026-07-22 17:06:56');
INSERT INTO `mine_log` VALUES ('90', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Cường hóa thạch x2 · +2 Cường Hóa Thạch', '2026-07-22 17:06:58');
INSERT INTO `mine_log` VALUES ('91', '8d0329755724609ccb9713953eee47f4', '21', '0', 'Bích Lục Tiên Thảo x2 · +2 Bích Lục Tiên Thảo', '2026-07-22 17:06:58');
INSERT INTO `mine_log` VALUES ('92', '8d0329755724609ccb9713953eee47f4', '56', '0', 'Mảnh linh quặng + Trúc Diệp Đan · +1 Trúc Diệp Đan', '2026-07-22 17:07:12');
INSERT INTO `mine_log` VALUES ('93', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Đá vụn', '2026-07-22 17:08:08');
INSERT INTO `mine_log` VALUES ('94', '8d0329755724609ccb9713953eee47f4', '202', '0', 'Linh thạch thô', '2026-07-22 17:08:10');
INSERT INTO `mine_log` VALUES ('95', '8d0329755724609ccb9713953eee47f4', '0', '0', 'Cường hóa thạch x2 · +2 Cường Hóa Thạch', '2026-07-22 17:08:11');
INSERT INTO `mine_log` VALUES ('96', '8d0329755724609ccb9713953eee47f4', '202', '0', 'Linh thạch thô', '2026-07-22 17:08:13');
INSERT INTO `mine_log` VALUES ('97', '8d0329755724609ccb9713953eee47f4', '101', '0', 'Linh thạch thô', '2026-07-22 17:08:18');
INSERT INTO `mine_log` VALUES ('98', '8d0329755724609ccb9713953eee47f4', '94', '0', 'Linh thạch thô', '2026-07-22 17:08:21');
INSERT INTO `mine_log` VALUES ('99', '8d0329755724609ccb9713953eee47f4', '56', '0', 'Mảnh linh quặng + Long Đảm Hoa +1 Long Đảm Hoa', '2026-07-22 17:08:22');
INSERT INTO `mine_log` VALUES ('100', '8d0329755724609ccb9713953eee47f4', '55', '0', 'Mảnh linh quặng + Linh Chi Huyết Sắc +1 Linh Chi Huyết Sắc', '2026-07-22 17:08:22');
INSERT INTO `mine_log` VALUES ('101', '8d0329755724609ccb9713953eee47f4', '17', '0', 'Phong Linh Thảo x3 +3 Phong Linh Thảo', '2026-07-22 17:08:24');
INSERT INTO `mine_log` VALUES ('102', '8d0329755724609ccb9713953eee47f4', '48', '0', 'Mảnh linh quặng + Phục Linh Vạn Niên +1 Phục Linh Vạn Niên', '2026-07-22 17:08:25');
INSERT INTO `mine_log` VALUES ('103', '8d0329755724609ccb9713953eee47f4', '12', '0', 'Cường hóa thạch x2 +2 Cường Hóa Thạch', '2026-07-22 17:08:25');
INSERT INTO `mine_log` VALUES ('104', '8d0329755724609ccb9713953eee47f4', '55', '0', 'Mảnh linh quặng + Bích Lục Tiên Thảo +1 Bích Lục Tiên Thảo', '2026-07-22 17:08:26');

-- ----------------------------
-- Table structure for `mine_player`
-- ----------------------------
DROP TABLE IF EXISTS `mine_player`;
CREATE TABLE `mine_player` (
  `sid` varchar(64) NOT NULL,
  `today_digs` int(11) NOT NULL DEFAULT '0',
  `last_dig_date` date DEFAULT NULL,
  `total_digs` int(11) NOT NULL DEFAULT '0',
  `total_yxb` int(11) NOT NULL DEFAULT '0',
  `total_czb` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mine_player
-- ----------------------------
INSERT INTO `mine_player` VALUES ('8d0329755724609ccb9713953eee47f4', '0', '2026-10-03', '74', '5205', '0', '2026-10-03 13:22:25');
INSERT INTO `mine_player` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '0', '2026-10-16', '0', '0', '0', '2026-10-16 08:37:35');

-- ----------------------------
-- Table structure for `player_alchemist`
-- ----------------------------
DROP TABLE IF EXISTS `player_alchemist`;
CREATE TABLE `player_alchemist` (
  `sid` varchar(64) NOT NULL,
  `alch_rank` int(11) DEFAULT '1' COMMENT 'C???p luy???n ??an s?? (1-9)',
  `alch_exp` int(11) DEFAULT '0' COMMENT 'Kinh nghi???m luy???n ??an',
  `total_craft` int(11) DEFAULT '0' COMMENT 'T???ng s??? l???n luy???n ??an th??nh c??ng',
  `total_toxin` int(11) DEFAULT '0' COMMENT 'T???ng ??an ?????c t??ch t???',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `pill_toxin` int(11) DEFAULT '0' COMMENT 'Pill toxin from using pills',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_alchemist
-- ----------------------------
INSERT INTO `player_alchemist` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '1', '0', '0', '0', '2026-10-16 08:13:49', '2026-10-16 08:13:49', '0');
INSERT INTO `player_alchemist` VALUES ('062aefa38dfc651e9590f98b7f7363a8', '1', '0', '0', '0', '2026-10-16 09:37:03', '2026-10-16 09:37:03', '0');
INSERT INTO `player_alchemist` VALUES ('15649bfc31246f2baa5aab857ceed4ab', '1', '0', '0', '0', '2026-10-16 09:21:42', '2026-10-16 09:21:42', '0');
INSERT INTO `player_alchemist` VALUES ('378bf7fdd60f2d81207e58ad6da4aaca', '1', '0', '0', '0', '2026-10-03 21:09:47', '2026-10-03 21:09:47', '0');
INSERT INTO `player_alchemist` VALUES ('40e5df6609a8fd1b15904b9aa798a877', '1', '0', '0', '0', '2026-10-06 11:21:56', '2026-10-06 11:21:56', '0');
INSERT INTO `player_alchemist` VALUES ('4c5c628532920dab5bdf08b667d0dd48', '1', '0', '0', '0', '2026-10-16 09:27:35', '2026-10-16 09:27:35', '0');
INSERT INTO `player_alchemist` VALUES ('6b7766a40825443262489376feafd063', '1', '0', '0', '0', '2026-10-16 15:44:54', '2026-10-16 15:44:54', '0');
INSERT INTO `player_alchemist` VALUES ('6db638049a596ef5049586bceaf61dec', '1', '0', '0', '0', '2026-10-16 09:06:03', '2026-10-16 09:06:03', '0');
INSERT INTO `player_alchemist` VALUES ('8d0329755724609ccb9713953eee47f4', '4', '2082', '116', '358', '2026-10-03 13:22:07', '2026-10-03 16:07:35', '0');
INSERT INTO `player_alchemist` VALUES ('a49a6c4337615603acc2f3902e48e280', '1', '0', '0', '0', '2026-10-16 09:42:17', '2026-10-16 09:42:17', '0');
INSERT INTO `player_alchemist` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', '1', '15', '1', '1', '2026-10-03 11:11:07', '2026-10-03 11:17:25', '0');
INSERT INTO `player_alchemist` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1', '0', '0', '0', '2026-10-04 23:13:40', '2026-10-04 23:13:40', '0');
INSERT INTO `player_alchemist` VALUES ('f3829669e2fdc04aaf5c9725725fe022', '1', '0', '0', '0', '2026-10-16 09:39:23', '2026-10-16 09:39:23', '0');
INSERT INTO `player_alchemist` VALUES ('f6858a418360c29a5a4d5c2f1b622228', '1', '0', '0', '0', '2026-10-16 15:49:13', '2026-10-16 15:49:13', '0');
INSERT INTO `player_alchemist` VALUES ('f91190a62d0f3267f85d972e4ba496d3', '1', '0', '0', '0', '2026-10-16 09:29:46', '2026-10-16 09:29:46', '0');
INSERT INTO `player_alchemist` VALUES ('ffffffffffffffffffffffffffffffff', '1', '0', '0', '0', '2026-10-14 10:53:50', '2026-10-14 10:53:50', '0');

-- ----------------------------
-- Table structure for `player_alchemy_formation`
-- ----------------------------
DROP TABLE IF EXISTS `player_alchemy_formation`;
CREATE TABLE `player_alchemy_formation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `location_id` int(11) NOT NULL,
  `formation_type` int(11) DEFAULT '1' COMMENT '1-H???a, 2-Th???y, 3-Phong, 4-L??i',
  `duration_hour` int(11) DEFAULT '1',
  `cost_ma_thach` int(11) DEFAULT '5',
  `created_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_alchemy_formation
-- ----------------------------

-- ----------------------------
-- Table structure for `player_alchemy_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_alchemy_log`;
CREATE TABLE `player_alchemy_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `dan_van` int(11) DEFAULT '0',
  `dan_toxin` int(11) DEFAULT '0',
  `dan_loi_level` int(11) DEFAULT '0' COMMENT 'C???p ??an L??i nh???n ???????c',
  `alch_exp_gained` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_alchemy_log
-- ----------------------------
INSERT INTO `player_alchemy_log` VALUES ('1', '8', '1', '1', '3', '4', '0', '21', '2026-07-24 09:29:29');
INSERT INTO `player_alchemy_log` VALUES ('2', '8', '1', '1', '0', '8', '0', '15', '2026-07-24 09:30:41');
INSERT INTO `player_alchemy_log` VALUES ('3', '8', '1', '1', '0', '1', '0', '15', '2026-07-25 06:01:09');
INSERT INTO `player_alchemy_log` VALUES ('4', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '1', '0', '1', '0', '15', '2026-10-03 11:17:25');
INSERT INTO `player_alchemy_log` VALUES ('5', '8d0329755724609ccb9713953eee47f4', '1', '1', '0', '1', '0', '15', '2026-10-03 14:05:56');
INSERT INTO `player_alchemy_log` VALUES ('6', '8d0329755724609ccb9713953eee47f4', '1', '1', '0', '1', '0', '15', '2026-10-03 14:05:57');
INSERT INTO `player_alchemy_log` VALUES ('7', '8d0329755724609ccb9713953eee47f4', '1', '1', '0', '1', '0', '15', '2026-10-03 14:43:44');
INSERT INTO `player_alchemy_log` VALUES ('8', '8d0329755724609ccb9713953eee47f4', '1', '1', '0', '1', '0', '15', '2026-10-03 14:43:45');
INSERT INTO `player_alchemy_log` VALUES ('9', '8d0329755724609ccb9713953eee47f4', '11', '0', '0', '0', '0', '3', '2026-10-03 15:02:01');
INSERT INTO `player_alchemy_log` VALUES ('10', '8d0329755724609ccb9713953eee47f4', '11', '1', '0', '10', '0', '15', '2026-10-03 15:02:01');
INSERT INTO `player_alchemy_log` VALUES ('11', '8d0329755724609ccb9713953eee47f4', '11', '1', '0', '0', '0', '15', '2026-10-03 15:02:02');
INSERT INTO `player_alchemy_log` VALUES ('12', '8d0329755724609ccb9713953eee47f4', '11', '1', '1', '0', '0', '17', '2026-10-03 15:02:02');
INSERT INTO `player_alchemy_log` VALUES ('13', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:02:03');
INSERT INTO `player_alchemy_log` VALUES ('14', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:14');
INSERT INTO `player_alchemy_log` VALUES ('15', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:15');
INSERT INTO `player_alchemy_log` VALUES ('16', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:15');
INSERT INTO `player_alchemy_log` VALUES ('17', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:16');
INSERT INTO `player_alchemy_log` VALUES ('18', '8d0329755724609ccb9713953eee47f4', '11', '1', '0', '15', '0', '15', '2026-10-03 15:02:16');
INSERT INTO `player_alchemy_log` VALUES ('19', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:17');
INSERT INTO `player_alchemy_log` VALUES ('20', '8d0329755724609ccb9713953eee47f4', '11', '1', '0', '15', '0', '15', '2026-10-03 15:02:18');
INSERT INTO `player_alchemy_log` VALUES ('21', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:18');
INSERT INTO `player_alchemy_log` VALUES ('22', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:19');
INSERT INTO `player_alchemy_log` VALUES ('23', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:19');
INSERT INTO `player_alchemy_log` VALUES ('24', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:20');
INSERT INTO `player_alchemy_log` VALUES ('25', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:20');
INSERT INTO `player_alchemy_log` VALUES ('26', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:02:21');
INSERT INTO `player_alchemy_log` VALUES ('27', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:21');
INSERT INTO `player_alchemy_log` VALUES ('28', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '5', '0', '19', '2026-10-03 15:02:22');
INSERT INTO `player_alchemy_log` VALUES ('29', '8d0329755724609ccb9713953eee47f4', '11', '0', '2', '0', '0', '3', '2026-10-03 15:02:23');
INSERT INTO `player_alchemy_log` VALUES ('30', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '5', '0', '19', '2026-10-03 15:02:23');
INSERT INTO `player_alchemy_log` VALUES ('31', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:24');
INSERT INTO `player_alchemy_log` VALUES ('32', '8d0329755724609ccb9713953eee47f4', '11', '1', '0', '15', '0', '15', '2026-10-03 15:02:24');
INSERT INTO `player_alchemy_log` VALUES ('33', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:02:25');
INSERT INTO `player_alchemy_log` VALUES ('34', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '0', '0', '19', '2026-10-03 15:27:58');
INSERT INTO `player_alchemy_log` VALUES ('35', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:27:59');
INSERT INTO `player_alchemy_log` VALUES ('36', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:27:59');
INSERT INTO `player_alchemy_log` VALUES ('37', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:00');
INSERT INTO `player_alchemy_log` VALUES ('38', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:01');
INSERT INTO `player_alchemy_log` VALUES ('39', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:01');
INSERT INTO `player_alchemy_log` VALUES ('40', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:02');
INSERT INTO `player_alchemy_log` VALUES ('41', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:02');
INSERT INTO `player_alchemy_log` VALUES ('42', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:03');
INSERT INTO `player_alchemy_log` VALUES ('43', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:28:03');
INSERT INTO `player_alchemy_log` VALUES ('44', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '15', '0', '19', '2026-10-03 15:28:04');
INSERT INTO `player_alchemy_log` VALUES ('45', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:04');
INSERT INTO `player_alchemy_log` VALUES ('46', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:05');
INSERT INTO `player_alchemy_log` VALUES ('47', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:06');
INSERT INTO `player_alchemy_log` VALUES ('48', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:06');
INSERT INTO `player_alchemy_log` VALUES ('49', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:28:07');
INSERT INTO `player_alchemy_log` VALUES ('50', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:07');
INSERT INTO `player_alchemy_log` VALUES ('51', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:08');
INSERT INTO `player_alchemy_log` VALUES ('52', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:08');
INSERT INTO `player_alchemy_log` VALUES ('53', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:09');
INSERT INTO `player_alchemy_log` VALUES ('54', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:09');
INSERT INTO `player_alchemy_log` VALUES ('55', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:10');
INSERT INTO `player_alchemy_log` VALUES ('56', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:10');
INSERT INTO `player_alchemy_log` VALUES ('57', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:11');
INSERT INTO `player_alchemy_log` VALUES ('58', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:12');
INSERT INTO `player_alchemy_log` VALUES ('59', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '5', '0', '3', '2026-10-03 15:28:12');
INSERT INTO `player_alchemy_log` VALUES ('60', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:28:13');
INSERT INTO `player_alchemy_log` VALUES ('61', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:13');
INSERT INTO `player_alchemy_log` VALUES ('62', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:28:14');
INSERT INTO `player_alchemy_log` VALUES ('63', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '15', '0', '19', '2026-10-03 15:28:15');
INSERT INTO `player_alchemy_log` VALUES ('64', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:15');
INSERT INTO `player_alchemy_log` VALUES ('65', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:16');
INSERT INTO `player_alchemy_log` VALUES ('66', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '15', '0', '19', '2026-10-03 15:28:16');
INSERT INTO `player_alchemy_log` VALUES ('67', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:17');
INSERT INTO `player_alchemy_log` VALUES ('68', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:17');
INSERT INTO `player_alchemy_log` VALUES ('69', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:18');
INSERT INTO `player_alchemy_log` VALUES ('70', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:19');
INSERT INTO `player_alchemy_log` VALUES ('71', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:28:19');
INSERT INTO `player_alchemy_log` VALUES ('72', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:20');
INSERT INTO `player_alchemy_log` VALUES ('73', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:20');
INSERT INTO `player_alchemy_log` VALUES ('74', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:21');
INSERT INTO `player_alchemy_log` VALUES ('75', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:21');
INSERT INTO `player_alchemy_log` VALUES ('76', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:22');
INSERT INTO `player_alchemy_log` VALUES ('77', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:23');
INSERT INTO `player_alchemy_log` VALUES ('78', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:23');
INSERT INTO `player_alchemy_log` VALUES ('79', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:24');
INSERT INTO `player_alchemy_log` VALUES ('80', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '10', '0', '3', '2026-10-03 15:28:24');
INSERT INTO `player_alchemy_log` VALUES ('81', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:25');
INSERT INTO `player_alchemy_log` VALUES ('82', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:25');
INSERT INTO `player_alchemy_log` VALUES ('83', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:28:26');
INSERT INTO `player_alchemy_log` VALUES ('84', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:28:27');
INSERT INTO `player_alchemy_log` VALUES ('85', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:28:27');
INSERT INTO `player_alchemy_log` VALUES ('86', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:28');
INSERT INTO `player_alchemy_log` VALUES ('87', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:28:28');
INSERT INTO `player_alchemy_log` VALUES ('88', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:29');
INSERT INTO `player_alchemy_log` VALUES ('89', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:29');
INSERT INTO `player_alchemy_log` VALUES ('90', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '0', '0', '3', '2026-10-03 15:28:30');
INSERT INTO `player_alchemy_log` VALUES ('91', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:30');
INSERT INTO `player_alchemy_log` VALUES ('92', '8d0329755724609ccb9713953eee47f4', '11', '0', '2', '15', '0', '3', '2026-10-03 15:28:31');
INSERT INTO `player_alchemy_log` VALUES ('93', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:32');
INSERT INTO `player_alchemy_log` VALUES ('94', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:32');
INSERT INTO `player_alchemy_log` VALUES ('95', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:33');
INSERT INTO `player_alchemy_log` VALUES ('96', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '15', '0', '19', '2026-10-03 15:28:34');
INSERT INTO `player_alchemy_log` VALUES ('97', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:34');
INSERT INTO `player_alchemy_log` VALUES ('98', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '5', '0', '3', '2026-10-03 15:28:35');
INSERT INTO `player_alchemy_log` VALUES ('99', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:28:35');
INSERT INTO `player_alchemy_log` VALUES ('100', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:06');
INSERT INTO `player_alchemy_log` VALUES ('101', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:06');
INSERT INTO `player_alchemy_log` VALUES ('102', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:07');
INSERT INTO `player_alchemy_log` VALUES ('103', '8d0329755724609ccb9713953eee47f4', '11', '1', '2', '15', '0', '19', '2026-10-03 15:30:07');
INSERT INTO `player_alchemy_log` VALUES ('104', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '10', '0', '3', '2026-10-03 15:30:08');
INSERT INTO `player_alchemy_log` VALUES ('105', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:10');
INSERT INTO `player_alchemy_log` VALUES ('106', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '5', '0', '21', '2026-10-03 15:30:10');
INSERT INTO `player_alchemy_log` VALUES ('107', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:11');
INSERT INTO `player_alchemy_log` VALUES ('108', '8d0329755724609ccb9713953eee47f4', '11', '0', '3', '5', '0', '3', '2026-10-03 15:30:12');
INSERT INTO `player_alchemy_log` VALUES ('109', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:14');
INSERT INTO `player_alchemy_log` VALUES ('110', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:14');
INSERT INTO `player_alchemy_log` VALUES ('111', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:15');
INSERT INTO `player_alchemy_log` VALUES ('112', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:15');
INSERT INTO `player_alchemy_log` VALUES ('113', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '10', '0', '21', '2026-10-03 15:30:16');
INSERT INTO `player_alchemy_log` VALUES ('114', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:16');
INSERT INTO `player_alchemy_log` VALUES ('115', '8d0329755724609ccb9713953eee47f4', '11', '1', '3', '0', '0', '21', '2026-10-03 15:30:17');
INSERT INTO `player_alchemy_log` VALUES ('116', '8d0329755724609ccb9713953eee47f4', '1', '1', '3', '2', '0', '21', '2026-10-03 15:49:23');
INSERT INTO `player_alchemy_log` VALUES ('117', '8d0329755724609ccb9713953eee47f4', '1', '0', '3', '2', '0', '3', '2026-10-03 15:49:23');
INSERT INTO `player_alchemy_log` VALUES ('118', '8d0329755724609ccb9713953eee47f4', '7', '1', '0', '31', '0', '25', '2026-10-03 15:49:38');
INSERT INTO `player_alchemy_log` VALUES ('119', '8d0329755724609ccb9713953eee47f4', '8', '1', '0', '13', '0', '25', '2026-10-03 16:07:35');
INSERT INTO `player_alchemy_log` VALUES ('120', '8d0329755724609ccb9713953eee47f4', '8', '1', '2', '26', '0', '29', '2026-10-03 16:07:35');

-- ----------------------------
-- Table structure for `player_chetao_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_chetao_log`;
CREATE TABLE `player_chetao_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `log_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chetao',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=65163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of player_chetao_log
-- ----------------------------
INSERT INTO `player_chetao_log` VALUES ('1', '8', '1', '1', '2026-07-19 22:51:44', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('2', '8', '1', '1', '2026-07-19 22:54:18', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('3', '8', '1', '0', '2026-07-19 22:54:20', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('4', '8', '1', '1', '2026-07-19 22:54:21', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('5', '8', '1', '1', '2026-07-19 23:00:06', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('6', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '1', '2026-10-03 11:18:03', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('7', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 14:05:59', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('8', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 14:48:01', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('9', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 14:48:04', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('10', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 15:03:09', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('11', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:26', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('12', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:29', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('13', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:32', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('14', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:35', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('15', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:11:37', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('16', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:40', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('17', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:43', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('18', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:46', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('19', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:49', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('20', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:11:52', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('21', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:11:55', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('22', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:11:58', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('23', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:01', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('24', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:04', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('25', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:07', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('26', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:09', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('27', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:12', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('28', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:15', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('29', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:18', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('30', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:21', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('31', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:24', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('32', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:27', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('33', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:30', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('34', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:33', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('35', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:36', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('36', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:39', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('37', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:42', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('38', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:45', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('39', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:47', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('40', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:50', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('41', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:12:53', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('42', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:56', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('43', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:12:59', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('44', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:02', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('45', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:05', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('46', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:08', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('47', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:11', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('48', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:14', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('49', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:17', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('50', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:20', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('51', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:23', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('52', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:26', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('53', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:29', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('54', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:32', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('55', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:35', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('56', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:38', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('57', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:41', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('58', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:44', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('59', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:47', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('60', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:50', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('61', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:13:53', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('62', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:56', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('63', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:13:59', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('64', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:14:02', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('65', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:14:05', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('66', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:14:08', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('67', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:14:11', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('68', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:14:14', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('69', '8d0329755724609ccb9713953eee47f4', '8', '0', '2026-10-03 16:14:17', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('70', '8d0329755724609ccb9713953eee47f4', '8', '1', '2026-10-03 16:14:20', 'chetao');
INSERT INTO `player_chetao_log` VALUES ('107', '8d0329755724609ccb9713953eee47f4', '0', '1', '2026-10-04 17:18:19', 'nap_hanh');
INSERT INTO `player_chetao_log` VALUES ('106', '8d0329755724609ccb9713953eee47f4', '0', '1', '2026-10-04 17:16:38', 'nap_hanh');

-- ----------------------------
-- Table structure for `player_dan_dinh`
-- ----------------------------
DROP TABLE IF EXISTS `player_dan_dinh`;
CREATE TABLE `player_dan_dinh` (
  `sid` varchar(64) NOT NULL,
  `dinh_type` int(11) DEFAULT '1' COMMENT 'Lo???i l?? 1-9',
  `dinh_level` int(11) DEFAULT '0' COMMENT 'Level n??ng c???p 0-5',
  `dinh_exp` int(11) DEFAULT '0' COMMENT 'EXP l??',
  `skill_hoa_thuat` tinyint(1) DEFAULT '0' COMMENT 'K??? n??ng H???a Thu???t',
  `skill_linh_khi` tinyint(1) DEFAULT '0' COMMENT 'K??? n??ng Linh Kh??',
  `skill_bao_than` tinyint(1) DEFAULT '0' COMMENT 'K??? n??ng B???o Th??n',
  `skill_thien_loi` tinyint(1) DEFAULT '0' COMMENT 'K??? n??ng Thi??n L??i',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_dan_dinh
-- ----------------------------
INSERT INTO `player_dan_dinh` VALUES ('1b3246e8f45e4276967d00ad79cab118', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('378bf7fdd60f2d81207e58ad6da4aaca', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('40e5df6609a8fd1b15904b9aa798a877', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('4c149ae65f7dab8474cd47dbb3b17677', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('7841cebe57141c476b1d1d2272b65e53', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('8d0329755724609ccb9713953eee47f4', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('dc4996fe7860595b1bb92db3c2af9fab', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);
INSERT INTO `player_dan_dinh` VALUES ('fb36ea13a56ad413d9cccebca56bb546', '1', '0', '0', '0', '0', '0', '0', '2026-10-03 11:26:16', null);

-- ----------------------------
-- Table structure for `player_dan_linh`
-- ----------------------------
DROP TABLE IF EXISTS `player_dan_linh`;
CREATE TABLE `player_dan_linh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `linh_type` int(11) DEFAULT '1' COMMENT 'Lo???i 1-7 (Long/Ph?????ng/Huy???n V??/B??ng Li??n/H???a Ph?????ng/L??i ?????/Thi??n Ma)',
  `linh_level` int(11) DEFAULT '1' COMMENT 'C???p ??an Linh',
  `linh_exp` int(11) DEFAULT '0' COMMENT 'EXP ??an Linh',
  `base_hp_bonus` int(11) DEFAULT '0' COMMENT 'Bonus HP c?? b???n',
  `base_atk_bonus` int(11) DEFAULT '0' COMMENT 'Bonus ATK c?? b???n',
  `base_def_bonus` int(11) DEFAULT '0' COMMENT 'Bonus DEF c?? b???n',
  `skill_1` int(11) DEFAULT '0' COMMENT 'Skill 1',
  `skill_2` int(11) DEFAULT '0' COMMENT 'Skill 2',
  `skill_3` int(11) DEFAULT '0' COMMENT 'Skill 3',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '??ang active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_dan_linh
-- ----------------------------

-- ----------------------------
-- Table structure for `player_dan_thu`
-- ----------------------------
DROP TABLE IF EXISTS `player_dan_thu`;
CREATE TABLE `player_dan_thu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `thu_type` int(11) DEFAULT '1' COMMENT 'Lo???i 1-6 (Ph??m/Th???n)',
  `recipe_id` int(11) NOT NULL COMMENT 'ID c??ng th???c trong alchemy_recipe',
  `is_learned` tinyint(1) DEFAULT '0' COMMENT '???? h???c ch??a',
  `craft_count` int(11) DEFAULT '0' COMMENT 'S??? l???n luy???n th??nh c??ng',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_dan_thu
-- ----------------------------

-- ----------------------------
-- Table structure for `player_huyetmach_awaken_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_huyetmach_awaken_log`;
CREATE TABLE `player_huyetmach_awaken_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `from_stage` tinyint(4) NOT NULL,
  `to_stage` tinyint(4) NOT NULL,
  `boss_killed` varchar(100) DEFAULT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_huyetmach_awaken_log
-- ----------------------------
INSERT INTO `player_huyetmach_awaken_log` VALUES ('1', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '1', null, '1', '2026-10-03 11:21:47');
INSERT INTO `player_huyetmach_awaken_log` VALUES ('20', '2b06d0ff01b346e5f2a0666ab83dc044', '0', '1', null, '1', '2026-10-11 04:25:39');
INSERT INTO `player_huyetmach_awaken_log` VALUES ('21', '2b06d0ff01b346e5f2a0666ab83dc044', '1', '2', 'Hỗn Độn Thạch', '1', '2026-10-11 04:27:12');
INSERT INTO `player_huyetmach_awaken_log` VALUES ('22', '2b06d0ff01b346e5f2a0666ab83dc044', '2', '3', 'Hỗn Độn Thú', '1', '2026-10-11 04:28:17');

-- ----------------------------
-- Table structure for `player_huyetmach_boss`
-- ----------------------------
DROP TABLE IF EXISTS `player_huyetmach_boss`;
CREATE TABLE `player_huyetmach_boss` (
  `sid` varchar(64) NOT NULL,
  `boss_id` varchar(50) NOT NULL,
  `killed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`boss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_huyetmach_boss
-- ----------------------------
INSERT INTO `player_huyetmach_boss` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'Hỗn Độn Thạch', '2026-10-11 04:26:13');
INSERT INTO `player_huyetmach_boss` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'Hỗn Độn Thú', '2026-10-11 04:27:58');

-- ----------------------------
-- Table structure for `player_huyetmach_quest`
-- ----------------------------
DROP TABLE IF EXISTS `player_huyetmach_quest`;
CREATE TABLE `player_huyetmach_quest` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(50) NOT NULL,
  `progress` text,
  `completed` tinyint(1) DEFAULT '0',
  `claimed` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`quest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_huyetmach_quest
-- ----------------------------

-- ----------------------------
-- Table structure for `player_huyetmach_upgrade_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_huyetmach_upgrade_log`;
CREATE TABLE `player_huyetmach_upgrade_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `from_quality` int(11) NOT NULL,
  `to_quality` int(11) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=799 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_huyetmach_upgrade_log
-- ----------------------------
INSERT INTO `player_huyetmach_upgrade_log` VALUES ('797', '2b06d0ff01b346e5f2a0666ab83dc044', '1', '2', '1', '2026-10-11 04:31:04');
INSERT INTO `player_huyetmach_upgrade_log` VALUES ('798', '2b06d0ff01b346e5f2a0666ab83dc044', '2', '3', '1', '2026-10-11 04:31:04');

-- ----------------------------
-- Table structure for `player_huyetmach_vessel`
-- ----------------------------
DROP TABLE IF EXISTS `player_huyetmach_vessel`;
CREATE TABLE `player_huyetmach_vessel` (
  `sid` varchar(64) NOT NULL,
  `vessel_id` tinyint(4) NOT NULL,
  `level` int(11) DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`vessel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_huyetmach_vessel
-- ----------------------------
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1', '16', '2026-10-11 04:27:28');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2', '16', '2026-10-11 04:27:31');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3', '16', '2026-10-11 04:27:34');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '4', '16', '2026-10-11 04:27:36');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '5', '8', '2026-10-11 04:26:52');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '6', '8', '2026-10-11 04:26:55');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '7', '8', '2026-10-11 04:26:57');
INSERT INTO `player_huyetmach_vessel` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '8', '8', '2026-10-11 04:27:00');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1', '6', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '2', '6', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '3', '4', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '4', '2', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '5', '5', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '6', '16', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '7', '3', '2026-10-14 11:27:11');
INSERT INTO `player_huyetmach_vessel` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '8', '0', '2026-10-14 11:27:11');

-- ----------------------------
-- Table structure for `player_linhcan_break_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_linhcan_break_log`;
CREATE TABLE `player_linhcan_break_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `element` varchar(10) NOT NULL,
  `from_stage` tinyint(4) NOT NULL,
  `to_stage` tinyint(4) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_linhcan_break_log
-- ----------------------------
INSERT INTO `player_linhcan_break_log` VALUES ('20', '2b06d0ff01b346e5f2a0666ab83dc044', 'hoa', '0', '1', '2026-10-11 03:49:36');
INSERT INTO `player_linhcan_break_log` VALUES ('21', '2b06d0ff01b346e5f2a0666ab83dc044', 'hoa', '1', '2', '2026-10-11 03:50:50');

-- ----------------------------
-- Table structure for `player_linhcan_node`
-- ----------------------------
DROP TABLE IF EXISTS `player_linhcan_node`;
CREATE TABLE `player_linhcan_node` (
  `sid` varchar(64) NOT NULL,
  `element` varchar(10) NOT NULL,
  `level` int(11) DEFAULT '0',
  `stage` tinyint(4) DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_linhcan_node
-- ----------------------------
INSERT INTO `player_linhcan_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'hoa', '0', '2', '2026-10-11 03:51:53');
INSERT INTO `player_linhcan_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'moc', '1', '0', '2026-10-11 03:43:51');

-- ----------------------------
-- Table structure for `player_linhcan_quest`
-- ----------------------------
DROP TABLE IF EXISTS `player_linhcan_quest`;
CREATE TABLE `player_linhcan_quest` (
  `sid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quest_week` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress` text COLLATE utf8mb4_unicode_ci,
  `completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`sid`,`quest_week`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of player_linhcan_quest
-- ----------------------------
INSERT INTO `player_linhcan_quest` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', '202640', '{\"1\":0,\"2\":12,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('8d0329755724609ccb9713953eee47f4', '202640', '{\"1\":0,\"2\":500,\"3\":0,\"4\":0,\"5\":21}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('f91190a62d0f3267f85d972e4ba496d3', '202642', '{\"1\":0,\"2\":1,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '202641', '{\"1\":31,\"2\":10,\"3\":2,\"4\":92,\"5\":2}', '1');
INSERT INTO `player_linhcan_quest` VALUES ('40e5df6609a8fd1b15904b9aa798a877', '202641', '{\"1\":0,\"2\":0,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '202642', '{\"1\":0,\"2\":4,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('4c5c628532920dab5bdf08b667d0dd48', '202642', '{\"1\":0,\"2\":1,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('062aefa38dfc651e9590f98b7f7363a8', '202642', '{\"1\":0,\"2\":7,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('a49a6c4337615603acc2f3902e48e280', '202642', '{\"1\":0,\"2\":1,\"3\":0,\"4\":0,\"5\":0}', '0');
INSERT INTO `player_linhcan_quest` VALUES ('6b7766a40825443262489376feafd063', '202642', '{\"1\":0,\"2\":1,\"3\":0,\"4\":0,\"5\":0}', '0');

-- ----------------------------
-- Table structure for `player_linhcan_skill`
-- ----------------------------
DROP TABLE IF EXISTS `player_linhcan_skill`;
CREATE TABLE `player_linhcan_skill` (
  `sid` varchar(64) NOT NULL,
  `skill_id` varchar(40) NOT NULL,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_linhcan_skill
-- ----------------------------

-- ----------------------------
-- Table structure for `player_luonden_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_luonden_log`;
CREATE TABLE `player_luonden_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of player_luonden_log
-- ----------------------------

-- ----------------------------
-- Table structure for `player_nhiemvu_achievements`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_achievements`;
CREATE TABLE `player_nhiemvu_achievements` (
  `sid` varchar(64) NOT NULL,
  `achievement_id` varchar(20) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`achievement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_achievements
-- ----------------------------
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'MS1', '500', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'MS2', '500', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'MS3', '500', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'AM1', '100', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'AM2', '106', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'AM3', '106', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BS1', '39', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BS2', '39', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BS3', '39', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CL1', '500', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CL2', '725', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CL3', '725', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BH1', '10', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BH2', '21', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'BH3', '21', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'PV1', '10', '1');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'PV2', '22', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'PV3', '22', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CE1', '92', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CE2', '92', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'CE3', '92', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'SC1', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'SC2', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'SC3', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'TC1', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'TC2', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'TC3', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'LC1', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'LC2', '0', '0');
INSERT INTO `player_nhiemvu_achievements` VALUES ('8d0329755724609ccb9713953eee47f4', 'LC3', '0', '0');

-- ----------------------------
-- Table structure for `player_nhiemvu_daily`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_daily`;
CREATE TABLE `player_nhiemvu_daily` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(20) NOT NULL,
  `reset_date` date NOT NULL,
  `progress` int(11) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`quest_id`,`reset_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_daily
-- ----------------------------
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'D1', '2026-10-03', '91', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'D4', '2026-10-03', '1', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'D6', '2026-10-03', '10', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'D7', '2026-10-03', '1', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'D8', '2026-10-03', '3', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('8d0329755724609ccb9713953eee47f4', 'daily_bonus', '2026-10-03', '1', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'D1', '2026-10-07', '60', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'D3', '2026-10-07', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'D5', '2026-10-07', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'D6', '2026-10-07', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'D8', '2026-10-07', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'D1', '2026-10-11', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'D2', '2026-10-11', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'D3', '2026-10-11', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'D5', '2026-10-11', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'D6', '2026-10-11', '20', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'D3', '2026-10-16', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'D5', '2026-10-16', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'D6', '2026-10-16', '10', '1', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'D7', '2026-10-16', '0', '0', '0');
INSERT INTO `player_nhiemvu_daily` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'D8', '2026-10-16', '0', '0', '0');

-- ----------------------------
-- Table structure for `player_nhiemvu_hidden`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_hidden`;
CREATE TABLE `player_nhiemvu_hidden` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(20) NOT NULL,
  `discovered` tinyint(1) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `progress` int(11) NOT NULL DEFAULT '0',
  `timer` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`quest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_hidden
-- ----------------------------
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H1', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H2', '1', '1', '5', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H3', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H4', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H5', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H6', '1', '1', '100', '2026-10-03 17:17:59');
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H7', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('8d0329755724609ccb9713953eee47f4', 'H8', '1', '1', '5', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', 'H6', '1', '0', '8', '2026-10-03 22:25:54');
INSERT INTO `player_nhiemvu_hidden` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'H3', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('ffffffffffffffffffffffffffffffff', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', 'H4', '1', '1', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('ffffffffffffffffffffffffffffffff', 'H6', '1', '0', '8', '2026-10-14 12:11:00');
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTP8052db4b57bcda3ef3935816368', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTPbc5604cd1481be7bbcf692d7f2b', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('c8c35537f4a2bfa618801654b9cd1fc5', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('40e5df6609a8fd1b15904b9aa798a877', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('fb36ea13a56ad413d9cccebca56bb546', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('dc4996fe7860595b1bb92db3c2af9fab', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('1b3246e8f45e4276967d00ad79cab118', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('378bf7fdd60f2d81207e58ad6da4aaca', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('4c149ae65f7dab8474cd47dbb3b17677', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('6c4666ee50d00945c0251d14ca2275f2', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('7841cebe57141c476b1d1d2272b65e53', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTP091cea5bb7dc031041130a53249', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTPf703c2b46e4b5c45cffd7789aef', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTPe7227d011123d04fd6e38535689', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('MIGTP4ba1b9152e2e68ca136d492203e', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'H6', '1', '0', '4', '2026-10-16 09:41:54');
INSERT INTO `player_nhiemvu_hidden` VALUES ('03bb973718e9c9e68ad90317c5efdf68', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('6db638049a596ef5049586bceaf61dec', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('15649bfc31246f2baa5aab857ceed4ab', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('4c5c628532920dab5bdf08b667d0dd48', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('4c5c628532920dab5bdf08b667d0dd48', 'H6', '1', '0', '1', '2026-10-16 10:27:35');
INSERT INTO `player_nhiemvu_hidden` VALUES ('f91190a62d0f3267f85d972e4ba496d3', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('f91190a62d0f3267f85d972e4ba496d3', 'H6', '1', '0', '1', '2026-10-16 10:29:47');
INSERT INTO `player_nhiemvu_hidden` VALUES ('062aefa38dfc651e9590f98b7f7363a8', 'H6', '1', '0', '7', '2026-10-16 10:37:04');
INSERT INTO `player_nhiemvu_hidden` VALUES ('062aefa38dfc651e9590f98b7f7363a8', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('a49a6c4337615603acc2f3902e48e280', 'H6', '1', '0', '1', '2026-10-16 10:42:23');
INSERT INTO `player_nhiemvu_hidden` VALUES ('6b7766a40825443262489376feafd063', 'H4', '0', '0', '0', null);
INSERT INTO `player_nhiemvu_hidden` VALUES ('6b7766a40825443262489376feafd063', 'H6', '1', '0', '1', '2026-10-16 16:44:55');

-- ----------------------------
-- Table structure for `player_nhiemvu_main`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_main`;
CREATE TABLE `player_nhiemvu_main` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `progress` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`quest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_main
-- ----------------------------
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.1', '2', '1');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.2', '2', '10');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.3', '2', '20');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.4', '2', '5');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.5', '2', '1');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.6', '2', '1');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '1.7', '2', '1');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.1', '2', '15');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.2', '2', '3');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.3', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.4', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.5', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '2.6', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.1', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.2', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.3', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.4', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.5', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.6', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.7', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('8d0329755724609ccb9713953eee47f4', '3.8', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.1', '2', '1');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.2', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.3', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.4', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.5', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.6', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1.7', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.1', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.2', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.3', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.4', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.5', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2.6', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.1', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.2', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.3', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.4', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.5', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.6', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.7', '0', '0');
INSERT INTO `player_nhiemvu_main` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3.8', '0', '0');

-- ----------------------------
-- Table structure for `player_nhiemvu_monthly`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_monthly`;
CREATE TABLE `player_nhiemvu_monthly` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(20) NOT NULL,
  `month` varchar(7) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`quest_id`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_monthly
-- ----------------------------
INSERT INTO `player_nhiemvu_monthly` VALUES ('8d0329755724609ccb9713953eee47f4', 'M1', '2026-10', '100', '1', '1');
INSERT INTO `player_nhiemvu_monthly` VALUES ('8d0329755724609ccb9713953eee47f4', 'M2', '2026-10', '10', '1', '0');
INSERT INTO `player_nhiemvu_monthly` VALUES ('8d0329755724609ccb9713953eee47f4', 'M3', '2026-10', '21', '0', '0');
INSERT INTO `player_nhiemvu_monthly` VALUES ('8d0329755724609ccb9713953eee47f4', 'M4', '2026-10', '18', '0', '0');
INSERT INTO `player_nhiemvu_monthly` VALUES ('8d0329755724609ccb9713953eee47f4', 'M5', '2026-10', '2', '0', '0');

-- ----------------------------
-- Table structure for `player_nhiemvu_weekly`
-- ----------------------------
DROP TABLE IF EXISTS `player_nhiemvu_weekly`;
CREATE TABLE `player_nhiemvu_weekly` (
  `sid` varchar(64) NOT NULL,
  `quest_id` varchar(20) NOT NULL,
  `week_number` int(11) NOT NULL,
  `progress` int(11) NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`quest_id`,`week_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_nhiemvu_weekly
-- ----------------------------
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W1', '202640', '10', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W2', '202640', '100', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W3', '202640', '3', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W4', '202640', '5', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W5', '202640', '5', '1', '1');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W6', '202640', '200', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W7', '202640', '20', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W8', '202640', '10', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W9', '202640', '50', '1', '0');
INSERT INTO `player_nhiemvu_weekly` VALUES ('8d0329755724609ccb9713953eee47f4', 'W10', '202640', '9', '1', '1');

-- ----------------------------
-- Table structure for `player_sauthu`
-- ----------------------------
DROP TABLE IF EXISTS `player_sauthu`;
CREATE TABLE `player_sauthu` (
  `sid` varchar(64) NOT NULL,
  `current_map` int(11) NOT NULL DEFAULT '1',
  `best_map` int(11) NOT NULL DEFAULT '0',
  `total_fights` int(11) NOT NULL DEFAULT '0',
  `total_captures` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_sauthu
-- ----------------------------
INSERT INTO `player_sauthu` VALUES ('8d0329755724609ccb9713953eee47f4', '1', '3', '13', '9', '2026-07-22 23:02:35');
INSERT INTO `player_sauthu` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '1', '0', '0', '0', '2026-10-16 08:37:35');

-- ----------------------------
-- Table structure for `player_thechat_awaken_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_thechat_awaken_log`;
CREATE TABLE `player_thechat_awaken_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `from_stage` tinyint(4) NOT NULL,
  `to_stage` tinyint(4) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_thechat_awaken_log
-- ----------------------------
INSERT INTO `player_thechat_awaken_log` VALUES ('1', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '1', '1', '2026-10-03 11:23:34');
INSERT INTO `player_thechat_awaken_log` VALUES ('2', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '0', '1', '1', '2026-10-09 12:08:09');
INSERT INTO `player_thechat_awaken_log` VALUES ('3', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '0', '1', '1', '2026-10-09 12:23:18');
INSERT INTO `player_thechat_awaken_log` VALUES ('4', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1', '2', '1', '2026-10-09 12:43:44');
INSERT INTO `player_thechat_awaken_log` VALUES ('5', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '0', '1', '1', '2026-10-09 12:49:21');
INSERT INTO `player_thechat_awaken_log` VALUES ('6', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1', '2', '1', '2026-10-09 14:36:26');
INSERT INTO `player_thechat_awaken_log` VALUES ('7', '2b06d0ff01b346e5f2a0666ab83dc044', '0', '1', '1', '2026-10-11 04:09:02');

-- ----------------------------
-- Table structure for `player_thechat_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_thechat_log`;
CREATE TABLE `player_thechat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `from_quality` int(11) NOT NULL,
  `to_quality` int(11) NOT NULL,
  `success` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_thechat_log
-- ----------------------------
INSERT INTO `player_thechat_log` VALUES ('1', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '3', '1', '2026-10-03 11:19:04');
INSERT INTO `player_thechat_log` VALUES ('2', '2b06d0ff01b346e5f2a0666ab83dc044', '2', '3', '1', '2026-10-11 04:12:14');

-- ----------------------------
-- Table structure for `player_thechat_node`
-- ----------------------------
DROP TABLE IF EXISTS `player_thechat_node`;
CREATE TABLE `player_thechat_node` (
  `sid` varchar(64) NOT NULL,
  `node_id` tinyint(4) NOT NULL,
  `node_lv` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`node_id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_thechat_node
-- ----------------------------
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '1', '12', '2026-10-11 04:09:37');
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '2', '10', '2026-10-11 04:08:21');
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '3', '10', '2026-10-11 04:08:25');
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '4', '3', '2026-10-11 04:09:56');
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '5', '3', '2026-10-11 04:09:58');
INSERT INTO `player_thechat_node` VALUES ('2b06d0ff01b346e5f2a0666ab83dc044', '6', '3', '2026-10-11 04:10:01');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1', '12', '2026-10-09 20:04:54');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '2', '9', '2026-10-09 20:04:54');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '3', '14', '2026-10-09 20:04:54');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '4', '6', '2026-10-09 20:04:54');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '5', '8', '2026-10-09 20:04:54');
INSERT INTO `player_thechat_node` VALUES ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '6', '11', '2026-10-09 20:04:54');

-- ----------------------------
-- Table structure for `player_tianfu`
-- ----------------------------
DROP TABLE IF EXISTS `player_tianfu`;
CREATE TABLE `player_tianfu` (
  `sid` varchar(64) NOT NULL,
  `tianfu_id` tinyint(4) NOT NULL,
  `quality` tinyint(4) NOT NULL DEFAULT '1',
  `lv` int(11) NOT NULL DEFAULT '0',
  `slot` tinyint(4) NOT NULL DEFAULT '0',
  `obtained_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`,`tianfu_id`),
  KEY `idx_sid_slot` (`sid`,`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_tianfu
-- ----------------------------
INSERT INTO `player_tianfu` VALUES ('8d0329755724609ccb9713953eee47f4', '4', '4', '24', '1', '2026-10-27 17:51:10');
INSERT INTO `player_tianfu` VALUES ('8d0329755724609ccb9713953eee47f4', '8', '6', '0', '0', '2026-10-27 17:41:19');

-- ----------------------------
-- Table structure for `player_tianfu_log`
-- ----------------------------
DROP TABLE IF EXISTS `player_tianfu_log`;
CREATE TABLE `player_tianfu_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `kind` varchar(20) NOT NULL,
  `tianfu_id` tinyint(4) NOT NULL,
  `from_q` tinyint(4) DEFAULT '0',
  `to_q` tinyint(4) DEFAULT '0',
  `detail` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=2867 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_tianfu_log
-- ----------------------------
INSERT INTO `player_tianfu_log` VALUES ('2825', 'c8c35537f4a2bfa618801654b9cd1fc5', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2827', '2b06d0ff01b346e5f2a0666ab83dc044', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2828', '8d0329755724609ccb9713953eee47f4', 'migrate', '0', '0', '0', 'hoan 362 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2829', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2830', '40e5df6609a8fd1b15904b9aa798a877', 'migrate', '0', '0', '0', 'hoan 22 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2831', 'fb36ea13a56ad413d9cccebca56bb546', 'migrate', '0', '0', '0', 'hoan 24 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2832', 'dc4996fe7860595b1bb92db3c2af9fab', 'migrate', '0', '0', '0', 'hoan 2 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2833', '1b3246e8f45e4276967d00ad79cab118', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2834', '378bf7fdd60f2d81207e58ad6da4aaca', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2835', '4c149ae65f7dab8474cd47dbb3b17677', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2836', '6c4666ee50d00945c0251d14ca2275f2', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2837', '7841cebe57141c476b1d1d2272b65e53', 'migrate', '0', '0', '0', 'hoan 0 diem tu 7 cot cu + 1 Khai Phu Dan 114', '2026-10-14 15:05:40');
INSERT INTO `player_tianfu_log` VALUES ('2838', '8d0329755724609ccb9713953eee47f4', 'open', '8', '0', '6', 'khai mo', '2026-10-27 17:41:19');
INSERT INTO `player_tianfu_log` VALUES ('2839', '8d0329755724609ccb9713953eee47f4', 'slot', '8', '0', '1', 'activated=8', '2026-10-27 17:43:50');
INSERT INTO `player_tianfu_log` VALUES ('2840', '8d0329755724609ccb9713953eee47f4', 'slot', '8', '1', '0', 'activated=', '2026-10-27 17:43:55');
INSERT INTO `player_tianfu_log` VALUES ('2841', '8d0329755724609ccb9713953eee47f4', 'open', '4', '0', '4', 'khai mo', '2026-10-27 17:51:10');
INSERT INTO `player_tianfu_log` VALUES ('2842', '8d0329755724609ccb9713953eee47f4', 'slot', '4', '0', '1', 'activated=4', '2026-10-27 17:53:47');
INSERT INTO `player_tianfu_log` VALUES ('2843', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '0', '1', 'cost tf=1 lt=200', '2026-10-27 17:53:55');
INSERT INTO `player_tianfu_log` VALUES ('2844', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '1', '2', 'cost tf=1 lt=225', '2026-10-27 17:53:58');
INSERT INTO `player_tianfu_log` VALUES ('2845', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '2', '3', 'cost tf=1 lt=250', '2026-10-27 17:53:58');
INSERT INTO `player_tianfu_log` VALUES ('2846', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '3', '4', 'cost tf=1 lt=275', '2026-10-27 17:53:59');
INSERT INTO `player_tianfu_log` VALUES ('2847', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '4', '5', 'cost tf=1 lt=300', '2026-10-27 17:53:59');
INSERT INTO `player_tianfu_log` VALUES ('2848', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '5', '6', 'cost tf=1 lt=325', '2026-10-27 17:54:00');
INSERT INTO `player_tianfu_log` VALUES ('2849', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '6', '7', 'cost tf=1 lt=350', '2026-10-27 17:54:00');
INSERT INTO `player_tianfu_log` VALUES ('2850', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '7', '8', 'cost tf=1 lt=375', '2026-10-27 17:54:00');
INSERT INTO `player_tianfu_log` VALUES ('2851', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '8', '9', 'cost tf=1 lt=400', '2026-10-27 17:54:01');
INSERT INTO `player_tianfu_log` VALUES ('2852', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '9', '10', 'cost tf=1 lt=425', '2026-10-27 17:54:01');
INSERT INTO `player_tianfu_log` VALUES ('2853', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '10', '11', 'cost tf=1 lt=450', '2026-10-27 17:54:01');
INSERT INTO `player_tianfu_log` VALUES ('2854', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '11', '12', 'cost tf=1 lt=475', '2026-10-27 17:54:02');
INSERT INTO `player_tianfu_log` VALUES ('2855', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '12', '13', 'cost tf=1 lt=500', '2026-10-27 17:54:02');
INSERT INTO `player_tianfu_log` VALUES ('2856', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '13', '14', 'cost tf=1 lt=525', '2026-10-27 17:54:02');
INSERT INTO `player_tianfu_log` VALUES ('2857', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '14', '15', 'cost tf=1 lt=550', '2026-10-27 17:54:03');
INSERT INTO `player_tianfu_log` VALUES ('2858', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '15', '16', 'cost tf=1 lt=575', '2026-10-27 17:54:03');
INSERT INTO `player_tianfu_log` VALUES ('2859', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '16', '17', 'cost tf=1 lt=600', '2026-10-27 17:54:03');
INSERT INTO `player_tianfu_log` VALUES ('2860', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '17', '18', 'cost tf=1 lt=625', '2026-10-27 17:54:04');
INSERT INTO `player_tianfu_log` VALUES ('2861', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '18', '19', 'cost tf=1 lt=650', '2026-10-27 17:54:04');
INSERT INTO `player_tianfu_log` VALUES ('2862', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '19', '20', 'cost tf=1 lt=675', '2026-10-27 17:54:04');
INSERT INTO `player_tianfu_log` VALUES ('2863', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '20', '21', 'cost tf=1 lt=700', '2026-10-27 17:54:05');
INSERT INTO `player_tianfu_log` VALUES ('2864', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '21', '22', 'cost tf=1 lt=725', '2026-10-27 17:54:05');
INSERT INTO `player_tianfu_log` VALUES ('2865', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '22', '23', 'cost tf=1 lt=750', '2026-10-27 17:54:06');
INSERT INTO `player_tianfu_log` VALUES ('2866', '8d0329755724609ccb9713953eee47f4', 'lvup', '4', '23', '24', 'cost tf=1 lt=775', '2026-10-27 17:54:06');

-- ----------------------------
-- Table structure for `player_toayeuthap`
-- ----------------------------
DROP TABLE IF EXISTS `player_toayeuthap`;
CREATE TABLE `player_toayeuthap` (
  `sid` varchar(64) NOT NULL,
  `floor` int(11) NOT NULL DEFAULT '1',
  `best_floor` int(11) NOT NULL DEFAULT '0',
  `wins` int(11) NOT NULL DEFAULT '0',
  `last_reset_week` int(11) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of player_toayeuthap
-- ----------------------------
INSERT INTO `player_toayeuthap` VALUES ('8d0329755724609ccb9713953eee47f4', '119', '128', '246', '32', '2026-08-05 09:54:32');
INSERT INTO `player_toayeuthap` VALUES ('03bb973718e9c9e68ad90317c5efdf68', '1', '0', '0', '42', '2026-10-16 08:37:35');

-- ----------------------------
-- Table structure for `playerchongwu`
-- ----------------------------
DROP TABLE IF EXISTS `playerchongwu`;
CREATE TABLE `playerchongwu` (
  `cwid` int(11) NOT NULL AUTO_INCREMENT,
  `cwname` varchar(255) NOT NULL,
  `cwhp` int(11) NOT NULL,
  `cwmaxhp` int(11) NOT NULL,
  `cwgj` int(11) NOT NULL,
  `cwfy` int(11) NOT NULL,
  `cwbj` int(11) NOT NULL,
  `cwxx` int(11) NOT NULL,
  `cwlv` int(11) NOT NULL,
  `cwexp` int(11) NOT NULL,
  `tool1` int(11) NOT NULL,
  `tool2` int(11) NOT NULL,
  `tool3` int(11) NOT NULL,
  `tool4` int(11) NOT NULL,
  `tool5` int(11) NOT NULL,
  `tool6` int(11) NOT NULL,
  `tool7` int(11) NOT NULL DEFAULT '0',
  `sid` varchar(255) NOT NULL,
  `uphp` int(11) NOT NULL,
  `upgj` int(11) NOT NULL,
  `upfy` int(11) NOT NULL,
  `cwpz` int(11) NOT NULL,
  `pet_config_id` int(11) NOT NULL DEFAULT '0',
  `cwmaxexp` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`cwid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=552 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerchongwu
-- ----------------------------
INSERT INTO `playerchongwu` VALUES ('2', 'Bổn Bổn Ngưu', '100', '100', '6', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a0f5700982d69976ce54c08cb1c2bbb0', '21', '3', '3', '0', '0', '');
INSERT INTO `playerchongwu` VALUES ('14', 'Quai Quai Cẩu', '300', '300', '30', '36', '0', '0', '9', '10014', '0', '0', '0', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '18', '2', '3', '4', '0', '');
INSERT INTO `playerchongwu` VALUES ('17', 'Đản Đản Kê', '-22560', '100', '6', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '15', '3', '7', '4', '0', '');
INSERT INTO `playerchongwu` VALUES ('64', 'Khốc Khốc Long', '274', '478', '96', '166', '0', '0', '19', '8624', '0', '0', '0', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '16', '4', '7', '3', '0', '');
INSERT INTO `playerchongwu` VALUES ('73', 'Bổn Bổn Ngưu', '-67353', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '25', '5', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('74', 'Khiêu Khiêu Thố', '-67353', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '12', '2', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('75', 'Khốc Khốc Long', '176', '188', '20', '22', '0', '0', '5', '2156', '0', '0', '0', '0', '0', '0', '0', '8090f13824dc62c1bdd7b3db5a101fd7', '17', '2', '4', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('76', 'Chiêu Tài Trư', '-108', '160', '32', '46', '0', '0', '6', '5509', '0', '0', '0', '0', '0', '0', '0', 'd1d51188e1fb089f7ad442c257683417', '11', '4', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('77', 'Bổn Bổn Ngưu', '190', '190', '30', '49', '0', '0', '6', '1300', '0', '0', '0', '0', '0', '0', '0', '70619bdc2bdd92dcc83489657743848d', '13', '2', '6', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('78', 'Uy Uy Hổ', '-5591', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '70619bdc2bdd92dcc83489657743848d', '18', '5', '7', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('79', 'Uy Uy Hổ', '86', '100', '10', '4', '0', '0', '1', '195', '0', '0', '0', '0', '0', '0', '0', '8db89ecf31a6a27cf2d3f68c095843b9', '22', '4', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('80', 'Chiêu Tài Trư', '-466', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8db89ecf31a6a27cf2d3f68c095843b9', '20', '2', '8', '5', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('81', 'Hoa Hoa Xà', '128', '142', '26', '28', '0', '0', '3', '2114', '0', '0', '0', '0', '0', '0', '0', 'd580de813e3c794d847c3cd35dfe5fb0', '16', '4', '7', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('83', 'Uy Uy Hổ', '-1295', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'd580de813e3c794d847c3cd35dfe5fb0', '11', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('85', 'Đản Đản Kê', '-13297', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '22', '3', '3', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('86', 'Chiêu Tài Trư', '-13297', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '9', '3', '6', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('87', 'Chiêu Tài Trư', '-13297', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '23', '3', '6', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('88', 'Chiêu Tài Trư', '190', '400', '42', '120', '0', '0', '13', '24807', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '23', '2', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('89', 'Quai Quai Cẩu', '-13297', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '21', '5', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('90', 'Hoa Hoa Xà', '-13297', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '24', '2', '7', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('91', 'Quai Quai Cẩu', '-13297', '100', '17', '11', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '17', '3', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('92', 'Mị Mị Dương', '-13297', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '12', '5', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('93', 'Mị Mị Dương', '-13297', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '20', '4', '6', '5', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('94', 'Soái Soái Hầu', '-8743', '163', '32', '54', '0', '0', '8', '3728', '0', '0', '0', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '8', '3', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('95', 'Bổn Bổn Ngưu', '-4432', '496', '102', '162', '0', '0', '23', '69651', '0', '0', '0', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '18', '4', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('96', 'Soái Soái Hầu', '-1496', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b6d9f0bb1852a15b241410b3b07a21f8', '16', '2', '3', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('97', 'Khiêu Khiêu Thố', '-1923', '146', '16', '10', '0', '0', '3', '1076', '0', '0', '0', '0', '0', '0', '0', '08b46397b9704ed9b6230e3864dfde52', '23', '4', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('98', 'Quai Quai Cẩu', '-36539', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '18', '2', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('99', 'Mị Mị Dương', '-36539', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '18', '4', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('100', 'Linh Lỵ Thử', '-810', '100', '8', '2', '0', '0', '1', '119', '0', '0', '0', '0', '0', '0', '0', '2584819298592ea557f2c1d6ae08bbfe', '22', '2', '6', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('101', 'Soái Soái Hầu', '242', '271', '26', '65', '0', '0', '10', '11407', '0', '0', '0', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '16', '2', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('102', 'Quai Quai Cẩu', '-22793', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '23', '4', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('103', 'Lưu Lưu Mã', '-454', '151', '28', '28', '0', '0', '4', '43', '0', '0', '0', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '12', '3', '4', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('109', 'Khiêu Khiêu Thố', '-16253', '550', '123', '153', '0', '0', '19', '51490', '0', '0', '0', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '18', '4', '6', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('123', 'Hoa Hoa Xà', '236', '238', '24', '42', '0', '0', '7', '8324', '0', '0', '0', '0', '0', '0', '0', 'f991c4f27c28fb7eae2cbc3f80d00245', '19', '2', '5', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('124', 'Mị Mị Dương', '-49473', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '21', '2', '6', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('125', 'Linh Lỵ Thử', '-49473', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '8', '4', '8', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('131', 'Uy Uy Hổ', '-1580', '828', '147', '115', '0', '0', '27', '7721', '0', '0', '0', '0', '0', '0', '0', '111111', '23', '4', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('132', 'Chiêu Tài Trư', '-44407', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '111111', '23', '3', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('133', 'Quai Quai Cẩu', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '08b46397b9704ed9b6230e3864dfde52', '19', '4', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('134', 'Quai Quai Cẩu', '126', '130', '19', '15', '0', '0', '3', '2232', '0', '0', '0', '0', '0', '0', '0', '98e3c23a97294ce8109186ba55d8530f', '14', '5', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('135', 'Khiêu Khiêu Thố', '-5429', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '98e3c23a97294ce8109186ba55d8530f', '18', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('136', 'Khiêu Khiêu Thố', '148', '152', '15', '21', '0', '0', '3', '2212', '0', '0', '0', '0', '0', '0', '0', '77584b0fdec3e5bbf4f256da2fc3fc09', '22', '2', '7', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('137', 'Quai Quai Cẩu', '-776', '117', '22', '17', '0', '0', '2', '89', '0', '0', '0', '0', '0', '0', '0', 'b624121f7f5c40f8c6ac507f6cab45f5', '15', '2', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('150', 'Mị Mị Dương', '-1152', '564', '83', '141', '0', '0', '17', '23454', '0', '0', '0', '0', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '21', '3', '6', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('180', 'Khốc Khốc Long', '46', '100', '8', '2', '0', '0', '1', '318', '0', '0', '0', '0', '0', '0', '0', '7660cbcbcfec945cbdac430361da363f', '12', '4', '3', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('187', 'Chiêu Tài Trư', '160', '160', '37', '55', '0', '0', '5', '3491', '0', '0', '0', '0', '0', '0', '0', '72e2a2bb613ef6ed768ba27a20fbe7de', '11', '3', '7', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('188', 'Uy Uy Hổ', '94', '100', '19', '13', '0', '0', '1', '216', '0', '0', '0', '0', '0', '0', '0', 'a86db6a86e4d32c7b409d3822ed5bba2', '14', '2', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('189', 'Khiêu Khiêu Thố', '-38', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a86db6a86e4d32c7b409d3822ed5bba2', '14', '4', '8', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('190', 'Lưu Lưu Mã', '100', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '704825b240505b4599c32a64fb882c2c', '20', '3', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('200', 'Khiêu Khiêu Thố', '233', '235', '56', '77', '0', '0', '10', '1631', '0', '0', '0', '0', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '10', '3', '5', '5', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('205', 'Linh Lỵ Thử', '334', '386', '77', '71', '0', '0', '14', '20096', '0', '0', '0', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '22', '5', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('214', 'Mị Mị Dương', '100', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '15', '4', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('215', 'Lưu Lưu Mã', '100', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '19', '2', '6', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('216', 'Hoa Hoa Xà', '100', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '18', '2', '8', '5', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('217', 'Khốc Khốc Long', '100', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '16', '5', '5', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('228', 'Uy Uy Hổ', '-19481', '113', '18', '12', '0', '0', '2', '904', '0', '0', '0', '0', '0', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '10', '3', '3', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('232', 'Hoa Hoa Xà', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '14', '4', '7', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('238', 'Bổn Bổn Ngưu', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '16', '2', '5', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('261', 'Quai Quai Cẩu', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '087efeb819f49c1789df1f599ec15388', '16', '3', '3', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('265', 'Đản Đản Kê', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '923eb396db35867b31b310d8fb8260d2', '21', '5', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('266', 'Chiêu Tài Trư', '-25811212', '1519', '227', '436', '0', '0', '44', '10539', '0', '0', '0', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '25', '4', '8', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('274', 'Hoa Hoa Xà', '-25808763', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '20', '3', '3', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('278', 'Khốc Khốc Long', '584', '1526', '288', '374', '0', '0', '47', '68658', '0', '0', '0', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '22', '4', '6', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('298', 'Hoa Hoa Xà', '75', '180', '38', '32', '0', '0', '5', '5276', '0', '0', '0', '0', '0', '0', '0', 'd9bc42eda8a25d32618518f4ae68b92a', '17', '4', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('300', 'Soái Soái Hầu', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'f7fed9ff73c268ee01eaa000afd54d6f', '11', '3', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('301', 'Linh Lỵ Thử', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'f7fed9ff73c268ee01eaa000afd54d6f', '24', '2', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('302', 'Khốc Khốc Long', '-24519368', '287', '32', '59', '0', '0', '12', '15484', '0', '0', '0', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '14', '2', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('316', 'Khiêu Khiêu Thố', '-12413', '1572', '250', '428', '0', '0', '47', '588194', '0', '0', '0', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '21', '3', '6', '5', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('332', 'Soái Soái Hầu', '-13729', '364', '87', '105', '0', '0', '25', '23475', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '8', '2', '3', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('341', 'Bổn Bổn Ngưu', '-1921181', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '17', '2', '4', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('342', 'Quai Quai Cẩu', '-1921181', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '12', '3', '8', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('343', 'Khốc Khốc Long', '-1921181', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '9', '2', '4', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('347', 'Đản Đản Kê', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a1cf65d31ba46d90e465db5279329470', '10', '5', '3', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('352', 'Lưu Lưu Mã', '-1833581', '100', '17', '11', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '18', '2', '5', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('355', 'Đản Đản Kê', '-1833581', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '21', '5', '3', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('357', 'Đản Đản Kê', '-1833581', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '22', '3', '8', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('360', 'Linh Lỵ Thử', '-78084', '276', '31', '57', '0', '0', '9', '6255', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '20', '3', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('362', 'Khiêu Khiêu Thố', '10', '166', '19', '13', '0', '0', '4', '1099', '0', '0', '0', '0', '0', '0', '0', '6dd9bd98618400f882c43aa6444d4948', '20', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('371', 'Mị Mị Dương', '-472142', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '16', '5', '8', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('373', 'Mị Mị Dương', '-75400', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '16', '3', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('374', 'Mị Mị Dương', '-45555', '220', '91', '85', '0', '0', '13', '11154', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '8', '5', '5', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('376', 'Linh Lỵ Thử', '-44325', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '13', '3', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('377', 'Uy Uy Hổ', '-44325', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '16', '4', '8', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('378', 'Khiêu Khiêu Thố', '-722', '478', '77', '113', '0', '0', '15', '29548', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '21', '3', '5', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('382', 'Linh Lỵ Thử', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '25', '2', '6', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('383', 'Lưu Lưu Mã', '100', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '20', '2', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('384', 'Soái Soái Hầu', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '25', '4', '3', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('388', 'Bổn Bổn Ngưu', '100', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '25', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('389', 'Hoa Hoa Xà', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '17', '2', '3', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('390', 'Đản Đản Kê', '100', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '11', '5', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('391', 'Quai Quai Cẩu', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '11', '4', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('392', 'Linh Lỵ Thử', '100', '100', '20', '14', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '24', '4', '8', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('393', 'Quai Quai Cẩu', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '24', '2', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('394', 'Đản Đản Kê', '100', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '23', '5', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('397', 'Chiêu Tài Trư', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '17', '4', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('398', 'Uy Uy Hổ', '100', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '22', '5', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('399', 'Soái Soái Hầu', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '20', '3', '5', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('400', 'Khốc Khốc Long', '100', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '12', '5', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('402', 'Uy Uy Hổ', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '16', '3', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('403', 'Linh Lỵ Thử', '100', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '11', '5', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('404', 'Mị Mị Dương', '100', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '8', '5', '3', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('406', 'Bổn Bổn Ngưu', '100', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '22', '5', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('408', 'Quai Quai Cẩu', '100', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '24', '4', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('409', 'Linh Lỵ Thử', '100', '100', '17', '11', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '19', '5', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('412', 'Lưu Lưu Mã', '100', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '15', '5', '7', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('413', 'Uy Uy Hổ', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '23', '3', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('416', 'Uy Uy Hổ', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '16', '4', '7', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('417', 'Lưu Lưu Mã', '100', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '17', '3', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('418', 'Đản Đản Kê', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '9', '3', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('420', 'Soái Soái Hầu', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '21', '3', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('421', 'Khốc Khốc Long', '100', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '16', '5', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('423', 'Lưu Lưu Mã', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '8', '2', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('426', 'Bổn Bổn Ngưu', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '23', '4', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('428', 'Quai Quai Cẩu', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '19', '3', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('429', 'Khiêu Khiêu Thố', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '16', '3', '4', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('431', 'Hoa Hoa Xà', '100', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '25', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('433', 'Chiêu Tài Trư', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '12', '4', '8', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('434', 'Mị Mị Dương', '100', '100', '20', '14', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '25', '4', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('435', 'Bổn Bổn Ngưu', '100', '100', '17', '11', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '10', '3', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('438', 'Khiêu Khiêu Thố', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '22', '2', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('439', 'Đản Đản Kê', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '20', '5', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('440', 'Bổn Bổn Ngưu', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '11', '4', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('443', 'Quai Quai Cẩu', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '18', '4', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('444', 'Quai Quai Cẩu', '100', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '12', '4', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('445', 'Linh Lỵ Thử', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '25', '5', '5', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('448', 'Hoa Hoa Xà', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '24', '2', '8', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('450', 'Linh Lỵ Thử', '100', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '12', '3', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('451', 'Quai Quai Cẩu', '100', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '16', '4', '6', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('452', 'Soái Soái Hầu', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '15', '3', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('455', 'Soái Soái Hầu', '-161', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '12', '4', '6', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('456', 'Khiêu Khiêu Thố', '-161', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '24', '5', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('457', 'Khiêu Khiêu Thố', '-161', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '9', '2', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('458', 'Lưu Lưu Mã', '117', '131', '12', '13', '0', '0', '2', '210', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '22', '3', '8', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('459', 'Đản Đản Kê', '-161', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '20', '2', '5', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('460', 'Bổn Bổn Ngưu', '-161', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '12', '5', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('461', 'Chiêu Tài Trư', '-161', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '13', '5', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('463', 'Quai Quai Cẩu', '-161', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '20', '2', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('465', 'Lưu Lưu Mã', '100', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '13ca4b7ef4d57e20f442a787cf8c88ac', '23', '4', '7', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('471', 'Mị Mị Dương', '100', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '13ca4b7ef4d57e20f442a787cf8c88ac', '10', '4', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('473', 'Mị Mị Dương', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '13ca4b7ef4d57e20f442a787cf8c88ac', '21', '4', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('477', 'Bổn Bổn Ngưu', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '14', '5', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('478', 'Khốc Khốc Long', '100', '100', '19', '13', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '12', '5', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('479', 'Khiêu Khiêu Thố', '100', '100', '20', '14', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '16', '5', '3', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('480', 'Bổn Bổn Ngưu', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '11', '3', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('481', 'Khiêu Khiêu Thố', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '10', '4', '5', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('483', 'Khiêu Khiêu Thố', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '10', '5', '7', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('485', 'Đản Đản Kê', '100', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '25', '2', '7', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('486', 'Mị Mị Dương', '100', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '9', '2', '8', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('488', 'Chiêu Tài Trư', '100', '100', '13', '7', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '13', '4', '6', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('501', 'Khốc Khốc Long', '-3388', '148', '24', '18', '0', '0', '4', '2666', '0', '0', '0', '0', '0', '0', '0', '1ac81512a158e2622c17a49ba9b7eaf8', '13', '4', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('503', 'Đản Đản Kê', '-1078107', '205', '60', '40', '0', '0', '8', '5843', '0', '0', '0', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '14', '5', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('505', 'Bổn Bổn Ngưu', '-7673', '738', '171', '143', '0', '0', '23', '55595', '0', '0', '0', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '21', '5', '4', '4', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('508', 'Hoa Hoa Xà', '-1078125', '100', '11', '5', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '17', '3', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('509', 'Linh Lỵ Thử', '-1078125', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '16', '3', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('514', 'Khốc Khốc Long', '-2771', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '23', '4', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('515', 'Mị Mị Dương', '-2771', '100', '20', '14', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '23', '5', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('516', 'Soái Soái Hầu', '-2771', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '12', '5', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('517', 'Đản Đản Kê', '-2771', '100', '16', '10', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '20', '2', '5', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('518', 'Uy Uy Hổ', '-2747', '100', '16', '10', '0', '0', '1', '268', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '21', '3', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('519', 'Linh Lỵ Thử', '-2771', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '22', '2', '6', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('520', 'Soái Soái Hầu', '-2771', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '20', '4', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('521', 'Uy Uy Hổ', '100', '100', '8', '2', '0', '0', '1', '108', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '20', '5', '4', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('522', 'Khiêu Khiêu Thố', '-2723', '100', '6', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '23', '4', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('523', 'Chiêu Tài Trư', '100', '100', '8', '2', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'd7e14e0899708a60b1369af7ca3f7e40', '12', '2', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('524', 'Soái soái hầu', '-348', '100', '10', '4', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', 'fb36ea13a56ad413d9cccebca56bb546', '20', '3', '6', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('525', 'Chiêu tài trư', '58', '121', '14', '15', '0', '0', '2', '1287', '0', '0', '0', '0', '0', '0', '0', 'fb36ea13a56ad413d9cccebca56bb546', '16', '2', '8', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('531', 'Lưu lưu mã', '100', '100', '7', '1', '0', '0', '1', '126', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '13', '2', '7', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('530', 'Soái soái hầu', '-27001', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '9', '5', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('529', 'Hoa hoa xà', '208', '208', '35', '41', '0', '0', '13', '11284', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '8', '2', '3', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('532', 'Khốc khốc long', '100', '100', '15', '9', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '12', '3', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('533', 'Uy uy hổ', '100', '100', '18', '12', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '19', '5', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('534', 'Khiêu khiêu thố', '100', '100', '14', '8', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '9', '5', '7', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('535', 'Linh lỵ thử', '100', '100', '20', '14', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '11', '4', '4', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('536', 'Khốc khốc long', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '23', '3', '8', '1', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('537', 'Mị mị dương', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '22', '3', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('538', 'Uy uy hổ', '100', '100', '21', '15', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '10', '2', '4', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('539', 'Linh lỵ thử', '114', '114', '25', '23', '0', '0', '2', '7357', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '12', '5', '8', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('540', 'Đản đản kê', '100', '100', '9', '3', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '13', '3', '3', '3', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('541', 'Lưu lưu mã', '100', '100', '7', '1', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '14', '5', '6', '2', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('542', 'Bổn bổn ngưu', '100', '100', '12', '6', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '21', '2', '5', '0', '0', 'cwmaxexp');
INSERT INTO `playerchongwu` VALUES ('543', 'Khóc Khóc Long', '110', '110', '9', '6', '0', '2', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '11', '1', '1', '0', '5', '100');
INSERT INTO `playerchongwu` VALUES ('544', 'Linh Ly Thủy', '120', '120', '8', '5', '0', '2', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '12', '1', '1', '0', '1', '100');
INSERT INTO `playerchongwu` VALUES ('545', 'Hoa Hoa Xà', '100', '100', '10', '4', '0', '3', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '10', '1', '1', '0', '2', '100');
INSERT INTO `playerchongwu` VALUES ('546', 'Báo Báo Ngưu', '150', '150', '6', '8', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '15', '1', '1', '0', '3', '100');
INSERT INTO `playerchongwu` VALUES ('547', 'Linh Ly Thủy', '120', '120', '8', '5', '0', '2', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '12', '1', '1', '0', '1', '100');
INSERT INTO `playerchongwu` VALUES ('548', 'Hoa Hoa Xà', '100', '100', '10', '4', '0', '3', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '10', '1', '1', '0', '2', '100');
INSERT INTO `playerchongwu` VALUES ('549', 'Hoa Hoa Xà', '100', '100', '10', '4', '0', '3', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '10', '1', '1', '0', '2', '100');
INSERT INTO `playerchongwu` VALUES ('550', 'Báo Báo Ngưu', '150', '150', '6', '8', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '15', '1', '1', '0', '3', '100');
INSERT INTO `playerchongwu` VALUES ('551', 'Khóc Khóc Long', '110', '110', '9', '6', '0', '2', '1', '0', '0', '0', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '11', '1', '1', '0', '5', '100');

-- ----------------------------
-- Table structure for `playercongphap`
-- ----------------------------
DROP TABLE IF EXISTS `playercongphap`;
CREATE TABLE `playercongphap` (
  `cpid` int(11) NOT NULL,
  `sid` varchar(32) NOT NULL,
  `cpdj` int(11) DEFAULT '1',
  `cpexp` int(11) DEFAULT '0',
  `cpexpmax` int(11) DEFAULT '100',
  `cpequipped` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`cpid`,`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of playercongphap
-- ----------------------------
INSERT INTO `playercongphap` VALUES ('11', '8d0329755724609ccb9713953eee47f4', '10', '1050', '1050', '1');
INSERT INTO `playercongphap` VALUES ('12', '8d0329755724609ccb9713953eee47f4', '1', '0', '100', '0');

-- ----------------------------
-- Table structure for `playerdaoju`
-- ----------------------------
DROP TABLE IF EXISTS `playerdaoju`;
CREATE TABLE `playerdaoju` (
  `djname` varchar(255) NOT NULL,
  `djzl` varchar(255) NOT NULL,
  `djinfo` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` text NOT NULL,
  `djsum` int(11) NOT NULL,
  `djid` int(11) NOT NULL,
  `djused` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`djid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerdaoju
-- ----------------------------
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '0', '111111', '346', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '0', '111111', '150', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '419', '087efeb819f49c1789df1f599ec15388', '0', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '422', '771086b453fb6d53d76bf46880cf4b41', '505', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '422', '771086b453fb6d53d76bf46880cf4b41', '6', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '426', 'a2377a650da6bb0ad4fe0514f971f858', '10', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '426', 'a2377a650da6bb0ad4fe0514f971f858', '1', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Trúc Cơ Đột Phá Đan', '20', 'Đột phá trúc cơ kỳ lúc sử dụng mấu chốt nội đan', '426', 'a2377a650da6bb0ad4fe0514f971f858', '28', '20', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '427', '70619bdc2bdd92dcc83489657743848d', '13', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '427', '70619bdc2bdd92dcc83489657743848d', '2', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '427', '70619bdc2bdd92dcc83489657743848d', '2', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '431', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '20', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '438', 'b624121f7f5c40f8c6ac507f6cab45f5', '230', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '440', 'b3897bd53e13f8c92b92933d4e9b7167', '50', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '441', '111111', '347', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '441', '111111', '151', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '441', '111111', '6', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '441', '111111', '6', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '442', '08b46397b9704ed9b6230e3864dfde52', '395', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '442', '08b46397b9704ed9b6230e3864dfde52', '0', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '442', '08b46397b9704ed9b6230e3864dfde52', '1', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '448', 'a0f5700982d69976ce54c08cb1c2bbb0', '200', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '448', 'a0f5700982d69976ce54c08cb1c2bbb0', '100', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '448', 'a0f5700982d69976ce54c08cb1c2bbb0', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '450', '1d75409bd985acf3644f25f2a87b6552', '4', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '457', 'e8d7c6fb768be55653f7cb2f7164bd1d', '280', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '457', 'e8d7c6fb768be55653f7cb2f7164bd1d', '100', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '457', 'e8d7c6fb768be55653f7cb2f7164bd1d', '52', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '457', 'e8d7c6fb768be55653f7cb2f7164bd1d', '1', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '458', '4e8fe201aac55b7ddf0a721fe2dde89d', '19', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '459', '39f73d46e13bed758f42d5695f3f43f0', '148', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '459', '39f73d46e13bed758f42d5695f3f43f0', '150', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '459', '39f73d46e13bed758f42d5695f3f43f0', '150', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '459', '39f73d46e13bed758f42d5695f3f43f0', '5', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '462', 'a86db6a86e4d32c7b409d3822ed5bba2', '6', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '463', '7083fa1a1d41824363cd4df0ef5e5d90', '51', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '463', '7083fa1a1d41824363cd4df0ef5e5d90', '2', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '464', '54b9e0e2999c06a210aff10c2bd70578', '50', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '465', '38171773e6842eb588ccc4ec4a7f48a8', '74', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '465', '38171773e6842eb588ccc4ec4a7f48a8', '3', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '465', '38171773e6842eb588ccc4ec4a7f48a8', '2', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '467', '043c4eac8ffaa70af1ccb3e0eef0a364', '284', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '467', '043c4eac8ffaa70af1ccb3e0eef0a364', '100', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '467', '043c4eac8ffaa70af1ccb3e0eef0a364', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ti Cân', '13', 'Thiếp thân trang bị', '467', '043c4eac8ffaa70af1ccb3e0eef0a364', '2', '13', '0');
INSERT INTO `playerdaoju` VALUES ('Thuẫn Thổ Tàn Chương', '15', 'Có thể thu được thuẫn thổ thuật', '467', '043c4eac8ffaa70af1ccb3e0eef0a364', '1', '15', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '468', 'b8a188334584e024060c33c6b3a9bbc5', '419', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '468', 'b8a188334584e024060c33c6b3a9bbc5', '44', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '468', 'b8a188334584e024060c33c6b3a9bbc5', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '468', 'b8a188334584e024060c33c6b3a9bbc5', '6', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '468', 'b8a188334584e024060c33c6b3a9bbc5', '1', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '469', '8090f13824dc62c1bdd7b3db5a101fd7', '55', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '470', 'd1d51188e1fb089f7ad442c257683417', '38', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '470', 'd1d51188e1fb089f7ad442c257683417', '3', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '473', '8db89ecf31a6a27cf2d3f68c095843b9', '17', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '473', '8db89ecf31a6a27cf2d3f68c095843b9', '2', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '475', 'd580de813e3c794d847c3cd35dfe5fb0', '9', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '475', 'd580de813e3c794d847c3cd35dfe5fb0', '100', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '475', 'd580de813e3c794d847c3cd35dfe5fb0', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '475', 'd580de813e3c794d847c3cd35dfe5fb0', '5', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '476', '0b82a8fd07dfafc237a5dcc15b332ce8', '14', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '479', 'a1cf65d31ba46d90e465db5279329470', '10', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '480', 'cb5927f98eb22d2870b0547c6ab6f7f1', '1', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '481', 'f52f24af276734367a122e13b0af67d2', '264', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '481', 'f52f24af276734367a122e13b0af67d2', '0', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '481', 'f52f24af276734367a122e13b0af67d2', '4', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '481', 'f52f24af276734367a122e13b0af67d2', '7', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '482', '8361f38084bda036106506df54fcd779', '62', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '483', 'b6d9f0bb1852a15b241410b3b07a21f8', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '484', '2584819298592ea557f2c1d6ae08bbfe', '105', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '485', '929610ec747f6e087aebb1af550673fa', '64', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '485', '929610ec747f6e087aebb1af550673fa', '2', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '485', '929610ec747f6e087aebb1af550673fa', '2', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Tàn Mặc', '14', 'Có thể đổi lấy tập tranh', '485', '929610ec747f6e087aebb1af550673fa', '2', '14', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '487', 'f991c4f27c28fb7eae2cbc3f80d00245', '296', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '487', 'f991c4f27c28fb7eae2cbc3f80d00245', '300', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '487', 'f991c4f27c28fb7eae2cbc3f80d00245', '300', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '487', 'f991c4f27c28fb7eae2cbc3f80d00245', '1', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '488', '98e3c23a97294ce8109186ba55d8530f', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '488', '98e3c23a97294ce8109186ba55d8530f', '2', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '489', '77584b0fdec3e5bbf4f256da2fc3fc09', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '492', '1d3b7593022084ce21637b975b82a533', '397', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '492', '1d3b7593022084ce21637b975b82a533', '135', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '492', '1d3b7593022084ce21637b975b82a533', '151', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '494', 'fce6f2da3b931558b5ec44dfa71b0646', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '498', '3fbbf11bd57912b4677e195e091fc41a', '3', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '503', 'ef7cd5b1db4562a2841a3e84f17e6ce6', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '504', '6f6b750a6a23e083c483f9f2885440e0', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '505', '7660cbcbcfec945cbdac430361da363f', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '506', '72e2a2bb613ef6ed768ba27a20fbe7de', '2', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '506', '72e2a2bb613ef6ed768ba27a20fbe7de', '1', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '509', 'ded62d071f32ba11a20f5c75cc36153f', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '510', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '361', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '514', 'af976a314459ca05fc3563f0d6fd7e39', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '515', '0d471de3efe5dcf749e6fa60fde8ae76', '19', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '515', '0d471de3efe5dcf749e6fa60fde8ae76', '151', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '515', '0d471de3efe5dcf749e6fa60fde8ae76', '152', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '515', '0d471de3efe5dcf749e6fa60fde8ae76', '10', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '516', '923eb396db35867b31b310d8fb8260d2', '200', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '517', 'acea4cf5bfd750233ebc90c1361cd568', '52', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '517', 'acea4cf5bfd750233ebc90c1361cd568', '0', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '517', 'acea4cf5bfd750233ebc90c1361cd568', '6', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '517', 'acea4cf5bfd750233ebc90c1361cd568', '10', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '517', 'acea4cf5bfd750233ebc90c1361cd568', '6', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '10', 'Phù lục tàn trang - trung cấp linh', '517', 'acea4cf5bfd750233ebc90c1361cd568', '2', '10', '0');
INSERT INTO `playerdaoju` VALUES ('[Thần Khí] Yêu Vương Kiếm Toái Phiến', '12', '[ thần khí ] yêu vương kiếm mảnh', '517', 'acea4cf5bfd750233ebc90c1361cd568', '7', '12', '0');
INSERT INTO `playerdaoju` VALUES ('Thuẫn Thổ Tàn Chương', '15', 'Có thể thu được thuẫn thổ thuật', '517', 'acea4cf5bfd750233ebc90c1361cd568', '1', '15', '0');
INSERT INTO `playerdaoju` VALUES ('Trúc Cơ Đột Phá Đan', '', 'Đột phá trúc cơ kỳ lúc sử dụng mấu chốt nội đan', '517', 'acea4cf5bfd750233ebc90c1361cd568', '1', '20', '0');
INSERT INTO `playerdaoju` VALUES ('Kim Đan Đột Phá Đan', '21', 'Đột phá kim đan kỳ lúc sử dụng mấu chốt nội đan', '517', 'acea4cf5bfd750233ebc90c1361cd568', '0', '21', '0');
INSERT INTO `playerdaoju` VALUES ('Nguyên Anh Đột Phá Đan', '22', 'Đột phá nguyên anh kỳ lúc sử dụng mấu chốt nội đan', '517', 'acea4cf5bfd750233ebc90c1361cd568', '5', '22', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '518', 'd9bc42eda8a25d32618518f4ae68b92a', '163', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '518', 'd9bc42eda8a25d32618518f4ae68b92a', '102', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '518', 'd9bc42eda8a25d32618518f4ae68b92a', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '518', 'd9bc42eda8a25d32618518f4ae68b92a', '4', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Sơ Cấp Tàn Hiệt Phù Lục', '', 'Đổi đồ vật', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '8', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '11', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '4', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '10', 'Phù lục tàn trang - trung cấp linh', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '10', '0');
INSERT INTO `playerdaoju` VALUES ('[Thần Khí] Yêu Vương Kiếm Toái Phiến', '12', '[ thần khí ] yêu vương kiếm mảnh', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '12', '0');
INSERT INTO `playerdaoju` VALUES ('Thuẫn Thổ Tàn Chương', '15', 'Có thể thu được thuẫn thổ thuật', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '2', '15', '0');
INSERT INTO `playerdaoju` VALUES ('Nguyên Anh Đột Phá Đan', '22', 'Đột phá nguyên anh kỳ lúc sử dụng mấu chốt nội đan', '520', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '22', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '521', '454ac9cbdb63df840643655755078a94', '0', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '521', '454ac9cbdb63df840643655755078a94', '125', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '521', '454ac9cbdb63df840643655755078a94', '152', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '', 'Cứng rắn cánh ong mật ong', '521', '454ac9cbdb63df840643655755078a94', '7', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '521', '454ac9cbdb63df840643655755078a94', '100', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '10', 'Phù lục tàn trang - trung cấp linh', '521', '454ac9cbdb63df840643655755078a94', '50', '10', '0');
INSERT INTO `playerdaoju` VALUES ('Ti Cân', '13', 'Thiếp thân trang bị', '521', '454ac9cbdb63df840643655755078a94', '2', '13', '0');
INSERT INTO `playerdaoju` VALUES ('Tàn Mặc', '14', 'Có thể đổi lấy tập tranh', '521', '454ac9cbdb63df840643655755078a94', '2', '14', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '522', '0e975728648b4af97a3eb0dfaf7e303d', '0', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '522', '0e975728648b4af97a3eb0dfaf7e303d', '145', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '522', '0e975728648b4af97a3eb0dfaf7e303d', '134', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '522', '0e975728648b4af97a3eb0dfaf7e303d', '5', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '522', '0e975728648b4af97a3eb0dfaf7e303d', '100', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '10', 'Phù lục tàn trang - trung cấp linh', '522', '0e975728648b4af97a3eb0dfaf7e303d', '50', '10', '0');
INSERT INTO `playerdaoju` VALUES ('[Thần Khí] Yêu Vương Kiếm Toái Phiến', '', '[ thần khí ] yêu vương kiếm mảnh', '522', '0e975728648b4af97a3eb0dfaf7e303d', '0', '12', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '525', '1266218ff94820df0b66c6ef60d0541f', '0', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '526', '6dd9bd98618400f882c43aa6444d4948', '107', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '526', '6dd9bd98618400f882c43aa6444d4948', '2', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '527', 'a76e665e8322cccdfd421deb60d63a3b', '759', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '528', 'b83a15ae83b0f866411ae82d4da8b32a', '7', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '529', '214efa2220dcfa302d616be486c7788b', '150', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '530', '69a7a5ae8235caef07c21001c326fa75', '376', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '531', 'fc77607e4a6912b43841336a2b25f3f2', '151', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '532', '9a6da705ba923ca7752d634d2826caff', '150', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '533', 'eb66dc52f48cb910d5cf8243ba14a4db', '30', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '533', 'eb66dc52f48cb910d5cf8243ba14a4db', '100', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '533', 'eb66dc52f48cb910d5cf8243ba14a4db', '100', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '533', 'eb66dc52f48cb910d5cf8243ba14a4db', '9', '8', '0');
INSERT INTO `playerdaoju` VALUES ('[Thần Khí] Yêu Vương Kiếm Toái Phiến', '', '[ thần khí ] yêu vương kiếm mảnh', '533', 'eb66dc52f48cb910d5cf8243ba14a4db', '2', '12', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '534', '13ca4b7ef4d57e20f442a787cf8c88ac', '379', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '535', '4ada08fae36224a94fc8fade80bab37b', '376', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị dùng vật phẩm', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '1157', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '150', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '150', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '4', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '100', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '', 'Phù lục tàn trang - trung cấp linh', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '101', '10', '0');
INSERT INTO `playerdaoju` VALUES ('Ti Cân', '13', 'Thiếp thân trang bị', '538', '1ac81512a158e2622c17a49ba9b7eaf8', '1', '13', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '1', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đổi phù lục dùng', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '0', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đổi phù lục', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '6', '7', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Cứng rắn cánh ong mật ong', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '5', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Man', '9', 'Phù lục tàn trang - sơ cấp man', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '7', '9', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Trung Cấp Linh', '10', 'Phù lục tàn trang - trung cấp linh', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '0', '10', '0');
INSERT INTO `playerdaoju` VALUES ('[Thần Khí] Yêu Vương Kiếm Toái Phiến', '12', '[ thần khí ] yêu vương kiếm mảnh', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '5', '12', '0');
INSERT INTO `playerdaoju` VALUES ('Tàn Mặc', '14', 'Có thể đổi lấy tập tranh', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '2', '14', '0');
INSERT INTO `playerdaoju` VALUES ('Thuẫn Thổ Tàn Chương', '15', 'Có thể thu được thuẫn thổ thuật', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '5', '15', '0');
INSERT INTO `playerdaoju` VALUES ('Trúc Cơ Đột Phá Đan', '', 'Đột phá trúc cơ kỳ lúc sử dụng mấu chốt nội đan', '539', 'be7f41af7f2b397af5129fe5dec95e1a', '30', '20', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị dùng vật phẩm', '541', 'e17309aac194beac068b4383e5575918', '100', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '2', 'Cường hóa trang bị đạo cụ', '544', 'fb36ea13a56ad413d9cccebca56bb546', '122', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Ngạnh Sí Phong Mật', '8', 'Ngạnh sí phong phong mật', '547', '8d0329755724609ccb9713953eee47f4', '5', '8', '0');
INSERT INTO `playerdaoju` VALUES ('Cường Hóa Thạch', '', 'Cường hóa trang bị đạo cụ', '547', '8d0329755724609ccb9713953eee47f4', '171', '1', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Linh', '6', 'Đoái hoán phù lục', '547', '8d0329755724609ccb9713953eee47f4', '150', '6', '0');
INSERT INTO `playerdaoju` VALUES ('Phù Lục Tàn Hiệt - Sơ Cấp Ma', '7', 'Đoái hoán phù lục', '547', '8d0329755724609ccb9713953eee47f4', '150', '7', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '93', '38', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '392', '36', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '390', '34', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '392', '32', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '391', '35', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '392', '33', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '2', '109', '3');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '112', '1');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '38', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '113', '1');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '3', '8', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '112', '1');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '0', '111', '3');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '2', '30', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '3', '61', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '3', '54', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '1', '59', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '1', '64', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '1', '50', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '3', '58', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '1', '63', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '11', '70', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '0', '71', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '10', '72', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '9', '1', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '27', '30', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '10', '33', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '17', '32', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '20', '36', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '20', '100', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '17', '101', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '20', '102', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '0', '8d0329755724609ccb9713953eee47f4', '39', '37', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '1', '31', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '22', '22', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '545', 'dc4996fe7860595b1bb92db3c2af9fab', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '544', 'fb36ea13a56ad413d9cccebca56bb546', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990113', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '547', '8d0329755724609ccb9713953eee47f4', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '304', '112', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '0', '110', '1');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '3', '101', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '0', '109', '1');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '31', '100', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '1', '111', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '1', '37', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '4', '34', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990125', '2b06d0ff01b346e5f2a0666ab83dc044', '75', '32', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '548', '40e5df6609a8fd1b15904b9aa798a877', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990144', '6b7766a40825443262489376feafd063', '1', '8', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '543', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '550', '1b3246e8f45e4276967d00ad79cab118', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '551', '378bf7fdd60f2d81207e58ad6da4aaca', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '546', '4c149ae65f7dab8474cd47dbb3b17677', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '552', '6c4666ee50d00945c0251d14ca2275f2', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '549', '7841cebe57141c476b1d1d2272b65e53', '1', '114', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990144', '6b7766a40825443262489376feafd063', '10', '1', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990140', 'f91190a62d0f3267f85d972e4ba496d3', '10', '1', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990136', '03bb973718e9c9e68ad90317c5efdf68', '10', '1', '0');
INSERT INTO `playerdaoju` VALUES ('', '', '', '990141', '062aefa38dfc651e9590f98b7f7363a8', '2', '8', '0');

-- ----------------------------
-- Table structure for `playerjineng`
-- ----------------------------
DROP TABLE IF EXISTS `playerjineng`;
CREATE TABLE `playerjineng` (
  `jnname` varchar(255) NOT NULL,
  `jnid` int(11) NOT NULL,
  `jngj` int(11) NOT NULL,
  `jnfy` int(11) NOT NULL,
  `jnbj` int(11) NOT NULL,
  `jnxx` int(11) NOT NULL,
  `sid` text NOT NULL,
  `jncount` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerjineng
-- ----------------------------
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'c9879e4c3738297c06b0b14d78dc39d6', '15');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'c9879e4c3738297c06b0b14d78dc39d6', '25');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '781a121e409741ff53f5978578067146', '12');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'eb61a0b08b9bfdfda961d64410eba5bc', '3');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'eb61a0b08b9bfdfda961d64410eba5bc', '2');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '77099dca8f67b65ca91af53d0461ac2f', '0');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '77099dca8f67b65ca91af53d0461ac2f', '0');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '775c0ff651b405a676fc9ee1729302f4', '10');
INSERT INTO `playerjineng` VALUES ('Man Lực Phụ Thể', '6', '7', '7', '7', '7', '775c0ff651b405a676fc9ee1729302f4', '23');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'ad8f4d8e577f50deae4492bd03c96b56', '2');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'ad8f4d8e577f50deae4492bd03c96b56', '3');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'bb2a45b7652a7900e7810128a329597e', '2');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'bb2a45b7652a7900e7810128a329597e', '0');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '4e079b4084dcdd84cf4393a003a38283', '2');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'ce13ba86e509d2fab5ed13c2cddf00c9', '2');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'ce13ba86e509d2fab5ed13c2cddf00c9', '8');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'ea5a17b7f6c42320f7c242f2871a0f8d', '0');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'dd58b4170ec8d6d20f876405b4b1265d', '0');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'dd58b4170ec8d6d20f876405b4b1265d', '0');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '7159624dfdba178c848da03a6ffefec9', '2');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '7159624dfdba178c848da03a6ffefec9', '3');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '771086b453fb6d53d76bf46880cf4b41', '2');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '771086b453fb6d53d76bf46880cf4b41', '2');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'e8d7c6fb768be55653f7cb2f7164bd1d', '6');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'b8a188334584e024060c33c6b3a9bbc5', '5');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'f52f24af276734367a122e13b0af67d2', '20');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'f52f24af276734367a122e13b0af67d2', '8');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '111111', '16');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '1d3b7593022084ce21637b975b82a533', '3');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'acea4cf5bfd750233ebc90c1361cd568', '8');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'acea4cf5bfd750233ebc90c1361cd568', '0');
INSERT INTO `playerjineng` VALUES ('Nộ Huyết Bạo', '7', '12', '2', '20', '8', 'acea4cf5bfd750233ebc90c1361cd568', '4');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'c1a90c4939ae6e0353f41d09c2b62421', '0');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'c1a90c4939ae6e0353f41d09c2b62421', '0');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '454ac9cbdb63df840643655755078a94', '4');
INSERT INTO `playerjineng` VALUES ('Man Lực Phụ Thể', '6', '7', '7', '7', '7', 'c1a90c4939ae6e0353f41d09c2b62421', '12');
INSERT INTO `playerjineng` VALUES ('Nộ Huyết Bạo', '7', '12', '2', '20', '8', 'c1a90c4939ae6e0353f41d09c2b62421', '5');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', '0e975728648b4af97a3eb0dfaf7e303d', '1');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', '0e975728648b4af97a3eb0dfaf7e303d', '1');
INSERT INTO `playerjineng` VALUES ('Man Lực Phụ Thể', '6', '7', '7', '7', '7', 'acea4cf5bfd750233ebc90c1361cd568', '12');
INSERT INTO `playerjineng` VALUES ('Tụ Linh Trảm', '4', '10', '0', '0', '2', 'be7f41af7f2b397af5129fe5dec95e1a', '10');
INSERT INTO `playerjineng` VALUES ('Ma Tâm Bạo Phát', '5', '5', '0', '10', '10', 'be7f41af7f2b397af5129fe5dec95e1a', '5');
INSERT INTO `playerjineng` VALUES ('Man Lực Phụ Thể', '6', '7', '7', '7', '7', 'be7f41af7f2b397af5129fe5dec95e1a', '10');
INSERT INTO `playerjineng` VALUES ('Độn Địa Thuật', '9', '0', '12', '0', '30', 'be7f41af7f2b397af5129fe5dec95e1a', '5');

-- ----------------------------
-- Table structure for `playerrenwu`
-- ----------------------------
DROP TABLE IF EXISTS `playerrenwu`;
CREATE TABLE `playerrenwu` (
  `rwname` varchar(255) NOT NULL,
  `rwzl` int(11) NOT NULL,
  `rwdj` varchar(255) NOT NULL,
  `rwzb` varchar(255) NOT NULL,
  `rwexp` varchar(255) NOT NULL,
  `rwyxb` varchar(255) NOT NULL,
  `sid` text NOT NULL,
  `rwzt` int(11) NOT NULL,
  `rwid` int(11) NOT NULL,
  `rwyq` int(11) NOT NULL,
  `rwcount` int(11) NOT NULL,
  `rwnowcount` int(11) NOT NULL,
  `rwlx` int(11) NOT NULL,
  `rwyp` text NOT NULL,
  `data` int(11) NOT NULL,
  `rwjineng` varchar(255) NOT NULL,
  `nid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerrenwu
-- ----------------------------
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '929610ec747f6e087aebb1af550673fa', '1', '23', '55', '20', '1', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', '98e3c23a97294ce8109186ba55d8530f', '2', '13', '56', '5', '5', '2', '', '10', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '929610ec747f6e087aebb1af550673fa', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '929610ec747f6e087aebb1af550673fa', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '929610ec747f6e087aebb1af550673fa', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '39f73d46e13bed758f42d5695f3f43f0', '1', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'a2377a650da6bb0ad4fe0514f971f858', '1', '23', '55', '20', '1', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '39f73d46e13bed758f42d5695f3f43f0', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '39f73d46e13bed758f42d5695f3f43f0', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '39f73d46e13bed758f42d5695f3f43f0', '1', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '39f73d46e13bed758f42d5695f3f43f0', '1', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '98e3c23a97294ce8109186ba55d8530f', '1', '23', '55', '20', '6', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '111111', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '111111', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '111111', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '111111', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '111111', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '111111', '1', '30', '98', '10', '1', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '111111', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '111111', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '111111', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '111111', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '111111', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '111111', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '111111', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '111111', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '98e3c23a97294ce8109186ba55d8530f', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '77584b0fdec3e5bbf4f256da2fc3fc09', '1', '23', '55', '20', '4', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '77584b0fdec3e5bbf4f256da2fc3fc09', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', 'a86db6a86e4d32c7b409d3822ed5bba2', '2', '13', '56', '5', '5', '2', '', '3', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'a86db6a86e4d32c7b409d3822ed5bba2', '1', '23', '55', '20', '3', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'a86db6a86e4d32c7b409d3822ed5bba2', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '1d3b7593022084ce21637b975b82a533', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '1d3b7593022084ce21637b975b82a533', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', '1d3b7593022084ce21637b975b82a533', '3', '13', '56', '5', '0', '2', '', '13', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '4721b25faef7b79a4e9062b0856cee1e', '1', '23', '55', '20', '1', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '1d3b7593022084ce21637b975b82a533', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '1d3b7593022084ce21637b975b82a533', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '1d3b7593022084ce21637b975b82a533', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '1d3b7593022084ce21637b975b82a533', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '1d3b7593022084ce21637b975b82a533', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '1d3b7593022084ce21637b975b82a533', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '1d3b7593022084ce21637b975b82a533', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '1d3b7593022084ce21637b975b82a533', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '1d3b7593022084ce21637b975b82a533', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', '1d3b7593022084ce21637b975b82a533', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tầm lão bạn', '3', '1|20', '', '500', '500', '1d3b7593022084ce21637b975b82a533', '3', '33', '28', '11', '0', '2', '', '13', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '1d3b7593022084ce21637b975b82a533', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '1d3b7593022084ce21637b975b82a533', '1', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '1d3b7593022084ce21637b975b82a533', '1', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Xích Lân Thú da', '2', '1|10,10|50', '39', '800', '350', '1d3b7593022084ce21637b975b82a533', '1', '21', '82', '30', '0', '2', '', '13', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', '1d3b7593022084ce21637b975b82a533', '1', '19', '76', '50', '18', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', '1d3b7593022084ce21637b975b82a533', '1', '20', '77', '50', '7', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '7660cbcbcfec945cbdac430361da363f', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '1d05b0d3a5ffa94de8a51fa17e2a55ea', '1', '23', '55', '20', '2', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '1d05b0d3a5ffa94de8a51fa17e2a55ea', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', '1d05b0d3a5ffa94de8a51fa17e2a55ea', '1', '13', '56', '5', '0', '2', '', '8', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', '0d471de3efe5dcf749e6fa60fde8ae76', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'a2377a650da6bb0ad4fe0514f971f858', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '13', '56', '5', '0', '2', '', '10', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', '0d471de3efe5dcf749e6fa60fde8ae76', '1', '20', '77', '50', '20', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', '0d471de3efe5dcf749e6fa60fde8ae76', '1', '19', '76', '50', '39', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Xích Lân Thú da', '2', '1|10,10|50', '39', '800', '350', '0d471de3efe5dcf749e6fa60fde8ae76', '1', '21', '82', '30', '1', '2', '', '10', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '0d471de3efe5dcf749e6fa60fde8ae76', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'acea4cf5bfd750233ebc90c1361cd568', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'acea4cf5bfd750233ebc90c1361cd568', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', 'acea4cf5bfd750233ebc90c1361cd568', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', 'acea4cf5bfd750233ebc90c1361cd568', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', 'acea4cf5bfd750233ebc90c1361cd568', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', 'acea4cf5bfd750233ebc90c1361cd568', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', 'acea4cf5bfd750233ebc90c1361cd568', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', 'acea4cf5bfd750233ebc90c1361cd568', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', 'acea4cf5bfd750233ebc90c1361cd568', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', 'acea4cf5bfd750233ebc90c1361cd568', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', 'acea4cf5bfd750233ebc90c1361cd568', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', 'acea4cf5bfd750233ebc90c1361cd568', '1', '27', '12', '150', '7', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', 'acea4cf5bfd750233ebc90c1361cd568', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', 'acea4cf5bfd750233ebc90c1361cd568', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', 'acea4cf5bfd750233ebc90c1361cd568', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', 'acea4cf5bfd750233ebc90c1361cd568', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', 'a2377a650da6bb0ad4fe0514f971f858', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', 'acea4cf5bfd750233ebc90c1361cd568', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '0e975728648b4af97a3eb0dfaf7e303d', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '13', '56', '5', '0', '2', '', '18', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'f7fed9ff73c268ee01eaa000afd54d6f', '1', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '69a7a5ae8235caef07c21001c326fa75', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', 'c1a90c4939ae6e0353f41d09c2b62421', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '0e975728648b4af97a3eb0dfaf7e303d', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', 'c1a90c4939ae6e0353f41d09c2b62421', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '0e975728648b4af97a3eb0dfaf7e303d', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', '0e975728648b4af97a3eb0dfaf7e303d', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', 'c1a90c4939ae6e0353f41d09c2b62421', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '454ac9cbdb63df840643655755078a94', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '0e975728648b4af97a3eb0dfaf7e303d', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '454ac9cbdb63df840643655755078a94', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '454ac9cbdb63df840643655755078a94', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '454ac9cbdb63df840643655755078a94', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '454ac9cbdb63df840643655755078a94', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '454ac9cbdb63df840643655755078a94', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '454ac9cbdb63df840643655755078a94', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '454ac9cbdb63df840643655755078a94', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '454ac9cbdb63df840643655755078a94', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '454ac9cbdb63df840643655755078a94', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '454ac9cbdb63df840643655755078a94', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', '454ac9cbdb63df840643655755078a94', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', '454ac9cbdb63df840643655755078a94', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', '454ac9cbdb63df840643655755078a94', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Xích Lân Thú da', '2', '1|10,10|50', '39', '800', '350', '454ac9cbdb63df840643655755078a94', '1', '21', '82', '30', '0', '2', '', '19', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '454ac9cbdb63df840643655755078a94', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '454ac9cbdb63df840643655755078a94', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '454ac9cbdb63df840643655755078a94', '1', '31', '14', '10', '2', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '0e975728648b4af97a3eb0dfaf7e303d', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '0e975728648b4af97a3eb0dfaf7e303d', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', 'd9bc42eda8a25d32618518f4ae68b92a', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', 'd9bc42eda8a25d32618518f4ae68b92a', '1', '35', '66', '15', '9', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '0e975728648b4af97a3eb0dfaf7e303d', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', '0e975728648b4af97a3eb0dfaf7e303d', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', '0e975728648b4af97a3eb0dfaf7e303d', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'a76e665e8322cccdfd421deb60d63a3b', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '0e975728648b4af97a3eb0dfaf7e303d', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '0e975728648b4af97a3eb0dfaf7e303d', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '0e975728648b4af97a3eb0dfaf7e303d', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '0e975728648b4af97a3eb0dfaf7e303d', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '0e975728648b4af97a3eb0dfaf7e303d', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '214efa2220dcfa302d616be486c7788b', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '0e975728648b4af97a3eb0dfaf7e303d', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'b83a15ae83b0f866411ae82d4da8b32a', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '0e975728648b4af97a3eb0dfaf7e303d', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'fc77607e4a6912b43841336a2b25f3f2', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '9a6da705ba923ca7752d634d2826caff', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'eb66dc52f48cb910d5cf8243ba14a4db', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '13ca4b7ef4d57e20f442a787cf8c88ac', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '4ada08fae36224a94fc8fade80bab37b', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '13', '56', '5', '0', '2', '', '21', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'eb66dc52f48cb910d5cf8243ba14a4db', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', 'eb66dc52f48cb910d5cf8243ba14a4db', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', 'eb66dc52f48cb910d5cf8243ba14a4db', '1', '29', '62', '10', '2', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', '1ac81512a158e2622c17a49ba9b7eaf8', '1', '27', '12', '150', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Xích Lân Thú da', '2', '1|10,10|50', '39', '800', '350', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '21', '82', '30', '0', '2', '', '21', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', '1ac81512a158e2622c17a49ba9b7eaf8', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '34', '18', '9', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương đại mụ', '3', '1|20', '25', '200', '100', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Sói hoạn', '2', '1|100', '', '400', '300', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Đánh giết máu Lôi Ưng', '2', '1|50', '', '500', '1000', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '35', '66', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '36', '9', '15', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', '1ac81512a158e2622c17a49ba9b7eaf8', '1', '30', '98', '10', '3', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', '1ac81512a158e2622c17a49ba9b7eaf8', '1', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', '1ac81512a158e2622c17a49ba9b7eaf8', '1', '31', '14', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tầm lão bạn', '3', '1|20', '', '500', '500', '1ac81512a158e2622c17a49ba9b7eaf8', '2', '33', '28', '11', '0', '2', '', '21', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết sạch Yêu Vương', '1', '', '45', '2000', '2000', 'be7f41af7f2b397af5129fe5dec95e1a', '1', '27', '12', '150', '5', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ có chuyện muốn nói', '3', '1|20', '', '1000', '1500', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '37', '15', '13', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Môn phái nhân viên quản lý có cái gì quyền hạn?', '3', '1|50', '', '1000', '1500', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '38', '13', '26', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hoa Quả Sơn có biến', '3', '1|30', '', '1000', '1500', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '39', '26', '6', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Phù lục hối đoái', '3', '1|30,6|50,7|50', '34', '2000', '2000', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '40', '6', '16', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngoài vòng pháp luật cuồng đồ', '2', '1|100', '49', '5', '5', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '30', '98', '10', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Quỷ nước nháo sự', '2', '1|100', '51', '1000', '1500', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '32', '54', '5', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Giết!', '2', '1|50,9|50', '39', '600', '400', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '20', '77', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Rất!', '2', '1|30,9|50', '38', '500', '500', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '19', '76', '50', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', 'be7f41af7f2b397af5129fe5dec95e1a', '3', '13', '56', '5', '0', '2', '', '22', '', null);
INSERT INTO `playerrenwu` VALUES ('Tàn mực thu thập', '1', '1|100', '49', '2500', '1500', 'be7f41af7f2b397af5129fe5dec95e1a', '1', '31', '14', '10', '2', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'e17309aac194beac068b4383e5575918', '1', '23', '55', '20', '2', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lợn rừng họa loạn', '2', '1|5', '24', '100', '120', 'e17309aac194beac068b4383e5575918', '1', '13', '56', '5', '2', '2', '', '22', '', null);
INSERT INTO `playerrenwu` VALUES ('Cứng rắn cánh ong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '23', '55', '20', '3', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', '03bb973718e9c9e68ad90317c5efdf68', '3', '41', '0', '0', '0', '0', '', '2026', '', '1');
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', '03bb973718e9c9e68ad90317c5efdf68', '1', '42', '0', '0', '0', '0', '', '2026', '', '1');
INSERT INTO `playerrenwu` VALUES ('Ngạnh sí phong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'dc4996fe7860595b1bb92db3c2af9fab', '1', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', 'dc4996fe7860595b1bb92db3c2af9fab', '2', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngạnh sí phong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', 'fb36ea13a56ad413d9cccebca56bb546', '1', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Ngạnh sí phong nhiễu', '2', '1|15,6|100,7|100', '', '200', '150', '8d0329755724609ccb9713953eee47f4', '3', '23', '55', '20', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Tìm Vương Đại Mụ', '3', '1|20', '25', '200', '100', '8d0329755724609ccb9713953eee47f4', '3', '24', '11', '18', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Lang hoạn', '2', '1|100', '', '400', '300', '8d0329755724609ccb9713953eee47f4', '3', '29', '62', '10', '0', '3', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', 'f91190a62d0f3267f85d972e4ba496d3', '1', '42', '0', '0', '0', '0', '', '2026', '', '1');
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', '6b7766a40825443262489376feafd063', '3', '41', '0', '0', '0', '0', '', '2026', '', '1');
INSERT INTO `playerrenwu` VALUES ('Cố nhân', '3', '1|50', '29', '400', '200', '8d0329755724609ccb9713953eee47f4', '3', '28', '11', '14', '0', '1', '', '0', '', null);
INSERT INTO `playerrenwu` VALUES ('Hà quan mị hoặc', '3', '1|20', '', '500', '1000', '8d0329755724609ccb9713953eee47f4', '3', '34', '18', '9', '0', '3', '', '0', '', '18');
INSERT INTO `playerrenwu` VALUES ('Kích sát huyết lôi ưng', '2', '1|50', '', '500', '1000', '8d0329755724609ccb9713953eee47f4', '3', '35', '66', '15', '0', '3', '', '0', '', '9');
INSERT INTO `playerrenwu` VALUES ('Mua dược phẩm', '3', '1|30', '', '800', '1800', '8d0329755724609ccb9713953eee47f4', '3', '36', '9', '15', '0', '3', '', '0', '', '9');
INSERT INTO `playerrenwu` VALUES ('Vương lão ngũ hữu sự yếu thuyết', '3', '1|20', '', '1000', '1500', '8d0329755724609ccb9713953eee47f4', '3', '37', '15', '13', '0', '3', '', '0', '', '15');
INSERT INTO `playerrenwu` VALUES ('Môn phái quản lý viên hữu thập yêu quyền hạn?', '3', '1|50', '', '1000', '1500', '8d0329755724609ccb9713953eee47f4', '3', '38', '13', '26', '0', '3', '', '0', '', '13');
INSERT INTO `playerrenwu` VALUES ('Hoa quả sơn hữu tình huống', '3', '1|30', '', '1000', '1500', '8d0329755724609ccb9713953eee47f4', '3', '39', '26', '6', '0', '3', '', '0', '', '26');
INSERT INTO `playerrenwu` VALUES ('Phù lục đoái hoán', '3', '1|30,6|50,7|50', '34', '2000', '2000', '8d0329755724609ccb9713953eee47f4', '3', '40', '6', '16', '0', '3', '', '0', '', '6');
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', 'f91190a62d0f3267f85d972e4ba496d3', '3', '41', '0', '0', '0', '0', '', '2026', '', '1');
INSERT INTO `playerrenwu` VALUES ('', '0', '', '', '', '', '6b7766a40825443262489376feafd063', '1', '42', '0', '0', '0', '0', '', '2026', '', '1');

-- ----------------------------
-- Table structure for `playertaozhuang`
-- ----------------------------
DROP TABLE IF EXISTS `playertaozhuang`;
CREATE TABLE `playertaozhuang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tzid` varchar(255) NOT NULL DEFAULT '' COMMENT '套装ID',
  `sid` varchar(255) NOT NULL DEFAULT '',
  `tzinfo` varchar(255) NOT NULL DEFAULT '' COMMENT '套装介绍',
  `tzgj` varchar(255) NOT NULL DEFAULT '',
  `tzfy` varchar(255) NOT NULL DEFAULT '',
  `tzbj` varchar(255) NOT NULL DEFAULT '',
  `tzsb` varchar(255) NOT NULL DEFAULT '',
  `tzxx` varchar(255) NOT NULL DEFAULT '',
  `tzxy` varchar(255) NOT NULL DEFAULT '',
  `tzmaxhp` varchar(255) NOT NULL DEFAULT '',
  `tzys` varchar(255) NOT NULL DEFAULT '' COMMENT '套装颜色',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='测试';

-- ----------------------------
-- Records of playertaozhuang
-- ----------------------------
INSERT INTO `playertaozhuang` VALUES ('1', '1', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '内测码装备套装，专属内测玩家用。', '123', '123', '5', '5', '5', '5', '123', '#09e0b0');
INSERT INTO `playertaozhuang` VALUES ('2', '10', 'acea4cf5bfd750233ebc90c1361cd568', '灵套装激活获得的装备加成', '350', '200', '30', '10', '10', '10', '500', '#00bebf');
INSERT INTO `playertaozhuang` VALUES ('3', '1', 'a2377a650da6bb0ad4fe0514f971f858', '内测码装备套装，专属内测玩家用。', '123', '123', '5', '5', '5', '5', '123', '#217f6a');
INSERT INTO `playertaozhuang` VALUES ('4', '1', '1ac81512a158e2622c17a49ba9b7eaf8', '内测码装备套装，专属内测玩家用。', '123', '123', '5', '5', '5', '5', '123', '#217f6a');

-- ----------------------------
-- Table structure for `playerwugong`
-- ----------------------------
DROP TABLE IF EXISTS `playerwugong`;
CREATE TABLE `playerwugong` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `wgname` varchar(255) NOT NULL DEFAULT '',
  `wgys` varchar(255) NOT NULL DEFAULT '',
  `wginfo` longtext NOT NULL,
  `wgid` varchar(11) NOT NULL DEFAULT '',
  `wglx` varchar(255) NOT NULL DEFAULT '' COMMENT '0是功能1是属性',
  `uid` int(11) NOT NULL DEFAULT '0',
  `sid` text NOT NULL,
  `wgdj` varchar(255) NOT NULL DEFAULT '0',
  `wgsum` varchar(255) NOT NULL DEFAULT '' COMMENT '修炼使用',
  `wgxl` varchar(255) NOT NULL DEFAULT '0',
  `wgxlmax` varchar(255) NOT NULL DEFAULT '0',
  `xlzt` varchar(255) NOT NULL DEFAULT '0',
  `xlsj` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wghl` varchar(255) NOT NULL DEFAULT '20' COMMENT '武功魂力',
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerwugong
-- ----------------------------
INSERT INTO `playerwugong` VALUES ('1', 'Mượn gió bẻ măng', '#39b512', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '0', '3', '0', '0', '0', '1899-12-30 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('2', 'Bách độc bất xâm', '#ff3100', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '0', '5', '0', '0', '0', '1899-12-30 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('3', 'Vung vôi', '#647b5d', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '0', '2', '0', '0', '0', '1899-12-30 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('4', 'Bách độc bất xâm', '#ff3100', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '2', '15', '681', '5550', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('5', 'Vung vôi', '#647b5d', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '2', '13', '681', '5550', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('6', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '6', '0', '0', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('7', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '111111', '0', '10', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('8', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '111111', '0', '6', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('9', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '111111', '0', '4', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('10', 'Thủ hạ lưu tình', '#f9c508', 'Nội Công / Tâm Pháp|Huyền diệu thông huyền|Tăng khả năng lĩnh ngộ võ học, hiệu quả tu luyện và sức mạnh nội lực|Thâm sâu – huyền ảo – ngộ đạo phá cảnh|Huyền Thưởng Diệu Nghĩa là bí điển ghi chép những chân lý huyền diệu của võ học và thiên địa. Người tu luyện thông qua việc lĩnh ngộ đạo lý bên trong để khai mở tâm cảnh, dung hội tinh hoa của các môn phái và nâng cao tốc độ tu luyện. Khi đạt đến cảnh giới cao thâm, có thể lấy ý ngự khí, lấy tâm dẫn lực, khiến mọi chiêu thức đều ẩn chứa huyền cơ và phát huy uy lực vượt xa thường nhân.', '4', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '1', '1', '42', '59', '1', '2022-03-08 19:02:55', '17');
INSERT INTO `playerwugong` VALUES ('11', 'Dịch Cân Kinh', '#f9c508', 'Nội Công / Luyện Thể|Dịch cân đổi cốt|Tăng sinh lực, phòng ngự và nội lực, nâng cao khả năng kháng sát thương|Kiên cường – bền bỉ – lấy thủ làm công|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy phép dịch cân đổi cốt làm căn cơ, khai thông kinh mạch và cường hóa gân cốt. Người tu luyện sở hữu thể phách vượt trội, nội lực hùng hậu, công thủ kiêm bị. Khi đại thành có thể đạt đến cảnh giới vô kiên bất tồi, lấy thân làm binh, không gì không thể phá vỡ.', '9', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '2', '0', '0', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('12', 'Quyết trương tâm pháp', '#f9c508', 'Nỏ Thuật / Tâm Pháp|Khống chế viễn kích|Tăng sát thương nỏ, độ chính xác và khả năng xuyên phá ở tầm xa|Chuẩn xác – ổn định – nhất kích trí mạng|Quyết Trương Tâm Pháp, còn được gọi là Nỏ Pháp, là một phần tinh hoa được ghi chép trong bộ Canh Dư Thặng Kỹ. Công pháp lấy kỹ nghệ chế tạo và vận dụng nỏ làm nền tảng, chú trọng khả năng ngắm bắn, điều tức và phát lực chuẩn xác. Người tu luyện có thể phát huy tối đa uy lực của nỏ cơ, xuất tiễn nhanh như điện, trăm bước xuyên dương, khiến đối thủ khó lòng phòng bị.', '5', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '3', '0', '0', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('13', 'Dính áo mười tám ngã', '#f9c508', 'Quyền Pháp / Cầm Nã|Mượn lực chế địch|Tăng khả năng khống chế, quật ngã và phá vỡ thế thủ của đối phương|Âm nhu – linh hoạt – lấy nhu thắng cương|Triêm Y Thập Bát Điệt là tuyệt kỹ cận chiến chuyên về cầm nã và quật ngã. Mượn lực chế địch, phá vỡ trọng tâm chỉ trong tích tắc, kết hợp Văn luyện và Võ luyện để đạt đến cảnh giới lấy nhu thắng cương, một chiêu định thắng bại.', '8', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '2', '0', '0', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('14', 'Trường thương pháp tuyển', '#f9c508', 'Thương / Côn|Thương côn hợp nhất|Tăng sát thương vật lý, mở rộng phạm vi công kích, xác suất kích hoạt liên chiêu|Cương mãnh – liên tục – áp chế đối phương|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy côn pháp Thiếu Lâm làm căn cơ, dung hợp tinh hoa của các gia thương pháp trong thiên hạ, sáng tạo nên hệ võ học lấy thương làm chủ, côn làm phụ. Chiêu thức cương mãnh, biến hóa linh hoạt, thương – côn hợp nhất, công thủ toàn diện, uy lực vượt trội.', '6', '1', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '2', '0', '0', '1', '2022-03-08 19:02:55', '20');
INSERT INTO `playerwugong` VALUES ('15', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'ef7cd5b1db4562a2841a3e84f17e6ce6', '0', '6', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('16', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'ef7cd5b1db4562a2841a3e84f17e6ce6', '0', '4', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('17', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'ef7cd5b1db4562a2841a3e84f17e6ce6', '0', '3', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('18', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '7660cbcbcfec945cbdac430361da363f', '0', '1', '0', '0', '1', '2022-02-27 21:54:55', '20');
INSERT INTO `playerwugong` VALUES ('19', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '7660cbcbcfec945cbdac430361da363f', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('20', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '3', '1086', '1313', '1', '2022-03-18 23:22:09', '20');
INSERT INTO `playerwugong` VALUES ('21', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '8', '0', '0', '1', '2022-03-18 23:22:09', '20');
INSERT INTO `playerwugong` VALUES ('22', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '5', '0', '0', '1', '2022-03-18 23:22:09', '20');
INSERT INTO `playerwugong` VALUES ('23', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'af976a314459ca05fc3563f0d6fd7e39', '0', '2', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('24', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'af976a314459ca05fc3563f0d6fd7e39', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('25', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'af976a314459ca05fc3563f0d6fd7e39', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('26', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '0', '1', '0', '0', '1', '2022-03-10 19:49:14', '20');
INSERT INTO `playerwugong` VALUES ('27', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('28', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '0', '3', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('29', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '0', '7', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('30', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '0', '2', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('31', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '771086b453fb6d53d76bf46880cf4b41', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('32', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '087efeb819f49c1789df1f599ec15388', '0', '3', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('33', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '087efeb819f49c1789df1f599ec15388', '0', '2', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('34', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '087efeb819f49c1789df1f599ec15388', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('35', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '4', '0', '753', '1936', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('36', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '3', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('37', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '4', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('38', 'Vung vôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '1', '0', '0', '1', '2022-03-19 08:22:49', '20');
INSERT INTO `playerwugong` VALUES ('39', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '2', '0', '0', '1', '2022-03-19 08:22:49', '20');
INSERT INTO `playerwugong` VALUES ('40', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '1', '0', '127', '162', '1', '2022-03-19 08:22:49', '20');
INSERT INTO `playerwugong` VALUES ('41', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '454ac9cbdb63df840643655755078a94', '1', '0', '9', '16', '0', '2022-03-18 11:21:13', '20');
INSERT INTO `playerwugong` VALUES ('42', 'Mượn gió bẻ măng', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '1266218ff94820df0b66c6ef60d0541f', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('43', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', '1266218ff94820df0b66c6ef60d0541f', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('44', 'Thủ hạ lưu tình', '#f9c508', 'Nội Công / Tâm Pháp|Huyền diệu thông huyền|Tăng khả năng lĩnh ngộ võ học, hiệu quả tu luyện và sức mạnh nội lực|Thâm sâu – huyền ảo – ngộ đạo phá cảnh|Huyền Thưởng Diệu Nghĩa là bí điển ghi chép những chân lý huyền diệu của võ học và thiên địa. Người tu luyện thông qua việc lĩnh ngộ đạo lý bên trong để khai mở tâm cảnh, dung hội tinh hoa của các môn phái và nâng cao tốc độ tu luyện. Khi đạt đến cảnh giới cao thâm, có thể lấy ý ngự khí, lấy tâm dẫn lực, khiến mọi chiêu thức đều ẩn chứa huyền cơ và phát huy uy lực vượt xa thường nhân.', '4', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '1', '0', '0', '1', '2022-03-21 17:03:15', '8');
INSERT INTO `playerwugong` VALUES ('45', 'Dịch Cân Kinh', '#f9c508', 'Nội Công / Luyện Thể|Dịch cân đổi cốt|Tăng sinh lực, phòng ngự và nội lực, nâng cao khả năng kháng sát thương|Kiên cường – bền bỉ – lấy thủ làm công|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy phép dịch cân đổi cốt làm căn cơ, khai thông kinh mạch và cường hóa gân cốt. Người tu luyện sở hữu thể phách vượt trội, nội lực hùng hậu, công thủ kiêm bị. Khi đại thành có thể đạt đến cảnh giới vô kiên bất tồi, lấy thân làm binh, không gì không thể phá vỡ.', '9', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '1', '1', '10', '22', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('46', 'Dính áo mười tám ngã', '#f9c508', 'Quyền Pháp / Cầm Nã|Mượn lực chế địch|Tăng khả năng khống chế, quật ngã và phá vỡ thế thủ của đối phương|Âm nhu – linh hoạt – lấy nhu thắng cương|Triêm Y Thập Bát Điệt là tuyệt kỹ cận chiến chuyên về cầm nã và quật ngã. Mượn lực chế địch, phá vỡ trọng tâm chỉ trong tích tắc, kết hợp Văn luyện và Võ luyện để đạt đến cảnh giới lấy nhu thắng cương, một chiêu định thắng bại.', '8', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '1', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('47', 'Quyết trương tâm pháp', '#f9c508', 'Nỏ Thuật / Tâm Pháp|Khống chế viễn kích|Tăng sát thương nỏ, độ chính xác và khả năng xuyên phá ở tầm xa|Chuẩn xác – ổn định – nhất kích trí mạng|Quyết Trương Tâm Pháp, còn được gọi là Nỏ Pháp, là một phần tinh hoa được ghi chép trong bộ Canh Dư Thặng Kỹ. Công pháp lấy kỹ nghệ chế tạo và vận dụng nỏ làm nền tảng, chú trọng khả năng ngắm bắn, điều tức và phát lực chuẩn xác. Người tu luyện có thể phát huy tối đa uy lực của nỏ cơ, xuất tiễn nhanh như điện, trăm bước xuyên dương, khiến đối thủ khó lòng phòng bị.', '5', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '1', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('48', 'Cửu Âm Chân Kinh', '#f9c508', 'Nội Công / Tâm Pháp|Âm nhu huyền diệu|Tăng nội lực, khả năng né tránh và sức mạnh chiêu thức, gia tăng hiệu quả phản kích|Âm nhu – biến hóa – hậu phát chế nhân|Tuyệt học vô thượng lưu truyền trong võ lâm, ghi chép tinh hoa của đạo gia và võ học thượng cổ. Lấy âm nhu làm gốc, lấy tĩnh chế động, kết hợp nội công, quyền cước và tâm pháp thành một thể thống nhất. Người tu luyện có thể khai mở kỳ kinh bát mạch, vận chuyển chân khí vô tận, lấy nhu thắng cương, hóa giải sát chiêu của địch và phát huy uy lực kinh thiên động địa khi đạt đến cảnh giới đại thành.', '10', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '2', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('49', 'Trường thương pháp tuyển', '#f9c508', 'Thương / Côn|Thương côn hợp nhất|Tăng sát thương vật lý, mở rộng phạm vi công kích, xác suất kích hoạt liên chiêu|Cương mãnh – liên tục – áp chế đối phương|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy côn pháp Thiếu Lâm làm căn cơ, dung hợp tinh hoa của các gia thương pháp trong thiên hạ, sáng tạo nên hệ võ học lấy thương làm chủ, côn làm phụ. Chiêu thức cương mãnh, biến hóa linh hoạt, thương – côn hợp nhất, công thủ toàn diện, uy lực vượt trội.', '6', '1', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '2', '0', '0', '1', '2022-03-21 17:03:15', '20');
INSERT INTO `playerwugong` VALUES ('50', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '0', 'fb36ea13a56ad413d9cccebca56bb546', '0', '2', '0', '0', '1', '2024-08-31 00:43:13', '20');
INSERT INTO `playerwugong` VALUES ('51', 'Thuận thủ khiên dương', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', 'fb36ea13a56ad413d9cccebca56bb546', '0', '2', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('52', 'Tát thạch hôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', 'fb36ea13a56ad413d9cccebca56bb546', '0', '1', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('53', 'Trường thương pháp tuyển', '#f9c508', 'Thương / Côn|Thương côn hợp nhất|Tăng sát thương vật lý, mở rộng phạm vi công kích, xác suất kích hoạt liên chiêu|Cương mãnh – liên tục – áp chế đối phương|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy côn pháp Thiếu Lâm làm căn cơ, dung hợp tinh hoa của các gia thương pháp trong thiên hạ, sáng tạo nên hệ võ học lấy thương làm chủ, côn làm phụ. Chiêu thức cương mãnh, biến hóa linh hoạt, thương – côn hợp nhất, công thủ toàn diện, uy lực vượt trội.', '6', '1', '0', '8d0329755724609ccb9713953eee47f4', '9', '0', '5963', '5963', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('54', 'Dịch cân kinh', '#f9c508', 'Nội Công / Luyện Thể|Dịch cân đổi cốt|Tăng sinh lực, phòng ngự và nội lực, nâng cao khả năng kháng sát thương|Kiên cường – bền bỉ – lấy thủ làm công|Tuyệt học trấn phái của Thiếu Lâm Tự. Lấy phép dịch cân đổi cốt làm căn cơ, khai thông kinh mạch và cường hóa gân cốt. Người tu luyện sở hữu thể phách vượt trội, nội lực hùng hậu, công thủ kiêm bị. Khi đại thành có thể đạt đến cảnh giới vô kiên bất tồi, lấy thân làm binh, không gì không thể phá vỡ.', '9', '1', '0', '8d0329755724609ccb9713953eee47f4', '1', '0', '10', '10', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('55', 'Thiện đao pháp tuyển', '#f9c508', 'Đao Pháp / Đơn Đao|Đơn đao song thủ|Tăng sát thương vật lý, khả năng phá giáp và sức mạnh đòn chém|Cương mãnh – sắc bén – dũng mãnh tiến công|Thiện Đao Pháp Tuyển ghi chép tinh yếu của thuật dùng đơn đao hai tay. Đao pháp lấy tốc độ và uy lực làm trọng, kết hợp kỹ nghệ rèn, mài và dưỡng đao tinh xảo, khiến lưỡi đao luôn sắc bén, xuất chiêu dũng mãnh, có thể phá giáp đoạn kim.', '7', '1', '0', '8d0329755724609ccb9713953eee47f4', '0', '0', '0', '0', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('56', 'Tát thạch hôi', '#10bf61', 'Thân Pháp / Mưu Thuật|Tung cát mê nhãn|Tăng tỷ lệ chạy trốn lên 99%, làm gián đoạn truy kích và tạo cơ hội phản công|Xảo diệu – linh hoạt – bảo mệnh thoát thân|Tát Thạch Hôi là bí kỹ giang hồ chuyên dùng để thoát khỏi hiểm cảnh. Người thi triển lợi dụng cát bụi hoặc tro mịn che khuất tầm nhìn đối phương, kết hợp bộ pháp biến ảo và động tác nghi binh để nhanh chóng thoát thân hoặc phản kích bất ngờ. Chiêu thức tưởng chừng đơn giản nhưng hiệu quả phi thường, được mệnh danh là tuyệt học bảo mệnh với tỷ lệ đào thoát gần như tuyệt đối.', '1', '1', '0', '8d0329755724609ccb9713953eee47f4', '0', '2', '0', '0', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('57', 'Thuận thủ khiên dương', '#288dc7', 'Khinh Công / Cầm Nã|Xuất thủ như điện|Tăng tốc độ ra tay, xác suất đoạt binh khí và cướp lấy vật phẩm của đối phương|Nhanh nhẹn – xảo diệu – mượn lực đoạt vật|Thuận Thủ Khiên Dương là tuyệt kỹ cầm nã nổi tiếng trong võ lâm, lấy sự nhanh nhạy và chuẩn xác làm cốt lõi. Người tu luyện có thể nhân lúc đối phương sơ hở mà thuận thế xuất thủ, đoạt lấy binh khí hoặc vật phẩm trong chớp mắt. Chiêu thức nhẹ nhàng nhưng khó lường, hư thực đan xen, khiến địch nhân chưa kịp phản ứng đã rơi vào thế bị động.', '2', '0', '0', '8d0329755724609ccb9713953eee47f4', '0', '2', '0', '0', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('58', 'Triêm y thập bát điệt', '#f9c508', 'Quyền Pháp / Cầm Nã|Mượn lực chế địch|Tăng khả năng khống chế, quật ngã và phá vỡ thế thủ của đối phương|Âm nhu – linh hoạt – lấy nhu thắng cương|Triêm Y Thập Bát Điệt là tuyệt kỹ cận chiến chuyên về cầm nã và quật ngã. Mượn lực chế địch, phá vỡ trọng tâm chỉ trong tích tắc, kết hợp Văn luyện và Võ luyện để đạt đến cảnh giới lấy nhu thắng cương, một chiêu định thắng bại.', '8', '1', '0', '8d0329755724609ccb9713953eee47f4', '0', '1', '0', '0', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('59', 'Cửu âm chân kinh', '#f9c508', 'Nội Công / Tâm Pháp|Âm nhu huyền diệu|Tăng nội lực, khả năng né tránh và sức mạnh chiêu thức, gia tăng hiệu quả phản kích|Âm nhu – biến hóa – hậu phát chế nhân|Tuyệt học vô thượng lưu truyền trong võ lâm, ghi chép tinh hoa của đạo gia và võ học thượng cổ. Lấy âm nhu làm gốc, lấy tĩnh chế động, kết hợp nội công, quyền cước và tâm pháp thành một thể thống nhất. Người tu luyện có thể khai mở kỳ kinh bát mạch, vận chuyển chân khí vô tận, lấy nhu thắng cương, hóa giải sát chiêu của địch và phát huy uy lực kinh thiên động địa khi đạt đến cảnh giới đại thành.', '10', '1', '0', '8d0329755724609ccb9713953eee47f4', '0', '1', '0', '0', '0', '2026-03-30 17:08:53', '20');
INSERT INTO `playerwugong` VALUES ('60', 'Bách độc bất xâm', '#f9c508', 'Kháng Độc / Nội Công|Bách độc bất xâm|Tăng kháng độc, miễn nhiễm hiệu ứng trúng độc và giảm sát thương từ độc tố|Vững vàng – thanh tịnh – lấy nội chế độc|Bách Độc Bất Xâm là tuyệt học hộ thể lấy nội lực làm gốc, thông qua việc tôi luyện kinh mạch và tạng phủ để tạo nên thể chất kháng độc phi thường. Người tu luyện có thể hóa giải bách độc, miễn nhiễm nhiều loại kỳ độc và tà khí, duy trì chân khí ổn định trong hiểm cảnh. Khi đạt đến cảnh giới đại thành, thân thể thanh tịnh vô cấu, trăm loại độc dược khó lòng xâm hại.', '3', '0', '547', '8d0329755724609ccb9713953eee47f4', '0', '3', '0', '0', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('61', '', '', '', '6', '', '547', '8d0329755724609ccb9713953eee47f4', '1', '-2', '5963', '994', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('62', '', '', '', '6', '', '547', '8d0329755724609ccb9713953eee47f4', '1', '-2', '5963', '994', '0', '0000-00-00 00:00:00', '20');
INSERT INTO `playerwugong` VALUES ('63', '', '', '', '6', '', '547', '8d0329755724609ccb9713953eee47f4', '1', '-2', '5963', '994', '0', '0000-00-00 00:00:00', '20');

-- ----------------------------
-- Table structure for `playeryaodan`
-- ----------------------------
DROP TABLE IF EXISTS `playeryaodan`;
CREATE TABLE `playeryaodan` (
  `ydname` varchar(255) NOT NULL,
  `ydid` int(11) NOT NULL,
  `ydhp` int(11) NOT NULL,
  `ydgj` int(11) NOT NULL,
  `ydfy` int(11) NOT NULL,
  `ydxx` int(11) NOT NULL,
  `ydbj` int(11) NOT NULL,
  `sid` text NOT NULL,
  `ydsum` int(11) NOT NULL,
  `ydjg` int(11) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  `ydjgm` int(11) NOT NULL DEFAULT '0',
  `yd_van` int(11) DEFAULT '0',
  `yd_toxin` int(11) DEFAULT '0',
  `yd_quality` int(11) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playeryaodan
-- ----------------------------
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '4', '5000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '0', '2', '39f73d46e13bed758f42d5695f3f43f0', '1', '20000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经残卷A', '2', '0', '15', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '0', '8000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '0', '5000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '0', '2', '111111', '0', '20000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('金刚经', '4', '0', '-100', '100', '0', '1', '39f73d46e13bed758f42d5695f3f43f0', '4', '20000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('南拳洪拳', '16', '0', '28', '18', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '0', '32000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('残金缺玉', '10', '0', '25', '25', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '0', '100000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八掌A', '5', '0', '80', '50', '5', '5', 'e8d7c6fb768be55653f7cb2f7164bd1d', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('心意六合', '13', '0', '26', '26', '6', '6', '38171773e6842eb588ccc4ec4a7f48a8', '0', '380000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', '38171773e6842eb588ccc4ec4a7f48a8', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('心意六合', '13', '0', '26', '26', '6', '6', '043c4eac8ffaa70af1ccb3e0eef0a364', '0', '380000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('侠门内功', '14', '0', '33', '32', '3', '3', '043c4eac8ffaa70af1ccb3e0eef0a364', '0', '380000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '2', '2', '043c4eac8ffaa70af1ccb3e0eef0a364', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八掌A', '5', '0', '80', '50', '5', '5', '043c4eac8ffaa70af1ccb3e0eef0a364', '29', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '2', '2', 'b8a188334584e024060c33c6b3a9bbc5', '1', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '2', '2', 'd1d51188e1fb089f7ad442c257683417', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('泰拳忍术', '12', '0', '30', '30', '0', '0', 'd1d51188e1fb089f7ad442c257683417', '0', '150000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八掌A', '5', '0', '80', '50', '5', '5', 'd580de813e3c794d847c3cd35dfe5fb0', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内功', '11', '0', '10', '80', '10', '0', 'd580de813e3c794d847c3cd35dfe5fb0', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('神照经A', '6', '0', '40', '-5', '5', '1', '0b82a8fd07dfafc237a5dcc15b332ce8', '1', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('残金缺玉', '10', '0', '25', '25', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '1', '100000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '1', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋经', '1', '0', '10', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八掌A', '5', '0', '80', '50', '5', '5', 'f52f24af276734367a122e13b0af67d2', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内功', '11', '0', '10', '80', '10', '0', 'f52f24af276734367a122e13b0af67d2', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('神照经A', '6', '0', '40', '-5', '5', '1', 'f52f24af276734367a122e13b0af67d2', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太玄经A', '7', '0', '45', '5', '0', '1', 'f52f24af276734367a122e13b0af67d2', '0', '400000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阳真经', '8', '0', '20', '50', '10', '0', 'f52f24af276734367a122e13b0af67d2', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八掌A', '5', '0', '80', '50', '5', '5', '929610ec747f6e087aebb1af550673fa', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阳真经', '8', '0', '20', '50', '10', '0', '929610ec747f6e087aebb1af550673fa', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真经', '3', '0', '100', '-100', '2', '2', '929610ec747f6e087aebb1af550673fa', '3', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', '111111', '3', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', '087efeb819f49c1789df1f599ec15388', '1', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内丹', '11', '0', '10', '80', '10', '0', '3fbbf11bd57912b4677e195e091fc41a', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', '3fbbf11bd57912b4677e195e091fc41a', '1', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内丹', '11', '0', '10', '80', '10', '0', '72e2a2bb613ef6ed768ba27a20fbe7de', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八丹', '5', '0', '80', '50', '5', '5', '72e2a2bb613ef6ed768ba27a20fbe7de', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经丹', '2', '0', '15', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('金刚丹', '4', '0', '-100', '100', '1', '1', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阳真丹', '8', '0', '20', '50', '10', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八丹', '5', '0', '80', '50', '5', '5', '771086b453fb6d53d76bf46880cf4b41', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('降龙十八丹', '5', '0', '80', '50', '5', '5', 'acea4cf5bfd750233ebc90c1361cd568', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内丹', '11', '0', '10', '80', '10', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', 'acea4cf5bfd750233ebc90c1361cd568', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阳真丹', '8', '0', '20', '50', '10', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('南拳丹', '16', '0', '28', '18', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '32000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经丹', '2', '0', '15', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('南拳丹', '16', '0', '28', '18', '0', '0', '454ac9cbdb63df840643655755078a94', '0', '32000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('神照丹', '6', '0', '40', '-5', '5', '1', '454ac9cbdb63df840643655755078a94', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太极内丹', '11', '0', '10', '80', '10', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '1000000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('神照丹', '6', '0', '40', '-5', '5', '1', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('心意六丹', '13', '0', '26', '26', '6', '6', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '380000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阳真丹', '8', '0', '20', '50', '10', '0', '454ac9cbdb63df840643655755078a94', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经丹', '2', '0', '15', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '0', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', '454ac9cbdb63df840643655755078a94', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('金刚丹', '4', '0', '-100', '100', '1', '1', '454ac9cbdb63df840643655755078a94', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('太玄丹', '7', '0', '45', '5', '0', '1', '454ac9cbdb63df840643655755078a94', '0', '400000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('残金缺丹', '10', '0', '25', '25', '0', '0', '345155a30691b5c495e1efa12cf5ed96', '4', '100000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('金刚丹', '4', '0', '-100', '100', '1', '1', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('南拳丹', '16', '0', '28', '18', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '32000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('易筋丹', '1', '0', '10', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经丹', '2', '0', '15', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('心意六丹', '13', '0', '26', '26', '6', '6', 'eb66dc52f48cb910d5cf8243ba14a4db', '0', '380000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('九阴真丹', '3', '0', '100', '-100', '2', '2', '1ac81512a158e2622c17a49ba9b7eaf8', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('南拳丹', '16', '0', '28', '18', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '0', '32000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42章经丹', '2', '0', '15', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '1', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Dịch Cân Đan', '1', '0', '10', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '25', '15000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('42 Chương Kinh Đan', '2', '0', '15', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '0', '25000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Cửu Âm Chân Đan', '3', '0', '100', '-100', '2', '2', '8d0329755724609ccb9713953eee47f4', '0', '200000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Hàng Long Thập Bát Đan', '5', '0', '80', '50', '5', '5', '8d0329755724609ccb9713953eee47f4', '0', '800000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Ngọc Nữ Tâm Đan', '9', '0', '5', '45', '2', '0', '8d0329755724609ccb9713953eee47f4', '0', '500000', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Nam Quyền Đan', '16', '0', '28', '18', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '32000', '0', '180', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('Hiệp Môn Nội Đan', '14', '0', '33', '32', '3', '3', '8d0329755724609ccb9713953eee47f4', '1', '380000', '0', '400', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('', '17', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '0', '0', '0', '0', '0', '0', '1');
INSERT INTO `playeryaodan` VALUES ('', '4', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '0', '0', '0', '0', '13', '1');
INSERT INTO `playeryaodan` VALUES ('', '4', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '0', '0', '0', '2', '26', '1');

-- ----------------------------
-- Table structure for `playeryaopin`
-- ----------------------------
DROP TABLE IF EXISTS `playeryaopin`;
CREATE TABLE `playeryaopin` (
  `ypname` varchar(255) NOT NULL,
  `ypid` int(11) NOT NULL,
  `yphp` int(11) NOT NULL,
  `ypgj` int(11) NOT NULL,
  `ypfy` int(11) NOT NULL,
  `ypxx` int(11) NOT NULL,
  `ypbj` int(11) NOT NULL,
  `sid` text NOT NULL,
  `ypsum` int(11) NOT NULL,
  `ypjg` int(11) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playeryaopin
-- ----------------------------
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '43e4c5c6dda6740216e3bd54ff200c15', '69', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'af1d74362b935eb0ac845b7e4f7f707f', '11', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b4dd3b4ff25c3cdd67b858d5fed146fa', '10', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '7e9ab9a48f33d0f31ce4cfc1512ba0b8', '7', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '099f465c0c34dd5ef59f230a21447af4', '11', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '0820a45ab816fb9c222a84c6546581b7', '3', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a80e37407282b5feae841dd75b4dc7b7', '7', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ea5ea2d157d97045153a82e5a342ec8b', '3', '10', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '9bb5f086e2f1efdbc8215e55f8a4a30f', '81', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '099f465c0c34dd5ef59f230a21447af4', '118', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '6d07cdb650fc1fca94c0dd51a8ed971e', '0', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'af1d74362b935eb0ac845b7e4f7f707f', '15', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '6d07cdb650fc1fca94c0dd51a8ed971e', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '9bb5f086e2f1efdbc8215e55f8a4a30f', '705', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '43e4c5c6dda6740216e3bd54ff200c15', '2', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '36f30db66d6fe42f34a91d15b6097af0', '197', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '6d07cdb650fc1fca94c0dd51a8ed971e', '0', '55', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'e0e644a3727f0f1671e917f7b376c66f', '8', '15', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'c5cb7c746b98e872e69f0a5ef8e2d386', '8', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '6b7b2713b1a52397c7282509906e8c5e', '26', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '36f30db66d6fe42f34a91d15b6097af0', '81', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '36f30db66d6fe42f34a91d15b6097af0', '115', '55', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '33619221f61feb039c524037c50aeb95', '3', '15', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '03a4c698ec11e112036183c308cfb94c', '15', '15', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ea5a17b7f6c42320f7c242f2871a0f8d', '21', '15', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '4f49b5eb675277e6401832966f112262', '3', '15', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1385c8e98187fabdc04a600693e0ae8f', '2', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '9bb5f086e2f1efdbc8215e55f8a4a30f', '869', '55', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ca0904a308346c194ff8ec780ccf6736', '3', '15', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '43e4c5c6dda6740216e3bd54ff200c15', '0', '55', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'aa0b823f483b02cc5a7516cab09c4f92', '7', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'f58bb53a4bdd39d6d70b706d77fbc74f', '0', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'f58bb53a4bdd39d6d70b706d77fbc74f', '1', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1b10aad87b70a90c7514b3aa2feb52d1', '6', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ca083d233cbca174c1dec911b9c91e42', '3', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'c4339dfb63d53dbe95ff3f1297cd889d', '6', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'e2c3b13624da53a64bfd36596e04227b', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '099f465c0c34dd5ef59f230a21447af4', '2', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '49a77fab810b41f76e378df436869254', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '4ab5e4638b035213c341b7d4706ded4f', '7', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'bb2a45b7652a7900e7810128a329597e', '36', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'bb2a45b7652a7900e7810128a329597e', '0', '155', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a805c97cc13e0d4a7e07c7552cf101f9', '24', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '12b77bc3aa3cdf5b2283684271bc916c', '3', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '775c0ff651b405a676fc9ee1729302f4', '32', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '775c0ff651b405a676fc9ee1729302f4', '146', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '5f2f53403bda85d4fee078d944dd6d7e', '24', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '8bf2c38ba9d08fd609a3d40b35ea659c', '4', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '781a121e409741ff53f5978578067146', '6', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '592857bc812f813ed52fa8b187582fc2', '0', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'eb61a0b08b9bfdfda961d64410eba5bc', '38', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ad8f4d8e577f50deae4492bd03c96b56', '27', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '127260b5602737d7ca8a56f3ff47cfde', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1de67183872072ed605030a8bf3059be', '37', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '77099dca8f67b65ca91af53d0461ac2f', '31', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'f58bb53a4bdd39d6d70b706d77fbc74f', '0', '155', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'c9879e4c3738297c06b0b14d78dc39d6', '33', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '781a121e409741ff53f5978578067146', '10', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ba5b232ca920fefa398ae123afcc87eb', '15', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '2de0f1d3eca04c1a8aff5354db0874ed', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ce13ba86e509d2fab5ed13c2cddf00c9', '24', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '4e079b4084dcdd84cf4393a003a38283', '23', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'aa588ffcf27da87551c01d7c88b24829', '16', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a71dfe5ae44c6f0d4c4be100a2e85748', '28', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '98c04e5c0946bf13d399e8577f181912', '2', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '377b01fca16375319e1d921b89f66604', '13', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '2007608a6942d33ec38e7c3d870fa674', '29', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'cc5cfc9a3fac118adae1bfb7f6dc6c3a', '15', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'dd58b4170ec8d6d20f876405b4b1265d', '22', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'd4cd8bac31b76768226bd72adc430ab9', '25', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '97563ab1d73bbb1e071ec933b5b06e6c', '3', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'e777555550491c15e506da8cafb60086', '29', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '7159624dfdba178c848da03a6ffefec9', '23', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '087efeb819f49c1789df1f599ec15388', '34', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '087efeb819f49c1789df1f599ec15388', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '45ab8af3305f70a9e14037d5e3cb474c', '3', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '16', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '0', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'ad1d34062743fe68d93897d75d8c37d9', '25', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '771086b453fb6d53d76bf46880cf4b41', '4959', '310', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '50', '771086b453fb6d53d76bf46880cf4b41', '1', '25', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '16', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '111111', '30', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '12', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '328', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'b624121f7f5c40f8c6ac507f6cab45f5', '109', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'b3897bd53e13f8c92b92933d4e9b7167', '2', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b3897bd53e13f8c92b92933d4e9b7167', '5', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '08b46397b9704ed9b6230e3864dfde52', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '08b46397b9704ed9b6230e3864dfde52', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '50', '08b46397b9704ed9b6230e3864dfde52', '1', '25', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b624121f7f5c40f8c6ac507f6cab45f5', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1d75409bd985acf3644f25f2a87b6552', '13', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '776a11c3614307517b39e381c89aafc1', '1', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a0f5700982d69976ce54c08cb1c2bbb0', '24', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b8e2bbf8273ee643a807becfac3cfe14', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '7f47f208d5b697de3791115ff8aecc7f', '1', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'a0f5700982d69976ce54c08cb1c2bbb0', '4', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '70619bdc2bdd92dcc83489657743848d', '3', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '70619bdc2bdd92dcc83489657743848d', '14', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '70619bdc2bdd92dcc83489657743848d', '2', '80', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '111111', '92', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '704825b240505b4599c32a64fb882c2c', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '25', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '4', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '4', '155', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '50', 'e8d7c6fb768be55653f7cb2f7164bd1d', '7', '25', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '1', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '17', '30', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '50', '39f73d46e13bed758f42d5695f3f43f0', '1', '25', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '105', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '0', '155', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '3', '80', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', '39f73d46e13bed758f42d5695f3f43f0', '3', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a86db6a86e4d32c7b409d3822ed5bba2', '5', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '7083fa1a1d41824363cd4df0ef5e5d90', '16', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '7083fa1a1d41824363cd4df0ef5e5d90', '1', '310', '0');
INSERT INTO `playeryaopin` VALUES ('千年人参', '2', '5000', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '1', '2000', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', 'e8d7c6fb768be55653f7cb2f7164bd1d', '4', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '54b9e0e2999c06a210aff10c2bd70578', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '38171773e6842eb588ccc4ec4a7f48a8', '4', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '38171773e6842eb588ccc4ec4a7f48a8', '10', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '38171773e6842eb588ccc4ec4a7f48a8', '1', '155', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '111111', '37', '155', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', '111111', '1', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '0', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '13', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '043c4eac8ffaa70af1ccb3e0eef0a364', '3', '155', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '1', '043c4eac8ffaa70af1ccb3e0eef0a364', '1', '20000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '20', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '7', '155', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '3', '80', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '8090f13824dc62c1bdd7b3db5a101fd7', '13', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'd1d51188e1fb089f7ad442c257683417', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'b8a188334584e024060c33c6b3a9bbc5', '103', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'd580de813e3c794d847c3cd35dfe5fb0', '18', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '0b82a8fd07dfafc237a5dcc15b332ce8', '2', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'cb5927f98eb22d2870b0547c6ab6f7f1', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '27', '30', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '0', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '1', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'b6d9f0bb1852a15b241410b3b07a21f8', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '188', '310', '0');
INSERT INTO `playeryaopin` VALUES ('千年人参', '2', '5000', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '5', '2000', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'f52f24af276734367a122e13b0af67d2', '5', '155', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '8361f38084bda036106506df54fcd779', '10', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '2584819298592ea557f2c1d6ae08bbfe', '101', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '2584819298592ea557f2c1d6ae08bbfe', '3', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '305', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '14', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '929610ec747f6e087aebb1af550673fa', '10', '155', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'f991c4f27c28fb7eae2cbc3f80d00245', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'f991c4f27c28fb7eae2cbc3f80d00245', '37', '30', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'f991c4f27c28fb7eae2cbc3f80d00245', '2', '80', '0');
INSERT INTO `playeryaopin` VALUES ('千年人参', '2', '5000', '0', '0', '0', '0', '111111', '2', '2000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '98e3c23a97294ce8109186ba55d8530f', '103', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '98e3c23a97294ce8109186ba55d8530f', '2', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '77584b0fdec3e5bbf4f256da2fc3fc09', '104', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '77584b0fdec3e5bbf4f256da2fc3fc09', '1', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '8db89ecf31a6a27cf2d3f68c095843b9', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '1d3b7593022084ce21637b975b82a533', '95', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1d3b7593022084ce21637b975b82a533', '29', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'fce6f2da3b931558b5ec44dfa71b0646', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '1d3b7593022084ce21637b975b82a533', '4', '80', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'ef7cd5b1db4562a2841a3e84f17e6ce6', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '6f6b750a6a23e083c483f9f2885440e0', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '7660cbcbcfec945cbdac430361da363f', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '72e2a2bb613ef6ed768ba27a20fbe7de', '2', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'd3cd0c8382ef5e102996dfdf2b3a7a48', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '23eeb3fcd2a7e1875c37b09540c01ba9', '2', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'ded62d071f32ba11a20f5c75cc36153f', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '87', '310', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'af976a314459ca05fc3563f0d6fd7e39', '100', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '26', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '92', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '0d471de3efe5dcf749e6fa60fde8ae76', '12', '80', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '1', '155', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', '2', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '923eb396db35867b31b310d8fb8260d2', '200', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '51', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '19', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '80', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '1', 'acea4cf5bfd750233ebc90c1361cd568', '0', '20000', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '279', '155', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '0', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('千年人参', '2', '5000', '0', '0', '0', '0', 'acea4cf5bfd750233ebc90c1361cd568', '77', '2000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'd9bc42eda8a25d32618518f4ae68b92a', '105', '310', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'd9bc42eda8a25d32618518f4ae68b92a', '27', '30', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '23', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '90', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '17', '80', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '162', '155', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '1', 'c1a90c4939ae6e0353f41d09c2b62421', '0', '20000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '32', '30', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '94', '310', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '5', '80', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '61', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('千年人参', '2', '5000', '0', '0', '0', '0', 'c1a90c4939ae6e0353f41d09c2b62421', '72', '2000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '95', '310', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-50', '1', '1', '0', '1', '454ac9cbdb63df840643655755078a94', '2', '20000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '1266218ff94820df0b66c6ef60d0541f', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '46', '500', '0');
INSERT INTO `playeryaopin` VALUES ('百年仙桃', '1', '2000', '0', '0', '0', '0', '454ac9cbdb63df840643655755078a94', '6', '5000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '100', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1266218ff94820df0b66c6ef60d0541f', '1', '100', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '400', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '500', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '6dd9bd98618400f882c43aa6444d4948', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '6dd9bd98618400f882c43aa6444d4948', '1', '100', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-200', '0', '1', '0', '0', '0e975728648b4af97a3eb0dfaf7e303d', '0', '50000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'b83a15ae83b0f866411ae82d4da8b32a', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'a76e665e8322cccdfd421deb60d63a3b', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '69a7a5ae8235caef07c21001c326fa75', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'fc77607e4a6912b43841336a2b25f3f2', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '9a6da705ba923ca7752d634d2826caff', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'eb66dc52f48cb910d5cf8243ba14a4db', '29', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '13ca4b7ef4d57e20f442a787cf8c88ac', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '13ca4b7ef4d57e20f442a787cf8c88ac', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '4ada08fae36224a94fc8fade80bab37b', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '214efa2220dcfa302d616be486c7788b', '10', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', '1ac81512a158e2622c17a49ba9b7eaf8', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', '1ac81512a158e2622c17a49ba9b7eaf8', '26', '100', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', '1ac81512a158e2622c17a49ba9b7eaf8', '7', '400', '0');
INSERT INTO `playeryaopin` VALUES ('还原丹', '6', '100', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '23', '100', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '51', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', '1ac81512a158e2622c17a49ba9b7eaf8', '0', '500', '0');
INSERT INTO `playeryaopin` VALUES ('回血散', '7', '300', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '0', '400', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '47', '500', '0');
INSERT INTO `playeryaopin` VALUES ('大朗饼', '10', '-200', '0', '1', '0', '0', 'be7f41af7f2b397af5129fe5dec95e1a', '0', '50000', '0');
INSERT INTO `playeryaopin` VALUES ('回春汤', '8', '600', '0', '0', '0', '0', 'a2377a650da6bb0ad4fe0514f971f858', '2', '500', '0');
INSERT INTO `playeryaopin` VALUES ('复伤丹', '9', '1200', '0', '0', '0', '0', 'e17309aac194beac068b4383e5575918', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('Phục Thương Đan', '9', '1200', '0', '0', '0', '0', 'fb36ea13a56ad413d9cccebca56bb546', '100', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('Phục Thương Đan', '9', '1200', '0', '0', '0', '0', 'dc4996fe7860595b1bb92db3c2af9fab', '3', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('Hoàn Nguyên Đan', '6', '100', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '68', '100', '0');
INSERT INTO `playeryaopin` VALUES ('Phục Thương Đan', '9', '1200', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '204', '1000', '0');
INSERT INTO `playeryaopin` VALUES ('Bách Niên Tiên Đào', '1', '2000', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '3', '5000', '0');
INSERT INTO `playeryaopin` VALUES ('Thiên Niên Nhân Tham', '2', '5000', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '10000', '0');
INSERT INTO `playeryaopin` VALUES ('Hồi Huyết Tán', '7', '300', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '400', '0');
INSERT INTO `playeryaopin` VALUES ('', '17', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '1', '0', '0');
INSERT INTO `playeryaopin` VALUES ('', '9', '0', '0', '0', '0', '0', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '0', '0');
INSERT INTO `playeryaopin` VALUES ('', '6', '0', '0', '0', '0', '0', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '0', '0');
INSERT INTO `playeryaopin` VALUES ('', '8', '0', '0', '0', '0', '0', '8d0329755724609ccb9713953eee47f4', '13', '0', '0');
INSERT INTO `playeryaopin` VALUES ('', '9', '0', '0', '0', '0', '0', 'ffffffffffffffffffffffffffffffff', '5', '0', '0');
INSERT INTO `playeryaopin` VALUES ('', '6', '0', '0', '0', '0', '0', 'ffffffffffffffffffffffffffffffff', '2', '0', '0');

-- ----------------------------
-- Table structure for `playerzhuangbei`
-- ----------------------------
DROP TABLE IF EXISTS `playerzhuangbei`;
CREATE TABLE `playerzhuangbei` (
  `zbname` varchar(255) NOT NULL,
  `zbinfo` varchar(255) NOT NULL,
  `zbgj` varchar(255) NOT NULL,
  `zbfy` varchar(255) NOT NULL,
  `zbbj` varchar(255) NOT NULL,
  `zbxx` varchar(255) NOT NULL,
  `tuposjs` int(11) DEFAULT '0',
  `tupoexp` int(11) DEFAULT '0',
  `zbid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `zbnowid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` text NOT NULL,
  `zbhp` varchar(255) NOT NULL,
  `qianghua` int(11) NOT NULL,
  `zblv` int(11) NOT NULL,
  `zbtool` int(11) NOT NULL,
  `zbys` varchar(255) NOT NULL DEFAULT '#9EF18D',
  `taozhuang` varchar(255) NOT NULL DEFAULT '0',
  `element` varchar(24) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`zbnowid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=77594 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of playerzhuangbei
-- ----------------------------
INSERT INTO `playerzhuangbei` VALUES ('Huyền Ngọc Nhãn', 'Trang bị bậc Huyền, gắn vào ô Ngọc Nhãn.', '13', '0', '5', '2', '0', '0', '33', '544', '77039', 'fb36ea13a56ad413d9cccebca56bb546', '13', '0', '30', '10', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Linh Phù', 'Trang bị bậc Linh, gắn vào ô Linh Phù.', '3', '0', '0', '1', '0', '0', '27', '544', '77041', 'fb36ea13a56ad413d9cccebca56bb546', '6', '0', '10', '9', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Trấn Sơn Giáp', 'Trang bị bậc Đạo, gắn vào ô Giáp Bào.', '0', '572', '0', '0', '0', '0', '50', '543', '77177', 'c8c35537f4a2bfa618801654b9cd1fc5', '5192', '0', '800', '4', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Giáp', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '9', '543', '77178', 'c8c35537f4a2bfa618801654b9cd1fc5', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bách Luyện Khinh Nhị Giáp', 'Bách luyện nhẹ nhị giáp', '1', '8', '0', '0', '0', '0', '28', '547', '77090', '8d0329755724609ccb9713953eee47f4', '40', '1', '0', '3', '#509037', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Cổ Ngọc', 'Trang bị bậc Huyền, gắn vào ô Cổ Ngọc.', '13', '0', '5', '2', '5', '5', '53', '547', '77073', '8d0329755724609ccb9713953eee47f4', '13', '0', '30', '11', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Vai Khôi', 'Trang bị bậc Phàm, gắn vào ô Vai Khôi.', '0', '2', '0', '0', '5', '5', '56', '547', '77076', '8d0329755724609ccb9713953eee47f4', '7', '0', '1', '12', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Truy Phong', 'Tăng mạnh tốc độ di chuyển, hỗ trợ truy kích và né tránh.', '11', '10', '2', '1', '0', '0', '55', '547', '77145', '8d0329755724609ccb9713953eee47f4', '50', '1', '0', '16', '#9EF18D', '0', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Bí Pháp', 'Bí thuật tăng tốc đánh và sát thương, càng chiến càng mạnh.', '6', '50', '1', '1', '0', '0', '56', '547', '77146', '8d0329755724609ccb9713953eee47f4', '5', '1', '0', '15', '#9EF18D', '0', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Huyền Quan', 'Huyền quan của bộ Bạo Vũ, bảo hộ nguyên thần và ổn định khí huyết khi giao chiến.', '6', '15', '1', '1', '0', '0', '57', '547', '77147', '8d0329755724609ccb9713953eee47f4', '5', '1', '0', '2', '#9EF18D', '0', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Bá Vương Thương', 'Trường thương bá đạo, công kích mạnh, xuyên phá cao, thích hợp cận chiến áp đảo.', '406', '5', '8', '3', '5', '5', '60', '547', '77079', '8d0329755724609ccb9713953eee47f4', '50', '15', '10', '1', '#217f6a', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Phong Dực', 'Gia tăng tốc độ và khả năng né tránh linh hoạt.', '35', '30', '10', '2', '5', '5', '61', '547', '77080', '8d0329755724609ccb9713953eee47f4', '200', '7', '10', '11', '#217f6a', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Huyết Phong', 'Tăng sát thương kèm hiệu ứng hút máu khi chiến đấu.', '16', '50', '2', '10', '5', '5', '62', '547', '77081', '8d0329755724609ccb9713953eee47f4', '100', '1', '10', '10', '#217f6a', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Bạo Vũ Trấn Linh Đai', 'Ổn định nội lực, giảm hiệu ứng khống chế.', '3', '80', '0', '3', '5', '5', '63', '547', '77082', '8d0329755724609ccb9713953eee47f4', '300', '3', '10', '6', '#217f6a', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Hộ Thân Giáp', 'Trang bị bậc Phàm, gắn vào ô Giáp Bào.', '0', '3', '0', '0', '0', '0', '6', '543', '77176', 'c8c35537f4a2bfa618801654b9cd1fc5', '10', '0', '1', '4', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Bào', 'Trang bị bậc Thiên, gắn vào ô Giáp Bào.', '0', '132', '0', '0', '5', '5', '65', '547', '77084', '8d0329755724609ccb9713953eee47f4', '865', '0', '120', '4', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thánh Giới Chỉ', 'Trang bị bậc Thánh, gắn vào ô Giới Chỉ.', '130', '0', '14', '0', '5', '5', '66', '547', '77085', '8d0329755724609ccb9713953eee47f4', '0', '0', '200', '6', '#2196F3', '6', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77091', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77092', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Ngọc Nhãn', 'Trang bị bậc Phàm, gắn vào ô Ngọc Nhãn.', '2', '0', '1', '0', '0', '0', '31', '547', '77093', '8d0329755724609ccb9713953eee47f4', '1', '0', '1', '10', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Ngọc Nhãn', 'Trang bị bậc Phàm, gắn vào ô Ngọc Nhãn.', '2', '0', '1', '0', '0', '0', '31', '547', '77094', '8d0329755724609ccb9713953eee47f4', '1', '0', '1', '10', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77095', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77096', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Ngọc Nhãn', 'Trang bị bậc Phàm, gắn vào ô Ngọc Nhãn.', '2', '0', '1', '0', '0', '0', '31', '547', '77097', '8d0329755724609ccb9713953eee47f4', '1', '0', '1', '10', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77098', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Ngọc Nhãn', 'Trang bị bậc Phàm, gắn vào ô Ngọc Nhãn.', '2', '0', '1', '0', '0', '0', '31', '547', '77099', '8d0329755724609ccb9713953eee47f4', '1', '0', '1', '10', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77100', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Ngọc Nhãn', 'Trang bị bậc Linh, gắn vào ô Ngọc Nhãn.', '4', '0', '2', '1', '0', '0', '32', '547', '77101', '8d0329755724609ccb9713953eee47f4', '4', '0', '10', '10', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Lam Tinh Kiếm', 'Trang bị bậc Thần, gắn vào ô Vũ Khí.', '783', '0', '48', '28', '0', '0', '23', '547', '77108', '8d0329755724609ccb9713953eee47f4', '0', '0', '400', '1', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Linh Phù', 'Trang bị bậc Địa, gắn vào ô Linh Phù.', '27', '0', '0', '3', '0', '0', '29', '547', '77103', '8d0329755724609ccb9713953eee47f4', '63', '0', '60', '9', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77104', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Hoàng Kiếm', 'Trang bị bậc Đạo, gắn vào ô Vũ Khí.', '1391', '0', '48', '28', '0', '0', '25', '547', '77105', '8d0329755724609ccb9713953eee47f4', '0', '0', '800', '1', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77106', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Cổ Ngọc', 'Trang bị bậc Huyền, gắn vào ô Cổ Ngọc.', '13', '0', '5', '2', '0', '0', '53', '547', '77143', '8d0329755724609ccb9713953eee47f4', '13', '0', '30', '11', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Cổ Ngọc', 'Trang bị bậc Địa, gắn vào ô Cổ Ngọc.', '38', '0', '7', '3', '0', '0', '54', '547', '77144', '8d0329755724609ccb9713953eee47f4', '42', '0', '60', '11', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Hoàng Kiếm', 'Trang bị bậc Thiên, gắn vào ô Vũ Khí.', '304', '0', '28', '14', '0', '0', '5', '543', '77175', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '120', '1', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77107', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Bào', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '64', '547', '77168', '8d0329755724609ccb9713953eee47f4', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Bào', 'Trang bị bậc Thiên, gắn vào ô Giáp Bào.', '0', '132', '0', '0', '0', '0', '65', '547', '77169', '8d0329755724609ccb9713953eee47f4', '865', '0', '120', '4', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thánh Giới Chỉ', 'Trang bị bậc Thánh, gắn vào ô Giới Chỉ.', '130', '0', '14', '0', '0', '0', '66', '547', '77170', '8d0329755724609ccb9713953eee47f4', '0', '0', '200', '6', '#2196F3', '6', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Mặc Ảnh Kiếm', 'Trang bị bậc Linh, gắn vào ô Vũ Khí.', '17', '0', '6', '3', '0', '0', '2', '543', '77304', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '10', '1', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Vai Khôi', 'Trang bị bậc Thiên, gắn vào ô Vai Khôi.', '0', '84', '0', '0', '0', '0', '60', '547', '77164', '8d0329755724609ccb9713953eee47f4', '577', '0', '120', '12', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Bào', 'Trang bị bậc Phàm, gắn vào ô Giáp Bào.', '0', '3', '0', '0', '0', '0', '61', '547', '77165', '8d0329755724609ccb9713953eee47f4', '10', '0', '1', '4', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Bào', 'Trang bị bậc Linh, gắn vào ô Giáp Bào.', '0', '8', '0', '0', '0', '0', '62', '547', '77166', '8d0329755724609ccb9713953eee47f4', '35', '0', '10', '4', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Bào', 'Trang bị bậc Huyền, gắn vào ô Giáp Bào.', '0', '24', '0', '0', '0', '0', '63', '547', '77167', '8d0329755724609ccb9713953eee47f4', '121', '0', '30', '4', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Cổ Ngọc', 'Trang bị bậc Thiên, gắn vào ô Cổ Ngọc.', '76', '0', '11', '5', '0', '0', '55', '547', '77160', '8d0329755724609ccb9713953eee47f4', '96', '0', '120', '11', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Vai Khôi', 'Trang bị bậc Phàm, gắn vào ô Vai Khôi.', '0', '2', '0', '0', '0', '0', '56', '547', '77161', '8d0329755724609ccb9713953eee47f4', '7', '0', '1', '12', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Vai Khôi', 'Trang bị bậc Linh, gắn vào ô Vai Khôi.', '0', '5', '0', '0', '0', '0', '57', '547', '77162', '8d0329755724609ccb9713953eee47f4', '23', '0', '10', '12', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Vai Khôi', 'Trang bị bậc Huyền, gắn vào ô Vai Khôi.', '0', '15', '0', '0', '0', '0', '58', '547', '77163', '8d0329755724609ccb9713953eee47f4', '81', '0', '30', '12', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Tiên Giới Chỉ', 'Trang bị bậc Tiên, gắn vào ô Giới Chỉ.', '183', '0', '18', '0', '0', '0', '67', '547', '77156', '8d0329755724609ccb9713953eee47f4', '0', '0', '300', '6', '#FF5722', '7', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Chấn Hồn Kiếm', 'Trang bị bậc Huyền, gắn vào ô Vũ Khí.', '52', '0', '12', '6', '0', '0', '178', '543', '77174', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '30', '1', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Cổ Ngọc', 'Trang bị bậc Huyền, gắn vào ô Cổ Ngọc.', '13', '0', '5', '2', '0', '0', '53', '547', '77158', '8d0329755724609ccb9713953eee47f4', '13', '0', '30', '11', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Cổ Ngọc', 'Trang bị bậc Địa, gắn vào ô Cổ Ngọc.', '38', '0', '7', '3', '0', '0', '54', '547', '77159', '8d0329755724609ccb9713953eee47f4', '42', '0', '60', '11', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Bào', 'Trang bị bậc Huyền, gắn vào ô Giáp Bào.', '0', '24', '0', '0', '0', '0', '63', '547', '77152', '8d0329755724609ccb9713953eee47f4', '121', '0', '30', '4', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Bào', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '64', '547', '77153', '8d0329755724609ccb9713953eee47f4', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Bào', 'Trang bị bậc Thiên, gắn vào ô Giáp Bào.', '0', '132', '0', '0', '0', '0', '65', '547', '77154', '8d0329755724609ccb9713953eee47f4', '865', '0', '120', '4', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thánh Giới Chỉ', 'Trang bị bậc Thánh, gắn vào ô Giới Chỉ.', '130', '0', '14', '0', '0', '0', '66', '547', '77155', '8d0329755724609ccb9713953eee47f4', '0', '0', '200', '6', '#2196F3', '6', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Vai Khôi', 'Trang bị bậc Huyền, gắn vào ô Vai Khôi.', '0', '15', '0', '0', '0', '0', '58', '547', '77148', '8d0329755724609ccb9713953eee47f4', '81', '0', '30', '12', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Vai Khôi', 'Trang bị bậc Thiên, gắn vào ô Vai Khôi.', '0', '84', '0', '0', '0', '0', '60', '547', '77149', '8d0329755724609ccb9713953eee47f4', '577', '0', '120', '12', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Bào', 'Trang bị bậc Phàm, gắn vào ô Giáp Bào.', '0', '3', '0', '0', '0', '0', '61', '547', '77150', '8d0329755724609ccb9713953eee47f4', '10', '0', '1', '4', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Bào', 'Trang bị bậc Linh, gắn vào ô Giáp Bào.', '0', '8', '0', '0', '0', '0', '62', '547', '77151', '8d0329755724609ccb9713953eee47f4', '35', '0', '10', '4', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Tiên Giới Chỉ', 'Trang bị bậc Tiên, gắn vào ô Giới Chỉ.', '183', '0', '18', '0', '5', '5', '67', '547', '77137', '8d0329755724609ccb9713953eee47f4', '0', '0', '300', '6', '#FF5722', '7', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Bát Tướng Giới', 'Trang bị bậc Phàm, gắn vào ô Giới Chỉ.', '2', '0', '1', '0', '0', '0', '11', '543', '77179', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '1', '6', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Giới Chỉ', 'Trang bị bậc Thần, gắn vào ô Giới Chỉ.', '235', '0', '18', '0', '0', '0', '68', '543', '77180', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '400', '6', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Ngọc Nhãn', 'Trang bị bậc Huyền, gắn vào ô Ngọc Nhãn.', '13', '0', '5', '2', '0', '0', '33', '547', '77139', '8d0329755724609ccb9713953eee47f4', '13', '0', '30', '10', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Cổ Ngọc', 'Trang bị bậc Thiên, gắn vào ô Cổ Ngọc.', '76', '0', '11', '5', '5', '5', '55', '547', '77140', '8d0329755724609ccb9713953eee47f4', '96', '0', '120', '11', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Vai Khôi', 'Trang bị bậc Linh, gắn vào ô Vai Khôi.', '0', '5', '0', '0', '5', '5', '57', '547', '77141', '8d0329755724609ccb9713953eee47f4', '23', '0', '10', '12', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77142', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Mặc Ảnh Kiếm', 'Trang bị bậc Linh, gắn vào ô Vũ Khí.', '17', '0', '6', '3', '0', '0', '2', '543', '77303', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '10', '1', '#4CAF50', '2', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Vô Danh Kiếm', 'Trang bị bậc Phàm, gắn vào ô Vũ Khí.', '7', '0', '2', '1', '0', '0', '1', '543', '77173', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '1', '1', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Liêm Giác Giới', 'Trang bị bậc Địa, gắn vào ô Giới Chỉ.', '46', '0', '7', '0', '0', '0', '14', '543', '77181', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '60', '6', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Phi Sát Phong', 'Trang bị bậc Phàm, gắn vào ô Phi Phong.', '0', '1', '0', '0', '0', '0', '16', '543', '77182', 'c8c35537f4a2bfa618801654b9cd1fc5', '6', '0', '1', '8', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Phi Phong', 'Trang bị bậc Thần, gắn vào ô Phi Phong.', '0', '150', '0', '0', '0', '0', '93', '543', '77183', 'c8c35537f4a2bfa618801654b9cd1fc5', '1538', '0', '400', '8', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Phong', 'Trang bị bậc Địa, gắn vào ô Phi Phong.', '0', '30', '0', '0', '0', '0', '19', '543', '77184', 'c8c35537f4a2bfa618801654b9cd1fc5', '212', '0', '60', '8', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Linh Phù', 'Trang bị bậc Phàm, gắn vào ô Linh Phù.', '1', '0', '0', '0', '0', '0', '26', '543', '77185', 'c8c35537f4a2bfa618801654b9cd1fc5', '2', '0', '1', '9', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Linh Phù', 'Trang bị bậc Thần, gắn vào ô Linh Phù.', '137', '0', '0', '11', '0', '0', '103', '543', '77186', 'c8c35537f4a2bfa618801654b9cd1fc5', '462', '0', '400', '9', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Linh Phù', 'Trang bị bậc Địa, gắn vào ô Linh Phù.', '27', '0', '0', '3', '0', '0', '29', '543', '77187', 'c8c35537f4a2bfa618801654b9cd1fc5', '63', '0', '60', '9', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Ngọc Nhãn', 'Trang bị bậc Phàm, gắn vào ô Ngọc Nhãn.', '2', '0', '1', '0', '0', '0', '31', '543', '77188', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '0', '1', '10', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Ngọc Nhãn', 'Trang bị bậc Thần, gắn vào ô Ngọc Nhãn.', '196', '0', '18', '11', '0', '0', '108', '543', '77189', 'c8c35537f4a2bfa618801654b9cd1fc5', '308', '0', '400', '10', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Ngọc Nhãn', 'Trang bị bậc Địa, gắn vào ô Ngọc Nhãn.', '38', '0', '7', '3', '0', '0', '34', '543', '77190', 'c8c35537f4a2bfa618801654b9cd1fc5', '42', '0', '60', '10', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Quan', 'Trang bị bậc Đạo, gắn vào ô Thiên Quan.', '174', '156', '0', '0', '0', '0', '40', '543', '77191', 'c8c35537f4a2bfa618801654b9cd1fc5', '1442', '0', '800', '3', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Quan', 'Trang bị bậc Phàm, gắn vào ô Thiên Quan.', '1', '1', '0', '0', '0', '0', '36', '543', '77192', 'c8c35537f4a2bfa618801654b9cd1fc5', '3', '0', '1', '3', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Quan', 'Trang bị bậc Thần, gắn vào ô Thiên Quan.', '98', '90', '0', '0', '0', '0', '39', '543', '77193', 'c8c35537f4a2bfa618801654b9cd1fc5', '769', '0', '400', '3', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Thiên Thuẫn', 'Trang bị bậc Đạo, gắn vào ô Thiên Thuẫn.', '0', '416', '0', '0', '0', '0', '45', '543', '77194', 'c8c35537f4a2bfa618801654b9cd1fc5', '2885', '0', '800', '2', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Thiên Thuẫn', 'Trang bị bậc Phàm, gắn vào ô Thiên Thuẫn.', '0', '2', '0', '0', '0', '0', '41', '543', '77195', 'c8c35537f4a2bfa618801654b9cd1fc5', '6', '0', '1', '2', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Thiên Thuẫn', 'Trang bị bậc Thần, gắn vào ô Thiên Thuẫn.', '0', '240', '0', '0', '0', '0', '44', '543', '77196', 'c8c35537f4a2bfa618801654b9cd1fc5', '1538', '0', '400', '2', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Cổ Ngọc', 'Trang bị bậc Phàm, gắn vào ô Cổ Ngọc.', '2', '0', '1', '0', '0', '0', '51', '543', '77197', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '0', '1', '11', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Cổ Ngọc', 'Trang bị bậc Thần, gắn vào ô Cổ Ngọc.', '196', '0', '18', '11', '0', '0', '113', '543', '77198', 'c8c35537f4a2bfa618801654b9cd1fc5', '308', '0', '400', '11', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Cổ Ngọc', 'Trang bị bậc Địa, gắn vào ô Cổ Ngọc.', '38', '0', '7', '3', '0', '0', '54', '543', '77199', 'c8c35537f4a2bfa618801654b9cd1fc5', '42', '0', '60', '11', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Vai Khôi', 'Trang bị bậc Phàm, gắn vào ô Vai Khôi.', '0', '2', '0', '0', '0', '0', '56', '543', '77200', 'c8c35537f4a2bfa618801654b9cd1fc5', '7', '0', '1', '12', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Vai Khôi', 'Trang bị bậc Thần, gắn vào ô Vai Khôi.', '0', '210', '0', '0', '0', '0', '118', '543', '77201', 'c8c35537f4a2bfa618801654b9cd1fc5', '1846', '0', '400', '12', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Vai Khôi', 'Trang bị bậc Địa, gắn vào ô Vai Khôi.', '0', '42', '0', '0', '0', '0', '59', '543', '77202', 'c8c35537f4a2bfa618801654b9cd1fc5', '254', '0', '60', '12', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Hộ Thủ', 'Trang bị bậc Đạo, gắn vào ô Hộ Thủ.', '0', '364', '0', '0', '0', '0', '75', '543', '77203', 'c8c35537f4a2bfa618801654b9cd1fc5', '3462', '0', '800', '5', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Hộ Thủ', 'Trang bị bậc Phàm, gắn vào ô Hộ Thủ.', '0', '2', '0', '0', '0', '0', '71', '543', '77204', 'c8c35537f4a2bfa618801654b9cd1fc5', '7', '0', '1', '5', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Hộ Thủ', 'Trang bị bậc Thần, gắn vào ô Hộ Thủ.', '0', '210', '0', '0', '0', '0', '74', '543', '77205', 'c8c35537f4a2bfa618801654b9cd1fc5', '1846', '0', '400', '5', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Đai', 'Trang bị bậc Phàm, gắn vào ô Yêu Đai.', '0', '1', '0', '0', '0', '0', '76', '543', '77206', 'c8c35537f4a2bfa618801654b9cd1fc5', '3', '0', '1', '7', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Yêu Đai', 'Trang bị bậc Thần, gắn vào ô Yêu Đai.', '0', '90', '0', '0', '0', '0', '88', '543', '77207', 'c8c35537f4a2bfa618801654b9cd1fc5', '923', '0', '400', '7', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Đai', 'Trang bị bậc Địa, gắn vào ô Yêu Đai.', '0', '18', '0', '0', '0', '0', '79', '543', '77208', 'c8c35537f4a2bfa618801654b9cd1fc5', '127', '0', '60', '7', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Linh Bội', 'Trang bị bậc Phàm, gắn vào ô Linh Bội.', '1', '0', '1', '0', '0', '0', '81', '543', '77209', 'c8c35537f4a2bfa618801654b9cd1fc5', '1', '0', '1', '14', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Linh Bội', 'Trang bị bậc Thần, gắn vào ô Linh Bội.', '157', '0', '18', '11', '0', '0', '128', '543', '77210', 'c8c35537f4a2bfa618801654b9cd1fc5', '308', '0', '400', '14', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Linh Bội', 'Trang bị bậc Địa, gắn vào ô Linh Bội.', '30', '0', '7', '3', '0', '0', '84', '543', '77211', 'c8c35537f4a2bfa618801654b9cd1fc5', '42', '0', '60', '14', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Tọa Kỵ', 'Trang bị bậc Phàm, gắn vào ô Tọa Kỵ.', '0', '1', '0', '0', '0', '0', '96', '543', '77212', 'c8c35537f4a2bfa618801654b9cd1fc5', '9', '0', '1', '15', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Tọa Kỵ', 'Trang bị bậc Thần, gắn vào ô Tọa Kỵ.', '0', '90', '0', '0', '0', '0', '133', '543', '77213', 'c8c35537f4a2bfa618801654b9cd1fc5', '2462', '0', '400', '15', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Tọa Kỵ', 'Trang bị bậc Địa, gắn vào ô Tọa Kỵ.', '0', '18', '0', '0', '0', '0', '99', '543', '77214', 'c8c35537f4a2bfa618801654b9cd1fc5', '338', '0', '60', '15', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Đai', 'Trang bị bậc Đạo, gắn vào ô Linh Đai.', '0', '156', '0', '0', '0', '0', '125', '543', '77215', 'c8c35537f4a2bfa618801654b9cd1fc5', '1731', '0', '800', '13', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Đai', 'Trang bị bậc Phàm, gắn vào ô Linh Đai.', '0', '1', '0', '0', '0', '0', '121', '543', '77216', 'c8c35537f4a2bfa618801654b9cd1fc5', '3', '0', '1', '13', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Đai', 'Trang bị bậc Thần, gắn vào ô Linh Đai.', '0', '90', '0', '0', '0', '0', '124', '543', '77217', 'c8c35537f4a2bfa618801654b9cd1fc5', '923', '0', '400', '13', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Chấn Hồn Kiếm', 'Trang bị bậc Huyền, gắn vào ô Vũ Khí.', '52', '0', '12', '6', '0', '0', '178', '544', '77218', 'fb36ea13a56ad413d9cccebca56bb546', '0', '0', '30', '1', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Yêu Đai', 'Trang bị bậc Thần, gắn vào ô Yêu Đai.', '0', '90', '0', '0', '0', '0', '88', '543', '77220', 'c8c35537f4a2bfa618801654b9cd1fc5', '923', '0', '400', '7', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thần Lam Tinh Kiếm', 'Trang bị bậc Thần, gắn vào ô Vũ Khí.', '783', '0', '48', '28', '0', '0', '23', '547', '77221', '8d0329755724609ccb9713953eee47f4', '0', '0', '400', '1', '#d4af37', '8', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Huyết Điểm Kiếm', 'Trang bị bậc Đế, gắn vào ô Vũ Khí.', '1043', '0', '48', '28', '0', '0', '24', '547', '77222', '8d0329755724609ccb9713953eee47f4', '0', '0', '600', '1', '#d4af37', '9', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77223', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77224', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77225', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77226', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Cổ Ngọc', 'Trang bị bậc Huyền, gắn vào ô Cổ Ngọc.', '13', '0', '5', '2', '0', '0', '53', '547', '77227', '8d0329755724609ccb9713953eee47f4', '13', '0', '30', '11', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77228', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77229', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77230', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77231', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77232', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77233', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77234', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77235', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77236', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77237', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77238', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77239', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77240', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77241', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77242', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77243', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77244', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77245', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77246', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77247', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77248', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77249', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77250', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77251', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77252', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77253', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77254', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77255', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77256', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77257', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77258', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77259', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77260', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77261', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77262', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77263', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77264', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77265', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77266', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77267', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77268', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77269', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77270', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77271', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77272', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77273', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77274', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77275', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77276', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77277', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77278', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77279', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77280', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77281', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77282', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77283', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77284', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77285', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77286', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77287', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77288', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77289', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77290', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77291', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77292', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77293', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77294', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77295', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Linh Phù', 'Trang bị bậc Huyền, gắn vào ô Linh Phù.', '9', '0', '0', '2', '0', '0', '28', '547', '77296', '8d0329755724609ccb9713953eee47f4', '20', '0', '30', '9', '#9C27B0', '3', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Vô Danh Kiếm', 'Trang bị bậc Phàm, gắn vào ô Vũ Khí.', '7', '0', '2', '1', '0', '0', '1', '547', '77297', '8d0329755724609ccb9713953eee47f4', '0', '0', '1', '1', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Bát Tướng Giới', 'Trang bị bậc Phàm, gắn vào ô Giới Chỉ.', '2', '0', '1', '0', '0', '0', '11', '547', '77299', '8d0329755724609ccb9713953eee47f4', '0', '9', '1', '6', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Phi Sát Phong', 'Trang bị bậc Phàm, gắn vào ô Phi Phong.', '0', '1', '0', '0', '0', '0', '16', '547', '77300', '8d0329755724609ccb9713953eee47f4', '6', '5', '1', '8', '#9e9e9e', '1', 'kim,moc');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Thiên Thuẫn', 'Trang bị bậc Phàm, gắn vào ô Thiên Thuẫn.', '0', '2', '0', '0', '0', '0', '41', '547', '77301', '8d0329755724609ccb9713953eee47f4', '6', '0', '1', '2', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Linh Mặc Ảnh Kiếm', '', '50', '0', '10', '5', '0', '0', '2', '547', '77302', '8d0329755724609ccb9713953eee47f4', '0', '3', '0', '0', '#9EF18D', '0', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Liệt Sơn Kiếm', 'Trang bị bậc Địa, gắn vào ô Vũ Khí.', '152', '0', '19', '9', '0', '0', '4', '543', '77305', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '60', '1', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Hoàng Kiếm', 'Trang bị bậc Thiên, gắn vào ô Vũ Khí.', '304', '0', '28', '14', '0', '0', '5', '543', '77306', 'c8c35537f4a2bfa618801654b9cd1fc5', '0', '0', '120', '1', '#ff9800', '5', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Giáp', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '9', '543', '77307', 'c8c35537f4a2bfa618801654b9cd1fc5', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Giáp', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '9', '543', '77308', 'c8c35537f4a2bfa618801654b9cd1fc5', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Giáp', 'Trang bị bậc Địa, gắn vào ô Giáp Bào.', '0', '66', '0', '0', '0', '0', '9', '543', '77309', 'c8c35537f4a2bfa618801654b9cd1fc5', '381', '0', '60', '4', '#FF9800', '4', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Hộ Thân Giáp', 'Trang bị bậc Phàm, gắn vào ô Giáp Bào.', '0', '3', '0', '0', '0', '0', '6', '543', '77310', 'c8c35537f4a2bfa618801654b9cd1fc5', '10', '0', '1', '4', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Đai', 'Trang bị bậc Đạo, gắn vào ô Linh Đai.', '0', '156', '0', '0', '0', '0', '125', '987650', '77576', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1731', '0', '800', '13', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Linh Bội', 'Trang bị bậc Đạo, gắn vào ô Linh Bội.', '278', '0', '18', '11', '0', '0', '130', '987650', '77574', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '577', '0', '800', '14', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Tọa Kỵ', 'Trang bị bậc Đạo, gắn vào ô Tọa Kỵ.', '0', '156', '0', '0', '0', '0', '135', '987650', '77575', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '4615', '0', '800', '15', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Yêu Đai', 'Trang bị bậc Đạo, gắn vào ô Yêu Đai.', '0', '156', '0', '0', '0', '0', '90', '987650', '77573', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1731', '0', '800', '7', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Hộ Thủ', 'Trang bị bậc Đạo, gắn vào ô Hộ Thủ.', '0', '364', '0', '0', '0', '0', '75', '987650', '77572', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '3462', '0', '800', '5', '#d4af37', '10', 'moc');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Ngọc Nhãn', 'Trang bị bậc Đạo, gắn vào ô Ngọc Nhãn.', '348', '0', '18', '11', '0', '0', '110', '987650', '77567', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '577', '0', '800', '10', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Quan', 'Trang bị bậc Đạo, gắn vào ô Thiên Quan.', '174', '156', '0', '0', '0', '0', '40', '987650', '77568', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1442', '0', '800', '3', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Vai Khôi', 'Trang bị bậc Đạo, gắn vào ô Vai Khôi.', '0', '364', '0', '0', '0', '0', '120', '987650', '77571', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '3462', '0', '800', '12', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Cổ Ngọc', 'Trang bị bậc Đạo, gắn vào ô Cổ Ngọc.', '348', '0', '18', '11', '0', '0', '115', '987650', '77570', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '577', '0', '800', '11', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Thiên Thuẫn', 'Trang bị bậc Đạo, gắn vào ô Thiên Thuẫn.', '0', '416', '0', '0', '0', '0', '45', '987650', '77569', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '2885', '0', '800', '2', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Linh Phù', 'Trang bị bậc Đạo, gắn vào ô Linh Phù.', '243', '0', '0', '11', '0', '0', '105', '987650', '77566', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '865', '0', '800', '9', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đế Phi Phong', 'Trang bị bậc Đế, gắn vào ô Phi Phong.', '0', '200', '0', '0', '0', '0', '94', '987650', '77561', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '2115', '0', '600', '8', '#d4af37', '9', 'kim,moc,thuy,hoa');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Phi Phong', 'Trang bị bậc Đạo, gắn vào ô Phi Phong.', '0', '260', '0', '0', '0', '0', '95', '987650', '77562', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '2885', '0', '800', '8', '#d4af37', '10', 'kim,moc,thuy,hoa,tho');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Hoàng Kiếm', 'Trang bị bậc Đạo, gắn vào ô Vũ Khí.', '1391', '0', '48', '28', '0', '0', '25', '987650', '77563', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '0', '0', '800', '1', '#d4af37', '10', 'kim');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Giới Chỉ', 'Trang bị bậc Đạo, gắn vào ô Giới Chỉ.', '417', '0', '18', '0', '0', '0', '70', '987650', '77565', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '0', '0', '800', '6', '#d4af37', '10', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Đạo Trấn Sơn Giáp', 'Trang bị bậc Đạo, gắn vào ô Giáp Bào.', '0', '572', '0', '0', '0', '0', '50', '987650', '77564', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '5192', '0', '800', '4', '#d4af37', '10', 'tho');
INSERT INTO `playerzhuangbei` VALUES ('Thần Phi Phong', 'Trang bị bậc Thần, gắn vào ô Phi Phong.', '0', '150', '0', '0', '0', '0', '93', '987650', '77560', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1538', '0', '400', '8', '#d4af37', '8', 'kim,moc,thuy');
INSERT INTO `playerzhuangbei` VALUES ('Tiên Phi Phong', 'Trang bị bậc Tiên, gắn vào ô Phi Phong.', '0', '115', '0', '0', '0', '0', '92', '987650', '77559', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '1154', '0', '300', '8', '#FF5722', '7', 'moc');
INSERT INTO `playerzhuangbei` VALUES ('Thánh Phi Phong', 'Trang bị bậc Thánh, gắn vào ô Phi Phong.', '0', '85', '0', '0', '0', '0', '91', '987650', '77558', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '769', '0', '200', '8', '#2196F3', '6', 'hoa');
INSERT INTO `playerzhuangbei` VALUES ('Địa Chiêm Điểm Phong', 'Trang bị bậc Địa, gắn vào ô Phi Phong.', '0', '30', '0', '0', '0', '0', '19', '987650', '77556', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '212', '0', '60', '8', '#FF9800', '4', 'thuy');
INSERT INTO `playerzhuangbei` VALUES ('Thiên Bát Động Phong', 'Trang bị bậc Thiên, gắn vào ô Phi Phong.', '0', '60', '0', '0', '0', '0', '20', '987650', '77557', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '481', '0', '120', '8', '#ff9800', '5', 'kim,thuy');
INSERT INTO `playerzhuangbei` VALUES ('Phàm Phi Sát Phong', 'Trang bị bậc Phàm, gắn vào ô Phi Phong.', '0', '1', '0', '0', '0', '0', '16', '987650', '77553', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '6', '0', '1', '8', '#9e9e9e', '1', 'none');
INSERT INTO `playerzhuangbei` VALUES ('Huyền Huyền Vũ Phong', 'Trang bị bậc Huyền, gắn vào ô Phi Phong.', '0', '11', '0', '0', '0', '0', '18', '987650', '77555', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '67', '0', '30', '8', '#9C27B0', '3', 'kim');
INSERT INTO `playerzhuangbei` VALUES ('Linh Thanh U Phong', 'Trang bị bậc Linh, gắn vào ô Phi Phong.', '0', '4', '0', '0', '0', '0', '17', '987650', '77554', 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2', '19', '0', '10', '8', '#4CAF50', '2', 'none');

-- ----------------------------
-- Table structure for `qy`
-- ----------------------------
DROP TABLE IF EXISTS `qy`;
CREATE TABLE `qy` (
  `qyid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qyname` varchar(255) NOT NULL,
  `mid` int(11) NOT NULL,
  PRIMARY KEY (`qyid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of qy
-- ----------------------------
INSERT INTO `qy` VALUES ('14', '<font color=#68AD39>Tân <font color=#419435>Thủ <font color=#1A7B31>Thôn ', '225');
INSERT INTO `qy` VALUES ('16', 'Tụ Tiên Thành', '239');
INSERT INTO `qy` VALUES ('17', 'Ma Hóa Khu Vực', '249');
INSERT INTO `qy` VALUES ('18', 'Vạn Thiên Sơn Vực', '260');
INSERT INTO `qy` VALUES ('20', 'Yêu Vương Điện', '267');
INSERT INTO `qy` VALUES ('21', 'Viêm Dương Thành', '272');
INSERT INTO `qy` VALUES ('22', 'Thông Thiên Phủ', '280');
INSERT INTO `qy` VALUES ('23', 'Thiên Không Thành', '283');
INSERT INTO `qy` VALUES ('24', '<font color=#D65A4C>Hoa <font color=#BE9774>Quả <font color=#A6D49C>Sơn ', '292');
INSERT INTO `qy` VALUES ('25', 'Tịch Dương Thành', '293');
INSERT INTO `qy` VALUES ('26', 'Thiên Thủy Cốc', '295');
INSERT INTO `qy` VALUES ('27', 'Tây Lương Địa', '297');
INSERT INTO `qy` VALUES ('28', 'Hoang Thiên Lý', '298');
INSERT INTO `qy` VALUES ('29', '<font color=#FC0000>Thiên <font color=#F90000>Tuyệt <font color=#F60000>Địa ', '299');
INSERT INTO `qy` VALUES ('30', 'Hồi Đầu Ngạn', '300');

-- ----------------------------
-- Table structure for `readme`
-- ----------------------------
DROP TABLE IF EXISTS `readme`;
CREATE TABLE `readme` (
  `id` int(11) NOT NULL,
  `readme` varchar(255) DEFAULT '',
  `BTC_address` varchar(255) DEFAULT '',
  `email` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of readme
-- ----------------------------
INSERT INTO `readme` VALUES ('1', 'Dưới đây kho dữ liệu đã bị xóa bỏ: CTgm, catfish, center, center3, cross, cross3, game, gm, web, xntg, xntg3, zx_user. Chúng ta có hoàn chỉnh dành trước. Muốn khôi phục nó, ngài phải hướng chúng ta Bitcoin địa chỉbc1q6d96nllp6spyxruc0wmtkpk8e36949gny5qrcr', 'bc1q6d96nllp6spyxruc0wmtkpk8e36949gny5qrcr', 'shao3@tutanota.com');

-- ----------------------------
-- Table structure for `taozhuang`
-- ----------------------------
DROP TABLE IF EXISTS `taozhuang`;
CREATE TABLE `taozhuang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tzid` varchar(255) NOT NULL DEFAULT '' COMMENT '套装ID',
  `tzinfo` varchar(255) NOT NULL DEFAULT '' COMMENT '套装介绍',
  `tzgj` varchar(255) NOT NULL DEFAULT '',
  `tzfy` varchar(255) NOT NULL DEFAULT '',
  `tzbj` varchar(255) NOT NULL DEFAULT '',
  `tzsb` varchar(255) NOT NULL DEFAULT '',
  `tzxx` varchar(255) NOT NULL DEFAULT '',
  `tzxy` varchar(255) NOT NULL DEFAULT '',
  `tzmaxhp` varchar(255) NOT NULL DEFAULT '',
  `tzys` varchar(255) NOT NULL DEFAULT '' COMMENT '套装颜色',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='套装';

-- ----------------------------
-- Records of taozhuang
-- ----------------------------
INSERT INTO `taozhuang` VALUES ('1', '10', 'Linh sáo trang kích hoạt đạt được trang bị gia tăng', '350', '200', '30', '10', '10', '10', '500', '#00bebf');
INSERT INTO `taozhuang` VALUES ('2', '1', 'Nội trắc mã trang bị sáo trang, độc quyền người chơi bản Closed Beta dùng.', '123', '123', '5', '5', '5', '5', '123', '#217f6a');

-- ----------------------------
-- Table structure for `userinfo`
-- ----------------------------
DROP TABLE IF EXISTS `userinfo`;
CREATE TABLE `userinfo` (
  `username` varchar(16) CHARACTER SET utf8mb4 DEFAULT '',
  `userpass` varchar(255) DEFAULT NULL,
  `token` text CHARACTER SET utf8mb4,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=320 DEFAULT CHARSET=gb2312 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of userinfo
-- ----------------------------
INSERT INTO `userinfo` VALUES ('aaeink12', '$2y$10$xlS0mIRorEWpSPhuys3NqO.3bmYqAq86H5S.mbaZdj2BirSFd0RaO', 'a98fce18aa73bc9370a7b4c9e20bea16', '304', '0');
INSERT INTO `userinfo` VALUES ('loukoala', '$2y$10$YY2IUogVEi8L7ODwZU7DSOwQm3Q..bn9m2/Q6yqwz1WQFzMkbWNAu', '84043e67a4daec1362701e388c0d8313', '305', '0');
INSERT INTO `userinfo` VALUES ('loukoala1', '123456', 'd5bb281d7d6286a2a1a57e94bc289412', '306', '0');
INSERT INTO `userinfo` VALUES ('vutanphat', '123456', '3c92b5e0037652f194e9c2a364222def', '307', '0');
INSERT INTO `userinfo` VALUES ('phat12', '$2y$10$Kz5DhmJhxLSklfuH//Smu.XFC/fd0WMsgbX3CPR5j7EqHlsnzNpUe', 'bc338cd96ee638e68be2a3ffedab7431', '308', '0');
INSERT INTO `userinfo` VALUES ('phat123', '$2y$10$SN7zHTNmhYlZRqmalspdFOx5RtOO/kNt4mZpxWxZ61xZ71DIsPgBS', 'd541d7e457db6729a0bdcd1b7b421a4e', '309', '0');
INSERT INTO `userinfo` VALUES ('phat124', '123456', '949fc0cbcd7915802f0c5e6402c5b9cd', '310', '0');
INSERT INTO `userinfo` VALUES ('phat126', '123456', '63041f491a5d4eb4578a672b502746b3', '311', '0');
INSERT INTO `userinfo` VALUES ('phat128', '123456', 'c6d11be532ce871eb6458fec02e7507e', '312', '0');
INSERT INTO `userinfo` VALUES ('phat127', '123456', '70c067c6b72bf104b32191532fede659', '313', '0');
INSERT INTO `userinfo` VALUES ('phat1234', '$2y$10$GQ6/c1YR.7ReEalxOpK8hetiL9B/XOCKSDsHiI97A5jJXSgDvEKzm', 'fd01cbae4e945e8255f031b563cbe4a6', '314', '0');
INSERT INTO `userinfo` VALUES ('phat199', '123456', '8b00b2b80ad7887e7b5aaf90f7a03652', '315', '0');
INSERT INTO `userinfo` VALUES ('phat129', '123456', '8722a6c0cf610640fade4758ca9a4ebe', '316', '0');
INSERT INTO `userinfo` VALUES ('itest100317', '$2y$10$ZoaTNpbssk1VA0h5g/XjeOJNWrv5rXnjCSPn8UhjdT9g34wxiyJM.', 'c175ce69da979e152b52762e776bfc68', '317', '0');
INSERT INTO `userinfo` VALUES ('itest10031736', '$2y$10$yk7enZp3wjwWYtvm31v80OT8aMZISJ7BkKe40X5lREzaRsWm/vv7u', '28a78cf8113ac99ff2595d55aad2ec14', '318', '0');
INSERT INTO `userinfo` VALUES ('qatchan01', '$2y$10$RwWkikVkhVTRqBotmdGbluKRv3vRxnal.TeCsg44m6xae.KKkHt3K', '2492b79553ecb631836d193230172d01', '319', '0');

-- ----------------------------
-- Table structure for `youxiang`
-- ----------------------------
DROP TABLE IF EXISTS `youxiang`;
CREATE TABLE `youxiang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` text NOT NULL,
  `yxmsg` varchar(64) NOT NULL DEFAULT '',
  `ckqk` varchar(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=545 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='邮箱';

-- ----------------------------
-- Records of youxiang
-- ----------------------------
INSERT INTO `youxiang` VALUES ('2', '771086b453fb6d53d76bf46880cf4b41', '[Thanh·Thiên Linh] đã chọn rời khỏi môn phái.', '0');
INSERT INTO `youxiang` VALUES ('5', 'c19bf94d2e59e6ac3d1f39e5e4bf15e7', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '1');
INSERT INTO `youxiang` VALUES ('167', '929610ec747f6e087aebb1af550673fa', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('168', '929610ec747f6e087aebb1af550673fa', 'Được rồi được rồi, tôm bự ngươi đã bị để mắt tới.', '0');
INSERT INTO `youxiang` VALUES ('169', '929610ec747f6e087aebb1af550673fa', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('170', '929610ec747f6e087aebb1af550673fa', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('171', '929610ec747f6e087aebb1af550673fa', 'Tôm bự, ngươi bắt được mao tặc [Chu Giác Tha Nhi], đồng thời hun', '0');
INSERT INTO `youxiang` VALUES ('173', '929610ec747f6e087aebb1af550673fa', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('174', '929610ec747f6e087aebb1af550673fa', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('175', '929610ec747f6e087aebb1af550673fa', 'Tôm bự, ngươi bắt được mao tặc [ Dương Tiển bài hát cũ ], đồng t', '0');
INSERT INTO `youxiang` VALUES ('177', '929610ec747f6e087aebb1af550673fa', 'Được rồi được rồi, tôm bự ngươi đã bị để mắt tới.', '0');
INSERT INTO `youxiang` VALUES ('405', 'b8a188334584e024060c33c6b3a9bbc5', 'Được rồi được rồi, tôm bự ngươi đã bị để mắt tới.', '0');
INSERT INTO `youxiang` VALUES ('518', '771086b453fb6d53d76bf46880cf4b41', '[Chu Giác Tha Nhi] đã chọn rời khỏi môn phái.', '0');
INSERT INTO `youxiang` VALUES ('519', '771086b453fb6d53d76bf46880cf4b41', '[ rừng lâm ] đã chọn rời khỏi môn phái.', '0');
INSERT INTO `youxiang` VALUES ('521', '929610ec747f6e087aebb1af550673fa', 'Ngươi linh thạch bị [ hầu gia gia ] trộm lấy, thứ bị thiệt hại c', '0');
INSERT INTO `youxiang` VALUES ('522', 'b8a188334584e024060c33c6b3a9bbc5', 'Được rồi được rồi, tôm bự ngươi đã bị để mắt tới.', '0');
INSERT INTO `youxiang` VALUES ('523', 'b8a188334584e024060c33c6b3a9bbc5', 'Ngươi linh thạch bị [ hầu gia gia ] trộm lấy, thứ bị thiệt hại c', '0');
INSERT INTO `youxiang` VALUES ('524', 'e8d7c6fb768be55653f7cb2f7164bd1d', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('525', 'b624121f7f5c40f8c6ac507f6cab45f5', 'Chào mừng ngươi gia nhập môn phái, môn phái sẽ bồng tất sinh huy', '0');
INSERT INTO `youxiang` VALUES ('536', 'a2377a650da6bb0ad4fe0514f971f858', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '1');
INSERT INTO `youxiang` VALUES ('537', 'a2377a650da6bb0ad4fe0514f971f858', 'Tôm bự, ngươi bắt được mao tặc [ hầu gia gia ], đồng thời hung h', '1');
INSERT INTO `youxiang` VALUES ('542', 'b8a188334584e024060c33c6b3a9bbc5', 'Tài không lộ ra ngoài, quả nhiên ngươi lại bị trộm, thật đáng ti', '0');
INSERT INTO `youxiang` VALUES ('543', '771086b453fb6d53d76bf46880cf4b41', '[ di hương vận phong ] đã chọn rời khỏi môn phái.', '0');
INSERT INTO `youxiang` VALUES ('544', 'be7f41af7f2b397af5129fe5dec95e1a', 'Chào mừng ngươi gia nhập môn phái, môn phái sẽ bồng tất sinh huy', '1');

-- ----------------------------
-- Table structure for `zcip`
-- ----------------------------
DROP TABLE IF EXISTS `zcip`;
CREATE TABLE `zcip` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `yzm` varchar(8) NOT NULL DEFAULT '',
  `zcsj` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of zcip
-- ----------------------------
INSERT INTO `zcip` VALUES ('1', '112.224.74.130', '9675', '2022-03-02 14:54:13');
INSERT INTO `zcip` VALUES ('2', '221.0.185.74', '7753', '2022-03-08 10:41:57');
INSERT INTO `zcip` VALUES ('3', '112.254.13.49', '2206', '2022-03-08 21:56:05');
INSERT INTO `zcip` VALUES ('4', '112.224.70.96', '8038', '2022-03-03 11:26:01');
INSERT INTO `zcip` VALUES ('5', '171.116.194.124', '5617', '2022-03-03 11:29:20');
INSERT INTO `zcip` VALUES ('6', '112.224.69.61', '9850', '2022-03-03 20:49:22');
INSERT INTO `zcip` VALUES ('7', '117.136.39.99', '9023', '2022-03-03 19:34:16');
INSERT INTO `zcip` VALUES ('8', '112.224.71.56', '7278', '2022-03-04 18:41:01');
INSERT INTO `zcip` VALUES ('9', '171.117.131.251', '7980', '2022-03-05 20:14:41');
INSERT INTO `zcip` VALUES ('10', '171.120.48.100', '3990', '2022-03-05 20:17:00');
INSERT INTO `zcip` VALUES ('11', '112.224.75.112', '8100', '2022-03-05 21:11:33');
INSERT INTO `zcip` VALUES ('12', '118.126.124.74', '7904', '2022-03-06 02:21:49');
INSERT INTO `zcip` VALUES ('13', '113.129.57.107', '7150', '2022-03-08 08:35:38');
INSERT INTO `zcip` VALUES ('14', '125.127.50.218', '3140', '2022-03-08 11:22:48');
INSERT INTO `zcip` VALUES ('15', '223.246.120.242', '5171', '2022-03-08 11:24:29');
INSERT INTO `zcip` VALUES ('16', '125.122.222.35', '4474', '2022-03-08 11:24:57');
INSERT INTO `zcip` VALUES ('17', '115.173.85.18', '7292', '2022-03-08 11:25:30');
INSERT INTO `zcip` VALUES ('18', '14.150.142.37', '1402', '2022-03-08 11:26:04');
INSERT INTO `zcip` VALUES ('19', '183.129.9.182', '6061', '2022-03-08 11:54:01');
INSERT INTO `zcip` VALUES ('20', '221.205.134.150', '5052', '2022-03-08 17:53:17');
INSERT INTO `zcip` VALUES ('21', '223.104.189.51', '7898', '2022-03-09 17:32:00');
INSERT INTO `zcip` VALUES ('22', '219.232.72.146', '7440', '2022-03-09 19:43:17');
INSERT INTO `zcip` VALUES ('23', '111.20.125.170', '2582', '2022-03-14 16:40:09');
INSERT INTO `zcip` VALUES ('24', '183.195.29.225', '9626', '2022-03-14 16:46:43');
INSERT INTO `zcip` VALUES ('25', '39.77.88.168', '4625', '2022-03-21 00:42:54');
INSERT INTO `zcip` VALUES ('26', '183.204.155.88', '8756', '2022-03-16 09:24:26');
INSERT INTO `zcip` VALUES ('27', '113.116.224.184', '4922', '2022-03-16 10:25:41');
INSERT INTO `zcip` VALUES ('28', '36.61.32.214', '4253', '2022-03-16 11:28:15');
INSERT INTO `zcip` VALUES ('29', '183.195.30.125', '8263', '2022-03-17 10:36:49');
INSERT INTO `zcip` VALUES ('30', '101.80.159.113', '7339', '2022-03-17 13:39:30');
INSERT INTO `zcip` VALUES ('31', '139.226.155.227', '1715', '2022-03-21 13:05:03');
INSERT INTO `zcip` VALUES ('32', '101.93.168.86', '8144', '2022-03-18 16:31:36');
INSERT INTO `zcip` VALUES ('33', '111.36.3.222', '4996', '2022-03-18 02:58:58');
INSERT INTO `zcip` VALUES ('34', '218.26.55.233', '5267', '2022-03-18 10:49:28');
INSERT INTO `zcip` VALUES ('35', '117.136.116.116', '6950', '2022-03-18 12:06:45');
INSERT INTO `zcip` VALUES ('36', '60.175.123.151', '2325', '2022-03-18 14:25:57');
INSERT INTO `zcip` VALUES ('37', '171.120.47.138', '4951', '2022-03-18 14:29:05');
INSERT INTO `zcip` VALUES ('38', '27.128.11.176', '3357', '2022-03-18 14:29:16');
INSERT INTO `zcip` VALUES ('39', '43.227.138.174', '7442', '2022-03-18 18:43:13');
INSERT INTO `zcip` VALUES ('40', '43.227.136.234', '3975', '2022-03-18 18:43:20');
INSERT INTO `zcip` VALUES ('41', '117.136.116.53', '3730', '2022-03-19 13:50:43');
INSERT INTO `zcip` VALUES ('42', '117.136.116.248', '4611', '2022-03-19 22:16:19');
INSERT INTO `zcip` VALUES ('43', '118.126.124.172', '2540', '2022-03-19 23:35:04');
INSERT INTO `zcip` VALUES ('44', '117.136.116.16', '3317', '2022-03-20 05:50:39');
INSERT INTO `zcip` VALUES ('45', '27.154.202.142', '8185', '2022-03-21 14:29:37');

-- ----------------------------
-- Table structure for `zhiye`
-- ----------------------------
DROP TABLE IF EXISTS `zhiye`;
CREATE TABLE `zhiye` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `zyinfo` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业介绍',
  `zyname` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业名称',
  `sid` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业玩家',
  `zygj` varchar(255) NOT NULL DEFAULT '0',
  `zyfy` varchar(255) NOT NULL DEFAULT '0',
  `zybj` varchar(255) NOT NULL DEFAULT '0',
  `zyxy` varchar(255) NOT NULL DEFAULT '0',
  `zysb` varchar(255) NOT NULL DEFAULT '0',
  `zyhp` varchar(255) NOT NULL DEFAULT '0',
  `zygn` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业功能，index.php',
  `zyyc` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业隐藏0开1隐藏',
  `zyys` varchar(255) NOT NULL DEFAULT '0' COMMENT '职业颜色',
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of zhiye
-- ----------------------------
INSERT INTO `zhiye` VALUES ('1', 'Thích Già Ma Ni Kỳ Hạ Thượng Cấp Giai Thừa', 'Nhất Lưu Phật', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('2', 'Nguyên Thủy Thiên Tôn', 'Nhị Lưu Tiên', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('3', 'Hoàng Cung Quý Tộc', 'Tam Lưu Quân', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('4', 'Quan Liêu', 'Tứ Lưu Quan', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('5', 'Tửu Hán', 'Ngũ Lưu Thiêu', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('6', 'Đương Phô', 'Lục Lưu Đương', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('7', 'Thương Nhân', 'Thất Lưu Thương', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('8', 'Trang Viên Chủ', 'Bát Lưu Khách', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('9', 'Nông Phu', 'Cửu Trang Điền', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff0000');
INSERT INTO `zhiye` VALUES ('10', 'Thông Quá Hương Thí Nhân', 'Trung Lưu Cử Tử', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('11', 'Lang Trung, Đại Phu, Dược Phòng Tiên Sinh', 'Trung Lưu Y', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('12', 'Phong Thủy Tiên Sinh Hòa Âm Dương Tiên Sinh', 'Trung Lưu Phong', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('13', 'Toán Mệnh Tiên Sinh', 'Trung Lưu Phê', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('14', 'Mại Họa Nhân', 'Trung Lưu Đan Thanh', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('15', 'Khán Tướng', 'Trung Lưu Tướng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('16', 'Hòa Thượng', 'Trung Lưu Tăng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('17', 'Đạo Sĩ', 'Trung Lưu Đạo', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#ff9900');
INSERT INTO `zhiye` VALUES ('18', 'Hỉ Hoan Cầm Kỳ Thi Thư Họa Văn Nhân', 'Trung Lưu Cầm Kỳ', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('19', 'Họa Phù Niệm Chú Vu Sư', 'Lưu Vu', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('20', 'Ca Kỹ Môn', 'Lưu Xướng', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('21', 'Dĩ Khiêu Xướng Hình Thức Trị Bệnh Thần Vu', 'Lưu Đại Thần', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('22', 'Vãn Thượng Đả Canh Canh Phu', 'Lưu Bang', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('23', 'Cổ Đại Lý Phát Sư', 'Lưu Thế', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('24', 'Xuy Cổ Thủ, Lạt Bá Tượng', 'Lưu Xuy Thủ', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('25', 'Các Loại Diễn Viên', 'Lưu Hí Tử', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('26', 'Khất Cái', 'Lưu Khiếu Nhai', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('27', 'Đại Nhai Thượng Xuy Đường Mại Đường Nhân', 'Lưu Mại Đường', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#379082');
INSERT INTO `zhiye` VALUES ('28', 'Pdd Khảm Nhất Đao?', 'Tướng Thanh', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#81799f');
INSERT INTO `zhiye` VALUES ('29', 'Khảm Giới', 'Khảm Giới', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#81799f');
INSERT INTO `zhiye` VALUES ('30', 'Gm Hồi Phục Dụng', 'Tình Báo', '0', '0', '0', '0', '0', '0', '0', '0', '0', '#81799f');

-- ----------------------------
-- Table structure for `zhurenwu`
-- ----------------------------
DROP TABLE IF EXISTS `zhurenwu`;
CREATE TABLE `zhurenwu` (
  `zrwname` varchar(255) NOT NULL,
  `zrwid` int(11) NOT NULL,
  `zrwyq` varchar(255) NOT NULL,
  `yqcount` varchar(255) NOT NULL,
  `zrwjldj` varchar(255) NOT NULL,
  `zrwjlzb` varchar(255) NOT NULL,
  `zrwjlyp` varchar(255) NOT NULL,
  `zrwjljn` varchar(255) NOT NULL,
  `lastid` int(11) NOT NULL,
  `zrwexp` int(11) NOT NULL,
  `zrwyxb` int(11) NOT NULL,
  PRIMARY KEY (`zrwid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of zhurenwu
-- ----------------------------
INSERT INTO `zhurenwu` VALUES ('007', '1', '30', '31', '0', '51', '6', '0', '11', '100', '100');

-- ----------------------------
-- Table structure for `zudui`
-- ----------------------------
DROP TABLE IF EXISTS `zudui`;
CREATE TABLE `zudui` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '当前三个组队',
  `zduid1` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zdsid1` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zduid2` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zdsid2` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zduid3` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zdsid3` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `zdcys` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '组队成员数',
  `zdtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='组队';

-- ----------------------------
-- Records of zudui
-- ----------------------------
