var APIUserQualificationReport = ServiceCaller.extend( {

	key_name: 'UserQualificationReport',
	className: 'APIUserQualificationReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonUserQualificationReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserQualificationReportData', arguments );

	},

	getUserQualificationReport: function() {

		return this.argumentsHandler( this.className, 'getUserQualificationReport', arguments );

	},

	setUserQualificationReport: function() {

		return this.argumentsHandler( this.className, 'setUserQualificationReport', arguments );

	},

	getUserQualificationReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserQualificationReportDefaultData', arguments );

	},

	deleteUserQualificationReport: function() {

		return this.argumentsHandler( this.className, 'deleteUserQualificationReport', arguments );

	},

	validateUserQualificationReport: function() {

		return this.argumentsHandler( this.className, 'validateUserQualificationReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	},

	setCompanyFormConfig: function() {

		return this.argumentsHandler( this.className, 'setCompanyFormConfig', arguments );

	},

	getCompanyFormConfig: function() {

		return this.argumentsHandler( this.className, 'getCompanyFormConfig', arguments );

	}



} );