var APIUserGroup = ServiceCaller.extend( {

	key_name: 'UserGroup',
	className: 'APIUserGroup',

	getUserGroup: function() {

		return this.argumentsHandler( this.className, 'getUserGroup', arguments );

	},

	getUserGroupDefaultData: function() {
		return this.argumentsHandler( this.className, 'getUserGroupDefaultData', arguments );
	},

	getCommonUserGroupData: function() {
		return this.argumentsHandler( this.className, 'getCommonUserGroupData', arguments );
	},

	validateUserGroup: function() {
		return this.argumentsHandler( this.className, 'validateUserGroup', arguments );
	},

	setUserGroup: function() {
		return this.argumentsHandler( this.className, 'setUserGroup', arguments );
	},

	deleteUserGroup: function() {
		return this.argumentsHandler( this.className, 'deleteUserGroup', arguments );
	},

	dragNdropUserGroup: function() {
		return this.argumentsHandler( this.className, 'dragNdropUserGroup', arguments );
	}




} );