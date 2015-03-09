var APIQualificationGroup = ServiceCaller.extend( {

	key_name: 'QualificationGroup',
	className: 'APIQualificationGroup',

	getCommonQualificationGroupData: function() {

		return this.argumentsHandler( this.className, 'getCommonQualificationGroupData', arguments );

	},

	getQualificationGroup: function() {

		return this.argumentsHandler( this.className, 'getQualificationGroup', arguments );

	},

	setQualificationGroup: function() {

		return this.argumentsHandler( this.className, 'setQualificationGroup', arguments );

	},

	getQualificationGroupDefaultData: function() {

		return this.argumentsHandler( this.className, 'getQualificationGroupDefaultData', arguments );

	},

	deleteQualificationGroup: function() {

		return this.argumentsHandler( this.className, 'deleteQualificationGroup', arguments );

	},

	validateQualificationGroup: function() {

		return this.argumentsHandler( this.className, 'validateQualificationGroup', arguments );

	},

	copyQualificationGroup: function() {

		return this.argumentsHandler( this.className, 'copyQualificationGroup', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );