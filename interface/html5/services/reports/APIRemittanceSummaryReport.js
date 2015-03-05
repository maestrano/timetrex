var APIRemittanceSummaryReport = ServiceCaller.extend( {

	key_name: 'RemittanceSummaryReport',
	className: 'APIRemittanceSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonRemittanceSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonRemittanceSummaryReportData', arguments );

	},

	getRemittanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getRemittanceSummaryReport', arguments );

	},

	setRemittanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setRemittanceSummaryReport', arguments );

	},

	getRemittanceSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRemittanceSummaryReportDefaultData', arguments );

	},

	deleteRemittanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteRemittanceSummaryReport', arguments );

	},

	validateRemittanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateRemittanceSummaryReport', arguments );

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