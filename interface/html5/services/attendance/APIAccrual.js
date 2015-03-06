var APIAccrual = ServiceCaller.extend( {

	key_name: 'Accrual',
	className: 'APIAccrual',

	getCommonAccrualData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualData', arguments );

	},

	getAccrual: function() {

		return this.argumentsHandler( this.className, 'getAccrual', arguments );

	},

	setAccrual: function() {

		return this.argumentsHandler( this.className, 'setAccrual', arguments );

	},

	getAccrualDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualDefaultData', arguments );

	},

	deleteAccrual: function() {

		return this.argumentsHandler( this.className, 'deleteAccrual', arguments );

	},

	validateAccrual: function() {

		return this.argumentsHandler( this.className, 'validateAccrual', arguments );

	},

	copyAccrual: function() {

		return this.argumentsHandler( this.className, 'copyAccrual', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );