var APIUserReportData = ServiceCaller.extend( {

	key_name: 'UserReportData',
	className: 'APIUserReportData',

	shareUserReportData: function() {

		return this.argumentsHandler( this.className, 'shareUserReportData', arguments );

	},

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonUserReportDataData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserReportDataData', arguments );

	},

	getUserReportData: function() {

		return this.argumentsHandler( this.className, 'getUserReportData', arguments );

	},

	setUserReportData: function() {

		return this.argumentsHandler( this.className, 'setUserReportData', arguments );

	},

	getUserReportDataDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserReportDataDefaultData', arguments );

	},

	deleteUserReportData: function() {

		return this.argumentsHandler( this.className, 'deleteUserReportData', arguments );

	},

	validateUserReportData: function() {

		return this.argumentsHandler( this.className, 'validateUserReportData', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	}



} );