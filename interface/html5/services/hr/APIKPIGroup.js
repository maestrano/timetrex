var APIKPIGroup = ServiceCaller.extend( {

	key_name: 'KPIGroup',
	className: 'APIKPIGroup',

	getCommonKPIGroupData: function() {

		return this.argumentsHandler( this.className, 'getCommonKPIGroupData', arguments );

	},

	getKPIGroup: function() {

		return this.argumentsHandler( this.className, 'getKPIGroup', arguments );

	},

	setKPIGroup: function() {

		return this.argumentsHandler( this.className, 'setKPIGroup', arguments );

	},

	getKPIGroupDefaultData: function() {

		return this.argumentsHandler( this.className, 'getKPIGroupDefaultData', arguments );

	},

	deleteKPIGroup: function() {

		return this.argumentsHandler( this.className, 'deleteKPIGroup', arguments );

	},

	validateKPIGroup: function() {

		return this.argumentsHandler( this.className, 'validateKPIGroup', arguments );

	},

	copyKPIGroup: function() {

		return this.argumentsHandler( this.className, 'copyKPIGroup', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );