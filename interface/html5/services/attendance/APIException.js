var APIException = ServiceCaller.extend( {

	key_name: 'Exception',
	className: 'APIException',

	getExceptionDefaultData: function() {

		return this.argumentsHandler( this.className, 'getExceptionDefaultData', arguments );

	},

	getException: function() {

		return this.argumentsHandler( this.className, 'getException', arguments );

	},

	getCommonExceptionData: function() {

		return this.argumentsHandler( this.className, 'getCommonExceptionData', arguments );

	},

	validateException: function() {

		return this.argumentsHandler( this.className, 'validateException', arguments );

	},

	setException: function() {

		return this.argumentsHandler( this.className, 'setException', arguments );

	},

	deleteException: function() {

		return this.argumentsHandler( this.className, 'deleteException', arguments );

	},

	copyException: function() {
		return this.argumentsHandler( this.className, 'copyException', arguments );
	}


} );