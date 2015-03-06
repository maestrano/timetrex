(function( $ ) {

	$.fn.Paging2 = function( options ) {
		var opts = $.extend( {}, $.fn.Paging2.defaults, options );
		var $this = this;
		var pager_data;
		var start;
		var last;
		var next;
		var end;
		var paging_selector;
		var left_buttons_div;
		var right_buttons_div;

		var left_buttons_enable;
		var right_buttons_enable;

		this.setPagerData = function( value ) {

			pager_data = value;

			if ( !pager_data ) {
				$( this.css( 'display', 'none' ) );
				return;
			} else {
				$( this.css( 'display', 'block' ) );
			}

			$( paging_selector ).empty();

			var len = pager_data.last_page_number;

			if ( len === -1 ) {
				$( paging_selector ).append( '<option value="' + 1 + '">' + 1 + '</option>' )
			} else {
				for ( var i = 1; i <= len; i++ ) {
					$( paging_selector ).append( '<option value="' + i + '">' + i + '</option>' )
				}
			}

			$( $( paging_selector ).find( 'option' ) ).filter(function() {
				var current_value = parseInt( $( this ).attr( 'value' ) );

				return current_value === pager_data.current_page;
			} ).prop( 'selected', true ).attr( 'selected', true );

			if ( pager_data.is_last_page === true ) {
				right_buttons_div.addClass( 'disabled' );
				right_buttons_div.addClass( 'disabled-image' );
				right_buttons_enable = false;
			} else {
				right_buttons_div.removeClass( 'disabled' );
				right_buttons_div.removeClass( 'disabled-image' );
				right_buttons_enable = true;
			}

			if ( pager_data.is_first_page ) {
				left_buttons_div.addClass( 'disabled' );
				left_buttons_div.addClass( 'disabled-image' );
				left_buttons_enable = false;

			} else {
				left_buttons_div.removeClass( 'disabled' );
				left_buttons_div.removeClass( 'disabled-image' );
				left_buttons_enable = true;
			}

			if ( len === -1 || (pager_data.is_first_page && pager_data.is_last_page) ) {

				left_buttons_div.addClass( 'disabled' );
				left_buttons_div.addClass( 'disabled-image' );
				left_buttons_enable = false;
				right_buttons_div.addClass( 'disabled' );
				right_buttons_div.addClass( 'disabled-image' );
				right_buttons_enable = false;

				$this.hide();
			} else {
				$this.show();
			}

		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			var pages_label = $( this ).find( '.page-label-span' );

			pages_label.text( $.i18n._( 'Page' ) + ':' );

			left_buttons_div = $( this ).find( '.left-buttons-div' );
			right_buttons_div = $( this ).find( '.right-buttons-div' );

			start = $( this ).find( '.start' );
			last = $( this ).find( '.last' );
			next = $( this ).find( '.next' );
			end = $( this ).find( '.end' );
			paging_selector = $( this ).find( '.paging-selector' );

			start.text( $.i18n._( 'Start' ) );
			last.text( $.i18n._( 'Previous' ) );

			next.text( $.i18n._( 'Next' ) );
			end.text( $.i18n._( 'End' ) );

			$( this ).hide();

			start.click( function() {
				if ( left_buttons_enable ) {
					$this.trigger( 'paging', ['start'] );
				}
			} );

			last.click( function() {
				if ( left_buttons_enable ) {
					$this.trigger( 'paging', ['last'] );
				}
			} );

			next.click( function() {
				if ( right_buttons_enable ) {
					$this.trigger( 'paging', ['next'] );
				}
			} );

			end.click( function() {
				if ( right_buttons_enable ) {
					$this.trigger( 'paging', ['end'] );
				}
			} );

			$( paging_selector ).change( $.proxy( function() {

				$( paging_selector ).find( 'option:selected' ).each( function() {
					var page_number = $( this ).attr( 'value' );
					$this.trigger( 'paging', ['go_to', page_number] );
				} );

			}, this ) );

		} );

		return this;

	};

	$.fn.Paging2.defaults = {

	};

})
	( jQuery );