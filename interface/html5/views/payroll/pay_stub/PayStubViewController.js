PayStubViewController = BaseViewController.extend( {
	el: '#pay_stub_view_container',
	filtered_status_array: null,
	user_status_array: null,
	user_group_array: null,

	country_array: null,
	province_array: null,

	e_province_array: null,

	user_api: null,
	user_group_api: null,
	company_api: null,

	pay_stub_entry_api: null,

	include_entries: true,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PayStubEditView.html';
		this.permission_id = 'pay_stub';
		this.viewId = 'PayStub';
		this.script_name = 'PayStubView';
		this.table_name_key = 'pay_stub';
		this.context_menu_name = $.i18n._( 'Pay Stub' );
		this.navigation_label = $.i18n._( 'Pay Stubs' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.pay_stub_entry_api = new (APIFactory.getAPIClass( 'APIPayStubEntry' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true; //Hide some context menus

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayStub' );

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

		this.initDropDownOption( 'filtered_status', 'status_id' );
		this.initDropDownOption( 'status', 'user_status_id', this.user_api );
		this.initDropDownOption( 'country', 'country', this.company_api );
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

		var pay_stubs_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Pay Stubs' ),
			id: this.script_name + 'Pay Stubs',
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

		var schedule = new RibbonSubMenu( {
			label: $.i18n._( 'Schedule' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var pay_stub_amendments = new RibbonSubMenu( {
			label: $.i18n._( 'Pay Stub<br>Amendments' ),
			id: ContextMenuIconName.pay_stub_amendment,
			group: navigation_group,
			icon: Icons.pay_stub_amendment,
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

		var employee_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employee Pay<br>Stubs' ),
			id: ContextMenuIconName.employee_pay_stubs,
			group: pay_stubs_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var employer_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employer Pay<br>Stubs' ),
			id: ContextMenuIconName.employer_pay_stubs,
			group: pay_stubs_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var generate_pay_stub = new RibbonSubMenu( {
			label: $.i18n._( 'Generate<br>Pay Stub' ),
			id: ContextMenuIconName.generate_pay_stub,
			group: other_group,
			icon: Icons.process_payroll,
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

		var export_cheque_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_cheque', {async: false} );

		//Error: Uncaught TypeError: Cannot read property 'getResult' of undefined in https://ondemand1.timetrex.com/interface/html5/#!m=PayStub line 317
		if ( export_cheque_result ) {
			export_cheque_result = export_cheque_result.getResult();
			export_cheque_result = Global.buildRecordArray( export_cheque_result );
		} else {
			export_cheque_result = [];
		}

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
				case ContextMenuIconName.view:
					//View icon should be displayed separate from Employee Pay Stub/Employer Pay Stub icons.
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.pay_stub_amendment:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.employee_pay_stubs:
				case ContextMenuIconName.employer_pay_stubs:
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					this.setDefaultMenuReportRelatedIcons( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length );
					break;
			}
		}

		this.setContextMenuGroupVisibility();
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

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub_amendment' ) {
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
				case ContextMenuIconName.timesheet:
					this.setEditMenuViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuViewIcon( context_btn, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setEditMenuViewIcon( context_btn, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuViewIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.view:
				case ContextMenuIconName.employer_pay_stubs:
				case ContextMenuIconName.employee_pay_stubs:
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					this.setEditMenuReportRelatedIcons( context_btn );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setEditMenuGeneratePayStubIcon( context_btn );
					break;
			}
		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	payStubReportIconsValidate: function() {
		if ( !PermissionManager.validate( 'pay_stub', 'enabled' ) ) {
			return false;
		}

		var selected_item = this.getSelectedItem();

		if ( PermissionManager.validate( 'pay_stub', 'view' ) || this.ownerOrChildPermissionValidate( 'pay_stub', 'view_child', selected_item ) ) {
			return true;
		}

		return false;
	},

	setDefaultMenuReportRelatedIcons: function( context_btn, grid_selected_length, pId ) {
		if ( !this.payStubReportIconsValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuReportRelatedIcons: function( context_btn, grid_selected_length, pId ) {
		if ( !this.payStubReportIconsValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.current_edit_record.id && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.endOfPayValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.current_edit_record.id ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	endOfPayValidate: function() {
		if ( PermissionManager.validate( 'pay_period_schedule', 'enabled' ) && PermissionManager.validate( 'pay_period_schedule', 'view' ) ) {
			return true;
		}

		return false;
	},

	setDefaultMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.endOfPayValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	removeInsideEditorCover: function() {
		if ( this.cover && this.cover.length > 0 ) {
			this.cover.remove();
		}
		this.cover = null;

	},

	setCurrentEditRecordData: function() {
		this.include_entries = true;

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.eSetProvince( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'full_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
					case 'status_id':
						if ( this.current_edit_record[key] == 40 ) {
							this.include_entries = false;
						}
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

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.initInsideEditorData();
	},

	initInsideEditorData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			$this.editor.removeAllRows( true );
			$this.pay_stub_entry_api['get' + this.pay_stub_entry_api.key_name + 'DefaultData']( args, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}
				var data = res.getResult();
				$this.editor.setValue( data );

			}} );

		} else {

			args.filter_data.pay_stub_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			$this.pay_stub_entry_api['get' + $this.pay_stub_entry_api.key_name]( args, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}
				var data = res.getResult();
				$this.editor.setValue( data );

			}} );

		}

	},

	insideEditorSetValue: function( val ) {
		var length = _.size( val );
		var pay_stub_status_id = this.parent_controller['current_edit_record']['status_id'];
		var is_add = false;

		if ( !this.parent_controller['current_edit_record']['id'] ) {
			is_add = true;
		}
		this.removeAllRows( true );
		this.removeCover();

		// set value
		if ( length > 0 ) {
			var render = this.getRender(); //get render, should be a table
			for ( var key in val ) {
				var args = null, item = val[key];
				var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnHeader.html' );
				if ( key == '10' ) {
					headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorFiveColumnHeader.html' );
					args = {
						col1: $.i18n._( 'Earnings' ),
						col2: $.i18n._( 'Rate' ),
						col3: $.i18n._( 'Hrs/Units' ),
						col4: $.i18n._( 'Amount' ),
						col5: $.i18n._( 'YTD Amount' )
					};
				} else if ( key == '20' ) {
					args = {
						col1: $.i18n._( 'Deductions' ),
						col2: $.i18n._( 'Amount' ),
						col3: $.i18n._( 'YTD Amount' )
					};
				} else if ( key == '30' ) {
					args = {
						col1: $.i18n._( 'Employer Contributions' ),
						col2: $.i18n._( 'Amount' ),
						col3: $.i18n._( 'YTD Amount' )
					};
				} else if ( key == '50' ) {
					args = {
						col1: $.i18n._( 'Accrual' ),
						col2: $.i18n._( 'Amount' ),
						col3: $.i18n._( 'Balance' )
					};
				}

				if ( args ) {
					$( render ).append( _.template( headerRow, args ) );
					this.rows_widgets_array.push( true );

					for ( var i = 0; i < item.length; i++ ) {
						if ( Global.isSet( item[i] ) ) {
							var row = item[i];
							this.addRow( row );
						}
					}

					$( render ).append( '<tr><td colspan="7"><br></td></tr>' );
					this.rows_widgets_array.push( true );

					// add net pay total line after total deducation.
					if ( key == '20' && Global.isSet(val['40']) ) {

						var netPay = val['40'];
						for ( var i = 0; i < netPay.length; i++ ) {
							if ( Global.isSet( netPay[i] ) ) {
								var row = netPay[i];
								this.addRow( row );
							}
						}

						$( render ).append( '<tr><td colspan="7"><br></td></tr>' );
						this.rows_widgets_array.push( true );
					}

				}

			}

		}
		// set the cover
		if ( length > 0 && !is_add && pay_stub_status_id == 25 ) {

			this.cover = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
			this.cover.NoResultBox( {
				related_view_controller: this,
				message: $.i18n._( 'Click the Edit icon below to override pay stub amounts' ),
				is_edit: true
			} );

			this.cover.css( {width: this.width(), height: this.height()} );

			this.parent().append( this.cover )

		}

	},

	insideEditorAddRow: function( data, index ) {

		var $this = this;
		var pay_stub_status_id = this.parent_controller['current_edit_record']['status_id'];
		var pay_stub_amendment_id = 0, user_expense_id = 0;
		var is_add = false;

		if ( !this.parent_controller['current_edit_record']['id'] ) {
			is_add = true;
		}

		if ( !data ) {
			$this.addRow( {}, index );
		} else {
			if ( typeof index != 'undefined' ) {
				data['tmp_type'] = data['tmp_type'] ? data['tmp_type'] : this.rows_widgets_array[index].ytd_amount.attr( 'tmp_type' );
			}

			if ( !isNaN( parseFloat( data['pay_stub_amendment_id'] ) ) && parseFloat( data['pay_stub_amendment_id'] ) > 0 ) {
				pay_stub_amendment_id = data['pay_stub_amendment_id']
			}

			if ( !isNaN( parseFloat( data['user_expense_id'] ) ) && parseFloat( data['user_expense_id'] ) > 0 ) {
				user_expense_id = data['user_expense_id'];
			}
			var render = this.getRender(); //get render, should be a table
			var widgets = {}; //Save each row's widgets
			var row; //Get Row render

			var args = { filter_data: {} };

			// Pay Stub Account
			var form_item_name_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_name_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				show_search_inputs: true,
				set_empty: true,
				field: 'pay_stub_entry_name_id'
			} );

			var form_item_name_text = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_name_text.TText( {field: 'name'} );
			form_item_name_text.setValue( data.name ? ( data['type_id'] != 40 ? "  " + data.name : data.name  ) : '' );

			var form_item_rate_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_rate_input.TTextInput( {field: 'rate', width: 60, hasKeyEvent: true} );
			form_item_rate_input.setValue( data.rate );
			form_item_rate_input.attr( 'editable', true );
			form_item_rate_input.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
				$this.onFormItemKeyUp( target );
			} );

			form_item_rate_input.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
				$this.onFormItemKeyDown( target );
			} );

			form_item_rate_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item_rate_text = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_rate_text.TText( {field: 'rate'} );
			form_item_rate_text.setValue( data.rate );

			var form_item_units_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_units_input.TTextInput( {field: 'units', width: 60, hasKeyEvent: true} );
			form_item_units_input.setValue( data.units );
			form_item_units_input.attr( 'editable', true );
			form_item_units_input.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
				$this.onFormItemKeyUp( target );
			} );

			form_item_units_input.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
				$this.onFormItemKeyDown( target );
			} );
			form_item_units_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item_units_text = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_units_text.TText( {field: 'units'} );
			form_item_units_text.setValue( data.units );

			var form_item_amount_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_amount_input.TTextInput( {field: 'amount', width: 60} );
			form_item_amount_input.setValue( data.amount );
			form_item_amount_input.attr( 'editable', true );
			form_item_amount_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item_amount_text = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_amount_text.TText( {field: 'amount'} );
			form_item_amount_text.setValue( data.amount );

//			var form_item_ytd_amount_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
//			form_item_ytd_amount_input.TTextInput( {field: 'ytd_amount', width: 60} );
//			form_item_ytd_amount_input.setValue( data.ytd_amount ? data.ytd_amount : '0.0000' );
//			form_item_ytd_amount_input.attr( 'editable', true );
//			form_item_ytd_amount_input.attr({
//				'pay_stub_entry_id': (data.id && this.parent_controller.current_edit_record.id) ? data.id : '',
//				'tmp_type': data.tmp_type,
//				'type_id': data['type_id'],
//				'pay_stub_amendment_id': data['pay_stub_amendment_id'] ? data['pay_stub_amendment_id'] : '',
//				'user_expense_id': data['user_expense_id'] ? data['user_expense_id'] : ''
//			});

			var form_item_ytd_amount_text = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_ytd_amount_text.TText( {field: 'ytd_amount'} );
			form_item_ytd_amount_text.setValue( data.ytd_amount );
			form_item_ytd_amount_text.attr( {
				'pay_stub_entry_id': (data.id && this.parent_controller.current_edit_record.id) ? data.id : '',
				'tmp_type': data.tmp_type,
				'type_id': data['type_id'],
				'original_amount': data['amount'] ? data['amount'] : '0.00',
				'original_ytd_amount': data['ytd_amount'] ? data['ytd_amount'] : '0.00',
				'pay_stub_entry_name_id': data['pay_stub_entry_name_id'] ? data['pay_stub_entry_name_id'] : null
			} );

			if ( pay_stub_amendment_id > 0 ) {
				form_item_ytd_amount_text.attr( 'pay_stub_amendment_id', pay_stub_amendment_id );
			}

			if ( user_expense_id > 0 ) {
				form_item_ytd_amount_text.attr( 'user_expense_id', user_expense_id );
			}

			if ( parseInt( data['ytd_amount'] ) > 0 ) {

			} else if ( pay_stub_status_id == 40 ) {
				form_item_ytd_amount_text.text( '-' );
			}

			if ( parseInt( data.rate ) > 0 ) {
				form_item_amount_input.setReadOnly( true );
			} else if ( pay_stub_status_id == 40 ) {
				form_item_rate_text.text( '-' );
			}

			if (  parseInt( data.units ) > 0 ) {
				form_item_amount_input.setReadOnly( true );
			} else if ( pay_stub_status_id == 40 ) {
				form_item_units_text.text( '-' );
			}

			if ( data['tmp_type'] == '10' ) {
				row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorFiveColumnRow.html' ) );
				// name
				if ( Global.isSet( index ) || is_add ) {

					if (  data['type_id'] == 40  ) {
						form_item_name_text.css( 'font-weight', 'bold' );
						widgets[form_item_name_text.getField()] = form_item_name_text;
						row.children().eq( 0 ).append( form_item_name_text );

					} else {
						args['filter_data']['type_id'] = [10];
						form_item_name_input.setDefaultArgs( args );
						widgets[form_item_name_input.getField()] = form_item_name_input;
						row.children().eq( 0 ).append( form_item_name_input );
					}

				} else {
					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
					}
					widgets[form_item_name_text.getField()] = form_item_name_text;
					row.children().eq( 0 ).append( form_item_name_text );
				}

				// rate
				if ( data['type_id'] == 10 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
							form_item_rate_input.setReadOnly( true );
						}
						widgets[form_item_rate_input.getField()] = form_item_rate_input;
						row.children().eq( 1 ).append( form_item_rate_input );

					} else {
						widgets[form_item_rate_text.getField()] = form_item_rate_text;
						row.children().eq( 1 ).append( form_item_rate_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add ) {
						if ( data['type_id'] == 40 ) {

						} else {
							widgets[form_item_rate_input.getField()] = form_item_rate_input;
							row.children().eq( 1 ).append( form_item_rate_input );
						}
					}
				}

				// units
				if ( data['type_id'] == 10 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
							form_item_units_input.setReadOnly( true );
						}
						widgets[form_item_units_input.getField()] = form_item_units_input;
						row.children().eq( 2 ).append( form_item_units_input );
					} else {
						widgets[form_item_units_text.getField()] = form_item_units_text;
						row.children().eq( 2 ).append( form_item_units_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add ) {

						if ( data['type_id'] == 40 ) {
							form_item_units_text.css( 'font-weight', 'bold' );
							widgets[form_item_units_text.getField()] = form_item_units_text;
							row.children().eq( 2 ).append( form_item_units_text );
						} else {
							widgets[form_item_units_input.getField()] = form_item_units_input;
							row.children().eq( 2 ).append( form_item_units_input );
						}
					} else {
						form_item_units_text.css( 'font-weight', 'bold' );
						widgets[form_item_units_text.getField()] = form_item_units_text;
						row.children().eq( 2 ).append( form_item_units_text );
					}
				}

				// amount
				if ( data['type_id'] == 10 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
							form_item_amount_input.setReadOnly( true );
						}
						widgets[form_item_amount_input.getField()] = form_item_amount_input;
						row.children().eq( 3 ).append( form_item_amount_input );
					} else {
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 3 ).append( form_item_amount_text );

					}
				} else {
					if ( Global.isSet( index ) || is_add ) {

						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
							widgets[form_item_amount_text.getField()] = form_item_amount_text;
							row.children().eq( 3 ).append( form_item_amount_text );
						} else {
							widgets[form_item_amount_input.getField()] = form_item_amount_input;
							row.children().eq( 3 ).append( form_item_amount_input );
						}

					} else {
						form_item_amount_text.css( 'font-weight', 'bold' );
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 3 ).append( form_item_amount_text );
					}
				}

				// Ytd amount
				if ( data['type_id'] == 40 ) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}
				if ( Global.isSet( index ) || is_add ) {
					form_item_ytd_amount_text.text( '-' );
				}
				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				row.children().eq( 4 ).append( form_item_ytd_amount_text );

			} else if ( data['tmp_type'] == 20 ) {

				row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnRow.html' ) );
				// name

				if ( Global.isSet( index ) || is_add ) {


					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
						widgets[form_item_name_text.getField()] = form_item_name_text;
						row.children().eq( 0 ).append( form_item_name_text );
					} else {
						args['filter_data']['type_id'] = [20];
						form_item_name_input.setDefaultArgs( args );
						widgets[form_item_name_input.getField()] = form_item_name_input;
						row.children().eq( 0 ).append( form_item_name_input );
					}

				} else {
					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
					}
					widgets[form_item_name_text.getField()] = form_item_name_text;
					row.children().eq( 0 ).append( form_item_name_text );

				}

				// amount
				if ( data['type_id'] == 20 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
							form_item_amount_input.setReadOnly( true );
						}

						widgets[form_item_amount_input.getField()] = form_item_amount_input;
						row.children().eq( 1 ).append( form_item_amount_input );
					} else {
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add ) {


						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
							widgets[form_item_amount_text.getField()] = form_item_amount_text;
							row.children().eq( 1 ).append( form_item_amount_text );
						} else {
							widgets[form_item_amount_input.getField()] = form_item_amount_input;
							row.children().eq( 1 ).append( form_item_amount_input );
						}
 					} else {
						form_item_amount_text.css( 'font-weight', 'bold' );
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				}

				// Ytd amount
				if ( data['type_id'] == 40 ) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}
				if ( Global.isSet( index ) || is_add ) {
					form_item_ytd_amount_text.text( '-' );
				}
				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				row.children().eq( 2 ).append( form_item_ytd_amount_text );

			} else if ( data['tmp_type'] == 30 ) {

				row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnRow.html' ) );
				// name

				if ( Global.isSet( index ) || is_add ) {


					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
						widgets[form_item_name_text.getField()] = form_item_name_text;
						row.children().eq( 0 ).append( form_item_name_text );
					} else {
						args['filter_data']['type_id'] = [30];
						form_item_name_input.setDefaultArgs( args );
						widgets[form_item_name_input.getField()] = form_item_name_input;
						row.children().eq( 0 ).append( form_item_name_input );
					}

				} else {
					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
					}
					widgets[form_item_name_text.getField()] = form_item_name_text;
					row.children().eq( 0 ).append( form_item_name_text );

				}

				// amount
				if ( data['type_id'] == 30 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {

							form_item_amount_input.setReadOnly( true )
						}
						widgets[form_item_amount_input.getField()] = form_item_amount_input;
						row.children().eq( 1 ).append( form_item_amount_input );
					} else {
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add ) {


						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
							widgets[form_item_amount_text.getField()] = form_item_amount_text;
							row.children().eq( 1 ).append( form_item_amount_text );
						} else {
							widgets[form_item_amount_input.getField()] = form_item_amount_input;
							row.children().eq( 1 ).append( form_item_amount_input );
						}

					} else {
						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
						}
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				}

				// Ytd amount
				if ( data['type_id'] == 40 ) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}
				if ( Global.isSet( index ) || is_add ) {
					form_item_ytd_amount_text.text( '-' );
				}
				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				row.children().eq( 2 ).append( form_item_ytd_amount_text );

			} else if ( data['tmp_type'] == 40 ) {

				row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnRow.html' ) );
//				row.removeClass('tblDataWhite').addClass( 'tblHeader' );
				// name
				if ( data['type_id'] == 40 ) {
					form_item_name_text.css( 'font-weight', 'bold' );
				}
				widgets[form_item_name_text.getField()] = form_item_name_text;
				row.children().eq( 0 ).append( form_item_name_text );

				// amount
				form_item_amount_text.css( 'font-weight', 'bold' );
				widgets[form_item_amount_text.getField()] = form_item_amount_text;
				row.children().eq( 1 ).append( form_item_amount_text );

				// Ytd amount
				if ( data['type_id'] == 40 ) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}

				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				row.children().eq( 2 ).append( form_item_ytd_amount_text );

			} else if ( data['tmp_type'] == 50 ) {

				row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnRow.html' ) );
				// name

				if ( Global.isSet( index ) || is_add ) {


					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
						widgets[form_item_name_text.getField()] = form_item_name_text;
						row.children().eq( 0 ).append( form_item_name_text );
					} else {
						args['filter_data']['type_id'] = [50];
						form_item_name_input.setDefaultArgs( args );
						widgets[form_item_name_input.getField()] = form_item_name_input;
						row.children().eq( 0 ).append( form_item_name_input );
					}

				} else {
					if ( data['type_id'] == 40 ) {
						form_item_name_text.css( 'font-weight', 'bold' );
					}
					widgets[form_item_name_text.getField()] = form_item_name_text;
					row.children().eq( 0 ).append( form_item_name_text );
				}

				// amount
				if ( data['type_id'] == 50 ) {
					if ( pay_stub_status_id == 25 ) {
						if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
							form_item_amount_input.setReadOnly( true );
						}
						widgets[form_item_amount_input.getField()] = form_item_amount_input;
						row.children().eq( 1 ).append( form_item_amount_input );
					} else {
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add ) {


						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
							widgets[form_item_amount_text.getField()] = form_item_amount_text;
							row.children().eq( 1 ).append( form_item_amount_text );
						} else {
							widgets[form_item_amount_input.getField()] = form_item_amount_input;
							row.children().eq( 1 ).append( form_item_amount_input );
						}

					} else {
						form_item_amount_text.css( 'font-weight', 'bold' );
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						row.children().eq( 1 ).append( form_item_amount_text );
					}
				}

				// Ytd amount
				if ( data['type_id'] == 40 ) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}
				if ( Global.isSet( index ) || is_add ) {
					form_item_ytd_amount_text.text( '-' );
				}
				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				row.children().eq( 2 ).append( form_item_ytd_amount_text );

			}
			//Build row widgets

			if ( pay_stub_amendment_id > 0 || user_expense_id > 0 ) {
				row.children().last().find( '.minus-icon ' ).hide();
			}

			if ( data['type_id'] == 40 ) {

				if ( data['tmp_type'] == 40 ) {
					widgets['net_pay_row'] = true;
				} else {
					widgets['total_row'] = true;
				}
				row.children().last().empty();
			}

			if ( typeof index != 'undefined' ) {
				row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
				this.rows_widgets_array.splice( (index + 1 ), 0, widgets );

			} else {
				$( render ).append( row );
				this.rows_widgets_array.push( widgets );
			}

			if ( pay_stub_status_id == 25 ) {
				this.addIconsEvent( row ); //Bind event to add and minus icon
			} else {
				row.children().last().empty();
			}

		}

	},

	insideEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex;
		var remove_id = this.rows_widgets_array[index].ytd_amount.attr( 'pay_stub_entry_id' );
		var tmp_type = this.rows_widgets_array[index].ytd_amount.attr( 'tmp_type' );
		if ( remove_id > 0 ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		if ( this.rows_widgets_array[index - 1] === true && ( this.rows_widgets_array[index + 1]['total_row'] === true || this.rows_widgets_array[index + 1] === true ) ) {
			this.addRow( {id: '', tmp_type: tmp_type}, index - 1 );
			this.rows_widgets_array.splice( index + 1, 1 );
		} else {

			this.rows_widgets_array.splice( index, 1 );
		}

		this.calcTotal();
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

		if ( this.include_entries ) {
			var entries = $this.saveInsideEditorData();
			if ( entries.length > 0 ) {
				record['entries'] = entries;
			}
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {

			$this.onSaveResult( result );

		}} );
	},

//	onSaveResult: function( result ) {
//		var $this = this;
//		if ( result.isValid() ) {
//			var result_data = result.getResult();
//			if ( result_data === true ) {
//				$this.refresh_id = $this.current_edit_record.id;
//			} else if ( result_data > 0 ) {
//				$this.refresh_id = result_data
//			}
//
//			$this.saveInsideEditorData( function() {
//				$this.search();
//				$this.onSaveDone( result );
//				$this.current_edit_record = null;
//				$this.removeEditView();
//			} );
//
//		} else {
//			$this.setErrorTips( result );
//			$this.setErrorMenu();
//		}
//	},


	onSaveAndContinue: function() {
		var $this = this;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );
		if ( this.include_entries ) {
			var entries = $this.saveInsideEditorData();
			if ( entries.length > 0 ) {
				record['entries'] = entries;
			}
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
			$this.onSaveAndContinueResult( result );

		}} );
	},

//	onSaveAndContinueResult: function( result ) {
//		var $this = this;
//		if ( result.isValid() ) {
//			var result_data = result.getResult();
//			if ( result_data === true ) {
//				$this.refresh_id = $this.current_edit_record.id;
//
//			} else if ( result_data > 0 ) { // as new
//				$this.refresh_id = result_data
//			}
//
//			$this.saveInsideEditorData( function() {
//				$this.search( false );
//				$this.onEditClick( $this.refresh_id, true );
//
//				$this.onSaveAndContinueDone( result );
//
//			} );
//
//		} else {
//			$this.setErrorTips( result );
//			$this.setErrorMenu();
//		}
//	},

	saveInsideEditorData: function( callBack ) {
		var $this = this;
		var data = this.editor.getValue( $this.current_edit_record.id ? $this.current_edit_record.id : '' );

		if ( data.length > 0 ) {

			var remove_ids = $this.editor.delete_ids;

			if ( remove_ids.length > 0 ) {
				this.pay_stub_entry_api.deletePayStubEntry( remove_ids, {onResult: function( res ) {
					$this.editor.delete_ids = [];
				}} )
			}

//			this.pay_stub_entry_api.setPayStubEntry( data, {onResult: function( res ) {
//				var res_data = res.getResult();
//				if ( res_data ) {
//					if ( Global.isSet( callBack ) ) {
//						callBack();
//					}
//				} else {
//					$this.setErrorTips( res );
//					$this.setErrorMenu();
//				}
//
//
//			}} )


		} else {

			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}

		return data;

	},

	insideEditorGetValue: function( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;
		var result = [];

		if ( this.cover && this.cover.length > 0 ) {
			return result;
		}

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = {};

			if ( row === true ) {
				continue;
			}

//			var pay_stub_amendment_id = row['ytd_amount'].attr( 'pay_stub_amendment_id' );
//			var user_expense_id = row['ytd_amount'].attr( 'user_expense_id' );
//
//			if (  pay_stub_amendment_id > 0 || user_expense_id > 0  ) {
//				continue;
//			}

			data['id'] = row['ytd_amount'].attr( 'pay_stub_entry_id' );

			if ( row['ytd_amount'].attr( 'type_id' ) ) {
				data['type'] = row['ytd_amount'].attr( 'type_id' );
			}

			if ( Global.isSet( row['rate'] )
				&& row['rate'].attr( 'editable' ) ) {
				data['rate'] = row['rate'].getValue();
			}
			if ( Global.isSet( row['units'] )
				&& row['units'].attr( 'editable' ) ) {
				data['units'] = row['units'].getValue();
			}
			if ( Global.isSet( row['amount'] )
				&& row['amount'].attr( 'editable' ) ) {
				data['amount'] = row['amount'].getValue();
			}
			if ( Global.isSet( row['ytd_amount'] )
				&& row['ytd_amount'].attr( 'editable' ) ) {
				data['ytd_amount'] = row['ytd_amount'].getValue();
			}

			if ( Global.isSet( row['pay_stub_entry_name_id'] ) ) {
				data['pay_stub_entry_name_id'] = row['pay_stub_entry_name_id'].getValue();
			} else {
				data['pay_stub_entry_name_id'] = row['ytd_amount'].attr( 'pay_stub_entry_name_id' );
			}

			if ( Global.isSet( row['pay_stub_amendment_id'] ) ) {
				data['pay_stub_amendment_id'] = row['ytd_amount'].attr( 'pay_stub_amendment_id' );
			}

			if ( Global.isSet( row['user_expense_id'] ) ) {
				data['user_expense_id'] = row['ytd_amount'].attr( 'user_expense_id' );
			}

			data['pay_stub_id'] = current_edit_item_id;
			result.push( data );
		}

		return result;
	},

	getFilterColumnsFromDisplayColumns: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

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
		column_filter.pay_period_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'status_id':
				if ( c_value == 40 ) {
					this.include_entries = false;
				}
				break;
			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
		}

		this.current_edit_record[key] = c_value;
		if ( key === 'country' ) {
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onSetSearchFilterFinished: function() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}

	},
	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {

			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.province_array = Global.buildRecordArray( res );
				$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

			}} );
		}
	},
	eSetProvince: function( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.e_province_array = Global.buildRecordArray( res );
				if ( refresh && $this.e_province_array.length > 0 ) {
					$this.current_edit_record.province = $this.e_province_array[0].value;
					province_widget.setValue( $this.current_edit_record.province );
				}
				province_widget.setSourceData( $this.e_province_array );

			}} );
		}
	},


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

		if ( this.include_entries ) {
			var entries = $this.saveInsideEditorData();
			if ( entries.length > 0 ) {
				record['entries'] = entries;
			}
		}

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_pay_stub': $.i18n._( 'Pay Stub' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

//		var tab_0_label = this.edit_view.find( 'a[ref=tab_pay_stub]' );
//		var tab_1_label = this.edit_view.find( 'a[ref=tab_audit]' );
//		tab_0_label.text( $.i18n._( 'Pay Stub' ) );
//		tab_1_label.text( $.i18n._( 'Audit' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStub' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub = this.edit_view_tab.find( '#tab_pay_stub' );

		var tab_pay_stub_column1 = tab_pay_stub.find( '.first-column' );
		var tab_pay_stub_column2 = tab_pay_stub.find( '.second-column' );

		var form_item_input;

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_column1 );

		// Employee
		if ( this.is_add ) {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_empty: false,
				field: 'user_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_pay_stub_column1 );
		} else if ( !Global.isSet( this.is_mass_editing ) || Global.isFalseOrNull( this.is_mass_editing ) ) {

			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'full_name'} );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_pay_stub_column1, '' );
		}

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.filtered_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_column1 );

		// Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			show_search_inputs: true,
			set_empty: false,
			field: 'currency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_pay_stub_column1 );

		// Pay Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Start Date' ), form_item_input, tab_pay_stub_column2 );

		// Pay End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay End Date' ), form_item_input, tab_pay_stub_column2 );

		// Payment Date

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'transaction_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Payment Date' ), form_item_input, tab_pay_stub_column2, '' );

		//Inside editor

		var inside_editor_div = tab_pay_stub.find( '.inside-editor-div' );

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			parent_controller: this,
			api: this.pay_stub_entry_api,
			render: 'views/payroll/pay_stub/PayStubEntryViewInsideEditorRender.html',
			render_args: {},
			row_render: 'views/payroll/pay_stub/PayStubEntryViewInsideEditorRow.html'

		} );

		this.editor.removeCover = this.removeInsideEditorCover;
		this.editor.onEditClick = this.removeInsideEditorCover;
		this.editor.onFormItemKeyUp = function( target ) {
			var index = target.parent().parent().index();
			var $this = this;
			var widget_rate = $this.rows_widgets_array[index]['rate'];
			var widget_units = $this.rows_widgets_array[index]['units'];
			var widget_amount = $this.rows_widgets_array[index]['amount'];

			if ( target.getValue().length === 0 ) {
				widget_amount.setReadOnly( false );
			}
			if ( widget_rate.getValue().length > 0 || widget_units.getValue().length > 0 ) {
				widget_amount.setReadOnly( true );
			}

			if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {
				widget_amount.setValue( ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ).toFixed( 2 ) );
				this.onFormItemChange( widget_amount, true );
			} else {
				widget_amount.setValue( '0.00' );
				this.onFormItemChange( widget_amount, true );
			}
		};
		this.editor.onFormItemKeyDown = function( target ) {
			var index = target.parent().parent().index();
			var $this = this;
			var widget = $this.rows_widgets_array[index]['amount'];
			var widget_rate = $this.rows_widgets_array[index]['rate'];
			var widget_units = $this.rows_widgets_array[index]['units'];
			if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {

			} else {
				widget.setValue( '0.00' );
				this.onFormItemChange( widget, true );
			}

			widget.setReadOnly( true );
		};
		this.editor.onFormItemChange = function( target, doNotValidate ) {
			var key = target.getField();
			var c_value = parseFloat( target.getValue() ? target.getValue() : 0 ); // new value
			var index = target.parent().parent().index();

			if ( key == 'amount' ) {
				var original_ytd_amount = parseFloat( this.rows_widgets_array[index]['ytd_amount'].attr( 'original_ytd_amount' ) );
				var original_amount = parseFloat( this.rows_widgets_array[index]['ytd_amount'].attr( 'original_amount' ) );
				var new_ytd_amount = (original_ytd_amount - original_amount + c_value).toFixed( 4 );
				this.rows_widgets_array[index]['ytd_amount'].setValue( new_ytd_amount > 0 ? new_ytd_amount : '-' );
				this.rows_widgets_array[index]['ytd_amount'].attr( 'original_ytd_amount', new_ytd_amount );
				this.rows_widgets_array[index]['ytd_amount'].attr( 'original_amount', c_value );
			}

			if ( doNotValidate ) {

			} else {
				this.parent_controller.validate();
			}

			this.calcTotal();
		};

		this.editor.calcTotal = function() {
			var total_units = 0, total_amount = 0, total_ytd_amount = 0;
			var net_pay_amount = 0;
			var net_pay_ytd_amount = 0;

			for ( var i = 0; i < this.rows_widgets_array.length; i++ ) {
				var row = this.rows_widgets_array[i];
				if ( row === true ) {
					total_units = 0;
					total_amount = 0;
					total_ytd_amount = 0;
					continue;
				}

				if ( row['total_row'] === true ) {
					if ( Global.isSet( row['units'] ) ) {
						row['units'].setValue( total_units.toFixed( 4 ) );
					}
					row['amount'].setValue( total_amount.toFixed( 4 ) );
					row['ytd_amount'].setValue( total_ytd_amount.toFixed( 4 ) );

					if ( net_pay_amount > 0 ) {
						net_pay_amount = net_pay_amount - total_amount;
					} else {
						net_pay_amount = total_amount;
					}

					if ( net_pay_ytd_amount > 0 ) {
						net_pay_ytd_amount = net_pay_ytd_amount - total_ytd_amount;
					} else {
						net_pay_ytd_amount = total_ytd_amount;
					}

					continue;
				}

				if ( row['net_pay_row'] === true ) {
					row['amount'].setValue( net_pay_amount.toFixed( 4 ) );
					row['ytd_amount'].setValue( net_pay_ytd_amount.toFixed( 4 ) );
					continue;
				}

				total_units = parseFloat( total_units ) + ( Global.isSet( row['units'] ) ? ( isNaN( parseFloat( row['units'].getValue() ) ) ? 0 : parseFloat( row['units'].getValue() ) ) : 0 );
				total_amount = parseFloat( total_amount ) + ( isNaN( parseFloat( row['amount'].getValue() ) ) ? 0 : parseFloat( row['amount'].getValue() ) );
				total_ytd_amount = parseFloat( total_ytd_amount ) + ( isNaN( parseFloat( row['ytd_amount'].getValue() ) ) ? 0 : parseFloat( row['ytd_amount'].getValue() ) );

			}

		};

		inside_editor_div.append( this.editor );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Pay Stub Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: false,
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
			new SearchField( {label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Currency' ),
				field: 'currency_id',
				in_column: 1,
				layout_name: ALayoutIDs.CURRENCY,
				api_class: (APIFactory.getAPIClass( 'APICurrency' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 1,
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
			new SearchField( {label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX} ),
			new SearchField( {label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT} ),

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
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
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
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.view:
				this.onViewClick( id );
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub_amendment:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.employee_pay_stubs:
			case ContextMenuIconName.employer_pay_stubs:
			case ContextMenuIconName.generate_pay_stub:
				this.onNavigationClick( id );
				break;

		}

	},

	onViewClick: function( editId, noRefreshUI ) {
		this.onNavigationClick( ContextMenuIconName.view );
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

	onNavigationClick: function( iconName ) {

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

		var post_data;

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
					Global.addViewTab( $this.viewId, 'Pay Stubs', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = {value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, 'Pay Stubs', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case ContextMenuIconName.pay_stub_amendment:
				filter.filter_data = {};
				filter.filter_data.user_id = user_ids[0];
				filter.filter_data.pay_period_id = pay_period_ids[0];
				Global.addViewTab( this.viewId, 'Pay Stubs', window.location.href );
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case ContextMenuIconName.generate_pay_stub:

				if ( user_ids.length === 1 ) {
					filter.user_id = user_ids[0];
				} else if ( user_ids.length > 1 ) {
					filter.user_id = user_ids;
				}

				if ( pay_period_ids.length === 1 ) {
					filter.pay_period_id = pay_period_ids[0];
				} else if ( pay_period_ids.length > 1 ) {
					filter.pay_period_id = pay_period_ids;
				}

				IndexViewController.openWizard( 'GeneratePayStubWizard', filter, function() {
					$this.search();
				} );
				break;
			case ContextMenuIconName.view:
				post_data = {0: args, 1: false, 2: 'pdf', 3: true};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.employee_pay_stubs:
				post_data = {0: args, 1: false, 2: 'pdf', 3: true};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.employer_pay_stubs:
				post_data = {0: args, 1: false, 2: 'pdf', 3: false};
				this.doFormIFrameCall( post_data );
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

	onReportMenuClick: function( id ) {
		this.onReportPrintClick( id );
	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=' + this.api.className + '&Method=' + 'get' + this.api.key_name );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		this.sendFormIFrameCall( postData, url, message_id );

	}


} );

PayStubViewController.loadView = function() {

	Global.loadViewSource( 'PayStub', 'PayStubView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
