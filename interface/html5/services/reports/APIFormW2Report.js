var APIFormW2Report = ServiceCaller.extend( {

	key_name: 'FormW2Report',
	className: 'APIFormW2Report',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonFormW2ReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonFormW2ReportData', arguments );

	},

	getFormW2Report: function() {

		return this.argumentsHandler( this.className, 'getFormW2Report', arguments );

	},

	setFormW2Report: function() {

		return this.argumentsHandler( this.className, 'setFormW2Report', arguments );

	},

	getFormW2ReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getFormW2ReportDefaultData', arguments );

	},

	deleteFormW2Report: function() {

		return this.argumentsHandler( this.className, 'deleteFormW2Report', arguments );

	},

	validateFormW2Report: function() {

		return this.argumentsHandler( this.className, 'validateFormW2Report', arguments );

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