ROEViewController = BaseViewController.extend( {
	el: '#roe_view_container', //Must set el here and can only set string, so events can work
	user_api: null,
	company_api: null,
	pay_period_schedule_api: null,
	code_array: null,
	type_array: null,

	user_generic_data_api: null,

	form_setup_item: null,

	initialize: function() {

		this._super( 'initialize' );
		this.permission_id = 'roe';
		this.viewId = 'ROE';
		this.edit_view_tpl = 'ROEEditView.html';
		this.script_name = 'ROEView';
		this.table_name_key = 'roe';
		this.context_menu_name = $.i18n._( 'Record Of Employment' );
		this.navigation_label = $.i18n._( 'Record Of Employment' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIROE' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.pay_period_schedule_api = new (APIFactory.getAPIClass( 'APIPayPeriodSchedule' ))();
		this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();

		this.render();
		this.buildContextMenu();
		this.initData();

		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'code' );

		this.initDropDownOption( 'type', 'pay_period_type_id', this.pay_period_schedule_api, function( res ) {
			var result = res.getResult();
			$this['type_array'] = Global.buildRecordArray( result );
			$this['type_array'].shift();
		} );

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
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.user_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
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

		var form_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Form' ),
			id: this.viewId + 'Form',
			ribbon_menu: menu,
			sub_menus: []
		} );

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

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: form_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var print = new RibbonSubMenu( {
			label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print,
			group: form_group,
			icon: Icons.print,
			permission_result: true,
			permission: null
		} );

		var efile = new RibbonSubMenu( {
			label: $.i18n._( 'eFile' ),
			id: ContextMenuIconName.e_file,
			group: form_group,
			icon: Icons.e_file,
			permission_result: true,
			permission: null
		} );

		var save_setup = new RibbonSubMenu( {
			label: $.i18n._( 'Save Setup' ),
			id: ContextMenuIconName.save_setup,
			group: form_group,
			icon: Icons.save_setup,
			permission_result: true,
			permission: null
		} );

		var pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Pay Stubs' ),
			id: ContextMenuIconName.pay_stub,
			group: navigation_group,
			icon: Icons.pay_stubs,
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
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

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.print:
					this.setDefaultMenuPrintIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.e_file:
					this.setDefaultMenuEfileIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_setup:
					this.setDefaultMenuSaveSetupIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuTimesheetIcon( context_btn, grid_selected_length );

			}

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenuPrintIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEfileIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuSaveSetupIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuPayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuTimesheetIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
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
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.print:
					this.setEditMenuPrintIcon( context_btn );
					break;
				case ContextMenuIconName.e_file:
					this.setEditMenuEfileIcon( context_btn );
					break;
				case ContextMenuIconName.save_setup:
					this.setEditMenuSaveSetupIcon( context_btn );
					break;
				case ContextMenuIconName.pay_stub:
					this.setEditMenuPayStubIcon( context_btn );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuEditEmployeeIcon( context_btn );
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuTimeSheetIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuPrintIcon: function( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuEfileIcon: function( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveSetupIcon: function( context_btn, pId ) {

//		if ( !this.current_edit_record || !this.current_edit_record.id ) {
//			context_btn.addClass( 'disable-image' );
//		}
	},

	setEditMenuPayStubIcon: function( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuEditEmployeeIcon: function( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuTimeSheetIcon: function( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		var default_args = {permission_section: 'roe'};
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Reason' ),
				in_column: 1,
				field: 'code_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Pay Period Type' ),
				in_column: 1,
				field: 'pay_period_type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Comments' ),
				field: 'comments',
				basic_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'First Name' ),
				in_column: 2,
				field: 'first_name',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				in_column: 2,
				field: 'last_name',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		var $this = this;

		if ( !this.form_setup_item ) {
			this.initFormSetup( function() {
				$this._super( 'search', set_default_menu, page_action, page_number, callBack );
			} );
		} else {
			$this._super( 'search', set_default_menu, page_action, page_number, callBack );
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

		this.setFormSetupData();
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_roe': $.i18n._( 'ROE' ),
			'tab_form_setup': $.i18n._( 'Form Setup' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIROE' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ROE,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_roe = this.edit_view_tab.find( '#tab_roe' );
		var tab_form_setup = this.edit_view_tab.find( '#tab_form_setup' );

		var tab_roe_column1 = tab_roe.find( '.first-column' );
		var tab_form_setup_column1 = tab_form_setup.find( '.first-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[0].push( tab_roe_column1 );
		this.edit_view_tabs[1].push( tab_form_setup_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			field: 'user_id',
			show_search_inputs: true,
			set_empty: true
		} );

		var default_args = {};
		default_args.permission_section = 'roe';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_roe_column1, '' );

		// Reason
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'code_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.code_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Reason' ), form_item_input, tab_roe_column1 );

		// Pay Period Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'pay_period_type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Type' ), form_item_input, tab_roe_column1 );

		// First Day Worked

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'first_date'} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + '(' + $.i18n._( 'Or first day since last ROE' ) + ')' + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'First Day Worked' ), form_item_input, tab_roe_column1, '', widgetContainer );

		// Last Day For Which Paid
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'last_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Last Day For Which Paid' ), form_item_input, tab_roe_column1 );

		//Final Pay Period Ending Date

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'pay_period_end_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Final Pay Period Ending Date' ), form_item_input, tab_roe_column1 );

		// Expected Date of Recall

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'recall_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Expected Date of Recall' ), form_item_input, tab_roe_column1 )

		// Serial No

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'serial', width: 100} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + '(' + $.i18n._( 'Optional' ) + ')' + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Serial No' ), form_item_input, tab_roe_column1, '', widgetContainer );

		// Comments
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'comments', width: 400} );
		this.addEditFieldToColumn( $.i18n._( 'Comments' ), form_item_input, tab_roe_column1 );

		// Release All Accruals
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'release_accruals'} );
		this.addEditFieldToColumn( $.i18n._( 'Release All Accruals' ), form_item_input, tab_roe_column1 );

		// Generate Final Pay Stub
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'generate_pay_stub'} );
		this.addEditFieldToColumn( $.i18n._( 'Generate Final Pay Stub' ), form_item_input, tab_roe_column1, '' );

		// Insurable Absence Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			field: 'absence_policy_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Insurable Absence Policies' ), form_item_input, tab_form_setup_column1, '' );

		var args = {};
		args.filter_data = {};
		args.filter_data.type_id = [10, 30, 40, 80];
		args.filter_data.status_id = 10;

		// Insurable Earnings (Box 15B)
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			field: 'insurable_earnings_psea_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Insurable Earnings (Box 15B)' ), form_item_input, tab_form_setup_column1 );

		// Vacation Pay (Box 17A)

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			field: 'vacation_psea_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Vacation Pay (Box 17A)' ), form_item_input, tab_form_setup_column1, '' );

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
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.view:
			case ContextMenuIconName.print:
			case ContextMenuIconName.e_file:
				this.onNavigationClick( id );
				break;
			case ContextMenuIconName.save_setup:
				this.onSaveSetup();

		}

	},

	initFormSetup: function( callBack ) {
		var args = {};
		var $this = this;
		args.filter_data = {};
		args.filter_data.script = "roe";
		args.filter_data.user_id = "0";
		args.filter_data.is_default = true;

		this.user_generic_data_api.getUserGenericData( args, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( result_data && result_data.length > 0 ) {
					$this.form_setup_item = result_data[0];
				} else {
					$this.form_setup_item = {};
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );
	},

	setFormSetupData: function() {
		if ( this.form_setup_item.data ) {
			this.edit_view_ui_dic.absence_policy_ids.setValue( this.form_setup_item.data.absence_policy_ids );
			this.edit_view_ui_dic.insurable_earnings_psea_ids.setValue( this.form_setup_item.data.insurable_earnings_psea_ids );
			this.edit_view_ui_dic.vacation_psea_ids.setValue( this.form_setup_item.data.vacation_psea_ids );
		}
	},

	getFormSetupData: function( form_item ) {

		//Error: TypeError: form_item is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-091433 line 2 > eval line 1015
		if ( !form_item ) {
			form_item = {};
		}

		form_item.form = {};

		form_item.form.absence_policy_ids = this.edit_view_ui_dic.absence_policy_ids.getValue();
		form_item.form.insurable_earnings_psea_ids = this.edit_view_ui_dic.insurable_earnings_psea_ids.getValue();
		form_item.form.vacation_psea_ids = this.edit_view_ui_dic.vacation_psea_ids.getValue();

		return form_item;
	},

	onSaveSetup: function() {
		var $this = this;
		var form_setup = this.form_setup_item;

		form_setup.user_id = '0';
		form_setup.is_default = true;

		if ( !form_setup.id ) {
			form_setup.script = 'roe';
			form_setup.name = 'form';
		}

		form_setup.data = this.getFormSetupData( {} ).form;

		this.user_generic_data_api.setUserGenericData( form_setup, {
			onResult: function( result ) {

				if ( result.isValid() ) {
					TAlertManager.showAlert( $.i18n._( 'Form setup has been saved successfully' ) );
				} else {
					TAlertManager.showAlert( $.i18n._( 'Form setup save failed, Please try again' ) );
				}

			}
		} );

	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.last_date;
			ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.last_date;
				ids.push( grid_selected_row.id );
			} );
		}

		var args = {roe_id: ids};

		if ( !$this.edit_view ) {
			if ( this.form_setup_item.data ) {
				args.form = this.form_setup_item.data;
			}
		} else {
			args.form = this.getFormSetupData( this.current_edit_record ).form;
		}

		var post_data;

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case ContextMenuIconName.pay_stub:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, 'Record of Employment', window.location.href );
					IndexViewController.goToView( 'PayStub', filter );

				}
				break;
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, 'Record of Employment', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );

				}
				break;
			case ContextMenuIconName.view:
				post_data = {0: args, 1: 'pdf_form'};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.print:
				post_data = {0: args, 1: 'pdf_form_print'};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.e_file:
				post_data = {0: args, 1: 'efile_xml'};
				this.doFormIFrameCall( post_data );
				break;

		}

	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=APIROEReport' + '&Method=getROEReport' );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		this.sendFormIFrameCall( postData, url, message_id );

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

			$this.removeEditView();

			$this.onSaveDone( result );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveDone: function( result ) {
		var user_ids = this.current_edit_record.user_id;
		var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );

		this.current_edit_record = null;

		if ( user_generic_status_batch_id && user_generic_status_batch_id > 0 ) {
			UserGenericStatusWindowController.open( user_generic_status_batch_id, user_ids );
		}
	},

	initTabData: function() {

		//Handle most case that one tab and one audit tab
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {
			if ( this.current_edit_record.id ) {
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

		//Handle most cases that one tab and on audit tab
		if ( this.edit_view_tab_selected_index === 2 ) {

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

	onFormItemChange: function( target, doNotValidate ) {

		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		var c_value = target.getValue();

		switch ( key ) {
			case 'user_id':
				this.api['get' + this.api.key_name + 'DefaultData']( c_value, {
					onResult: function( res ) {
						var result = res.getResult();
						$this.edit_view_ui_dic['first_date'].setValue( result.first_date );
						$this.edit_view_ui_dic['last_date'].setValue( result.last_date );
						$this.edit_view_ui_dic['pay_period_end_date'].setValue( result.pay_period_end_date );
						$this.edit_view_ui_dic['pay_period_type_id'].setValue( result.pay_period_type_id );

						$this.current_edit_record.first_date = result.first_date;
						$this.current_edit_record.last_date = result.last_date;
						$this.current_edit_record.pay_period_end_date = result.pay_period_end_date;
						$this.current_edit_record.pay_period_type_id = result.pay_period_type_id;

						$this.current_edit_record[key] = c_value;
					}
				} );
				break;
			default:
				this.current_edit_record[key] = c_value;
				if ( !doNotValidate ) {
					this.validate();
				}
				break;
		}

	},

	uniformVariable: function( records ) {

		records.form = this.getFormSetupData( records ).form;

		return records;
	}

} );