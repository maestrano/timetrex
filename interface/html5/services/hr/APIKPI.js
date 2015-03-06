var APIKPI = ServiceCaller.extend( {

	key_name: 'KPI',
	className: 'APIKPI',

	getCommonKPIData: function() {

		return this.argumentsHandler( this.className, 'getCommonKPIData', arguments );

	},

	getKPI: function() {

		return this.argumentsHandler( this.className, 'getKPI', arguments );

	},

	setKPI: function() {

		return this.argumentsHandler( this.className, 'setKPI', arguments );

	},

	getKPIDefaultData: function() {

		return this.argumentsHandler( this.className, 'getKPIDefaultData', arguments );

	},

	deleteKPI: function() {

		return this.argumentsHandler( this.className, 'deleteKPI', arguments );

	},

	validateKPI: function() {

		return this.argumentsHandler( this.className, 'validateKPI', arguments );

	},

	copyKPI: function() {

		return this.argumentsHandler( this.className, 'copyKPI', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );