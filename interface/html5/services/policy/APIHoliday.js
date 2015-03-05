var APIHoliday = ServiceCaller.extend( {

	key_name: 'Holiday',
	className: 'APIHoliday',

	getHolidayDefaultData: function() {

		return this.argumentsHandler( this.className, 'getHolidayDefaultData', arguments );

	},

	getHoliday: function() {

		return this.argumentsHandler( this.className, 'getHoliday', arguments );

	},

	getCommonHolidayData: function() {

		return this.argumentsHandler( this.className, 'getCommonHolidayData', arguments );

	},

	validateHoliday: function() {

		return this.argumentsHandler( this.className, 'validateHoliday', arguments );

	},

	setHoliday: function() {

		return this.argumentsHandler( this.className, 'setHoliday', arguments );

	},

	deleteHoliday: function() {

		return this.argumentsHandler( this.className, 'deleteHoliday', arguments );

	},

	copyHoliday: function() {
		return this.argumentsHandler( this.className, 'copyHoliday', arguments );
	}


} );