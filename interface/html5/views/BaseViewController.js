/* jshint ignore:start */
//Don't check this file for now. Too many issues.
BaseViewController = Backbone.View.extend( {

	real_this: null, //For super call in second level sub class

	sub_view_mode: false,

	edit_only_mode: false,

	can_cache_controller: true, //if allow to cache current controller

	permission_id: '',
	api: null,
	user_generic_data_api: null,
	all_columns: [],
	display_columns: [],
	default_display_columns: [],
	script_name: '',
	filter_data: null, //current Filter data get from Search panel
	temp_basic_filter_data: null,
	temp_adv_filter_data: null,
	sortData: null, //Current Sort data get from search panel
	select_layout: null,
	search_panel: null,
	grid: null,
	context_menu_name: '',
	navigation_label: '',
	context_menu_array: [],
	t_grid_header_array: [],

	//Column Selector in search panel
	column_selector: null,

	sort_by_selector: null,

	save_search_as_input: null,

	previous_saved_layout_selector: null,

	previous_saved_layout_div: null,

	need_select_layout_name: '', //Set this when save new layout to choose the new layout

	search_fields: null,

	basic_search_field_ui_dic: {}, //Save AwesomeBox when they created

	adv_search_field_ui_dic: {}, //Save AwesomeBox when they created

	edit_view_ui_dic: {},

	edit_view_form_item_dic: {}, //Whole FormItem

	edit_view_error_ui_dic: {},

	edit_view: null,

	edit_view_tab: null,

	edit_view_tab_selected_index: 0,

	current_edit_record: null, //Current edit record

	refresh_id: null, //Set this to refresh one record in grid view.

	navigation: null, // Navigation widget in edit view

	is_mass_editing: false, //Set when mass edit

	is_viewing: false,
	is_edit: false,
	is_add: false,

	unique_columns: [], //Set when Mass edit, mark which fields need to be disable

	linked_fields: [],

	mass_edit_record_ids: [], // Mass edit records

	edit_view_tabs: [],

	refresh_sub_view: false,

	parent_key: null, //default filter when search

	parent_value: null, //default filter when search

	parent_edit_record: null,

	invisible_context_menu_dic: null,

	total_display_span: null,

	paging_widget: null,

	paging_widget_2: null, //Put in the bottom of data grid

	pager_data: null,

	viewId: null,

	init_options_complete: false,

	no_result_box: null, // No Result Found Black cover when no result in grid

	table_name_key: null,

	sub_log_view_controller: null,

	parent_view_controller: null, //Add this to call parent_view_controll cancel action when cancel from sub view

	ui_id: '',

	is_changed: false, // Track if modified any fields in edit view

	edit_view_tpl: '', //Edit view html name

	subMenuNavMap: null,

	trySetGridSizeWhenTabShow: false, // Set sub view grid size when tab show instead when tab select

	copied_record_id: '', // When copy as new, save copied reord's id

	other_field_api: null,

	last_select_ids: null,

	saving_layout_in_layout_tab: false, //Mark if save layout from Saved and layout tab. if so, don't switch tabs when set values to search panel

	need_switch_to_context_menu: false,

	show_search_tab: true,

	grid_total_width: null,

	initialize: function() {

		if ( this.options && Global.isSet( this.options.can_cache_controller ) ) {
			this.can_cache_controller = this.options.can_cache_controller;
		}

		if ( this.options && Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		if ( Global.isSet( this.options.sub_view_mode ) ) {
			this.sub_view_mode = this.options.sub_view_mode;
		} else {
			this.sub_view_mode = false;
		}

		if ( !this.edit_only_mode ) {

			if ( this.can_cache_controller ) {
				if ( !this.sub_view_mode ) {
					LocalCacheData.current_open_primary_controller = this;
				} else {
					LocalCacheData.current_open_sub_controller = this;
				}
			}

			//Reset main container id so it won't duplicate when in sub view. Like Audit view.
			var root_container = $( this.el );
			var new_id = root_container.attr( 'id' ) + '_' + Global.getRandomNum();
			root_container.attr( 'id', new_id );
			this.el = '#' + new_id;
			this.ui_id = new_id;

			this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();

			this.total_display_span = $( $( this.el ).find( '.total-number-span' )[0] );

			//This shouldn't be displayed as it caused "flashing" of text and it wasn't translated either.
			//if ( this.total_display_span ) {
			//this.total_display_span.text( 'Displaying 0 - 0 of 0 total. Selected: 0' );
			//}

			//Init paging widget, next step, add widget to UI and bind events in setSelectLayout
			if ( LocalCacheData.paging_type === 0 ) {
				this.paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING );
			} else {
				this.paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING_2 );
				this.paging_widget_2 = Global.loadWidgetByName( WidgetNamesDic.PAGING_2 );

				this.paging_widget = this.paging_widget.Paging2();
				this.paging_widget_2 = this.paging_widget_2.Paging2();
			}
		} else {
			this.ui_id = Global.getRandomNum();
		}

		//init all dic or array, or it will extends last viewcontroller's value. Why?
		this.sub_log_view_controller = null;
		this.edit_view_ui_dic = {};
		this.basic_search_field_ui_dic = {};
		this.adv_search_field_ui_dic = {};
		this.invisible_context_menu_dic = {};
		this.edit_view_tabs = [];
		this.context_menu_array = [];

		this.other_field_api = new (APIFactory.getAPIClass( 'APIOtherField' ))();

		this.initKeyboardEvent(); // register keyboard events if it's a main view

	},

	initKeyboardEvent: function() {

		var $this = this;
		if ( this.sub_view_mode || this.edit_only_mode ) {
			return;
		}

//		$( this.el ).unbind( 'keydown' ).bind( 'keydown', function( e ) {
//
//			if ( e.keyCode === 13 && !$this.search_panel.isCollapsed() ) {
//				$this.onSearch();
//			}
//
//		} );

		$( this.el ).unbind( 'keyup' ).bind( 'keydown', function( e ) {

			if ( e.keyCode === 13 && $this.search_panel && !$this.search_panel.isCollapsed() ) {

				$this.onSearch();
				$( ':focus' ).blur(); //Make focus out of current view. pevent search too much when user keep click enter

			}

		} );

	},

	//Speical permission check for views, need override
	initPermission: function() {

	},

	sendFormIFrameCall: function( postData, url, message_id ) {
		var tempForm = $( "<form></form>" );
		tempForm.attr( 'id', 'temp_form' );
		tempForm.attr( 'method', 'POST' );
		tempForm.attr( 'action', url );
		tempForm.attr( 'target', is_browser_iOS ? '_blank' : 'hideReportIFrame' ); //hideReportIFrame
		tempForm.css( 'display', 'none' );
		var hideInput = $( "<input type='hidden' name='json'>" );
		hideInput.attr( 'value', JSON.stringify( postData ) );
		tempForm.append( hideInput );
		tempForm.appendTo( 'body' );
		tempForm.css( 'display', 'none' );
		tempForm.submit();
		tempForm.remove();

		if ( !is_browser_iOS ) {
			ProgressBar.showProgressBar( message_id, true );
		}
	},

	//Set this when setDefault menu
	setTotalDisplaySpan: function() {

		if ( !this.total_display_span ) {
			return;
		}

		var totalRows;
		var start;
		var end;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = 0;
		//Uncaught TypeError: Cannot read property 'length' of undefined
		if ( grid_selected_id_array ) {
			grid_selected_length = grid_selected_id_array.length;
		}

		var items_pre_page = parseInt( LocalCacheData.getLoginUserPreference().items_per_page );

		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.pager_data ) {
				totalRows = this.pager_data.total_rows;
				start = 1;
				end = this.grid.getGridParam( 'data' ).length;
			} else {
				totalRows = 0;
				start = 0;
				end = 0;
			}
		} else {
			if ( this.pager_data ) {
				totalRows = this.pager_data.total_rows;
				start = 0;
				end = 0;

				if ( this.pager_data.last_page_number > 1 ) {
					if ( !this.pager_data.is_last_page ) {

						start = (this.pager_data.current_page - 1) * items_pre_page + 1;
						end = start + items_pre_page - 1;
					} else {
						start = (this.pager_data.current_page - 1) * items_pre_page + 1;
						end = totalRows;
					}

				} else {
					start = 1;
					end = totalRows;
				}

			} else {

				totalRows = 0;
				start = 0;
				end = 0;
			}
		}

		//Counting pages can be disabled, in which case totalRows returns FALSE unless the user is on the last page.
		var totalInfo = start + ' - ' + end;
		if ( totalRows !== false ) {
			totalInfo = totalInfo + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '.';
		}

		this.total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo + ' [ ' + $.i18n._( 'Selected' ) + ': ' + grid_selected_length + ' ]' );
	},

	//For Browser back/forward to set correct menu
	setSelectRibbonMenuIfNecessary: function() {
		//Try to fixed  Cannot read property 'setSelectMenu' of null，add TopMenuManager.ribbon_view_controlle

		if ( TopMenuManager.ribbon_view_controller && TopMenuManager.selected_sub_menu_id !== this.viewId && !this.sub_view_mode && !this.edit_only_mode ) {

			TopMenuManager.ribbon_view_controller.setSelectMenu( this.viewId );
			TopMenuManager.ribbon_view_controller.setSelectSubMenu( this.viewId );
		}
	},

	getContextIconByName: function( name ) {
		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			if ( id === name ) {
				if ( context_btn.hasClass( 'disable-image' ) || context_btn.hasClass( 'invisible-image' ) ) {
					return true;
				} else {
					return false;
				}
			}

		}

		return false;
	},

	//Set right click menu for list view grid
	initRightClickMenu: function( target_type ) {

		//Error: Object doesn't support property or method 'contextMenu' in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.6-20141027-132733 line 393
		if ( !$.hasOwnProperty( 'contextMenu' ) ) {
			return;
		}
		var $this = this;

		var selector = '';

		switch ( target_type ) {
			case RightClickMenuType.LISTVIEW:
				selector = '#gbox_' + this.ui_id + '_grid';
				break;
			case RightClickMenuType.EDITVIEW:
				selector = '#' + this.ui_id + '_edit_view_tab';
				break;
			case RightClickMenuType.NORESULTBOX:
				selector = '#' + this.ui_id + '_no_result_box';
				break;
			case RightClickMenuType.ABSENCE_GRID:
				selector = '#' + this.ui_id + '_absence_grid';
				break;
			default:
				selector = '#gbox_' + this.ui_id + '_grid';
				break;

		}

		if ( $( selector ).length == 0 ) {
			return;
		}

		var items = this.getRightClickMenuItems();

		if ( !items || $.isEmptyObject( items ) ) {
			return;
		}

		$.contextMenu( {
			selector: selector,
			callback: function( key, options ) {
				$this.onContextMenuClick( null, key );
			},

			onContextMenu: function() {
				return false;
			},
			items: items
		} );
	},

	getRightClickMenuItems: function() {

		var $this = this;
		var items = {};
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var html_content = $( context_btn.html() );
			var label = context_btn.text();

			//DOn't add sub menu context icon to right click
			if ( context_btn.children().eq( 0 ).hasClass( 'ribbon-sub-menu-nav-icon' ) ) {
				continue;
			}

			label = this.replaceRightClickLabel( html_content );

			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'invisible-image' ) ) {
				continue;
			}

			items[id] = {
				name: label, icon: id, disabled: function( key ) {
					return $this.getContextIconByName( key );
				}
			};

		}

		return items;
	},

	replaceRightClickLabel: function( html_content ) {

		var label = html_content.children().eq( 1 ).html();

		label = label.replace( '<br>', ' ' );

		return label;
	},

	//Don't initOptions if edit_only_mode. Do it in sub views
	initData: function() {
		var $this = this;

		//Work around to init sub view after tab is shown.
		Global.removeViewTab( this.viewId );
		ProgressBar.showOverlay();
		if ( !$this.edit_only_mode ) {
			$this.initOptions();
			$this.getAllColumns( function() {
				$this.initLayout();
			} );
		}

	},

	initLayout: function() {

		var $this = this;

		$this.getAllLayouts( function() {

			$this.getDefaultDisplayColumns( function() {

				$this.setSelectLayout();

				$this.search();

				//set right click menu to list view grid
				$this.initRightClickMenu();

			} );

		} );

	},

	// edit_only_mode call this when open edit view. Not in initData
	initOptions: function() {

	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var edit = new RibbonSubMenu( {
			label: $.i18n._( 'Edit' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		var mass_edit = new RibbonSubMenu( {
			label: $.i18n._( 'Mass<br>Edit' ),
			id: ContextMenuIconName.mass_edit,
			group: editor_group,
			icon: Icons.mass_edit,
			permission_result: true,
			permission: null
		} );

		var del = new RibbonSubMenu( {
			label: $.i18n._( 'Delete' ),
			id: ContextMenuIconName.delete_icon,
			group: editor_group,
			icon: Icons.delete_icon,
			permission_result: true,
			permission: null
		} );

		var delAndNext = new RibbonSubMenu( {
			label: $.i18n._( 'Delete<br>& Next' ),
			id: ContextMenuIconName.delete_and_next,
			group: editor_group,
			icon: Icons.delete_and_next,
			permission_result: true,
			permission: null
		} );

		var copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.copy,
			group: editor_group,
			icon: Icons.copy_as_new,
			permission_result: true,
			permission: null
		} );

		var copy_as_new = new RibbonSubMenu( {
			label: $.i18n._( 'Copy<br>as New' ),
			id: ContextMenuIconName.copy_as_new,
			group: editor_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var save = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save,
			group: editor_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_and_continue = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Continue' ),
			id: ContextMenuIconName.save_and_continue,
			group: editor_group,
			icon: Icons.save_and_continue,
			permission_result: true,
			permission: null
		} );

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
			permission_result: true,
			permission: null
		} );

		var save_and_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Copy' ),
			id: ContextMenuIconName.save_and_copy,
			group: editor_group,
			icon: Icons.save_and_copy,
			permission_result: true,
			permission: null
		} );

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	buildContextMenu: function( setFocus ) {
		var $this = this;
		if ( !Global.isSet( setFocus ) ) {
			setFocus = true;
		}

		if ( !this.sub_view_mode ) {
			LocalCacheData.current_open_sub_controller = null; //Clean sub controller if current view is a main view
		}

		var ribbon_menu_array = this.buildContextMenuModels();
		var ribbon_menu_label_node = $( '.ribbonTabLabel' );
		var ribbon_menu_root_node = $( '.ribbon' );

		var len = ribbon_menu_array.length;

		var ribbon_menu;

		for ( var i = 0; i < len; i++ ) {
			ribbon_menu = ribbon_menu_array[i];
			var ribbon_menu_group_array = ribbon_menu.get( 'sub_menu_groups' );
			var ribbon_menu_ui = $( '<div id="' + ribbon_menu.get( 'id' ) + '" class="ribbon-tab-out-side ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"><div class="context-ribbon-tab"><div class="ribbon-sub-menu"></div></div></div>' );

			//make sure only one context menu shown at a time
			if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName !== ribbon_menu.get( 'id' ) ) {
				this.removeContentMenuByName( LocalCacheData.currentShownContextMenuName );
				this.context_menu_array = [];

			} else if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName === ribbon_menu.get( 'id' ) ) {
				return;
			}

			this.subMenuNavMap = {};

			LocalCacheData.currentShownContextMenuName = ribbon_menu.get( 'id' );
			var len1 = ribbon_menu_group_array.length;
			for ( var x = 0; x < len1; x++ ) {

				var ribbon_menu_group = ribbon_menu_group_array[x];
				var ribbon_sub_menu_array = ribbon_menu_group.get( 'sub_menus' );
				var sub_menu_ui_nodes = $( "<ul></ul>" );
				var ribbon_menu_group_ui = $( '<div class="menu top-ribbon-menu"  ondragstart="return false;" />' );

				var len2 = ribbon_sub_menu_array.length;
				for ( var y = 0; y < len2; y++ ) {

					var ribbon_sub_menu = ribbon_sub_menu_array[y];

					//Do not add context menu which in invisible_context_menu_dic
					if ( this.invisible_context_menu_dic[ribbon_sub_menu.get( 'id' )] ) {
						continue;
					}
//					var sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					if ( ribbon_sub_menu.get( 'selected' ) ) {

						var sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon selected-menu" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					} else {
						sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					}

					this.context_menu_array.push( sub_menu_ui_node );

					if ( ribbon_sub_menu.get( 'type' ) === RibbonSubMenuType.NAVIGATION ) {

						sub_menu_ui_node.children().eq( 0 ).addClass( 'ribbon-sub-menu-nav-icon' );
						$this.subMenuNavMap[ribbon_sub_menu.get( 'id' )] = ribbon_sub_menu;

						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onSubMenuNavClick( this, id );
						} );

					} else {
						//defend empty block error when comments following codes

						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onContextMenuClick( this );
						} );

					}

					sub_menu_ui_nodes.append( sub_menu_ui_node );
				}

				if ( sub_menu_ui_nodes.children().length > 0 ) {
					ribbon_menu_group_ui.append( sub_menu_ui_nodes );
					ribbon_menu_group_ui.append( $( '<div class="menu-bottom"><span>' + ribbon_menu_group.get( 'label' ) + '</span></div>' ) );
					ribbon_menu_ui.find( '.ribbon-sub-menu' ).append( ribbon_menu_group_ui );
				}

			}
			ribbon_menu_label_node.append( $( '<li class="context-menu ui-state-default ui-corner-top"><a ref="' + ribbon_menu.get( 'id' ) + '" href="#' + ribbon_menu.get( 'id' ) + '">' + ribbon_menu.get( 'label' ) + '</a></li>' ) );
			ribbon_menu_root_node.append( ribbon_menu_ui );
		}

		//Register ribbon menu to tab widget
		$( '#ribbon_view_container' ).tabs( 'add', '#' + ribbon_menu.get( 'id' ) );
		$( '#ribbon_view_container' ).tabs( 'remove', ($( '#ribbon_view_container' ).tabs( 'length' ) - 1) );

		if ( setFocus ) {
//

			this.need_switch_to_context_menu = true;
			//Don't select context menu until search complete

		}

	},

	onSubMenuNavClick: function( target, id ) {
		var $this = this;
		var sub_menu = this.subMenuNavMap[id];

		if ( LocalCacheData.openRibbonNaviMenu ) {

			if ( LocalCacheData.openRibbonNaviMenu.attr( 'id' ) === 'sub_nav' + id ) {
				LocalCacheData.openRibbonNaviMenu.close();
				return;
			} else {
				LocalCacheData.openRibbonNaviMenu.close();
			}

		}

		showNavItems();

		function showNavItems() {
			var items = sub_menu.get( 'items' );
			var box = $( "<ul id='sub_nav" + id + "' class='ribbon-sub-menu-nav'> </ul>" );

			for ( var i = 0; i < items.length; i++ ) {
				var item = items[i];
				var item_node = $( "<li class='ribbon-sub-menu-nav-item' id='" + item.get( 'id' ) + "'><span class='label'>" + item.get( 'label' ) + "</span></li>" );
				box.append( item_node );

				item_node.unbind( 'click' ).click( function() {
					var id = $( this ).attr( 'id' );
					$this.onReportMenuClick( id );
				} );

			}

			box = box.RibbonSubMenuNavWidget();

			LocalCacheData.openRibbonNaviMenu = box;

			$( target ).append( box );

			if ( box.offset().left + box.width() > Global.bodyWidth() ) {
				box.css( 'left', Global.bodyWidth() - box.width() - 10 );
			}
		}

	},

	onReportMenuClick: function( id ) {

	},

	onContextMenuClick: function( context_btn, menu_name ) {
		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.add:
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_new:
				ProgressBar.showOverlay();
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				ProgressBar.showOverlay();
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.mass_edit:
				ProgressBar.showOverlay();
				this.onMassEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
				break;
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.download:
				this.onDownloadClick();
				break;
			default:
				this.onCustomContextClick( id )

		}

	},

	onCustomContextClick: function( id ) {

	},

	onNavigationClick: function( id ) {

	},

	onAddClick: function() {
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		//Error: Uncaught TypeError: undefined is not a function in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 897
		if ( $this.api && typeof $this.api['get' + $this.api.key_name + 'DefaultData'] === 'function' ) {
			$this.api['get' + $this.api.key_name + 'DefaultData']( {
				onResult: function( result ) {
					$this.onAddResult( result );

				}
			} );
		}

	},

	onAddResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	onDeleteAndNextClick: function() {
		var $this = this;
		$this.is_add = false;

		TAlertManager.showConfirmAlert( $.i18n._( Global.delete_confirm_message ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			}

			if ( result ) {

				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteAndNextResult( result, remove_ids );

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	onDeleteAndNextResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();
		if ( result.isValid() ) {

			$this.search( false, null, null, function( result ) {
				var current_grid_source = result.getResult();

				if ( $.type( current_grid_source ) !== 'array' || current_grid_source.length < 1 ) {
					$this.removeEditView();
					$this.setDefaultMenu();
				} else {
					$this.navigation.setSourceData( current_grid_source );
					$this.navigation.setPagerData( $this.pager_data );
					$this.refreshCurrentRecord();

				}
			} );

			$this.onDeleteAndNextDone( result );
		} else {
			TAlertManager.showErrorAlert( result );
		}
	},

	resetNavigationSourceData: function() {

	},

	onDeleteClick: function() {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			} else {
				remove_ids = $this.getGridSelectIdArray().slice();
			}
			if ( result ) {
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {
						$this.onDeleteResult( result, remove_ids );
					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	onDeleteResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();
		if ( result.isValid() ) {
			$this.search();
			$this.onDeleteDone( result );
			if ( $this.edit_view ) {
				$this.removeEditView();
			}
		} else {
			TAlertManager.showErrorAlert( result );
		}
	},

	removeDeletedRows: function( remove_ids ) {
		var $this = this;
		$.each( remove_ids, function( index, value ) {
			$this.grid.jqGrid( 'delRowData', value );
			$this.paging_widget.minus();

		} );
//
//		if ( this.grid.getGridParam( 'data' ).length === 0 ) {
//			this.search();
//		}

//		this.search();

//		var grid_selected_id_array = this.getGridSelectIdArray();
//		var grid_selected_length = grid_selected_id_array.length;
//
//		if ( grid_selected_length === 0 ) {
//			this.search();
//		}

	},

	clearNavigationData: function() {
		if ( this.navigation ) {
			this.navigation.setSourceData( null );
		}
	},

	onSaveAndCopy: function() {
		var $this = this;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndCopyResult( result );

			}
		} );
	},

	onSaveAndCopyResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}
			$this.search( false );
			$this.onCopyAsNewClick();
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNewClick: function() {
		var $this = this;
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
	},

	onSaveAndNewResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}
			$this.search( false );
			$this.onAddClick( true );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndContinue: function() {
		var $this = this;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	},

	onSaveAndContinueResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;

			}
			$this.onEditClick( $this.refresh_id, true );
			$this.onSaveAndContinueDone( result );
			$this.search( false );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNextClick: function() {
		var $this = this;
		this.is_add = false;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_next';
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndNextResult( result );

			}
		} );
	},

	onSaveAndNextResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}
			$this.onRightArrowClick();
			$this.search( false );
			$this.onSaveAndNextDone( result );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	uniformVariable: function( records ) {

		return records;
	},

	onSaveClick: function() {
		var $this = this;
		var record;
//		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {

				$this.onSaveResult( result );

			}
		} );
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {

			$this.is_add = false;
			var result_data = result.getResult();
			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( result_data > 0 ) {
					$this.refresh_id = result_data;
				}

				$this.search();
			}

			$this.onSaveDone( result );
			$this.current_edit_record = null;
			$this.removeEditView();

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveDone: function( result ) {

	},

	onSaveAndContinueDone: function( result ) {

	},

	onSaveAndNextDone: function( result ) {

	},

	onDeleteDone: function( result ) {

	},

	onDeleteAndNextDone: function( result ) {

	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();
								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		$this.is_viewing = true;
		$this.is_edit = false;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( editId ) ) {
			var selectedId = editId
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onCopyClick: function() {
		var $this = this;
		var copyIds = [];
		$this.is_add = false;
		if ( $this.edit_view ) {
			copyIds.push( $this.current_edit_record.id );
		} else {
			copyIds = $this.getGridSelectIdArray().slice();
		}

		ProgressBar.showOverlay();
		$this.api['copy' + $this.api.key_name]( copyIds, {
			onResult: function( result ) {
				$this.onCopyResult( result )

			}
		} );
	},

	onCopyResult: function( result ) {
		var $this = this;

		if ( result.isValid() ) {
			$this.search();
			if ( $this.edit_view ) {
				$this.removeEditView();
			}

		} else {

			TAlertManager.showErrorAlert( result );

			if ( result.getRecordDetails().total > 1 ) {
				$this.search();
			}
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {
					$this.onCopyAsNewResult( result );

				}
			} );
		}

	},

	onCopyAsNewResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		result_data.id = '';

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {

		//Need override
	},

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value};

			this.job_api.getJob( args, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_id = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0] );

					} else {
						$this.edit_view_ui_dic['job_id'].setValue( '' );
						$this.current_edit_record.job_id = false;
						$this.setJobItemValueWhenJobChanged( false );
					}

				}
			} );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value};

			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_item_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_item_id = result_data[0].id;

					} else {
						$this.edit_view_ui_dic['job_item_id'].setValue( '' );
						$this.current_edit_record.job_item_id = false;
					}

				}
			} );
		}

	},

	onCancelClick: function( force, cancel_all ) {

		var $this = this;
		LocalCacheData.current_doing_context_action = 'cancel';
		if ( this.is_changed && !force ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {

				if ( flag === true ) {
					doNext();
				}

			} );
		} else {
			doNext();
		}

		function doNext() {
			if ( !$this.edit_view && $this.parent_view_controller && $this.sub_view_mode ) {
				$this.parent_view_controller.is_changed = false;
				$this.parent_view_controller.buildContextMenu( true );
				$this.parent_view_controller.onCancelClick();

			} else {
				$this.removeEditView();
			}

		}

	},

	//Don't call super if override this function.
	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		this.current_edit_record[key] = target.getValue();

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setIsChanged: function( target ) {
		var key = target.getField();
		if ( this.current_edit_record && this.current_edit_record[key] != target.getValue() ) {
			this.is_changed = true;
		}
	},

	onFormItemKeyUp: function( target ) {

	},

	onFormItemKeyDown: function( target ) {

	},

	setMassEditingFieldsWhenFormChange: function( target ) {
		var $this = this;

		if ( this.is_mass_editing ) {
			var field = target.getField();
			var linked_fields = [];
			var is_linked_field = false;

			$.each( this.linked_columns, function( index, value ) {
				if ( value !== field ) {
					linked_fields.push( value )
				} else {
					is_linked_field = true;
				}
			} );

			if ( is_linked_field ) {
				$.each( linked_fields, function( index, value ) {
					var is_checked = $this.edit_view_ui_dic[field].isChecked();
					$this.edit_view_ui_dic[value].setCheckBox( is_checked )
				} );
			}

		}
	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		//Handle most cases that one tab and on audit tab
		if ( this.edit_view_tab_selected_index === 1 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {

				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

	},

	onTabIndexChange: function( e, ui ) {
		this.edit_view_tab_selected_index = ui.index;

		if ( !this.sub_view_mode && !this.edit_only_mode ) {
			var current_url = window.location.href;

			if ( current_url.indexOf( '&tab' ) > 0 ) {
				current_url = current_url.substring( 0, current_url.indexOf( '&tab' ) );
				var tab_name = this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.edit_view_tab_selected_index ).text();
				tab_name = tab_name.replace( /\/|\s+/g, '' );
				current_url = current_url + '&tab=' + tab_name;
			}

			Global.setURLToBrowser( current_url );

		}

	},

	setTabStatus: function() {
		// exception that edit_view_tab is null
		if ( !this.edit_view_tab ) {
			return;
		}
		//Handle most cases that one tab and on audit tab
		if ( this.is_mass_editing ) {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else {

			if ( this.subAuditValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

		}

		this.editFieldResize( 0 );
	},

	onCountryChange: function() {
		var selectVal = this.edit_view_ui_dic['country'].getValue();
		this.eSetProvince( selectVal, true );
		this.clearErrorTips();
		this.setEditMenu();
	},

	//Make sure this.current_edit_record is updated before validate
	validate: function() {

		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	},

	validateResult: function( result ) {
		var $this = this;
		$this.clearErrorTips(); //Always clear error

		if ( !$this.edit_view ) {
			return;
		}

		if ( result.isValid() ) {
			$this.edit_view.attr( 'validate_complete', true );
			$this.setEditMenu();
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	clearErrorTips: function() {

		for ( var key in this.edit_view_error_ui_dic ) {

			//Error: Uncaught TypeError: Cannot read property 'clearErrorStyle' of undefined in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 1779
			if ( !this.edit_view_error_ui_dic.hasOwnProperty( key ) || !this.edit_view_error_ui_dic[key] ) {
				continue;
			}

			this.edit_view_error_ui_dic[key].clearErrorStyle();
		}

		this.edit_view_error_ui_dic = {};
	},

	//Override this if more than one tab
	setErrorTips: function( result, dont_switch_tab ) {
		this.clearErrorTips();

		//Error: Unable to get property 'find' of undefined or null reference in http://timeclock:8085/interface/html5/views/BaseViewController.js?v=7.4.3-20140926-105827 line 1769
		if ( !this.edit_view_tab ) {
			return;
		}

		var details = result.getDetails();
		var error_list = details[0];

		var found_in_current_tab = false;

		for ( var key in error_list ) {

			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}

			if ( !Global.isSet( this.edit_view_ui_dic[key] ) ) {
				continue;
			}

			if ( this.edit_view_ui_dic[key].is( ':visible' ) ) {

				this.edit_view_ui_dic[key].setErrorStyle( error_list[key], true );
				found_in_current_tab = true;

			} else {

				this.edit_view_ui_dic[key].setErrorStyle( error_list[key] );
			}

			this.edit_view_error_ui_dic[key] = this.edit_view_ui_dic[key];

		}

		if ( !found_in_current_tab ) {

			this.showEditViewError( result );

		}
	},

	showEditViewError: function( result ) {

		var details = result.getDetails();
		var error_string = '';

		$.each( details, function( index, val ) {
			for ( var key in val ) {
				if ( Global.isArray( val[key] ) ) {
					for ( var i = 0, ii = val[key].length; i < ii; i++ ) {
						var item = val[key][i];
						error_string = error_string + val[key][i] + "<br>";
					}
				} else {
					error_string = error_string + val[key] + "<br>";
				}

			}
		} );

		this.edit_view.children().eq( 0 ).children().eq( 2 ).qtip(
			{
				show: {
					when: false,
					ready: true
				},
				hide: {
					when: 'unfocus',
					delay: 1000
				},
				content: error_string,
				style: {
					name: 'red',
					color: '#ffffff',
					'background-color': '#cb2e2e',
					tip: true,
					border: {
						width: 2
					}
				},
				position: {
					corner: {
						tooltip: 'bottomMiddle', target: 'bottomMiddle'
					},
					adjust: {
						y: -30
					}
				}
			} );
	},

	selectContextMenu: function() {

		//Error: Uncaught TypeError: Cannot read property 'el' of null in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141230-113526 line 1880
		if ( TopMenuManager.selected_menu_id !== this.viewId + 'ContextMenu' && TopMenuManager.ribbon_view_controller ) {
			var ribbon = $( TopMenuManager.ribbon_view_controller.el );
			ribbon.tabs( {selected: this.viewId + 'ContextMenu'} );
		}

	},

	openEditView: function() {
		if ( !this.edit_view ) {
			this.initEditViewUI( this.viewId, this.edit_view_tpl );
		}

		this.setEditViewWidgetsMode();
	},

	setTabOVisibility: function( flag ) {
		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab' )[0] );
		if ( flag ) {
			tab0.css( 'opacity', 1 );
			this.edit_view.attr( 'init_complete', true );
			this.setEditViewTabSize();

		} else {
			this.edit_view_tab.find( 'ul li' ).hide();
			tab0.css( 'opacity', 0 );
			this.edit_view.attr( 'init_complete', false );
		}

	},
	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {

		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );

			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {

				if ( !did_clean_dic[tab_id] ) {
					column.find( '.edit-view-form-item-label-div-first-row' ).removeClass( 'edit-view-form-item-label-div-first-row' );
					column.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
					column.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
					did_clean_dic[tab_id] = true;
				}

				var child_length = column.children().length;
				var parent_div = widget.parent().parent();

				if ( child_length === 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				} else if ( parent_div.index() === 0 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
				} else if ( parent_div.index() === child_length - 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				}

				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}

			widget.setValue( '' ); // Set all value back to empty before new value coming.

			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {

					widget.setEnabled( true );

				}
			}

		}

	},

	//Call this when edit view open
	initEditViewUI: function( view_id, edit_view_file_name ) {

		var $this = this;
		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );

		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		this.setTabOVisibility( false );

		this.edit_view_tab = this.edit_view_tab.tabs( {
			show: function( e, ui ) {
				if ( !$this.edit_view_tab || !$this.edit_view_tab.is( ':visible' ) ) {
					return;
				}

				$this.onTabShow( e, ui )
			}
		} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui )
		} );

		Global.contentContainer().append( this.edit_view );

		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		this.buildEditViewUI();

		//Calculated tab's height
		this.edit_view_tab.resize( function() {

			$this.setEditViewTabHeight();

		} );

		$this.setEditViewTabHeight();
	},

	setEditViewTabHeight: function() {
		var $this = this;
		var tab = $this.edit_view_tab.find( '.edit-view-tab-outside' );
		if ( tab.length > 0 ) {

			tab.height( $this.edit_view_tab.height() - 60 );

		}

		tab = $this.edit_view_tab.find( '.edit-view-tab-outside-sub-view' );
		if ( tab.length > 0 ) {

			tab.height( $this.edit_view_tab.height() - 34 );

		}
//		var i = 0;
//		var hasTab = true;
//		while ( hasTab ) {
//			var tab = $this.edit_view_tab.find( 'edit-view-tab-outside' );
//			if ( tab.length > 0 ) {
//				hasTab = true;
//
//				if ( tab.hasClass( 'edit-view-tab-outside-sub-view' ) ) {
//					tab.height( $this.edit_view_tab.height() - 34 );
//				} else {
//					tab.height( $this.edit_view_tab.height() - 60 );
//				}
//
//			} else {
//				hasTab = false;
//			}
//
//			i = i + 1;
//		}
	},

	setTabLabels: function( source ) {
		for ( var key in source ) {
			this.edit_view.find( 'a[ref=' + key + ']' ).text( source[key] );
		}
	},

	//Call this after initEditViewUI, usually after current_edit_record is set
	initEditView: function() {

		//Uncaught TypeError: Cannot read property 'find' of null in Timehseet Authorization view when quickly click Cancel from replay
		if ( !this.edit_view_tab ) {
			return;
		}
		this.setURL();
		this.setEditMenu();
		//Remove cover once edit menu is set
		ProgressBar.closeOverlay();

		//Error: Unable to get property 'find' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.6-20141027-074127 line 2055
		if ( this.edit_view_tab ) {
			this.edit_view_tab.find( 'ul li' ).show(); // All tabs are hidden when initEditView UI, show all of them before set status
		}

		this.setTabStatus();
		this.clearEditViewData();
		this.setEditViewData();
		this.setOtherFields();
		this.setFocusToFirstInput();

	},

	setOtherFields: function() {

		var $this = this;
		var type_id = this.getOtherFieldTypeId();

		if ( type_id === 0 ) {
			return;
		}

		var filter = {filter_data: {type_id: type_id}};

		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab-outside' )[0] );
		var tab0_column1 = tab0.find( '.first-column' );

		this.other_field_api.getOtherField( filter, true, {
			onResult: function( result ) {
				var res_data = result.getResult();
				if ( $.type( res_data ) === 'array' && res_data.length > 0 ) {
					res_data = res_data[0];

					for ( var i = 1; i < 10; i++ ) {
						if ( res_data['other_id' + i] ) {
							$this.buildOtherFieldUI( 'other_id' + i, res_data['other_id' + i] );
						}
					}

					$this.editFieldResize( 0 );
				}

				$this.resetLastWidgetStyle();

			}
		} );

	},

	buildOtherFieldUI: function( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab-outside' )[0] );
		var tab0_column1 = tab0.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: field} );
			$this.addEditFieldToColumn( label, form_item_input, tab0_column1 );

			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		}

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	resetLastWidgetStyle: function() {

		if ( !this.edit_view_tab || !this.edit_view ) {
			return;
		}

		var tab0 = $( this.edit_view_tab.find( '.edit-view-tab-outside' )[0] );
		var tab0_column1 = tab0.find( '.first-column' );
		tab0_column1.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
		tab0_column1.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );

		var len = tab0_column1.children().length;

		var last_form_item = tab0_column1.children().eq( len - 2 );
		last_form_item.addClass( 'edit-view-form-item-div-last-row' );
		last_form_item.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );

	},

	getOtherFieldTypeId: function() {
		var res = 0;
		switch ( this.viewId ) {
			case 'Employee':
				res = 10;
				break;
			case 'Document':
				res = 80;
				break;
			case 'Job':
				res = 20;
				break;
			case 'JobItem':
				res = 30;
				break;
			case 'Product':
				res = 60;
				break;
			case 'Invoice':
				res = 70;
				break;
			case 'ClientContact':
				res = 55;
				break;
			case 'Client':
				res = 50;
				break;
			case 'UserTitle':
				res = 12;
				break;
			case 'Department':
				res = 5;
				break;
			case 'Company':
			case 'Companies':
				res = 2;
				break;
			case 'Branch':
				res = 4;
				break;
			case 'TimeSheet':
			case 'Punches':
			case 'InOut':
				res = 15;
				break;

		}

		return res;
	},

	setURL: function() {
		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}

		if ( this.canSetURL() ) {

			var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.edit_view_tab_selected_index ).text() : '';
			tab_name = tab_name.replace( /\/|\s+/g, '' );

			//Error: Unable to get property 'id' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-132941 line 2234
			if ( this.current_edit_record && this.current_edit_record.id ) {
				if ( a ) {

					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a + '&id=' + this.current_edit_record.id +
					'&tab=' + tab_name );

				} else {
					Global.setURLToBrowser( window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&id=' + this.current_edit_record.id );

				}

				Global.trackView( this.viewId, LocalCacheData.current_doing_context_action );

			} else {
				if ( a ) {

					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + 'new' +
						'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a +
						'&tab=' + tab_name );
					}

				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId );
				}
			}

		}

	},

	canSetURL: function() {
		if ( this.sub_view_mode || this.edit_only_mode ) {
			return false;
		}

		return true;
	},

	setFocusToFirstInput: function() {
		if ( !this.is_viewing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}
				var widget = this.edit_view_ui_dic[key];

				if ( widget.hasClass( 't-text-input' ) && widget.is( ':visible' ) === true && !widget.attr( 'readonly' ) ) {
					widget.focus();
					break;
				}

			}
		}
	},

	removeLastRowClass: function( formItem ) {
		//Error: TypeError: undefined is not an object (evaluating 'formItem.find') in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20150126-192230 line 2339
		if ( !formItem ) {
			return;
		}
		formItem.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
		formItem.removeClass( 'edit-view-form-item-div-last-row' );
	},

	addLastRowClass: function( formItem ) {

		this.removeLastRowClass( formItem );

		formItem.find( '.edit-view-form-item-label-div' ).addClass( 'edit-view-form-item-label-div-last-row' );
		formItem.addClass( 'edit-view-form-item-div-last-row' );
	},

	buildEditViewUI: function() {
		var $this = this;

		//No navigation when edit only mode

		if ( !this.edit_only_mode ) {
			var navigation_div = this.edit_view.find( '.navigation-div' );
			var label = navigation_div.find( '.navigation-label' );
			var left_click = navigation_div.find( '.left-click' );
			var right_click = navigation_div.find( '.right-click' );
			var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );

			this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			navigation_widget_div.append( this.navigation );

			left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
			right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );

			label.text( this.navigation_label );

			navigation_widget_div.append( this.navigation );
		}

		var close_icon = this.edit_view.find( '.close-icon' );

		close_icon.click( function() {

			$this.onCloseIconClick();

		} );

	},

	onCloseIconClick: function() {
		if ( LocalCacheData.current_open_sub_controller ) {
			LocalCacheData.current_open_sub_controller.onCancelClick();
		} else {
			this.onCancelClick();
		}
	},

	//widgetContainer: add widget to custom container
	//saveFormItemDiv: if cache current formItemDiv and use it later
	addEditFieldToColumn: function( label, widgets, column, firstOrLastRecord, widgetContainer, saveFormItemDiv, setResizeEvent, saveFormItemDivKey, hasKeyEvent, customLabelWidget ) {

		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		var widget = widgets;

		if ( Global.isArray( widgets ) ) {
			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				widget.css( 'opacity', 0 );
			}
		} else {
			widget.css( 'opacity', 0 );
		}

		if ( customLabelWidget ) {
			form_item_label.parent().append( customLabelWidget );
			form_item_label.remove();
		} else {
			form_item_label.text( label + ': ' );
		}

		if ( Global.isSet( widgetContainer ) ) {

			form_item_input_div.append( widgetContainer );

		} else {
			form_item_input_div.append( widget );
		}

		column.append( form_item );
		column.append( "<div class='clear-both-div'></div>" );

		//set height to text area
		if ( form_item.height() > 35 ) {
			form_item_label_div.css( 'height', form_item.height() );
		} else if ( widget.hasClass( 'a-dropdown' ) ) {
			form_item_label_div.css( 'height', 240 );
		}

		if ( setResizeEvent ) {

			form_item.unbind( 'resize' ).bind( 'resize', function() {
				if ( form_item_label_div.height() !== form_item.height() && form_item.height() !== 0 ) {
					form_item_label_div.css( 'height', form_item.height() );
				}

			} );
			//
			widget.unbind( 'setSize' ).bind( 'setSize', function() {
				form_item_label_div.css( 'height', widget.height() + 10 );
			} );
		}

		if ( !label ) {
			form_item_input_div.remove();
			form_item_label_div.remove();

			form_item.append( widget );
			widget.css( 'opacity', 1 );

			if ( saveFormItemDiv && saveFormItemDivKey ) {
				this.edit_view_form_item_dic[saveFormItemDivKey] = form_item;
			}

			return;
		}

		if ( saveFormItemDiv ) {

			if ( Global.isArray( widgets ) ) {
				this.edit_view_form_item_dic[widgets[0].getField()] = form_item;
			} else {
				this.edit_view_form_item_dic[widget.getField()] = form_item;
			}

		}
		if ( Global.isArray( widgets ) ) {

			for ( i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				this.edit_view_ui_dic[widget.getField()] = widget;

				widget.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );

				if ( hasKeyEvent ) {
					widget.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
						$this.onFormItemKeyUp( target );
					} );

					widget.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
						$this.onFormItemKeyDown( target );
					} );
				}
			}
		} else {
			this.edit_view_ui_dic[widget.getField()] = widget;

			widget.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );

			if ( hasKeyEvent ) {
				widget.bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				widget.bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
			}
		}

		return form_item;

	},

	//Set fields label to same size
	editFieldResize: function( index ) {

		if ( Global.isSet( index ) ) {

		} else {
			index = this.edit_view_tab_selected_index;
		}

		if ( Global.isSet( this.edit_view_tabs[index] ) && !Global.isFalseOrNull( this.edit_view_tabs[index] ) ) {
			var tab_div = this.edit_view_tabs[index];
			for ( var i = 0; i < tab_div.length; i++ ) {
				var tab_column_div = tab_div[i].find( '.edit-view-form-item-label-div' );
				var tab_column_sub_div = tab_div[i].find( '.edit-view-form-item-sub-label-div > span' );
				if ( Global.isSet( tab_column_sub_div ) && tab_column_sub_div.length > 0 ) {
					this.setEditFieldSize( tab_column_sub_div );
				}
				this.setEditFieldSize( tab_column_div );
			}
		}
//		this.edit_view_tabs[index] = false;
	},

	setEditFieldSize: function( tab_column_div, width ) {

		if ( Global.isSet( width ) ) {

			tab_column_div.each( function() {
				$( this ).width( width );
			} );

		} else {

			var item_label_div_width = [];
			tab_column_div.each( function() {

				if ( $( this ).width() === 0 ) {
					return true;
				}

				$( this ).css( 'width', 'auto' );

				item_label_div_width.push( $( this ).width() );
			} );

			item_label_div_width.sort( function( a, b ) {
				return (b - a);
			} );

			tab_column_div.each( function() {
				$( this ).width( item_label_div_width[0] + 1 );
			} );
		}
	},

	setNavigation: function() {

		var $this = this;

		//Error: Unable to get value of the property 'getGridParam': object is null or undefined in https://villa.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141230-103725 line 2575
		if ( !this.grid ) {
			return;
		}

		this.navigation.setPossibleDisplayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ),
			this.buildDisplayColumns( this.default_display_columns ) );

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {

			var key = target.getField();
			var next_select_item_id = target.getValue();

			if ( !next_select_item_id ) {
				return;
			}

			if ( next_select_item_id !== $this.current_edit_record.id ) {
				ProgressBar.showOverlay();

				if ( $this.is_viewing ) {
					$this.onViewClick( next_select_item_id ); //Dont refresh UI
				} else {
					$this.onEditClick( next_select_item_id ); //Dont refresh UI
				}

			}

			$this.setNavigationArrowsEnabled();

		} );

	},

	clearEditViewData: function() {

		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			this.edit_view_ui_dic[key].setValue( null );
			this.edit_view_ui_dic[key].clearErrorStyle();
		}

	},

	//Called after set current_edit_record
	setEditViewData: function() {
		this.is_changed = false;
		this.initEditViewData();
		this.initTabData();
		this.switchToProperTab();

	},

	switchToProperTab: function() {
		if ( LocalCacheData.all_url_args &&
			LocalCacheData.all_url_args.hasOwnProperty( 'tab' ) &&
			LocalCacheData.all_url_args.tab.length > 0 &&
			LocalCacheData.current_open_primary_controller.viewId === this.viewId ) {

			var target_node = this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().filter( function() {
				var value = $( this ).text().replace( /\/|\s+/g, '' );
				return value === LocalCacheData.all_url_args.tab;
			} );

			var target_index = 0;
			if ( target_node.length > 0 ) {
				target_node = $( target_node[0] );
				target_index = target_node.index();
			}
			this.edit_view_tab.tabs( 'select', target_index );
		}
	},

	//Call this from setEditViewData
	initTabData: function() {
		//Handle most case that one tab and one audit tab
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	//Call this from setEditViewData
	initEditViewData: function() {
		var $this = this;

		//add this.grid to fix exception
		//Error: Unable to get property 'getGridParam' of undefined or null reference in https://villa.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 2523
		if ( !this.edit_only_mode && this.navigation && this.grid ) {

			var grid_current_page_items = this.grid.getGridParam( 'data' );

			var navigation_div = this.edit_view.find( '.navigation-div' );

			//Error: TypeError: this.current_edit_record is undefined in https://villa.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141230-103725 line 2673
			if ( this.current_edit_record && Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					this.navigation.setSourceData( grid_current_page_items );
					this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
					this.navigation.setPagerData( this.pager_data );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( this.current_edit_record );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		this.setUIWidgetFieldsToCurrentEditRecord();

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.setMassEditMode ) ) {
					widget.setMassEditMode( true );
				}

			}
			$.each( this.unique_columns, function( index, value ) {
				if ( Global.isSet( $this.edit_view_ui_dic[value] ) && Global.isSet( $this.edit_view_ui_dic[value].setEnabled ) ) {
					$this.edit_view_ui_dic[value].setEnabled( false );
				}

			} );

		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

	setUIWidgetFieldsToCurrentEditRecord: function() {
		if ( this.is_mass_editing ) {
			return;
		}

		var $this = this;

		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

//			//Set all UI field to current edit record, we need validate all UI field when save and validate
			//use != to ingore string or number, value from html is string.

			//Error: TypeError: $this.current_edit_record is undefined in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-122453 line 2702
			if ( $this.current_edit_record && !Global.isSet( $this.current_edit_record[key] ) ) {

				$this.current_edit_record[key] = false;

			}

		}
	},

	setDefaultData: function( columnsArr ) {
		var $this = this;
		$.each( columnsArr, function( field, value ) {
			if ( Global.isSet( $this.current_edit_record[field] ) ) {

			} else {
				$this.current_edit_record[field] = value;
			}
		} )

	},

	collectUIDataToCurrentEditRecord: function() {
		if ( this.is_mass_editing ) {
			return;
		}
		var $this = this;
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];

			//only check dropdownlist
			if ( !widget.hasClass( 't-select' ) ) {
				continue;
			}

			var value = widget.getValue();

//			//Set all UI field to current edit record, we need validate all UI field when save and validate
			//use != to ingore string or number, value from html is string.
			//is visible make sure the widget is shown on screen of current select type

			//Error: TypeError: undefined is not an object (evaluating '$this.current_edit_record[key]') in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141230-124906 line 2792 
			if ( value && $this.current_edit_record && $this.current_edit_record[key] != value ) {

				if ( !value || value === '0' || (Global.isArray( value ) && value.length === 0) ) {
					$this.current_edit_record[key] = false;
				} else {
					$this.current_edit_record[key] = value;
				}

			}

		}
	},

	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country': //popular case
						this.eSetProvince( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	putInputToInsideFormItem: function( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
//		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
//
//		form_item_label_div.attr( 'class', 'edit-view-form-item-sub-label-div' );

		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) + ': ' );

		form_item_input_div.append( form_item_input );

		return form_item;
	},

	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function() {
		// Remove this on 14.9.14 because adding tab url support, ned set url when tab index change and
		// need know waht's current doing action. See if this cause any problem
		//LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );

	},

	setNavigationArrowsStatus: function() {

		var left_arrow = this.edit_view.find( '.left-click' );
		var right_arrow = this.edit_view.find( '.right-click' );
		var $this = this;

		left_arrow.unbind( 'click' ).click( function() {

			if ( !left_arrow.hasClass( 'disabled' ) ) {
				$this.onLeftArrowClick();
			}

		} );

		right_arrow.unbind( 'click' ).click( function() {

			if ( !right_arrow.hasClass( 'disabled' ) ) {
				$this.onRightArrowClick();
			}

		} );

		this.setNavigationArrowsEnabled();

	},

	setNavigationArrowsEnabled: function() {

		var left_arrow = this.edit_view.find( '.left-click' );
		var right_arrow = this.edit_view.find( '.right-click' );

		left_arrow.removeClass( 'disabled' );
		right_arrow.removeClass( 'disabled' );

		if ( !this.navigation ) {
			return;
		}

		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();

		if ( !source_data ) {
			return;
		}

		if ( selected_index === 0 ) {
			left_arrow.addClass( 'disabled' );
		}

		if ( selected_index === source_data.length - 1 ) {
			right_arrow.addClass( 'disabled' );
		}
	},

	onLeftArrowClick: function() {
		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();

		if ( selected_index > 0 ) {
			var next_select_item = this.navigation.getItemByIndex( selected_index - 1 );

		} else {
//			next_select_item = this.navigation.getItemByIndex( source_data.length - 1 );

			this.onCancelClick();
			return;

		}

		ProgressBar.showOverlay();

		if ( this.is_viewing ) {
			this.onViewClick( next_select_item.id ); //Dont refresh UI
		} else {
			this.onEditClick( next_select_item.id ); //Dont refresh UI
		}

		this.setNavigationArrowsEnabled();
	},

	refreshCurrentRecord: function() {
		var next_select_item = this.navigation.getItemByIndex( this.navigation.getSelectIndex() );
		ProgressBar.showOverlay();
		if ( this.is_viewing ) {
			this.onViewClick( next_select_item.id ); //Dont refresh UI
		} else {
			this.onEditClick( next_select_item.id ); //Dont refresh UI
		}

		this.setNavigationArrowsEnabled();
	},

	onRightArrowClick: function() {
		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();

		//Error: Uncaught TypeError: Cannot read property 'length' of null in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141230-125919 line 2956
		if ( !source_data ) {
			return;
		}

		if ( selected_index < (source_data.length - 1) ) {
			var next_select_item = this.navigation.getItemByIndex( (selected_index + 1) );

		} else {
//			next_select_item = this.navigation.getItemByIndex( 0 );
			this.onCancelClick();
			return;
		}

		ProgressBar.showOverlay();
		if ( this.is_viewing ) {
			this.onViewClick( next_select_item.id ); //Dont refresh UI
		} else {
			this.onEditClick( next_select_item.id ); //Dont refresh UI
		}

		this.setNavigationArrowsEnabled();
	},

	setParentContextMenuAfterSubViewClose: function() {

		//Error: Uncaught TypeError: Cannot read property 'buildContextMenu' of null in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.6-20141027-085016 line 2887
		if ( !this.parent_view_controller ) {
			return;
		}

		this.parent_view_controller.buildContextMenu();

		if ( this.parent_view_controller.edit_view ) {
			this.parent_view_controller.setEditMenu();
		} else {
			this.parent_view_controller.setDefaultMenu();
		}
	},

	removeEditView: function() {

		if ( this.edit_view ) {
			this.edit_view.remove();
		}
		this.edit_view = null;
		this.edit_view_tab = null;
		this.is_mass_editing = false;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_changed = false;
		this.mass_edit_record_ids = [];
		this.edit_view_tab_selected_index = 0;
		LocalCacheData.current_doing_context_action = '';

		if ( this.edit_only_mode ) {
			var current_url = window.location.href;
			if ( current_url.indexOf( '&sm' ) > 0 ) {
				current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				Global.setURLToBrowser( current_url );
			}

			LocalCacheData.current_open_edit_only_controller = null;
		}

		// reset parent context menu if edit only mode
		if ( !this.edit_only_mode ) {
			this.setDefaultMenu();
			this.initRightClickMenu();
		} else {
			this.setParentContextMenuAfterSubViewClose();

		}

		this.reSetURL();

		//If there is a action in url, add it back. So we have correct url when set tabs urls
		//This caused a bug where whenever saving a punch on Attendance ->TimeSheet, it would re-open the edit view, same with navigating between weeks, or even deleting punches in some cases.
		//This need to put under reSetUrl and need clean url_agrs until it set from onViewChange in router again
		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.a ) {
			LocalCacheData.current_doing_context_action = LocalCacheData.all_url_args.a;
		}

		this.sub_log_view_controller = null;
		this.edit_view_ui_dic = {};
		this.edit_view_form_item_dic = {};
		this.edit_view_error_ui_dic = {};
	},

	reSetURL: function() {
		if ( this.canSetURL() ) {
			window.location = Global.getBaseURL() + '#!m=' + this.viewId;
			LocalCacheData.all_url_args = null;
		}
	},

	getGridSelectIdArray: function() {

		if ( !this.grid ) {
			return false;
		}

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand3.timetrex.com/interface/html5/#!m=RecurringScheduleTemplateControl line 1007
		var result = this.grid.jqGrid( 'getGridParam', 'selarrrow' );

		if ( !result ) {
			result = [];
		}

		return result;
	},

//	getGridSelectRowArray: function( colName ) {
//		var selRowId = this.getGridSelectIdArray();
//		var result = this.grid.jqGrid( 'getCell', selRowId, colName );
//
//		return result;
//	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuMassEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.copyPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteAndNextIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndNextIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndContinueIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndAddIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuCopyAsNewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.copyAsNewPermissionValidate( pId )) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuLoginIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.validate( 'company', 'login_other_user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.getGridSelectIdArray().length !== 1 ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCancelIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.sub_view_mode ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuImportIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuPermissionWizardIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuPermissionWizardIcon: function( context_btn, pId ) {

	},

	setEditMenuImportIcon: function( context_btn, pId ) {
		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setEditMenuAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.is_changed ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuEditIcon: function( context_btn, pId ) {

		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuNavEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setEditMenuNavViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuMassEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyIcon: function( context_btn, pId ) {
		if ( !this.copyPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyAndAddIcon: function( context_btn, pId ) {
		if ( !this.copyAsNewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveIcon: function( context_btn, pId ) {

		this.saveValidate( context_btn, pId );

		if ( this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndCopyValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndNextIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCancelIcon: function( context_btn, pId ) {

	},

	ifContextButtonExist: function( value ) {
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			if ( id === value ) {
				return true;
			}
		}

		return false;
	},

	//Call this when select grid row
	//Call this when setLayout
	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand0.timetrex.com/interface/html5/#!m=Client line 308
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.permission_wizard:
					this.setDefaultMenuPermissionWizardIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}

				continue;
			}

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.import_icon:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.permission_wizard:
					this.setEditMenuPermissionWizardIcon( context_btn );
					break;
				case ContextMenuIconName.login:
					this.setEditMenuLoginIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuLoginIcon: function( context_btn ) {
		context_btn.addClass( 'disable-image' );
	},

	//Disable context menu if no visible item
	setContextMenuGroupVisibility: function() {

		var ribbon_menu = Global.topContainer().find( '#' + this.viewId + 'ContextMenu' );
		var menus = ribbon_menu.find( '.menu' );

		var len = menus.length;

		for ( var i = 0; i < len; i++ ) {
			var menu = $( menus[i] );
			var li_array = menu.find( 'li' );

			var all_invisible = true;

			var li_len = li_array.length;

			for ( var j = 0; j < li_len; j++ ) {
				var li = $( li_array[j] );
				if ( !li.hasClass( 'invisible-image' ) ) {
					all_invisible = false;
					break;
				}
			}

			if ( all_invisible ) {
				menu.addClass( 'invisible-image' );
			} else {
				menu.removeClass( 'invisible-image' );
			}

		}

		//go context menu after everything set done
		if ( this.need_switch_to_context_menu ) {
			this.selectContextMenu();
		}

	},

	setErrorMenu: function() {
		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.cancel:
					break;
				default:
					context_btn.addClass( 'disable-image' );
					break;
			}

		}
	},

	render: function() {

		var $this = this;

		$( window ).resize( function() {
			if ( $this.grid ) {
				$this.setGridSize();

			}

			if ( $this.edit_view ) {
				$this.setEditViewTabSize();
			}

		} );

		//Create search panel only when show as a main view

		if ( !this.sub_view_mode && !this.edit_only_mode ) {
			var searchPanelWidget = Global.loadWidget( 'global/widgets/search_panel/SearchPanel.html' );
			var search_panel_w = $( searchPanelWidget );

			$( this.el ).prepend( search_panel_w );

			if ( !this.show_search_tab ) {
				search_panel_w.hide();
			}

			this.search_panel = search_panel_w.SearchPanel( {viewController: this} );

			this.search_panel.bind( 'searchTabSelect', $.proxy( this.onSearchTabSelect, this ) );

			this.buildSearchFields();

			this.buildBasicSearchUI();

			this.buildAdvancedSearchUI();

			this.buildSearchAndLayoutUI();

			//Work around that the li offset is empty in chrome
			setTimeout( function() {
				$this.setCurrentViewPosition();

			}, 500 );

		}

	},

	setCurrentViewPosition: function() {
		var current_view_div = this.search_panel.find( '.layout-selector-div' );
		var saved_layout_li = this.search_panel.find( "a[ref='saved_layout']" ).parent();
		var offset_left = saved_layout_li.offset().left;

		current_view_div.css( 'left', offset_left + saved_layout_li.width() + 20 );
	},

	//Build fields when search tab change
	onSearchTabSelect: function( e, e1, ui ) {

		var tab_id = $( ui.tab ).attr( 'ref' );

		switch ( tab_id ) {
			case 'basic_search':

				if ( this.search_panel.getLastSelectTabId() !== 'saved_layout' ) {
					this.getSearchPanelFilter( 1, true );
					this.buildBasicSearchUI();
					this.setSearchPanelFilter( false, 0 );
				}

				break;
			case 'adv_search':
				if ( this.search_panel.getLastSelectTabId() !== 'saved_layout' ) {
					this.getSearchPanelFilter( 0, true );
					this.buildAdvancedSearchUI();
					this.setSearchPanelFilter( false, 1 );
				}

				break;
			case 'saved_layout':
				this.getSearchPanelFilter( this.search_panel.getLastSelectTabIndex() );
		}

	},

	initDropDownOptions: function( options, callBack ) {
		var len = options.length;
		var complete_count = 0;
		var option_result = [];

		for ( var i = 0; i < len; i++ ) {
			var option_info = options[i];

			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result )
			}
		}

	},

	buildWidgetContainerWithTextTip: function( widget, tip ) {
		var h_box = $( "<div class='h-box'></div>" );

		var text_box = Global.loadWidgetByName( FormItemType.TEXT );
		text_box.css( 'margin-left', '10px' );
		text_box.TText();
		text_box.setValue( tip );

		h_box.append( widget );
		h_box.append( text_box );

		return h_box;

	},

	//Set option list for search panel and edit view
	initDropDownOption: function( option_name, field_name, api, callBack, array_name ) {
		var $this = this;
		if ( !Global.isSet( api ) ) {
			api = this.api;
		}

		if ( !Global.isSet( field_name ) || !field_name ) {
			field_name = option_name + '_id';
		}
		api.getOptions( option_name, {
			onResult: function( res ) {
				var result = res.getResult();

				if ( array_name ) {
					$this[array_name] = Global.buildRecordArray( result );
				} else {

					$this[option_name + '_array'] = Global.buildRecordArray( result );
				}

				if ( !$this.sub_view_mode ) {

					if ( Global.isSet( $this.basic_search_field_ui_dic[field_name] ) ) {
						$this.basic_search_field_ui_dic[field_name].setSourceData( Global.buildRecordArray( result ) );
					}

					if ( Global.isSet( $this.adv_search_field_ui_dic[field_name] ) ) {
						$this.adv_search_field_ui_dic[field_name].setSourceData( Global.buildRecordArray( result ) );
					}
				}
				if ( Global.isSet( callBack ) ) {
					callBack( res );
				}

			}
		} );
	},

	clearSearchPanel: function() {

		for ( var key in this.basic_search_field_ui_dic ) {
			var search_input = this.basic_search_field_ui_dic[key];
			search_input.setValue( null );
		}

		for ( key in this.adv_search_field_ui_dic ) {
			search_input = this.adv_search_field_ui_dic[key];
			search_input.setValue( null );
		}

	},

	onSearch: function() {
		var do_update = false;
//
//		if ( this.search_panel ) {
//			this.search_panel.attr( 'search_complete', false );
//		}

		//don't keep temp filter any more, set them when change tab
		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;

		this.getSearchPanelFilter();

		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( "option:contains('" + BaseViewController.default_layout_name + "')" ).attr( 'value' );

			if ( !default_layout_id ) {
				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}
			var layout_name = BaseViewController.default_layout_name;

		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		ProgressBar.showOverlay();
		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onClearSearch: function() {
		var do_update = false;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( "option:contains('" + BaseViewController.default_layout_name + "')" ).attr( 'value' );

			if ( !default_layout_id ) {
				this.clearSearchPanel();
				this.filter_data = null;
				this.temp_adv_filter_data = null;
				this.temp_basic_filter_data = null;
				this.column_selector.setSelectGridData( this.default_display_columns );
				this.sort_by_selector.setValue( null );

				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}

			var layout_name = BaseViewController.default_layout_name;
			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			do_update = true;

		} else {

			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			this.column_selector.setSelectGridData( this.default_display_columns );
			this.sort_by_selector.setValue( null );

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

//		this.column_selector.setSelectGridData( this.default_display_columns );

		this.sort_by_selector.setValue( null );

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		if ( do_update ) {
			var args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.display_columns = selected_display_columns;
			args.data.filter_data = filter_data;
			args.data.filter_sort = sort_filter;

		}

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSaveNewLayout: function( default_layout_name ) {

		if ( Global.isSet( default_layout_name ) ) {
			var layout_name = default_layout_name
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		var $this = this;

		var a_layout_name = ALayoutCache.layout_dic[this.script_name];
		if ( a_layout_name && ALayoutCache.layout_dic[a_layout_name] ) {
			ALayoutCache.layout_dic[a_layout_name] = null;
		}

		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();

				} else {
					TAlertManager.showErrorAlert( res );
				}

			}
		} );

	},

	onUpdateLayout: function() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var sort_filter = this.getSearchPanelSortFilter();
		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.filter_sort = sort_filter;

		var $this = this;

		var a_layout_name = ALayoutCache.layout_dic[this.script_name];
		if ( a_layout_name && ALayoutCache.layout_dic[a_layout_name] ) {
			ALayoutCache.layout_dic[a_layout_name] = null;
		}

		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	clearAwesomeboxLayoutCache: function() {
		// Removed saved view layout for awesomebox if it existed.
		if ( ALayoutCache.layout_dic && ALayoutCache.layout_dic[this.script_name] ) {
			ALayoutCache.layout_dic[ALayoutCache.layout_dic[this.script_name]] = null;
		}
	},

	onDeleteLayout: function() {
		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );

		var $this = this;
		this.user_generic_data_api.deleteUserGenericData( selectId, {
			onResult: function( res ) {
				if ( res.isValid() ) {
					$this.clearAwesomeboxLayoutCache();
					$this.need_select_layout_name = $this.select_layout.name;
					$this.initLayout();
				}

			}
		} );
	},

	buildSearchFields: function() {
		//Override in all subview
	},

	buildBasicSearchUI: function() {
		if ( !this.search_fields ) {
			return
		}

		var basic_search_div = this.search_panel.find( 'div #basic_search_content_div' );

		var len = this.search_fields.length;
		var $this = this;

		var column1 = basic_search_div.find( '.first-column' );
		var column2 = basic_search_div.find( '.second-column' );
		var column3 = basic_search_div.find( '.third-column' );

		var already_created_ui = false;
		$.each( this.search_fields, function( index, search_field ) {
			if ( Global.isSet( $this.basic_search_field_ui_dic[search_field.get( 'field' )] ) ) {
				already_created_ui = true;
				return false;
			}

			if ( !search_field.get( 'basic_search' ) ) {
				return true;
			}

			var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
			var form_item_label = form_item.find( '.form-item-label' );
			var form_item_input_div = form_item.find( '.form-item-input-div' );
			var form_item_input = $this.getFormItemInput( search_field );
			form_item_label.text( search_field.get( 'label' ) + ': ' );
			form_item_input_div.append( form_item_input );

			switch ( search_field.get( 'in_column' ) ) {
				case 1:
					column1.append( form_item );
					column1.append( "<div class='clear-both-div'></div>" );
					break;
				case 2:
					column2.append( form_item );
					column2.append( "<div class='clear-both-div'></div>" );
					break;
				case 3:
					column3.append( form_item );
					column3.append( "<div class='clear-both-div'></div>" );
					break;
			}

			$this.basic_search_field_ui_dic[search_field.get( 'field' )] = form_item_input;
		} );

		if ( !already_created_ui ) {
			this.onBuildBasicUIFinished()
		}

	},

	buildAdvancedSearchUI: function() {
		if ( !this.search_fields ) {
			return
		}

		var advSearchDiv = this.search_panel.find( 'div #adv_search_content_div' );

		var $this = this;

		var column1 = advSearchDiv.find( '.first-column' );
		var column2 = advSearchDiv.find( '.second-column' );
		var column3 = advSearchDiv.find( '.third-column' );

		var already_created_ui = false;
		var no_adv_ui = true;

		$.each( this.search_fields, function( index, search_field ) {

			if ( Global.isSet( $this.adv_search_field_ui_dic[search_field.get( 'field' )] ) ) {
				already_created_ui = true;
				no_adv_ui = false;
				return false;
			}

			if ( !search_field.get( 'adv_search' ) ) {
				return true;
			}

			var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
			var form_item_label = form_item.find( '.form-item-label' );
			var form_item_input_div = form_item.find( '.form-item-input-div' );
			var form_item_input = $this.getFormItemInput( search_field );
			form_item_label.text( search_field.get( 'label' ) + ': ' );
			form_item_input_div.append( form_item_input );

			switch ( search_field.get( 'in_column' ) ) {
				case 1:
					column1.append( form_item );
					column1.append( "<div class='clear-both-div'></div>" );
					break;
				case 2:
					column2.append( form_item );
					column2.append( "<div class='clear-both-div'></div>" );
					break;
				case 3:
					column3.append( form_item );
					column3.append( "<div class='clear-both-div'></div>" );
					break;
			}

			$this.adv_search_field_ui_dic[search_field.get( 'field' )] = form_item_input;
			no_adv_ui = false
		} );

		if ( no_adv_ui ) {

			this.search_panel.hideAdvSearchPanel();
		}

		if ( !already_created_ui ) {
			this.onBuildAdvUIFinished()
		}

	},

	onSetSearchFilterFinished: function() {

	},

	onBuildAdvUIFinished: function() {
		//Always override in sub class
	},

	onBuildBasicUIFinished: function() {
		//Always override in sub class
	},

	getFormItemInput: function( search_field ) {
		var input;
		var form_type = search_field.get( 'form_item_type' );

		switch ( form_type ) {
			case FormItemType.AWESOME_BOX:
				input = Global.loadWidget( 'global/widgets/awesomebox/AComboBox.html' );
				input = $( input );
				var show_search = false;
				var key;

				if ( search_field.get( 'layout_name' ) !== ALayoutIDs.OPTION_COLUMN && search_field.get( 'layout_name' ) !== ALayoutIDs.TREE_COLUMN ) {
					show_search = true;
					key = 'id';
				} else {

					if ( search_field.get( 'layout_name' ) === ALayoutIDs.TREE_COLUMN ) {
						key = 'id';
					} else {
						key = 'value';
					}

				}

				input.AComboBox( {
					api_class: search_field.get( 'api_class' ),
					allow_multiple_selection: search_field.get( 'multiple' ),
					layout_name: search_field.get( 'layout_name' ),
					tree_mode: search_field.get( 'tree_mode' ),
					default_args: search_field.get( 'default_args' ),
					show_search_inputs: show_search,
					set_any: search_field.get( 'set_any' ),
					addition_source_function: search_field.get( 'addition_source_function' ),
					script_name: search_field.get( 'script_name' ),
					custom_first_label: search_field.get( 'custom_first_label' ),
					key: key,
					search_panel_model: true,
					field: search_field.get( 'field' )
				} );

				if ( search_field.get( 'customSearchFilter' ) ) {
					input.customSearchFilter = search_field.get( 'customSearchFilter' );
				}

				break;
			case FormItemType.TEXT_INPUT:
				input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
				input = $( input );
				input.TTextInput( {
					field: search_field.get( 'field' )
				} );
				break;
			case FormItemType.PASSWORD_INPUT:
				input = Global.loadWidget( 'global/widgets/text_input/TPasswordInput.html' );
				input = $( input );
				input.TTextInput( {
					field: search_field.get( 'field' )
				} );
				break;
			case FormItemType.COMBO_BOX:
				input = Global.loadWidget( 'global/widgets/combobox/TComboBox.html' );
				input = $( input );
				input.TComboBox( {
					field: search_field.get( 'field' ),
					set_any: true
				} );
				break;
			case FormItemType.TAG_INPUT:
				input = Global.loadWidget( 'global/widgets/tag_input/TTagInput.html' );
				input = $( input );
				input.TTagInput( {
					field: search_field.get( 'field' ),
					object_type_id: search_field.get( 'object_type_id' )
				} );

				break;
			case FormItemType.DATE_PICKER:
				input = Global.loadWidgetByName( form_type );
				input = $( input );
				input.TDatePicker( {
					field: search_field.get( 'field' )
				} );

				break;
			case FormItemType.CHECKBOX:
				input = Global.loadWidget( 'global/widgets/checkbox/TCheckbox.html' );
				input = $( input );
				input.TCheckbox( {
					field: search_field.get( 'field' )
				} );

				break;
		}

		return input;
	},

	buildSearchAndLayoutUI: function() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		//Display Columns

		var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		var column_selector = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );

		this.column_selector = $( column_selector );

		this.column_selector = this.column_selector.ADropDown( {
			display_show_all: false,
			id: this.ui_id + '_column_selector',
			key: 'value',
			allow_drag_to_order: true,
			display_close_btn: false
		} );

		form_item_label.text( $.i18n._( 'Display Columns' ) + ':' );
		form_item_input_div.append( this.column_selector );

		layout_div.append( form_item );

		layout_div.append( "<div class='clear-both-div'></div>" );

		this.column_selector.setColumns( [
			{name: 'label', index: 'label', label: $.i18n._('Column Name'), width: 100, sortable: false}
		] );

		//Sort By
		form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );
		this.sort_by_selector = $( Global.loadWidget( 'global/widgets/awesomebox/AComboBox.html' ) );
		this.sort_by_selector = this.sort_by_selector.AComboBox( {
			allow_drag_to_order: true,
			allow_multiple_selection: true,
			set_empty: true,
			layout_name: ALayoutIDs.SORT_COLUMN
		} );

		form_item_label.text( $.i18n._( 'Sort By' ) + ':' );
		form_item_input_div.append( this.sort_by_selector );

		layout_div.append( form_item );

		layout_div.append( "<div class='clear-both-div'></div>" );

		//Save and update layout

		form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) + ':' );

		this.save_search_as_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
		this.save_search_as_input = $( this.save_search_as_input );
		this.save_search_as_input.TTextInput();

		var save_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Save' ) + "' />" )

		form_item_input_div.append( this.save_search_as_input );
		form_item_input_div.append( save_btn );

		var $this = this;
		save_btn.click( function() {
			$this.saving_layout_in_layout_tab = true;
			$this.onSaveNewLayout();
		} );

		//Previous Saved Layout

		this.previous_saved_layout_div = $( "<div class='previous-saved-layout-div'></div>" );

		form_item_input_div.append( this.previous_saved_layout_div );

		form_item_label = $( "<span style='margin-left: 5px' >" + $.i18n._( 'Previous Saved Searches' ) + ":</span>" );
		this.previous_saved_layout_div.append( form_item_label );

		this.previous_saved_layout_selector = $( "<select style='margin-left: 5px' class='t-select'>" );
		var update_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Update' ) + "' />" );
		var del_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Delete' ) + "' />" );

		update_btn.click( function() {
			$this.onUpdateLayout();
		} );

		del_btn.click( function() {
			$this.onDeleteLayout();
		} );

		this.previous_saved_layout_div.append( this.previous_saved_layout_selector );
		this.previous_saved_layout_div.append( update_btn );
		this.previous_saved_layout_div.append( del_btn );

		layout_div.append( form_item );

		this.previous_saved_layout_div.css( 'display', 'none' );

	},

	onGridSelectRow: function() {
		this.setDefaultMenu();
	},

	setPreviousSavedSearchSourcesAndValue: function( layouts_array ) {
		var $this = this;

		this.previous_saved_layout_selector.empty();
		if ( layouts_array && layouts_array.length > 0 ) {
			this.previous_saved_layout_div.css( 'display', 'inline' );

			var len = layouts_array.length;
			for ( var i = 0; i < len; i++ ) {
				var item = layouts_array[i];
				this.previous_saved_layout_selector.append( '<option value="' + item.id + '">' + item.name + '</option>' )
			}

			$( this.previous_saved_layout_selector.find( 'option' ) ).filter( function() {
				return parseInt( $( this ).attr( 'value' ) ) === $this.select_layout.id;
			} ).prop( 'selected', true ).attr( 'selected', true );

		} else {
			this.previous_saved_layout_div.css( 'display', 'none' );
		}
	},

	setSelectLayout: function( column_start_from ) {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		var column_info_array = [];

		if ( !this.select_layout ) { //Set to default layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
			this.select_layout.data.display_columns = this.default_display_columns;
		}
		var layout_data = this.select_layout.data;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( !this.sub_view_mode ) {

			//Set Display Column in layout panel
			this.column_selector.setSelectGridData( display_columns );

			//Set Sort by awesomebox in layout panel
			this.sort_by_selector.setSourceData( this.buildSortSelectorUnSelectColumns( display_columns ) );
			this.sort_by_selector.setValue( this.buildSortBySelectColumns() );

			//Set Previoous Saved layout combobox in layout panel
			var layouts_array = this.search_panel.getLayoutsArray();

			this.setPreviousSavedSearchSourcesAndValue( layouts_array );

		}

		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}
		for ( var i = start_from; i < len; i++ ) {
			var view_column_data = display_columns[i];

			var column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );
		}

		if ( !this.grid ) {
			this.grid = grid;

			this.grid = this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: (Global.bodyWidth() - 14),
				rowNum: 10000,
				colNames: [],
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				onSelectAll: function() {
					$this.onGridSelectAll();
				},
				gridComplete: function() {
					if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {

						$this.setGridColumnsWidth();
					}

				},
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				colModel: column_info_array,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true

			} );

		} else {

			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );

			this.grid = this.grid.jqGrid( {
				altRows: true,
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				data: [],
				rowNum: 10000,
				onSelectAll: function() {
					$this.onGridSelectAll();
				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				gridComplete: function() {
					if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {

						$this.setGridColumnsWidth();
					}
				},
				sortable: false,
				datatype: 'local',
				width: (Global.bodyWidth() - 14),
				colNames: [],
				colModel: column_info_array,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true
			} );

		}

		$this.setGridSize();

		//Add widget on UI and bind events. Next set data in it in search result.
		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.paging_widget.parent().length > 0 ) {
				this.paging_widget.remove();
			}

			this.paging_widget.css( 'width', this.grid.width() );
			this.grid.append( this.paging_widget );

			this.paging_widget.click( $.proxy( this.onPaging, this ) );

		} else {
			if ( this.paging_widget.parent().length < 1 ) {
				$( this.el ).find( '.total-number-div' ).append( this.paging_widget );
				$( this.el ).find( '.bottom-div' ).append( this.paging_widget_2 );

				this.paging_widget.bind( 'paging', $.proxy( this.onPaging2, this ) );
				this.paging_widget_2.bind( 'paging', $.proxy( this.onPaging2, this ) );
			}

		}

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		if ( !this.sub_view_mode ) {
			this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.
		}

		this.showGridBorders();

	},

	onGridSelectAll: function() {
		this.setDefaultMenu();
	},

	unSelectAll: function() {
		this.grid.resetSelection();
	},

	onGridDblClickRow: function() {
		var len = this.context_menu_array.length;

		var need_break = false;

		for ( var i = 0; i < len; i++ ) {

			if ( need_break ) {
				break;
			}

			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.edit:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onEditClick();
						return;
					}
					break;
			}
		}

		for ( i = 0; i < len; i++ ) {

			if ( need_break ) {
				break;
			}

			context_btn = this.context_menu_array[i];
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.view:
					need_break = true;
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onViewClick();
						return;
					}
					break;
			}
		}

		for ( i = 0; i < len; i++ ) {

			context_btn = this.context_menu_array[i];
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.add:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onAddClick();
						return;
					}
					break;
			}
		}
	},

	onPaging: function() {
		this.search( true, 'next' );
	},

	onPaging2: function( e, action, page_number ) {

		this.search( true, action, page_number );
	},

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindGridColumnEvents: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		if ( !display_columns ) {
			return;
		}

		var len = display_columns.length;

		this.t_grid_header_array = [];

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			var column_header = $( $( this.el ).find( '#gbox_' + this.ui_id + '_grid' ).find( 'div #jqgh_' + this.ui_id + '_grid_' + column_info.name ) );

			this.t_grid_header_array.push( column_header.TGridHeader() );
			column_header.bind( 'click', onColumnHeaderClick );
		}

		var $this = this;

		function onColumnHeaderClick( e ) {

			var field = $( this ).attr( 'id' );
			field = field.substring( 10 + $this.ui_id.length + 1, field.length );

			if ( field === 'cb' ) { //first column, check box column.
				return;
			}

			if ( e.metaKey || e.ctrlKey ) {
				$this.buildSortCondition( false, field );
			} else {
				$this.buildSortCondition( true, field );

			}

			if ( $this.sub_view_mode ) {
				$this.search();
				$this.setGridHeaderStyle();
			} else {
				$this.sort_by_selector.setValue( $this.buildSortBySelectColumns() );
				$this.onSearch();
			}

		}

	},

	getValidSearchFilter: function() {
		var validFilterData = {};
		for ( var key in  this.filter_data ) {
			if ( Global.isSet( this.filter_data[key].value ) && this.filter_data[key].value !== '' ) {
				validFilterData[key] = this.filter_data[key];
			}
		}

		return validFilterData;
	},

	getSearchPanelDisplayColumns: function() {
		var display_columns = [];
		var select_items = this.column_selector.getSelectItems();

		if ( select_items && select_items.length > 0 ) {
			$.each( select_items, function( index, content ) {
				display_columns.push( content.value );
			} );
		}

		return display_columns;
	},

	getSearchPanelSortFilter: function() {
		var sort_filter = [];
		var select_items = this.sort_by_selector.getValue( true );

		if ( select_items && select_items.length > 0 ) {
			$.each( select_items, function( index, content ) {
				var sort = {}
				sort[content.value] = content.sort;
				sort_filter.push( sort );
			} );
		}

		return sort_filter;
	},

	getSearchPanelFilter: function( getFromTabIndex, save_temp_filter ) {

		if ( Global.isSet( getFromTabIndex ) ) {
			var search_tab_select_index = getFromTabIndex;
		} else {
			search_tab_select_index = this.search_panel.getSelectTabIndex();
		}

//		var basic_fields_len = this.search_fields.length;
		var target_ui_dic = null;

		if ( search_tab_select_index === 0 ) {
			this.filter_data = [];
			target_ui_dic = this.basic_search_field_ui_dic;
		} else if ( search_tab_select_index === 1 && this.search_panel.isAdvTabVisible() ) {
			this.filter_data = [];
			target_ui_dic = this.adv_search_field_ui_dic;
		} else {
			return;
		}

		var $this = this;
		$.each( target_ui_dic, function( key, content ) {
			$this.filter_data[key] = {field: key, id: '', value: target_ui_dic[key].getValue( true )};

			if ( $this.temp_basic_filter_data ) {
				$this.temp_basic_filter_data[key] = $this.filter_data[key];
			}

			if ( $this.temp_adv_filter_data ) {
				$this.temp_adv_filter_data[key] = $this.filter_data[key];
			}
		} );

		if ( save_temp_filter ) {
			if ( search_tab_select_index === 0 ) {
				$this.temp_basic_filter_data = Global.clone( $this.filter_data );
			} else if ( search_tab_select_index === 1 ) {
				$this.temp_adv_filter_data = Global.clone( $this.filter_data );
			}

		}

	},

	//Set value to field UI in search tab
	setSearchPanelFilter: function( autoChangeTab, tab_index ) {

		this.clearSearchPanel();

		if ( !Global.isSet( autoChangeTab ) ) {
			autoChangeTab = false;
		}

		var filter = this.filter_data;

		if ( Global.isSet( tab_index ) ) {
			if ( tab_index === 0 && this.temp_basic_filter_data ) {
				filter = this.temp_basic_filter_data;
			} else if ( tab_index === 1 && this.temp_adv_filter_data ) {
				filter = this.temp_adv_filter_data;
			}
		}

		if ( !Global.isSet( filter ) || !this.search_fields ) {
			return;
		}

		var basic_fields_len = this.search_fields.length;

		for ( var i = 0; i < basic_fields_len; i++ ) {
			var field = this.search_fields[i];
			var field_name = field.get( 'field' );

			var search_input = this.basic_search_field_ui_dic[field_name];
			var search_input_1 = this.adv_search_field_ui_dic[field_name];

			if ( Global.isSet( filter[field_name] ) ) {

				if ( Global.isSet( search_input ) ) {

					if ( $.type( filter[field_name] ) === 'string' || $.type( filter[field_name] ) === 'number' ) {
						search_input.setValue( filter[field_name] );
					} else {

						if ( filter[field_name].hasOwnProperty( 'value' ) ) { // when set default filter don't have 'value' in it, For example Invoice edit view
							search_input.setValue( filter[field_name].value );
						} else {
							search_input.setValue( filter[field_name] );
						}

					}

				} else if ( autoChangeTab && !this.saving_layout_in_layout_tab ) {
					if ( this.search_panel.getSelectTabIndex() !== 1 ) {
						this.search_panel.setSelectTabIndex( 1, false );
					}
				}

				if ( Global.isSet( search_input_1 ) ) {

					if ( $.type( filter[field_name] ) === 'string' || $.type( filter[field_name] ) === 'number' ) {
						search_input_1.setValue( filter[field_name] );
					} else {
						if ( filter[field_name].hasOwnProperty( 'value' ) ) { // when set default filter don't have 'value' in it, For example Invoice edit view
							search_input_1.setValue( filter[field_name].value );
						} else {
							search_input_1.setValue( filter[field_name] );
						}
					}
//					search_input_1.setValue( filter[field_name].value );
				}

			}

		}

		this.getSearchPanelFilter(); //Make sure filter only has fields on current display ab

		this.search_panel.setSearchFlag( this.getValidSearchFilter() ); // Add ! to tab which has search condition in it

		this.onSetSearchFilterFinished();

	},

	//Set Grid header style for asc or desc
	setGridHeaderStyle: function() {

		var len = this.t_grid_header_array.length;

		for ( var i = 0; i < len; i++ ) {
			var t_grid_header = this.t_grid_header_array[i];
			var field = t_grid_header.attr( 'id' );
			field = field.substring( 10 + this.ui_id.length + 1, field.length );

			t_grid_header.cleanSortStyle();

			if ( this.select_layout.data.filter_sort ) {
				var sort_array_len = this.select_layout.data.filter_sort.length;

				for ( var j = 0; j < sort_array_len; j++ ) {
					var sort_item = this.select_layout.data.filter_sort[j];
					var sortField = Global.getFirstKeyFromObject( sort_item );
					if ( sortField === field ) {

						if ( sort_array_len > 1 ) {
							t_grid_header.setSortStyle( sort_item[sortField], j + 1 );
						} else {
							t_grid_header.setSortStyle( sort_item[sortField], 0 );
						}

					}

				}
			}

		}

	},

	buildSortCondition: function( reset, field ) {
		var next_sort = 'asc';

		if ( reset ) {

			if ( this.select_layout.data.filter_sort && this.select_layout.data.filter_sort.length > 0 ) {
				var len = this.select_layout.data.filter_sort.length;
				var found = false;

				for ( i = 0; i < len; i++ ) {
					var sort_item = this.select_layout.data.filter_sort[i];
					for ( var key in sort_item ) {

						if ( !sort_item.hasOwnProperty( key ) ) {
							continue;
						}

						if ( key === field ) {
							if ( sort_item[key] === 'asc' ) {
								next_sort = 'desc'
							} else {
								next_sort = 'asc'
							}

							found = true;
						}
					}

					if ( found ) {
						break;
					}

				}

			}

			this.select_layout.data.filter_sort = [
				{}
			];
			this.select_layout.data.filter_sort[0][field] = next_sort;

		} else {
			if ( !this.select_layout.data.filter_sort ) {
				this.select_layout.data.filter_sort = [
					{}
				];
				this.select_layout.data.filter_sort[0][field] = 'asc'
			} else {
				len = this.select_layout.data.filter_sort.length;
				found = false;
				for ( var i = 0; i < len; i++ ) {
					sort_item = this.select_layout.data.filter_sort[i];
					for ( key in sort_item ) {

						if ( !sort_item.hasOwnProperty( key ) ) {
							continue;
						}

						if ( key === field ) {
							if ( sort_item[key] === 'asc' ) {
								sort_item[key] = 'desc'
							} else {
								sort_item[key] = 'asc'
							}

							found = true;
						}
					}

					if ( found ) {
						break;
					}

				}

				if ( !found ) {
					this.select_layout.data.filter_sort.push( {} );
					this.select_layout.data.filter_sort[len][field] = 'asc';
				}
			}

		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}
		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined
		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {
				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}
			}
		} else {
			filter.filter_page = 1;
		}
		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}
		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {
			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );
		}
		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		if ( this.refresh_id > 0 ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {
			this.last_select_ids = this.getGridSelectIdArray();
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				var len;

				if ( !Global.isArray( result_data ) && !($this.refresh_id > 0) ) {
					$this.showNoResultCover()
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );

					len = result_data.length;
				}
				if ( $this.refresh_id > 0 ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.getGridParam( 'data' );
					len = grid_source_data.length;

					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}

					var found = false;
					var new_record = result_data[0];

					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
						for ( var i = 0; i < len; i++ ) {
							var record = grid_source_data[i];

							//Fixed === issue. The id set by jQGrid is string type.
							if ( !isNaN( parseInt( record.id ) ) ) {
								record.id = parseInt( record.id );
							}

							if ( record.id == new_record.id ) {
								$this.grid.setRowData( new_record.id, new_record );
								found = true;
								break
							}
						}

						if ( !found ) {
							$this.grid.clearGridData();
							$this.grid.setGridParam( {data: grid_source_data.concat( new_record )} );

							if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGridHeight ) ) {
								len = Global.isSet( len ) ? len : 0;
								$this.resizeSubGridHeight( len + 1 );
							}

							$this.grid.trigger( 'reloadGrid' );
						}
					}

				} else {
					//Set Page data to widget, next show display info when setDefault Menu
					$this.pager_data = result.getPagerData();

					//CLick to show more mode no need this step
					if ( LocalCacheData.paging_type !== 0 ) {
						$this.paging_widget.setPagerData( $this.pager_data );
						$this.paging_widget_2.setPagerData( $this.pager_data );
					}

					if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
						var current_data = $this.grid.getGridParam( 'data' );
						result_data = current_data.concat( result_data );
					}
					$this.grid.clearGridData();
					$this.grid.setGridParam( {data: result_data} );

					if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGridHeight ) ) {
						$this.resizeSubGridHeight( len );
					}

					$this.grid.trigger( 'reloadGrid' );
					$this.reSelectLastSelectItems();

				}

				$this.setGridCellBackGround(); //Set cell background for some views

				ProgressBar.closeOverlay(); //Add this in initData

				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				$this.searchDone();

			}
		} );

	},

	searchDone: function() {
		//the rotate icon from search panel
		var $this = this;
		$( '.search-refresh-rotate' ).removeClass( 'search-refresh-rotate' );

		$( this.el ).attr( 'init_complete', true );

		setTimeout( function() {
			if ( $this.search_panel && typeof $this.search_panel.attr( 'search_complete' ) !== 'undefined' ) {
				$this.search_panel.attr( 'search_complete', true );
			}
		}, 4000 );

	},

	reSelectLastSelectItems: function() {
		var $this = this;
		if ( this.last_select_ids && this.last_select_ids.length > 0 ) {
			$.each( this.last_select_ids, function( index, content ) {
				$this.grid.jqGrid( 'setSelection', content, false );

				if ( $this.grid_select_id_array ) {
					$this.grid_select_id_array.push( content );
				}

			} );

			this.last_select_ids = [];
			if ( !this.edit_view ) {
				this.setDefaultMenu();
			}
		}

	},

	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'edit':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onEditClick( LocalCacheData.edit_id_for_next_open_view );
					LocalCacheData.edit_id_for_next_open_view = null;
				}

				break;
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				if ( !this.edit_view ) {
					this.onAddClick();
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	autoOpenEditOnlyViewIfNecessary: function() {

		//Don't try to open anything if current loading a sub view
		if ( this.sub_view_mode ) {
			return;
		}
		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.sm && !LocalCacheData.current_open_edit_only_controller ) {

			if ( LocalCacheData.all_url_args.sm.indexOf( 'Report' ) < 0 ) {
				IndexViewController.openEditView( this, LocalCacheData.all_url_args.sm, LocalCacheData.all_url_args.sid );
			} else {
				IndexViewController.openReport( this, LocalCacheData.all_url_args.sm );

				if ( LocalCacheData.all_url_args.sid ) {
					LocalCacheData.default_edit_id_for_next_open_edit_view = LocalCacheData.all_url_args.sid;
				}
			}

		}
	},

	setGridCellBackGround: function() {

		//Set backgournd for all policy view and RecurringScheduleTemplateControlView
		if ( this.script_name.indexOf( 'Policy' ) >= 0 ||
			this.script_name === 'RecurringScheduleTemplateControlView' ||
			this.script_name === 'PayCodeView' ||
			this.script_name === 'RecurringHolidayView'
		) {

			var data = this.grid.getGridParam( 'data' );

			//Error: TypeError: data is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}

			var len = data.length;

			for ( var i = 0; i < len; i++ ) {
				var item = data[i];

				if ( item.is_in_use === false ) {
					$( "tr[id='" + item.id + "']" ).addClass( 'policy-not-in-use' );
				}
			}

		}
	},

	showGridBorders: function() {
		var top_border = $( this.el ).find( '.grid-top-border' );
		var bottom_border = $( this.el ).find( '.grid-bottom-border' );

		top_border.css( 'display', 'block' );
		bottom_border.css( 'display', 'block' );
	},

	setGridColumnsWidth: function() {
		var col_model = this.grid.getGridParam( 'colModel' );
		var grid_data = this.grid.getGridParam( 'data' );
		this.grid_total_width = 0;

		//Possible exception
		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20141102&user_id=53130 line 4288
		if ( !col_model ) {
			return;
		}

		for ( var i = 0; i < col_model.length; i++ ) {
			var col = col_model[i];
			var field = col.name;
			var longest_words = '';

			for ( var j = 0; j < grid_data.length; j++ ) {
				var row_data = grid_data[j];
				if ( !row_data.hasOwnProperty( field ) ) {
					break;
				}

				var current_words = row_data[field];

				if ( !current_words ) {
					current_words = '';
				}

				if ( !longest_words ) {
					longest_words = current_words.toString();
				} else {
					if ( current_words && current_words.toString().length > longest_words.length ) {
						longest_words = current_words.toString();
					}
				}

			}

			if ( longest_words ) {
				var width_test = $( '<span id="width_test" />' );
//				width_test.css( 'font-family', "'Lucida Grande', 'Lucida Sans', Arial, sans-serif" );
				width_test.css( 'font-size', '11' );
				width_test.css( 'font-weight', 'normal' );
				$( 'body' ).append( width_test );
				width_test.text( longest_words );

				var width = width_test.width();
				width_test.text( col.label );
				var header_width = width_test.width();

				if ( header_width > width ) {
					width = header_width + 20;
				}

				this.grid_total_width += width + 5;

				this.grid.setColProp( field, {widthOrg: width + 5} );
				width_test.remove();

			}
		}

		var gw = this.grid.getGridParam( 'width' );

//		if ( total_width > gw ) {
//			gw = total_width;
//		}

		this.grid.setGridWidth( gw );

	},

	setGridSize: function() {

		if ( (!this.grid || !this.grid.is( ':visible' )) ) {

			return;
		}

		if ( !this.sub_view_mode ) {
			if ( Global.bodyWidth() > Global.app_min_width ) {
				this.grid.setGridWidth( Global.bodyWidth() - 14 );
			} else {
				this.grid.setGridWidth( Global.app_min_width - 14 );
			}
		} else {

			this.grid.setGridWidth( $( this.el ).parent().width() - 2 );
		}

		if ( !this.sub_view_mode ) {
			this.grid.setGridHeight( ($( this.el ).height() - this.search_panel.height() - 90) );
		} else {
			this.grid.setGridHeight( $( this.el ).parent().parent().parent().height() - 80 );
		}

	},

	setEditViewTabSize: function() {

		var $this = this;
		var tab_bar_label = this.edit_view_tab.find( '.edit-view-tab-bar-label' );
		var tab_width = this.edit_view_tab.width();
		var nav_width = this.edit_view_tab.find( '.navigation-div' ).width();
		var wrap_div = this.edit_view.find( '.tab-label-wrap' );

		var total_tab_width = 0;
		tab_bar_label.children().each( function() {
			total_tab_width += $( this ).width();
		} );

		if ( total_tab_width > (tab_width - nav_width - 25) ) {

			tab_bar_label.width( total_tab_width + 10 );

			if ( wrap_div.length === 0 ) {
				var right_arrow = $( '<img class="tab-arrow tab-right-arrow" style="display: none" src="theme/default/images/right_big_arrow.png" >' );
				var left_arrow = $( '<img class="tab-arrow tab-left-arrow" style="display: none" src="theme/default/images/left_big_arrow.png" >' );
				wrap_div = $( '<div class="tab-label-wrap"><div class="label-wrap"></div><div class="btn-wrap"></div></div>' );
				wrap_div.insertBefore( tab_bar_label );
				wrap_div.width( tab_width - nav_width - 25 );
				wrap_div.children().eq( 0 ).width( tab_width - nav_width - 100 );
				wrap_div.children().eq( 0 ).append( tab_bar_label );
				wrap_div.children().eq( 1 ).append( left_arrow );
				wrap_div.children().eq( 1 ).append( right_arrow );

				right_arrow.bind( 'click', function() {
					wrap_div.children().eq( 0 ).scrollLeft( wrap_div.children().eq( 0 ).scrollLeft() + 500 );
					setArrowStatus();
				} );
				left_arrow.bind( 'click', function() {
					wrap_div.children().eq( 0 ).scrollLeft( wrap_div.children().eq( 0 ).scrollLeft() - 500 );
					setArrowStatus();
				} );
			} else {
				wrap_div.width( tab_width - nav_width - 25 );
				wrap_div.children().eq( 0 ).width( tab_width - nav_width - 100 );
			}

			if ( tab_bar_label.children().eq( 0 ).is( ':visible' ) ) {
				this.edit_view_tab.find( '.tab-arrow' ).show()
			} else {
				this.edit_view_tab.find( '.tab-arrow' ).hide()
			}

			setArrowStatus();

		} else {
			tab_bar_label.width( 'auto' );
			if ( wrap_div.length > 0 ) {
				tab_bar_label.insertBefore( wrap_div );
				wrap_div.remove();

			}
		}

		function setArrowStatus() {
			var left_arrow = $this.edit_view_tab.find( '.tab-left-arrow' );
			var right_arrow = $this.edit_view_tab.find( '.tab-right-arrow' );
			var label_wrap = wrap_div.children().eq( 0 );

			left_arrow.removeClass( 'disable-image' );
			right_arrow.removeClass( 'disable-image' );

			if ( label_wrap.scrollLeft() === 0 ) {
				left_arrow.addClass( 'disable-image' );
			}

			if ( label_wrap.scrollLeft() === label_wrap[0].scrollWidth - label_wrap.width() ) {
				right_arrow.addClass( 'disable-image' );
			}

		}

	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
		//Fixed possible exception -- Error: Unable to get property 'length' of undefined or null reference in https://villa.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 5031
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	getAllLayouts: function( callBack ) {

		var $this = this;

		var current_select_layout_name;

		if ( this.need_select_layout_name ) {
			current_select_layout_name = this.need_select_layout_name;
			this.need_select_layout_name = '';
		} else {
			current_select_layout_name = BaseViewController.default_layout_name;
		}

		if ( this.sub_view_mode || !this.show_search_tab ) {

			$this.select_layout = null;
			if ( callBack ) {
				callBack();
			}

			return;
		}

		this.user_generic_data_api.getUserGenericData( {filter_data: {script: this.script_name, deleted: false}}, {
			onResult: function( results ) {
				var result_data = results.getResult();

				$this.select_layout = null; //Reset select layout;

				if ( result_data && result_data.length > 0 ) {

					result_data.sort( function( a, b ) {
							if ( a.name > b.name ) {
								return true;
							} else {
								return false;
							}
						}
					);

					var len = result_data.length;

					for ( var i = 0; i < len; i++ ) {
						var layout = result_data[i];
						if ( layout.name === current_select_layout_name ) {
							$this.select_layout = layout;
							break;
						}
					}

					if ( !$this.select_layout ) {
						$this.select_layout = result_data[0];
					}

					$this.search_panel.setLayoutsArray( result_data );

				} else {

					$this.select_layout = null;
					$this.search_panel.setLayoutsArray( null );
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	getAllColumns: function( callBack ) {

		var $this = this;
		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.all_columns = Global.buildColumnArray( columns_result_data );
				if ( !$this.sub_view_mode ) {
					$this.column_selector.setUnselectedGridData( $this.all_columns );
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	getDefaultDisplayColumns: function( callBack ) {

		var $this = this;
		this.api.getOptions( 'default_display_columns', {
			onResult: function( columns_result ) {

				var columns_result_data = columns_result.getResult();

				$this.default_display_columns = columns_result_data;

				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	buildSortBySelectColumns: function() {
		var sort_by_array = this.select_layout.data.filter_sort;
		var sort_by_select_columns = [];
		var sort_by_unselect_columns = this.sort_by_selector.getSourceData();

		if ( sort_by_array ) {
			$.each( sort_by_array, function( index, content ) {

				for ( var key in content ) {

					$.each( sort_by_unselect_columns, function( index1, content1 ) {
						if ( content1.value === key ) {
							content1.sort = content[key];
							sort_by_select_columns.push( content1 );
							return false;
						}
					} );
				}

			} );
		}

		return sort_by_select_columns;

	},

	buildSortSelectorUnSelectColumns: function( display_columns ) {
		var fina_array = [];
		var i = 100;
		$.each( display_columns, function( index, content ) {
			var new_content = $.extend( {}, content );
			new_content.id = i; //Need
			new_content.sort = 'asc';
			fina_array.push( new_content );
			i = i + 1;
		} );

		return fina_array;
	},

	buildDisplayColumns: function( apiDisplayColumnsArray ) {
		var len = this.all_columns.length;
		var len1 = apiDisplayColumnsArray.length;
		var display_columns = [];

		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.all_columns[i].value ) {
					display_columns.push( this.all_columns[i] );
				}
			}
		}
		return display_columns;

	},

	buildDisplayColumnsByColumnModel: function( colModel ) {

		if ( !colModel ) {
			return;
		}

		var len = colModel.length;
		var display_columns = [];
		var id = 2000; // Makse sure the id not duplicate with all_columns, this wiil be used in acombox, set possible columns in navigation mode
		for ( var i = 0; i < len; i++ ) {
			var column = colModel[i];
			if ( column.name === 'cb' ) {
				continue;
			}
			display_columns.push( {label: column.label, value: column.name, id: id} );
			id = id + 1;
		}

		return display_columns;
	},

	removeContentMenuByName: function( name ) {

		var primary_view_id = LocalCacheData.current_open_primary_controller.viewId;
		var select_menu_id = TopMenuManager.menus_quick_map[primary_view_id];
		if ( TopMenuManager.ribbon_view_controller && TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) !== -1 ) {
			TopMenuManager.ribbon_view_controller.setSelectMenu( select_menu_id );
		}

		if ( !Global.isSet( name ) ) {
			name = this.context_menu_name;
		}

		var tab = $( '#ribbon ul a' ).filter( function() {
			return $( this ).attr( 'ref' ) === name;
		} ).parent();

		var index = $( 'li', $( '#ribbon' ) ).index( tab );
		if ( index >= 0 ) {
			$( '#ribbon_view_container' ).tabs( 'remove', index );
		}

	},

	movePermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.addPermissionValidate( p_id ) && this.deletePermissionValidate( p_id ) ) {
			return true;
		}

		return false;

	},

	subAuditValidate: function() {

		if ( this.editPermissionValidate() ) {
			return true;
		}

		return false;
	},

	subDocumentValidate: function() {

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) && PermissionManager.checkTopLevelPermission( 'Document' ) ) {
			return true;
		}

		return false;
	},

	addPermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'add' ) ) {
			return true;
		}

		return false;

	},

	getRecordFromGridById: function( id ) {

		var data = this.grid.getGridParam( 'data' );
		var result = null;
		/* jshint ignore:start */
		//id could be string or number.
		$.each( data, function( index, value ) {

			if ( value.id == id ) {
				result = Global.clone( value );
				return false;
			}

		} );
		/* jshint ignore:end */
		return result;

	},

	getSelectedItem: function() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
			}

		}

		if ( selected_item ) {
			return Global.clone( selected_item );
		} else {
			return null;
		}

	},

	deleteOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'delete' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'delete_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'delete_child' )) ) {

			return true;

		}

		return false;

	},

	viewOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if (
			PermissionManager.validate( p_id, 'view' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'view_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'view_child' )) ) {

			return true;

		}

		return false;

	},

	editOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'edit' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'edit_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'edit_child' )) ) {

			return true;

		}

		return false;

	},

	ownerOrChildPermissionValidate: function( p_id, permission_name, selected_item ) {

		var field;
		if ( permission_name.indexOf( 'child' ) > -1 ) {
			field = 'is_child';
		} else {
			field = 'is_owner';
		}
//
//		if ( PermissionManager.validate( p_id, permission_name ) &&
//			(!selected_item ||
//				( selected_item && (selected_item[field] || (!selected_item.id && !selected_item.hasOwnProperty( field )) ) ) ) ) {
//			return true;
//		}

		if ( PermissionManager.validate( p_id, permission_name ) ) {
			return true;
		}

		return false;
	},

	editChildPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !PermissionManager.validate( p_id, 'enabled' ) ) {
			return false;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) ||
			this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) ) {

			return true;
		}

		return false;

	},

	editPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) || this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'edit_own', selected_item ) ) {

			return true;
		}

		return false;

	},

	copyPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.viewPermissionValidate( p_id, selected_item ) && this.addPermissionValidate( p_id, selected_item ) ) {
			return true;
		}

		return false;

	},

	copyAsNewPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( this.viewPermissionValidate( p_id, selected_item ) && this.addPermissionValidate( p_id, selected_item ) ) {
			return true;
		}

		return false;
	},

	viewPermissionValidate: function( p_id, selected_item ) {

		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'view' ) || this.ownerOrChildPermissionValidate( p_id, 'view_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'view_own', selected_item ) ) {
			return true;
		}

		return false;

	},

	deletePermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectedItem();
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'delete' ) || this.ownerOrChildPermissionValidate( p_id, 'delete_child', selected_item ) || this.ownerOrChildPermissionValidate( p_id, 'delete_own', selected_item ) ) {
			return true;
		}

		return false;

	},

	saveValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}
	},

	saveAndCopyValidate: function( context_btn, p_id ) {

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) || !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	saveAndContinueValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) || !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (( !this.current_edit_record || !this.current_edit_record.id ) && this.is_mass_editing) || this.current_edit_record.id ) {

			if ( !this.editPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	saveAndNewValidate: function( context_btn, p_id ) {
		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
			if ( !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		} else if ( (!Global.isSet( this.current_edit_record.id ) && this.is_mass_editing) || Global.isSet( this.current_edit_record.id ) ) {

			if ( !this.editPermissionValidate( p_id ) || !this.addPermissionValidate( p_id ) ) {
				context_btn.addClass( 'invisible-image' );
			}
		}

		if ( this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	initSubLogView: function( tab_id ) {

		var $this = this;
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/core/log/LogViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Log' + 'View', LocalCacheData.current_doing_context_action );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_key = 'object_id';
			$this.sub_log_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();

		}
	},

	showNoResultCover: function( show_new_btn ) {
		show_new_btn = this.ifContextButtonExist( ContextMenuIconName.add );

		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: show_new_btn} );
		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );

		var grid_div = $( this.el ).find( '.grid-div' );

		grid_div.append( this.no_result_box );

		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
	},

	removeNoResultCover: function() {

		if ( this.no_result_box && this.no_result_box.length > 0 ) {
			this.no_result_box.remove();
		}
		this.no_result_box = null;

	},

	cleanWhenUnloadView: function( callBack ) {

		this.removeContentMenuByName();
		if ( Global.isSet( callBack ) ) {
			callBack();
		}
	},

	gridScrollTop: function() {

		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.parent().parent().scrollTop( 0 );
	},

	gridScrollDown: function() {

		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.parent().parent().scrollTop( 10000 );
	},

	selectAll: function() {
		if ( this.viewId === 'TimeSheet' || this.viewId === 'Schedule' ) {
			return;
		}

		if ( !this.grid ) {
			return;
		}

		this.grid.resetSelection();
		var source_data = this.grid.getGridParam( 'data' );
		var len = source_data.length;
		for ( var i = 0; i < len; i++ ) {
			var item = source_data[i];
			if ( Global.isSet( item.id ) ) {
				this.grid.jqGrid( 'setSelection', item.id, false );
			} else {
				this.grid.jqGrid( 'setSelection', i + 1, false );
			}

		}

		this.grid.parent().parent().parent().find( '.cbox-header' ).attr( 'checked', true );
		this.setDefaultMenu();
	}

} );
//Don't check the file for now. Too many issues
/* jshint ignore:end */

BaseViewController.loadView = function( view_id ) {

	Global.loadViewSource( view_id, view_id + 'View.html', function( result ) {
		var args = {};

		switch ( view_id ) {
			case 'TimeSheet':
				Global.loadViewSource( view_id, view_id + 'View.css' );
				args = {
					accumulated_time: $.i18n._( 'Accumulated Time' ),
					verify: $.i18n._( 'Verify' ),
					timesheet_verification: $.i18n._( 'TimeSheet Verification' )
				};
				break;
			case 'Login':
			case 'PortalLogin':
				$( 'body' ).addClass( 'login-bg' );
				$( 'body' ).removeClass( 'application-bg' );
				Global.loadViewSource( view_id, view_id + 'View.css' );
				args = {
					secure_login: $.i18n._( 'Secure Login' ),
					user_name: $.i18n._( 'User Name' ),
					password: $.i18n._( 'Password' ),
					forgot_your_password: $.i18n._( 'Forgot Your Password' ),
					quick_punch: $.i18n._( 'Quick Punch' ),
					login: $.i18n._( 'Login' ),
					language: $.i18n._( 'Language' )
				};
				break;
			case 'Schedule':
				Global.loadViewSource( view_id, view_id + 'View.css' );
				break;
		}

		var template = _.template( result, args );
		Global.contentContainer().html( template );

		LocalCacheData.current_open_view_id = view_id;

		Global.trackView( view_id, LocalCacheData.current_doing_context_action );
	} );

};

BaseViewController.default_layout_name = '-Default-';
