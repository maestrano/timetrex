var APIAccrualPolicyAccount = ServiceCaller.extend( {

	key_name: 'AccrualPolicyAccount',
	className: 'APIAccrualPolicyAccount',

	getAccrualPolicyAccountDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyAccountDefaultData', arguments );

	},

	getAccrualPolicyAccount: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyAccount', arguments );

	},

	getCommonAccrualPolicyAccountData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualPolicyAccountData', arguments );

	},

	validateAccrualPolicyAccount: function() {

		return this.argumentsHandler( this.className, 'validateAccrualPolicyAccount', arguments );

	},

	setAccrualPolicyAccount: function() {

		return this.argumentsHandler( this.className, 'setAccrualPolicyAccount', arguments );

	},

	deleteAccrualPolicyAccount: function() {

		return this.argumentsHandler( this.className, 'deleteAccrualPolicyAccount', arguments );

	},

	copyAccrualPolicyAccount: function() {
		return this.argumentsHandler( this.className, 'copyAccrualPolicyAccount', arguments );
	}


} );