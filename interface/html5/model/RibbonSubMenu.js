var RibbonSubMenu = Base.extend( {

	defaults: {
		label: null,
		id: null,
		icon: null,
		group: null,
		visible: null,
		type: null,
		items: null, //For Nav Type
		permission: null,
		permission_result: true
	},

	constructor: function() {

		this._super( 'constructor', arguments[0] );

		if ( !this.get( 'type' ) ) {
			this.set( 'type', RibbonSubMenuType.NORMAL );
		}

		this.set( 'icon', this.icon = Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + this.get( 'icon' ) ) );

		if ( this.get( 'permission_result' ) ) { //Only save maps for menus passed validation
			TopMenuManager.menus_quick_map[this.get( 'id' )] = this.get( 'group' ).get( 'ribbon_menu' ).get( 'id' );
			this.get( 'group' ).get( 'sub_menus' ).push( this );
		}

	}

} )

var RibbonSubMenuType = (function() {
	var normal = '1';
	var nav = '2';

	return {NORMAL: normal,
		NAVIGATION: nav}

})();