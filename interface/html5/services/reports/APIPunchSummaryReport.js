var APIPunchSummaryReport = ServiceCaller.extend( {

	key_name: 'PunchSummaryReport',
	className: 'APIPunchSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonPunchSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonPunchSummaryReportData', arguments );

	},

	getPunchSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getPunchSummaryReport', arguments );

	},

	setPunchSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setPunchSummaryReport', arguments );

	},

	getPunchSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPunchSummaryReportDefaultData', arguments );

	},

	deletePunchSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deletePunchSummaryReport', arguments );

	},

	validatePunchSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validatePunchSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );