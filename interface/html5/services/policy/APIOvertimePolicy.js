var APIOvertimePolicy = ServiceCaller.extend( {

	key_name: 'OverTimePolicy',
	className: 'APIOverTimePolicy',

	getOverTimePolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getOverTimePolicyDefaultData', arguments );

	},

	getOverTimePolicy: function() {

		return this.argumentsHandler( this.className, 'getOverTimePolicy', arguments );

	},

	getCommonOverTimePolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonOverTimePolicyData', arguments );

	},

	validateOverTimePolicy: function() {

		return this.argumentsHandler( this.className, 'validateOverTimePolicy', arguments );

	},

	setOverTimePolicy: function() {

		return this.argumentsHandler( this.className, 'setOverTimePolicy', arguments );

	},

	deleteOverTimePolicy: function() {

		return this.argumentsHandler( this.className, 'deleteOverTimePolicy', arguments );

	},

	copyOverTimePolicy: function() {
		return this.argumentsHandler( this.className, 'copyOverTimePolicy', arguments );
	}


} );