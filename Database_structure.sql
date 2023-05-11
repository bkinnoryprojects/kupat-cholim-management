-- Adminer 4.8.1 MySQL 8.0.31 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE TABLE `corona_infections` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `personalinfoID` int NOT NULL,
  `PositiveResultDate` date NOT NULL,
  `RecoveryDate` date NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `personalinfoID` (`personalinfoID`),
  CONSTRAINT `corona_infections_ibfk_2` FOREIGN KEY (`personalinfoID`) REFERENCES `personalinfo` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `corona_vaccinations` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `PersonalInfoID` int DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Manufacturer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`ID`),
  KEY `PersonalInfoID` (`PersonalInfoID`),
  CONSTRAINT `corona_vaccinations_ibfk_2` FOREIGN KEY (`PersonalInfoID`) REFERENCES `personalinfo` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `personalinfo` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `LastName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IDCard` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MobilePhone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IDCard` (`IDCard`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2023-05-11 09:55:15
