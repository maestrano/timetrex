var APIMisc = ServiceCaller.extend( {

	key_name: 'Misc',
	className: 'APIMisc',

	isLoggedIn: function() {

		return this.argumentsHandler( 'APIAuthentication', 'isLoggedIn', arguments );

	},

	ping: function() {

		return this.argumentsHandler( this.className, 'getRawData', arguments );

	}

} );