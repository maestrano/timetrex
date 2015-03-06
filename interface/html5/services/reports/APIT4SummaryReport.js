var APIT4SummaryReport = ServiceCaller.extend( {

	key_name: 'T4SummaryReport',
	className: 'APIT4SummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonT4SummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonT4SummaryReportData', arguments );

	},

	getT4SummaryReport: function() {

		return this.argumentsHandler( this.className, 'getT4SummaryReport', arguments );

	},

	setT4SummaryReport: function() {

		return this.argumentsHandler( this.className, 'setT4SummaryReport', arguments );

	},

	getT4SummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getT4SummaryReportDefaultData', arguments );

	},

	deleteT4SummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteT4SummaryReport', arguments );

	},

	validateT4SummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateT4SummaryReport', arguments );

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