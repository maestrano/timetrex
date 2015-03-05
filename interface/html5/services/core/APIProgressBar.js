var APIProgressBar = ServiceCaller.extend( {
	key_name: 'ProgressBar',
	className: 'APIProgressBar',

	getProgressBar: function() {
		return this.argumentsHandler( this.className, 'get', arguments );
	}

} )