var APIAccrualPolicy = ServiceCaller.extend( {

	key_name: 'AccrualPolicy',
	className: 'APIAccrualPolicy',

	recalculateAccrual: function() {

		return this.argumentsHandler( this.className, 'recalculateAccrual', arguments );

	},

	getAccrualPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyDefaultData', arguments );

	},

	getAccrualPolicy: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicy', arguments );

	},

	getCommonAccrualPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualPolicyData', arguments );

	},

	validateAccrualPolicy: function() {

		return this.argumentsHandler( this.className, 'validateAccrualPolicy', arguments );

	},

	setAccrualPolicy: function() {

		return this.argumentsHandler( this.className, 'setAccrualPolicy', arguments );

	},

	deleteAccrualPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteAccrualPolicy', arguments );

	},

	copyAccrualPolicy: function() {
		return this.argumentsHandler( this.className, 'copyAccrualPolicy', arguments );
	}


} );