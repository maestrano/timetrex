CREATE UNIQUE INDEX "pay_code_id" ON "pay_code" USING btree (id);
CREATE UNIQUE INDEX "pay_formula_policy_id" ON "pay_formula_policy" USING btree (id);
CREATE UNIQUE INDEX "regular_time_policy_id" ON "regular_time_policy" USING btree (id);
CREATE UNIQUE INDEX "contributing_pay_code_policy_id" ON "contributing_pay_code_policy" USING btree (id);
CREATE UNIQUE INDEX "contributing_shift_policy_id" ON "contributing_shift_policy" USING btree (id);
CREATE UNIQUE INDEX "accrual_policy_account_id" ON "accrual_policy_account" USING btree (id);

