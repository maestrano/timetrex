var APIRequest = ServiceCaller.extend( {

	key_name: 'Request',
	className: 'APIRequest',

	getRequestDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRequestDefaultData', arguments );

	},

	getHierarchyLevelOptions: function() {

		return this.argumentsHandler( this.className, 'getHierarchyLevelOptions', arguments );

	},

	getRequest: function() {

		return this.argumentsHandler( this.className, 'getRequest', arguments );

	},

	getCommonRequestData: function() {

		return this.argumentsHandler( this.className, 'getCommonRequestData', arguments );

	},

	validateRequest: function() {

		return this.argumentsHandler( this.className, 'validateRequest', arguments );

	},

	setRequest: function() {

		return this.argumentsHandler( this.className, 'setRequest', arguments );

	},

	deleteRequest: function() {
		return this.argumentsHandler( this.className, 'deleteRequest', arguments );
	},

	copyRequest: function() {
		return this.argumentsHandler( this.className, 'copyRequest', arguments );
	}





} );