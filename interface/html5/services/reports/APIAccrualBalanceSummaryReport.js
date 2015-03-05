var APIAccrualBalanceSummaryReport = ServiceCaller.extend( {

	key_name: 'AccrualBalanceSummaryReport',
	className: 'APIAccrualBalanceSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonAccrualBalanceSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualBalanceSummaryReportData', arguments );

	},

	getAccrualBalanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getAccrualBalanceSummaryReport', arguments );

	},

	setAccrualBalanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setAccrualBalanceSummaryReport', arguments );

	},

	getAccrualBalanceSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualBalanceSummaryReportDefaultData', arguments );

	},

	deleteAccrualBalanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteAccrualBalanceSummaryReport', arguments );

	},

	validateAccrualBalanceSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateAccrualBalanceSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );