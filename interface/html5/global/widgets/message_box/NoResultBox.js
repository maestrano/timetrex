(function( $ ) {

	$.fn.NoResultBox = function( options ) {

		Global.addCss( 'global/widgets/message_box/NoResultBox.css' );
		var opts = $.extend( {}, $.fn.NoResultBox.defaults, options );
		var related_view_controller;
		var message = Global.no_result_message;

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.related_view_controller ) {
				related_view_controller = o.related_view_controller;
			}

			if ( o.message ) {
				message = o.message;
			}

			var ribbon_button = $( this ).find( '.ribbon-button' );
			var ribbon_button_div = $( this ).find( '.add-div' );
			var label = $( this ).find( '.label' );
			var icon = $( this ).find( '.icon' );
			var message_div = $( this ).find( '.message' );

			ribbon_button_div.css( 'display', 'block' );

			if ( o.is_new ) {

				icon.attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + Icons.new_add ) );
				label.text( $.i18n._( 'New' ) );

				ribbon_button.bind( 'click', function() {
					related_view_controller.onAddClick();
				} );


			} else if ( o.is_edit ) {

				icon.attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + Icons.edit ) );
				label.text( $.i18n._( 'Edit' ) );

				ribbon_button.bind( 'click', function() {
					related_view_controller.onEditClick();
				} );

			} else {

				ribbon_button_div.css( 'display', 'none' );
			}

			message_div.text( message );

		} );

		return this;

	};

	$.fn.NoResultBox.defaults = {

	};

})( jQuery );