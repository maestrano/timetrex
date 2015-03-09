var APISchedule = ServiceCaller.extend( {

	key_name: 'Schedule',
	className: 'APISchedule',


	getCombinedSchedule: function() {

		return this.argumentsHandler( this.className, 'getCombinedSchedule', arguments );

	},

	getCommonScheduleData: function() {

		return this.argumentsHandler( this.className, 'getCommonScheduleData', arguments );

	},

	getScheduleTotalData: function() {

		return this.argumentsHandler( this.className, 'getScheduleTotalData', arguments );

	},

	getScheduleData: function() {

		return this.argumentsHandler( this.className, 'getScheduleData', arguments );

	},

	getSchedule: function() {

		return this.argumentsHandler( this.className, 'getSchedule', arguments );

	},

	setSchedule: function() {

		return this.argumentsHandler( this.className, 'setSchedule', arguments );

	},

	getScheduleDefaultData: function() {

		return this.argumentsHandler( this.className, 'getScheduleDefaultData', arguments );

	},

	getScheduleDates: function() {

		return this.argumentsHandler( this.className, 'getScheduleDates', arguments );

	},

	deleteSchedule: function() {

		return this.argumentsHandler( this.className, 'deleteSchedule', arguments );

	},

	getScheduleTotalTime: function() {

		return this.argumentsHandler( this.className, 'getScheduleTotalTime', arguments );

	},

	swapSchedule: function() {

		return this.argumentsHandler( this.className, 'swapSchedule', arguments );

	},

	validateSchedule: function() {

		return this.argumentsHandler( this.className, 'validateSchedule', arguments );

	}



} );