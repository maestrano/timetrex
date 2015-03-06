var APIRoundIntervalPolicy = ServiceCaller.extend( {

	key_name: 'RoundIntervalPolicy',
	className: 'APIRoundIntervalPolicy',

	getRoundIntervalPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRoundIntervalPolicyDefaultData', arguments );

	},

	getRoundIntervalPolicy: function() {

		return this.argumentsHandler( this.className, 'getRoundIntervalPolicy', arguments );

	},

	getCommonRoundIntervalPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonRoundIntervalPolicyData', arguments );

	},

	validateRoundIntervalPolicy: function() {

		return this.argumentsHandler( this.className, 'validateRoundIntervalPolicy', arguments );

	},

	setRoundIntervalPolicy: function() {

		return this.argumentsHandler( this.className, 'setRoundIntervalPolicy', arguments );

	},

	deleteRoundIntervalPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteRoundIntervalPolicy', arguments );

	},

	copyRoundIntervalPolicy: function() {
		return this.argumentsHandler( this.className, 'copyRoundIntervalPolicy', arguments );
	}


} );