var APIPayCode = ServiceCaller.extend( {

	key_name: 'PayCode',
	className: 'APIPayCode',

	getPayCodeDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayCodeDefaultData', arguments );

	},

	getPayCode: function() {

		return this.argumentsHandler( this.className, 'getPayCode', arguments );

	},

	getCommonPayCodeData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayCodeData', arguments );

	},

	validatePayCode: function() {

		return this.argumentsHandler( this.className, 'validatePayCode', arguments );

	},

	setPayCode: function() {

		return this.argumentsHandler( this.className, 'setPayCode', arguments );

	},

	deletePayCode: function() {

		return this.argumentsHandler( this.className, 'deletePayCode', arguments );

	},

	copyPayCode: function() {
		return this.argumentsHandler( this.className, 'copyPayCode', arguments );
	},

	migratePayCode: function() {
		return this.argumentsHandler( this.className, 'migratePayCode', arguments );
	}


} );