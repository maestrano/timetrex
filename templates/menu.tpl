{literal}
fixMozillaZIndex=true; //Fixes Z-Index problem  with Mozilla browsers but causes odd scrolling problem, toggle to see if it helps
horizontalMenuDelay = true;
_menuCloseDelay=350;
_menuOpenDelay=150;
_subOffsetTop=2;
_subOffsetLeft=0;

with(menuStyle=new mm_style()){
BorderColor="#FFFFFF";
BorderStyle="solid";
BorderWidth=0;
fontfamily="Verdana, Sans-Serif";
fontsize="75%";
fontstyle="normal";
fontweight="bold";
offbgcolor="#336699";
offcolor="#FFFFFF";
onbgcolor="#0099CC";
padding=4;
separatorcolor="#000000";
separatorsize=1;
subimage="{/literal}{$IMAGES_URL}arrow.gif{literal}";
subimagepadding=2;
}

with(milonic=new menuname("Main Menu")){
style=menuStyle;
top=23;
left=0;
alwaysvisible=1;
orientation="horizontal";
{/literal}
aI("url={$BASE_URL}index.php;image={$IMAGES_URL}home_icon.gif;");
{if $permission->Check('punch','enabled') AND $permission->Check('punch','punch_in_out') }
	aI("text={t}In / Out{/t};url=javascript:timePunch();");
{/if}
{if $permission->Check('punch','enabled') }
	aI("showmenu=timesheet;text={t}TimeSheet{/t};{if $display_exception_flag == 'red'}image={$IMAGES_URL}{$display_exception_flag}_flag.gif{/if}");
{/if}
{if $permission->Check('schedule','enabled')
	OR $permission->Check('recurring_schedule','enabled')
	OR $permission->Check('recurring_schedule_template','enabled')}
	aI("showmenu=schedule;text={t}Schedule{/t};");
{/if}
{if $permission->Check('job','enabled')
		AND ( $permission->Check('job','view')
			OR $permission->Check('job','view_own')
			) }
	aI("showmenu=job;text={t}JobTracking{/t};");
{/if}
{if $permission->Check('client','enabled')
		AND ( $permission->Check('client','view')
			OR $permission->Check('client','view_own')
			) }
	aI("showmenu=invoice;text={t}Invoice{/t};");
{/if}
{if $permission->Check('document','enabled')
		AND ( $permission->Check('document','view')
			OR $permission->Check('document','view_own')
			OR $permission->Check('document','view_private')
			) }
	aI("showmenu=document;text={t}Docs{/t};");
{/if}
{if ( $permission->Check('station','enabled') AND $permission->Check('station','view') )
	OR ( $permission->Check('user','enabled') AND ( $permission->Check('user','view') OR $permission->Check('user','view_child') ) )
	OR ( $permission->Check('department','enabled') AND $permission->Check('department','view') )
	OR ( $permission->Check('branch','enabled') AND $permission->Check('branch','view') )
	OR ( $permission->Check('company','enabled') AND $permission->Check('company','view') )
	OR ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
	OR ( $permission->Check('hierarchy','enabled') AND $permission->Check('hierarchy','view') )
	OR ( $permission->Check('authorization','enabled') AND $permission->Check('authorization','view') )}
	aI("showmenu=admin;text={t}Admin{/t};");
{/if}
{if $permission->Check('report','enabled')}
	aI("showmenu=report;text={t}Reports{/t};");
{/if}
{if $permission->Check('user','edit_own')
	OR $permission->Check('user_preference','enabled')
	OR $permission->Check('user','edit_own_bank')
	OR $permission->Check('message','enabled')}
	aI("showmenu=myaccount;text={t}MyAccount{/t};{if $unread_messages > 0}image={$IMAGES_URL}mail.png{/if}");
{/if}
{if $permission->Check('user','edit') OR $permission->Check('user','edit_child')
	OR $permission->Check('recurring_schedule','enabled')
	OR $permission->Check('recurring_schedule_template','enabled')}
	aI("showmenu=help;text={t}Help{/t};{if $system_settings.new_version == 1}image={$IMAGES_URL}red_flag.gif{/if}");
{/if}
aI("text={t}Logout{/t};url={$BASE_URL}Logout.php;");
aI("image={$IMAGES_URL}tab_menu.gif");
{literal}
}
{/literal}

{if $permission->Check('punch','enabled') }
{literal}
with(milonic=new menuname("timesheet")){
style=menuStyle;
{/literal}
{if $permission->Check('punch','view') OR $permission->Check('punch','view_own')}
	aI("text={t}MyTimeSheet{/t};url={$BASE_URL}timesheet/ViewUserTimeSheet.php;");
{/if}
{if $permission->Check('punch','edit') OR $permission->Check('punch','edit_child')}
	aI("text={t}Punches{/t};url={$BASE_URL}punch/PunchList.php;");
	aI("text={t}Mass Punch{/t};url={$BASE_URL}punch/AddMassPunch.php;");
{/if}
{if $permission->Check('request','view') OR $permission->Check('request','view_own')}
	aI("text={t}Requests{/t};url={$BASE_URL}request/UserRequestList.php;");
{/if}
{if $permission->Check('punch','view') OR $permission->Check('punch','view_own')}
	aI("text={t}Exceptions{/t};url={$BASE_URL}punch/UserExceptionList.php;{if $display_exception_flag != FALSE}image={$IMAGES_URL}{$display_exception_flag}_flag.gif{/if}");
{/if}
{if $permission->Check('accrual','view') OR $permission->Check('accrual','view_own')}
	aI("text={t}Accruals{/t};url={$BASE_URL}accrual/UserAccrualBalanceList.php;");
{/if}
{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own')}
	aI("text={t}Pay Stubs{/t};url={$BASE_URL}pay_stub/PayStubList.php;");
{/if}
{literal}
}
{/literal}
{/if}

{if $permission->Check('schedule','enabled')
	OR $permission->Check('recurring_schedule','enabled')
	OR $permission->Check('recurring_schedule_template','enabled')}
{literal}
with(milonic=new menuname("schedule")){
style=menuStyle;
{/literal}
{if $permission->Check('schedule','view') OR $permission->Check('schedule','view_own')}
	aI("text={t}MySchedule{/t};url={$BASE_URL}schedule/ViewSchedule.php;");
{/if}
{if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_child')}
	aI("text={t}Scheduled Shifts{/t};url={$BASE_URL}schedule/ScheduleList.php;");
	aI("text={t}Mass Schedule{/t};url={$BASE_URL}schedule/AddMassSchedule.php;");
{/if}
{if $permission->Check('recurring_schedule','enabled')}
	aI("text={t}Recurring Schedule{/t};url={$BASE_URL}schedule/RecurringScheduleControlList.php;");
{/if}
{if $permission->Check('recurring_schedule_template','enabled')}
	aI("text={t}Recurring Schedule Template{/t};url={$BASE_URL}schedule/RecurringScheduleTemplateControlList.php;");
{/if}
{literal}
}
{/literal}
{/if}

{if $permission->Check('job','enabled')
		AND ( $permission->Check('job','view')
			OR $permission->Check('job','view_own')
			) }
{literal}
with(milonic=new menuname("job")){
style=menuStyle;
{/literal}
{if $permission->Check('job','view') OR $permission->Check('job','view_own')}
	aI("text={t}Jobs{/t};url={$BASE_URL}job/JobList.php;");
{/if}
{if $permission->Check('job_item','view') OR $permission->Check('job_item','view_own')}
	aI("text={t}Tasks{/t};url={$BASE_URL}job_item/JobItemList.php;");
{/if}
{if $permission->Check('job','view') OR $permission->Check('job','view_own')}
	aI("text={t}Job Groups{/t};url={$BASE_URL}job/JobGroupList.php;");
{/if}
{if $permission->Check('job_item','view') OR $permission->Check('job_item','view_own')}
	aI("text={t}Task Groups{/t};url={$BASE_URL}job_item/JobItemGroupList.php;");
{/if}
{literal}
}
{/literal}
{/if}


{if $permission->Check('client','enabled')
		AND ( $permission->Check('client','view')
			OR $permission->Check('client','view_own') ) }
{literal}
with(milonic=new menuname("invoice")){
style=menuStyle;
{/literal}
{if $permission->Check('client','view') OR $permission->Check('client','view_own')}
	aI("text={t}Clients{/t};url={$BASE_URL}client/ClientList.php;");
{/if}
{if $permission->Check('invoice','view') OR $permission->Check('invoice','view_own')}
	aI("text={t}Invoices{/t};url={$BASE_URL}invoice/InvoiceList.php;");
{/if}
{if $permission->Check('transaction','view') OR $permission->Check('transaction','view_own')}
	aI("text={t}Transactions{/t};url={$BASE_URL}invoice/TransactionList.php;");
{/if}
{if $permission->Check('product','view') OR $permission->Check('product','view_own')}
	aI("text={t}Products{/t};url={$BASE_URL}product/ProductList.php;");
{/if}
{if $permission->Check('tax_policy','view') OR $permission->Check('tax_policy','view_own')}
	aI("showmenu=invoice_policies;text={t}Policies{/t};");
	aI("text={t}Districts{/t};url={$BASE_URL}invoice/DistrictList.php;");
{/if}
{if $permission->Check('client','view') OR $permission->Check('client','view_own')}
	aI("text={t}Client Groups{/t};url={$BASE_URL}client/ClientGroupList.php;");
{/if}
{if $permission->Check('product','view') OR $permission->Check('product','view_own')}
	aI("text={t}Product Groups{/t};url={$BASE_URL}product/ProductGroupList.php;");
{/if}
{if $permission->Check('payment_gateway','edit') OR $permission->Check('payment_gateway','edit_own')}
aI("text={t}Payment Gateway{/t};url={$BASE_URL}invoice/PaymentGatewayList.php;");
{/if}
{if $permission->Check('invoice_config','edit') OR $permission->Check('invoice_config','edit_own')}
	aI("text={t}Settings{/t};url={$BASE_URL}invoice/EditInvoiceConfig.php;");
{/if}
{literal}
}
{/literal}

	{if $permission->Check('tax_policy','view') OR $permission->Check('tax_policy','view_own') }
	{literal}
	with(milonic=new menuname("invoice_policies")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('tax_policy','view') OR $permission->Check('tax_policy','view_own')}
		aI("text={t}Tax Policies{/t};url={$BASE_URL}invoice_policy/TaxPolicyList.php;");
	{/if}
	{if $permission->Check('shipping_policy','view') OR $permission->Check('shipping_policy','view_own')}
		aI("text={t}Shipping Policies{/t};url={$BASE_URL}invoice_policy/ShippingPolicyList.php;");
	{/if}
	{if $permission->Check('area_policy','view') OR $permission->Check('area_policy','view_own')}
		aI("text={t}Area Policies{/t};url={$BASE_URL}invoice_policy/AreaPolicyList.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}
{/if}

{if $permission->Check('document','enabled')
		AND ( $permission->Check('document','view')
			OR $permission->Check('document','view_own')
			OR $permission->Check('document','view_private')
			) }
{literal}
with(milonic=new menuname("document")){
style=menuStyle;
{/literal}
{if $permission->Check('document','view') OR $permission->Check('document','view_own') OR $permission->Check('document','view_private')}
	aI("text={t}Documents{/t};url={$BASE_URL}document/DocumentList.php;");
{/if}
{if $permission->Check('document','edit') }
	aI("text={t}Document Groups{/t};url={$BASE_URL}document/DocumentGroupList.php;");
{/if}
{literal}
}
{/literal}
{/if}

{if ( $permission->Check('station','enabled') AND $permission->Check('station','view') )
	OR ( $permission->Check('user','enabled') AND ( $permission->Check('user','view') OR $permission->Check('user','view_child') ) )
	OR ( $permission->Check('department','enabled') AND $permission->Check('department','view') )
	OR ( $permission->Check('branch','enabled') AND $permission->Check('branch','view') )
	OR ( $permission->Check('company','enabled') AND $permission->Check('company','view') )
	OR ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
	OR ( $permission->Check('hierarchy','enabled') AND $permission->Check('hierarchy','view') )
	OR ( $permission->Check('authorization','enabled') AND $permission->Check('authorization','view') )}
{literal}
with(milonic=new menuname("admin")){
style=menuStyle;
{/literal}
{if $permission->Check('user','enabled') AND ( $permission->Check('user','view') OR $permission->Check('user','view_child') )}
	aI("text={t}Employee Administration{/t};url={$BASE_URL}users/UserList.php;");
{/if}
{if ( $permission->Check('company','enabled') AND $permission->Check('company','edit_own') )
	OR ( $permission->Check('user','enabled') AND $permission->Check('user','edit') AND $permission->Check('user','add') )
	OR ( $permission->Check('branch','enabled') AND $permission->Check('branch','view') )
	OR ( $permission->Check('currency','enabled') AND $permission->Check('currency','view') )
	OR ( $permission->Check('station','enabled') AND $permission->Check('station','view') )
	OR ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
	OR ( $permission->Check('permission','enabled') AND $permission->Check('permission','edit') )
	OR ( $permission->Check('hierarchy','enabled') AND $permission->Check('hierarchy','view') )
	OR ( $permission->Check('company','enabled') AND $permission->Check('company','edit_own_bank') )}
	aI("showmenu=admin_company;text={t}Company{/t};");
{/if}

{if ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
	OR ( $permission->Check('policy_group','enabled') AND $permission->Check('policy_group','view') )
	OR ( $permission->Check('schedule_policy','enabled') AND $permission->Check('schedule_policy','view') )
	OR ( $permission->Check('meal_policy','enabled') AND $permission->Check('meal_policy','view') )
	OR ( $permission->Check('break_policy','enabled') AND $permission->Check('break_policy','view') )
	OR ( $permission->Check('over_time_policy','enabled') AND $permission->Check('over_time_policy','view') )
	OR ( $permission->Check('premium_policy','enabled') AND $permission->Check('premium_policy','view') )
	OR ( $permission->Check('accrual_policy','enabled') AND $permission->Check('accrual_policy','view') )
	OR ( $permission->Check('absence_policy','enabled') AND $permission->Check('absence_policy','view') )
	OR ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
	OR ( $permission->Check('exception_policy','enabled') AND $permission->Check('exception_policy','view') )
	OR ( $permission->Check('holiday_policy','enabled') AND $permission->Check('holiday_policy','view') )
	}
	aI("showmenu=admin_policy;text={t}Policies{/t};");
{/if}

{if ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
	OR ( $permission->Check('pay_stub_amendment','enabled') AND ( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_child') OR $permission->Check('pay_stub_amendment','view_own') ) )
	OR ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
	OR ( $permission->Check('pay_stub_amendment','enabled') AND $permission->Check('pay_stub_amendment','edit') )}
	aI("showmenu=admin_payroll;text={t}Payroll{/t};");
{/if}
{if $permission->Check('authorization','enabled') AND ( $permission->Check('authorization','view') )}
	aI("text={t}Authorization{/t};url={$BASE_URL}authorization/AuthorizationList.php;");
{/if}
{if $current_company->getProductEdition() > 10 AND $permission->Check('company','enabled') AND $permission->Check('company','view')}
	aI("text={t}Company Administration{/t};url={$BASE_URL}company/CompanyList.php;");
{/if}
{if $permission->Check('help','enabled') AND $permission->Check('help','edit')}
	aI("text={t}Help Administration{/t};url={$BASE_URL}help/HelpList.php;");
{/if}
{if $permission->Check('help','enabled') AND $permission->Check('help','edit')}
	aI("text={t}Help Group Administration{/t};url={$BASE_URL}help/HelpGroupControlList.php;");
{/if}
{literal}
}
{/literal}

	{if ( $permission->Check('company','enabled') AND $permission->Check('company','edit_own') )
		OR ( $permission->Check('user','enabled') AND $permission->Check('user','edit') AND $permission->Check('user','add') )
		OR ( $permission->Check('branch','enabled') AND $permission->Check('branch','view') )
		OR ( $permission->Check('currency','enabled') AND $permission->Check('currency','view') )
		OR ( $permission->Check('station','enabled') AND $permission->Check('station','view') )
		OR ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
		OR ( $permission->Check('permission','enabled') AND $permission->Check('permission','edit') )
		OR ( $permission->Check('hierarchy','enabled') AND $permission->Check('hierarchy','view') )
		OR ( $permission->Check('company','enabled') AND $permission->Check('company','edit_own_bank') )}
	{literal}
	with(milonic=new menuname("admin_company")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('company','enabled') AND $permission->Check('company','edit_own')}
		aI("text={t}Company Information{/t};url={$BASE_URL}company/EditCompany.php?id={$current_company->getId()};");
	{/if}
	{if $permission->Check('user','enabled') AND $permission->Check('user','edit') AND $permission->Check('user','add')}
		aI("text={t}Employee Titles{/t};url={$BASE_URL}users/UserTitleList.php;");
		aI("text={t}Employee Groups{/t};url={$BASE_URL}users/UserGroupList.php;");
	{/if}
	{if $permission->Check('currency','enabled') AND $permission->Check('currency','view')}
		aI("text={t}Currencies{/t};url={$BASE_URL}currency/CurrencyList.php;");
	{/if}
	{if $permission->Check('branch','enabled') AND $permission->Check('branch','view')}
		aI("text={t}Branches{/t};url={$BASE_URL}branch/BranchList.php;");
	{/if}
	{if $permission->Check('department','enabled') AND $permission->Check('department','view')}
		aI("text={t}Departments{/t};url={$BASE_URL}department/DepartmentList.php;");
	{/if}
	{if $permission->Check('wage','enabled') AND $permission->Check('wage','view')}
		aI("text={t}Secondary Wage Groups{/t};url={$BASE_URL}company/WageGroupList.php;");
	{/if}
	{if $permission->Check('station','enabled') AND $permission->Check('station','view')}
		aI("text={t}Stations{/t};url={$BASE_URL}station/StationList.php;");
	{/if}
	{if $permission->Check('permission','enabled') AND $permission->Check('permission','edit')}
		aI("text={t}Permission Groups{/t};url={$BASE_URL}permission/PermissionControlList.php;");
	{/if}
	{if $permission->Check('user','enabled') AND $permission->Check('user','edit') AND $permission->Check('user','add')}
		aI("text={t}New Hire Defaults{/t};url={$BASE_URL}users/EditUserDefault.php;");
	{/if}
	{if $permission->Check('hierarchy','enabled') AND $permission->Check('hierarchy','view')}
		aI("text={t}Hierarchy{/t};url={$BASE_URL}hierarchy/HierarchyControlList.php;");
	{/if}
	{if $permission->Check('company','enabled') AND $permission->Check('company','edit_own_bank')}
		aI("text={t}Company Bank Information{/t};url={$BASE_URL}bank_account/EditBankAccount.php?company_id={$current_company->getId()};");
	{/if}
	{if $permission->Check('holiday_policy','enabled') AND $permission->Check('holiday_policy','view')}
		aI("text={t}Recurring Holidays{/t};url={$BASE_URL}policy/RecurringHolidayList.php;");
	{/if}
	{if $permission->Check('other_field','enabled') AND $permission->Check('other_field','view')}
		aI("text={t}Other Fields{/t};url={$BASE_URL}company/OtherFieldList.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}

	{if ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
		OR ( $permission->Check('policy_group','enabled') AND $permission->Check('policy_group','view') )
		OR ( $permission->Check('schedule_policy','enabled') AND $permission->Check('schedule_policy','view') )
		OR ( $permission->Check('meal_policy','enabled') AND $permission->Check('meal_policy','view') )
		OR ( $permission->Check('break_policy','enabled') AND $permission->Check('break_policy','view') )
		OR ( $permission->Check('over_time_policy','enabled') AND $permission->Check('over_time_policy','view') )
		OR ( $permission->Check('premium_policy','enabled') AND $permission->Check('premium_policy','view') )
		OR ( $permission->Check('accrual_policy','enabled') AND $permission->Check('accrual_policy','view') )
		OR ( $permission->Check('absence_policy','enabled') AND $permission->Check('absence_policy','view') )
		OR ( $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view') )
		OR ( $permission->Check('exception_policy','enabled') AND $permission->Check('exception_policy','view') )
		OR ( $permission->Check('holiday_policy','enabled') AND $permission->Check('holiday_policy','view') )}
	{literal}
	with(milonic=new menuname("admin_policy")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('policy_group','enabled') AND $permission->Check('policy_group','view')}
		aI("text={t}Policy Groups{/t};url={$BASE_URL}policy/PolicyGroupList.php;");
	{/if}
	{if $permission->Check('schedule_policy','enabled') AND $permission->Check('schedule_policy','view')}
		aI("text={t}Schedule Policies{/t};url={$BASE_URL}policy/SchedulePolicyList.php;");
	{/if}
	{if $permission->Check('round_policy','enabled') AND $permission->Check('round_policy','view')}
		aI("text={t}Rounding Policies{/t};url={$BASE_URL}policy/RoundIntervalPolicyList.php;");
	{/if}
	{if $permission->Check('meal_policy','enabled') AND $permission->Check('meal_policy','view')}
		aI("text={t}Meal Policies{/t};url={$BASE_URL}policy/MealPolicyList.php;");
	{/if}
	{if $permission->Check('break_policy','enabled') AND $permission->Check('break_policy','view')}
		aI("text={t}Break Policies{/t};url={$BASE_URL}policy/BreakPolicyList.php;");
	{/if}
	{if $permission->Check('accrual_policy','enabled') AND $permission->Check('accrual_policy','view')}
		aI("text={t}Accrual Policies{/t};url={$BASE_URL}policy/AccrualPolicyList.php;");
	{/if}
	{if $permission->Check('over_time_policy','enabled') AND $permission->Check('over_time_policy','view')}
		aI("text={t}Overtime Policies{/t};url={$BASE_URL}policy/OverTimePolicyList.php;");
	{/if}
	{if $permission->Check('premium_policy','enabled') AND $permission->Check('premium_policy','view')}
		aI("text={t}Premium Policies{/t};url={$BASE_URL}policy/PremiumPolicyList.php;");
	{/if}
	{if $permission->Check('absence_policy','enabled') AND $permission->Check('absence_policy','view')}
		aI("text={t}Absence Policies{/t};url={$BASE_URL}policy/AbsencePolicyList.php;");
	{/if}
	{if $permission->Check('exception_policy','enabled') AND $permission->Check('exception_policy','view')}
		aI("text={t}Exception Policies{/t};url={$BASE_URL}policy/ExceptionPolicyControlList.php;");
	{/if}
	{if $permission->Check('holiday_policy','enabled') AND $permission->Check('holiday_policy','view')}
		aI("text={t}Holiday Policies{/t};url={$BASE_URL}policy/HolidayPolicyList.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}

	{if ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
		OR ( $permission->Check('pay_stub_amendment','enabled') AND ( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_child') OR $permission->Check('pay_stub_amendment','view_own') ) )
		OR ( $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view') )
		OR ( $permission->Check('pay_stub_amendment','enabled') AND $permission->Check('pay_stub_amendment','edit') )}
	{literal}
	with(milonic=new menuname("admin_payroll")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view')}
		aI("text={t}End of Pay Period{/t};url={$BASE_URL}payperiod/ClosePayPeriod.php;");
	{/if}
	{if $permission->Check('pay_stub_amendment','enabled') AND ( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_child') OR $permission->Check('pay_stub_amendment','view_own') )}
		aI("text={t}Pay Stub Amendments{/t};url={$BASE_URL}pay_stub_amendment/PayStubAmendmentList.php;");
		aI("text={t}Recurring PS Amendments{/t};url={$BASE_URL}pay_stub_amendment/RecurringPayStubAmendmentList.php;");
	{/if}
	{if $permission->Check('pay_period_schedule','enabled') AND $permission->Check('pay_period_schedule','view')}
		aI("text={t}Pay Period Schedules{/t};url={$BASE_URL}payperiod/PayPeriodScheduleList.php;");
	{/if}
	{if $permission->Check('pay_stub_account','enabled') AND $permission->Check('pay_stub_account','view')}
		aI("text={t}Pay Stub Accounts{/t};url={$BASE_URL}pay_stub/PayStubEntryAccountList.php;");
	{/if}
	{if $permission->Check('company_tax_deduction','enabled') AND $permission->Check('company_tax_deduction','view')}
		aI("text={t}Taxes / Deductions{/t};url={$BASE_URL}company/CompanyDeductionList.php;");
	{/if}
	{if $permission->Check('pay_stub_account','enabled') AND $permission->Check('pay_stub_account','view')}
		aI("text={t}Pay Stub Account Linking{/t};url={$BASE_URL}pay_stub/EditPayStubEntryAccountLink.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}
{/if}


{if $permission->Check('report','enabled')}
{literal}
with(milonic=new menuname("report")){
style=menuStyle;
{/literal}
{if $permission->Check('job_report','enabled') }
	aI("showmenu=report_job;text={t}Job Reports{/t};");
{/if}
{if $permission->Check('invoice_report','enabled') }
	aI("showmenu=report_invoice;text={t}Invoice Reports{/t};");
{/if}
{if $permission->Check('report','view_active_shift')}
	aI("text={t}Whos In Summary{/t};url={$BASE_URL}report/ActiveShiftList.php;");
{/if}
{if $permission->Check('report','view_user_information')}
	aI("text={t}Employee Information Summary{/t};url={$BASE_URL}report/UserInformation.php;");
{/if}
{if $permission->Check('report','view_user_detail')}
	aI("text={t}Employee Detail{/t};url={$BASE_URL}report/UserDetail.php;");
{/if}
{if $permission->Check('report','view_schedule_summary')}
	aI("text={t}Schedule Summary{/t};url={$BASE_URL}report/ScheduleSummary.php;");
{/if}
{if $permission->Check('report','view_timesheet_summary')}
	aI("text={t}Timesheet Summary{/t};url={$BASE_URL}report/TimesheetSummary.php;");
	aI("text={t}Timesheet Detail{/t};url={$BASE_URL}report/TimesheetDetail.php;");
{/if}
{if $permission->Check('report','view_punch_summary')}
	aI("text={t}Punch Summary{/t};url={$BASE_URL}report/PunchSummary.php;");
{/if}
{if $permission->Check('report','view_accrual_balance_summary')}
	aI("text={t}Accrual Balance Summary{/t};url={$BASE_URL}report/AccrualBalanceSummary.php;");
{/if}
{if $permission->Check('report','view_pay_stub_summary')}
	aI("text={t}Pay Stub Summary{/t};url={$BASE_URL}report/PayStubSummary.php;");
{/if}
{if $permission->Check('report','view_wages_payable_summary')}
	aI("text={t}Wages Payable Summary{/t};url={$BASE_URL}report/WagesPayableSummary.php;");
{/if}
{if $permission->Check('report','view_payroll_export')}
	aI("text={t}Payroll Export{/t};url={$BASE_URL}report/PayrollExport.php;");
{/if}
{if $permission->Check('report','view_system_log')}
	aI("text={t}Audit Trail{/t};url={$BASE_URL}report/SystemLog.php;");
{/if}
{if $permission->Check('report','view_general_ledger_summary')}
	aI("text={t}General Ledger Summary{/t};url={$BASE_URL}report/GeneralLedgerSummary.php;");
{/if}
{if $permission->Check('report','view_user_barcode')}
	aI("text={t}Employee Barcodes{/t};url={$BASE_URL}report/UserBarcode.php;");
{/if}
{if $permission->Check('report','view_remittance_summary')
	OR $permission->Check('report','view_t4_summary')
	OR $permission->Check('report','view_form941')
	OR $permission->Check('report','view_form1099misc')}
	aI("showmenu=report_tax;text={t}Tax Reports{/t};");
{/if}
{literal}
}
{/literal}

	{if $permission->Check('job_report','enabled') }
	{literal}
	with(milonic=new menuname("report_job")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('job_report','view_job_summary')}
		aI("text={t}Job Summary{/t};url={$BASE_URL}report/JobSummary.php;");
	{/if}
	{if $permission->Check('job_report','view_job_analysis')}
		aI("text={t}Job Analysis{/t};url={$BASE_URL}report/JobDetail.php;");
	{/if}
	{if $permission->Check('job_report','view_job_payroll_analysis')}
		aI("text={t}Job Payroll Analysis{/t};url={$BASE_URL}report/JobPayrollDetail.php;");
	{/if}
	{if $permission->Check('job_report','view_job_barcode')}
		aI("text={t}Barcodes{/t};url={$BASE_URL}report/JobBarcode.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}

	{if $permission->Check('invoice_report','enabled') }
	{literal}
	with(milonic=new menuname("report_invoice")){
	style=menuStyle;
	{/literal}
	{if $permission->Check('invoice_report','view_transaction_summary')}
		aI("text={t}Transaction Summary{/t};url={$BASE_URL}report/InvoiceTransactionSummary.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}

	{if $permission->Check('report','view_remittance_summary')
		OR $permission->Check('report','view_t4_summary')
		OR $permission->Check('report','view_form941')
		OR $permission->Check('report','view_form1099misc')}
	{literal}
	with(milonic=new menuname("report_tax")){
	style=menuStyle;
	{/literal}
	{if $current_company->getCountry() == 'CA'}
		{if $permission->Check('report','view_remittance_summary')}
			aI("text={t}Remittance Summary{/t};url={$BASE_URL}report/RemittanceSummary.php;");
		{/if}
		{if $permission->Check('report','view_t4_summary')}
			aI("text={t}T4 Summary{/t};url={$BASE_URL}report/T4Summary.php;");
			aI("text={t}T4A Summary{/t};url={$BASE_URL}report/T4ASummary.php;");
		{/if}
	{/if}
	{if $current_company->getCountry() == 'US'}
		{if $permission->Check('report','view_form941')}
			aI("text={t}Form 941{/t};url={$BASE_URL}report/Form941.php;");
		{/if}
		{if $permission->Check('report','view_form940')}
			aI("text={t}FUTA - Form 940{/t};url={$BASE_URL}report/Form940.php;");
		{/if}
		{if $permission->Check('report','view_form940ez')}
			aI("text={t}FUTA - Form 940-EZ{/t};url={$BASE_URL}report/Form940ez.php;");
		{/if}
		{if $permission->Check('report','view_form1099misc')}
			aI("text={t}Form 1099-Misc{/t};url={$BASE_URL}report/Form1099Misc.php;");
		{/if}
		{if $permission->Check('report','view_formW2')}
			aI("text={t}Form W2 / W3{/t};url={$BASE_URL}report/FormW2.php;");
		{/if}
	{/if}
	{if $permission->Check('report','view_generic_tax_summary')}
		aI("text={t}Tax Summary (Generic){/t};url={$BASE_URL}report/GenericTaxSummary.php;");
	{/if}
	{literal}
	}
	{/literal}
	{/if}
{/if}

{if $permission->Check('user','edit_own')
	OR $permission->Check('user_preference','enabled')
	OR $permission->Check('user','edit_own_bank')
	OR $permission->Check('message','enabled')}
{literal}
with(milonic=new menuname("myaccount")){
style=menuStyle;
{/literal}
{if $permission->Check('message','enabled') }
	aI("text={t}Messages{/t}{if $unread_messages > 0} ({$unread_messages}){/if};url={$BASE_URL}message/UserMessageList.php;{if $unread_messages > 0}image={$IMAGES_URL}/mail.png{/if}");
{/if}
{if $permission->Check('user','edit_own')}
	aI("text={t}Contact Information{/t};url={$BASE_URL}users/EditUser.php?id={$current_user->getId()};");
{/if}
{if $permission->Check('user_preference','enabled') AND ( $permission->Check('user_preference','edit_own') OR $permission->Check('user_preference','edit') OR $permission->Check('user_preference','edit_child') )}
	aI("text={t}Preferences{/t};url={$BASE_URL}users/EditUserPreference.php;");
{/if}
{if $permission->Check('user','edit_own_password')}
	aI("text={t}Web Password{/t};url={$BASE_URL}users/EditUserPassword.php?id={$current_user->getId()};");
{/if}
{if $permission->Check('user','edit_own_phone_password')}
	aI("text={t}Quick Punch Password{/t};url={$BASE_URL}users/EditUserPhonePassword.php?id={$current_user->getId()};");
{/if}
{if $permission->Check('user','edit_own_bank')}
	aI("text={t}Bank Information{/t};url={$BASE_URL}bank_account/EditBankAccount.php;");
{/if}
{literal}
}
{/literal}
{/if}

{if ( $permission->Check('user','edit') OR $permission->Check('user','edit_child')
	OR $permission->Check('recurring_schedule','enabled')
	OR $permission->Check('recurring_schedule_template','enabled') )
	AND ( !isset($config_vars.branding ) OR ( isset($config_vars.branding) AND $current_company->getID() == $config_vars.other.primary_company_id ) )}
{literal}
with(milonic=new menuname("help")){
style=menuStyle;
{/literal}
{if DEMO_MODE == FALSE}
	aI("text={t}Online University{/t};target=new;url={$BASE_URL}help/About.php?action:university=1;");
{/if}
aI("text={t}Administrator Guide{/t};target=new;url=http://www.timetrex.com/wiki/index.php/TimeTrex_{php}echo getTTProductEditionName(){/php}_Edition_Administrator_Guide_v3;");
aI("text={t}FAQ{/t};target=new;url=http://www.timetrex.com/wiki/index.php/TimeTrex_{php}echo getTTProductEditionName(){/php}_Edition_FAQ_v3;");
{if $current_company->getProductEdition() == 10}
aI("text={t}Support Forums{/t};target=new;url=http://forums.timetrex.com;");
{/if}
aI("text={t}What's New{/t};target=new;url=http://www.timetrex.com/wiki/index.php/TimeTrex_{php}echo getTTProductEditionName(){/php}_Edition_ChangeLog_v{$system_settings.system_version};");
aI("text={t}About{/t};url={$BASE_URL}help/About.php;{if $system_settings.new_version == 1}image={$IMAGES_URL}red_flag.gif{/if}");
{literal}
}
{/literal}
{/if}

drawMenus();