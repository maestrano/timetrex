var APIBreakPolicy = ServiceCaller.extend( {

	key_name: 'BreakPolicy',
	className: 'APIBreakPolicy',

	getBreakPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getBreakPolicyDefaultData', arguments );

	},

	getBreakPolicy: function() {

		return this.argumentsHandler( this.className, 'getBreakPolicy', arguments );

	},

	getCommonBreakPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonBreakPolicyData', arguments );

	},

	validateBreakPolicy: function() {

		return this.argumentsHandler( this.className, 'validateBreakPolicy', arguments );

	},

	setBreakPolicy: function() {

		return this.argumentsHandler( this.className, 'setBreakPolicy', arguments );

	},

	deleteBreakPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteBreakPolicy', arguments );

	},

	copyBreakPolicy: function() {
		return this.argumentsHandler( this.className, 'copyBreakPolicy', arguments );
	}


} );