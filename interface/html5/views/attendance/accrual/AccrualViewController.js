AccrualViewController = BaseViewController.extend( {
	el: '#accrual_view_container',
	type_array: null,

	user_group_api: null,
	user_group_array: null,
	user_type_array: null,
	delete_type_array: null,
	date_api: null,

	edit_enabled: false,
	delete_enabled: false,

	is_trigger_add: false,

	sub_view_grid_data: null,

	hide_search_field: false,

//	  parent_filter: null,

	initialize: function() {
		if ( Global.isSet( this.options.sub_view_mode ) ) {
			this.sub_view_mode = this.options.sub_view_mode;
		}
		this._super( 'initialize' );
		this.edit_view_tpl = 'AccrualEditView.html';
		this.permission_id = 'accrual';
		this.viewId = 'Accrual';
		this.script_name = 'AccrualView';
		this.table_name_key = 'accrual';
		this.context_menu_name = $.i18n._( 'Accruals' );
		this.navigation_label = $.i18n._( 'Accrual' ) + ':';

		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true; //Hide some context menus
		this.api = new (APIFactory.getAPIClass( 'APIAccrual' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		this.initPermission();
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
		this.setSelectRibbonMenuIfNecessary( 'Accrual' );

	},

	initPermission: function() {

		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.hide_search_field = false;
		} else {
			this.hide_search_field = true;
		}

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'user_type', null, null, function( res ) {
			var result = res.getResult();
			$this.user_type_array = result;

		} );
		this.initDropDownOption( 'delete_type', null, null, function( res ) {
			var result = res.getResult();
			$this.delete_type_array = result;

		} );

		this.initDropDownOption( 'type', null, null, function( res ) {
			var result = res.getResult();
			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['type_id'] ) {
				$this.basic_search_field_ui_dic['type_id'].setSourceData( Global.buildRecordArray( result ) );
			}
		} );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				}

				$this.user_group_array = res;

			}
		} );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_accrual': $.i18n._( 'Accrual' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrual' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_accrual = this.edit_view_tab.find( '#tab_accrual' );

		var tab_accrual_column1 = tab_accrual.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_accrual_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee

		if ( this.sub_view_mode ) {

			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'full_name'} );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_accrual_column1, '' );
		} else {

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_empty: true,
				field: 'user_id'
			} );

			var default_args = {};
			default_args.permission_section = 'accrual';
			form_item_input.setDefaultArgs( default_args );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_accrual_column1, '' );
		}

		// Accrual Policy Account

		if ( this.sub_view_mode ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'accrual_policy_account'} );
			this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_column1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
				show_search_inputs: true,
				set_empty: true,
				field: 'accrual_policy_account_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_column1 );

		}

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_accrual_column1 );

		// Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'amount', width: 150, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_accrual_column1, '', widgetContainer );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'time_stamp'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_accrual_column1, '', widgetContainer );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				field: 'user_id',
				in_column: 1,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Accrual Account' ),
				field: 'accrual_policy_account_id',
				in_column: 1,
				layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 1,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 1,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 2,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

//		switch ( key ) {
//			case 'amount':
//				c_value = this.date_api.parseTimeUnit( target.getValue(), {async: false} ).getResult();
//				break;
//		}

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setEditViewData: function() {

		var $this = this;
		this._super( 'setEditViewData' ); //Set Navigation

		if ( !this.sub_view_mode ) {
			var widget = $this.edit_view_ui_dic['user_id'];
			if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {

				widget.setAllowMultipleSelection( true );

			} else {
				widget.setAllowMultipleSelection( false );
			}
		}

	},

	uniformVariable: function( records ) {

		var record_array = [];
		if ( $.type( records.user_id ) === 'array' ) {

			if ( records.user_id.length === 0 ) {
				records.user_id = false;
				return records;
			}

			for ( var key in records.user_id ) {
				var new_record = Global.clone( records );
				new_record.user_id = records.user_id[key];
				record_array.push( new_record );
			}
		}

		if ( record_array.length > 0 ) {
			records = record_array;
		}

		return records;
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						if ( this.current_edit_record['first_name'] ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
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

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.type_id = true;
		if ( this.sub_view_mode ) {
			column_filter.accrual_policy_account = true;
			column_filter.accrual_policy_account_id = true;
			column_filter.user_id = true;
		}
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

	onGridSelectRow: function() {

		var selected_item = null;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );

			if ( Global.isSet( this.user_type_array[selected_item.type_id] ) ) {
				this.edit_enabled = true;
			} else {
				this.edit_enabled = false;
			}

			if ( Global.isSet( this.delete_type_array[selected_item.type_id] ) ) {
				this.delete_enabled = true;
			} else {
				this.delete_enabled = false;

			}
		}

		this.setDefaultMenu();
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) ) {
			if ( this.edit_enabled ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	setDefaultMenuMassEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 1 ) {
			if ( this.edit_enabled ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			if ( this.delete_enabled ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {

			context_btn.addClass( 'invisible-image' );
		}

		if ( this.edit_enabled && this.editOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
			if ( !this.is_viewing ) {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.delete_enabled && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.delete_enabled && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
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

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
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

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
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

		if ( !this.sub_view_mode ) {
			var other_group = new RibbonSubMenuGroup( {
				label: $.i18n._( 'Other' ),
				id: this.viewId + 'other',
				ribbon_menu: menu,
				sub_menus: []
			} );

			var ttimport = new RibbonSubMenu( {
				label: $.i18n._( 'Import' ),
				id: ContextMenuIconName.import_icon,
				group: other_group,
				icon: Icons.import_icon,
				permission_result: true,
				permission: null
			} );
		}

		return [menu];

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
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
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
			case ContextMenuIconName.timesheet:
				this.onNavigationClick();
				break;

			case ContextMenuIconName.import_icon:
				ProgressBar.showOverlay();
				this.onImportClick();
				break;

		}
	},

	onImportClick: function() {
		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'accrual', function() {
			$this.search();
		} );
	},

//	removeEditView: function() {
//
//		if ( this.edit_view ) {
//			this.edit_view.remove();
//		}
//		this.edit_view = null;
//		this.edit_view_tab = null;
//		this.is_mass_editing = false;
//		this.is_viewing = false;
//		this.is_edit = false;
//		this.is_changed = false;
//		this.mass_edit_record_ids = [];
//		this.edit_view_tab_selected_index = 0;
//		LocalCacheData.current_doing_context_action = '';
//		//If there is a action in url, add it back. So we have correct url when set tabs urls
//		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.a ) {
//			LocalCacheData.current_doing_context_action = LocalCacheData.all_url_args.a;
//		}
//
//		if ( this.current_edit_record ) {
//			this.current_edit_record = null;
//		}
//
//		// reset parent context menu if edit only mode
//		if ( !this.edit_only_mode ) {
//			this.setDefaultMenu();
//			this.initRightClickMenu();
//		} else {
//			this.setParentContextMenuAfterSubViewClose();
//
//		}
//
//		this.reSetURL();
//
//		this.sub_log_view_controller = null;
//		this.edit_view_ui_dic = {};
//		this.edit_view_form_item_dic = {};
//		this.edit_view_error_ui_dic = {};
//	},

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
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.timesheet:
					this.setEditMenuNavViewIcon( context_btn, 'punch' );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	onNavigationClick: function() {

		var $this = this;
		var filter = {filter_data: {}};
		var label = this.sub_view_mode ? $.i18n._( "Accrual Balances" ) : $.i18n._( "Accruals" );

		if ( Global.isSet( this.current_edit_record ) ) {

			filter.user_id = this.current_edit_record.user_id;
			filter.base_date = this.current_edit_record.time_stamp;

			Global.addViewTab( this.viewId, label, window.location.href );
			IndexViewController.goToView( 'TimeSheet', filter );

		} else {
			var accrual_filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];

				accrual_filter.filter_data = {};
				accrual_filter.filter_data.id = [selectedId];

				this.api['get' + this.api.key_name]( accrual_filter, {
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !result_data ) {
							result_data = [];
						}

						result_data = result_data[0];

						filter.user_id = result_data.user_id;
						filter.base_date = result_data.time_stamp;

						Global.addViewTab( $this.viewId, label, window.location.href );
						IndexViewController.goToView( 'TimeSheet', filter );

					}
				} );
			}

		}

	},

	getSubViewFilter: function( filter ) {
		if ( this.parent_edit_record && this.parent_edit_record.user_id && this.parent_edit_record.accrual_policy_account_id ) {
			filter.user_id = this.parent_edit_record.user_id;
			filter.accrual_policy_account_id = this.parent_edit_record.accrual_policy_account_id;
		}
		return filter;
	},

	onAddResult: function( result ) {

		if ( Global.isSet( this.is_trigger_add ) && this.is_trigger_add ) {
			this.is_trigger_add = false;
		}

		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode ) {
			result_data['user_id'] = $this.parent_edit_record['user_id'];
			result_data['first_name'] = $this.parent_edit_record['first_name'];
			result_data['last_name'] = $this.parent_edit_record['last_name'];
			result_data['accrual_policy_account_id'] = $this.parent_edit_record['accrual_policy_account_id'];
			result_data['accrual_policy_account'] = $this.parent_edit_record['accrual_policy_account'];
		}

		$this.current_edit_record = result_data;

		$this.initEditView();
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

		if ( this.refresh_id > 0 && !this.sub_view_mode ) {
			filter.filter_data = {};
			filter.filter_data.id = this.refresh_id;
		} else {
			this.last_select_ids = this.getGridSelectIdArray();
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
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

				if ( $this.refresh_id > 0 && !$this.sub_view_mode ) {
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

						if ( record.id === new_record.id ) {
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

				$this.sub_view_grid_data = result_data[0];
				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				if ( Global.isSet( $this.is_trigger_add ) && $this.is_trigger_add ) {
					$this.onAddClick();
				}
				$this.searchDone();

			}
		} );

	},

	searchDone: function() {
		var result_data = this.grid.getGridParam( 'data' );
		this._super( 'searchDone' );
		if ( this.sub_view_mode ) {
			if ( !Global.isArray( result_data ) || result_data.length < 1 ) {
				this.onCancelClick();
				if ( this.parent_view_controller ) {
					this.parent_view_controller.search();
				}
			}
		}
	},

	setTabStatus: function() {

		//Handle most cases that one tab and on audit tab
		if ( this.is_mass_editing || this.sub_view_mode ) {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else if ( !this.sub_view_mode ) {
			if ( this.subAuditValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}
		}

		this.editFieldResize( 0 );
	}

} );

AccrualViewController.loadView = function() {

	Global.loadViewSource( 'Accrual', 'AccrualView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};

AccrualViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'Accrual', 'SubAccrualView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_accrual_view_controller );
			}
		}
	} )
}