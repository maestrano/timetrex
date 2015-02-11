var APIPremiumPolicy = ServiceCaller.extend( {

	key_name: 'PremiumPolicy',
	className: 'APIPremiumPolicy',

	getPremiumPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPremiumPolicyDefaultData', arguments );

	},

	getPremiumPolicy: function() {

		return this.argumentsHandler( this.className, 'getPremiumPolicy', arguments );

	},

	getCommonPremiumPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonPremiumPolicyData', arguments );

	},

	validatePremiumPolicy: function() {

		return this.argumentsHandler( this.className, 'validatePremiumPolicy', arguments );

	},

	setPremiumPolicy: function() {

		return this.argumentsHandler( this.className, 'setPremiumPolicy', arguments );

	},

	deletePremiumPolicy: function() {

		return this.argumentsHandler( this.className, 'deletePremiumPolicy', arguments );

	},

	copyPremiumPolicy: function() {
		return this.argumentsHandler( this.className, 'copyPremiumPolicy', arguments );
	}


} );