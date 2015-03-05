(function( $ ) {

	$.fn.TGridHeader = function( options ) {
		var opts = $.extend( {}, $.fn.TGridHeader.defaults, options );

		var sort_type = true;

		var sort_number = null;

		var column_model = null; //Column model from Grid

		var $this = this;

		Global.addCss( 'global/widgets/jqgrid/TGridHeader.css' );

		this.getColumnModel = function() {
			return column_model;
		}

		this.setSortStyle = function( sort_type, index ) {
//			  alert(this.attr('id'));
			var sortIcon = $( "<img class='t-grid-header-sort-icon' />" )

			if ( sort_type === 'asc' ) {

				sortIcon.attr( 'src', Global.getRealImagePath( 'images/sort_up.png' ) );

			} else {
				sortIcon.attr( 'src', Global.getRealImagePath( 'images/sort_down.png' ) );
			}

			this.append( sortIcon );

			if ( index > 0 ) {
				var sortNumberSpan = $( "<span class='t-grid-header-sort-number'>" + index + "</span>" )

				this.append( sortNumberSpan );
			}

		}

		this.getWidth = function() {
			return $( this ).parent().width();
		}

		this.cleanSortStyle = function() {
			var sortIcon = this.find( 'img' );
			var sort_number = this.find( 'span[class=t-grid-header-sort-number]' );

			if ( sortIcon.length > 0 ) {
				$( sortIcon ).remove();
			}

			if ( sort_number.length > 0 ) {
				$( sort_number ).remove();
			}
		}

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.column_model ) {

				column_model = o.column_model;
			}

			$( this ).click( function( e ) {

				$( $this ).trigger( 'headerClick', [e, column_model] )

			} );

		} );

		return this;

	};

	$.fn.TGridHeader.defaults = {

	};

})( jQuery );