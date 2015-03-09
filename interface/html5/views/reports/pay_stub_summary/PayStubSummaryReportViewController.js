PayStubSummaryReportViewController = ReportBaseViewController.extend( {

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'PayStubSummaryReport';
		this.viewId = 'PayStubSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'PayStubSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubSummaryReport' ))();
		this.buildContextMenu();

	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
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
		var saved_report_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Saved Report' ),
			id: this.viewId + 'SavedReport',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var pay_stub_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Pay Stub' ),
			id: this.script_name + 'PayStub',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var export_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Export' ),
			id: this.viewId + 'Export',
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

		var excel = new RibbonSubMenu( {
			label: $.i18n._( 'Excel' ),
			id: ContextMenuIconName.export_excel,
			group: editor_group,
			icon: Icons.export_excel,
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

		var save_existed_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save_existed_report,
			group: saved_report_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_new_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save as New' ),
			id: ContextMenuIconName.save_new_report,
			group: saved_report_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		var employee_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employee<br>Pay Stubs' ),
			id: ContextMenuIconName.employee_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var employer_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employer<br>Pay Stubs' ),
			id: ContextMenuIconName.employer_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var print_checks = new RibbonSubMenu( {label: $.i18n._( 'Print Checks' ),
			id: ContextMenuIconName.print_checks,
			group: export_group,
			icon: 'print_checks-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true} );

		var export_cheque_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_cheque', {async: false} ).getResult();

		export_cheque_result = Global.buildRecordArray( export_cheque_result );

		for ( var i = 0; i < export_cheque_result.length; i++ ) {
			var item = export_cheque_result[i];
			var btn = new RibbonSubMenuNavItem( {label: item.label,
				id: item.value,
				nav: print_checks
			} );
		}

		var direct_deposit = new RibbonSubMenu( {label: $.i18n._( 'Direct Deposit' ),
			id: ContextMenuIconName.direct_deposit,
			group: export_group,
			icon: 'direct_deposit-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true} );

		var direct_deposit_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_eft', {async: false} ).getResult();

		direct_deposit_result = Global.buildRecordArray( direct_deposit_result );

		for ( i = 0; i < direct_deposit_result.length; i++ ) {
			item = direct_deposit_result[i];
			btn = new RibbonSubMenuNavItem( {label: item.label,
				id: item.value,
				nav: direct_deposit
			} );
		}

		return [menu];

	},

	openEditView: function() {

		var $this = this;
		$this.initOptions( function() {

			for ( var i = 0; i < $this.setup_fields_array.length; i++ ) {
				var item = $this.setup_fields_array[i];
				if ( item.value === 'status_id' ) {
					item.value = 'filter';
				}
			}

			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.view_file );
			}

			$this.do_validate_after_create_ui = true;

			$this.getReportData( function( result ) {
				// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.

				var edit_item = result[0];
				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					for ( var i = 0; i < result.length; i++ ) {
						if ( result[i].id == LocalCacheData.default_edit_id_for_next_open_edit_view ) {
							edit_item = result[i];
						}
					}
					LocalCacheData.default_edit_id_for_next_open_edit_view = null;
				}

				if ( result && result.length > 0 ) {
					$this.current_saved_report = edit_item;
					$this.saved_report_array = result;
				} else {
					$this.current_saved_report = {};
					$this.saved_report_array = [];
				}

				$this.current_edit_record = {};
				$this.visible_report_values = {};
				$this.setEditViewWidgetsMode();
				$this.initEditView();

			} );

		} );

	},

	onFormItemChange: function( target, doNotDoValidate ) {
		var $this = this;
		this.setIsChanged( target );
		var key = target.getField();

		if ( this.visible_report_widgets && this.visible_report_widgets[key] ) {
			if ( key === 'sort' ) {
				this.visible_report_values[key] = target.getValue( true );

			} else if ( key === 'time_period' ) {
				var time_period = target.getValue();
				this.visible_report_values[key] = {time_period: time_period};

				this.onTimePeriodChange( target );

			} else if ( key === 'filter' ) {
				var filter = target.getValue();
				this.visible_report_values[key] = {status_id: filter};

			} else if ( key === 'start_date' || key === 'end_date' || key === 'pay_period_id' || key === 'pay_period_schedule_id' ) {
				time_period = this.visible_report_values['time_period'];
				time_period[key] = target.getValue();

			} else {
				this.visible_report_values[key] = target.getValue();
			}

		} else {
			this.current_edit_record[key] = target.getValue();
		}

		if ( key === 'template' ) {
			$this.onTemplateChange( this.current_edit_record[key] );
			$this.setEditMenu(); //clean error, set edit menu
		} else {

			if ( this.edit_view_tab_selected_index === 0 ) {
				if ( !doNotDoValidate ) {
					this.validate();
				}

			}

		}

	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.status_id );
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
			case ContextMenuIconName.view:
				this.onViewClick();
				break;
			case ContextMenuIconName.export_excel:
				this.onViewExcelClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.save_existed_report: //All report view
				this.onSaveExistedReportClick();
				break;
			case ContextMenuIconName.save_new_report: //All report view
				this.onSaveNewReportClick();
				break;
			case ContextMenuIconName.employee_pay_stubs: //All report view
				this.onViewClick( 'pdf_employee_pay_stub' )
				break;
			case ContextMenuIconName.employer_pay_stubs: //All report view
				this.onViewClick( 'pdf_employer_pay_stub' )
				break;
		}
	}

} );