(function( $ ) {

	$.fn.NoHierarchyBox = function( options ) {
		var opts = $.extend( {}, $.fn.NoHierarchyBox.defaults, options );
		var field;
		var related_view_controller;

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.related_view_controller ) {
				related_view_controller = o.related_view_controller;
			}

			var label = $( this ).find( '.label' );
			var icon = $( this ).find( '.icon' );
			var message = $( this ).find( '.message' );

			var ribbon_button = $( this ).find( '.ribbon-button' );
			;

			message.text( Global.no_hierarchy_message );
			icon.attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + Icons.hierarchy ) );
			label.html( $.i18n._( 'Hierarchy' ) );

			var len = related_view_controller.context_menu_array.length;

			if ( !PermissionManager.checkTopLevelPermission( 'HierarchyControl' ) ) {
				ribbon_button.addClass( 'disable-image' )
			}

			ribbon_button.bind( 'click', function() {

				if ( ribbon_button.hasClass( 'disable-image' ) ) {
					return;
				}
				TopMenuManager.goToView( 'HierarchyControl' );
			} );

		} );

		return this;

	};

	$.fn.NoHierarchyBox.defaults = {};

})( jQuery );