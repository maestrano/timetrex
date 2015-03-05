var APITimeSheetVerify = ServiceCaller.extend( {

	key_name: 'TimeSheetVerify',
	className: 'APIPayPeriodTimeSheetVerify',

	getTimeSheetVerifyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getTimeSheetVerifyDefaultData', arguments );

	},

	getTimeSheetVerify: function() {

		return this.argumentsHandler( this.className, 'getTimeSheetVerify', arguments );

	},
	getCommonTimeSheetVerifyData: function() {

		return this.argumentsHandler( this.className, 'getCommonTimeSheetVerifyData', arguments );

	},
	validateTimeSheetVerify: function() {

		return this.argumentsHandler( this.className, 'validateTimeSheetVerify', arguments );

	},
	setTimeSheetVerify: function() {

		return this.argumentsHandler( this.className, 'setTimeSheetVerify', arguments );

	},
	deleteTimeSheetVerify: function() {

		return this.argumentsHandler( this.className, 'deleteTimeSheetVerify', arguments );

	},
	importData: function() {

		return this.argumentsHandler( this.className, 'importData', arguments );

	},
	deleteData: function() {

		return this.argumentsHandler( this.className, 'deleteData', arguments );

	}


} );