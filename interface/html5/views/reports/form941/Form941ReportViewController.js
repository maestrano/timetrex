Form941ReportViewController = ReportBaseViewController.extend( {


	return_type_array: null,
	exempt_payment_array: null,
	state_array: null,
	province_array: null,

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'Form941Report';
		this.viewId = 'Form941Report';
		this.context_menu_name = $.i18n._( 'Form 941' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'Form941ReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIForm941Report' ))();
		this.include_form_setup = true;

		this.buildContextMenu();

	},

	initOptions: function( callBack ) {
		var $this = this;
		var options = [
			{option_name: 'page_orientation'},
			{option_name: 'font_size'},
			{option_name: 'chart_display_mode'},
			{option_name: 'chart_type'},
			{option_name: 'templates'},
			{option_name: 'setup_fields'},
			{option_name: 'schedule_deposit'}
		];

		this.initDropDownOptions( options, function( result ) {

			new (APIFactory.getAPIClass( 'APICompany' ))().getOptions( 'province', 'US', {onResult: function( provinceResult ) {

				$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}} );

		} );

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

		var view_form = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view_form,
			group: form_setup_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var print_form = new RibbonSubMenu( {
			label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print_form,
			group: form_setup_group,
			icon: Icons.print,
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
			case ContextMenuIconName.view_form: //All report view
				this.onViewClick( 'pdf_form' );
				break;
			case ContextMenuIconName.print_form: //All report view
				this.onViewClick( 'pdf_form_print' );
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},
	/* jshint ignore:end */
	schedule_deposit_array: null,

	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab3' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Schedule Depositor

		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input = form_item_input.AComboBox( {
			field: 'deposit_schedule',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OPTION_COLUMN,
			key: 'value'
		} );

		form_item_input.setSourceData( Global.addFirstItemToArray( $this.schedule_deposit_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Depositor' ), form_item_input, tab3_column1, '' );

		//Total Deposits For This Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'quarter_deposit'} );
		this.addEditFieldToColumn( $.i18n._( 'Total Deposits For This Quarter' ), form_item_input, tab3_column1 );

		//Wages, tips and other compensation (Line 2
		var v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'wages_include_pay_stub_entry_account'
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
			field: 'wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Wages, tips and other compensation (Line 2)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax (Line 3)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'income_tax_include_pay_stub_entry_account'
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
			field: 'income_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Income Tax (Line 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Wages (Line 5a)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_wages_include_pay_stub_entry_account'
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
			field: 'social_security_wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Wages (Line 5a)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Taxes Withheld
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_include_pay_stub_entry_account'
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
			field: 'social_security_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Tips (Line 5b)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tips_include_pay_stub_entry_account'
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
			field: 'social_security_tips_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Tips (Line 5b)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Medicare Wages (Line 5c)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_wages_include_pay_stub_entry_account'
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
			field: 'medicare_wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Medicare Wages (Line 5c)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Taxes Withheld
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_include_pay_stub_entry_account'
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
			field: 'medicare_tax_exclude_pay_stub_entry_account'

		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Sick Pay Adjustments (Line 8)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'sick_wages_include_pay_stub_entry_account'
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
			field: 'sick_wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Sick Pay Adjustments (Line 8)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab3_column1 );
		form_item_input.parent().width( '45%' );

		//Company Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'company_name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Company Name' ), form_item_input, tab3_column1 );

		form_item_input.parent().width( '45%' );

		//Address
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'address1', width:'100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Address' ), form_item_input, tab3_column1 );
		form_item_input.parent().width( '45%' );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'city'} );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab3_column1 );

		//State

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'province', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.province_array ) );
		this.addEditFieldToColumn( $.i18n._( 'State' ), form_item_input, tab3_column1 );

		//ZIP Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'postal_code'} );
		this.addEditFieldToColumn( $.i18n._( 'ZIP Code' ), form_item_input, tab3_column1, '' );

	},

	getFormSetupData: function() {
		var other = {};
		other.wages = {include_pay_stub_entry_account: this.current_edit_record.wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.wages_exclude_pay_stub_entry_account};

		other.income_tax = {include_pay_stub_entry_account: this.current_edit_record.income_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.income_tax_exclude_pay_stub_entry_account};

		other.social_security_wages = {include_pay_stub_entry_account: this.current_edit_record.social_security_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_wages_exclude_pay_stub_entry_account};

		other.social_security_tax = {include_pay_stub_entry_account: this.current_edit_record.social_security_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tax_exclude_pay_stub_entry_account};

		other.social_security_tips = {include_pay_stub_entry_account: this.current_edit_record.social_security_tips_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tips_exclude_pay_stub_entry_account};

		other.medicare_wages = {include_pay_stub_entry_account: this.current_edit_record.medicare_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_wages_exclude_pay_stub_entry_account};

		other.medicare_tax = {include_pay_stub_entry_account: this.current_edit_record.medicare_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_tax_exclude_pay_stub_entry_account};

		other.sick_wages = {include_pay_stub_entry_account: this.current_edit_record.sick_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.sick_wages_exclude_pay_stub_entry_account};

		other.eic = {include_pay_stub_entry_account: this.current_edit_record.eic_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.eic_exclude_pay_stub_entry_account};

		other.deposit_schedule = this.current_edit_record.deposit_schedule;
		other.quarter_deposit = this.current_edit_record.quarter_deposit;
		other.name = this.current_edit_record.name;
		other.company_name = this.current_edit_record.company_name;
		other.city = this.current_edit_record.city;
		other.province = this.current_edit_record.province;
		other.postal_code = this.current_edit_record.postal_code;
		other.address1 = this.current_edit_record.address1;

		return other;
	},
	/* jshint ignore:start */
	setFormSetupData: function( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {
			if ( res_Data.wages ) {
				this.edit_view_ui_dic.wages_exclude_pay_stub_entry_account.setValue( res_Data.wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.wages_include_pay_stub_entry_account.setValue( res_Data.wages.include_pay_stub_entry_account )

				this.current_edit_record.wages_include_pay_stub_entry_account = res_Data.wages.include_pay_stub_entry_account;
				this.current_edit_record.wages_exclude_pay_stub_entry_account = res_Data.wages.exclude_pay_stub_entry_account;

			}

			if ( res_Data.income_tax ) {
				this.edit_view_ui_dic.income_tax_exclude_pay_stub_entry_account.setValue( res_Data.income_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.income_tax_include_pay_stub_entry_account.setValue( res_Data.income_tax.include_pay_stub_entry_account );

				this.current_edit_record.income_tax_include_pay_stub_entry_account = res_Data.income_tax.include_pay_stub_entry_account;
				this.current_edit_record.income_tax_exclude_pay_stub_entry_account = res_Data.income_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_wages ) {
				this.edit_view_ui_dic.social_security_wages_exclude_pay_stub_entry_account.setValue( res_Data.social_security_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_wages_include_pay_stub_entry_account.setValue( res_Data.social_security_wages.include_pay_stub_entry_account );

				this.current_edit_record.social_security_wages_include_pay_stub_entry_account = res_Data.social_security_wages.include_pay_stub_entry_account;
				this.current_edit_record.social_security_wages_exclude_pay_stub_entry_account = res_Data.social_security_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_tax ) {
				this.edit_view_ui_dic.social_security_tax_exclude_pay_stub_entry_account.setValue( res_Data.social_security_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_tax_include_pay_stub_entry_account.setValue( res_Data.social_security_tax.include_pay_stub_entry_account );

				this.current_edit_record.social_security_tax_include_pay_stub_entry_account = res_Data.social_security_tax.include_pay_stub_entry_account;
				this.current_edit_record.social_security_tax_exclude_pay_stub_entry_account = res_Data.social_security_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_tips ) {
				this.edit_view_ui_dic.social_security_tips_exclude_pay_stub_entry_account.setValue( res_Data.social_security_tips.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_tips_include_pay_stub_entry_account.setValue( res_Data.social_security_tips.include_pay_stub_entry_account );

				this.current_edit_record.social_security_tips_include_pay_stub_entry_account = res_Data.social_security_tips.include_pay_stub_entry_account;
				this.current_edit_record.social_security_tips_exclude_pay_stub_entry_account = res_Data.social_security_tips.exclude_pay_stub_entry_account;
			}

			if ( res_Data.medicare_wages ) {
				this.edit_view_ui_dic.medicare_wages_exclude_pay_stub_entry_account.setValue( res_Data.medicare_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.medicare_wages_include_pay_stub_entry_account.setValue( res_Data.medicare_wages.include_pay_stub_entry_account );

				this.current_edit_record.medicare_wages_include_pay_stub_entry_account = res_Data.medicare_wages.include_pay_stub_entry_account;
				this.current_edit_record.medicare_wages_exclude_pay_stub_entry_account = res_Data.medicare_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.medicare_tax ) {
				this.edit_view_ui_dic.medicare_tax_exclude_pay_stub_entry_account.setValue( res_Data.medicare_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.medicare_tax_include_pay_stub_entry_account.setValue( res_Data.medicare_tax.include_pay_stub_entry_account );

				this.current_edit_record.medicare_tax_include_pay_stub_entry_account = res_Data.medicare_tax.include_pay_stub_entry_account;
				this.current_edit_record.medicare_tax_exclude_pay_stub_entry_account = res_Data.medicare_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.sick_wages ) {
				this.edit_view_ui_dic.sick_wages_exclude_pay_stub_entry_account.setValue( res_Data.sick_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.sick_wages_include_pay_stub_entry_account.setValue( res_Data.sick_wages.include_pay_stub_entry_account );

				this.current_edit_record.sick_wages_include_pay_stub_entry_account = res_Data.sick_wages.include_pay_stub_entry_account;
				this.current_edit_record.sick_wages_exclude_pay_stub_entry_account = res_Data.sick_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.eic ) {
				this.edit_view_ui_dic.eic_exclude_pay_stub_entry_account.setValue( res_Data.eic.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.eic_include_pay_stub_entry_account.setValue( res_Data.eic.include_pay_stub_entry_account );

				this.current_edit_record.eic_include_pay_stub_entry_account = res_Data.eic.include_pay_stub_entry_account;
				this.current_edit_record.eic_exclude_pay_stub_entry_account = res_Data.eic.exclude_pay_stub_entry_account;
			}

			if ( res_Data.quarter_deposit ) {
				this.edit_view_ui_dic.quarter_deposit.setValue( res_Data.quarter_deposit );

				this.current_edit_record.quarter_deposit = res_Data.quarter_deposit;
			}

			if ( res_Data.deposit_schedule ) {
				this.edit_view_ui_dic.deposit_schedule.setValue( res_Data.deposit_schedule );

				this.current_edit_record.deposit_schedule = res_Data.deposit_schedule;
			}

			if ( res_Data.name ) {
				this.edit_view_ui_dic.name.setValue( res_Data.name );

				this.current_edit_record.name = res_Data.name;
			}

			if ( res_Data.company_name ) {
				this.edit_view_ui_dic.company_name.setValue( res_Data.company_name );

				this.current_edit_record.company_name = res_Data.company_name;
			}

			if ( res_Data.address1 ) {
				this.edit_view_ui_dic.address1.setValue( res_Data.address1 );

				this.current_edit_record.address1 = res_Data.address1;
			}

			if ( res_Data.city ) {
				this.edit_view_ui_dic.city.setValue( res_Data.city );

				this.current_edit_record.city = res_Data.city;
			}

			if ( res_Data.province ) {
				this.edit_view_ui_dic.province.setValue( res_Data.province );

				this.current_edit_record.province = res_Data.province;
			}

			if ( res_Data.postal_code ) {
				this.edit_view_ui_dic.postal_code.setValue( res_Data.postal_code );

				this.current_edit_record.postal_code = res_Data.postal_code;
			}

		}
	}
	/* jshint ignore:end */

} );
