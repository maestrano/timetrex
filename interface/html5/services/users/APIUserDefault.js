var APIUserDefault = ServiceCaller.extend( {


	key_name: 'UserDefault',
	className: 'APIUserDefault',

	getUserDefaultDefaultData: function() {
		return this.argumentsHandler( this.className, 'getUserDefaultDefaultData', arguments );

	},

	getUserDefault: function() {
		return this.argumentsHandler( this.className, 'getUserDefault', arguments );

	},

	getCommonUserDefaultData: function() {
		return this.argumentsHandler( this.className, 'getCommonUserDefaultData', arguments );

	},

	validateUserDefault: function() {
		return this.argumentsHandler( this.className, 'validateUserDefault', arguments );

	},

	setUserDefault: function() {
		return this.argumentsHandler( this.className, 'setUserDefault', arguments );

	},

	deleteUserDefault: function() {
		return this.argumentsHandler( this.className, 'deleteUserDefault', arguments );

	},

	copyUserDefault: function() {
		return this.argumentsHandler( this.className, 'copyUserDefault', arguments );

	}


} );