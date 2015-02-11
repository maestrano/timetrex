var APIPolicyGroup = ServiceCaller.extend( {

	key_name: 'PolicyGroup',
	className: 'APIPolicyGroup',

	getPolicyGroupDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPolicyGroupDefaultData', arguments );

	},

	getPolicyGroup: function() {

		return this.argumentsHandler( this.className, 'getPolicyGroup', arguments );

	},

	getCommonPolicyGroupData: function() {

		return this.argumentsHandler( this.className, 'getCommonPolicyGroupData', arguments );

	},

	validatePolicyGroup: function() {

		return this.argumentsHandler( this.className, 'validatePolicyGroup', arguments );

	},

	setPolicyGroup: function() {

		return this.argumentsHandler( this.className, 'setPolicyGroup', arguments );

	},

	deletePolicyGroup: function() {

		return this.argumentsHandler( this.className, 'deletePolicyGroup', arguments );

	},

	copyPolicyGroup: function() {

		return this.argumentsHandler( this.className, 'copyPolicyGroup', arguments );

	}

} );