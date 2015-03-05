var APIUserLicense = ServiceCaller.extend( {

	key_name: 'UserLicense',
	className: 'APIUserLicense',

	getCommonUserLicenseData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserLicenseData', arguments );

	},

	getUserLicense: function() {

		return this.argumentsHandler( this.className, 'getUserLicense', arguments );

	},

	setUserLicense: function() {

		return this.argumentsHandler( this.className, 'setUserLicense', arguments );

	},

	getUserLicenseDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserLicenseDefaultData', arguments );

	},

	deleteUserLicense: function() {

		return this.argumentsHandler( this.className, 'deleteUserLicense', arguments );

	},

	validateUserLicense: function() {

		return this.argumentsHandler( this.className, 'validateUserLicense', arguments );

	},

	copyUserLicense: function() {

		return this.argumentsHandler( this.className, 'copyUserLicense', arguments );

	}



} );