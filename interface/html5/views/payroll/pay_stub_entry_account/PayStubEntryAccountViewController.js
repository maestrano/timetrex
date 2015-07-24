PayStubEntryAccountViewController = BaseViewController.extend( {
	el: '#pay_stub_entry_account_view_container',
	type_array: null,
	status_array: null,
	accrual_type_array: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PayStubEntryAccountEditView.html';
		this.permission_id = 'pay_stub_account';
		this.viewId = 'PayStubEntryAccount';
		this.script_name = 'PayStubEntryAccountView';
		this.table_name_key = 'pay_stub_entry_account';
		this.context_menu_name = $.i18n._( 'Pay Stub Accounts' );
		this.navigation_label = $.i18n._( 'Pay Stub Account' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubEntryAccount' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayStubEntryAccount' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'accrual_type' );
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
		if ( key === 'accrual_pay_stub_entry_account_id' ) {
			this.onAccrualPayStubEntryAccountChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onTypeChange();
		this.onAccrualPayStubEntryAccountChange();

	},

	onTypeChange: function() {
		if ( this.current_edit_record.type_id === 50 ) {
			this.edit_view_form_item_dic['accrual_pay_stub_entry_account_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['accrual_type_id'].css( 'display', 'none' );

		} else {
			this.edit_view_form_item_dic['accrual_pay_stub_entry_account_id'].css( 'display', 'block' );
			this.onAccrualPayStubEntryAccountChange();
		}

		this.editFieldResize();
	},
	onAccrualPayStubEntryAccountChange: function() {
		if ( this.current_edit_record.accrual_pay_stub_entry_account_id > 0 ) {
			this.edit_view_form_item_dic['accrual_type_id'].css( 'display', 'block' );
		} else {
			this.edit_view_form_item_dic['accrual_type_id'].css( 'display', 'none' );
		}

		this.editFieldResize();
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

		var wizard = new RibbonSubMenu( {
			label: $.i18n._( 'Migrate<br>PS Accounts ' ),
			id: ContextMenuIconName.migrate_pay_stub_account,
			group: other_group,
			icon: Icons.wizard,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.migrate_pay_stub_account:
				this.onWizardClick();
				break;

		}
	},

	onWizardClick: function() {
		var $this = this;
		IndexViewController.openWizard( 'PayStubAccountWizard', null, function(){
		} );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_pay_stub_account': $.i18n._( 'Pay Stub Account' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();


		//Tab 0 start

		var tab_pay_stub_account = this.edit_view_tab.find( '#tab_pay_stub_account' );

		var tab_pay_stub_account_column1 = tab_pay_stub_account.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_account_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_account_column1, '' );

		//Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_pay_stub_account_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_stub_account_column1 );

		form_item_input.parent().width( '45%' );

		//Order

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'ps_order', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Order' ), form_item_input, tab_pay_stub_account_column1 );

		//Accrual

		var args = {};
		var filter_data = {};
		filter_data.type_id = [50, 80];
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_pay_stub_entry_account_id'

		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Accrual' ), form_item_input, tab_pay_stub_account_column1, '', null, true );
		// Accrual Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'accrual_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.accrual_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Type' ), form_item_input, tab_pay_stub_account_column1, '', null, true );

		// Debit Account

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'debit_account', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Debit Account' ), form_item_input, tab_pay_stub_account_column1 );

		// Credit Account
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'credit_account', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Credit Account' ), form_item_input, tab_pay_stub_account_column1, '' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Debit Account' ),
				in_column: 1,
				field: 'debit_account',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Credit Account' ),
				in_column: 1,
				field: 'credit_account',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
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
	}


} );

PayStubEntryAccountViewController.loadView = function() {

	Global.loadViewSource( 'PayStubEntryAccount', 'PayStubEntryAccountView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};