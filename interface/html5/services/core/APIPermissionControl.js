var APIPermissionControl = ServiceCaller.extend( {

	key_name: 'PermissionControl',
	className: 'APIPermissionControl',

	getPermissionControl: function() {

		return this.argumentsHandler( this.className, 'getPermissionControl', arguments );

	},
	getPermissionControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getPermissionControlDefaultData', arguments );

	},
	getPermissionOptions: function() {

		return this.argumentsHandler( this.className, 'getPermissionOptions', arguments );

	},
	getCommonPermissionControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonPermissionControlData', arguments );

	},
	validatePermissionControl: function() {

		return this.argumentsHandler( this.className, 'validatePermissionControl', arguments );

	},
	setPermissionControl: function() {

		return this.argumentsHandler( this.className, 'setPermissionControl', arguments );

	},
	deletePermissionControl: function() {

		return this.argumentsHandler( this.className, 'deletePermissionControl', arguments );

	},
	copyPermissionControl: function() {

		return this.argumentsHandler( this.className, 'copyPermissionControl', arguments );

	}



} );