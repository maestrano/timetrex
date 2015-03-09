var APIPayStubEntry = ServiceCaller.extend( {

	key_name: 'PayStubEntry',
	className: 'APIPayStubEntry',


	getPayStubEntry: function() {

		return this.argumentsHandler( this.className, 'getPayStubEntry', arguments );

	},

	getPayStubEntryDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubEntryDefaultData', arguments );

	},

	getCommonPayStubEntryData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubEntryData', arguments );

	},

	validatePayStubEntry: function() {

		return this.argumentsHandler( this.className, 'validatePayStubEntry', arguments );

	},

	setPayStubEntry: function() {

		return this.argumentsHandler( this.className, 'setPayStubEntry', arguments );

	},

	deletePayStubEntry: function() {

		return this.argumentsHandler( this.className, 'deletePayStubEntry', arguments );

	},

	copyPayStubEntry: function() {

		return this.argumentsHandler( this.className, 'copyPayStubEntry', arguments );

	}
} );