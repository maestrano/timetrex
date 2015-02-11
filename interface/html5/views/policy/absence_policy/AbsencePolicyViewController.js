AbsencePolicyViewController = BaseViewController.extend( {
	el: '#absence_policy_view_container',
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'AbsencePolicyEditView.html';
		this.permission_id = 'absence_policy';
		this.viewId = 'AbsencePolicy';
		this.script_name = 'AbsencePolicyView';
		this.table_name_key = 'absence_policy';
		this.context_menu_name = $.i18n._( 'Absence Policy' );
		this.navigation_label = $.i18n._( 'Absence Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;
		//this.initDropDownOption( 'type' );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_absence_policy': $.i18n._( 'Absence Policy' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_absence_policy = this.edit_view_tab.find( '#tab_absence_policy' );

		var tab_absence_policy_column1 = tab_absence_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_absence_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_absence_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_absence_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayCode' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_absence_policy_column1 );

		//Pay Formula Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayFormulaPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_FORMULA_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_formula_policy_id',
			custom_first_label: $.i18n._( '-- Defined By Pay Code --' ),
			added_items: [
				{value: 0, label: $.i18n._( '-- Defined By Pay Code --' )}
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_absence_policy_column1 );
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;
		/*
		 switch ( key ) {
		 case 'type_id':
		 case 'accrual_policy_id':
		 this.onTypeChange();
		 break;

		 }
		 */
		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );

		this.collectUIDataToCurrentEditRecord();
		this.onTypeChange();
	},

	onTypeChange: function() {
		/*
		 if ( this.current_edit_record['type_id'] === 20 ) {

		 this.edit_view_form_item_dic['rate'].css( 'display', 'none' );
		 this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'none' );
		 this.edit_view_form_item_dic['pay_stub_entry_account_id'].css( 'display', 'none' );

		 } else {
		 this.edit_view_form_item_dic['rate'].css( 'display', 'block' );
		 this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
		 this.edit_view_form_item_dic['pay_stub_entry_account_id'].css( 'display', 'block' );
		 }

		 if ( this.current_edit_record.accrual_policy_id ) {
		 this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'block' );

		 } else {
		 this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'none' );
		 }
		 */
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
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Accrual Policy' ),
				in_column: 1,
				field: 'accrual_policy_id',
				layout_name: ALayoutIDs.ACCRUAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Pay Stub Account' ),
				in_column: 1,
				field: 'pay_stub_entry_account_id',
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Wage Group' ),
				in_column: 2,
				field: 'wage_group_id',
				layout_name: ALayoutIDs.WAGE_GROUP,
				api_class: (APIFactory.getAPIClass( 'APIWageGroup' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

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

AbsencePolicyViewController.loadView = function() {

	Global.loadViewSource( 'AbsencePolicy', 'AbsencePolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};