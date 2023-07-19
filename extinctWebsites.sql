-- Adminer 4.8.0 MySQL 8.0.32-0ubuntu0.20.04.2 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `contract`;
CREATE TABLE `contract` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contracted` tinyint(1) DEFAULT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `id_url` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_contract_url` (`id_url`),
  CONSTRAINT `fk_contract_url` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `exticint`;
CREATE TABLE `exticint` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_url` int NOT NULL,
  `exticintDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_url` (`id_url`),
  CONSTRAINT `exticint_ibfk_1` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `harvest`;
CREATE TABLE `harvest` (
  `id` int NOT NULL AUTO_INCREMENT,
  `timestamp` date NOT NULL,
  `crawler` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `harvest` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `id_url` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_harvest_harvest` (`id_url`),
  CONSTRAINT `fk_harvest_harvest` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `harvest_report`;
CREATE TABLE `harvest_report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` int DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `seed` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `uuid_final_url` text COLLATE utf8mb4_czech_ci,
  `redirect_depth` int DEFAULT '0',
  `id_harvest` int DEFAULT '0',
  `contentType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `Encoding` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `contentLength` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `serverEngine` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `serverVersion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `Error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  PRIMARY KEY (`id`),
  KEY `fk_report_harvest` (`id_harvest`),
  CONSTRAINT `fk_report_harvest` FOREIGN KEY (`id_harvest`) REFERENCES `harvest` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `name_servers`;
CREATE TABLE `name_servers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `srv` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `srv_ip` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `id_whois` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_whois` (`id_whois`),
  CONSTRAINT `name_servers_ibfk_1` FOREIGN KEY (`id_whois`) REFERENCES `whois` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `page_data`;
CREATE TABLE `page_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `date` date DEFAULT NULL,
  `id_url` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_url` (`id_url`),
  CONSTRAINT `page_data_ibfk_1` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `request`;
CREATE TABLE `request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_url` int NOT NULL,
  `date` datetime NOT NULL,
  `finished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_url` (`id_url`),
  CONSTRAINT `request_ibfk_1` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_url` int NOT NULL,
  `dead` tinyint NOT NULL DEFAULT '0',
  `confirmed` tinyint NOT NULL,
  `requires` tinyint NOT NULL,
  `metadata` tinyint NOT NULL,
  `metadata_match` smallint NOT NULL,
  `whois` smallint NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_url` (`id_url`),
  CONSTRAINT `status_ibfk_1` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `updated_date`;
CREATE TABLE `updated_date` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `id_whois` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_whois` (`id_whois`),
  CONSTRAINT `updated_date_ibfk_1` FOREIGN KEY (`id_whois`) REFERENCES `whois` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `url`;
CREATE TABLE `url` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `uuid` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `url_group`;
CREATE TABLE `url_group` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_url` int NOT NULL,
  `groupname` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `autocheck` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `lastcheck` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_url` (`id_url`),
  CONSTRAINT `url_group_ibfk_1` FOREIGN KEY (`id_url`) REFERENCES `url` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `whois`;
CREATE TABLE `whois` (
  `id` int NOT NULL AUTO_INCREMENT,
  `domain_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `registrant_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `registrar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `expiration_date` datetime NOT NULL,
  `id_url` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


-- 2023-07-19 14:28:04

