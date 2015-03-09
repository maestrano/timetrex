var APIUserWage = ServiceCaller.extend( {

	key_name: 'UserWage',
	className: 'APIUserWage',

	getHourlyRate: function() {

		return this.argumentsHandler( this.className, 'getHourlyRate', arguments );
	},

	getCommonUserWageData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserWageData', arguments );

	},

	getUserWage: function() {

		return this.argumentsHandler( this.className, 'getUserWage', arguments );

	},

	setUserWage: function() {

		return this.argumentsHandler( this.className, 'setUserWage', arguments );

	},

	copyUserWage: function() {

		return this.argumentsHandler( this.className, 'copyUserWage', arguments );

	},

	getUserWageDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserWageDefaultData', arguments );

	},

	deleteUserWage: function() {

		return this.argumentsHandler( this.className, 'deleteUserWage', arguments );

	},

	validateUserWage: function() {

		return this.argumentsHandler( this.className, 'validateUserWage', arguments );

	}



} );