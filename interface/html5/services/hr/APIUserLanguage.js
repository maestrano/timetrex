var APIUserLanguage = ServiceCaller.extend( {

	key_name: 'UserLanguage',
	className: 'APIUserLanguage',

	getCommonUserLanguageData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserLanguageData', arguments );

	},

	getUserLanguage: function() {

		return this.argumentsHandler( this.className, 'getUserLanguage', arguments );

	},

	setUserLanguage: function() {

		return this.argumentsHandler( this.className, 'setUserLanguage', arguments );

	},

	getUserLanguageDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserLanguageDefaultData', arguments );

	},

	deleteUserLanguage: function() {

		return this.argumentsHandler( this.className, 'deleteUserLanguage', arguments );

	},

	validateUserLanguage: function() {

		return this.argumentsHandler( this.className, 'validateUserLanguage', arguments );

	},

	copyUserLanguage: function() {

		return this.argumentsHandler( this.className, 'copyUserLanguage', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );