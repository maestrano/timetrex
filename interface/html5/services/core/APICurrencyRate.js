var APICurrencyRate = ServiceCaller.extend( {


	key_name: 'CurrencyRate',
	className: 'APICurrencyRate',

	getCurrencyRate: function() {

		return this.argumentsHandler( this.className, 'getCurrencyRate', arguments );

	},
	getCurrencyRateDefaultData: function() {

		return this.argumentsHandler( this.className, 'getCurrencyRateDefaultData', arguments );

	},
	getCommonCurrencyRateData: function() {

		return this.argumentsHandler( this.className, 'getCommonCurrencyRateData', arguments );

	},
	validateCurrencyRate: function() {

		return this.argumentsHandler( this.className, 'validateCurrencyRate', arguments );

	},
	setCurrencyRate: function() {

		return this.argumentsHandler( this.className, 'setCurrencyRate', arguments );

	},
	deleteCurrencyRate: function() {

		return this.argumentsHandler( this.className, 'deleteCurrencyRate', arguments );

	},
	copyCurrencyRate: function() {

		return this.argumentsHandler( this.className, 'copyCurrencyRate', arguments );

	}



} );