var APIUserDeduction = ServiceCaller.extend( {

	key_name: 'UserDeduction',
	className: 'APIUserDeduction',

	getHourlyRate: function() {

		return this.argumentsHandler( this.className, 'getHourlyRate', arguments );
	},

	getCommonUserDeductionData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserDeductionData', arguments );

	},

	getUserDeduction: function() {

		return this.argumentsHandler( this.className, 'getUserDeduction', arguments );

	},

	setUserDeduction: function() {

		return this.argumentsHandler( this.className, 'setUserDeduction', arguments );

	},

	copyUserDeduction: function() {

		return this.argumentsHandler( this.className, 'copyUserDeduction', arguments );

	},

	getUserDeductionDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserDeductionDefaultData', arguments );

	},

	deleteUserDeduction: function() {

		return this.argumentsHandler( this.className, 'deleteUserDeduction', arguments );

	},

	validateUserDeduction: function() {

		return this.argumentsHandler( this.className, 'validateUserDeduction', arguments );

	}



} );