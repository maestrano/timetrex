(function( $ ) {

	$.fn.TToggleButton = function( options ) {

		Global.addCss( 'global/widgets/toggle_button/ToggleButton.css' );
		var opts = $.extend( {}, $.fn.TToggleButton.defaults, options );

		var data_provider = [];

		var $this = this;

		var btn_dic = {};

		var selected_btn;

		this.getValue = function() {
			return selected_btn ? selected_btn.val() : null;
		}

		this.setValue = function( val ) {
			if ( selected_btn ) {
				selected_btn.removeClass( 'selected' );
			}

			selected_btn = btn_dic[val];
			selected_btn.addClass( 'selected' )
		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			data_provider = o.data_provider;

			btn_dic = {};

			var len = data_provider.length;
			for ( var i = 0; i < len; i++ ) {
				var button_data = data_provider[i];
				var btn = $( "<button></button>" );
				if ( i === 0 ) {
					btn.addClass( 'toggle-button first' )
				} else if ( i === len - 1 ) {
					btn.addClass( 'toggle-button last' )
				} else if ( i === 0 && i === len - 1 ) {
					btn.addClass( 'toggle-button first-last' )
				} else {
					btn.addClass( 'toggle-button middle' )
				}

				btn_dic[button_data.value] = btn;

				btn.val( button_data.value );
				btn.text( button_data.label );

				btn.click( function() {
					$this.setValue( $( this ).val() );
					$this.trigger( 'change', [$this.getValue()] );
				} )

				$this.append( btn );
			}

		} );

		return this;

	};

	$.fn.TToggleButton.defaults = {

	};

})( jQuery );