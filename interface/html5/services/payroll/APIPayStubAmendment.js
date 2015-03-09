var APIPayStubAmendment = ServiceCaller.extend( {

	key_name: 'PayStubAmendment',
	className: 'APIPayStubAmendment',

	getPayStubAmendmentDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubAmendmentDefaultData', arguments );

	},

	getPayStubAmendment: function() {

		return this.argumentsHandler( this.className, 'getPayStubAmendment', arguments );

	},

	getCommonPayStubAmendmentData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubAmendmentData', arguments );

	},

	validatePayStubAmendment: function() {

		return this.argumentsHandler( this.className, 'validatePayStubAmendment', arguments );

	},

	setPayStubAmendment: function() {

		return this.argumentsHandler( this.className, 'setPayStubAmendment', arguments );

	},

	deletePayStubAmendment: function() {

		return this.argumentsHandler( this.className, 'deletePayStubAmendment', arguments );

	},

	copyPayStubAmendment: function() {
		return this.argumentsHandler( this.className, 'copyPayStubAmendment', arguments );
	},

	calcAmount: function() {
		return this.argumentsHandler( this.className, 'calcAmount', arguments );
	}



} );