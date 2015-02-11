var APIPunch = ServiceCaller.extend( {

	key_name: 'Punch',
	className: 'APIPunch',

	setUserPunch: function() {

		return this.argumentsHandler( this.className, 'setUserPunch', arguments );

	},

	getPunchTotalData: function() {

		return this.argumentsHandler( this.className, 'getPunchTotalData', arguments );

	},

	getUserPunch: function() {

		return this.argumentsHandler( this.className, 'getUserPunch', arguments );

	},

	getPunchData: function() {

		return this.argumentsHandler( this.className, 'getPunchData', arguments );

	},

	getCommonPunchData: function() {

		return this.argumentsHandler( this.className, 'getCommonPunchData', arguments );

	},

	getPunch: function() {

		return this.argumentsHandler( this.className, 'getPunch', arguments );

	},

	setPunch: function() {

		return this.argumentsHandler( this.className, 'setPunch', arguments );

	},

	getPunchDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPunchDefaultData', arguments );

	},

	deletePunch: function() {

		return this.argumentsHandler( this.className, 'deletePunch', arguments );

	},

	validatePunch: function() {

		return this.argumentsHandler( this.className, 'validatePunch', arguments );

	}



} );