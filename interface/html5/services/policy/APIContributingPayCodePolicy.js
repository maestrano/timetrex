var APIContributingPayCodePolicy = ServiceCaller.extend( {

	key_name: 'ContributingPayCodePolicy',
	className: 'APIContributingPayCodePolicy',

	getContributingPayCodePolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getContributingPayCodePolicyDefaultData', arguments );

	},

	getContributingPayCodePolicy: function() {

		return this.argumentsHandler( this.className, 'getContributingPayCodePolicy', arguments );

	},

	getCommonContributingPayCodePolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonContributingPayCodePolicyData', arguments );

	},

	validateContributingPayCodePolicy: function() {

		return this.argumentsHandler( this.className, 'validateContributingPayCodePolicy', arguments );

	},

	setContributingPayCodePolicy: function() {

		return this.argumentsHandler( this.className, 'setContributingPayCodePolicy', arguments );

	},

	deleteContributingPayCodePolicy: function() {

		return this.argumentsHandler( this.className, 'deleteContributingPayCodePolicy', arguments );

	},

	copyContributingPayCodePolicy: function() {
		return this.argumentsHandler( this.className, 'copyContributingPayCodePolicy', arguments );
	}


} );