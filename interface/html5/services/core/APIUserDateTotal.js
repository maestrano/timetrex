var APIUserDateTotal = ServiceCaller.extend( {

	key_name: 'UserDateTotal',
	className: 'APIUserDateTotal',

	getUserDateTotalTotalData: function() {

		return this.argumentsHandler( this.className, 'getUserDateTotalTotalData', arguments );

	},

	getUserDateTotalData: function() {

		return this.argumentsHandler( this.className, 'getUserDateTotalData', arguments );

	},

	getCommonUserDateTotalData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserDateTotalData', arguments );

	},

	getUserDateTotal: function() {

		return this.argumentsHandler( this.className, 'getUserDateTotal', arguments );

	},

	setUserDateTotal: function() {

		return this.argumentsHandler( this.className, 'setUserDateTotal', arguments );

	},

	getUserDateTotalDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserDateTotalDefaultData', arguments );

	},

	deleteUserDateTotal: function() {

		return this.argumentsHandler( this.className, 'deleteUserDateTotal', arguments );

	},

	validateUserDateTotal: function() {

		return this.argumentsHandler( this.className, 'validateUserDateTotal', arguments );

	}



} );