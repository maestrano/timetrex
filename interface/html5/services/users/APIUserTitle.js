var APIUserTitle = ServiceCaller.extend( {

	key_name: 'UserTitle',
	className: 'APIUserTitle',

	getUserTitle: function() {

		return this.argumentsHandler( this.className, 'getUserTitle', arguments );

	},

	getUserTitleDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserTitleDefaultData', arguments );

	},

	getCommonUserTitleData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserTitleData', arguments );

	},

	setUserTitle: function() {

		return this.argumentsHandler( this.className, 'setUserTitle', arguments );

	},

	deleteUserTitle: function() {

		return this.argumentsHandler( this.className, 'deleteUserTitle', arguments );

	},

	validateUserTitle: function() {

		return this.argumentsHandler( this.className, 'validateUserTitle', arguments );

	},

	copyUserTitle: function() {
		return this.argumentsHandler( this.className, 'copyUserTitle', arguments );
	}


} );