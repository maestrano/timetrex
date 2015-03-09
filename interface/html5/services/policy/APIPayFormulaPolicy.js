var APIPayFormulaPolicy = ServiceCaller.extend( {

	key_name: 'PayFormulaPolicy',
	className: 'APIPayFormulaPolicy',

	getPayFormulaPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayFormulaPolicyDefaultData', arguments );

	},

	getPayFormulaPolicy: function() {

		return this.argumentsHandler( this.className, 'getPayFormulaPolicy', arguments );

	},

	getCommonPayFormulaPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayFormulaPolicyData', arguments );

	},

	validatePayFormulaPolicy: function() {

		return this.argumentsHandler( this.className, 'validatePayFormulaPolicy', arguments );

	},

	setPayFormulaPolicy: function() {

		return this.argumentsHandler( this.className, 'setPayFormulaPolicy', arguments );

	},

	deletePayFormulaPolicy: function() {

		return this.argumentsHandler( this.className, 'deletePayFormulaPolicy', arguments );

	},

	copyPayFormulaPolicy: function() {
		return this.argumentsHandler( this.className, 'copyPayFormulaPolicy', arguments );
	}


} );