var APIBankAccount = ServiceCaller.extend( {


	key_name: 'BankAccount',
	className: 'APIBankAccount',

	deleteBankAccount: function() {
		return this.argumentsHandler( this.className, 'deleteBankAccount', arguments );

	},

	getBankAccountDefaultData: function() {
		return this.argumentsHandler( this.className, 'getBankAccountDefaultData', arguments );

	},

	getBankAccount: function() {
		return this.argumentsHandler( this.className, 'getBankAccount', arguments );

	},

	getCommonBankAccountData: function() {
		return this.argumentsHandler( this.className, 'getCommonBankAccountData', arguments );

	},

	validateBankAccount: function() {
		return this.argumentsHandler( this.className, 'validateBankAccount', arguments );

	},

	setBankAccount: function() {
		return this.argumentsHandler( this.className, 'setBankAccount', arguments );

	}





} );