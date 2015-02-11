var APIRegularTimePolicy = ServiceCaller.extend( {

	key_name: 'RegularTimePolicy',
	className: 'APIRegularTimePolicy',

	getRegularTimePolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRegularTimePolicyDefaultData', arguments );

	},

	getRegularTimePolicy: function() {

		return this.argumentsHandler( this.className, 'getRegularTimePolicy', arguments );

	},

	getCommonRegularTimePolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonRegularTimePolicyData', arguments );

	},

	validateRegularTimePolicy: function() {

		return this.argumentsHandler( this.className, 'validateRegularTimePolicy', arguments );

	},

	setRegularTimePolicy: function() {

		return this.argumentsHandler( this.className, 'setRegularTimePolicy', arguments );

	},

	deleteRegularTimePolicy: function() {

		return this.argumentsHandler( this.className, 'deleteRegularTimePolicy', arguments );

	},

	copyRegularTimePolicy: function() {
		return this.argumentsHandler( this.className, 'copyRegularTimePolicy', arguments );
	}


} );