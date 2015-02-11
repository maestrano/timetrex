var APIHierarchyControl = ServiceCaller.extend( {

	key_name: 'HierarchyControl',
	className: 'APIHierarchyControl',

	reMapHierarchyLevels: function() {

		return this.argumentsHandler( this.className, 'reMapHierarchyLevels', arguments );

	},

	setHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'setHierarchyLevel', arguments );

	},

	getHierarchyLevel: function() {

		return this.argumentsHandler( this.className, 'getHierarchyLevel', arguments );

	},

	getHierarchyControlOptions: function() {

		return this.argumentsHandler( this.className, 'getHierarchyControlOptions', arguments );

	},

	getCommonHierarchyControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonHierarchyControlData', arguments );

	},

	getHierarchyControl: function() {

		return this.argumentsHandler( this.className, 'getHierarchyControl', arguments );

	},

	setHierarchyControl: function() {

		return this.argumentsHandler( this.className, 'setHierarchyControl', arguments );

	},

	getHierarchyControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getHierarchyControlDefaultData', arguments );

	},

	deleteHierarchyControl: function() {

		return this.argumentsHandler( this.className, 'deleteHierarchyControl', arguments );

	},

	validateHierarchyControl: function() {

		return this.argumentsHandler( this.className, 'validateHierarchyControl', arguments );

	},

	copyHierarchyControl: function() {

		return this.argumentsHandler( this.className, 'copyHierarchyControl', arguments );

	}



} );