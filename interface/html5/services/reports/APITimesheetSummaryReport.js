var APITimesheetSummaryReport = ServiceCaller.extend( {

	key_name: 'TimesheetSummaryReport',
	className: 'APITimesheetSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonTimesheetSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonTimesheetSummaryReportData', arguments );

	},

	getTimesheetSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getTimesheetSummaryReport', arguments );

	},

	setTimesheetSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setTimesheetSummaryReport', arguments );

	},

	getTimesheetSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getTimesheetSummaryReportDefaultData', arguments );

	},

	deleteTimesheetSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteTimesheetSummaryReport', arguments );

	},

	validateTimesheetSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateTimesheetSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );