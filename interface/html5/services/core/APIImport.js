var APIImport = ServiceCaller.extend( {

	key_name: 'Import',
	className: 'APIImport',

	getImportObjects: function() {

		return this.argumentsHandler( this.className, 'getImportObjects', arguments );

	},

	getRawData: function() {

		return this.argumentsHandler( this.className, 'getRawData', arguments );

	},

	generateColumnMap: function() {

		return this.argumentsHandler( this.className, 'generateColumnMap', arguments );

	},

	import: function() {

		return this.argumentsHandler( this.className, 'Import', arguments );

	}

} );