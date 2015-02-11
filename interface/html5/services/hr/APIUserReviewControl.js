var APIUserReviewControl = ServiceCaller.extend( {

	key_name: 'UserReviewControl',
	className: 'APIUserReviewControl',

	getCommonUserReviewControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserReviewControlData', arguments );

	},

	getUserReviewControl: function() {

		return this.argumentsHandler( this.className, 'getUserReviewControl', arguments );

	},

	setUserReviewControl: function() {

		return this.argumentsHandler( this.className, 'setUserReviewControl', arguments );

	},

	getUserReviewControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserReviewControlDefaultData', arguments );

	},

	deleteUserReviewControl: function() {

		return this.argumentsHandler( this.className, 'deleteUserReviewControl', arguments );

	},

	validateUserReviewControl: function() {

		return this.argumentsHandler( this.className, 'validateUserReviewControl', arguments );

	},

	copyUserReviewControl: function() {

		return this.argumentsHandler( this.className, 'copyUserReviewControl', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );