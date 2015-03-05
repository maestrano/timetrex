CREATE INDEX permission_control_company_id ON permission_control(company_id);
CREATE INDEX station_company_id_station_id ON station(company_id,station_id);
CREATE INDEX request_user_date_id ON request(user_date_id);
CREATE INDEX message_created_by ON message(created_by);
CREATE INDEX message_created_by_parent_id ON message(created_by,parent_id);
CREATE INDEX pay_period_time_sheet_verify_user_id_pay_period_id ON pay_period_time_sheet_verify(user_id,pay_period_id);
