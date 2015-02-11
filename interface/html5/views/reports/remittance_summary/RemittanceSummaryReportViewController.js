RemittanceSummaryReportViewController = ReportBaseViewController.extend( {

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'RemittanceSummaryReport';
		this.viewId = 'RemittanceSummaryReport';
		this.context_menu_name = $.i18n._( 'Remittance Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'RemittanceSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIRemittanceSummaryReport' ))();
		this.include_form_setup = true;

		this.buildContextMenu();

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
		var form_setup_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Form' ),
			id: this.viewId + 'Form',
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

		var save_setup = new RibbonSubMenu( {
			label: $.i18n._( 'Save Setup' ),
			id: ContextMenuIconName.save_setup,
			group: form_setup_group,
			icon: Icons.save_setup,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},
	/* jshint ignore:start */
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
			case ContextMenuIconName.timesheet_view: //All report view
				this.onViewClick( 'pdf_timesheet' );
				break;
			case ContextMenuIconName.timesheet_view_detail: //All report view
				this.onViewClick( 'pdf_timesheet_detail' );
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},
	/* jshint ignore:end */
	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab3' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Gross Payroll
		var v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'gross_payroll_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'gross_payroll_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Gross Payroll' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Employee/Employer EI Accounts
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'ei_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'ei_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Employee/Employer EI Accounts' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Employee/Employer CPP Accounts
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'cpp_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'cpp_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Employee/Employer CPP Accounts' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax Accounts
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Income Tax Accounts' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

	},

	getFormSetupData: function() {
		var other = {};

		other.gross_payroll = {include_pay_stub_entry_account: this.current_edit_record.gross_payroll_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.gross_payroll_exclude_pay_stub_entry_account};
		other.cpp = {include_pay_stub_entry_account: this.current_edit_record.cpp_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.cpp_exclude_pay_stub_entry_account};
		other.ei = {include_pay_stub_entry_account: this.current_edit_record.ei_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.ei_exclude_pay_stub_entry_account};
		other.tax = {include_pay_stub_entry_account: this.current_edit_record.tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.tax_exclude_pay_stub_entry_account};

		return other;
	},

	setFormSetupData: function( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {

			if ( res_Data.gross_payroll ) {
				this.edit_view_ui_dic.gross_payroll_exclude_pay_stub_entry_account.setValue( res_Data.gross_payroll.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.gross_payroll_include_pay_stub_entry_account.setValue( res_Data.gross_payroll.include_pay_stub_entry_account );

				this.current_edit_record.gross_payroll_include_pay_stub_entry_account = res_Data.gross_payroll.include_pay_stub_entry_account;
				this.current_edit_record.gross_payroll_exclude_pay_stub_entry_account = res_Data.gross_payroll.exclude_pay_stub_entry_account;

			}

			if ( res_Data.cpp ) {
				this.edit_view_ui_dic.cpp_exclude_pay_stub_entry_account.setValue( res_Data.cpp.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.cpp_include_pay_stub_entry_account.setValue( res_Data.cpp.include_pay_stub_entry_account );

				this.current_edit_record.cpp_include_pay_stub_entry_account = res_Data.cpp.include_pay_stub_entry_account;
				this.current_edit_record.cpp_exclude_pay_stub_entry_account = res_Data.cpp.exclude_pay_stub_entry_account;

			}

			if ( res_Data.ei ) {
				this.edit_view_ui_dic.ei_exclude_pay_stub_entry_account.setValue( res_Data.ei.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.ei_include_pay_stub_entry_account.setValue( res_Data.ei.include_pay_stub_entry_account );

				this.current_edit_record.ei_include_pay_stub_entry_account = res_Data.ei.include_pay_stub_entry_account;
				this.current_edit_record.ei_exclude_pay_stub_entry_account = res_Data.ei.exclude_pay_stub_entry_account;
			}

			if ( res_Data.tax ) {
				this.edit_view_ui_dic.tax_exclude_pay_stub_entry_account.setValue( res_Data.tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.tax_include_pay_stub_entry_account.setValue( res_Data.tax.include_pay_stub_entry_account );

				this.current_edit_record.tax_include_pay_stub_entry_account = res_Data.tax.include_pay_stub_entry_account;
				this.current_edit_record.tax_exclude_pay_stub_entry_account = res_Data.tax.exclude_pay_stub_entry_account;
			}
		}
	}

} );