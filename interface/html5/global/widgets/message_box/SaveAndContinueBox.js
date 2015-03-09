(function( $ ) {

	$.fn.SaveAndContinueBox = function( options ) {
		var opts = $.extend( {}, $.fn.SaveAndContinueBox.defaults, options );
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

			message.text( Global.save_and_continue_message );
			icon.attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + Icons.save_and_continue ) );
			label.html( $.i18n._( 'Save<br>& Continue' ) );

			var len = related_view_controller.context_menu_array.length;

			for ( var i = 0; i < len; i++ ) {
				var context_btn = related_view_controller.context_menu_array[i];
				var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
				if ( id === ContextMenuIconName.save_and_continue ) {
					if ( context_btn.hasClass( 'invisible-image' ) || context_btn.hasClass( 'disable-image' ) ) {
						ribbon_button.addClass( 'disable-image' )
					}
				}

			}

			ribbon_button.bind( 'click', function() {

				if ( ribbon_button.hasClass( 'disable-image' ) ) {
					return;
				}
				related_view_controller.onSaveAndContinue();
			} );

		} );

		return this;

	};

	$.fn.SaveAndContinueBox.defaults = {

	};

})( jQuery );