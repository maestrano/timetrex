var ALayoutCache = function() {

}

ALayoutCache.layout_dic = {};

ALayoutCache.default_columns = null;

ALayoutCache.getDefaultColumn = function( layout_name ) {
	if ( !ALayoutCache.default_columns ) {
		ALayoutCache.default_columns = ALayoutCache.buildDefaultColumns();
	}

	if ( !Global.isSet( ALayoutCache.default_columns[layout_name] ) ) {
		return [
			{label: 'Name', value: 'name'}
		]; //Default Column setting
	}

	return ALayoutCache.default_columns[layout_name];

}

ALayoutCache.buildDefaultColumns = function() {
	var default_columns = {};

//	  default_columns[ALayoutIDs.BRANCH] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns[ALayoutIDs.DEPARTMENT] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns[ALayoutIDs.JOB_TITLE] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns[ALayoutIDs.PERMISSION_CONTROL] = [new ViewColumn({label:'Name',value:'name'})];

	default_columns[ALayoutIDs.CLIENT_CONTACT] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Status', value: 'status'}
	];

	default_columns[ALayoutIDs.JOB] = [
		{label: 'Name', value: 'name'},
		{label: 'Code', value: 'manual_id'}
	];

	default_columns[ALayoutIDs.JOB_ITEM] = [
		{label: 'Name', value: 'name'},
		{label: 'Code', value: 'manual_id'}
	];

	default_columns[ALayoutIDs.CLIENT_PAYMENT] = [
		{label: 'Number', value: 'display_number'},
		{label: 'Type', value: 'type'}
	];

	default_columns[ALayoutIDs.OPTION_COLUMN] = [
		{label: 'Name', value: 'label'}
	];
	default_columns[ALayoutIDs.ABSENCE] = [
		{label: 'Name', value: 'name'},
		{label: 'Date', value: 'date_stamp'}
	];
	default_columns[ALayoutIDs.TIMESHEET] = [
		{label: 'Time', value: 'punch_time'},
		{label: 'In/Out', value: 'status'},
		{label: 'Punch Type', value: 'type'}

	];
	default_columns[ALayoutIDs.USER] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Status', value: 'status'}
	];

	default_columns[ALayoutIDs.MESSAGE_USER] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Status', value: 'status'}
	];

	default_columns[ALayoutIDs.USER_CONTACT] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'}
	];
	default_columns[ALayoutIDs.WAGE] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Type', value: 'type'},
		{label: 'Wage', value: 'wage'}
	];
	default_columns[ALayoutIDs.LOG] = [
		{label: 'Date', value: 'date'},
		{label: 'Action', value: 'action'},
		{label: 'Object', value: 'object'}
	];
	default_columns[ALayoutIDs.BANK_ACCOUNT] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Account Name', value: 'account'}
	];
	default_columns[ALayoutIDs.TREE_COLUMN] = [
		{label: '', value: 'id'},
		{label: 'Name', value: 'name'}
	];

	default_columns[ALayoutIDs.SORT_COLUMN] = [
		{label: 'Column Name', value: 'label'},
		{label: 'Sort', value: 'sort'}
	];
	default_columns[ALayoutIDs.PAY_PERIOD] = [
		{label: 'Start Date', value: 'start_date'},
		{label: 'End Date', value: 'end_date'},
		{label: 'Pay Period Schedule', value: 'pay_period_schedule'}

	];

	default_columns[ALayoutIDs.USER_SKILL] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Skill', value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_Education] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Course', value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_Membership] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Membership', value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_LICENSE] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'License', value: 'qualification'}

	];

	default_columns[ALayoutIDs.JOB_APPLICANT] = [
		{label: 'First Name', value: 'first_name'},
		{label: 'Last Name', value: 'last_name'},
		{label: 'Status', value: 'status'}

	];

	default_columns[ALayoutIDs.JOB_APPLICATION] = [
		{label: 'Job Vacancy', value: 'job_vacancy'},
		{label: 'Type', value: 'type'},
		{label: 'Status', value: 'status'}

	];

	default_columns[ALayoutIDs.KPI] = [
		{label: 'Name', value: 'name'},
		{label: 'Type', value: 'type'},
		{label: 'Status', value: 'status'}

	];

	default_columns[ALayoutIDs.KPI_REVIEW_CONTROL] = [
		{label: 'Employee Name', value: 'user'},
		{label: 'Reviewer Name', value: 'reviewer_user'},
		{label: 'Start Date', value: 'start_date'},
		{label: 'End Date', value: 'end_date'}

	];

	default_columns[ALayoutIDs.INVOICE] = [
		{label: 'Client', value: 'client'},
		{label: 'Status', value: 'status'}

	];

	default_columns[ALayoutIDs.CLIENT] = [
		{label: 'Company Name', value: 'company_name'},
		{label: 'Groups', value: 'group'}

	];

	default_columns[ALayoutIDs.INVOICE_TRANSACTION] = [
		{label: 'Client', value: 'client'},
		{label: 'Status', value: 'status'}

	];

	return default_columns;
}