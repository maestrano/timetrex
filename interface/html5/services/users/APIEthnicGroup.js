var APIEthnicGroup = ServiceCaller.extend( {

	key_name: 'EthnicGroup',
	className: 'APIEthnicGroup',

	getEthnicGroupDefaultData: function() {
		return this.argumentsHandler( this.className, 'getEthnicGroupDefaultData', arguments );

	},

	getEthnicGroup: function() {
		return this.argumentsHandler( this.className, 'getEthnicGroup', arguments );
	},

	getCommonEthnicGroupData: function() {

		return this.argumentsHandler( this.className, 'getCommonEthnicGroupData', arguments );

	},

	validateEthnicGroup: function() {

		return this.argumentsHandler( this.className, 'validateEthnicGroup', arguments );

	},

	setEthnicGroup: function() {

		return this.argumentsHandler( this.className, 'setEthnicGroup', arguments );

	},

	deleteEthnicGroup: function() {

		return this.argumentsHandler( this.className, 'deleteEthnicGroup', arguments );

	},

	copyEthnicGroup: function() {

		return this.argumentsHandler( this.className, 'copyEthnicGroup', arguments );

	}



} );