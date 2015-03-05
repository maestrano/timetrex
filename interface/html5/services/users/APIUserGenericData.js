var APIUserGenericData = ServiceCaller.extend( {

	key_name: 'UserGenericData',
	className: 'APIUserGenericData',

	getUserGenericData: function() {

		return this.argumentsHandler( this.className, 'getUserGenericData', arguments );

	},

	setUserGenericData: function() {

		return this.argumentsHandler( this.className, 'setUserGenericData', arguments );

	},

	deleteUserGenericData: function() {

		return this.argumentsHandler( this.className, 'deleteUserGenericData', arguments );

	}



} );