var APIAbout = ServiceCaller.extend( {

	key_name: 'AboutData',
	className: 'APIAbout',

	getAboutData: function() {

		return this.argumentsHandler( this.className, 'getAboutData', arguments );

	},

	isNewVersionAvailable: function() {

		return this.argumentsHandler( this.className, 'isNewVersionAvailable', arguments );

	}


} );