(function( $ ) {

	$.fn.SearchPanel = function( options ) {
		var opts = $.extend( {}, $.fn.SearchPanel.defaults, options );

		var is_collapsed = true;

		var layouts_array = null;

		var related_view_controller = null;

		var $this = this;

		var tab;

		var select_tab_index = 0;

		var select_tab = null;

		var last_select_tab_index = 0;

		var last_select_tab = null;

		var trigger_change_event = true;

		var hidedAdvTab = false;

//		Global.addCss( 'global/widgets/search_panel/SearchPanel.css' );

		this.isAdvTabVisible = function() {
			return !hidedAdvTab;
		}

		this.isCollapsed = function() {
			return is_collapsed;
		};

		this.hideAdvSearchPanel = function() {

			$( tab ).tabs( 'remove', 1 );
			hidedAdvTab = true;
		};

		this.setSearchFlag = function( filter ) {

			var basic_tab = this.find( "a[href='#basic_search']" );
			var adv_tab = this.find( "a[href='#adv_search']" );

			basic_tab.removeClass( 'active-label' );
			adv_tab.removeClass( 'active-label' );

			var hasFilter = false;
			for ( var key in filter ) {
				if ( key === 'country' && filter[key].value === -1 ) {
					continue;
				}

				//For Documents view
				if ( key === 'template' && filter[key].value === false ) {
					continue;
				}

				hasFilter = true
			}

			if ( hasFilter ) {
				$( this ).find( '.search-flag' ).remove();
				if ( select_tab_index === 0 || hidedAdvTab ) {
					basic_tab.addClass( 'active-label' );
					basic_tab.html( $.i18n._( "BASIC SEARCH" ) + "<img title='" + $.i18n._( 'Search is currently active' ) + "' src='" + Global.getRealImagePath( "css/global/widgets/ribbon/icons/alert-16x16.png" ) + "' class='search-flag'> </img>" )
				} else {
					adv_tab.addClass( 'active-label' );
					adv_tab.html( $.i18n._( "ADVANCED SEARCH" ) + "<img title='" + $.i18n._( 'Search is currently active' ) + "' src='" + Global.getRealImagePath( "css/global/widgets/ribbon/icons/alert-16x16.png" ) + "' class='search-flag'> </img>" );
				}
			} else {
				$( this ).find( '.search-flag' ).remove();
			}

		};

		//Don't trgiiger tab event in some case. Like first set filter to search panel
		this.setSelectTabIndex = function( val, triggerEvent ) {

			if ( select_tab_index === val ) {
				return;
			}

			if ( Global.isSet( triggerEvent ) ) {
				trigger_change_event = triggerEvent;
			} else {
				trigger_change_event = true;
			}

			$( tab ).tabs( 'select', val );
		}

		this.getLastSelectTabIndex = function() {
			return last_select_tab_index
		}

		this.getLastSelectTabId = function() {

			if ( !last_select_tab ) {
				return 'basic_search';
			}

			return $( last_select_tab.tab ).attr( 'ref' );
		}

		this.getSelectTabIndex = function() {
			return select_tab_index
		}

		this.getLayoutsArray = function() {
			return layouts_array;
		}

		//Set Select Layout combobox
		this.setLayoutsArray = function( val ) {
			layouts_array = val;
			var layout_selector = $( this ).find( '#layout_selector' );
			var layout_selector_div = $( this ).find( '.layout-selector-div' );

			$( layout_selector ).empty();

			if ( layouts_array && layouts_array.length > 0 ) {
				var len = layouts_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = layouts_array[i]
					$( layout_selector ).append( '<option value="' + item.id + '">' + item.name + '</option>' )
				}

				$( $( layout_selector ).find( 'option' ) ).filter(function() {

					//Saved layout id should always be number
					return parseInt( $( this ).attr( 'value' ) ) === related_view_controller.select_layout.id;
				} ).prop( 'selected', true ).attr( 'selected', true );

				$( layout_selector_div ).css( 'display', 'block' );

			} else {
				$( layout_selector_div ).css( 'display', 'none' );
			}

		};

		this.setReleatedViewController = function( val ) {
			related_view_controller = val;
		};

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			var basic_tab = $( this ).find( "a[href='#basic_search']" );
			var adv_tab = $( this ).find( "a[href='#adv_search']" );
			var layout_tab = $( this ).find( "a[href='#saved_layout']" );

			var current_view_label = $( this ).find( '.current-view-label' );

			basic_tab.html( $.i18n._( 'BASIC SEARCH' ) );
			adv_tab.html( $.i18n._( 'ADVANCED SEARCH' ) );
			layout_tab.html( $.i18n._( 'SAVED SEARCH & LAYOUT' ) );
			current_view_label.html( $.i18n._( 'Current View' ) + ':' );

			tab = $( this ).find( '.search-panel-tab-bar' );

			var collapseBtn = $( this ).find( '#collapseBtn' );

			var tabDiv = $( this ).find( '.search-panel-tab-outside' );

			var tabContentDiv = $( this ).find( '.search-panel-tab' );

			var layout_selector = $( this ).find( '#layout_selector' );

			var column_selector = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );

			var searchButtonDiv = $( this ).find( '.search-btn-div' );

			var refresh_btn = $( this ).find( "#refreshBtn" );

			refresh_btn.bind( 'click', function() {
				refresh_btn.addClass( 'search-refresh-rotate' );

				related_view_controller.search();

			} );

			$( searchButtonDiv ).css( 'display', 'none' );

			var searchBtn = $( searchButtonDiv ).find( '#searchBtn' );
			var clearSearchBtn = $( searchButtonDiv ).find( '#clearSearchBtn' );

			searchBtn.text( $.i18n._( 'Search' ) );
			clearSearchBtn.text( $.i18n._( 'Clear Search' ) );

			searchBtn.click( function() {
				//Delay 100 to make sure awesomebox values are set in select value
				setTimeout( function() {
					related_view_controller.onSearch();
//
				}, 100 );

				$this.attr( 'search_complete', false );

			} );

			clearSearchBtn.click( function() {
				related_view_controller.onClearSearch();
			} );

			related_view_controller = o.viewController;

			$( layout_selector ).change( $.proxy( function() {

				$( layout_selector ).find( 'option:selected' ).each( function() {
					var selectId = parseInt( $( this ).attr( 'value' ) );
					var len = layouts_array.length;
					for ( var i = 0; i < len; i++ ) {
						var item = layouts_array[i];

						if ( item.id === selectId ) {
							related_view_controller.select_layout = item;
							related_view_controller.setSelectLayout();
							related_view_controller.search();
							break;
						}

					}
				} );

			}, this ) );

			$( tab ).tabs();

			$( tab ).bind( 'tabsselect', onMenuSelect );

			$( tab ).find( 'li' ).mousedown( function( e ) {
				if ( is_collapsed ) {
					onCollapseBtnClick();
				}
			} );

			function onMenuSelect( e, ui ) {

				last_select_tab_index = select_tab_index;

				last_select_tab = select_tab;

				select_tab_index = ui.index;

				select_tab = ui;

				if ( trigger_change_event ) {

					$this.trigger( 'searchTabSelect', [e, ui] );
				} else
					trigger_change_event = true;

				var delayCaller = setInterval( function() {
					clearInterval( delayCaller )
					related_view_controller.setGridSize();
				}, 1 );

			}

			$( collapseBtn ).click( onCollapseBtnClick );

			function onCollapseBtnClick() {
				if ( is_collapsed ) {
					is_collapsed = false;
					$( collapseBtn ).removeClass( 'expend-btn' );

					$( tabDiv ).removeClass( 'search-panel-tab-outside-collapse' );
					$( tabContentDiv ).removeClass( 'search-panel-tab-collapse' );

					$( searchButtonDiv ).css( 'display', 'block' );

				} else {
					is_collapsed = true;
					$( collapseBtn ).addClass( 'expend-btn' );
					$( tabDiv ).addClass( 'search-panel-tab-outside-collapse' );
					$( tabContentDiv ).addClass( 'search-panel-tab-collapse' );

					$( searchButtonDiv ).css( 'display', 'none' );
				}

				related_view_controller.setGridSize();
			}

		} );

		return this;

	};

	$.fn.SearchPanel.defaults = {

	};

})( jQuery );