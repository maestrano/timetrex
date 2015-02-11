var APIUserSummaryReport = ServiceCaller.extend( {

	key_name: 'UserSummaryReport',
	className: 'APIUserSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonUserSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserSummaryReportData', arguments );

	},

	getUserSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getUserSummaryReport', arguments );

	},

	setUserSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setUserSummaryReport', arguments );

	},

	getUserSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserSummaryReportDefaultData', arguments );

	},

	deleteUserSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteUserSummaryReport', arguments );

	},

	validateUserSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateUserSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );