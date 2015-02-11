(function( $ ) {

	$.fn.ASearchInput = function( options ) {
		var opts = $.extend( {}, $.fn.ADropDown.defaults, options );

		var column_model = null;

		var search_timer = null;

		var $this = this;
		var default_tooltip = 'click to search';

		this.setFilter = function( filters ) {
			var field = column_model.name;

			if ( Global.isSet( filters[field] ) ) {
				$( this ).val( filters[field] );
				$( this ).addClass( 'search-input-focus-in' );
			}
		}

		this.clearValue = function() {
			$( this ).val( default_tooltip );
			$( this ).removeClass( 'search-input-focus-in' );
			$( this ).addClass( 'search-input-focus-out' );
		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.column_model ) {

				column_model = o.column_model;
			}

			$( this ).addClass( 'search-input-focus-out' );

			$( this ).val( default_tooltip );

			$( this ).focusin( function() {

				if ( $( this ).val() === default_tooltip ) {
					$( this ).val( '' );
					$( this ).addClass( 'search-input-focus-in' );
				}

			} );

			$( this ).focusout( function() {
				if ( $( this ).val() === '' ) {
					$( this ).val( default_tooltip );
					$( this ).removeClass( 'search-input-focus-in' );
				}

			} );

			$( this ).bind( 'input propertychange', function( e ) {

				if ( search_timer ) {
					clearTimeout( search_timer );
				}

				if ( e.keyCode === 91 || e.ctrlKey || e.metaKey || e.keyCode === 17 ) {
					return;
				}

				search_timer = setTimeout( function() {

					var val = ($this.val() === default_tooltip) ? '' : $this.val();

					$this.trigger( 'searchEnter', [val, column_model.name] );

				}, 500 );

			} );

		} );

		return this;

	};

	$.fn.ASearchInput.defaults = {

	};

})( jQuery );