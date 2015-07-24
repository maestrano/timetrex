PayStubAmendmentViewController = BaseViewController.extend( {
	el: '#pay_stub_amendment_view_container',
	user_status_array: null,
	filtered_status_array: null,
	type_array: null,

	user_api: null,
	user_group_api: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PayStubAmendmentEditView.html';
		this.permission_id = 'pay_stub_amendment';
		this.viewId = 'PayStubAmendment';
		this.script_name = 'PayStubAmendmentView';
		this.table_name_key = 'pay_stub_amendment';
		this.context_menu_name = $.i18n._( 'Pay Stub Amendment' );
		this.navigation_label = $.i18n._( 'Pay Stub Amendment' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubAmendment' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayStubAmendment' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status', 'user_status_id', this.user_api );
		this.initDropDownOption( 'filtered_status', 'status_id' );
		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();

			res = Global.buildTreeRecord( res );
			$this.user_group_array = res;

			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}} );
	},

	getFilterColumnsFromDisplayColumns: function() {

		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.effective_date = true;

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

	onReportPrintClick: function( key ) {
		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		var pay_period_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		var args = {filter_data: {id: ids}};
		var post_data = {0: args, 1: true, 2: key};

		this.doFormIFrameCall( post_data );

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

		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
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

		var print_checks = new RibbonSubMenu( {
			label: $.i18n._( 'Print Checks' ),
			id: ContextMenuIconName.print_checks,
			group: other_group,
			icon: 'print_checks-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var export_cheque_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_cheque', {async: false} ).getResult();

		export_cheque_result = Global.buildRecordArray( export_cheque_result );

		for ( var i = 0; i < export_cheque_result.length; i++ ) {
			var item = export_cheque_result[i];
			var btn = new RibbonSubMenuNavItem( {label: item.label,
				id: item.value,
				nav: print_checks
			} );
		}

		var direct_deposit = new RibbonSubMenu( {
			label: $.i18n._( 'Direct Deposit' ),
			id: ContextMenuIconName.direct_deposit,
			group: other_group,
			icon: 'direct_deposit-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var direct_deposit_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_eft', {async: false} ).getResult();

		direct_deposit_result = Global.buildRecordArray( direct_deposit_result );

		for ( i = 0; i < direct_deposit_result.length; i++ ) {
			item = direct_deposit_result[i];
			btn = new RibbonSubMenuNavItem( {label: item.label,
				id: item.value,
				nav: direct_deposit
			} );
		}

		var import_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Import' ),
			id: ContextMenuIconName.import_icon,
			group: other_group,
			icon: Icons.import_icon,
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVPayStubAmendment' ),
			permission: null
		} );

//		var costa_rica_std_form_1 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Costa Rica - Std Form 1' ),
//			id: 'CostaRicaSTDForm1',
//			nav: print_checks
//		} );
//
//		var costa_rica_std_form_2 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Costa Rica - Std Form 2' ),
//			id: 'CostaRicaSTDForm2',
//			nav: print_checks
//		} );

//		var nebs_9085 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'NEBS #9085' ),
//			id: 'NEBS9085',
//			nav: print_checks
//		} );
//
//		var nebs_9209p = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'NEBS #9209P' ),
//			id: 'NEBS9209P',
//			nav: print_checks
//		} );
//
//		var nebs_dlt103 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'NEBS #DLT103' ),
//			id: 'NEBSDLT103',
//			nav: print_checks
//		} );
//
//		var nebs_dlt104 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'NEBS #DLT104' ),
//			id: 'NEBSDLT104',
//			nav: print_checks
//		} );
//
//		var beanstream = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Beanstream (CSV)' ),
//			id: 'Beanstream',
//			nav: direct_deposit
//		} );
//
//		var canada_eft_105 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Canada - EFT (105-Byte)' ),
//			id: 'CanadaEFT105',
//			nav: direct_deposit
//		} );
//
//		var canada_eft_1464 = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Canada - EFT (1464-Byte)' ),
//			id: 'CanadaEFT1464',
//			nav: direct_deposit
//		} );
//
//		var canada_eft_cibc = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Canada - EFT CIBC (1464-Byte)' ),
//			id: 'CanadaEFTCIBC',
//			nav: direct_deposit
//		} );
//
//		var canada_hsbc_eft_pc = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'Canada - HSBC EFT-PC (CSV)' ),
//			id: 'CanadaHSBCEFTPC',
//			nav: direct_deposit
//		} );
//
//		var united_states_ach = new RibbonSubMenuNavItem( {
//			label: $.i18n._( 'United States - ACH (94-Byte)' ),
//			id: 'UnitedStatesACH',
//			nav: direct_deposit
//		} );

		return [menu];

	},
	/* jshint ignore:start */
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
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.print_checks:
					this.setDefaultMenuPrintChecksIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.direct_deposit:
					this.setDefaultMenuDirectDepositIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},
	/* jshint ignore:end */
	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {

		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub' ) {
			this._super( 'setDefaultMenuViewIcon', context_btn, grid_selected_length, pId );
		} else {

			if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
				context_btn.addClass( 'invisible-image' );
			}

			if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}

		}

	},

	setDefaultMenuPrintChecksIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDirectDepositIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},
	/* jshint ignore:start */
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
				case ContextMenuIconName.timesheet:
					this.setEditMenuViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.pay_stub:
					this.setEditMenuViewIcon( context_btn, 'pay_stub' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuViewIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					this.setEditMenuViewIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},
	/* jshint ignore:end */
	setEditMenuViewIcon: function( context_btn, pId ) {

		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'type_id' ) {
			this.onTypeChange();
		}

		if ( key === 'rate' || key === 'units' || key === 'amount' ) {
			if ( this.is_mass_editing ) {
				if ( target.isChecked() ) {
					this.edit_view_ui_dic['rate'].setCheckBox( true );
					this.edit_view_ui_dic['units'].setCheckBox( true );
					this.edit_view_ui_dic['amount'].setCheckBox( true );
				} else {
					this.edit_view_ui_dic['rate'].setCheckBox( false );
					this.edit_view_ui_dic['units'].setCheckBox( false );
					this.edit_view_ui_dic['amount'].setCheckBox( false );
				}
			}
			this.current_edit_record['amount'] = this.edit_view_ui_dic['amount'].getValue();
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onTypeChange: function() {
		if ( this.current_edit_record.type_id === 10 ) {
			this.edit_view_form_item_dic['percent_amount'].css( 'display', 'none' );
			this.edit_view_form_item_dic['percent_amount_entry_name_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['rate'].css( 'display', 'block' );
			this.edit_view_form_item_dic['units'].css( 'display', 'block' );
			this.edit_view_form_item_dic['amount'].css( 'display', 'block' );

		} else if ( this.current_edit_record.type_id === 20 ) {
			this.edit_view_form_item_dic['percent_amount'].css( 'display', 'block' );
			this.edit_view_form_item_dic['percent_amount_entry_name_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['rate'].css( 'display', 'none' );
			this.edit_view_form_item_dic['units'].css( 'display', 'none' );
			this.edit_view_form_item_dic['amount'].css( 'display', 'none' );
		}

		this.editFieldResize();
	},

	onFormItemKeyUp: function( target ) {
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		var widget_amount = this.edit_view_ui_dic['amount'];

		if ( target.getValue().length === 0 ) {
			widget_amount.setReadOnly( false );
		}
		if ( widget_rate.getValue().length > 0 || widget_units.getValue().length > 0 ) {
			widget_amount.setReadOnly( true );
		}

		if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {
			widget_amount.setValue( ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ).toFixed( 2 ) );
		} else {
			widget_amount.setValue( '0.00' );
		}

	},
	/* jshint ignore:start */
	onFormItemKeyDown: function( target ) {
		var widget = this.edit_view_ui_dic['amount'];
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {

		} else {
			widget.setValue( '0.00' );
		}

		widget.setReadOnly( true );
	},
	/* jshint ignore:end */
	onContextMenuClick: function( context_btn, menu_name ) {

		this._super( 'onContextMenuClick', context_btn, menu_name );

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
			case ContextMenuIconName.import_icon:
				ProgressBar.showOverlay();
				this.onImportClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.edit_employee:
				this.onNavigationClick( id );
				break;

		}
	},

	onImportClick: function() {

		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'paystubamendment', function() {
			$this.search();
		} );
	},
	/* jshint ignore:start */
	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.effective_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.effective_date;
			} );
		}

		var args = {filter_data: {id: ids}};

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, 'Pay Stub Amendments', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.pay_stub:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, 'Pay Stub Amendments', window.location.href );
					IndexViewController.goToView( 'PayStub', filter );
				}
				break;
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
//			case 'CostaRicaSTDForm1':
//				post_data = {0: args, 1: true, 2: '-2050-cheque_cr_standard_form_1'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'CostaRicaSTDForm2':
//				post_data = {0: args, 1: true, 2: '-2060-cheque_cr_standard_form_2'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'NEBS9085':
//				post_data = {0: args, 1: true, 2: '-2010-cheque_9085'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'NEBS9209P':
//				post_data = {0: args, 1: true, 2: '-2020-cheque_9209p'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'NEBSDLT103':
//				post_data = {0: args, 1: true, 2: '-2030-cheque_dlt103'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'NEBSDLT104':
//				post_data = {0: args, 1: true, 2: '-2040-cheque_dlt104'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'Beanstream':
//				post_data = {0: args, 1: true, 2: '-1050-eft_BEANSTREAM'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'CanadaEFT105':
//				post_data = {0: args, 1: true, 2: '-1030-eft_105'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'CanadaEFT1464':
//				post_data = {0: args, 1: true, 2: '-1020-eft_1464'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'CanadaEFTCIBC':
//				post_data = {0: args, 1: true, 2: '-1022-eft_1464_cibc'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'CanadaHSBCEFTPC':
//				post_data = {0: args, 1: true, 2: '-1040-eft_HSBC'};
//				this.doFormIFrameCall( post_data );
//				break;
//			case 'UnitedStatesACH':
//				post_data = {0: args, 1: true, 2: '-1010-eft_ACH'};
//				this.doFormIFrameCall( post_data );
//				break;

		}

	},
	/* jshint ignore:end */
	onReportMenuClick: function( id ) {
		this.onReportPrintClick( id );
	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=' + this.api.className + '&Method=' + 'get' + this.api.key_name );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		this.sendFormIFrameCall( postData, url, message_id );

	},

	setCurrentEditRecordData: function() {


		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'type_id': 10
		} );

		//Set current edit record data to all widgets
		var widget;
		for ( var key in this.current_edit_record ) {
			widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( this.current_edit_record.rate || this.current_edit_record.units ) {
			widget = this.edit_view_ui_dic['amount'];
			widget.setReadOnly( true );
		}

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onTypeChange();
	},

	openEditView: function() {
		this.initEditViewUI( this.viewId, this.edit_view_tpl );

		this.setEditViewWidgetsMode();
	},

//	onCopyAsNewClick: function() {
//		var $this = this;
//		this.is_add = true;
//		LocalCacheData.current_doing_context_action = 'copy_as_new';
//
//		var selectedId;
//
//		if ( Global.isSet( this.edit_view ) ) {
//			this.current_edit_record.id = '';
//			var navigation_div = this.edit_view.find( '.navigation-div' );
//			navigation_div.css( 'display', 'none' );
//			this.setEditMenu();
//			this.setTabStatus();
//			ProgressBar.closeOverlay();
//
//		} else {
//
//			var filter = {};
//			var grid_selected_id_array = this.getGridSelectIdArray();
//			var grid_selected_length = grid_selected_id_array.length;
//
//			if ( grid_selected_length > 0 ) {
//				selectedId = grid_selected_id_array[0];
//			} else {
//				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
//				return;
//			}
//
//			filter.filter_data = {};
//			filter.filter_data.id = [selectedId];
//
//			this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
//
//				var result_data = result.getResult();
//
//				if ( !result_data ) {
//					result_data = [];
//				}
//
//				result_data = result_data[0];
//
//				result_data.id = '';
//
//				$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.
//
//				if ( $this.sub_view_mode && $this.parent_key ) {
//					result_data[$this.parent_key] = $this.parent_value;
//				}
//
//				$this.current_edit_record = result_data;
//				$this.initEditView();
//
//			}} );
//
//		}
//
//	},

	validate: function() {

		var $this = this;

		var record = {};

		var records_data = null;

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
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
		if ( $.isArray( record.user_id ) ) {

			records_data = [];
			var length = record.user_id.length;
			// if the length > 0, loop it.
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
			} else {
				// if the length is 0, set it to string
				record.user_id = record.user_id.toString();
			}

		}

		if ( Global.isSet( records_data ) && records_data.length > 0 ) {
			record = records_data;
		}

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onSaveAndContinue: function() {
		var $this = this;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		this.current_edit_record.user_id = this.current_edit_record.user_id[0];
		this.api['set' + this.api.key_name]( this.current_edit_record, {onResult: function( result ) {
			$this.onSaveAndContinueResult( result );

		}} );
	},

	onSaveClick: function() {
		var $this = this;
		var record;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';

		var records_data = null;

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
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
		}

		if ( $.isArray( record.user_id ) ) {
			records_data = [];
			var length = record.user_id.length;
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
			} else {
				record.user_id = record.user_id.toString();
			}

		}

		if ( Global.isSet( records_data ) && records_data.length > 0 ) {
			record = records_data;
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
			if ( result.isValid() ) {
				var result_data = result.getResult();
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( result_data > 0 ) {
					$this.refresh_id = result_data;
				}
				$this.search();
				$this.onSaveDone( result );
				$this.current_edit_record = null;
				$this.removeEditView();

			} else {
				$this.setErrorTips( result );
				$this.setErrorMenu();
			}

		}} );
	},

	onSaveAndCopy: function() {
		var $this = this;
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var records_data = null;
		this.clearNavigationData();

		if ( $.isArray( record.user_id ) ) {
			records_data = [];
			var length = record.user_id.length;
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
			} else {
				record.user_id = record.user_id.toString();
			}

		}

		if ( Global.isSet( records_data ) && records_data.length > 0 ) {
			record = records_data;
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
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

		}} );
	},

	onSaveAndNewClick: function() {
		var $this = this;
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';

		var records_data = null;

		if ( $.isArray( record.user_id ) ) {
			records_data = [];
			var length = record.user_id.length;
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
			} else {
				record.user_id = record.user_id.toString();
			}

		}

		if ( Global.isSet( records_data ) && records_data.length > 0 ) {
			record = records_data;
		}
		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
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

		}} );
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || (Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;
		var allow_multiple_selection = false;

		this.setTabLabels( {
			'tab_pay_stub_amendment': $.i18n._( 'Pay Stub Amendment' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubAmendment' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_AMENDMENT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub_amendment = this.edit_view_tab.find( '#tab_pay_stub_amendment' );

		var tab_pay_stub_amendment_column1 = tab_pay_stub_amendment.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_amendment_column1 );

		if ( this.is_add ) {
			allow_multiple_selection = true;
		}

		//Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: allow_multiple_selection,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_pay_stub_amendment_column1, '' );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.filtered_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_amendment_column1 );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [10, 20, 30, 50, 60, 65, 80];
		args.filter_data = filter_data;

		// Pay Stub Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_name_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Amount Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Amount Type' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Fixed

		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'rate', width: 114, hasKeyEvent: true} );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true, null, null, true );

		// Units
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'units', width: 114, hasKeyEvent: true} );
		this.addEditFieldToColumn( $.i18n._( 'Units' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true, null, null, true );

		// Amount

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'amount', width: 114} );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Percent

		//Percent
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'percent_amount', width: 79} );
		this.addEditFieldToColumn( $.i18n._( 'Percent' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		args = {};
		filter_data = {};
		filter_data.type_id = [10, 20, 30, 40, 50, 60, 65];
		args.filter_data = filter_data;

		// Percent of
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'percent_amount_entry_name_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Percent of' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'description', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_pay_stub_amendment_column1 );

		form_item_input.parent().width( '45%' );
		// Description (Private)

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'private_description'} );
		this.addEditFieldToColumn( $.i18n._( 'Description (Private)' ), form_item_input, tab_pay_stub_amendment_column1, '', null, null, true );

		// Effective Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'effective_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Effective Date' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Year to Date (YTD) Adjustment -- DISABLED
		//form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		//form_item_input.TCheckbox( {field: 'ytd_adjustment'} );
		//this.addEditFieldToColumn( $.i18n._( 'Year to Date (YTD) Adjustment' ), form_item_input, tab_pay_stub_amendment_column1, '' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';

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
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
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

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	}


} );

PayStubAmendmentViewController.loadView = function() {

	Global.loadViewSource( 'PayStubAmendment', 'PayStubAmendmentView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
