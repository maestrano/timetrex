var APITaxSummaryReport = ServiceCaller.extend( {

	key_name: 'TaxSummaryReport',
	className: 'APITaxSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonTaxSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonTaxSummaryReportData', arguments );

	},

	getTaxSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getTaxSummaryReport', arguments );

	},

	setTaxSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setTaxSummaryReport', arguments );

	},

	getTaxSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getTaxSummaryReportDefaultData', arguments );

	},

	deleteTaxSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteTaxSummaryReport', arguments );

	},

	validateTaxSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateTaxSummaryReport', arguments );

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