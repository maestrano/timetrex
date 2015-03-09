var APIUserGenericStatus = ServiceCaller.extend( {

	key_name: 'UserGenericStatus',
	className: 'APIUserGenericStatus',

	getUserGenericStatusCountArray: function() {

		return this.argumentsHandler( this.className, 'getUserGenericStatusCountArray', arguments );

	},

	getCommonUserGenericStatusData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserGenericStatusData', arguments );

	},

	getUserGenericStatus: function() {

		return this.argumentsHandler( this.className, 'getUserGenericStatus', arguments );

	},

	setUserGenericStatus: function() {

		return this.argumentsHandler( this.className, 'setUserGenericStatus', arguments );

	},

	getUserGenericStatusDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserGenericStatusDefaultData', arguments );

	},

	deleteUserGenericStatus: function() {

		return this.argumentsHandler( this.className, 'deleteUserGenericStatus', arguments );

	},

	validateUserGenericStatus: function() {

		return this.argumentsHandler( this.className, 'validateUserGenericStatus', arguments );

	}



} );