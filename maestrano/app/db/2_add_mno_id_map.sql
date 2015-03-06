CREATE TABLE IF NOT EXISTS `mno_id_map` (
  `mno_entity_guid` varchar(255) NOT NULL,
  `mno_entity_name` varchar(255) NOT NULL,
  `app_entity_id` varchar(255) NOT NULL,
  `app_entity_name` varchar(255) NOT NULL,
  `db_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_flag` int(1) NOT NULL DEFAULT '0'
);