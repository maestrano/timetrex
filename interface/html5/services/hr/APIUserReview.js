var APIUserReview = ServiceCaller.extend( {

	key_name: 'UserReview',
	className: 'APIUserReview',

	getUserReviewDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserReviewDefaultData', arguments );

	},

	getUserReview: function() {

		return this.argumentsHandler( this.className, 'getUserReview', arguments );

	},

	getCommonUserReviewData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserReviewData', arguments );

	},

	validateUserReview: function() {

		return this.argumentsHandler( this.className, 'validateUserReview', arguments );

	},

	setUserReview: function() {

		return this.argumentsHandler( this.className, 'setUserReview', arguments );

	},

	deleteUserReview: function() {

		return this.argumentsHandler( this.className, 'deleteUserReview', arguments );

	},

	copyUserReview: function() {
		return this.argumentsHandler( this.className, 'copyUserReview', arguments );
	}


} );