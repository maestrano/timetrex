var APIUserContact = ServiceCaller.extend( {

	key_name: 'UserContact',
	className: 'APIUserContact',

	getUserContactDefaultData: function() {
		return this.argumentsHandler( this.className, 'getUserContactDefaultData', arguments );

	},

	getUserContact: function() {
		return this.argumentsHandler( this.className, 'getUserContact', arguments );
	},

	getCommonUserContactData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserContactData', arguments );

	},

	validateUserContact: function() {

		return this.argumentsHandler( this.className, 'validateUserContact', arguments );

	},

	setUserContact: function() {

		return this.argumentsHandler( this.className, 'setUserContact', arguments );

	},

	deleteUserContact: function() {

		return this.argumentsHandler( this.className, 'deleteUserContact', arguments );

	},

	copyUserContact: function() {

		return this.argumentsHandler( this.className, 'copyUserContact', arguments );

	}



} );