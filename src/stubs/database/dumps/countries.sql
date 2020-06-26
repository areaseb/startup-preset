-- MySQL dump 10.13  Distrib 5.7.30, for Linux (x86_64)
--
-- Host: localhost    Database: crm
-- ------------------------------------------------------
-- Server version	5.7.30-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL DEFAULT '',
  `iso2` char(2) NOT NULL DEFAULT '',
  `iso3` char(3) NOT NULL,
  `phone_code` int(7) NOT NULL,
  `is_eu` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso2` (`iso2`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'Andorra','AD','AND',376,0),(2,'United Arab Emirates','AE','ARE',971,0),(3,'Afghanistan','AF','AFG',93,0),(4,'Antigua and Barbuda','AG','ATG',1268,0),(5,'Anguilla','AI','AIA',1264,0),(6,'Albania','AL','ALB',355,0),(7,'Armenia','AM','ARM',374,0),(8,'Angola','AO','AGO',244,0),(9,'Antarctica','AQ','ATA',672,0),(10,'Argentina','AR','ARG',54,0),(11,'American Samoa','AS','ASM',1684,0),(12,'Austria','AT','AUT',43,1),(13,'Australia','AU','AUS',61,0),(14,'Aruba','AW','ABW',297,0),(15,'Åland Islands','AX','ALA',0,0),(16,'Azerbaijan','AZ','AZE',994,0),(17,'Bosnia and Herzegovina','BA','BIH',387,0),(18,'Barbados','BB','BRB',1246,0),(19,'Bangladesh','BD','BGD',880,0),(20,'Belgium','BE','BEL',32,1),(21,'Burkina Faso','BF','BFA',226,0),(22,'Bulgaria','BG','BGR',359,1),(23,'Bahrain','BH','BHR',973,0),(24,'Burundi','BI','BDI',257,0),(25,'Benin','BJ','BEN',229,0),(26,'Saint Barthélemy','BL','BLM',0,0),(27,'Bermuda','BM','BMU',1441,0),(28,'Brunei Darussalam','BN','BRN',673,0),(29,'Bolivia','BO','BOL',591,0),(30,'Bonaire, Sint Eustatius and Saba','BQ','BES',0,0),(31,'Brazil','BR','BRA',55,0),(32,'Bahamas','BS','BHS',1242,0),(33,'Bhutan','BT','BTN',975,0),(34,'Bouvet Island','BV','BVT',44,0),(35,'Botswana','BW','BWA',267,0),(36,'Belarus','BY','BLR',375,0),(37,'Belize','BZ','BLZ',501,0),(38,'Canada','CA','CAN',1,0),(39,'Cocos (Keeling) Islands','CC','CCK',61,0),(40,'Congo (Democratic Republic of the)','CD','COD',243,0),(41,'Central African Republic','CF','CAF',236,0),(42,'Congo','CG','COG',242,0),(43,'Switzerland','CH','CHE',41,0),(44,'Ivory Coast','CI','CIV',225,0),(45,'Cook Islands','CK','COK',682,0),(46,'Chile','CL','CHL',56,0),(47,'Cameroon','CM','CMR',237,0),(48,'China','CN','CHN',86,0),(49,'Colombia','CO','COL',57,0),(50,'Costa Rica','CR','CRI',506,0),(51,'Cuba','CU','CUB',53,0),(52,'Cape Verde','CV','CPV',238,0),(53,'Curaçao','CW','CUW',0,0),(54,'Christmas Island','CX','CXR',61,0),(55,'Cyprus','CY','CYP',357,1),(56,'Czech Republic','CZ','CZE',420,1),(57,'Germany','DE','DEU',49,1),(58,'Djibouti','DJ','DJI',253,0),(59,'Denmark','DK','DNK',45,1),(60,'Dominica','DM','DMA',1767,0),(61,'Dominican Republic','DO','DOM',1809,0),(62,'Algeria','DZ','DZA',213,0),(63,'Ecuador','EC','ECU',593,0),(64,'Estonia','EE','EST',372,1),(65,'Egypt','EG','EGY',20,0),(66,'Western Sahara','EH','ESH',0,0),(67,'Eritrea','ER','ERI',291,0),(68,'Spain','ES','ESP',34,1),(69,'Ethiopia','ET','ETH',251,0),(70,'Finland','FI','FIN',358,1),(71,'Fiji','FJ','FJI',679,0),(72,'Falkland Islands (Malvinas)','FK','FLK',500,0),(73,'Micronesia (Federated States of)','FM','FSM',691,0),(74,'Faroe Islands','FO','FRO',298,0),(75,'France','FR','FRA',33,1),(76,'Gabon','GA','GAB',241,0),(77,'United Kingdom','GB','GBR',44,1),(78,'Grenada','GD','GRD',1473,0),(79,'Georgia','GE','GEO',995,0),(80,'French Guiana','GF','GUF',594,0),(81,'Guernsey','GG','GGY',0,0),(82,'Ghana','GH','GHA',233,0),(83,'Gibraltar','GI','GIB',350,0),(84,'Greenland','GL','GRL',299,0),(85,'Gambia','GM','GMB',220,0),(86,'Guinea','GN','GIN',224,0),(87,'Guadeloupe','GP','GLP',590,0),(88,'Equatorial Guinea','GQ','GNQ',240,0),(89,'Greece','GR','GRC',30,1),(90,'South Georgia and the South Sandwich Islands','GS','SGS',44,0),(91,'Guatemala','GT','GTM',502,0),(92,'Guam','GU','GUM',1671,0),(93,'Guinea-Bissau','GW','GNB',245,0),(94,'Guyana','GY','GUY',592,0),(95,'Hong Kong','HK','HKG',852,0),(96,'Heard Island and McDonald Islands','HM','HMD',44,0),(97,'Honduras','HN','HND',504,0),(98,'Croatia (Hrvatska)','HR','HRV',385,1),(99,'Haiti','HT','HTI',509,0),(100,'Hungary','HU','HUN',36,1),(101,'Indonesia','ID','IDN',62,0),(102,'Ireland','IE','IRL',353,1),(103,'Israel','IL','ISR',972,0),(104,'Isle of Man','IM','IMN',0,0),(105,'India','IN','IND',91,0),(106,'British Indian Ocean Territory','IO','IOT',0,0),(107,'Iraq','IQ','IRQ',964,0),(108,'Iran (Islamic Republic of)','IR','IRN',98,0),(109,'Iceland','IS','ISL',354,0),(110,'Italy','IT','ITA',39,1),(111,'Jersey','JE','JEY',0,1),(112,'Jamaica','JM','JAM',1876,0),(113,'Jordan','JO','JOR',962,0),(114,'Japan','JP','JPN',81,0),(115,'Kenya','KE','KEN',254,0),(116,'Kyrgyzstan','KG','KGZ',996,0),(117,'Cambodia','KH','KHM',855,0),(118,'Kiribati','KI','KIR',686,0),(119,'Comoros','KM','COM',269,0),(120,'Saint Kitts and Nevis','KN','KNA',1869,0),(121,'Korea (Democratic People\'s Republic of)','KP','PRK',850,0),(122,'Korea (Republic of)','KR','KOR',82,0),(123,'Kuwait','KW','KWT',965,0),(124,'Cayman Islands','KY','CYM',1345,0),(125,'Kazakhstan','KZ','KAZ',7,0),(126,'Lao People\'s Democratic Republic','LA','LAO',856,0),(127,'Lebanon','LB','LBN',961,0),(128,'Saint Lucia','LC','LCA',1758,0),(129,'Liechtenstein','LI','LIE',423,0),(130,'Sri Lanka','LK','LKA',94,0),(131,'Liberia','LR','LBR',231,0),(132,'Lesotho','LS','LSO',266,0),(133,'Lithuania','LT','LTU',370,1),(134,'Luxembourg','LU','LUX',352,1),(135,'Latvia','LV','LVA',371,1),(136,'Libya','LY','LBY',218,0),(137,'Morocco','MA','MAR',212,0),(138,'Monaco','MC','MCO',377,0),(139,'Moldova (Republic of)','MD','MDA',373,0),(140,'Montenegro','ME','MNE',382,0),(141,'Saint Martin (French part)','MF','MAF',0,0),(142,'Madagascar','MG','MDG',261,0),(143,'Marshall Islands','MH','MHL',692,0),(144,'Macedonia','MK','MKD',389,0),(145,'Mali','ML','MLI',223,0),(146,'Myanmar','MM','MMR',95,0),(147,'Mongolia','MN','MNG',976,0),(148,'Macau','MO','MAC',853,0),(149,'Northern Mariana Islands','MP','MNP',1670,0),(150,'Martinique','MQ','MTQ',596,0),(151,'Mauritania','MR','MRT',222,0),(152,'Montserrat','MS','MSR',1664,0),(153,'Malta','MT','MLT',356,1),(154,'Mauritius','MU','MUS',230,0),(155,'Maldives','MV','MDV',960,0),(156,'Malawi','MW','MWI',265,0),(157,'Mexico','MX','MEX',52,0),(158,'Malaysia','MY','MYS',60,0),(159,'Mozambique','MZ','MOZ',258,0),(160,'Namibia','NA','NAM',264,0),(161,'New Caledonia','NC','NCL',687,0),(162,'Niger','NE','NER',227,0),(163,'Norfolk Island','NF','NFK',672,0),(164,'Nigeria','NG','NGA',234,0),(165,'Nicaragua','NI','NIC',505,0),(166,'Netherlands','NL','NLD',31,1),(167,'Norway','NO','NOR',47,0),(168,'Nepal','NP','NPL',977,0),(169,'Nauru','NR','NRU',674,0),(170,'Niue','NU','NIU',683,0),(171,'New Zealand','NZ','NZL',64,0),(172,'Oman','OM','OMN',968,0),(173,'Panama','PA','PAN',507,0),(174,'Peru','PE','PER',51,0),(175,'French Polynesia','PF','PYF',689,0),(176,'Papua New Guinea','PG','PNG',675,0),(177,'Philippines','PH','PHL',63,0),(178,'Pakistan','PK','PAK',92,0),(179,'Poland','PL','POL',48,1),(180,'Saint Pierre and Miquelon','PM','SPM',508,0),(181,'Pitcairn','PN','PCN',870,0),(182,'Puerto Rico','PR','PRI',1,0),(183,'Palestine, State of','PS','PSE',0,0),(184,'Portugal','PT','PRT',351,1),(185,'Palau','PW','PLW',680,0),(186,'Paraguay','PY','PRY',595,0),(187,'Qatar','QA','QAT',974,0),(188,'Reunion','RE','REU',262,0),(189,'Romania','RO','ROU',40,1),(190,'Serbia','RS','SRB',381,0),(191,'Russian Federation','RU','RUS',7,0),(192,'Rwanda','RW','RWA',250,0),(193,'Saudi Arabia','SA','SAU',966,0),(194,'Solomon Islands','SB','SLB',677,0),(195,'Seychelles','SC','SYC',248,0),(196,'Sudan','SD','SDN',249,0),(197,'Sweden','SE','SWE',46,1),(198,'Singapore','SG','SGP',65,0),(199,'Saint Helena, Ascension and Tristan da Cunha','SH','SHN',290,0),(200,'Slovenia','SI','SVN',386,1),(201,'Svalbard and Jan Mayen','SJ','SJM',0,0),(202,'Slovakia','SK','SVK',421,1),(203,'Sierra Leone','SL','SLE',232,0),(204,'San Marino','SM','SMR',378,0),(205,'Senegal','SN','SEN',221,0),(206,'Somalia','SO','SOM',252,0),(207,'Suriname','SR','SUR',597,0),(208,'South Sudan','SS','SSD',0,0),(209,'Sao Tome and Principe','ST','STP',239,0),(210,'El Salvador','SV','SLV',503,0),(211,'Sint Maarten (Dutch part)','SX','SXM',0,0),(212,'Syrian Arab Republic','SY','SYR',963,0),(213,'Swaziland','SZ','SWZ',268,0),(214,'Turks and Caicos Islands','TC','TCA',1649,0),(215,'Chad','TD','TCD',235,0),(216,'French Southern Territories','TF','ATF',44,0),(217,'Togo','TG','TGO',228,0),(218,'Thailand','TH','THA',66,0),(219,'Tajikistan','TJ','TJK',992,0),(220,'Tokelau','TK','TKL',690,0),(221,'Timor-Leste','TL','TLS',670,0),(222,'Turkmenistan','TM','TKM',993,0),(223,'Tunisia','TN','TUN',216,0),(224,'Tonga','TO','TON',676,0),(225,'Turkey','TR','TUR',90,0),(226,'Trinidad and Tobago','TT','TTO',1868,0),(227,'Tuvalu','TV','TUV',688,0),(228,'Taiwan','TW','TWN',886,0),(229,'Tanzania, United Republic of','TZ','TZA',255,0),(230,'Ukraine','UA','UKR',380,0),(231,'Uganda','UG','UGA',256,0),(232,'United States Minor Outlying Islands','UM','UMI',44,0),(233,'United States of America','US','USA',1,0),(234,'Uruguay','UY','URY',598,0),(235,'Uzbekistan','UZ','UZB',998,0),(236,'Vatican City State','VA','VAT',39,0),(237,'Saint Vincent and the Grenadines','VC','VCT',1784,0),(238,'Venezuela','VE','VEN',58,0),(239,'Virgin Islands (British)','VG','VGB',1284,0),(240,'Virgin Islands (U.S.)','VI','VIR',1340,0),(241,'Viet Nam','VN','VNM',84,0),(242,'Vanuatu','VU','VUT',678,0),(243,'Wallis and Futuna','WF','WLF',681,0),(244,'Samoa','WS','WSM',685,0),(245,'Yemen','YE','YEM',967,0),(246,'Mayotte','YT','MYT',262,0),(247,'South Africa','ZA','ZAF',27,0),(248,'Zambia','ZM','ZMB',260,0),(249,'Zimbabwe','ZW','ZWE',263,0);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-06-11 17:37:25
