var APICurrency = ServiceCaller.extend( {


	key_name: 'Currency',
	className: 'APICurrency',

	getCurrency: function() {

		return this.argumentsHandler( this.className, 'getCurrency', arguments );

	},
	getCurrencyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getCurrencyDefaultData', arguments );

	},
	getCommonCurrencyData: function() {

		return this.argumentsHandler( this.className, 'getCommonCurrencyData', arguments );

	},
	validateCurrency: function() {

		return this.argumentsHandler( this.className, 'validateCurrency', arguments );

	},
	setCurrency: function() {

		return this.argumentsHandler( this.className, 'setCurrency', arguments );

	},
	deleteCurrency: function() {

		return this.argumentsHandler( this.className, 'deleteCurrency', arguments );

	},
	copyCurrency: function() {

		return this.argumentsHandler( this.className, 'copyCurrency', arguments );

	},
	getISOCodesArray: function() {

		return this.argumentsHandler( this.className, 'getISOCodesArray', arguments );

	}



} );