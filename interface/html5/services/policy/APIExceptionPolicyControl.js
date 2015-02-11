var APIExceptionPolicyControl = ServiceCaller.extend( {

	key_name: 'ExceptionPolicyControl',
	className: 'APIExceptionPolicyControl',

	getExceptionPolicyControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getExceptionPolicyControlDefaultData', arguments );

	},

	getExceptionPolicyControl: function() {

		return this.argumentsHandler( this.className, 'getExceptionPolicyControl', arguments );

	},

	getCommonExceptionPolicyControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonExceptionPolicyControlData', arguments );

	},

	validateExceptionPolicyControl: function() {

		return this.argumentsHandler( this.className, 'validateExceptionPolicyControl', arguments );

	},

	setExceptionPolicyControl: function() {

		return this.argumentsHandler( this.className, 'setExceptionPolicyControl', arguments );

	},

	deleteExceptionPolicyControl: function() {

		return this.argumentsHandler( this.className, 'deleteExceptionPolicyControl', arguments );

	},

	copyExceptionPolicyControl: function() {

		return this.argumentsHandler( this.className, 'copyExceptionPolicyControl', arguments );

	}

} );