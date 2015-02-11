MessageControlViewController = BaseViewController.extend( {
	el: '#message_control_view_container',

	object_type_array: null,

	is_request: false,
	is_message: false,

	messages: null,
	request_api: null,

	folder_id: null,

	navigation_source_data: null,

	isReloadViewUI: false,

	current_select_message_control_data: null, //current select message control data, set in onViewClick

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'MessageControlEditView.html';
		this.permission_id = 'message';
		this.viewId = 'MessageControl';
		this.script_name = 'MessageControlView';
		this.table_name_key = 'message_control';
		this.context_menu_name = $.i18n._( 'Message' );
		this.api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();
		this.request_api = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.folder_id = 10;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'MessageControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'object_type' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'message_control';
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				multiple: true,
				field: 'object_type_id',
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Subject' ),
				in_column: 1,
				field: 'subject',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	getSubViewFilter: function( filter ) {

		if ( filter.length === 0 ) {
			filter = {};
		}

		filter['folder_id'] = this.folder_id;

		return filter;
	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var view_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'View' ),
			id: this.script_name + 'View',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var folder_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Folder' ),
			id: this.script_name + 'Folder',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New' ),
			id: ContextMenuIconName.add,
			group: view_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: view_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var reply = new RibbonSubMenu( {
			label: $.i18n._( 'Reply' ),
			id: ContextMenuIconName.edit,
			group: view_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		var del = new RibbonSubMenu( {
			label: $.i18n._( 'Delete' ),
			id: ContextMenuIconName.delete_icon,
			group: view_group,
			icon: Icons.delete_icon,
			permission_result: true,
			permission: null
		} );

		var delAndNext = new RibbonSubMenu( {
			label: $.i18n._( 'Delete<br>& Next' ),
			id: ContextMenuIconName.delete_and_next,
			group: view_group,
			icon: Icons.delete_and_next,
			permission_result: true,
			permission: null
		} );

		var close = new RibbonSubMenu( {
			label: $.i18n._( 'Close' ),
			id: ContextMenuIconName.close_misc,
			group: view_group,
			icon: Icons.close_misc,
			permission_result: true,
			permission: null
		} );

		var send = new RibbonSubMenu( {
			label: $.i18n._( 'Send' ),
			id: ContextMenuIconName.send,
			group: editor_group,
			icon: Icons.send,
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

		var inbox = new RibbonSubMenu( {
			label: $.i18n._( 'Inbox' ),
			id: ContextMenuIconName.inbox,
			group: folder_group,
			icon: Icons.inbox,
			selected: true,
			permission_result: true,
			permission: null
		} );

		var sent = new RibbonSubMenu( {
			label: $.i18n._( 'Sent' ),
			id: ContextMenuIconName.sent,
			group: folder_group,
			icon: Icons.sent,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
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
			/* jshint ignore:start */

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.close_misc:
					this.setDefaultMenuCloseMiscIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.send:
					this.setDefaultMenuSendIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.inbox:
					this.setDefaultMenuInboxIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.sent:
					this.setDefaultMenuSentIcon( context_btn, grid_selected_length );
					break;

			}
			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	onGridDblClickRow: function() {

		var len = this.context_menu_array.length;

		var need_break = false;

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
					}
					break;
			}
		}

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
		/* jshint ignore:start */

		switch ( id ) {
			case ContextMenuIconName.add:
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.send:
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.close_misc:
			case ContextMenuIconName.cancel:
				this.onCancelClick( id );
				break;
			case ContextMenuIconName.inbox:
				this.setCurrentSelectedIcon( context_btn );
				this.onInboxClick();
				break;
			case ContextMenuIconName.sent:
				this.setCurrentSelectedIcon( context_btn );
				this.onSentClick();
				break;

		}
		/* jshint ignore:end */

	},

	onCancelClick: function( iconName ) {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'cancel';

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {

				if ( flag === true ) {
					doNext();
				}

			} );
		} else {
			doNext();
		}

		function doNext() {

			if ( iconName === ContextMenuIconName.cancel && $this.isReloadViewUI ) {
				$this.isReloadViewUI = false;
//				$this.current_edit_record = null; //set to fix that IndexViewConroler force ui back to view when open view again
				$this.onViewClick( $this.current_select_message_control_data );
			} else {
				$this.removeEditView();
				$this.isReloadViewUI = false;
			}

		}

	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
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

			if ( $this.isReloadViewUI ) {
				$this.isReloadViewUI = false;
				$this.onViewClick( $this.current_select_message_control_data );

			} else {
				$this.removeEditView();
			}

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onInboxClick: function() {
		this.folder_id = 10;
		this.search();
	},

	onSentClick: function() {
		this.folder_id = 20;
		this.search();
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
		var pId = null;
		if ( this.is_message ) {
			pId = 'message';
		} else if ( this.is_request ) {
			pId = 'request';
		}
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];

			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );
			/* jshint ignore:start */

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn, pId );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn, pId );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn, pId );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn, pId );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn, pId );
					break;
				case ContextMenuIconName.close_misc:
					this.setEditMenuCloseMiscIcon( context_btn, pId );
					break;
				case ContextMenuIconName.send:
					this.setEditMenuSendIcon( context_btn, pId );
					break;
				case ContextMenuIconName.cancel:
					this.setEditMenuCancelIcon( context_btn, pId );
					break;
				case ContextMenuIconName.inbox:
//					this.setCurrentSelectedIcon( context_btn, pId );
					this.setEditMenuInboxIcon( context_btn, pId );
					break;
				case ContextMenuIconName.sent:
//					this.setCurrentSelectedIcon( context_btn, pId );
					this.setEditMenuSentIcon( context_btn, pId );
					break;
			}
			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setCurrentSelectedIcon: function( icon ) {

		//Error: Uncaught TypeError: Cannot read property 'find' of null in https://ondemand1.timetrex.com/interface/html5/#!m=MessageControl line 543
		if ( !icon ) {
			return;
		}

		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			$( context_btn.find( '.ribbon-sub-menu-icon' ) ).removeClass( 'selected-menu' );
		}
		$( icon.find( '.ribbon-sub-menu-icon' ) ).addClass( 'selected-menu' );
	},

	setDefaultMenuDeleteAndNextIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {

	},

	setEditMenuCloseMiscIcon: function( context_btn, pId ) {

	},

	setEditMenuSendIcon: function( context_btn, pId ) {

		if ( this.is_edit || this.is_add ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuInboxIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuSentIcon: function( context_btn, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuCancelIcon: function( context_btn, pId ) {

		if ( this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCloseMiscIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSendIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuInboxIcon: function( context_btn, grid_selected_length, pId ) {

	},

	setDefaultMenuSentIcon: function( context_btn, grid_selected_length, pId ) {

	},

	openEditView: function() {
		this.initEditViewUI( this.viewId, this.edit_view_tpl );
		this.setEditViewWidgetsMode();
	},

	setGridCellBackGround: function() {
		var data = this.grid.getGridParam( 'data' );

		//Error: TypeError: data is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.status_id === 10 ) {
				$( "tr[id='" + item.id + "'] td" ).css( 'font-weight', 'bold' );
			}
		}
	},

	getFilterColumnsFromDisplayColumns: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		var column_filter = {};

		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.is_owner = true;
		column_filter.object_type_id = true;
		column_filter.object_id = true;
		column_filter.status_id = true;
		column_filter.from_user_id = true;
		column_filter.to_user_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	initEditViewUI: function( view_id, edit_view_file_name ) {
//		if ( this.sub_view_mode ) {
//			Global.trackView( 'Sub' + this.viewId + '_view' + '_edit_view' );
//		} else {
//			Global.trackView( this.viewId + '_view' + '_edit_view' );
//		}

		var $this = this;

		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );

		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		this.setTabOVisibility( false );

		this.edit_view_tab = this.edit_view_tab.tabs( {show: function( e, ui ) {
			$this.onTabShow( e, ui );
		}} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui );
		} );

		if ( this.folder_id === 10 ) {
			this.navigation_label = $.i18n._( 'From' ) + ':';
		} else if ( this.folder_id === 20 ) {
			this.navigation_label = $.i18n._( 'To' ) + ':';
		}

		Global.contentContainer().append( this.edit_view );

		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_add ) {

			this.buildAddViewUI();
		} else if ( this.is_viewing ) {

			if ( this.is_request ) {
				this.buildRequestViewUI();
			} else if ( this.is_message ) {

				this.buildMessageViewUI();
			}

		} else if ( this.is_edit ) {

			this.buildEditViewUI();
		}

		//Calculated tab's height
		this.edit_view_tab.resize( function() {

			$this.setEditViewTabHeight();

		} );

		$this.setEditViewTabHeight();
	},

	onViewClick: function( next_selected_item, noRefreshUI ) {

		var $this = this;
		$this.is_viewing = true;
		$this.is_edit = false;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.isReloadViewUI = true;
		var filter = {};
		var selectedId;
		var selected_item;

		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( next_selected_item ) ) {
			selected_item = next_selected_item; // If the next_selected_item is defined, first to use this variable.

		} else if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( parseInt( grid_selected_id_array[0] ) );
		} else {
			return;
		}

		if ( selected_item.object_type_id === 50 ) {
			selectedId = selected_item.object_id;
			$this.is_request = true;
			$this.is_message = false;
		} else {
			selectedId = selected_item.id;
			$this.is_request = false;
			$this.is_message = true;
		}

		filter.filter_data = {};
		filter.filter_data.id = selectedId;

		$this.openEditView();

		if ( $this.is_request ) {

			this.request_api['getRequest']( filter, {onResult: function( result ) {

				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					if ( !$this.edit_view ) {
						$this.onCancelClick();
					}
					return;
				}

				// save current select grid data. Not this not work when access from url action. See autoOpenEditView function for why
				$this.current_select_message_control_data = selected_item;

				$this.current_edit_record = result_data;

				//if access from url, current_select_message_control_data need be get again
				if ( !$this.current_select_message_control_data.hasOwnProperty( 'to_user_id' ) ) {
					var filter = {filter_data: {id: $this.current_select_message_control_data.id}};
					var message_control_data = $this.api.getMessageControl( filter, {async: false} ).getResult()[0];

					if ( message_control_data ) {
						$this.current_select_message_control_data = message_control_data;
					}

				}

				$this.initEditView();

			}} );

		} else {

			this.api['getMessage']( filter, {onResult: function( result ) {

				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data.length > 1 ? result_data.reverse() : result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					if ( !$this.edit_view ) {
						$this.onCancelClick();
					}
					return;
				}

				// save current select grid data. Not this not work when access from url action. See autoOpenEditView function for why
				$this.current_select_message_control_data = selected_item;

				$this.current_edit_record = result_data;

				//if access from url, current_select_message_control_data need be get again
				if ( !$this.current_select_message_control_data.hasOwnProperty( 'to_user_id' ) ) {
					var filter = {filter_data: {id: $this.current_select_message_control_data.id}};
					var message_control_data = $this.api.getMessageControl( filter, {async: false} ).getResult()[0];

					if ( message_control_data ) {
						$this.current_select_message_control_data = message_control_data;
					}

				}

				$this.initEditView();

			}} );

		}

	},
	/* jshint ignore:start */
	setURL: function() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

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
			if ( this.current_select_message_control_data && this.current_select_message_control_data.id ) {
				if ( a ) {

					if ( this.is_request ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId +
							'&a=' + a + '&id=' + this.current_select_message_control_data.id +
							'&t=request&object_id=' + this.current_select_message_control_data.object_id +
							'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() +
							'#!m=' + this.viewId + '&a=' +
							a + '&id=' + this.current_select_message_control_data.id + '&t=message' +
							'&tab=' + tab_name );
					}

				}

				Global.trackView();

			} else {
				if ( a ) {
					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=new&t=' + (this.is_request ? 'request' : 'message') +
							'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a + '&t=' + (this.is_request ? 'request' : 'message') +
							'&tab=' + tab_name );
					}

				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId );
				}
			}

		}
	},
	/* jshint ignore:end */
	initEditViewData: function() {
		var $this = this;
		if ( !this.edit_only_mode && this.navigation ) {

			var grid_current_page_items = this.grid.getGridParam( 'data' );

			var navigation_div = this.edit_view.find( '.navigation-div' );
			var navigation_source_data;

			//because I will always get this in onViewClick, so else branch should never be in
			if ( this.current_select_message_control_data && this.current_select_message_control_data.hasOwnProperty( 'id' ) &&
				this.current_select_message_control_data.hasOwnProperty( 'subject' ) ) {
				navigation_source_data = this.current_select_message_control_data;
			} else {
				navigation_source_data = Global.isArray( this.current_edit_record ) ? this.current_edit_record[0] : this.current_edit_record;
			}

			this.navigation_source_data = navigation_source_data;

			if ( Global.isSet( navigation_source_data.id ) && navigation_source_data.id ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					this.navigation.setSourceData( grid_current_page_items );
					this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
					this.navigation.setPagerData( this.pager_data );

//					this.navigation.setDisPlayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ) );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( navigation_source_data );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		this.setUIWidgetFieldsToCurrentEditRecord();

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

//	collectUIDataToCurrentEditRecord: function() {
//
//		if ( this.is_mass_editing || this.is_viewing ) {
//			return;
//		}
//
//		var $this = this;
//		for ( var key in this.edit_view_ui_dic ) {
//
//			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
//				continue;
//			}
//
//			var widget = this.edit_view_ui_dic[key];
//			var value;
//			//Set all UI field to current edit record, we need validate all UI field when save and validate
//			if ( Global.isArray( $this.current_edit_record ) ) {
//
//				for ( var i = 0; i < $this.current_edit_record.length; i++ ) {
//					if ( !Global.isSet( $this.current_edit_record[i][key] ) || $this.current_edit_record[key] != value ) {
//
//						value = widget.getValue();
//						if ( !value || value === '0' || (Global.isArray( value ) && value.length === 0) ) {
//							$this.current_edit_record[i][key] = false;
//						} else {
//							$this.current_edit_record[i][key] = value;
//						}
//
//					}
//				}
//			} else {
//				if ( !Global.isSet( $this.current_edit_record[key] ) || $this.current_edit_record[key] != value ) {
//
//					value = widget.getValue();
//					if ( !value || value === '0' || (Global.isArray( value ) && value.length === 0) ) {
//						$this.current_edit_record[key] = false;
//					} else {
//						$this.current_edit_record[key] = value;
//					}
//
//				}
//			}
//
//		}
//	},

	setNavigation: function() {

		var $this = this;

		this.navigation.setPossibleDisplayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ),
			this.buildDisplayColumns( this.default_display_columns ) );

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {

			var key = target.getField();
			var next_select_item = target.getValue( true );

			if ( !next_select_item ) {
				return;
			}

			if ( next_select_item.id !== $this.navigation_source_data.id ) {
				ProgressBar.showOverlay();

				if ( $this.is_viewing ) {
					$this.onViewClick( next_select_item ); //Dont refresh UI
				} else {
					$this.onEditClick( next_select_item ); //Dont refresh UI
				}

			}

			$this.setNavigationArrowsEnabled();

		} );

	},

	onEditClick: function( editId, noRefreshUI ) {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'edit';

		var grid_selected_id_array = this.getGridSelectIdArray();
		var selected_item = {};

		if ( this.edit_view ) {
			selected_item = this.current_select_message_control_data;
		} else { // click Reply on list view.
			selected_item = this.getRecordFromGridById( parseInt( grid_selected_id_array[0] ) );
		}

		$this.openEditView();
		$this.current_edit_record = selected_item;

		$this.initEditView();

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_message': $.i18n._( 'Message' )
		} );

		this.navigation = null;

		//Tab 0 start

		var tab_message = this.edit_view_tab.find( '#tab_message' );

		var tab_message_column1 = tab_message.find( '.first-column' );

		var tab_message_column2 = tab_message.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_message_column1 );

		var form_item_input;

		// Employee(s)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'from_full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_message_column1, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'subject', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_message_column1 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'body', width: 600, height: 400} );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_message_column1, '', null, null, true );

		tab_message_column2.css( 'display', 'none' );

	},

	buildRequestViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_message': $.i18n._( 'Messages' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMessageControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MESSAGE_USER,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 first column start

		var tab_message = this.edit_view_tab.find( '#tab_message' );

		var tab_message_column1 = tab_message.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_message_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_message_column1, '' );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'date_stamp'} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_message_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'type'} );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_message_column1, '' );

		// tab_message first column end

		var separate_box = tab_message.find( '.separate' );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Messages' )} );
		this.addEditFieldToColumn( null, form_item_input, separate_box );

		// Tab 0 second column start

		var tab_message_column2 = tab_message.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_message_column2 );

		// From
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'from'} );
		this.addEditFieldToColumn( $.i18n._( 'From' ), form_item_input, tab_message_column2, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'subject'} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_message_column2 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'body'} );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_message_column2, '', null, null, true );

		// Tab 0 second column end

		tab_message_column2.css( 'display', 'none' );

	},

	buildMessageViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_message': $.i18n._( 'Messages' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMessageControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MESSAGE_USER,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_message = this.edit_view_tab.find( '#tab_message' );

		var tab_message_column1 = tab_message.find( '.first-column' );

		var tab_message_column2 = tab_message.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_message_column1 );

		// From
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'from_full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'From' ), form_item_input, tab_message_column1, '' );

		// To
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'to_full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'To' ), form_item_input, tab_message_column1 );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'updated_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_message_column1 );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'subject'} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_message_column1 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'body'} );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_message_column1, '', null, null, true );

		tab_message_column2.css( 'display', 'none' );

	},

	onLeftArrowClick: function() {

		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();
		var next_select_item;
		if ( selected_index > 0 ) {
			next_select_item = this.navigation.getItemByIndex( selected_index - 1 );

		} else {
			this.onCancelClick();
			return;

		}

		ProgressBar.showOverlay();

		this.onViewClick( next_select_item ); //Dont refresh UI

		this.setNavigationArrowsEnabled();
	},

	refreshCurrentRecord: function() {
		var next_select_item = this.navigation.getItemByIndex( this.navigation.getSelectIndex() );
		ProgressBar.showOverlay();
		this.onViewClick( next_select_item ); //Dont refresh UI
		this.setNavigationArrowsEnabled();
	},

	onRightArrowClick: function() {

		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();
		var next_select_item;

		if ( selected_index < (source_data.length - 1) ) {
			next_select_item = this.navigation.getItemByIndex( (selected_index + 1) );

		} else {
			this.onCancelClick();
			return;
		}

		ProgressBar.showOverlay();

		this.onViewClick( next_select_item ); //Dont refresh UI

		this.setNavigationArrowsEnabled();
	},

	buildAddViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_message': $.i18n._( 'Message' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMessageControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MESSAGE_USER,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_message = this.edit_view_tab.find( '#tab_message' );

		var tab_message_column1 = tab_message.find( '.first-column' );

		var tab_message_column2 = tab_message.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_message_column1 );

		// Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMessageControl' )),
			column_option_key: 'user_columns',
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.MESSAGE_USER,
			show_search_inputs: true,
			set_empty: true,
			custom_key_name: 'User',
			field: 'to_user_id'
		} );
		var default_args = {};
		default_args.permission_section = 'message_control';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_message_column1, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'subject'} );

		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_message_column1 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'body', width: 600, height: 400} );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_message_column1, '', null, null, true );

		tab_message_column2.css( 'display', 'none' );

	},

	onAddClick: function() {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		this.isReloadViewUI = false;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		var result_data = {};

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	setEditMenuAddIcon: function( context_btn, pId ) {

		if ( this.is_add || this.is_changed ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuEditIcon: function( context_btn, pId ) {

		if ( !this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.current_select_message_control_data ||
			this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {

		if ( !this.current_select_message_control_data ||
			this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}
	},

	validate: function() {

		var $this = this;

		var record = this.current_edit_record;

		if ( Global.isSet( this.edit_view_ui_dic['subject'] ) ) {
			record.subject = this.edit_view_ui_dic['subject'].getValue();
		}

		record = this.uniformVariable( record );

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onSaveClick: function() {

		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';
		var $this = this;
		var record = this.current_edit_record;

		if ( Global.isSet( this.edit_view_ui_dic['subject'] ) ) {
			record.subject = this.edit_view_ui_dic['subject'].getValue();
		}

		record = this.uniformVariable( record );

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {

			$this.onSaveResult( result );

		}} );
	},

	uniformVariable: function( records ) {
		var reply_data = {};

		if ( this.is_edit ) {

			reply_data.subject = records.subject;
			reply_data.body = records.body;

			// message
			if ( records.object_type_id !== 50 ) {
				reply_data.to_user_id = records.from_user_id;
				reply_data.object_type_id = 5;
				reply_data.object_id = LocalCacheData.loginUser.id;
				reply_data.parent_id = records.id;

			} else {
				// request
//				if ( Global.isSet( this.messages ) ) {
//					reply_data.object_id = records.object_id;
//				} else {
//					reply_data.object_id = records.id;
//				}

				reply_data.object_id = records.object_id;

				reply_data.to_user_id = LocalCacheData.loginUser.id;
				reply_data.object_type_id = 50;

				reply_data.parent_id = 1;
			}

			return reply_data;

		}

		if ( this.is_add ) {
			records.object_type_id = 5;
			records.object_id = LocalCacheData.loginUser.id;
			records.parent_id = 0;
		}

		return records;
	},
	/* jshint ignore:start */
	setCurrentEditRecordData: function() {
		var $this = this;
		// If the current_edit_record is an array, then handle them in setEditViewDataDone function.

		if ( Global.isArray( this.current_edit_record ) ) {
			this.setMultipleMessages();

		} else {

			//Set current edit record data to all widgets

			for ( var key in this.current_edit_record ) {

				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget ) ) {
					switch ( key ) {
						case 'from_full_name':
							widget.setValue( this.current_edit_record['from_first_name'] + ' ' + this.current_edit_record['from_last_name'] );
							break;
						case 'to_full_name':
							widget.setValue( this.current_edit_record['to_first_name'] + ' ' + this.current_edit_record['to_last_name'] );
							break;
						case 'full_name':
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
							break;
						case 'subject':
							if ( this.is_edit ) {
								if ( Global.isArray( this.messages ) ) {
									widget.setValue( 'Re: ' + this.messages[0].subject );
								} else {
									widget.setValue( 'Re: ' + this.current_edit_record[key] );
								}

							} else if ( this.is_viewing ) {
								widget.setValue( this.current_edit_record[key] );
							}
							break;
						default:
							widget.setValue( this.current_edit_record[key] );
							break;
					}

				}
			}

			//request will do this when initEmbeddedMessage
			if ( this.is_message && this.current_edit_record.status_id == 10 ) {
				this.api['markRecipientMessageAsRead']( [this.current_edit_record.id], {onResult: function( res ) {
					$this.search( false );
				}} );
			}

		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},
	/* jshint ignore:end */
	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					var item = {};
					item.id = LocalCacheData.edit_id_for_next_open_view;
					if ( LocalCacheData.all_url_args.t === 'request' ) {
						item.object_id = LocalCacheData.all_url_args.object_id;
						item.object_type_id = 50;
					}
					this.onViewClick( item );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				this.onAddClick();
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	onDeleteClick: function() {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_select_message_control_data.id );
			} else {
				remove_ids = $this.getGridSelectIdArray().slice();
			}
			if ( result ) {
				//Add folder
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, $this.folder_id, {onResult: function( result ) {
					$this.isReloadViewUI = false;
					$this.onDeleteResult( result, remove_ids );
				}} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	onDeleteAndNextClick: function() {
		var $this = this;
		$this.is_add = false;

		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_select_message_control_data.id );
			}

			if ( result ) {

				//Add folder
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, $this.folder_id, {onResult: function( result ) {

					$this.isReloadViewUI = false;
					$this.onDeleteAndNextResult( result, remove_ids );

				}} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );

		if ( this.is_viewing ) {

			if ( this.is_request ) {
				this.initEmbeddedMessageData();
			}

		} else {

			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

	},

	setMultipleMessages: function() {
		var $this = this;
		var container = $( '<div></div>' );

		this.messages = $this.current_edit_record;

		var read_ids = [];

		for ( var key in $this.current_edit_record ) {

			var currentItem = $this.current_edit_record[key];
			if ( !currentItem.hasOwnProperty( 'id' ) ) {
				continue;
			}

			if ( currentItem.status_id === 10 ) {
				read_ids.push( currentItem.id );
			}

			$this.edit_view_ui_dic['from_full_name'].setValue( currentItem['from_first_name'] + ' ' + currentItem['from_last_name'] );
			$this.edit_view_ui_dic['to_full_name'].setValue( currentItem['to_first_name'] + ' ' + currentItem['to_last_name'] );
			$this.edit_view_ui_dic['updated_date'].setValue( currentItem['updated_date'] );
			$this.edit_view_ui_dic['subject'].setValue( currentItem['subject'] );
			$this.edit_view_ui_dic['body'].setValue( currentItem['body'] );

			var cloneMessageControl = $( $this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).find( '.first-column' ) ).clone();

			cloneMessageControl.css( 'display', 'block' ).appendTo( container );

		}

		if ( read_ids.length > 0 ) {
			$this.api['markRecipientMessageAsRead']( read_ids, {onResult: function( res ) {
				$this.search( false );
			}} );
		}

		$this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).find( '.first-column' ).remove();
		$this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).append( container.html() );
	},

	initEmbeddedMessageData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};
		args.filter_data.object_type_id = 50;
		args.filter_data.object_id = this.current_edit_record.id;

		$this.api['getEmbeddedMessage']( args, {onResult: function( res ) {

			if ( !$this.edit_view ) {
				return;
			}

			var data = res.getResult();

			if ( Global.isArray( data ) ) {
				$( $this.edit_view.find( '.separate' ) ).css( 'display', 'block' );

				$this.messages = data;
				var read_ids = [];

				var container = $( '<div></div>' );

				for ( var key in data ) {

					var currentItem = data[key];
					/* jshint ignore:start */

					if ( currentItem.status_id == 10 ) {

						read_ids.push( currentItem.id );

					}
					/* jshint ignore:end */

					var from = currentItem.from_first_name + ' ' + currentItem.from_last_name + '@' + currentItem.updated_date;
					$this.edit_view_ui_dic['from'].setValue( from );
					$this.edit_view_ui_dic['subject'].setValue( currentItem.subject );
					$this.edit_view_ui_dic['body'].setValue( currentItem.body );

					var cloneMessageControl = $( $this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).find( '.second-column' ) ).clone();

					cloneMessageControl.css( 'display', 'block' ).appendTo( container );
				}

				if ( read_ids.length > 0 ) {
					$this.api['markRecipientMessageAsRead']( read_ids, {onResult: function( res ) {
						$this.search( false );
					}} );
				}

				$this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).find( '.second-column' ).remove();
				$this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).append( container.html() );
			} else {

				$( $this.edit_view.find( '.separate' ) ).css( 'display', 'none' );
			}

		}} );
	},

	/* jshint ignore:start */
	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = 0;
		this._super( 'search', set_default_menu, page_action, page_number, callBack )
	}

	/* jshint ignore:end */


} );

MessageControlViewController.loadView = function() {

	Global.loadViewSource( 'MessageControl', 'MessageControlView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};