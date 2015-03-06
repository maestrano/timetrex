var RibbonSubMenuGroup = Base.extend( {

	defaults: {

		label: null,
		id: null,
		ribbon_menu: null,
		sub_menus: null
	},

	constructor: function() {

		this._super( 'constructor', arguments[0] );

		this.get( 'ribbon_menu' ).get( 'sub_menu_groups' ).push( this );

	}

} )