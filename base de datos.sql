-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.38-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.1.0.6116
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for integracion
CREATE DATABASE IF NOT EXISTS `integracion` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `integracion`;

-- Dumping structure for table integracion.cruge_session
CREATE TABLE IF NOT EXISTS `cruge_session` (
  `idsession` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` varchar(50) NOT NULL DEFAULT '',
  `created` bigint(30) DEFAULT NULL,
  `expire` bigint(30) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `ipaddress` varchar(45) DEFAULT NULL,
  `usagecount` int(11) DEFAULT '0',
  `lastusage` bigint(30) DEFAULT NULL,
  `logoutdate` bigint(30) DEFAULT NULL,
  `ipaddressout` varchar(45) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idsession`) USING BTREE,
  KEY `crugesession_iduser` (`iduser`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=152222 DEFAULT CHARSET=latin1;

-- Dumping data for table integracion.cruge_session: ~1 rows (approximately)
/*!40000 ALTER TABLE `cruge_session` DISABLE KEYS */;
INSERT INTO `cruge_session` (`idsession`, `iduser`, `created`, `expire`, `status`, `ipaddress`, `usagecount`, `lastusage`, `logoutdate`, `ipaddressout`, `version`) VALUES
	(152221, 'A-240', NULL, NULL, 0, 'ERTOKEN', 0, NULL, NULL, 'ERTOKEN', NULL);
/*!40000 ALTER TABLE `cruge_session` ENABLE KEYS */;

-- Dumping structure for table integracion.gcca
CREATE TABLE IF NOT EXISTS `gcca` (
  `GCCA_Id` int(11) NOT NULL AUTO_INCREMENT,
  `GCCA_Cod` varchar(45) DEFAULT NULL,
  `GCCA_Nombre` varchar(45) DEFAULT NULL,
  `GCCA_Date` datetime DEFAULT CURRENT_TIMESTAMP,
  `GCCA_Country` varchar(3) DEFAULT NULL,
  `GCCA_Email` varchar(50) NOT NULL,
  `GCCA_Tv` varchar(5) NOT NULL,
  `GCCA_Type` varchar(5) DEFAULT NULL,
  `GCCD_Id` int(11) NOT NULL,
  `GCCA_Address` text,
  `GCCA_RIF` varchar(45) DEFAULT NULL,
  `GCCA_Fullname` varchar(45) DEFAULT NULL,
  `GCCA_status` tinyint(4) DEFAULT NULL COMMENT '0 Solo reportes\r\n1 Ventas Activas\r\n2 Bloqueadas\r\n3 Ocultas\r\n\r\n',
  `GCCA_Phone` varchar(45) DEFAULT NULL,
  `GCCA_Promo` varchar(45) DEFAULT NULL,
  `PCDI_Id` int(11) DEFAULT NULL,
  `GCCA_Step` int(10) unsigned DEFAULT NULL COMMENT '0 new, 1 email, 2 doc, 3 rookie, 4, 5, 6, etc',
  `GCCA_Birth` datetime DEFAULT NULL,
  `GCCA_Gender` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`GCCA_Id`,`GCCD_Id`) USING BTREE,
  KEY `fk_gcca_gccd_idx` (`GCCD_Id`) USING BTREE,
  KEY `fk_gcca_pcdi` (`PCDI_Id`) USING BTREE,
  KEY `GCCA_status` (`GCCA_status`) USING BTREE,
  CONSTRAINT `fk_gcca_gccd` FOREIGN KEY (`GCCD_Id`) REFERENCES `king_gecko_new`.`gccd` (`GCCD_Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gcca_pcdi` FOREIGN KEY (`PCDI_Id`) REFERENCES `king_gecko_new`.`pcdi` (`PCDI_Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=latin1 COMMENT='agencia\nPK compuesta';

-- Dumping data for table integracion.gcca: ~1 rows (approximately)
/*!40000 ALTER TABLE `gcca` DISABLE KEYS */;
INSERT INTO `gcca` (`GCCA_Id`, `GCCA_Cod`, `GCCA_Nombre`, `GCCA_Date`, `GCCA_Country`, `GCCA_Email`, `GCCA_Tv`, `GCCA_Type`, `GCCD_Id`, `GCCA_Address`, `GCCA_RIF`, `GCCA_Fullname`, `GCCA_status`, `GCCA_Phone`, `GCCA_Promo`, `PCDI_Id`, `GCCA_Step`, `GCCA_Birth`, `GCCA_Gender`) VALUES
	(240, 'usuario240', 'Usuario 240', '2021-04-28 17:30:43', NULL, '', '', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL);
/*!40000 ALTER TABLE `gcca` ENABLE KEYS */;

-- Dumping structure for table integracion.gccd
CREATE TABLE IF NOT EXISTS `gccd` (
  `GCCD_Id` int(11) NOT NULL AUTO_INCREMENT,
  `GCCD_Cod` varchar(45) NOT NULL,
  `GCCD_Nombre` varchar(45) DEFAULT NULL,
  `GCCD_IdSuperior` int(11) DEFAULT NULL,
  `GCCU_Id` int(11) NOT NULL,
  `GCCD_Estado` int(11) NOT NULL COMMENT 'Estado de grupo',
  `GCCD_Responsable` varchar(45) DEFAULT NULL,
  `GCCD_telefono` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`GCCD_Id`) USING BTREE,
  UNIQUE KEY `GCCD_Cod_GCCD_IdSuperior` (`GCCD_Cod`,`GCCD_IdSuperior`) USING BTREE,
  KEY `fk_gccd_gccd_idx` (`GCCD_IdSuperior`) USING BTREE,
  KEY `fk_gccd_gccu_idx` (`GCCU_Id`) USING BTREE,
  CONSTRAINT `fk_gccd_gccd` FOREIGN KEY (`GCCD_IdSuperior`) REFERENCES `king_gecko_new`.`gccd` (`GCCD_Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_gccd_gccu` FOREIGN KEY (`GCCU_Id`) REFERENCES `king_gecko_new`.`gccu` (`GCCU_Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=latin1 COMMENT='grupo\nGCUC_Id Estado del grupo';

-- Dumping data for table integracion.gccd: ~1 rows (approximately)
/*!40000 ALTER TABLE `gccd` DISABLE KEYS */;
INSERT INTO `gccd` (`GCCD_Id`, `GCCD_Cod`, `GCCD_Nombre`, `GCCD_IdSuperior`, `GCCU_Id`, `GCCD_Estado`, `GCCD_Responsable`, `GCCD_telefono`) VALUES
	(1, 'DEMO', 'Demostracion', 1, 1, 1, NULL, NULL);
/*!40000 ALTER TABLE `gccd` ENABLE KEYS */;

-- Dumping structure for table integracion.gcce
CREATE TABLE IF NOT EXISTS `gcce` (
  `GCCE_Id` int(11) NOT NULL AUTO_INCREMENT,
  `GCCA_Id` int(11) DEFAULT NULL COMMENT 'agencia',
  `GCCD_Id` int(11) DEFAULT NULL COMMENT 'grupo',
  `GCCE_PorcentajeVentasD` decimal(10,2) DEFAULT NULL COMMENT 'porcentaje Ventas directa',
  `GCCE_PorcentajeVentasP` decimal(10,2) DEFAULT NULL COMMENT 'porcentaje de Ventas para parley',
  `GCCE_ControlEsquema` varchar(45) DEFAULT '0-0-0-0' COMMENT 'no se para que era',
  `GCCE_PorcentajeUtilidad` int(11) DEFAULT '0' COMMENT 'porcentaje utilidad parlay grupos',
  `GCCE_PorcentajePerdida` int(11) DEFAULT '0' COMMENT 'porcentaje perdidas parlay grupos',
  `GCCE_Fecha` datetime DEFAULT NULL COMMENT 'timestamp',
  `GCCE_ApMinDirecta` int(11) DEFAULT '0',
  `GCCE_ApMinParley` int(11) DEFAULT '0',
  `GCCE_ApMaxDirecta` int(11) DEFAULT '0',
  `GCCE_ApMaxParley` int(11) DEFAULT '0',
  `GCCE_PreMaxDirecta` int(11) DEFAULT '0' COMMENT 'premio max directa',
  `GCCE_PreMaxParley` int(11) DEFAULT '0' COMMENT 'premio maximo parlay',
  `GCCE_MaxMulti` int(11) DEFAULT '0' COMMENT 'multiplicador maximo',
  `GCCE_MaxJugada` int(11) DEFAULT '0' COMMENT 'jugadas maximas',
  `GCCE_MinJugada` int(11) DEFAULT '0' COMMENT 'jugadas minimas',
  `GCCE_RepetidosMonto` int(11) DEFAULT '0',
  `GCCE_CupoDeuda` int(11) DEFAULT '0' COMMENT 'Endeudamiento',
  `GCCE_Repetidos` int(10) unsigned NOT NULL DEFAULT '2' COMMENT 'Cantidad de parlays repetidos',
  `GCCE_Label` varchar(160) DEFAULT '0' COMMENT 'Mensaje personalizado por agencia',
  `GCCE_Currency` varchar(4) DEFAULT NULL COMMENT 'Moneda del Control',
  `GCCE_Enabled` varchar(4) DEFAULT NULL,
  `GCCP_Id` int(11) NOT NULL,
  PRIMARY KEY (`GCCE_Id`) USING BTREE,
  KEY `fk_gcce_gcca_idx` (`GCCA_Id`,`GCCD_Id`) USING BTREE,
  KEY `fk_gcce_gccd_idx` (`GCCD_Id`) USING BTREE,
  KEY `fk_gcce_gccp1_idx` (`GCCP_Id`) USING BTREE,
  KEY `GCCE_Currency` (`GCCE_Currency`) USING BTREE,
  CONSTRAINT `fk_gcce_gcca` FOREIGN KEY (`GCCA_Id`, `GCCD_Id`) REFERENCES `king_gecko_new`.`gcca` (`GCCA_Id`, `GCCD_Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gcce_gccd` FOREIGN KEY (`GCCD_Id`) REFERENCES `king_gecko_new`.`gccd` (`GCCD_Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=370054 DEFAULT CHARSET=latin1 COMMENT='RelacionProductos o Tabla permisologia\n--\n ';

-- Dumping data for table integracion.gcce: ~0 rows (approximately)
/*!40000 ALTER TABLE `gcce` DISABLE KEYS */;
/*!40000 ALTER TABLE `gcce` ENABLE KEYS */;

-- Dumping structure for table integracion.gccs
CREATE TABLE IF NOT EXISTS `gccs` (
  `GCCS_Id` int(11) NOT NULL AUTO_INCREMENT,
  `GCCS_Fecha` datetime NOT NULL,
  `GCCS_Monto` decimal(16,2) NOT NULL DEFAULT '0.00',
  `GCCS_Descripcion` text,
  `GCCD_Id` int(11) NOT NULL,
  `GCCA_Id` int(11) DEFAULT NULL,
  `GCUA_Id` int(11) NOT NULL,
  `GCUI_Id` int(11) NOT NULL,
  `GCCS_Usuario` varchar(45) NOT NULL,
  `GCUT_IdOrigen` int(11) DEFAULT NULL,
  `GCUT_IdDestino` int(11) DEFAULT NULL,
  `GCCS_FechaRef` date DEFAULT NULL COMMENT 'Fecha de referencia de pago. OJO CAMBIAR  GCCS_FechaRef a DATE',
  `GCCS_Control` varchar(45) DEFAULT NULL,
  `GCCS_Status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 unchecked 1 checked',
  `GCCS_Currency` varchar(50) DEFAULT 'VES',
  PRIMARY KEY (`GCCS_Id`) USING BTREE,
  KEY `fk_gccs_gcca1_idx` (`GCCA_Id`,`GCCD_Id`) USING BTREE,
  KEY `fk_gccs_gccd1_idx` (`GCCD_Id`) USING BTREE,
  KEY `fk_gccs_gcut1_idx` (`GCUT_IdOrigen`) USING BTREE,
  KEY `fk_gccs_gcut2_idx` (`GCUT_IdDestino`) USING BTREE,
  KEY `fk_gccs_gcua1_idx` (`GCUA_Id`) USING BTREE,
  KEY `fk_gccs_gcui1_idx` (`GCUI_Id`) USING BTREE,
  KEY `GCCS_Control` (`GCCS_Control`) USING BTREE,
  KEY `GCCS_Status` (`GCCS_Status`) USING BTREE,
  KEY `GCCS_Currency` (`GCCS_Currency`) USING BTREE,
  KEY `GCCS_Fecha` (`GCCS_Fecha`) USING BTREE,
  KEY `GCCS_Usuario` (`GCCS_Usuario`) USING BTREE,
  CONSTRAINT `FK_gccs_gcca` FOREIGN KEY (`GCCA_Id`, `GCCD_Id`) REFERENCES `gcca` (`GCCA_Id`, `GCCD_Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_gccs_gccd` FOREIGN KEY (`GCCD_Id`) REFERENCES `gccd` (`GCCD_Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=350353 DEFAULT CHARSET=latin1 COMMENT='ajuste, envio o pago, \ncada registro es independiente, cada registro modifica el saldo actual del GCCA';

-- Dumping data for table integracion.gccs: ~2 rows (approximately)
/*!40000 ALTER TABLE `gccs` DISABLE KEYS */;
INSERT INTO `gccs` (`GCCS_Id`, `GCCS_Fecha`, `GCCS_Monto`, `GCCS_Descripcion`, `GCCD_Id`, `GCCA_Id`, `GCUA_Id`, `GCUI_Id`, `GCCS_Usuario`, `GCUT_IdOrigen`, `GCUT_IdDestino`, `GCCS_FechaRef`, `GCCS_Control`, `GCCS_Status`, `GCCS_Currency`) VALUES
	(1, '0000-00-00 00:00:00', 1000.00, NULL, 1, 240, 0, 0, '1', NULL, NULL, NULL, NULL, 1, 'VES'),
	(2, '0000-00-00 00:00:00', 1000.00, NULL, 1, 240, 0, 0, '1', NULL, NULL, NULL, NULL, 1, 'VES');
/*!40000 ALTER TABLE `gccs` ENABLE KEYS */;

-- Dumping structure for table integracion.gccu
CREATE TABLE IF NOT EXISTS `gccu` (
  `GCCU_Id` int(11) NOT NULL AUTO_INCREMENT,
  `GCCU_Nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`GCCU_Id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COMMENT='esquemaCliente\r\nBanca sub-Banca sub-Grupo\r\nSi el padre puede mover las operaciones...\r\nObsoleto';

-- Dumping data for table integracion.gccu: ~0 rows (approximately)
/*!40000 ALTER TABLE `gccu` DISABLE KEYS */;
/*!40000 ALTER TABLE `gccu` ENABLE KEYS */;

-- Dumping structure for table integracion.pcdi
CREATE TABLE IF NOT EXISTS `pcdi` (
  `PCDI_Id` int(11) NOT NULL AUTO_INCREMENT,
  `PCDI_Cod` varchar(50) DEFAULT NULL,
  `PCDI_Nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`PCDI_Id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Moneda';

-- Dumping data for table integracion.pcdi: ~1 rows (approximately)
/*!40000 ALTER TABLE `pcdi` DISABLE KEYS */;
INSERT INTO `pcdi` (`PCDI_Id`, `PCDI_Cod`, `PCDI_Nombre`) VALUES
	(1, 'VES', 'Bolivares Soberanos');
/*!40000 ALTER TABLE `pcdi` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
