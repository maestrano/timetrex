var APIExceptionSummaryReport = ServiceCaller.extend( {

	key_name: 'ExceptionReport',
	className: 'APIExceptionReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonExceptionReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonExceptionReportData', arguments );

	},

	getExceptionReport: function() {

		return this.argumentsHandler( this.className, 'getExceptionReport', arguments );

	},

	setExceptionReport: function() {

		return this.argumentsHandler( this.className, 'setExceptionReport', arguments );

	},

	getExceptionReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getExceptionReportDefaultData', arguments );

	},

	deleteExceptionReport: function() {

		return this.argumentsHandler( this.className, 'deleteExceptionReport', arguments );

	},

	validateExceptionReport: function() {

		return this.argumentsHandler( this.className, 'validateExceptionReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );