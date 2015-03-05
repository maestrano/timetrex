var APIRecurringScheduleControl = ServiceCaller.extend( {

	key_name: 'RecurringScheduleControl',
	className: 'APIRecurringScheduleControl',

	getRecurringScheduleControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRecurringScheduleControlDefaultData', arguments );

	},

	getRecurringScheduleControl: function() {

		return this.argumentsHandler( this.className, 'getRecurringScheduleControl', arguments );

	},

	getCommonRecurringScheduleControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonRecurringScheduleControlData', arguments );

	},

	validateRecurringScheduleControl: function() {

		return this.argumentsHandler( this.className, 'validateRecurringScheduleControl', arguments );

	},

	setRecurringScheduleControl: function() {

		return this.argumentsHandler( this.className, 'setRecurringScheduleControl', arguments );

	},

	deleteRecurringScheduleControl: function() {

		return this.argumentsHandler( this.className, 'deleteRecurringScheduleControl', arguments );

	},

	copyRecurringScheduleControl: function() {
		return this.argumentsHandler( this.className, 'copyRecurringScheduleControl', arguments );
	}


} );