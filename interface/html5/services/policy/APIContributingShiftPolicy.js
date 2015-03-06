var APIContributingShiftPolicy = ServiceCaller.extend( {

	key_name: 'ContributingShiftPolicy',
	className: 'APIContributingShiftPolicy',

	getContributingShiftPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getContributingShiftPolicyDefaultData', arguments );

	},

	getContributingShiftPolicy: function() {

		return this.argumentsHandler( this.className, 'getContributingShiftPolicy', arguments );

	},

	getCommonContributingShiftPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonContributingShiftPolicyData', arguments );

	},

	validateContributingShiftPolicy: function() {

		return this.argumentsHandler( this.className, 'validateContributingShiftPolicy', arguments );

	},

	setContributingShiftPolicy: function() {

		return this.argumentsHandler( this.className, 'setContributingShiftPolicy', arguments );

	},

	deleteContributingShiftPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteContributingShiftPolicy', arguments );

	},

	copyContributingShiftPolicy: function() {
		return this.argumentsHandler( this.className, 'copyContributingShiftPolicy', arguments );
	}


} );