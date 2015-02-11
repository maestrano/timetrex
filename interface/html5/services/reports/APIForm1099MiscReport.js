var APIForm1099MiscReport = ServiceCaller.extend( {

	key_name: 'Form1099MiscReport',
	className: 'APIForm1099MiscReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonForm1099MiscReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonForm1099MiscReportData', arguments );

	},

	getForm1099MiscReport: function() {

		return this.argumentsHandler( this.className, 'getForm1099MiscReport', arguments );

	},

	setForm1099MiscReport: function() {

		return this.argumentsHandler( this.className, 'setForm1099MiscReport', arguments );

	},

	getForm1099MiscReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getForm1099MiscReportDefaultData', arguments );

	},

	deleteForm1099MiscReport: function() {

		return this.argumentsHandler( this.className, 'deleteForm1099MiscReport', arguments );

	},

	validateForm1099MiscReport: function() {

		return this.argumentsHandler( this.className, 'validateForm1099MiscReport', arguments );

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