var APILog = ServiceCaller.extend( {
	key_name: 'Log',
	className: 'APILog',

	getLog: function() {
		return this.argumentsHandler( this.className, 'getLog', arguments );
	},

	validateLog: function() {
		return this.argumentsHandler( this.className, 'validateLog', arguments );
	},

	setLog: function() {
		return this.argumentsHandler( this.className, 'setLog', arguments );
	}


} )