(function( $ ) {

	$.fn.SeparatedBox = function( options ) {
		var opts = $.extend( {}, $.fn.SeparatedBox.defaults, options );

		var $this = this;

		var label = '';

		var field;

		this.getField = function() {
			return field;
		};

		this.setValue = function() {

		};

		this.getValue = function() {

		};

		this.setErrorStyle = function( errStr, show ) {

		};

		this.showErrorTip = function( sec ) {

		};

		this.hideErrorTip = function() {

		};

		this.clearErrorStyle = function() {

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			var label_span = $( this ).find( '.label' );

			label = o.label;

			label_span.text( label );

		} );

		return this;

	};

	$.fn.SeparatedBox.defaults = {

	};

})( jQuery );