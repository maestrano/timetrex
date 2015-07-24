RequestViewController = BaseViewController.extend( {
	el: '#request_view_container',
	type_array: null,
	status_array: null,

	message_control_api: null,

	messages: null,

	authorization_api: null,

	authorization_history_columns: [],

	authorization_history_default_display_columns: [],

	authorization_history_grid: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'RequestEditView.html';
		this.permission_id = 'request';
		this.viewId = 'Request';
		this.script_name = 'RequestView';
		this.table_name_key = 'request';
		this.context_menu_name = $.i18n._( 'Requests' );
		this.navigation_label = $.i18n._( 'Request' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.authorization_api = new (APIFactory.getAPIClass( 'APIAuthorization' ))();
		this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initPermission: function() {

		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );

	},

	buildViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_request': $.i18n._( 'Request' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRequest' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REQUESRT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 first column start

		var tab_request = this.edit_view_tab.find( '#tab_request' );

		var tab_request_column1 = tab_request.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_request_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_request_column1, '' );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'date_stamp'} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_request_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'type'} );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_request_column1, '' );

		var separate_box = tab_request.find( '.grid-title' );

		// Authorization History

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Authorization History' )} );

		form_item_input.attr( 'id', 'authorization_history' );

		this.addEditFieldToColumn( null, form_item_input, separate_box );

		// tab_request first column end

		separate_box = tab_request.find( '.separate' ).css( 'display', 'none' );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Messages' )} );
		this.addEditFieldToColumn( null, form_item_input, separate_box );

		// Tab 0 second column start

		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_request_column2 );

		// From
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'from'} );
		this.addEditFieldToColumn( $.i18n._( 'From' ), form_item_input, tab_request_column2, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'subject'} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_request_column2 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'body'} );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_request_column2, '', null, null, true );

		// Tab 0 second column end

		tab_request_column2.css( 'display', 'none' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_request': $.i18n._( 'Message' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		var tab_audit_label = this.edit_view.find( 'a[ref=tab_audit]' );

		tab_audit_label.css( 'display', 'none' );

		this.navigation = null;

		//Tab 0 start

		var tab_request = this.edit_view_tab.find( '#tab_request' );

		var tab_request_column1 = tab_request.find( '.first-column' );

		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_request_column1 );

		// Subject
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'subject', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_request_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'body', width: 600, height: 400} );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_request_column1, '', null, null, true );

		tab_request_column2.css( 'display', 'none' );

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

			if ( item.status_id === 30 ) {
				$( "tr[id='" + item.id + "']" ).addClass( 'bolder-request' );
			}
		}
	},

	buildAddViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_request': $.i18n._( 'Request' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRequest' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REQUESRT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_request = this.edit_view_tab.find( '#tab_request' );

		var tab_request_column1 = tab_request.find( '.first-column' );

		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_request_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_request_column1, '' );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'date_stamp'} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_request_column1, '', widgetContainer );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_request_column1 );

		// Message
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'message', width: 600, height: 400} );

		this.addEditFieldToColumn( $.i18n._( 'Message' ), form_item_input, tab_request_column1, '', null, null, true );

		tab_request_column2.css( 'display', 'none' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

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

		//menu group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
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

		var reply = new RibbonSubMenu( {
			label: $.i18n._( 'Reply' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedule' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		return [menu];

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
					case 'full_name':
						if ( this.is_add ) {
							widget.setValue( LocalCacheData.loginUser.first_name + ' ' + LocalCacheData.loginUser.last_name );
						} else if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						}
						break;
					case 'subject':
						if ( this.is_edit ) {
							widget.setValue( 'Re: ' + this.messages[0].subject );
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

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );

		if ( this.is_viewing ) {

			this.getAuthorizationHistoryColumns( function() {
				$this.initAuthorizationHistoryLayout();
			} );

		} else {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

	},

	initAuthorizationHistoryLayout: function() {

		var $this = this;

		$this.getAuthorizationHistoryDefaultDisplayColumns( function() {

			if ( !$this.edit_view ) {
				return;
			}

			$this.setAuthorizationHistorySelectLayout();

			$this.initAuthorizationHistoryData();
		} );

	},

	initAuthorizationHistoryData: function() {

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns( true );

		filter.filter_data.object_id = [this.current_edit_record.id];
		filter.filter_data.object_type_id = this.current_edit_record.hierarchy_type_id;

		this.authorization_api['get' + this.authorization_api.key_name]( filter, {onResult: function( result ) {

			if ( !$this.edit_view ) {
				return;
			}

			var result_data = result.getResult();

			if ( Global.isArray( result_data ) ) {

				$( $this.edit_view.find( '.grid-div' ) ).css( 'display', 'block' );

				$this.showAuthorizationHistoryGridBorders();

				result_data = Global.formatGridData( result_data, $this.authorization_api.key_name );

				$this.authorization_history_grid.clearGridData();

				$this.authorization_history_grid.setGridParam( {data: result_data} );
				$this.authorization_history_grid.trigger( 'reloadGrid' );
			} else {
				$( $this.edit_view.find( '.grid-div' ) ).css( 'display', 'none' );
			}

			$this.initEmbeddedMessageData();

		}} );

	},

	setAuthorizationHistorySelectLayout: function( column_start_from ) {

		var $this = this;
		var grid = this.edit_view.find( '#grid' );

		grid.attr( 'id', 'authorization_history_grid' );  //Grid's id is ScriptName + _grid

		grid = this.edit_view.find( '#authorization_history_grid' );

		var column_info_array = [];

		var display_columns = this.buildAuthorizationDisplayColumns( this.authorization_history_default_display_columns );

		//Set Data Grid on List view
		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var view_column_data = display_columns[i];

			var column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 100, sortable: false, title: false};
			column_info_array.push( column_info );
		}

		if ( !this.authorization_history_grid ) {
			this.authorization_history_grid = grid;

			this.authorization_history_grid = this.authorization_history_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: ($( this.edit_view.find( '.grid-div' ) ).width() - 14),
				rowNum: 10000,
				colNames: [],
				colModel: column_info_array

			} );

		} else {

			this.authorization_history_grid.jqGrid( 'GridUnload' );
			this.authorization_history_grid = null;

			grid = this.edit_view.find( '#authorization_history_grid' );

			this.authorization_history_grid = $( grid );

			this.authorization_history_grid = this.authorization_history_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				datatype: 'local',
				width: ($( this.edit_view.find( '.grid-div' ) ).width() - 14),
				colNames: [],
				colModel: column_info_array

			} );

		}

	},

	setAuthorizationGridSize: function() {

		if ( (!this.authorization_history_grid || !this.authorization_history_grid.is( ':visible' )) ) {
			return;
		}

		this.authorization_history_grid.setGridWidth( $( this.edit_view.find( '#authorization_history' ) ).width() );

	},

	buildAuthorizationDisplayColumns: function( apiDisplayColumnsArray ) {
		var len = this.authorization_history_columns.length;
		var len1 = apiDisplayColumnsArray.length;
		var display_columns = [];

		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.authorization_history_columns[i].value ) {
					display_columns.push( this.authorization_history_columns[i] );
				}
			}
		}
		return display_columns;

	},

	showAuthorizationHistoryGridBorders: function() {
		var top_border = this.edit_view.find( '.grid-top-border' );
		var bottom_border = this.edit_view.find( '.grid-bottom-border' );

		top_border.css( 'display', 'block' );
		bottom_border.css( 'display', 'block' );
	},

	getAuthorizationHistoryDefaultDisplayColumns: function( callBack ) {

		var $this = this;
		this.authorization_api.getOptions( 'default_display_columns', {onResult: function( columns_result ) {
			var columns_result_data = columns_result.getResult();

			$this.authorization_history_default_display_columns = columns_result_data;

			if ( callBack ) {
				callBack();
			}

		}} );

	},

	getAuthorizationHistoryColumns: function( callBack ) {

		var $this = this;
		this.authorization_api.getOptions( 'columns', {onResult: function( columns_result ) {
			var columns_result_data = columns_result.getResult();
			$this.authorization_history_columns = Global.buildColumnArray( columns_result_data );

			if ( callBack ) {
				callBack();
			}

		}} );

	},

	initEmbeddedMessageData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};
		args.filter_data.object_type_id = 50;
		args.filter_data.object_id = this.current_edit_record.id;

		var read_ids = [];

		$this.message_control_api['getEmbeddedMessage']( args, {onResult: function( res ) {

			if ( !$this.edit_view ) {
				return;
			}

			var data = res.getResult();

			if ( Global.isArray( data ) ) {

				$( $this.edit_view.find( '.separate' ) ).css( 'display', 'block' );

				$this.messages = data;

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

					var cloneMessageControl = $( $this.edit_view_tab.find( '#tab_request' ).find( '.edit-view-tab' ).find( '.second-column' ) ).clone();

					cloneMessageControl.css( 'display', 'block' ).appendTo( container );
				}

				if ( read_ids.length > 0 ) {
					$this.message_control_api['markRecipientMessageAsRead']( read_ids, {onResult: function( res ) {
					}} );
				}

				$this.edit_view_tab.find( '#tab_request' ).find( '.edit-view-tab' ).find( '.second-column' ).remove();
				$this.edit_view_tab.find( '#tab_request' ).find( '.edit-view-tab' ).append( container.html() );

			} else {

				$( $this.edit_view.find( '.separate' ) ).css( 'display', 'none' );
			}

			$this.setAuthorizationGridSize();

		}} );
	},

	onEditClick: function( editId, noRefreshUI ) {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();

		$this.initEditView();

	},

	setURL: function() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

		this._super( 'setURL' );
	},

	setDefaultMenu: function( doNotSetFocus ) {

        //Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
        if (!this.context_menu_array) {
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

			context_btn.removeClass( 'disable-image' );
			context_btn.removeClass( 'invisible-image' );

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
				case ContextMenuIconName.send:
					this.setDefaultMenuSendIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
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

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.send:
					this.setEditMenuSendIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuNavViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuNavViewIcon( context_btn, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuNavEditIcon( context_btn, 'user' );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.is_edit || this.is_add ) {
			context_btn.addClass( 'disable-image' );
		}

		if ( this.is_changed ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {
		if ( this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuNavViewIcon: function( context_btn, pId ) {
		if ( this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
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
		if ( this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuSendIcon: function( context_btn, pId ) {

		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		}

		if ( this.is_add ) {
			context_btn.removeClass( 'disable-image' );
		}

	},

	getFilterColumnsFromDisplayColumns: function( authorization_history ) {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( authorization_history ) {
			if ( this.authorization_history_grid ) {
				display_columns = this.authorization_history_grid.getGridParam( 'colModel' );
			}
		} else {
			if ( this.grid ) {
				display_columns = this.grid.getGridParam( 'colModel' );
			}
		}
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.user_id = true;
		column_filter.status_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
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

				if ( $this.is_edit ) {
					$this.onViewClick( $this.current_edit_record.id );
				} else {
					$this.removeEditView();
				}

			}

		}

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

			if ( $this.is_edit ) {
				$this.onViewClick( $this.current_edit_record.id );
			} else {
				$this.current_edit_record = null;
				$this.removeEditView();
			}

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	removeEditView: function() {

		if ( this.edit_view ) {
			this.edit_view.remove();
		}
		this.edit_view = null;
		this.is_mass_editing = false;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_changed = false;
		this.mass_edit_record_ids = [];
		this.edit_view_tab_selected_index = 0;
		LocalCacheData.current_doing_context_action = '';

		// reset parent context menu if edit only mode
		if ( !this.edit_only_mode ) {
			this.setDefaultMenu();
			this.initRightClickMenu();
		} else {
			this.setParentContextMenuAfterSubViewClose();
		}

		this.reSetURL();

		this.sub_log_view_controller = null;
		this.edit_view_ui_dic = {};
		this.edit_view_form_item_dic = {};
		this.edit_view_error_ui_dic = {};
	},

	uniformVariable: function( records ) {

		if ( typeof records === 'object' ) {
			records.user_id = LocalCacheData.loginUser.id;
			records.first_name = LocalCacheData.loginUser.first_name;
			records.last_name = LocalCacheData.loginUser.last_name;
		}

		var msg = {};

		if ( this.is_edit ) {

			msg.body = this.current_edit_record['body'];

			msg.from_user_id = this.current_edit_record['user_id'];
			msg.to_user_id = this.current_edit_record['user_id'];

			msg.object_id = this.current_edit_record['id'];

			msg.object_type_id = 50;

			if ( Global.isFalseOrNull( this.current_edit_record['subject'] ) ) {
				msg.subject = this.edit_view_ui_dic['subject'].getValue();
			} else {
				msg.subject = this.current_edit_record['subject'];
			}

			return msg;

		}

		return records;
	},

	validate: function() {
		if ( this.is_add ) {
			this._super( 'validate' );

		} else if ( this.is_edit ) {
			var $this = this;

			var record = {};

			record = this.current_edit_record;

			record = this.uniformVariable( record );

			this.message_control_api['validateMessageControl']( record, {onResult: function( result ) {
				$this.validateResult( result );

			}} );
		}
	},

	onSaveClick: function() {
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_add ) {
			this._super( 'onSaveClick' );
		} else if ( this.is_edit ) {
			var $this = this;
			var record;
			this.is_add = false;
			record = this.current_edit_record;
			record = this.uniformVariable( record );
			this.message_control_api['setMessageControl']( record, {onResult: function( result ) {
				$this.onSaveResult( result );
			}} );
		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = 0;
		this._super( 'search', set_default_menu, page_action, page_number, callBack )
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuScheduleIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuSendIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {
		if ( Global.isSet( menu_name ) ) {
			var id = menu_name;
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
			case ContextMenuIconName.send:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.edit_employee:
				this.onNavigationClick( id );
				break;

		}
	},

	onNavigationClick: function( iconName ) {
		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				filter = {filter_data: {}};
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id;
					filter.base_date = this.current_edit_record.date_stamp;

					Global.addViewTab( this.viewId, 'Requests', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						var selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = {user_id: true, date_stamp: true};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							filter.user_id = result_data.user_id;
							filter.base_date = result_data.date_stamp;

							Global.addViewTab( $this.viewId, 'Requests', window.location.href );
							IndexViewController.goToView( 'TimeSheet', filter );

						}} );
					}

				}

				break;

			case ContextMenuIconName.edit_employee:
				filter = {filter_data: {}};
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = {user_id: true};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							IndexViewController.openEditView( $this, 'Employee', result_data.user_id );

						}} );
					}

				}
				break;
			case ContextMenuIconName.schedule:

				filter = {filter_data: {}};

				var include_users = null;

				if ( Global.isSet( this.current_edit_record ) ) {

					include_users = [this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id];
					filter.filter_data.include_user_ids = {value: include_users };
					;
					filter.select_date = this.current_edit_record.date_stamp;

					Global.addViewTab( $this.viewId, 'Requests', window.location.href );
					IndexViewController.goToView( 'Schedule', filter );

				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = {user_id: true, date_stamp: true};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							include_users = [result_data.user_id];

							filter.filter_data.include_user_ids = {value: include_users };

							filter.select_date = result_data.date_stamp;

							Global.addViewTab( $this.viewId, 'Requests', window.location.href );
							IndexViewController.goToView( 'Schedule', filter );

						}} );
					}

				}
				break;
		}
	},

	setEditViewWidgetsMode: function() {

		var did_clean = false;
		for ( var key in this.edit_view_ui_dic ) {
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );

			var column = widget.parent().parent().parent();
			if ( !column.hasClass( 'v-box' ) ) {

				if ( !did_clean ) {
					column.find( '.edit-view-form-item-label-div-first-row' ).removeClass( 'edit-view-form-item-label-div-first-row' );
					column.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
					column.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
					did_clean = true;
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
//				  if ( Global.isSet( widget.setEnabled ) ) {
//					  widget.setEnabled( false );
//				  }
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {

					widget.setEnabled( true );

				}
			}

		}

	},

	openEditView: function() {

		this.initEditViewUI( this.viewId, this.edit_view_tpl );

		this.setEditViewWidgetsMode();
	},

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

		this.edit_view_tab = this.edit_view_tab.tabs( {show: function( e, ui ) {

			if ( !$this.edit_view_tab || !$this.edit_view_tab.is( ':visible' ) ) {
				return;
			}

			$this.onTabShow( e, ui )
		}} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui )
		} );

		Global.contentContainer().append( this.edit_view );
		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_add ) {

			this.buildAddViewUI();
		} else if ( this.is_viewing ) {

			this.buildViewUI();
		} else if ( this.is_edit ) {

			this.buildEditViewUI();
		}

		//Calculated tab's height
		this.edit_view_tab.resize( function() {

			$this.setEditViewTabHeight();

			$this.setAuthorizationGridSize();

		} );

		$this.setEditViewTabHeight();
	},

} );

RequestViewController.loadView = function() {

	Global.loadViewSource( 'Request', 'RequestView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};