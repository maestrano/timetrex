AccrualBalanceViewController = BaseViewController.extend( {
	el: '#accrual_balance_view_container',

	user_group_api: null,
	user_group_array: null,

	sub_accrual_view_controller: null,

	log_object_ids: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'AccrualBalanceEditView.html';
		this.permission_id = 'accrual';
		this.viewId = 'AccrualBalance';
		this.script_name = 'AccrualBalanceView';
		this.table_name_key = 'accrual';
		this.context_menu_name = $.i18n._( 'Accrual Balances' );
		this.navigation_label = $.i18n._( 'Accrual Balance' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIAccrualBalance' ))();
		this.accrual_api = new (APIFactory.getAPIClass( 'APIAccrual' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.edit] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save] = true;

		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.cancel] = true;
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

		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {

			res = res.getResult();
			res = Global.buildTreeRecord( res );

			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
			}

			$this.user_group_array = res;

		}} );

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_accrual': $.i18n._( 'Accrual' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualBalance' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_BALANCE,
			navigation_mode: true,
			addition_source_function: function( target, data ) {
				return $this.__createRowId( data );
			},
			show_search_inputs: true
		} );

		this.setNavigation();

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

			new SearchField( {label: $.i18n._( 'Accrual Account' ),
				in_column: 1,
				field: 'accrual_policy_account_id',
				layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )
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
		column_filter.user_id = true;
		column_filter.accrual_policy_account_id = true;

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

	__createRowId: function( data ) {
		for ( var i = 0; i < data.length; i++ ) {
			data[i].id = data[i]['user_id'] + '_' + data[i]['accrual_policy_account_id'];
		}

		return data;
	},

//	saveLogIds: function( data ) {
//		this.parent_view_controller.log_object_ids = [];
//		for ( var i = 0; i < data.length; i++ ) {
//			this.parent_view_controller.log_object_ids.push( data[i]['id'] );
//		}
//
//		return data;
//	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {
		this.setDefaultMenuEditIcon( context_btn, grid_selected_length, pId );
	},

	onAddClick: function() {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		var selected_item = null;
		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
		} else {
			var grid_source_data = $this.grid.getGridParam( 'data' );
			selected_item = grid_source_data[0];
		}

		var filter = {};
		filter.filter_data = {};
		if ( selected_item ) {
			filter.filter_data.user_id = selected_item.user_id;
			filter.filter_data.accrual_policy_account_id = selected_item.accrual_policy_account_id;
		}

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			result_data = $this.__createRowId( result_data );

			result_data = result_data[0];

			if ( !result_data ) {
				result_data = {};
			}

			$this.current_edit_record = result_data;

			if ( $this.current_edit_record && $this.current_edit_record.user_id && $this.current_edit_record.accrual_policy_account_id ) {
				filter.filter_data.user_id = $this.current_edit_record.user_id;
				filter.filter_data.accrual_policy_account_id = $this.current_edit_record.accrual_policy_account_id;
			}

			// get the accrual data with the same filter data in order to be used for the audit tab.
			$this.accrual_api['get' + $this.accrual_api.key_name]( filter, {onResult: function( res ) {
				var result = res.getResult();
				$this.log_object_ids = [];
				for ( var i = 0; i < result.length; i++ ) {
					$this.log_object_ids.push( result[i]['id'] );
				}

				$this.initEditView();

			}} );

		}} );

	},

	onViewClick: function( view_id ) {
		var $this = this;
		this.is_viewing = true;
		this.is_edit = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var selected_item = null;
		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
		} else {
			var grid_source_data = $this.grid.getGridParam( 'data' );
			selected_item = grid_source_data[0];
		}

		var filter = {};
		filter.filter_data = {};

		if ( Global.isSet( view_id ) ) {
			var filter_data = view_id.toString().split( '_' );
			filter.filter_data.user_id = filter_data[0];
			filter.filter_data.accrual_policy_account_id = filter_data[1];
		} else {
			filter.filter_data.user_id = selected_item.user_id;
			filter.filter_data.accrual_policy_account_id = selected_item.accrual_policy_account_id;
		}

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			result_data = $this.__createRowId( result_data );

			result_data = result_data[0];

			if ( !result_data ) {
				TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
				$this.onCancelClick();
				return;
			}

			$this.current_edit_record = result_data;
			if ( $this.current_edit_record && $this.current_edit_record.user_id && $this.current_edit_record.accrual_policy_account_id ) {
				filter.filter_data.user_id = $this.current_edit_record.user_id;
				filter.filter_data.accrual_policy_account_id = $this.current_edit_record.accrual_policy_account_id;
			}

			// get the accrual data with the same filter data in order to be used for the audit tab.
			$this.accrual_api['get' + $this.accrual_api.key_name]( filter, {onResult: function( res ) {
				var result = res.getResult();
				$this.log_object_ids = [];
				for ( var i = 0; i < result.length; i++ ) {
					$this.log_object_ids.push( result[i]['id'] );
				}

				$this.initEditView();

			}} );

		}} );

	},

	setEditViewData: function() {
		this.is_changed = false;
		this.initEditViewData();
		this.switchToProperTab();
		this.initTabData();
	},

	initEditViewData: function() {
		var $this = this;
		if ( !this.edit_only_mode && this.navigation ) {
			var grid_current_page_items = this.grid.getGridParam( 'data' );

			var navigation_div = this.edit_view.find( '.navigation-div' );

			navigation_div.css( 'display', 'block' );
			//Set Navigation Awesomebox

			//init navigation only when open edit view

			if ( !this.navigation.getSourceData() ) {

				this.navigation.setSourceData( grid_current_page_items );
				this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
				this.navigation.setPagerData( this.pager_data );

//				this.navigation.setDisPlayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ) );
				var default_args = {};
				default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				default_args.filter_sort = this.select_layout.data.filter_sort;
				this.navigation.setDefaultArgs( default_args );
			}

			this.navigation.setValue( this.current_edit_record );
		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();
		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

	initTabData: function() {
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 0 ) {
			if ( this.current_edit_record ) {
				this.edit_view_tab.find( '#tab_accrual' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubAccrualView();
			} else {
				this.edit_view_tab.find( '#tab_accrual' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {

			if ( this.current_edit_record ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	onTabShow: function( e, ui ) {

		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_tab_selected_index === 0 ) {
			if ( this.current_edit_record ) {
				this.edit_view_tab.find( '#tab_accrual' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubAccrualView();
			} else {
				this.edit_view_tab.find( '#tab_accrual' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 1 ) {
			if ( this.current_edit_record ) {
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

	showNoResultCover: function( show_new_btn ) {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: false} );
		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );

		var grid_div = $( this.el ).find( '.grid-div' );

		grid_div.append( this.no_result_box );

		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
	},

	initSubAccrualView: function() {
		var $this = this;

		if ( this.sub_accrual_view_controller ) {
			this.sub_accrual_view_controller.buildContextMenu( true );
			this.sub_accrual_view_controller.setDefaultMenu();
			$this.sub_accrual_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/attendance/accrual/AccrualViewController.js', function() {

			var tab_accrual = $this.edit_view_tab.find( '#tab_accrual' );
			var firstColumn = tab_accrual.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Accrual' + 'View' );
			AccrualViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_accrual_view_controller = subViewController;
			$this.sub_accrual_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_view_controller.parent_view_controller = $this;
			$this.sub_accrual_view_controller.is_trigger_add = $this.is_add ? true : false;
//			$this.sub_accrual_view_controller.__createRowId = $this.saveLogIds;
			$this.sub_accrual_view_controller.initData();
		}

	},

	initSubLogView: function( tab_id ) {
		var $this = this;
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_key = 'object_id';
			$this.sub_log_view_controller.parent_value = $this.log_object_ids;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/core/log/LogViewController.js', function() {

			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			// Can't directly open Audit in this case, because Audit need data from Sub Accrual View and it not
			// be loaded if directly open audit from url
			if(!$this.log_object_ids){
				$this.edit_view_tab.tabs( 'select', 0 );
				return;
			}
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_key = 'object_id';
			$this.sub_log_view_controller.parent_value = $this.log_object_ids;
			$this.sub_log_view_controller.table_name_key = $this.table_name_key;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();

		}
	},

	removeEditView: function() {
		this._super( 'removeEditView' );
		this.sub_accrual_view_controller = null;

	},

	setNavigation: function() {

		var $this = this;
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

				$this.onViewClick( next_select_item_id ); //Dont refresh UI

			}

		} );

	}

} );