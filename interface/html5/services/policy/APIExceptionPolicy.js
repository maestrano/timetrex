var APIExceptionPolicy = ServiceCaller.extend( {

	key_name: 'ExceptionPolicy',
	className: 'APIExceptionPolicy',

	getExceptionPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getExceptionPolicyDefaultData', arguments );

	},

	getExceptionPolicy: function() {

		return this.argumentsHandler( this.className, 'getExceptionPolicy', arguments );

	},

	getCommonExceptionPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonExceptionPolicyData', arguments );

	},

	validateExceptionPolicy: function() {

		return this.argumentsHandler( this.className, 'validateExceptionPolicy', arguments );

	},

	setExceptionPolicy: function() {

		return this.argumentsHandler( this.className, 'setExceptionPolicy', arguments );

	},

	deleteExceptionPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteExceptionPolicy', arguments );

	},

	copyExceptionPolicy: function() {

		return this.argumentsHandler( this.className, 'copyExceptionPolicy', arguments );

	}

} );