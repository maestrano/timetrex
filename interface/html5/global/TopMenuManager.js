var TopMenuManager = function() {

};

TopMenuManager.ribbon_menus = null;

TopMenuManager.selected_menu_id = '';

TopMenuManager.selected_sub_menu_id = '';

TopMenuManager.menus_quick_map = {}; //Save map for subMenuID to menuID, use this when set select menu on UI

TopMenuManager.ribbon_view_controller = null;

TopMenuManager.goToView = function( subMenuId, force_refresh ) {
	if ( !TopMenuManager.ribbon_menus ) {
		TopMenuManager.initRibbonMenu();

	}

	if ( window.location.href === Global.getBaseURL() + '#!m=' + subMenuId && force_refresh ) {
		IndexViewController.instance.router.reloadView( subMenuId );
	} else {
		window.location = Global.getBaseURL() + '#!m=' + subMenuId;

	}

};

TopMenuManager.goToPortalView = function( subMenuId, force_refresh ) {
	if ( !TopMenuManager.ribbon_menus ) {
		TopMenuManager.initPortalRibbonMenu();

	}

	if ( window.location.href === Global.getBaseURL() + '#!m=' + subMenuId && force_refresh ) {
		IndexViewController.instance.router.reloadView( subMenuId );
	} else {
		window.location = Global.getBaseURL() + '#!m=' + subMenuId;

	}

};

TopMenuManager.initRibbonMenu = function() {
	if ( !TopMenuManager.ribbon_menus ) {

		//when login and refresh, will go into this place, do session check here
		TopMenuManager.buildRibbonMenuModels();
		Global.setupPing();
		IndexViewController.setNotificationBar( 'login' );

	}
};

TopMenuManager.initPortalRibbonMenu = function() {
	if ( !TopMenuManager.ribbon_menus ) {

		//when login and refresh, will go into this place, do session check here
		TopMenuManager.buildPortalRibbonMenuModels();
		Global.setupPing();

	}
};

TopMenuManager.buildPortalRibbonMenuModels = function() {

	var permission = PermissionManager.getPermissionData();
	//HR Menu
	var hr_menu = new RibbonMenu( {
		label: $.i18n._( 'HR' ),
		id: 'hr_menu',
		sub_menu_groups: []
	} );

	//reviews group
	var reviewsSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Reviews' ),
		id: 'reviewsGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	//reviews Group Sub Menu
	var user_review_control = new RibbonSubMenu( {
		label: $.i18n._( 'Reviews' ),
		id: 'UserReviewControl',
		group: reviewsSubMenuGroup,
		icon: 'reviews-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserReviewControl' ),
		permission: permission.user_review
	} );

	var kpi = new RibbonSubMenu( {
		label: $.i18n._( 'KPI' ),
		id: 'KPI',
		group: reviewsSubMenuGroup,
		icon: 'KPI-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'KPI' ),
		permission: permission.kpi
	} );

	var kpi_group = new RibbonSubMenu( {
		label: $.i18n._( 'KPI<br>Groups' ),
		id: 'KPIGroup',
		group: reviewsSubMenuGroup,
		icon: 'KPI_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'KPIGroup' ),
		permission: permission.kpi
	} );

	//Qualifications group
	var qualificationSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Qualifications' ),
		id: 'qualificationGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	var qualification = new RibbonSubMenu( {
		label: $.i18n._( 'Qualifications' ),
		id: 'Qualification',
		group: qualificationSubMenuGroup,
		icon: 'qualifications.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Qualification' ),
		permission: permission.qualification
	} );

	var qualification_group = new RibbonSubMenu( {
		label: $.i18n._( 'Qualification<br>Groups' ),
		id: 'QualificationGroup',
		group: qualificationSubMenuGroup,
		icon: 'qualification_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'QualificationGroup' ),
		permission: permission.qualification
	} );

	var user_skill = new RibbonSubMenu( {
		label: $.i18n._( 'Skills' ),
		id: 'UserSkill',
		group: qualificationSubMenuGroup,
		icon: 'skill-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserSkill' ),
		permission: permission.user_skill
	} );

	var user_education = new RibbonSubMenu( {
		label: $.i18n._( 'Education' ),
		id: 'UserEducation',
		group: qualificationSubMenuGroup,
		icon: 'education-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserEducation' ),
		permission: permission.user_education
	} );

	var user_membership = new RibbonSubMenu( {
		label: $.i18n._( 'Memberships' ),
		id: 'UserMembership',
		group: qualificationSubMenuGroup,
		icon: 'memberships.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserMembership' ),
		permission: permission.user_membership
	} );

	var user_license = new RibbonSubMenu( {
		label: $.i18n._( 'Licenses' ),
		id: 'UserLicense',
		group: qualificationSubMenuGroup,
		icon: 'license-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserLicense' ),
		permission: permission.user_license
	} );

	var user_language = new RibbonSubMenu( {
		label: $.i18n._( 'Languages' ),
		id: 'UserLanguage',
		group: qualificationSubMenuGroup,
		icon: 'languages-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserLanguage' ),
		permission: permission.user_language
	} );

	// Recruitment group
	var recruitmentSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Recruitment' ),
		id: 'recruitmentGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	var job_vacancy = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Vacancies' ),
		id: 'PortalJobVacancy',
		group: recruitmentSubMenuGroup,
		icon: 'job_vacancies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobVacancy' ),
		permission: permission.job_vacancy
	} );

	var job_applicant = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Applicants' ),
		id: 'JobApplicant',
		group: recruitmentSubMenuGroup,
		icon: 'job_applicant-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobApplicant' ),
		permission: permission.job_applicant
	} );

	var job_application = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Applications' ),
		id: 'JobApplication',
		group: recruitmentSubMenuGroup,
		icon: 'jobapplications-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobApplication' ),
		permission: permission.job_application
	} );

	//My Account group

	var my_account_menu = new RibbonMenu( {
		label: $.i18n._( 'My Account' ),
		id: 'myAccountMenu',
		sub_menu_groups: []
	} );

	var logoutGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Logout' ),
		id: 'logoutGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var logout = new RibbonSubMenu( {
		label: $.i18n._( 'Logout' ),
		id: 'PortalLogout',
		group: logoutGroup,
		icon: 'logout-35x35.png',
		permission_result: true,
		permission: true
	} );

	TopMenuManager.ribbon_menus = [hr_menu, my_account_menu];

}

TopMenuManager.buildRibbonMenuModels = function() {

	var permission = PermissionManager.getPermissionData();

	//Error: TypeError: null is not an object (evaluating 'permission.punch') in https://ondemand1.timetrex.com/interface/html5/global/TopMenuManager.js?v=8.0.0-20141230-130626 line 280
	if ( !permission ) {
		permission = {};
	}

	//Attendance Menu
	var attendance_menu = new RibbonMenu( {
		label: $.i18n._( 'Attendance' ),
		id: 'attendance_menu',
		sub_menu_groups: []
	} );

	//Attendance group
	var attendanceSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Attendance' ),
		id: 'attendanceGroup',
		ribbon_menu: attendance_menu,
		sub_menus: []
	} );

	//Attendance Group Sub Menu
	var inout = new RibbonSubMenu( {
		label: $.i18n._( 'In/Out' ),
		id: 'InOut',
		group: attendanceSubMenuGroup,
		icon: 'clock_in_out-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'InOut' ),
		permission: permission.punch
	} );
	var timesheet = new RibbonSubMenu( {
		label: $.i18n._( 'TimeSheet' ),
		id: 'TimeSheet',
		group: attendanceSubMenuGroup,
		icon: 'timesheet-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheet' ),
		permission: permission.punch
	} );

	var punches = new RibbonSubMenu( {
		label: $.i18n._( 'Punches' ),
		id: 'Punches',
		group: attendanceSubMenuGroup,
		icon: 'punches-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Punches' ),
		permission: permission.punch
	} );

	var exceptions = new RibbonSubMenu( {
		label: $.i18n._( 'Exceptions' ),
		id: 'Exception',
		group: attendanceSubMenuGroup,
		icon: 'exceptions-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Exception' ),
		permission: permission.punch
	} );

	var accrual_balance = new RibbonSubMenu( {
		label: $.i18n._( 'Accrual<br>Balances' ),
		id: 'AccrualBalance',
		group: attendanceSubMenuGroup,
		icon: 'accrual_balance-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'AccrualBalance' ),
		permission: permission.accrual
	} );

	var accrual = new RibbonSubMenu( {
		label: $.i18n._( 'Accruals' ),
		id: 'Accrual',
		group: attendanceSubMenuGroup,
		icon: 'accrual-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Accrual' ),
		permission: permission.accrual
	} );

	//Schedule group
	var scheduleSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Schedule' ),
		id: 'scheduleGroup',
		ribbon_menu: attendance_menu,
		sub_menus: []
	} );

	var schedule = new RibbonSubMenu( {
		label: $.i18n._( 'Schedules' ),
		id: 'Schedule',
		group: scheduleSubMenuGroup,
		icon: 'schedule-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Schedule' ),
		permission: permission.schedule
	} );

	var schedule_shift = new RibbonSubMenu( {
		label: $.i18n._( 'Scheduled<br>Shifts' ),
		id: 'ScheduleShift',
		group: scheduleSubMenuGroup,
		icon: 'scheduled_shifts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ScheduleShift' ),
		permission: permission.schedule
	} );

	var recurring_schedule_control = new RibbonSubMenu( {
		label: $.i18n._( 'Recurring<br>Schedules' ),
		id: 'RecurringScheduleControl',
		group: scheduleSubMenuGroup,
		icon: 'recurring_schedule-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RecurringScheduleControl' ),
		permission: permission.recurring_schedule
	} );

	var recurring_schedule_template_control = new RibbonSubMenu( {
		label: $.i18n._( 'Recurring<br>Templates' ),
		id: 'RecurringScheduleTemplateControl',
		group: scheduleSubMenuGroup,
		icon: 'recurring_template-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RecurringScheduleTemplateControl' ),
		permission: permission.recurring_schedule_template
	} );

	//Job Trancking group
	var jobTrackingSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Job Tracking' ),
		id: 'jobTrackingGroup',
		ribbon_menu: attendance_menu,
		sub_menus: []
	} );

	var job = new RibbonSubMenu( {
		label: $.i18n._( 'Jobs' ),
		id: 'Job',
		group: jobTrackingSubMenuGroup,
		icon: 'jobs-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Job' ),
		permission: permission.job
	} );

	var job_item = new RibbonSubMenu( {
		label: $.i18n._( 'Tasks' ),
		id: 'JobItem',
		group: jobTrackingSubMenuGroup,
		icon: 'tasks-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobItem' ),
		permission: permission.job_item
	} );

	var job_group = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Groups' ),
		id: 'JobGroup',
		group: jobTrackingSubMenuGroup,
		icon: 'job_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobGroup' ),
		permission: permission.job
	} );

	var job_item_group = new RibbonSubMenu( {
		label: $.i18n._( 'Task<br>Groups' ),
		id: 'JobItemGroup',
		group: jobTrackingSubMenuGroup,
		icon: 'task_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobItemGroup' ),
		permission: permission.job_item
	} );

	//Employee Menu
	var employee_menu = new RibbonMenu( {
		label: $.i18n._( 'Employee' ),
		id: 'employee_menu',
		sub_menu_groups: []
	} );

	//Employee Group
	var employeeSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Employee' ),
		id: 'employeeGroup',
		ribbon_menu: employee_menu,
		sub_menus: []
	} );

	//Employee group Sub Menu
	var employee = new RibbonSubMenu( {
		label: $.i18n._( 'Employees' ),
		id: 'Employee',
		group: employeeSubMenuGroup,
		icon: 'employees-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Employee' ),
		permission: permission.user
	} );

	var user_contact = new RibbonSubMenu( {
		label: $.i18n._( 'Employee<br>Contacts' ),
		id: 'UserContact',
		group: employeeSubMenuGroup,
		icon: 'contact_information-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserContact' ),
		permission: permission.user_contact
	} );

	var user_preference = new RibbonSubMenu( {
		label: $.i18n._( 'Preferences' ),
		id: 'UserPreference',
		group: employeeSubMenuGroup,
		icon: 'preferences-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserPreference' ),
		permission: permission.user_preference
	} );

	var wages = new RibbonSubMenu( {
		label: $.i18n._( 'Wages' ),
		id: 'Wage',
		group: employeeSubMenuGroup,
		icon: 'wages-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Wage' ),
		permission: permission.wage
	} );

	var bank_account = new RibbonSubMenu( {
		label: $.i18n._( 'Bank<br>Accounts' ),
		id: 'EmployeeBankAccount',
		group: employeeSubMenuGroup,
		icon: 'bank_accounts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'EmployeeBankAccount' ),
		permission: permission.user
	} );

	var user_title = new RibbonSubMenu( {
		label: $.i18n._( 'Job Titles' ),
		id: 'UserTitle',
		group: employeeSubMenuGroup,
		icon: 'job_titles-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserTitle' ),
		permission: permission.user
	} );

	var user_group = new RibbonSubMenu( {
		label: $.i18n._( 'Groups' ),
		id: 'UserGroup',
		group: employeeSubMenuGroup,
		icon: 'groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserGroup' ),
		permission: permission.user
	} );

	//Moved Ethnic Groups icon to Employee top menu tab, this is different from Flex, but makes more sense.
	var ethnic_group = new RibbonSubMenu( {
		label: $.i18n._( 'Ethnic<br>Groups' ),
		id: 'EthnicGroup',
		group: employeeSubMenuGroup,
		icon: 'ethinicgroups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'EthnicGroup' ),
		permission: permission.user

	} );

	var user_default = new RibbonSubMenu( {
		label: $.i18n._( 'New Hire<br>Defaults' ),
		id: 'UserDefault',
		group: employeeSubMenuGroup,
		icon: 'new_hire_defaults-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserDefault' ),
		permission: permission.user
	} );

//	roeValidate
	var roe = new RibbonSubMenu( {
		label: $.i18n._( 'Record of<br>Employment' ),
		id: 'ROE',
		group: employeeSubMenuGroup,
		icon: 'record_of_employment-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ROE' ),
		permission: permission.roe
	} );

	//Company Menu
	var company_menu = new RibbonMenu( {
		label: $.i18n._( 'Company' ),
		id: 'company_menu',
		sub_menu_groups: []
	} );

	var companySubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Company' ),
		id: 'companyGroup',
		ribbon_menu: company_menu,
		sub_menus: []
	} );

	var companySubMenuOtherGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Other' ),
		id: 'companyOtherGroup',
		ribbon_menu: company_menu,
		sub_menus: []
	} );

	var companies = new RibbonSubMenu( {
		label: $.i18n._( 'Companies' ),
		id: 'Companies',
		group: companySubMenuGroup,
		icon: 'companies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Companies' ),
		permission: permission.company
	} );

	var company = new RibbonSubMenu( {
		label: $.i18n._( 'Company<br>Information' ),
		id: 'Company',
		group: companySubMenuGroup,
		icon: 'company_information-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Company' ),
		permission: permission.company
	} );

	var pay_period_schedule = new RibbonSubMenu( {
		label: $.i18n._( 'Pay Period<br>Schedules' ),
		id: 'PayPeriodSchedule',
		group: companySubMenuGroup,
		icon: 'pay_period_schedules-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayPeriodSchedule' ),
		permission: permission.pay_period_schedule
	} );

	var branch = new RibbonSubMenu( {
		label: $.i18n._( 'Branches' ),
		id: 'Branch',
		group: companySubMenuGroup,
		icon: 'branches-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Branch' ),
		permission: permission.branch
	} );

	var department = new RibbonSubMenu( {
		label: $.i18n._( 'Departments' ),
		id: 'Department',
		group: companySubMenuGroup,
		icon: 'departments-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Department' ),
		permission: permission.department
	} );

	var hierarchy_control = new RibbonSubMenu( {
		label: $.i18n._( 'Hierarchy' ),
		id: 'HierarchyControl',
		group: companySubMenuGroup,
		icon: 'hierarchy-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'HierarchyControl' ),
		permission: permission.hierarchy

	} );

	var wage_group = new RibbonSubMenu( {
		label: $.i18n._( 'Secondary<br>Wage Groups' ),
		id: 'WageGroup',
		group: companySubMenuGroup,
		icon: 'wage_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'WageGroup' ),
		permission: permission.wage

	} );

	var station = new RibbonSubMenu( {
		label: $.i18n._( 'Stations' ),
		id: 'Station',
		group: companySubMenuGroup,
		icon: 'stations-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Station' ),
		permission: permission.station
	} );

	var permission_control = new RibbonSubMenu( {
		label: $.i18n._( 'Permission<br>Groups' ),
		id: 'PermissionControl',
		group: companySubMenuGroup,
		icon: 'permission_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PermissionControl' ),
		permission: permission.permission
	} );
	var currency = new RibbonSubMenu( {
		label: $.i18n._( 'Currencies' ),
		id: 'Currency',
		group: companySubMenuGroup,
		icon: 'currencies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Currency' ),
		permission: permission.currency

	} );
	var company_bank_account = new RibbonSubMenu( {
		label: $.i18n._( 'Bank<br>Account' ),
		id: 'CompanyBankAccount',
		group: companySubMenuGroup,
		icon: 'bank_accounts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'CompanyBankAccount' ),
		permission: permission.user
	} );
	var other_field = new RibbonSubMenu( {
		label: $.i18n._( 'Custom<br>Fields' ),
		id: 'OtherField',
		group: companySubMenuGroup,
		icon: 'custom_fields-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'OtherField' ),
		permission: permission.other_field
	} );

	var import_csv = new RibbonSubMenu( {
		label: $.i18n._( 'Import' ),
		id: 'ImportCSV',
		group: companySubMenuOtherGroup,
		icon: Icons.import_icon,
		permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSV' ),
		permission: permission.other_field
	} );

	var quick_start = new RibbonSubMenu( {
		label: $.i18n._( 'Quick<br>Start' ),
		id: 'QuickStartWizard',
		group: companySubMenuOtherGroup,
		icon: Icons.quick_start_wizard,
		permission_result: PermissionManager.checkTopLevelPermission( 'QuickStartWizard' ),
		permission: permission.other_field
	} );

	var payroll_menu = new RibbonMenu( {
		label: $.i18n._( 'Payroll' ),
		id: 'payroll_menu',
		sub_menu_groups: []
	} );

	var payrollSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Payroll' ),
		id: 'payrollGroup',
		ribbon_menu: payroll_menu,
		sub_menus: []
	} );

	var process_payroll = new RibbonSubMenu( {
		label: $.i18n._( 'Process<br>Payroll' ),
		id: 'ProcessPayrollWizard',
		group: payrollSubMenuGroup,
		icon: Icons.process_payroll,
		permission_result: PermissionManager.checkTopLevelPermission( 'PayrollProcessWizard' ),
		permission: permission.pay_stub
	} );

	var pay_stub = new RibbonSubMenu( {
		label: $.i18n._( 'Pay<br>Stubs' ),
		id: 'PayStub',
		group: payrollSubMenuGroup,
		icon: 'pay_stubs-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayStub' ),
		permission: permission.pay_stub
	} );

	var pay_periods = new RibbonSubMenu( {
		label: $.i18n._( 'Pay<br>Periods' ),
		id: 'PayPeriods',
		group: payrollSubMenuGroup,
		icon: 'pay_periods-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayPeriods' ),
		permission: permission.pay_period_schedule
	} );

	var pay_stub_amendment = new RibbonSubMenu( {
		label: $.i18n._( 'Pay Stub<br>Amendments' ),
		id: 'PayStubAmendment',
		group: payrollSubMenuGroup,
		icon: 'pay_stub_amendments-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayStubAmendment' ),
		permission: permission.pay_stub_amendment
	} );

	var recurring_pay_stub_amendment = new RibbonSubMenu( {
		label: $.i18n._( 'Recurring PS<br>Amendments' ),
		id: 'RecurringPayStubAmendment',
		group: payrollSubMenuGroup,
		icon: 'recurring_pay_stub_amendments-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RecurringPayStubAmendment' ),
		permission: permission.pay_stub_amendment
	} );

	var pay_period_schedule_1 = new RibbonSubMenu( {
		label: $.i18n._( 'Pay Period<br>Schedules' ),
		id: 'PayPeriodSchedule',
		group: payrollSubMenuGroup,
		icon: 'pay_period_schedules-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayPeriodSchedule' ),
		permission: permission.pay_period_schedule
	} );

	var pay_stub_entry_account = new RibbonSubMenu( {
		label: $.i18n._( 'Pay Stub<br>Accounts' ),
		id: 'PayStubEntryAccount',
		group: payrollSubMenuGroup,
		icon: 'pay_stubs_accounts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayStubEntryAccount' ),
		permission: permission.pay_stub_account
	} );

	var company_tax_deduction = new RibbonSubMenu( {
		label: $.i18n._( 'Taxes &<br>Deductions' ),
		id: 'CompanyTaxDeduction',
		group: payrollSubMenuGroup,
		icon: 'taxes_deductions-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'CompanyTaxDeduction' ),
		permission: permission.company_tax_deduction
	} );

	var user_expense = new RibbonSubMenu( {
		label: $.i18n._( 'Expenses' ),
		id: 'UserExpense',
		group: payrollSubMenuGroup,
		icon: 'expenses-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserExpense' ),
		permission: permission.user_expense
	} );

	var policy_menu = new RibbonMenu( {
		label: $.i18n._( 'Policy' ),
		id: 'policy_menu',
		sub_menu_groups: []
	} );

	var policyBuildingBlocksSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Policy Building Blocks' ),
		id: 'policyBuildingBlocks',
		ribbon_menu: policy_menu,
		sub_menus: []
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Policy<br>Groups' ),
		id: 'PolicyGroup',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'policy_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PolicyGroup' ),
		permission: permission.policy_group
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Pay<br>Codes' ),
		id: 'PayCode',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'pay_code-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayCode' ),
		permission: permission.pay_code
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Pay<br>Formulas' ),
		id: 'PayFormulaPolicy',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'pay_formula_policy-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PayFormulaPolicy' ),
		permission: permission.pay_formula_policy
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Contributing<br>Pay Codes' ),
		id: 'ContributingPayCodePolicy',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'contributing_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ContributingPayCodePolicy' ),
		permission: permission.contributing_pay_code_policy
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Contributing<br>Shifts' ),
		id: 'ContributingShiftPolicy',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'contributing_shift_policy-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ContributingShiftPolicy' ),
		permission: permission.contributing_shift_policy
	} );

	var accrual_policy_account = new RibbonSubMenu( {
		label: $.i18n._( 'Accrual<br>Accounts' ),
		id: 'AccrualPolicyAccount',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'accrual_accounts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'AccrualPolicy' ),
		permission: permission.accrual_policy
	} );

	var recurring_holiday = new RibbonSubMenu( {
		label: $.i18n._( 'Recurring<br>Holidays' ),
		id: 'RecurringHoliday',
		group: policyBuildingBlocksSubMenuGroup,
		icon: 'recurring_holidays-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RecurringHoliday' ),
		permission: permission.holiday_policy
	} );

	var policySubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Policy' ),
		id: 'policyGroup',
		ribbon_menu: policy_menu,
		sub_menus: []
	} );

	var schedule_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Schedule<br>Policies' ),
		id: 'SchedulePolicy',
		group: policySubMenuGroup,
		icon: 'schedule_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'SchedulePolicy' ),
		permission: permission.schedule_policy
	} );

	var round_interval_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Rounding<br>Policies' ),
		id: 'RoundIntervalPolicy',
		group: policySubMenuGroup,
		icon: 'rounding_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RoundIntervalPolicy' ),
		permission: permission.round_policy
	} );

	var meal_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Meal<br>Policies' ),
		id: 'MealPolicy',
		group: policySubMenuGroup,
		icon: 'meal_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'MealPolicy' ),
		permission: permission.meal_policy
	} );

	var break_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Break<br>Policies' ),
		id: 'BreakPolicy',
		group: policySubMenuGroup,
		icon: 'break_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'BreakPolicy' ),
		permission: permission.break_policy
	} );

	var policy_group = new RibbonSubMenu( {
		label: $.i18n._( 'Regular Time<br> Policies' ),
		id: 'RegularTimePolicy',
		group: policySubMenuGroup,
		icon: 'regular_time_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RegularTimePolicy' ),
		permission: permission.regular_time_policy
	} );

	var overtime_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Overtime<br>Policies' ),
		id: 'OvertimePolicy',
		group: policySubMenuGroup,
		icon: 'overtime_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'OvertimePolicy' ),
		permission: permission.over_time_policy
	} );

	var premium_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Premium<br>Policies' ),
		id: 'PremiumPolicy',
		group: policySubMenuGroup,
		icon: 'premium_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PremiumPolicy' ),
		permission: permission.premium_policy
	} );

	var exception_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Exception<br>Policies' ),
		id: 'ExceptionPolicyControl',
		group: policySubMenuGroup,
		icon: 'exceptions_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ExceptionPolicyControl' ),
		permission: permission.exception_policy
	} );

	var accrual_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Accrual<br>Policies' ),
		id: 'AccrualPolicy',
		group: policySubMenuGroup,
		icon: 'accrual_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'AccrualPolicy' ),
		permission: permission.accrual_policy
	} );

	var absence_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Absence<br>Policies' ),
		id: 'AbsencePolicy',
		group: policySubMenuGroup,
		icon: 'absence_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'AbsencePolicy' ),
		permission: permission.absence_policy
	} );

	var expense_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Expense<br>Policies' ),
		id: 'ExpensePolicy',
		group: policySubMenuGroup,
		icon: 'expense_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ExpensePolicy' ),
		permission: permission.expense_policy
	} );

	var holiday_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Holiday<br>Policies' ),
		id: 'HolidayPolicy',
		group: policySubMenuGroup,
		icon: 'holiday_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'HolidayPolicy' ),
		permission: permission.holiday_policy
	} );

	// Invoice group
	var invoice_menu = new RibbonMenu( {
		label: $.i18n._( 'Invoice' ),
		id: 'invoiceMenu',
		sub_menu_groups: []
	} );

	var invoiceGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Invoice' ),
		id: 'invoiceGroup',
		ribbon_menu: invoice_menu,
		sub_menus: []
	} );

	var groups = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Groups' ),
		id: 'groups',
		ribbon_menu: invoice_menu,
		sub_menus: []
	} );

	var invoice_policies = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Policies' ),
		id: 'invoicePolicies',
		ribbon_menu: invoice_menu,
		sub_menus: []
	} );

	var invoice_settings = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Settings' ),
		id: 'invoiceSettings',
		ribbon_menu: invoice_menu,
		sub_menus: []
	} );

	var client = new RibbonSubMenu( {
		label: $.i18n._( 'Clients' ),
		id: 'Client',
		group: invoiceGroup,
		icon: 'clients-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Client' ),
		permission: permission.client
	} );

	var client_contact = new RibbonSubMenu( {
		label: $.i18n._( 'Client<br>Contacts' ),
		id: 'ClientContact',
		group: invoiceGroup,
		icon: 'clients_contacts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ClientContact' ),
		permission: permission.client
	} );

	var invoice = new RibbonSubMenu( {
		label: $.i18n._( 'Invoices' ),
		id: 'Invoice',
		group: invoiceGroup,
		icon: 'invoices-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Invoice' ),
		permission: permission.invoice
	} );

	var invoice_transaction = new RibbonSubMenu( {
		label: $.i18n._( 'Transactions' ),
		id: 'InvoiceTransaction',
		group: invoiceGroup,
		icon: 'transactions-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceTransaction' ),
		permission: permission.transaction
	} );

	var client_payment = new RibbonSubMenu( {
		label: $.i18n._( 'Payment<br>Methods' ),
		id: 'ClientPayment',
		group: invoiceGroup,
		icon: 'payment_methods-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ClientPayment' ),
		permission: permission.client_payment
	} );

	var products = new RibbonSubMenu( {
		label: $.i18n._( 'Products' ),
		id: 'Product',
		group: invoiceGroup,
		icon: 'products-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Product' ),
		permission: permission.product
	} );

	var district = new RibbonSubMenu( {
		label: $.i18n._( 'District' ),
		id: 'InvoiceDistrict',
		group: invoiceGroup,
		icon: 'district-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceDistrict' ),
		permission: permission.client
	} );

	var client_group = new RibbonSubMenu( {
		label: $.i18n._( 'Client' ),
		id: 'ClientGroup',
		group: groups,
		icon: 'client_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Client' ),
		permission: permission.client_payment
	} );

	var product_group = new RibbonSubMenu( {
		label: $.i18n._( 'Product' ),
		id: 'ProductGroup',
		group: groups,
		icon: 'product_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Product' ),
		permission: permission.client_payment
	} );

	var tax_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Tax' ),
		id: 'TaxPolicy',
		group: invoice_policies,
		icon: 'tax_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'TaxPolicy' ),
		permission: permission.client_payment
	} );

	var shipping_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Shipping' ),
		id: 'ShippingPolicy',
		group: invoice_policies,
		icon: 'shipping_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ShippingPolicy' ),
		permission: permission.client_payment
	} );

	var area_policy = new RibbonSubMenu( {
		label: $.i18n._( 'Area' ),
		id: 'AreaPolicy',
		group: invoice_policies,
		icon: 'area_policies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'AreaPolicy' ),
		permission: permission.client_payment
	} );

	var payment_gateway = new RibbonSubMenu( {
		label: $.i18n._( 'Payment<br>Gateway' ),
		id: 'PaymentGateway',
		group: invoice_settings,
		icon: 'payment_gateway_settings-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'PaymentGateway' ),
		permission: permission.client_payment
	} );

	var settings = new RibbonSubMenu( {
		label: $.i18n._( 'Settings' ),
		id: 'InvoiceConfig',
		group: invoice_settings,
		icon: 'settings-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceConfig' ),
		permission: permission.client_payment
	} );

	//HR Menu
	var hr_menu = new RibbonMenu( {
		label: $.i18n._( 'HR' ),
		id: 'hr_menu',
		sub_menu_groups: []
	} );

	//reviews group
	var reviewsSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Reviews' ),
		id: 'reviewsGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	//reviews Group Sub Menu
	var user_review_control = new RibbonSubMenu( {
		label: $.i18n._( 'Reviews' ),
		id: 'UserReviewControl',
		group: reviewsSubMenuGroup,
		icon: 'reviews-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserReviewControl' ),
		permission: permission.user_review
	} );

	var kpi = new RibbonSubMenu( {
		label: $.i18n._( 'KPI' ),
		id: 'KPI',
		group: reviewsSubMenuGroup,
		icon: 'KPI-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'KPI' ),
		permission: permission.kpi
	} );

	var kpi_group = new RibbonSubMenu( {
		label: $.i18n._( 'KPI<br>Groups' ),
		id: 'KPIGroup',
		group: reviewsSubMenuGroup,
		icon: 'KPI_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'KPIGroup' ),
		permission: permission.kpi
	} );

	//Qualifications group
	var qualificationSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Qualifications' ),
		id: 'qualificationGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	var qualification = new RibbonSubMenu( {
		label: $.i18n._( 'Qualifications' ),
		id: 'Qualification',
		group: qualificationSubMenuGroup,
		icon: 'qualifications.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Qualification' ),
		permission: permission.qualification
	} );

	var qualification_group = new RibbonSubMenu( {
		label: $.i18n._( 'Qualification<br>Groups' ),
		id: 'QualificationGroup',
		group: qualificationSubMenuGroup,
		icon: 'qualification_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'QualificationGroup' ),
		permission: permission.qualification
	} );

	var user_skill = new RibbonSubMenu( {
		label: $.i18n._( 'Skills' ),
		id: 'UserSkill',
		group: qualificationSubMenuGroup,
		icon: 'skill-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserSkill' ),
		permission: permission.user_skill
	} );

	var user_education = new RibbonSubMenu( {
		label: $.i18n._( 'Education' ),
		id: 'UserEducation',
		group: qualificationSubMenuGroup,
		icon: 'education-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserEducation' ),
		permission: permission.user_education
	} );

	var user_membership = new RibbonSubMenu( {
		label: $.i18n._( 'Memberships' ),
		id: 'UserMembership',
		group: qualificationSubMenuGroup,
		icon: 'memberships.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserMembership' ),
		permission: permission.user_membership
	} );

	var user_license = new RibbonSubMenu( {
		label: $.i18n._( 'Licenses' ),
		id: 'UserLicense',
		group: qualificationSubMenuGroup,
		icon: 'license-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserLicense' ),
		permission: permission.user_license
	} );

	var user_language = new RibbonSubMenu( {
		label: $.i18n._( 'Languages' ),
		id: 'UserLanguage',
		group: qualificationSubMenuGroup,
		icon: 'languages-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'UserLanguage' ),
		permission: permission.user_language
	} );

	// Recruitment group
	var recruitmentSubMenuGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Recruitment' ),
		id: 'recruitmentGroup',
		ribbon_menu: hr_menu,
		sub_menus: []
	} );

	var job_vacancy = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Vacancies' ),
		id: 'JobVacancy',
		group: recruitmentSubMenuGroup,
		icon: 'job_vacancies-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobVacancy' ),
		permission: permission.job_vacancy
	} );

	var job_applicant = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Applicants' ),
		id: 'JobApplicant',
		group: recruitmentSubMenuGroup,
		icon: 'job_applicant-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobApplicant' ),
		permission: permission.job_applicant
	} );

	var job_application = new RibbonSubMenu( {
		label: $.i18n._( 'Job<br>Applications' ),
		id: 'JobApplication',
		group: recruitmentSubMenuGroup,
		icon: 'jobapplications-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'JobApplication' ),
		permission: permission.job_application
	} );

	//My Account group

	var my_account_menu = new RibbonMenu( {
		label: $.i18n._( 'My Account' ),
		id: 'myAccountMenu',
		sub_menu_groups: []
	} );

	var myAccountGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'MyAccount' ),
		id: 'myAccountGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var authorization = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Authorization' ),
		id: 'myAccountGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var documentsGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Documents' ),
		id: 'documentsGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var securityGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Security' ),
		id: 'securityGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var logoutGroup = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Logout' ),
		id: 'logoutGroup',
		ribbon_menu: my_account_menu,
		sub_menus: []
	} );

	var logout = new RibbonSubMenu( {
		label: $.i18n._( 'Logout' ),
		id: 'Logout',
		group: logoutGroup,
		icon: 'logout-35x35.png',
		permission_result: true,
		permission: true
	} );

	var document = new RibbonSubMenu( {
		label: $.i18n._( 'Documents' ),
		id: 'Document',
		group: documentsGroup,
		icon: 'documents-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Document' ),
		permission: permission.document
	} );

	var document_group = new RibbonSubMenu( {
		label: $.i18n._( 'Document<br>Groups' ),
		id: 'DocumentGroup',
		group: documentsGroup,
		icon: 'document_groups-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'DocumentGroup' ),
		permission: permission.document
	} );

	var request = new RibbonSubMenu( {
		label: $.i18n._( 'Requests' ),
		id: 'Request',
		group: myAccountGroup,
		icon: 'requests-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'Request' ),
		permission: permission.request
	} );

	var message_control = new RibbonSubMenu( {
		label: $.i18n._( 'Messages' ),
		id: 'MessageControl',
		group: myAccountGroup,
		icon: 'messages-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'MessageControl' ),
		permission: permission.message
	} );

	var login_user_contact = new RibbonSubMenu( {
		label: $.i18n._( 'Contact<br>Information' ),
		id: 'LoginUserContact',
		group: myAccountGroup,
		icon: 'contact_information-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserContact' ),
		permission: permission.user
	} );

	var login_user_bank_account = new RibbonSubMenu( {
		label: $.i18n._( 'Bank<br>Information' ),
		id: 'LoginUserBankAccount',
		group: myAccountGroup,
		icon: 'bank_accounts-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserBankAccount' ),
		permission: permission.user
	} );

	var login_user_preference = new RibbonSubMenu( {
		label: $.i18n._( 'Preferences' ),
		id: 'LoginUserPreference',
		group: myAccountGroup,
		icon: 'preferences-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserPreference' ),
		permission: permission.user_preference
	} );

	var login_user_expense = new RibbonSubMenu( {
		label: $.i18n._( 'Expenses' ),
		id: 'LoginUserExpense',
		group: myAccountGroup,
		icon: 'expenses-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserExpense' ),
		permission: permission.user_expense
	} );

	var request_authorization = new RibbonSubMenu( {
		label: $.i18n._( 'Request<br>Authorization' ),
		id: 'RequestAuthorization',
		group: authorization,
		icon: 'authorize_request-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RequestAuthorization' ),
		permission: permission.request
	} );

	var pay_period_time_sheet_verify = new RibbonSubMenu( {
		label: $.i18n._( 'TimeSheet<br>Authorization' ),
		id: 'TimeSheetAuthorization',
		group: authorization,
		icon: 'authorize_timesheet-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'RequestAuthorization' ),
		permission: permission.punch
	} );

	var expense_authorization = new RibbonSubMenu( {
		label: $.i18n._( 'Expense<br>Authorizations' ),
		id: 'ExpenseAuthorization',
		group: authorization,
		icon: 'approved_expense-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ExpenseAuthorization' ),
		permission: permission.user_expense
	} );

	var change_password = new RibbonSubMenu( {
		label: $.i18n._( 'Passwords' ),
		id: 'ChangePassword',
		group: securityGroup,
		icon: 'passwords-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'ChangePassword' ),
		permission: permission.user
	} );

	//Help group
	var help_menu = new RibbonMenu( {
		label: $.i18n._( 'Help' ),
		id: 'helpMenu',
		sub_menu_groups: [],
		permission_result: PermissionManager.helpMenuValidate()
	} );

	var help_group = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Help' ),
		id: 'helpGroup',
		ribbon_menu: help_menu,
		sub_menus: []
	} );

	var admin_guide = new RibbonSubMenu( {
		label: $.i18n._( 'Administrator<br>Guide' ),
		id: 'AdminGuide',
		group: help_group,
		icon: 'administration_guide_manual-35x35.png',
		permission_result: true,
		permission: true
	} );

	var faqs = new RibbonSubMenu( {
		label: $.i18n._( 'FAQs' ),
		id: 'FAQS',
		group: help_group,
		icon: 'faq-35x35.png',
		permission_result: true,
		permission: true
	} );

	var email_help = new RibbonSubMenu( {
		label: $.i18n._( 'Email Help' ),
		id: 'EmailHelp',
		group: help_group,
		icon: 'emailhelp-35x35.png',
		permission_result: true,
		permission: true
	} );

	var whats_new = new RibbonSubMenu( {
		label: $.i18n._( "What's New" ),
		id: 'WhatsNew',
		group: help_group,
		icon: 'whats_new-35x35.png',
		permission_result: true,
		permission: true
	} );

	var about = new RibbonSubMenu( {
		label: $.i18n._( 'About' ),
		id: 'About',
		group: help_group,
		icon: 'about-35x35.png',
		permission_result: true,
		permission: true
	} );

	//Reports

	var report_menu = new RibbonMenu( {
		label: $.i18n._( 'Report' ),
		id: 'reportMenu',
		sub_menu_groups: []
	} );

	var report_group = new RibbonSubMenuGroup( {
		label: $.i18n._( 'Reports' ),
		id: 'reportGroup',
		ribbon_menu: report_menu,
		sub_menus: []
	} );

	var employee_report = new RibbonSubMenu( {
		label: $.i18n._( 'Employee<br>Reports' ),
		id: 'EmployeeReports',
		group: report_group,
		icon: 'employee_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'ActiveShiftReport' ) ) {
		var whos_in_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Whos In Summary' ),
			id: 'ActiveShiftReport',
			nav: employee_report
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'UserSummaryReport' ) ) {
		var employee_information = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Employee Information' ),
			id: 'UserSummaryReport',
			nav: employee_report
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'AuditTrailReport' ) ) {
		var audit_trail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Audit Trail' ),
			id: 'AuditTrailReport',
			nav: employee_report
		} );
	}

	var timesheet_reports = new RibbonSubMenu( {
		label: $.i18n._( 'TimeSheet<br>Reports' ),
		id: 'TimeSheetReports',
		group: report_group,
		icon: 'timesheet_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'ScheduleSummaryReport' ) ) {
		var schedule_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Schedule Summary' ),
			id: 'ScheduleSummaryReport',
			nav: timesheet_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'TimesheetSummaryReport' ) ) {
		var timesheet_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Timesheet Summary' ),
			id: 'TimesheetSummaryReport',
			nav: timesheet_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'TimesheetDetailReport' ) ) {
		var timesheet_detail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'TimeSheet Detail' ),
			id: 'TimesheetDetailReport',
			nav: timesheet_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'PunchSummaryReport' ) ) {
		var punch_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Punch Summary' ),
			id: 'PunchSummaryReport',
			nav: timesheet_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'AccrualBalanceSummaryReport' ) ) {
		var accrual_balance_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Accrual Balance Summary' ),
			id: 'AccrualBalanceSummaryReport',
			nav: timesheet_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'ExceptionSummaryReport' ) ) {
		var exception_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Exception Summary' ),
			id: 'ExceptionSummaryReport',
			nav: timesheet_reports
		} );
	}

	var payroll_reports = new RibbonSubMenu( {
		label: $.i18n._( 'Payroll<br>Reports' ),
		id: 'PayrollReports',
		group: report_group,
		icon: 'payroll_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'PayStubSummaryReport' ) ) {
		var pay_stub_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Pay Stub Summary' ),
			id: 'PayStubSummaryReport',
			nav: payroll_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'PayrollExportReport' ) ) {
		var payroll_export = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Payroll Export' ),
			id: 'PayrollExportReport',
			nav: payroll_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'GeneralLedgerSummaryReport' ) ) {
		var general_ledger_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'General Ledger Summary' ),
			id: 'GeneralLedgerSummaryReport',
			nav: payroll_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'ExpenseSummaryReport' ) ) {
		var expense_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Expense Summary' ),
			id: 'ExpenseSummaryReport',
			nav: payroll_reports
		} );
	}

	var job_tracking_reports = new RibbonSubMenu( {
		label: $.i18n._( 'Job Tracking<br>Reports' ),
		id: 'JobTrackingReports',
		group: report_group,
		icon: 'job_tracking_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'JobSummaryReport' ) ) {
		var job_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Job Summary' ),
			id: 'JobSummaryReport',
			nav: job_tracking_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'JobAnalysisReport' ) ) {
		var job_analysis = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Job Analysis' ),
			id: 'JobAnalysisReport',
			nav: job_tracking_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'JobInformationReport' ) ) {
		var job_info = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Job Information' ),
			id: 'JobInformationReport',
			nav: job_tracking_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'JobItemInformationReport' ) ) {
		var job_item_info = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Task Information' ),
			id: 'JobItemInformationReport',
			nav: job_tracking_reports
		} );
	}

	var invoice_reports = new RibbonSubMenu( {
		label: $.i18n._( 'Invoice<br>Reports' ),
		id: 'InvoiceReports',
		group: report_group,
		icon: 'invoice_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'InvoiceTransactionSummaryReport' ) ) {
		var invoice_transaction_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Transaction Summary' ),
			id: 'InvoiceTransactionSummaryReport',
			nav: invoice_reports
		} );
	}

	var tax_reports = new RibbonSubMenu( {
		label: $.i18n._( 'Tax<br>Reports' ),
		id: 'TaxReports',
		group: report_group,
		icon: 'tax_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'RemittanceSummaryReport' ) ) {
		var remittance_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Remittance Summary' ),
			id: 'RemittanceSummaryReport',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'T4SummaryReport' ) ) {
		var t4_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'T4 Summary' ),
			id: 'T4SummaryReport',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'T4ASummaryReport' ) ) {
		var t4a_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'T4A Summary' ),
			id: 'T4ASummaryReport',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'TaxSummaryReport' ) ) {
		var tax_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Tax Summary(Generic)' ),
			id: 'TaxSummaryReport',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'Form940Report' ) ) {
		var form_940 = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Form 940' ),
			id: 'Form940Report',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'Form941Report' ) ) {
		var form_941 = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Form 941' ),
			id: 'Form941Report',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'Form1099MiscReport' ) ) {
		var form_1099 = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Form 1099-Misc' ),
			id: 'Form1099MiscReport',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'FormW2Report' ) ) {
		var form_w2 = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Form W2/W3' ),
			id: 'FormW2Report',
			nav: tax_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'AffordableCareReport' ) ) {
		var affordable_care = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Affordable Care' ),
			id: 'AffordableCareReport',
			nav: tax_reports
		} );
	}

	var hr_reports = new RibbonSubMenu( {
		label: $.i18n._( 'HR<br>Reports' ),
		id: 'HRReports',
		group: report_group,
		icon: 'hr_reports-35x35.png',
		type: RibbonSubMenuType.NAVIGATION,
		items: [],
		permission_result: true,
		permission: true
	} );

	if ( PermissionManager.checkTopLevelPermission( 'UserQualificationReport' ) ) {
		var qualification_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Qualification Summary' ),
			id: 'UserQualificationReport',
			nav: hr_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'KPIReport' ) ) {
		var review_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Review Summary' ),
			id: 'KPIReport',
			nav: hr_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'UserRecruitmentSummaryReport' ) ) {
		var recruitment_summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Recruitment Summary' ),
			id: 'UserRecruitmentSummaryReport',
			nav: hr_reports
		} );
	}

	if ( PermissionManager.checkTopLevelPermission( 'UserRecruitmentDetailReport' ) ) {
		var recruitment_detail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Recruitment Detail' ),
			id: 'UserRecruitmentDetailReport',
			nav: hr_reports
		} );
	}

	var saved_report = new RibbonSubMenu( {
		label: $.i18n._( 'Saved<br>Reports' ),
		id: 'SavedReport',
		group: report_group,
		icon: 'saved_reports-35x35.png',
		permission_result: PermissionManager.checkTopLevelPermission( 'SavedReport' ),
		permission: permission.report
	} );

	TopMenuManager.ribbon_menus = [attendance_menu, employee_menu, company_menu, payroll_menu, policy_menu, invoice_menu, hr_menu, report_menu, my_account_menu, help_menu];

};
