var APIPayrollExportReport = ServiceCaller.extend( {

	key_name: 'PayrollExportReport',
	className: 'APIPayrollExportReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonPayrollExportReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayrollExportReportData', arguments );

	},

	getPayrollExportReport: function() {

		return this.argumentsHandler( this.className, 'getPayrollExportReport', arguments );

	},

	setPayrollExportReport: function() {

		return this.argumentsHandler( this.className, 'setPayrollExportReport', arguments );

	},

	getPayrollExportReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayrollExportReportDefaultData', arguments );

	},

	deletePayrollExportReport: function() {

		return this.argumentsHandler( this.className, 'deletePayrollExportReport', arguments );

	},

	validatePayrollExportReport: function() {

		return this.argumentsHandler( this.className, 'validatePayrollExportReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	},

	setCompanyFormConfig: function() {

		return this.argumentsHandler( this.className, 'setCompanyFormConfig', arguments );

	},

	getCompanyFormConfig: function() {

		return this.argumentsHandler( this.className, 'getCompanyFormConfig', arguments );

	}



} );