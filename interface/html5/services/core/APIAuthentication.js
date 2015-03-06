var APIAuthentication = ServiceCaller.extend( {

	key_name: 'Authentication',
	className: 'APIAuthentication',

	getAuthentication: function() {
		return this.argumentsHandler( this.className, 'getOptions', arguments );
	},

	changePassword: function() {

		return this.argumentsHandler( this.className, 'changePassword', arguments );

	},

	getLocale: function() {

		return this.argumentsHandler( this.className, 'getLocale', arguments );

	},

	newSession: function() {

		return this.argumentsHandler( this.className, 'newSession', arguments );

	},

	sendErrorReport: function() {

		return this.argumentsHandler( this.className, 'sendErrorReport', arguments );

	},

	login: function() {

		return this.argumentsHandler( this.className, 'Login', arguments );

	},

	getPreLoginData: function() {

		return this.argumentsHandler( this.className, 'getPreLoginData', arguments );

	},

	getCurrentUser: function() {
		return this.argumentsHandler( this.className, 'getCurrentUser', arguments );

	},

//	isApplicationBranded: function() {
//		return this.argumentsHandler( this.className, 'isApplicationBranded', arguments );
//
//	},

	getOrganizationName: function() {

		return this.argumentsHandler( this.className, 'getOrganizationName', arguments );

	},

	getApplicationName: function() {
		return this.argumentsHandler( this.className, 'getApplicationName', arguments );

	},

	getCurrentCompany: function() {
		return this.argumentsHandler( this.className, 'getCurrentCompany', arguments );

	},

	getOrganizationURL: function() {
		return this.argumentsHandler( this.className, 'getOrganizationURL', arguments );

	}




} );