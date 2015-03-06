var APIForm940Report = ServiceCaller.extend( {

	key_name: 'Form940Report',
	className: 'APIForm940Report',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonForm940ReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonForm940ReportData', arguments );

	},

	getForm940Report: function() {

		return this.argumentsHandler( this.className, 'getForm940Report', arguments );

	},

	setForm940Report: function() {

		return this.argumentsHandler( this.className, 'setForm940Report', arguments );

	},

	getForm940ReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getForm940ReportDefaultData', arguments );

	},

	deleteForm940Report: function() {

		return this.argumentsHandler( this.className, 'deleteForm940Report', arguments );

	},

	validateForm940Report: function() {

		return this.argumentsHandler( this.className, 'validateForm940Report', arguments );

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