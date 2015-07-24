var APIPayStubEntryAccount = ServiceCaller.extend( {

	key_name: 'PayStubEntryAccount',
	className: 'APIPayStubEntryAccount',

	migratePayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'migratePayStubEntryAccount', arguments );

	},

	getPayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'getPayStubEntryAccount', arguments );

	},

	getPayStubEntryAccountDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayStubEntryAccountDefaultData', arguments );

	},

	getCommonPayStubEntryAccountData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayStubEntryAccountData', arguments );

	},

	validatePayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'validatePayStubEntryAccount', arguments );

	},

	setPayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'setPayStubEntryAccount', arguments );

	},

	deletePayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'deletePayStubEntryAccount', arguments );

	},

	copyPayStubEntryAccount: function() {

		return this.argumentsHandler( this.className, 'copyPayStubEntryAccount', arguments );

	}

} );