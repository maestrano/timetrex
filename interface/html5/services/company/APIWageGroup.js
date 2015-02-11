var APIWageGroup = ServiceCaller.extend( {

	key_name: 'WageGroup',
	className: 'APIWageGroup',

	getCommonWageGroupData: function() {

		return this.argumentsHandler( this.className, 'getCommonWageGroupData', arguments );

	},

	getWageGroup: function() {

		return this.argumentsHandler( this.className, 'getWageGroup', arguments );

	},

	setWageGroup: function() {

		return this.argumentsHandler( this.className, 'setWageGroup', arguments );

	},

	getWageGroupDefaultData: function() {

		return this.argumentsHandler( this.className, 'getWageGroupDefaultData', arguments );

	},

	deleteWageGroup: function() {

		return this.argumentsHandler( this.className, 'deleteWageGroup', arguments );

	},

	validateWageGroup: function() {

		return this.argumentsHandler( this.className, 'validateWageGroup', arguments );

	},

	copyWageGroup: function() {

		return this.argumentsHandler( this.className, 'copyWageGroup', arguments );

	},

} );