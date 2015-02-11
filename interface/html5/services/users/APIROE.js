var APIROE = ServiceCaller.extend( {

	key_name: 'ROE',
	className: 'APIROE',

	getROEDefaultData: function() {

		return this.argumentsHandler( this.className, 'getROEDefaultData', arguments );

	},

	getROE: function() {

		return this.argumentsHandler( this.className, 'getROE', arguments );

	},

	getCommonROEData: function() {

		return this.argumentsHandler( this.className, 'getCommonROEData', arguments );

	},

	validateROE: function() {

		return this.argumentsHandler( this.className, 'validateROE', arguments );

	},

	setROE: function() {

		return this.argumentsHandler( this.className, 'setROE', arguments );

	},

	deleteROE: function() {

		return this.argumentsHandler( this.className, 'deleteROE', arguments );

	},

	copyROE: function() {
		return this.argumentsHandler( this.className, 'copyROE', arguments );
	}



} );