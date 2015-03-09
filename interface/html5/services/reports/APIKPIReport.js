var APIKPIReport = ServiceCaller.extend( {

	key_name: 'KPIReport',
	className: 'APIKPIReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonKPIReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonKPIReportData', arguments );

	},

	getKPIReport: function() {

		return this.argumentsHandler( this.className, 'getKPIReport', arguments );

	},

	setKPIReport: function() {

		return this.argumentsHandler( this.className, 'setKPIReport', arguments );

	},

	getKPIReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getKPIReportDefaultData', arguments );

	},

	deleteKPIReport: function() {

		return this.argumentsHandler( this.className, 'deleteKPIReport', arguments );

	},

	validateKPIReport: function() {

		return this.argumentsHandler( this.className, 'validateKPIReport', arguments );

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