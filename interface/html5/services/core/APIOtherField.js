var APIOtherField = ServiceCaller.extend( {

	key_name: 'OtherField',
	className: 'APIOtherField',

	getOtherFieldDefaultData: function() {

		return this.argumentsHandler( this.className, 'getOtherFieldDefaultData', arguments );

	},

	getOtherField: function() {

		return this.argumentsHandler( this.className, 'getOtherField', arguments );

	},

	getCommonOtherFieldData: function() {

		return this.argumentsHandler( this.className, 'getCommonOtherFieldData', arguments );

	},

	validateOtherField: function() {

		return this.argumentsHandler( this.className, 'validateOtherField', arguments );

	},

	setOtherField: function() {

		return this.argumentsHandler( this.className, 'setOtherField', arguments );

	},

	deleteOtherField: function() {

		return this.argumentsHandler( this.className, 'deleteOtherField', arguments );

	}


} );