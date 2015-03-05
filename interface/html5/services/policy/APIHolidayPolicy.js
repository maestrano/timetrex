var APIHolidayPolicy = ServiceCaller.extend( {

	key_name: 'HolidayPolicy',
	className: 'APIHolidayPolicy',

	getHolidayPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getHolidayPolicyDefaultData', arguments );

	},

	getHolidayPolicy: function() {

		return this.argumentsHandler( this.className, 'getHolidayPolicy', arguments );

	},

	getCommonHolidayPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonHolidayPolicyData', arguments );

	},

	validateHolidayPolicy: function() {

		return this.argumentsHandler( this.className, 'validateHolidayPolicy', arguments );

	},

	setHolidayPolicy: function() {

		return this.argumentsHandler( this.className, 'setHolidayPolicy', arguments );

	},

	deleteHolidayPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteHolidayPolicy', arguments );

	},

	copyHolidayPolicy: function() {
		return this.argumentsHandler( this.className, 'copyHolidayPolicy', arguments );
	}


} );