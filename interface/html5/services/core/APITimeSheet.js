var APITimeSheet = ServiceCaller.extend( {

	key_name: 'TimeSheet',
	className: 'APITimeSheet',

	reCalculateTimeSheet: function() {

		return this.argumentsHandler( this.className, 'reCalculateTimeSheet', arguments );

	},

	verifyTimeSheet: function() {

		return this.argumentsHandler( this.className, 'verifyTimeSheet', arguments );

	},

	getTimeSheetTotalData: function() {

		return this.argumentsHandler( this.className, 'getTimeSheetTotalData', arguments );

	},

	getTimeSheetData: function() {

		return this.argumentsHandler( this.className, 'getTimeSheetData', arguments );

	},

	getCommonTimeSheetData: function() {

		return this.argumentsHandler( this.className, 'getCommonTimeSheetData', arguments );

	},

	getTimeSheet: function() {

		return this.argumentsHandler( this.className, 'getTimeSheet', arguments );

	},

	setTimeSheet: function() {

		return this.argumentsHandler( this.className, 'setTimeSheet', arguments );

	},

	getTimeSheetDefaultData: function() {

		return this.argumentsHandler( this.className, 'getTimeSheetDefaultData', arguments );

	},

	deleteTimeSheet: function() {

		return this.argumentsHandler( this.className, 'deleteTimeSheet', arguments );

	},

	validateTimeSheet: function() {

		return this.argumentsHandler( this.className, 'validateTimeSheet', arguments );

	}



} );