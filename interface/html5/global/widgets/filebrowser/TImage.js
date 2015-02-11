(function( $ ) {

	$.fn.TImage = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImage.defaults, options );

		var $this = this;
		var field;

		this.clearErrorStyle = function() {

		}

		this.getField = function() {
			return field;
		}

		this.getValue = function() {
			return	null;
		}

		this.setValue = function( val ) {
			if ( !val ) {
				this.attr( 'src', '' );
				return;
			}
			var d = new Date();
			this.attr( 'src', val + '&t=' + d.getTime() );

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

		} );

		return this;

	};

	$.fn.TImage.defaults = {

	};

})( jQuery );