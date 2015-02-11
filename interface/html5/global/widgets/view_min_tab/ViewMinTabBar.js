(function( $ ) {

	$.fn.ViewMinTabBar = function( options ) {
		var opts = $.extend( {}, $.fn.ViewMinTabBar.defaults, options );

		var $this = this;

		Global.addCss( 'global/widgets/view_min_tab/ViewMinTab.css' );

		var createTab = function( view_id, view_label, url ) {
			var tab = $( Global.loadWidgetByName( WidgetNamesDic.VIEW_MIN_TAB ) );
			var view_name = tab.find( '.view-name' );
			view_name.text( view_label );
			tab.attr( 'id', 'min_tab_' + view_id );

			tab.attr( 'view_url', url );

			var close_btn = tab.find( '.close-btn' );

			tab.unbind( 'click' ).click( function() {
				var view_id = $( this ).attr( 'id' ).replace( 'min_tab_', '' );
				var url = $( this ).attr( 'view_url' );
				Global.removeViewTab( view_id );

				switch ( view_id ) {
					case 'ProcessPayrollWizard':
						IndexViewController.openWizard( view_id );
						break;
					default:
//						IndexViewController.goToView( view_id );
						window.location.href = url;
				}

			} );

			close_btn.unbind( 'click' ).click( function() {
				var view_id = $( this ).parent().attr( 'id' ).replace( 'min_tab_', '' );
				Global.removeViewTab( view_id );
			} );

			$this.append( tab );
		}

		this.buildTabs = function( tab_map ) {

			$this.empty();
			var i = 0;
			for ( var key in tab_map ) {

				if ( tab_map.hasOwnProperty( key ) && key.indexOf( '_url' ) === -1 ) {
					var view_id = key;
					var view_label = tab_map[key];
					var view_url = tab_map[key + '_url'];
					createTab( view_id, view_label, view_url );

				}

				i = i + 1;

			}

		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

		} );

		return this;

	};

	$.fn.ViewMinTabBar.defaults = {

	};

})( jQuery );