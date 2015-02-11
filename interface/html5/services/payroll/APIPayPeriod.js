var APIPayPeriod = ServiceCaller.extend( {

	key_name: 'PayPeriod',
	className: 'APIPayPeriod',

	getPayPeriodDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPayPeriodDefaultData', arguments );

	},

	getPayPeriod: function() {

		return this.argumentsHandler( this.className, 'getPayPeriod', arguments );

	},
	getCommonPayPeriodData: function() {

		return this.argumentsHandler( this.className, 'getCommonPayPeriodData', arguments );

	},
	validatePayPeriod: function() {

		return this.argumentsHandler( this.className, 'validatePayPeriod', arguments );

	},
	setPayPeriod: function() {

		return this.argumentsHandler( this.className, 'setPayPeriod', arguments );

	},
	deletePayPeriod: function() {

		return this.argumentsHandler( this.className, 'deletePayPeriod', arguments );

	},
	importData: function() {

		return this.argumentsHandler( this.className, 'importData', arguments );

	},
	deleteData: function() {

		return this.argumentsHandler( this.className, 'deleteData', arguments );

	}


} );