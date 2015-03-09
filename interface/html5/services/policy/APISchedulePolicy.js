var APISchedulePolicy = ServiceCaller.extend( {

	key_name: 'SchedulePolicy',
	className: 'APISchedulePolicy',

	getSchedulePolicyTotalData: function() {

		return this.argumentsHandler( this.className, 'getSchedulePolicyTotalData', arguments );

	},

	getSchedulePolicyData: function() {

		return this.argumentsHandler( this.className, 'getSchedulePolicyData', arguments );

	},

	getCommonSchedulePolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonSchedulePolicyData', arguments );

	},

	getSchedulePolicy: function() {

		return this.argumentsHandler( this.className, 'getSchedulePolicy', arguments );

	},

	setSchedulePolicy: function() {

		return this.argumentsHandler( this.className, 'setSchedulePolicy', arguments );

	},

	getSchedulePolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getSchedulePolicyDefaultData', arguments );

	},

	deleteSchedulePolicy: function() {

		return this.argumentsHandler( this.className, 'deleteSchedulePolicy', arguments );

	},

	validateSchedulePolicy: function() {

		return this.argumentsHandler( this.className, 'validateSchedulePolicy', arguments );

	},

	copySchedulePolicy: function() {

		return this.argumentsHandler( this.className, 'copySchedulePolicy', arguments );

	}



} );