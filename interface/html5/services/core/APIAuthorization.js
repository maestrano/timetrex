var APIAuthorization = ServiceCaller.extend( {

	key_name: 'Authorization',
	className: 'APIAuthorization',

	getAuthorizationDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAuthorizationDefaultData', arguments );

	},

	getAuthorization: function() {

		return this.argumentsHandler( this.className, 'getAuthorization', arguments );

	},

	getCommonAuthorizationData: function() {

		return this.argumentsHandler( this.className, 'getCommonAuthorizationData', arguments );

	},

	validateAuthorization: function() {

		return this.argumentsHandler( this.className, 'validateAuthorization', arguments );

	},

	setAuthorization: function() {

		return this.argumentsHandler( this.className, 'setAuthorization', arguments );

	},

	deleteAuthorization: function() {

		return this.argumentsHandler( this.className, 'deleteAuthorization', arguments );

	}


} );