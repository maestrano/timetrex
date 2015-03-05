var APIUserEducation = ServiceCaller.extend( {

	key_name: 'UserEducation',
	className: 'APIUserEducation',

	getUserEducationDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserEducationDefaultData', arguments );

	},

	getUserEducation: function() {

		return this.argumentsHandler( this.className, 'getUserEducation', arguments );

	},

	getCommonUserEducationData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserEducationData', arguments );

	},

	validateUserEducation: function() {

		return this.argumentsHandler( this.className, 'validateUserEducation', arguments );

	},

	setUserEducation: function() {

		return this.argumentsHandler( this.className, 'setUserEducation', arguments );

	},

	deleteUserEducation: function() {

		return this.argumentsHandler( this.className, 'deleteUserEducation', arguments );

	},

	copyUserEducation: function() {
		return this.argumentsHandler( this.className, 'copyUserEducation', arguments );
	}


} );