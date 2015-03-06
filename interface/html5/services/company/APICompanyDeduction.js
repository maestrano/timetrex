var APICompanyDeduction = ServiceCaller.extend( {

	key_name: 'CompanyDeduction',
	className: 'APICompanyDeduction',

	getCompanyDeduction: function() {
		return this.argumentsHandler( this.className, 'getCompanyDeduction', arguments );

	},

	getCompanyDeductionDefaultData: function() {
		return this.argumentsHandler( this.className, 'getCompanyDeductionDefaultData', arguments );

	},

	getCommonCompanyDeductionData: function() {
		return this.argumentsHandler( this.className, 'getCommonCompanyDeductionData', arguments );

	},

	validateCompanyDeduction: function() {
		return this.argumentsHandler( this.className, 'validateCompanyDeduction', arguments );

	},

	setCompanyDeduction: function() {
		return this.argumentsHandler( this.className, 'setCompanyDeduction', arguments );

	},

	deleteCompanyDeduction: function() {
		return this.argumentsHandler( this.className, 'deleteCompanyDeduction', arguments );

	},

	copyCompanyDeduction: function() {
		return this.argumentsHandler( this.className, 'copyCompanyDeduction', arguments );

	},

	getCombinedCalculationID: function() {
		return this.argumentsHandler( this.className, 'getCombinedCalculationID', arguments );

	},

	isCountryCalculationID: function() {
		return this.argumentsHandler( this.className, 'isCountryCalculationID', arguments );

	},

	isProvinceCalculationID: function() {
		return this.argumentsHandler( this.className, 'isProvinceCalculationID', arguments );

	},

	isDistrictCalculationID: function() {
		return this.argumentsHandler( this.className, 'isDistrictCalculationID', arguments );

	}



} );