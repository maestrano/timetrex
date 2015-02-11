var APIAbsencePolicy = ServiceCaller.extend( {

	key_name: 'AbsencePolicy',
	className: 'APIAbsencePolicy',

	getAbsencePolicyTotalData: function() {

		return this.argumentsHandler( this.className, 'getAbsencePolicyTotalData', arguments );

	},


	getProjectedAbsencePolicyBalance: function() {

		return this.argumentsHandler( this.className, 'getProjectedAbsencePolicyBalance', arguments );

	},

	getAbsencePolicyBalance: function() {

		return this.argumentsHandler( this.className, 'getAbsencePolicyBalance', arguments );

	},

	getAbsencePolicyData: function() {

		return this.argumentsHandler( this.className, 'getAbsencePolicyData', arguments );

	},

	getCommonAbsencePolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonAbsencePolicyData', arguments );

	},

	getAbsencePolicy: function() {

		return this.argumentsHandler( this.className, 'getAbsencePolicy', arguments );

	},

	setAbsencePolicy: function() {

		return this.argumentsHandler( this.className, 'setAbsencePolicy', arguments );

	},

	getAbsencePolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAbsencePolicyDefaultData', arguments );

	},

	deleteAbsencePolicy: function() {

		return this.argumentsHandler( this.className, 'deleteAbsencePolicy', arguments );

	},

	validateAbsencePolicy: function() {

		return this.argumentsHandler( this.className, 'validateAbsencePolicy', arguments );

	},

	copyAbsencePolicy: function() {

		return this.argumentsHandler( this.className, 'copyAbsencePolicy', arguments );

	}



} );