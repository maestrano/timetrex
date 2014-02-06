update user_date_total set punch_control_id = 0 where punch_control_id IS NULL;
update user_date_total set over_time_policy_id = 0 where over_time_policy_id IS NULL;
update user_date_total set absence_policy_id = 0 where absence_policy_id IS NULL;
update user_date_total set premium_policy_id = 0 where premium_policy_id IS NULL;

