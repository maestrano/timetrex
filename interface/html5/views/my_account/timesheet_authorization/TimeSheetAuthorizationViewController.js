TimeSheetAuthorizationViewController = BaseViewController.extend( {
	el: '#timesheet_authorization_view_container',

	type_array: null,
	hierarchy_level_array: null,

	messages: null,

	message_control_api: null,

	authorization_api: null,

	request_api: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'TimeSheetAuthorizationEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'TimeSheetAuthorization';
		this.script_name = 'TimeSheetAuthorizationView';
		this.table_name_key = 'pay_period_time_sheet_verify';
		this.context_menu_name = $.i18n._( 'Time Sheet(Authorization)' );
		this.navigation_label = $.i18n._( 'TimeSheet' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayPeriodTimeSheetVerify' ))();
		this.request_api = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();
		this.authorization_api = new (APIFactory.getAPIClass( 'APIAuthorization' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.add] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;

		this.render();
		this.buildContextMenu( true );

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;

		this.request_api.getHierarchyLevelOptions( [-1], {onResult: function( res ) {
			var data = res.getResult();
			$this['hierarchy_level_array'] = Global.buildRecordArray( data );

			if ( Global.isSet( $this.basic_search_field_ui_dic['hierarchy_level'] ) ) {
				$this.basic_search_field_ui_dic['hierarchy_level'].setSourceData( Global.buildRecordArray( data ) );
			}

		}} );

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = 0;
		this._super( 'search', set_default_menu, page_action, page_number, callBack )
	},

	/* jshint ignore:end */
	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Action' ),
			id: this.script_name + 'action',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var authorization_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Authorization' ),
			id: this.script_name + 'authorization',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var objects_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Objects' ),
			id: this.script_name + 'objects',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
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

		var authorization = new RibbonSubMenu( {
			label: $.i18n._( 'Authorize' ),
			id: ContextMenuIconName.authorization,
			group: authorization_group,
			icon: Icons.authorization,
			permission_result: true,
			permission: null
		} );

		var pass = new RibbonSubMenu( {
			label: $.i18n._( 'Pass' ),
			id: ContextMenuIconName.pass,
			group: authorization_group,
			icon: Icons.pass,
			permission_result: true,
			permission: null
		} );

		var decline = new RibbonSubMenu( {
			label: $.i18n._( 'Decline' ),
			id: ContextMenuIconName.decline,
			group: authorization_group,
			icon: Icons.decline,
			permission_result: true,
			permission: null
		} );

		var authorization_request = new RibbonSubMenu( {
			label: $.i18n._( 'Request<br>Authorization' ),
			id: ContextMenuIconName.authorization_request,
			group: objects_group,
			icon: Icons.authorization_request,
			permission_result: true,
			permission: null
		} );

		var authorization_timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet<br>Authorization' ),
			id: ContextMenuIconName.authorization_timesheet,
			group: objects_group,
			icon: Icons.authorization_timesheet,
			selected: true,
			permission_result: true,
			permission: null
		} );

		var authorization_expense = new RibbonSubMenu( {
			label: $.i18n._( 'Expense<br>Authorization' ),
			id: ContextMenuIconName.authorization_expense,
			group: objects_group,
			icon: Icons.authorization_expense,
			selected: false,
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

	getFilterColumnsFromDisplayColumns: function() {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.start_date = true;
		column_filter.end_date = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	setDefaultMenuAuthorizationExpenseIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !( LocalCacheData.getCurrentCompany().product_edition_id >= 25 ) ) {
			context_btn.addClass( 'invisible-image' );
		}
		context_btn.removeClass( 'disable-image' );
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
			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );
			/* jshint ignore:start */
			switch ( id ) {
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.send:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization:
					this.setDefaultMenuAuthorizationIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.pass:
					this.setDefaultMenuPassIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.decline:
					this.setDefaultMenuDeclineIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization_request:
					this.setDefaultMenuAuthorizationRequestIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization_timesheet:
					this.setDefaultMenuAuthorizationTimesheetIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.authorization_expense:
					this.setDefaultMenuAuthorizationExpenseIcon( context_btn, grid_selected_length );
					break;

			}

			/* jshint ignore:end */

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
			/* jshint ignore:start */
			switch ( id ) {
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.send:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.authorization:
					this.setEditMenuAuthorizationIcon( context_btn );
					break;
				case ContextMenuIconName.pass:
					this.setEditMenuPassIcon( context_btn );
					break;
				case ContextMenuIconName.decline:
					this.setEditMenuDeclineIcon( context_btn );
					break;
				case ContextMenuIconName.authorization_request:
					this.setEditMenuAuthorizationRequestIcon( context_btn );
					break;
				case ContextMenuIconName.authorization_timesheet:
					this.setEditMenuAuthorizationTimesheetIcon( context_btn );
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
				case ContextMenuIconName.authorization_expense:
					this.setEditMenuAuthorizationExpenseIcon( context_btn );
					break;

			}

			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuAuthorizationExpenseIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		context_btn = $( context_btn );

		var id;
		var context_menu = $( context_btn.find( '.ribbon-sub-menu-icon' ) );

		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			id = context_menu.attr( 'id' );
		}

		if ( context_btn.hasClass( 'disable-image' ) ) {
			return;
		}

		if ( ContextMenuIconName.authorization_timesheet === id && context_menu.hasClass( 'selected-menu' ) ) {
			return;
		}
		/* jshint ignore:start */
		switch ( id ) {
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.send:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.authorization:
				ProgressBar.showOverlay();
				this.onAuthorizationClick();
				break;
			case ContextMenuIconName.pass:
				this.onPassClick();
				break;
			case ContextMenuIconName.decline:
				ProgressBar.showOverlay();
				this.onDeclineClick();
				break;
			case ContextMenuIconName.authorization_request:
				this.onAuthorizationRequestClick();
				break;
			case ContextMenuIconName.authorization_timesheet:
				this.onAuthorizationTimesheetClick();
				break;
			case ContextMenuIconName.authorization_expense:
				this.onAuthorizationExpenseClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.edit_employee:
				this.onNavigationClick( id );
				break;

		}
		/* jshint ignore:end */
	},

	onAuthorizationExpenseClick: function() {
		IndexViewController.goToView( 'ExpenseAuthorization' );
	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, 'Authorization - TimeSheet', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = {value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, 'Authorization - TimeSheet', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;

		}

	},

	onSaveClick: function() {

		if ( this.is_edit ) {

			var $this = this;

			var record = {};

			this.is_add = false;

			record = this.uniformVariable( record );

			this.message_control_api['setMessageControl']( record, {onResult: function( result ) {

				$this.onSaveResult( result );

			}} );
		}

	},

	onSaveResult: function( result ) {
		var $this = this;
		var current_edit_record_id;

		if ( result.isValid() ) {

			current_edit_record_id = $this.current_edit_record.id;

			$this.current_edit_record = null;
			$this.onViewClick( current_edit_record_id );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	validate: function() {

		var $this = this;

		var record = this.current_edit_record;

		record = this.uniformVariable( record );

		this.message_control_api['validate' + this.message_control_api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onAuthorizationClick: function() {
		var $this = this;
		var filter = {};
		filter.authorized = true;
		filter.object_id = $this.current_edit_record.id;
		filter.object_type_id = 90;

		$this.authorization_api['setAuthorization']( [filter], {onResult: function( res ) {
			$this.search( null, null, null, function( result ) {
				if ( $.type( result.getResult() ) !== 'array' || result.getResult().length < 1 ) {
					$this.onCancelClick();
				} else {
					$this.onRightArrowClick();
				}

			} );

		}} );

	},

	onPassClick: function() {
		if ( this.grid.getGridParam( 'data' ).length === 1 ) {
			this.onCancelClick()
		} else {
			this.onRightArrowClick();
		}
	},

	onAuthorizationRequestClick: function() {
		IndexViewController.goToView( 'RequestAuthorization' );
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

	onDeclineClick: function() {

		var $this = this;
		var filter = {};

		filter.authorized = false;
		filter.object_id = $this.current_edit_record.id;
		filter.object_type_id = 90;

		$this.authorization_api['setAuthorization']( [filter], {onResult: function( res ) {
			$this.search( null, null, null, function( result ) {
				if ( $.type( result.getResult() ) !== 'array' || result.getResult().length < 1 ) {
					$this.onCancelClick();
				} else {
					$this.onRightArrowClick();
				}

			} );

		}} );
	},

	onAuthorizationTimesheetClick: function() {
		this.search( false );
	},

	uniformVariable: function( records ) {

		var msg = {};

		if ( this.is_edit ) {

			msg.body = this.current_edit_record['body'];

			msg.from_user_id = this.current_edit_record['user_id'];
			msg.to_user_id = this.current_edit_record['user_id'];

			msg.object_id = this.current_edit_record['id'];

			msg.object_type_id = 90;

			if ( Global.isFalseOrNull( this.current_edit_record['subject'] ) ) {
				msg.subject = this.edit_view_ui_dic['subject'].getValue();
			} else {
				msg.subject = this.current_edit_record['subject'];
			}

			return msg;

		}

		return records;
	},

	onGridDblClickRow: function() {

		ProgressBar.showOverlay();
		this.onViewClick();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuAuthorizationIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuPassIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuDeclineIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuAuthorizationRequestIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuAuthorizationTimesheetIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setDefaultMenuSaveIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuPassIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuDeclineIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationRequestIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.removeClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationTimesheetIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.removeClass( 'disable-image' );
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

			new SearchField( {label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Hierarchy Level' ),
				in_column: 1,
				multiple: false,
				set_any: false,
				field: 'hierarchy_level',
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

	openEditView: function() {

		this.initEditViewUI( this.viewId, this.edit_view_tpl );

		this.setEditViewWidgetsMode();
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

	getSubViewFilter: function( filter ) {

		if ( filter.length === 0 ) {
			filter = {};
		}

		if ( !Global.isSet( filter.hierarchy_level ) ) {
			filter['hierarchy_level'] = 1;
		}

		return filter;
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		this.setTabLabels( {
			'tab_timesheet_verification': $.i18n._( 'Message' )
		} );


		this.navigation = null;

		//Tab 0 start

		var tab_timesheet_verification = this.edit_view_tab.find( '#tab_timesheet_verification' );

		var tab_timesheet_verification_column1 = tab_timesheet_verification.find( '.first-column' );

		var tab_timesheet_verification_column2 = tab_timesheet_verification.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_timesheet_verification_column1 );

		// Subject
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'subject', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_timesheet_verification_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'body', width: 600, height: 400} );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_timesheet_verification_column1, '', null, null, true );

		tab_timesheet_verification_column2.css( 'display', 'none' );

	},

	buildViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_timesheet_verification': $.i18n._( 'TimeSheet Verification' )
		} );



		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayPeriodTimeSheetVerify' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 first column start

		var tab_timesheet_verification = this.edit_view_tab.find( '#tab_timesheet_verification' );

		var tab_timesheet_verification_column1 = tab_timesheet_verification.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_timesheet_verification_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_timesheet_verification_column1, '' );

		// Pay Period
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'pay_period'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period' ), form_item_input, tab_timesheet_verification_column1 );

		// tab_timesheet_verification first column end

		var separate_box = tab_timesheet_verification.find( '.separate' );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Messages' )} );
		this.addEditFieldToColumn( null, form_item_input, separate_box );

		separate_box.css( 'display', 'none' );

		// Tab 0 second column start

		var tab_timesheet_verification_column2 = tab_timesheet_verification.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_timesheet_verification_column2 );

		// From
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'from'} );
		this.addEditFieldToColumn( $.i18n._( 'From' ), form_item_input, tab_timesheet_verification_column2, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'subject'} );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_timesheet_verification_column2 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'body', width: 600, height: 400} );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_timesheet_verification_column2, '', null, true, true );

		// Tab 0 second column end

		tab_timesheet_verification_column2.css( 'display', 'none' );

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
			$this.onTabShow( e, ui );
		}} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui );
		} );

		Global.contentContainer().append( this.edit_view );
		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_viewing ) {
			LocalCacheData.current_doing_context_action = 'view';
			this.buildViewUI();
		} else if ( this.is_edit ) {
			LocalCacheData.current_doing_context_action = 'edit';
			this.buildEditViewUI();
		}

		//Calculated tab's height
		this.edit_view_tab.resize( function() {

			$this.setEditViewTabHeight();

		} );

		$this.setEditViewTabHeight();
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
						if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						}
						break;
					case 'pay_period':
						widget.setValue( this.current_edit_record['start_date'] + ' ' + this.current_edit_record['end_date'] );
						break;
					case 'subject':
						if ( this.is_edit ) {
							if ( Global.isSet( this.messages ) ) {
								widget.setValue( 'Re: ' + this.messages[0].subject );
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

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );

		if ( this.is_viewing ) {

			this.initEmbeddedMessageData();
		} else {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

	},

	initEmbeddedMessageData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};
		args.filter_data.object_type_id = 90;
		args.filter_data.object_id = this.current_edit_record.id;

		$this.message_control_api['getEmbeddedMessage']( args, {onResult: function( res ) {

			if ( !$this.edit_view ) {
				return;
			}

			var data = res.getResult();

			if ( Global.isArray( data ) && data.length > 0 ) {
				$this.messages = data;
				$( $this.edit_view_tab.find( '#tab_timesheet_verification' ).find( '.edit-view-tab' ).find( '.separate' ) ).css( 'display', 'block' );
			}

			var container = $( '<div></div>' );

			for ( var key in data ) {

				var currentItem = data[key];
				/* jshint ignore:start */
				if ( currentItem.status_id == 10 ) {
					$this.message_control_api['markRecipientMessageAsRead']( [currentItem.id], {onResult: function( res ) {
					}} );
				}
				/* jshint ignore:end */

				var from = currentItem.from_first_name + '' + currentItem.from_last_name + '@' + currentItem.updated_date;
				$this.edit_view_ui_dic['from'].setValue( from );
				$this.edit_view_ui_dic['subject'].setValue( currentItem.subject );
				$this.edit_view_ui_dic['body'].setValue( currentItem.body );

				var cloneMessageControl = $( $this.edit_view_tab.find( '#tab_timesheet_verification' ).find( '.edit-view-tab' ).find( '.second-column' ) ).clone();

			}

			$this.edit_view_tab.find( '#tab_timesheet_verification' ).find( '.edit-view-tab' ).find( '.second-column' ).remove();
			$this.edit_view_tab.find( '#tab_timesheet_verification' ).find( '.edit-view-tab' ).append( container.html() );

		}} );
	}


} );
