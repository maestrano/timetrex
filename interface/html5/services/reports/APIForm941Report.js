var APIForm941Report = ServiceCaller.extend( {

	key_name: 'Form941Report',
	className: 'APIForm941Report',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonForm941ReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonForm941ReportData', arguments );

	},

	getForm941Report: function() {

		return this.argumentsHandler( this.className, 'getForm941Report', arguments );

	},

	setForm941Report: function() {

		return this.argumentsHandler( this.className, 'setForm941Report', arguments );

	},

	getForm941ReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getForm941ReportDefaultData', arguments );

	},

	deleteForm941Report: function() {

		return this.argumentsHandler( this.className, 'deleteForm941Report', arguments );

	},

	validateForm941Report: function() {

		return this.argumentsHandler( this.className, 'validateForm941Report', arguments );

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