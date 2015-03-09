var APITimesheetDetailReport = ServiceCaller.extend( {

	key_name: 'TimesheetDetailReport',
	className: 'APITimesheetDetailReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonTimesheetDetailReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonTimesheetDetailReportData', arguments );

	},

	getTimesheetDetailReport: function() {

		return this.argumentsHandler( this.className, 'getTimesheetDetailReport', arguments );

	},

	setTimesheetDetailReport: function() {

		return this.argumentsHandler( this.className, 'setTimesheetDetailReport', arguments );

	},

	getTimesheetDetailReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getTimesheetDetailReportDefaultData', arguments );

	},

	deleteTimesheetDetailReport: function() {

		return this.argumentsHandler( this.className, 'deleteTimesheetDetailReport', arguments );

	},

	validateTimesheetDetailReport: function() {

		return this.argumentsHandler( this.className, 'validateTimesheetDetailReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );