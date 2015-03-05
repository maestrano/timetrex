var APIPayStubSummaryReport = ServiceCaller.extend( {

	key_name: 'PayStubSummaryReport',
	className: 'APIPayStubSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonPayStubSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubSummaryReportData', arguments );

	},

	getPayStubSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getPayStubSummaryReport', arguments );

	},

	setPayStubSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setPayStubSummaryReport', arguments );

	},

	getPayStubSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubSummaryReportDefaultData', arguments );

	},

	deletePayStubSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deletePayStubSummaryReport', arguments );

	},

	validatePayStubSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validatePayStubSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );