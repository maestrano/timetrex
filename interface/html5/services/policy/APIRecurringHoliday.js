var APIRecurringHoliday = ServiceCaller.extend( {

	key_name: 'RecurringHoliday',
	className: 'APIRecurringHoliday',

	getRecurringHolidayDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRecurringHolidayDefaultData', arguments );

	},

	getRecurringHoliday: function() {

		return this.argumentsHandler( this.className, 'getRecurringHoliday', arguments );

	},

	getCommonRecurringHolidayData: function() {

		return this.argumentsHandler( this.className, 'getCommonRecurringHolidayData', arguments );

	},

	validateRecurringHoliday: function() {

		return this.argumentsHandler( this.className, 'validateRecurringHoliday', arguments );

	},

	setRecurringHoliday: function() {

		return this.argumentsHandler( this.className, 'setRecurringHoliday', arguments );

	},

	deleteRecurringHoliday: function() {

		return this.argumentsHandler( this.className, 'deleteRecurringHoliday', arguments );

	},

	copyRecurringHoliday: function() {
		return this.argumentsHandler( this.className, 'copyRecurringHoliday', arguments );
	}


} );