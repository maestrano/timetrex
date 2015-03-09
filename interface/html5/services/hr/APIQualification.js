var APIQualification = ServiceCaller.extend( {

	key_name: 'Qualification',
	className: 'APIQualification',

	getCommonQualificationData: function() {

		return this.argumentsHandler( this.className, 'getCommonQualificationData', arguments );

	},

	getQualification: function() {

		return this.argumentsHandler( this.className, 'getQualification', arguments );

	},

	setQualification: function() {

		return this.argumentsHandler( this.className, 'setQualification', arguments );

	},

	getQualificationDefaultData: function() {

		return this.argumentsHandler( this.className, 'getQualificationDefaultData', arguments );

	},

	deleteQualification: function() {

		return this.argumentsHandler( this.className, 'deleteQualification', arguments );

	},

	validateQualification: function() {

		return this.argumentsHandler( this.className, 'validateQualification', arguments );

	},

	copyQualification: function() {

		return this.argumentsHandler( this.className, 'copyQualification', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );