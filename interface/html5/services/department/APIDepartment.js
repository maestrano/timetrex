var APIDepartment = ServiceCaller.extend( {

	key_name: 'Department',
	className: 'APIDepartment',

	getDepartment: function() {

		return this.argumentsHandler( this.className, 'getDepartment', arguments );

	},

	getDepartmentDefaultData: function() {

		return this.argumentsHandler( this.className, 'getDepartmentDefaultData', arguments );

	},

	getCommonDepartmentData: function() {

		return this.argumentsHandler( this.className, 'getCommonDepartmentData', arguments );

	},

	validateDepartment: function() {

		return this.argumentsHandler( this.className, 'validateDepartment', arguments );

	},

	setDepartment: function() {

		return this.argumentsHandler( this.className, 'setDepartment', arguments );

	},

	deleteDepartment: function() {

		return this.argumentsHandler( this.className, 'deleteDepartment', arguments );

	},

	copyDepartment: function() {

		return this.argumentsHandler( this.className, 'copyDepartment', arguments );

	}


} );