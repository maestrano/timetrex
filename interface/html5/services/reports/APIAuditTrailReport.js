var APIAuditTrailReport = ServiceCaller.extend( {

	key_name: 'AuditTrailReport',
	className: 'APIAuditTrailReport',

	getTemplate: function() {

		return this.argumentsHandler( this.className, 'getTemplate', arguments );

	},

	getCommonAuditTrailReportData: function() {

		return this.argumentsHandler( this.className, 'getCommonAuditTrailReportData', arguments );

	},

	getAuditTrailReport: function() {

		return this.argumentsHandler( this.className, 'getAuditTrailReport', arguments );

	},

	setAuditTrailReport: function() {

		return this.argumentsHandler( this.className, 'setAuditTrailReport', arguments );

	},

	getAuditTrailReportDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAuditTrailReportDefaultData', arguments );

	},

	deleteAuditTrailReport: function() {

		return this.argumentsHandler( this.className, 'deleteAuditTrailReport', arguments );

	},

	validateAuditTrailReport: function() {

		return this.argumentsHandler( this.className, 'validateAuditTrailReport', arguments );

	},

	validateReport: function() {

		return this.argumentsHandler( this.className, 'validateReport', arguments );

	},

	copyAuditTrailReport: function() {

		return this.argumentsHandler( this.className, 'copyAuditTrailReport', arguments );

	}



} );