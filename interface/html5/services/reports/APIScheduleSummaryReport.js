var APIScheduleSummaryReport = ServiceCaller.extend( {

	key_name: 'ScheduleSummaryReport',
	className: 'APIScheduleSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonScheduleSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonScheduleSummaryReportData', arguments );

	},

	getScheduleSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getScheduleSummaryReport', arguments );

	},

	setScheduleSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setScheduleSummaryReport', arguments );

	},

	getScheduleSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getScheduleSummaryReportDefaultData', arguments );

	},

	deleteScheduleSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteScheduleSummaryReport', arguments );

	},

	validateScheduleSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateScheduleSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );