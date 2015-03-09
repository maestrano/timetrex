var Base = Backbone.Model.extend( {

	defaults: {

	},

	fromJSONToAttributes: function( data ) {
		var handler = this;
		handler.set( data );

		return handler;
	}


} );