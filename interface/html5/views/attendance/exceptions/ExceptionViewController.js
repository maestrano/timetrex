ExceptionViewController = BaseViewController.extend( {
	el: '#exception_view_container',
	status_array: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'ExceptionEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'Exception';
		this.script_name = 'ExceptionView';
		this.context_menu_name = $.i18n._( 'Exceptions' );
		this.navigation_label = $.i18n._( 'Exception' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIException' ))();

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	onContextMenuClick: function( context_btn, menu_name ) {
		this._super( 'onContextMenuClick', context_btn, menu_name );

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
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.edit_pay_period:
			case ContextMenuIconName.edit_pay_period_schedule:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.timesheet:
				this.onNavigationClick( id );
				break;

		}

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

			switch ( id ) {
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.edit_pay_period_schedule:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'pay_period_schedule' );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'pay_period_schedule' );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	initPermission: function() {

		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

	},

	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController
		//Don't have any edit view
		//Error: Uncaught TypeError: undefined is not a function in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 2751
		this.autoOpenEditOnlyViewIfNecessary();

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

	onNavigationClick: function( iconName ) {

		var select_item = this.getSelectedItem();
		var user_id = select_item.user_id;
		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				IndexViewController.openEditView( this, 'Employee', user_id );
				break;
			case ContextMenuIconName.edit_pay_period:
				var pay_period_id = select_item.pay_period_id;
				if ( pay_period_id ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_id );
				}
				break;
			case ContextMenuIconName.edit_pay_period_schedule:
				var pay_period_schedule_id = select_item.pay_period_schedule_id;
				if ( pay_period_schedule_id ) {
					IndexViewController.openEditView( this, 'PayPeriodSchedule', pay_period_id );
				}
				break;
			case ContextMenuIconName.schedule:
				var filter = {filter_data: {}};
				var include_users = {value: [user_id] };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = select_item.date_stamp;
				Global.addViewTab( this.viewId, 'Exception', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );

				break;
			case ContextMenuIconName.timesheet:
				filter = {filter_data: {}};
				filter.user_id = user_id;
				filter.base_date = select_item.date_stamp;
				Global.addViewTab( this.viewId, 'Exception', window.location.href );
				IndexViewController.goToView( 'TimeSheet', filter );

				break;

		}
	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'status', 'user_status_id', new (APIFactory.getAPIClass( 'APIUser' ))() );
		this.initDropDownOption( 'severity', null, new (APIFactory.getAPIClass( 'APIExceptionPolicy' ))() );
		this.initDropDownOption( 'type', 'exception_policy_type_id', new (APIFactory.getAPIClass( 'APIExceptionPolicy' ))() );

		var user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {

			res = res.getResult();
			res = Global.buildTreeRecord( res );

			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}} );

	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//navigation group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var timesheet_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedules' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var edit_employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		var edit_pay_period = new RibbonSubMenu( {
			label: $.i18n._( 'Edit Pay<br>Period' ),
			id: ContextMenuIconName.edit_pay_period,
			group: navigation_group,
			icon: Icons.pay_period,
			permission_result: true,
			permission: null
		} );

		var pay_stub_view = new RibbonSubMenu( {
			label: $.i18n._( 'Edit PP<br>Schedule' ),
			id: ContextMenuIconName.edit_pay_period_schedule,
			group: navigation_group,
			icon: Icons.pay_period_schedule,
			permission_result: true,
			permission: null
		} );

		return [menu];

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
		} else if ( search_tab_select_index === 1 ) {
			this.filter_data = [];
			target_ui_dic = this.adv_search_field_ui_dic;
		} else {
			return;
		}

		var $this = this;
		$.each( target_ui_dic, function( key, content ) {

			$this.filter_data[key] = {field: key, id: '', value: target_ui_dic[key].getValue( true )};

			if ( key === 'show_pre_mature' && $this.filter_data[key].value !== true ) {

				delete $this.filter_data[key];
				return false;
			}

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

	search: function( set_default_menu, page_action, page_number ) {
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
		} else {
			this.last_select_ids = this.getGridSelectIdArray();
		}

		if ( filter.filter_data.show_pre_mature === true ) {
			filter.filter_data.type_id = [5];
		} else {
			filter.filter_data.type_id = [30, 40, 50, 55, 60, 70];
		}

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {

			var result_data = result.getResult();
			if ( !Global.isArray( result_data ) ) {
				$this.showNoResultCover()
			} else {
				$this.removeNoResultCover();
				if ( Global.isSet( $this.__createRowId ) ) {
					result_data = $this.__createRowId( result_data );
				}

				result_data = Global.formatGridData( result_data, $this.api.key_name );
			}

			if ( $this.refresh_id > 0 ) {
				$this.refresh_id = null;
				var grid_source_data = $this.grid.getGridParam( 'data' );
				var len = grid_source_data.length;

				if ( $.type( grid_source_data ) !== 'array' ) {
					grid_source_data = [];
				}

				var found = false;
				var new_record = result_data[0];
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
					$this.grid.trigger( 'reloadGrid' );
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

			$this.autoOpenEditViewIfNecessary();

			$this.searchDone();

		}} );

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
				case ContextMenuIconName.timesheet:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						this.onNavigationClick( ContextMenuIconName.timesheet );
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
				case ContextMenuIconName.schedule:
					need_break = true;
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						this.onNavigationClick( ContextMenuIconName.schedule );
						return;
					}
					break;
			}
		}

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Severity' ),
				in_column: 1,
				field: 'severity_id',
				multiple: true,
				adv_search: true,
				basic_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Exception' ),
				in_column: 1,
				field: 'exception_policy_type_id',
				multiple: true,
				adv_search: true,
				basic_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Department' ),
				field: 'department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Title' ),
				in_column: 3,
				field: 'title_id',
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Show Pre-Mature' ),
				in_column: 3,
				field: 'show_pre_mature',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.CHECKBOX} )

		];
	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.exception_color = true;
		column_filter.exception_background_color = true;
		column_filter.user_id = true;
		column_filter.pay_period_id = true;
		column_filter.pay_period_schedule_id = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
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

			if ( item.exception_background_color ) {
				var severity = $( "tr[id='" + item.id + "']" ).find( 'td[aria-describedby="' + this.ui_id + '_grid_severity"]' );
				severity.css( 'background-color', item.exception_background_color );
				severity.css( 'font-weight', 'bold' );
			}

			if ( item.exception_color ) {
				var code = $( "tr[id='" + item.id + "']" ).find( 'td[aria-describedby="' + this.ui_id + '_grid_exception_policy_type_id"]' );
				code.css( 'color', item.exception_color );
				code.css( 'font-weight', 'bold' );
			}

		}
	}

} );

ExceptionViewController.loadView = function() {

	Global.loadViewSource( 'Exception', 'ExceptionView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};