var APIHierarchyLevel = ServiceCaller.extend( {

	key_name: 'HierarchyLevel',
	className: 'APIHierarchyLevel',

	validateHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'validateHierarchyLevel', arguments );

	},

	getHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'getHierarchyLevel', arguments );

	},

	getHierarchyLevelDefaultData: function() {

		return this.argumentsHandler( this.className, 'getHierarchyLevelDefaultData', arguments );

	},

	getCommonHierarchyLevelData: function() {

		return this.argumentsHandler( this.className, 'getCommonHierarchyLevelData', arguments );

	},

	setHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'setHierarchyLevel', arguments );

	},

	deleteHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'deleteHierarchyLevel', arguments );

	},

	copyHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'copyHierarchyLevel', arguments );

	},


	getHierarchyLevelOptions: function() {

		return this.argumentsHandler( this.className, 'getHierarchyLevelOptions', arguments );

	},

	ReMapHierarchyLevels: function() {

		return this.argumentsHandler( this.className, 'ReMapHierarchyLevels', arguments );

	}



} );