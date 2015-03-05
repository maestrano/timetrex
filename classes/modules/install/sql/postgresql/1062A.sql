ALTER TABLE round_interval_policy ADD COLUMN condition_type_id smallint DEFAULT 0;
ALTER TABLE round_interval_policy ADD COLUMN condition_static_time time without time zone DEFAULT '08:00 AM';
ALTER TABLE round_interval_policy ADD COLUMN condition_static_total_time integer DEFAULT 3600;
ALTER TABLE round_interval_policy ADD COLUMN condition_start_window integer DEFAULT 900;
ALTER TABLE round_interval_policy ADD COLUMN condition_stop_window integer DEFAULT 900; 
