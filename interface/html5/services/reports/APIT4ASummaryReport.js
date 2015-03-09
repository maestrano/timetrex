var APIT4ASummaryReport = ServiceCaller.extend( {

	key_name: 'T4ASummaryReport',
	className: 'APIT4ASummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonT4ASummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonT4ASummaryReportData', arguments );

	},

	getT4ASummaryReport: function() {

		return this.argumentsHandler( this.className, 'getT4ASummaryReport', arguments );

	},

	setT4ASummaryReport: function() {

		return this.argumentsHandler( this.className, 'setT4ASummaryReport', arguments );

	},

	getT4ASummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getT4ASummaryReportDefaultData', arguments );

	},

	deleteT4ASummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteT4ASummaryReport', arguments );

	},

	validateT4ASummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateT4ASummaryReport', arguments );

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