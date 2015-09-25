-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2014 年 05 月 25 日 23:39
-- 服务器版本: 5.5.23
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `app_cctv15`
--

-- --------------------------------------------------------

--
-- 表的结构 `record`
--

DROP TABLE IF EXISTS `record`;
CREATE TABLE IF NOT EXISTS `record` (
  `openid` varchar(28) NOT NULL COMMENT '微信ID',
  `question` int(5) DEFAULT '-1' COMMENT '答题偏移',
  `question_y` int(5) DEFAULT '0' COMMENT '答题正确',
  `question_n` int(5) DEFAULT '0' COMMENT '答题错误',
  PRIMARY KEY (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

--
-- 转存表中的数据 `record`
--

INSERT INTO `record` (`openid`, `question`, `question_y`, `question_n`) VALUES
('ollB4jtmI_i8CqYlj-QMiujO_e_c', 3, 2, 1),
('ollB4jp1WeXXgyQoEk-BfyuqqvgQ', -1, 0, 0),
('oDeOAjj54GvEkkgCV2d7QV4-JLMc', 18, 17, 1),
('oQW8FuNH_V1zk4x3zTN1IMoAZDW0', 1, 1, 0),
('ollB4jqkxuXowpfVzgAmE0APDoJ8', 31, 13, 18),
('ollB4jiZJlxCNM8uzL2koTeDJP8U', -1, 0, 0);
