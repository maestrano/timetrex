update user_date_total set branch_id = 0 where branch_id IS NULL;
update user_date_total set department_id = 0 where department_id IS NULL;
update user_date_total set job_id = 0 where job_id IS NULL;
update user_date_total set job_item_id = 0 where job_item_id IS NULL;
update user_date_total set quantity = 0 where quantity IS NULL;
update user_date_total set bad_quantity = 0 where bad_quantity IS NULL;

update punch_control set branch_id = 0 where branch_id IS NULL;
update punch_control set department_id = 0 where department_id IS NULL;
update punch_control set job_id = 0 where job_id IS NULL;
update punch_control set job_item_id = 0 where job_item_id IS NULL;
update punch_control set quantity = 0 where quantity IS NULL;
update punch_control set bad_quantity = 0 where bad_quantity IS NULL;

