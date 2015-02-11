var APICompany = ServiceCaller.extend( {

	key_name: 'Company',
	className: 'APICompany',

	createPresets: function() {

		return this.argumentsHandler( 'APISetupPresets', 'createPresets', arguments );

	},

	validateCompany: function() {

		return this.argumentsHandler( this.className, 'validateCompany', arguments );

	},

	getCompany: function() {

		return this.argumentsHandler( this.className, 'getCompany', arguments );

	},

	getCompanyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getCompanyDefaultData', arguments );

	},

	getCommonCompanyData: function() {

		return this.argumentsHandler( this.className, 'getCommonCompanyData', arguments );

	},

	setCompany: function() {

		return this.argumentsHandler( this.className, 'setCompany', arguments );

	},

	deleteCompany: function() {

		return this.argumentsHandler( this.className, 'deleteCompany', arguments );

	},

	copyCompany: function() {

		return this.argumentsHandler( this.className, 'copyCompany', arguments );

	},

	getCompanyMinAvgMaxUserCounts: function() {

		return this.argumentsHandler( this.className, 'getCompanyMinAvgMaxUserCounts', arguments );

	},

	getCompanyEmailAddresses: function() {

		return this.argumentsHandler( this.className, 'getCompanyEmailAddresses', arguments );

	},

	getCompanyPhonePunchData: function() {

		return this.argumentsHandler( this.className, 'getCompanyPhonePunchData', arguments );

	},

	isBranchAndDepartmentAndJobAndJobItemEnabled: function() {

		return this.argumentsHandler( this.className, 'isBranchAndDepartmentAndJobAndJobItemEnabled', arguments );

	}






} );