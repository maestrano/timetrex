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
			{label: $.i18n._( 'Name' ), value: 'name'}
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
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Status' ), value: 'status'}
	];

	default_columns[ALayoutIDs.JOB] = [
		{label: $.i18n._( 'Name' ), value: 'name'},
		{label: $.i18n._( 'Code' ), value: 'manual_id'}
	];

	default_columns[ALayoutIDs.JOB_ITEM] = [
		{label: $.i18n._( 'Name' ), value: 'name'},
		{label: $.i18n._( 'Code' ), value: 'manual_id'}
	];

	default_columns[ALayoutIDs.CLIENT_PAYMENT] = [
		{label: $.i18n._( 'Number' ), value: 'display_number'},
		{label: $.i18n._( 'Type' ), value: 'type'}
	];

	default_columns[ALayoutIDs.OPTION_COLUMN] = [
		{label: $.i18n._( 'Name' ), value: 'label'}
	];
	default_columns[ALayoutIDs.ABSENCE] = [
		{label: $.i18n._( 'Name' ), value: 'name'},
		{label: $.i18n._( 'Date' ), value: 'date_stamp'}
	];
	default_columns[ALayoutIDs.TIMESHEET] = [
		{label: $.i18n._( 'Time' ), value: 'punch_time'},
		{label: $.i18n._( 'In/Out' ), value: 'status'},
		{label: $.i18n._( 'Punch Type' ), value: 'type'}

	];
	default_columns[ALayoutIDs.USER] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Status' ), value: 'status'}
	];

	default_columns[ALayoutIDs.MESSAGE_USER] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Status' ), value: 'status'}
	];

	default_columns[ALayoutIDs.USER_CONTACT] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'}
	];
	default_columns[ALayoutIDs.WAGE] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Type' ), value: 'type'},
		{label: $.i18n._( 'Wage' ), value: 'wage'}
	];
	default_columns[ALayoutIDs.LOG] = [
		{label: $.i18n._( 'Date' ), value: 'date'},
		{label: $.i18n._( 'Action' ), value: 'action'},
		{label: $.i18n._( 'Object' ), value: 'object'}
	];
	default_columns[ALayoutIDs.BANK_ACCOUNT] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Account Name' ), value: 'account'}
	];
	default_columns[ALayoutIDs.TREE_COLUMN] = [
		{label: $.i18n._( '' ), value: 'id'},
		{label: $.i18n._( 'Name' ), value: 'name'}
	];

	default_columns[ALayoutIDs.SORT_COLUMN] = [
		{label: $.i18n._( 'Column Name' ), value: 'label'},
		{label: $.i18n._( 'Sort' ), value: 'sort'}
	];
	default_columns[ALayoutIDs.PAY_PERIOD] = [
		{label: $.i18n._( 'Start Date' ), value: 'start_date'},
		{label: $.i18n._( 'End Date' ), value: 'end_date'},
		{label: $.i18n._( 'Pay Period Schedule' ), value: 'pay_period_schedule'}

	];

	default_columns[ALayoutIDs.USER_SKILL] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Skill' ), value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_Education] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Course' ), value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_Membership] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Membership' ), value: 'qualification'}

	];

	default_columns[ALayoutIDs.USER_LICENSE] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'License' ), value: 'qualification'}

	];

	default_columns[ALayoutIDs.JOB_APPLICANT] = [
		{label: $.i18n._( 'First Name' ), value: 'first_name'},
		{label: $.i18n._( 'Last Name' ), value: 'last_name'},
		{label: $.i18n._( 'Status' ), value: 'status'}

	];

	default_columns[ALayoutIDs.JOB_APPLICATION] = [
		{label: $.i18n._( 'Job Vacancy' ), value: 'job_vacancy'},
		{label: $.i18n._( 'Type' ), value: 'type'},
		{label: $.i18n._( 'Status' ), value: 'status'}

	];

	default_columns[ALayoutIDs.KPI] = [
		{label: $.i18n._( 'Name' ), value: 'name'},
		{label: $.i18n._( 'Type' ), value: 'type'},
		{label: $.i18n._( 'Status' ), value: 'status'}

	];

	default_columns[ALayoutIDs.KPI_REVIEW_CONTROL] = [
		{label: $.i18n._( 'Employee Name' ), value: 'user'},
		{label: $.i18n._( 'Reviewer Name' ), value: 'reviewer_user'},
		{label: $.i18n._( 'Start Date' ), value: 'start_date'},
		{label: $.i18n._( 'End Date' ), value: 'end_date'}

	];

	default_columns[ALayoutIDs.INVOICE] = [
		{label: $.i18n._( 'Client' ), value: 'client'},
		{label: $.i18n._( 'Status' ), value: 'status'}

	];

	default_columns[ALayoutIDs.CLIENT] = [
		{label: $.i18n._( 'Company Name' ), value: 'company_name'},
		{label: $.i18n._( 'Groups' ), value: 'group'}

	];

	default_columns[ALayoutIDs.INVOICE_TRANSACTION] = [
		{label: $.i18n._( 'Client' ), value: 'client'},
		{label: $.i18n._( 'Status' ), value: 'status'}

	];

	default_columns[ALayoutIDs.PAY_STUB_ACCOUNT] = [
		{label: $.i18n._( 'Name' ), value: 'name'},
		{label: $.i18n._( 'Type' ), value: 'type'}
	];

	return default_columns;
}