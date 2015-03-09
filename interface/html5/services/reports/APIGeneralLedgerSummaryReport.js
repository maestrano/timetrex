var APIGeneralLedgerSummaryReport = ServiceCaller.extend( {

	key_name: 'GeneralLedgerSummaryReport',
	className: 'APIGeneralLedgerSummaryReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonGeneralLedgerSummaryReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonGeneralLedgerSummaryReportData', arguments );

	},

	getGeneralLedgerSummaryReport: function() {

		return this.argumentsHandler( this.className, 'getGeneralLedgerSummaryReport', arguments );

	},

	setGeneralLedgerSummaryReport: function() {

		return this.argumentsHandler( this.className, 'setGeneralLedgerSummaryReport', arguments );

	},

	getGeneralLedgerSummaryReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getGeneralLedgerSummaryReportDefaultData', arguments );

	},

	deleteGeneralLedgerSummaryReport: function() {

		return this.argumentsHandler( this.className, 'deleteGeneralLedgerSummaryReport', arguments );

	},

	validateGeneralLedgerSummaryReport: function() {

		return this.argumentsHandler( this.className, 'validateGeneralLedgerSummaryReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );