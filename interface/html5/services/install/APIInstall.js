var APIInstall = ServiceCaller.extend( {

	key_name: 'Install',
	className: 'APIInstall',

	getLicense: function() {

		return this.argumentsHandler( this.className, 'getLicense', arguments );

	},

	getRequirements: function() {

		return this.argumentsHandler( this.className, 'getRequirements', arguments );

	},

	getDatabaseTypeArray: function() {

		return this.argumentsHandler( this.className, 'getDatabaseTypeArray', arguments );

	},

	getDatabaseConfig: function() {

		return this.argumentsHandler( this.className, 'getDatabaseConfig', arguments );

	},

	createDatabase: function() {
		return this.argumentsHandler( this.className, 'createDatabase', arguments );
	},

	getDatabaseSchema: function() {
		return this.argumentsHandler( this.className, 'getDatabaseSchema', arguments );
	},

	setDatabaseSchema: function() {
		return this.argumentsHandler( this.className, 'setDatabaseSchema', arguments );
	},

	postUpgrade: function() {
		return this.argumentsHandler( this.className, 'postUpgrade', arguments );
	},

	installDone: function() {
		return this.argumentsHandler( this.className, 'installDone', arguments );
	},

	getSystemSettings: function() {
		return this.argumentsHandler( this.className, 'getSystemSettings', arguments );
	},

	setSystemSettings: function() {
		return this.argumentsHandler( this.className, 'setSystemSettings', arguments );
	},

	getCompany: function() {
		return this.argumentsHandler( this.className, 'getCompany', arguments );
	},

	setCompany: function() {
		return this.argumentsHandler( this.className, 'setCompany', arguments );
	},

	getUser: function() {
		return this.argumentsHandler( this.className, 'getUser', arguments );
	},

	setUser: function() {
		return this.argumentsHandler( this.className, 'setUser', arguments );
	},


	getProvinceOptions: function() {
		return this.argumentsHandler( this.className, 'getProvinceOptions', arguments );
	},

	getMaintenanceJobs: function() {
		return this.argumentsHandler( this.className, 'getMaintenanceJobs', arguments );
	},

	testConnection: function() {
		return this.argumentsHandler( this.className, 'testConnection', arguments );
	}


} );