var APIPermission = ServiceCaller.extend( {

	key_name: 'Permission',
	className: 'APIPermission',

	getPermission: function() {

		return this.argumentsHandler( this.className, 'getPermissions', arguments );

	},

	getUniqueCountry: function() {
		return this.argumentsHandler( this.className, 'getUniqueCountry', arguments );
	},

	getSectionBySectionGroup: function() {
		return this.argumentsHandler( this.className, 'getSectionBySectionGroup', arguments );
	},

	filterPresetPermissions: function() {
		return this.argumentsHandler( this.className, 'filterPresetPermissions', arguments );
	}







} );