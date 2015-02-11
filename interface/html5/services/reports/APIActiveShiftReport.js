var APIActiveShiftReport = ServiceCaller.extend( {

	key_name: 'ActiveShiftReport',
	className: 'APIActiveShiftReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonActiveShiftReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonActiveShiftReportData', arguments );

	},

	getActiveShiftReport: function() {

		return this.argumentsHandler( this.className, 'getActiveShiftReport', arguments );

	},

	setActiveShiftReport: function() {

		return this.argumentsHandler( this.className, 'setActiveShiftReport', arguments );

	},

	getActiveShiftReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getActiveShiftReportDefaultData', arguments );

	},

	deleteActiveShiftReport: function() {

		return this.argumentsHandler( this.className, 'deleteActiveShiftReport', arguments );

	},

	validateActiveShiftReport: function() {

		return this.argumentsHandler( this.className, 'validateActiveShiftReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );