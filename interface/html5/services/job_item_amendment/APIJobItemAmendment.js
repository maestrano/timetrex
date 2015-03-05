var APIJobItemAmendment = ServiceCaller.extend( {

	key_name: 'JobItemAmendment',
	className: 'APIJobItemAmendment',

	getCommonJobItemAmendmentData: function() {

		return this.argumentsHandler( this.className, 'getCommonJobItemAmendmentData', arguments );

	},

	getJobItemAmendment: function() {

		return this.argumentsHandler( this.className, 'getJobItemAmendment', arguments );

	},

	setJobItemAmendment: function() {

		return this.argumentsHandler( this.className, 'setJobItemAmendment', arguments );

	},

	getJobItemAmendmentDefaultData: function() {

		return this.argumentsHandler( this.className, 'getJobItemAmendmentDefaultData', arguments );

	},

	deleteJobItemAmendment: function() {

		return this.argumentsHandler( this.className, 'deleteJobItemAmendment', arguments );

	},

	validateJobItemAmendment: function() {

		return this.argumentsHandler( this.className, 'validateJobItemAmendment', arguments );

	},

	copyJobItemAmendment: function() {

		return this.argumentsHandler( this.className, 'copyJobItemAmendment', arguments );

	}



} );