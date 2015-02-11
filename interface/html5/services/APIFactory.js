var APIFactory = (function() {

	var api_dic = {};

	var api_path_map = {

		'APICurrency': 'services/core/APICurrency',
		'APICurrencyRate': 'services/core/APICurrencyRate',
		'APIUserPreference': 'services/users/APIUserPreference',
		'APIDate': 'services/APIDate',
		'APIPermission': 'services/core/APIPermission',
		'APIUserGenericData': 'services/users/APIUserGenericData',
		'APIUser': 'services/users/APIUser',
		'APIUserGroup': 'services/users/APIUserGroup',
		'APIBranch': 'services/company/APIBranch',
		'APIDepartment': 'services/department/APIDepartment',
		'APICompany': 'services/company/APICompany',
		'APIHierarchyLevel': 'services/company/APIHierarchyLevel',
		'APIUserTitle': 'services/users/APIUserTitle',
		'APIAbout': 'services/help/APIAbout',
		'APIRoundingIntervalPolicy': 'services/policy/APIRoundingIntervalPolicy',
		'APIPermissionControl': 'services/core/APIPermissionControl',
		'APIPayPeriodSchedule': 'services/payperiod/APIPayPeriodSchedule',
		'APIPolicyGroup': 'services/policy/APIPolicyGroup',
		'APIExceptionPolicy': 'services/policy/APIExceptionPolicy',
		'APIExceptionPolicyControl': 'services/policy/APIExceptionPolicyControl',
		'APILog': 'services/core/APILog',
		'APIHierarchyControl': 'services/hierarchy/APIHierarchyControl',
		'APIUserWage': 'services/users/APIUserWage',
		'APIUserDeduction': 'services/users/APIUserDeduction',
		'APIWageGroup': 'services/company/APIWageGroup',
		'APIPunch': 'services/core/APIPunch',
		'APITimeSheet': 'services/core/APITimeSheet',
		'APIJob': 'services/job/APIJob',
		'APIJobGroup': 'services/job/APIJobGroup',
		'APIJobItem': 'services/job_item/APIJobItem',
		'APIJobItemAmendment': 'services/job_item_amendment/APIJobItemAmendment',
		'APIJobItemGroup': 'services/job_item/APIJobItemGroup',
		'APIUserContact': 'services/users/APIUserContact',
		'APIEthnicGroup': 'services/users/APIEthnicGroup',
		'APIBankAccount': 'services/users/APIBankAccount',
		'APIUserDefault': 'services/users/APIUserDefault',
		'APICompanyDeduction': 'services/company/APICompanyDeduction',
		'APIAbsencePolicy': 'services/policy/APIAbsencePolicy',
		'APIExpensePolicy': 'services/policy/APIExpensePolicy',
		'APIUserDateTotal': 'services/core/APIUserDateTotal',
		'APIPunchControl': 'services/core/APIPunchControl',
		'APIROE': 'services/users/APIROE',
		'APIClient': 'services/invoice/APIClient',
		'APIClientGroup': 'services/invoice/APIClientGroup',
		'APIClientContact': 'services/invoice/APIClientContact',
		'APIClientPayment': 'services/invoice/APIClientPayment',
		'APITaxPolicy': 'services/invoice/APITaxPolicy',
		'APIShippingPolicy': 'services/invoice/APIShippingPolicy',
		'APIPaymentGateway': 'services/invoice/APIPaymentGateway',
		'APIInvoiceConfig': 'services/invoice/APIInvoiceConfig',
		'APIInvoiceDistrict': 'services/invoice/APIInvoiceDistrict',
		'APIAreaPolicy': 'services/invoice/APIAreaPolicy',
		'APIPayPeriod': 'services/payroll/APIPayPeriod',
		'APISchedule': 'services/attendance/APISchedule',
		'APIScheduleAdvanced': 'services/attendance/APIScheduleAdvanced',
		'APIRecurringScheduleTemplate': 'services/attendance/APIRecurringScheduleTemplate',
		'APIRecurringScheduleTemplateControl': 'services/attendance/APIRecurringScheduleTemplateControl',
		'APIOtherField': 'services/core/APIOtherField',
		'APIStation': 'services/company/APIStation',
		'APIPayStub': 'services/payroll/APIPayStub',
		'APIPayStubEntry': 'services/payroll/APIPayStubEntry',
		'APIPayStubAmendment': 'services/payroll/APIPayStubAmendment',
		'APIRecurringPayStubAmendment': 'services/payroll/APIRecurringPayStubAmendment',
		'APIPayStubEntryAccount': 'services/payroll/APIPayStubEntryAccount',
		'APISchedulePolicy': 'services/policy/APISchedulePolicy',
		'APIUserExpense': 'services/payroll/APIUserExpense',
		'APIMealPolicy': 'services/policy/APIMealPolicy',
		'APIBreakPolicy': 'services/policy/APIBreakPolicy',
		'APIPayCode': 'services/policy/APIPayCode',
		'APIPayFormulaPolicy': 'services/policy/APIPayFormulaPolicy',
		'APIContributingPayCodePolicy': 'services/policy/APIContributingPayCodePolicy',
		'APIContributingShiftPolicy': 'services/policy/APIContributingShiftPolicy',
		'APIRegularTimePolicy': 'services/policy/APIRegularTimePolicy',
		'APIOvertimePolicy': 'services/policy/APIOvertimePolicy',
		'APIAccrualPolicyAccount': 'services/policy/APIAccrualPolicyAccount',
		'APIAccrualPolicy': 'services/policy/APIAccrualPolicy',
		'APIAccrualPolicyUserModifier': 'services/policy/APIAccrualPolicyUserModifier',
		'APIRecurringHoliday': 'services/policy/APIRecurringHoliday',
		'APIHolidayPolicy': 'services/policy/APIHolidayPolicy',
		'APIHoliday': 'services/policy/APIHoliday',
		'APIDocument': 'services/document/APIDocument',
		'APITransaction': 'services/invoice/APITransaction',
		'APIProduct': 'services/invoice/APIProduct',
		'APIProductPrice': 'services/invoice/APIProductPrice',
		'APIDocumentRevision': 'services/document/APIDocumentRevision',
		'APIDocumentGroup': 'services/document/APIDocumentGroup',
		'APIPremiumPolicy': 'services/policy/APIPremiumPolicy',
		'APIAccrualPolicyMilestone': 'services/policy/APIAccrualPolicyMilestone',
		'APIUserGenericStatus': 'services/users/APIUserGenericStatus',
		'APIRecurringScheduleControl': 'services/attendance/APIRecurringScheduleControl',

		'APIActiveShiftReport': 'services/reports/APIActiveShiftReport',
		'APIUserReportData': 'services/reports/APIUserReportData',
		'APIReportSchedule': 'services/reports/APIReportSchedule',
		'APIReportCustomColumn': 'services/reports/APIReportCustomColumn',
		'APIUserSummaryReport': 'services/reports/APIUserSummaryReport',
		'APIAuditTrailReport': 'services/reports/APIAuditTrailReport',
		'APITimesheetDetailReport': 'services/reports/APITimesheetDetailReport',
		'APIPunchSummaryReport': 'services/reports/APIPunchSummaryReport',
		'APIAccrualBalanceSummaryReport': 'services/reports/APIAccrualBalanceSummaryReport',
		'APIAccrual': 'services/attendance/APIAccrual',
		'APIAccrualBalance': 'services/attendance/APIAccrualBalance',
		'APIScheduleSummaryReport': 'services/reports/APIScheduleSummaryReport',
		'APITimesheetSummaryReport': 'services/reports/APITimesheetSummaryReport',
		'APITimeSheetVerify': 'services/reports/APITimeSheetVerify',
		'APIExceptionSummaryReport': 'services/reports/APIExceptionSummaryReport',
		'APIPayStubSummaryReport': 'services/reports/APIPayStubSummaryReport',
		'APIGeneralLedgerSummaryReport': 'services/reports/APIGeneralLedgerSummaryReport',
		'APIUserExpenseReport': 'services/reports/APIUserExpenseReport',
		'APIJobSummaryReport': 'services/reports/APIJobSummaryReport',
		'APIJobDetailReport': 'services/reports/APIJobDetailReport',
		'APIJobInformationReport': 'services/reports/APIJobInformationReport',
		'APIJobItemInformationReport': 'services/reports/APIJobItemInformationReport',
		'APIInvoiceTransactionSummaryReport': 'services/reports/APIInvoiceTransactionSummaryReport',
		'APIInvoice': 'services/invoice/APIInvoice',
		'APIProductGroup': 'services/invoice/APIProductGroup',
		'APIRemittanceSummaryReport': 'services/reports/APIRemittanceSummaryReport',
		'APIT4SummaryReport': 'services/reports/APIT4SummaryReport',
		'APIT4ASummaryReport': 'services/reports/APIT4ASummaryReport',
		'APITaxSummaryReport': 'services/reports/APITaxSummaryReport',
		'APIForm940Report': 'services/reports/APIForm940Report',
		'APIForm941Report': 'services/reports/APIForm941Report',
		'APIForm1099MiscReport': 'services/reports/APIForm1099MiscReport',
		'APIFormW2Report': 'services/reports/APIFormW2Report',
		'APIAffordableCareReport': 'services/reports/APIAffordableCareReport',
		'APIUserQualificationReport': 'services/reports/APIUserQualificationReport',
		'APIQualificationGroup': 'services/hr/APIQualificationGroup',
		'APIQualification': 'services/hr/APIQualification',
		'APIUserSkill': 'services/hr/APIUserSkill',
		'APIRequest': 'services/my_account/APIRequest',
		'APIMessageControl': 'services/my_account/APIMessageControl',
		'APIPayPeriodTimeSheetVerify': 'services/my_account/APIPayPeriodTimeSheetVerify',
		'APIJobApplicantEmployment': 'services/hr/APIJobApplicantEmployment',
		'APIJobApplicantLanguage': 'services/hr/APIJobApplicantLanguage',
		'APIJobApplicantLicense': 'services/hr/APIJobApplicantLicense',
		'APIJobApplicantMembership': 'services/hr/APIJobApplicantMembership',
		'APIJobApplicantEducation': 'services/hr/APIJobApplicantEducation',
		'APIJobApplicantSkill': 'services/hr/APIJobApplicantSkill',
		'APIJobApplicantReference': 'services/hr/APIJobApplicantReference',
		'APIJobApplicantLocation': 'services/hr/APIJobApplicantLocation',
		'APIUserLicense': 'services/hr/APIUserLicense',
		'APIUserEducation': 'services/hr/APIUserEducation',
		'APIUserLanguage': 'services/hr/APIUserLanguage',
		'APIUserMembership': 'services/hr/APIUserMembership',
		'APIKPIReport': 'services/reports/APIKPIReport',
		'APIKPIGroup': 'services/hr/APIKPIGroup',
		'APIKPI': 'services/hr/APIKPI',
		'APIUserReview': 'services/hr/APIUserReview',
		'APIUserReviewControl': 'services/hr/APIUserReviewControl',
		'APIUserRecruitmentSummaryReport': 'services/reports/APIUserRecruitmentSummaryReport',
		'APIJobApplicant': 'services/hr/APIJobApplicant',
		'APIJobApplication': 'services/hr/APIJobApplication',
		'APIJobVacancy': 'services/hr/APIJobVacancy',
		'APIUserRecruitmentDetailReport': 'services/reports/APIUserRecruitmentDetailReport',
		'APIPayrollExportReport': 'services/reports/APIPayrollExportReport',
		'APIImport': 'services/core/APIImport',
		'APIAuthorization': 'services/core/APIAuthorization',
		'APIAuthentication': 'services/core/APIAuthentication',
		'APICurrentUser': 'services/APICurrentUser',
		'APIRoundIntervalPolicy': 'services/policy/APIRoundIntervalPolicy',
		'APIException': 'services/attendance/APIException',
		'APIDocumentAttachment': 'services/document/APIDocumentAttachment',
		'APINotification': 'services/core/APINotification',
		'APIMisc': 'services/core/APIMisc',
		'APICompanyGenericTag': 'services/company/APICompanyGenericTag',
		'APIInstall': 'services/install/APIInstall'


	};

	var getAPIClass = function( apiName ) {

		var api_class = api_dic[apiName];
		var path;

		if ( !api_class ) {
			path = api_path_map[apiName] + '.js';
		}

		Global.loadScript( path );

		try {
			/* jshint ignore:start */
			api_class = eval( apiName + ';' );
			/* jshint ignore:end */
		} catch ( e ) {
			api_class = APIAuthentication;
		}

		return api_class;
	};

	return {
		getAPIClass: getAPIClass

	};

})();

