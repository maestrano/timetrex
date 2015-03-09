(function( $ ) {

	$.fn.RibbonSubMenuNavWidget = function( options ) {
		var opts = $.extend( {}, $.fn.RibbonSubMenuNavWidget.defaults, options );

		var $this = this;

		var is_mouse_over = false;

		this.close = function() {
			this.remove();
			LocalCacheData.openRibbonNaviMenu = null;
		};

		this.getIsMouseOver = function() {
			return is_mouse_over;
		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			$( this ).mouseenter( function() {
				is_mouse_over = true;
			} );

			$( this ).mouseleave( function() {
				is_mouse_over = false;
			} );

		} );

		return this;

	};

	$.fn.RibbonSubMenuNavWidget.defaults = {

	};

})( jQuery );