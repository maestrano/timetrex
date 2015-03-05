var APIPayPeriodSchedule = ServiceCaller.extend( {

	key_name: 'PayPeriodSchedule',
	className: 'APIPayPeriodSchedule',

	detectPayPeriodScheduleDates: function() {

		return this.argumentsHandler( this.className, 'detectPayPeriodScheduleDates', arguments );

	},

	getPayPeriodSchedule: function() {

		return this.argumentsHandler( this.className, 'getPayPeriodSchedule', arguments );

	},

	getPayPeriodScheduleDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayPeriodScheduleDefaultData', arguments );

	},
	getCommonPayPeriodScheduleData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayPeriodScheduleData', arguments );

	},
	validatePayPeriodSchedule: function() {

		return this.argumentsHandler( this.className, 'validatePayPeriodSchedule', arguments );

	},
	setPayPeriodSchedule: function() {

		return this.argumentsHandler( this.className, 'setPayPeriodSchedule', arguments );

	},
	deletePayPeriodSchedule: function() {

		return this.argumentsHandler( this.className, 'deletePayPeriodSchedule', arguments );

	},
	copyPayPeriodSchedule: function() {

		return this.argumentsHandler( this.className, 'copyPayPeriodSchedule', arguments );

	},
	detectPayPeriodScheduleSettings: function() {

		return this.argumentsHandler( this.className, 'detectPayPeriodScheduleSettings', arguments );

	},



} );