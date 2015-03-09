var RibbonSubMenuNavItem = Base.extend( {

	defaults: {
		label: null,
		id: null,
		nav: null
	},

	constructor: function() {

		this._super( 'constructor', arguments[0] );

		this.get( 'nav' ).get( 'items' ).push( this );

	}

} )