alter table premium_policy add column minimum_time_between_shift integer DEFAULT NULL;
alter table premium_policy add column minimum_first_shift_time integer DEFAULT NULL;
alter table premium_policy add column minimum_shift_time integer DEFAULT NULL;

