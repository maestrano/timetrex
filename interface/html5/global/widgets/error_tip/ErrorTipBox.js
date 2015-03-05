(function( $ ) {

	$.fn.ErrorTipBox = function( options ) {
		var opts = $.extend( {}, $.fn.ErrorTipBox.defaults, options );
		var $this = this;
		var related_widget;
		var timer;

		Global.addCss( 'global/widgets/error_tip/ErrorTipBox.css' );

		this.cancelRemove = function() {
			if ( Global.isSet( timer ) ) {
				clearTimeout( timer );
			}

		};

		this.show = function( target, error_string, sec ) {
			related_widget = target;

			if ( $.type( error_string ) === 'array' ) {
				error_string = error_string.join( '<br>' );
			}

			var error_tip_label = $( this ).find( '.errortip-label' );
			error_tip_label.html( error_string );

			$( this ).css( 'left', related_widget.offset().left + related_widget.width() + 5 );

			if ( related_widget.hasClass( 'a-combobox' ) ) {
				$( this ).css( 'top', related_widget.offset().top + 1 );
			} else {
				$( this ).css( 'top', related_widget.offset().top - 2 );
			}

			$( 'body' ).append( this );

			if ( sec > 0 ) {
				timer = setTimeout( function() {
					$this.remove();
				}, sec * 1000 );
			}
		}

		this.remove = function() {
			$( this ).remove();
		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

		} );

		return this;

	};

	$.fn.ErrorTipBox.defaults = {

	};

})( jQuery );