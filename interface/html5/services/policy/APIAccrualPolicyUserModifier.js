var APIAccrualPolicyUserModifier = ServiceCaller.extend( {

	key_name: 'AccrualPolicyUserModifier',
	className: 'APIAccrualPolicyUserModifier',

	getAccrualPolicyUserModifier: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyUserModifier', arguments );

	},

	getAccrualPolicyUserModifierDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyUserModifierDefaultData', arguments );

	},

	getCommonAccrualPolicyUserModifierData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualPolicyUserModifierData', arguments );

	},

	validateAccrualPolicyUserModifier: function() {

		return this.argumentsHandler( this.className, 'validateAccrualPolicyUserModifier', arguments );

	},

	setAccrualPolicyUserModifier: function() {

		return this.argumentsHandler( this.className, 'setAccrualPolicyUserModifier', arguments );

	},

	deleteAccrualPolicyUserModifier: function() {

		return this.argumentsHandler( this.className, 'deleteAccrualPolicyUserModifier', arguments );

	},

	copyAccrualPolicyUserModifier: function() {

		return this.argumentsHandler( this.className, 'copyAccrualPolicyUserModifier', arguments );

	},

	getAccrualPolicyDataFromUserModifier: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyDataFromUserModifier', arguments );

	}



} );