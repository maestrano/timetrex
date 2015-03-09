PayFormulaPolicyViewController = BaseViewController.extend( {
	el: '#pay_formula_policy_view_container',
	type_array: null,
	pay_type_array: null,
	wage_source_type_array: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PayFormulaPolicyEditView.html';
		this.permission_id = 'pay_formula_policy';
		this.viewId = 'PayFormulaPolicy';
		this.script_name = 'PayFormulaPolicyView';
		this.table_name_key = 'pay_formula_policy';
		this.context_menu_name = $.i18n._( 'Pay Formula Policy' );
		this.navigation_label = $.i18n._( 'Pay Formula Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayFormulaPolicy' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayFormulaPolicy' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'pay_type' );
		this.initDropDownOption( 'wage_source_type' );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_pay_formula_policy': $.i18n._( 'Pay Formula Policy' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayFormulaPolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_pay_formula_policy = this.edit_view_tab.find( '#tab_pay_formula_policy' );

		var tab_pay_formula_policy_column1 = tab_pay_formula_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_formula_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_formula_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_pay_formula_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Pay Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'pay_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.pay_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Type' ), form_item_input, tab_pay_formula_policy_column1 );

		// Wage Source
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'wage_source_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.wage_source_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Wage Source' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		//Wage Source Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingShiftPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'wage_source_contributing_shift_policy_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Wage Source Contributing Shift Policy' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		//Time Source Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingShiftPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'time_source_contributing_shift_policy_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Time Source Contributing Shift Policy' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );


		// Premium
		// Hourly Rate
		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'rate', width: 65} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> (" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '1.5 for time and a half' ) + ")</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_formula_policy_column1, '', widgetContainer, true );


		// Wage Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIWageGroup' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE_GROUP,
			show_search_inputs: true,
			set_default: true,
			field: 'wage_group_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab_pay_formula_policy_column1, '', null, true  );


		// Deposit Accrual Policy Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy_account_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_pay_formula_policy_column1, '' );

		// Accrual Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'accrual_rate', width: 100} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );
	},


	setCurrentEditRecordData: function() {

		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'pay_type_id': 10,
			'wage_source_type_id': 10
		} );

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.onPayTypeChange();
		this.onWageSourceTypeChange();
		this.onAccrualAccountChange();

		this.setEditViewDataDone();

	},

	onPayTypeChange: function() {
		if ( this.current_edit_record['pay_type_id'] === 10 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Rate' ) + ": " );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '1.5 for time and a half' ) + ')' );
			this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['wage_source_type_id'].css( 'display', 'block' );

		} else if ( this.current_edit_record['pay_type_id'] === 30 || this.current_edit_record['pay_type_id'] === 40 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) + ": " );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
			this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['wage_source_type_id'].css( 'display', 'block' );

		} else if ( this.current_edit_record['pay_type_id'] === 50 ) {

			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Premium' ) + ": " );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '0.75 for 75 cent/hr' ) + ')' );
			this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['wage_source_type_id'].css( 'display', 'block' );

		} else if ( this.current_edit_record['pay_type_id'] === 32 ) {

			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) + ": " );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
			this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['wage_source_contributing_shift_policy_id'].css('display', 'none');
			this.edit_view_form_item_dic['time_source_contributing_shift_policy_id'].css('display', 'none');
			this.edit_view_form_item_dic['wage_source_type_id'].css( 'display', 'none' );
		} else if ( this.current_edit_record['pay_type_id'] === 42 ) {

			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) + ": " );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
			this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['wage_source_type_id'].css( 'display', 'block' );

		}

		this.editFieldResize();
	},

	onAccrualAccountChange: function() {
		if ( this.current_edit_record['accrual_policy_account_id'] === false || typeof this.current_edit_record['accrual_policy_account_id'] === 'undefined' || this.current_edit_record['accrual_policy_account_id'] === 0 ) {
			this.edit_view_form_item_dic['accrual_rate'].css('display', 'none');
		} else {
			this.edit_view_form_item_dic['accrual_rate'].css('display', 'block');
		}
		this.editFieldResize();
	},

	onWageSourceTypeChange: function() {
		if ( this.current_edit_record['wage_source_type_id'] === 10 ) {
			this.edit_view_form_item_dic['wage_source_contributing_shift_policy_id'].css('display', 'none');
			this.edit_view_form_item_dic['time_source_contributing_shift_policy_id'].css('display', 'none');

			if ( this.current_edit_record['pay_type_id'] === 32 ) {
				this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'none' );
			} else {
				this.edit_view_form_item_dic['wage_group_id'].css('display', 'block');
			}

		} else if ( this.current_edit_record['wage_source_type_id'] === 20 ) {
			this.edit_view_form_item_dic['wage_source_contributing_shift_policy_id'].css('display', 'none');
			this.edit_view_form_item_dic['time_source_contributing_shift_policy_id'].css('display', 'none');
			this.edit_view_form_item_dic['wage_group_id'].css('display', 'none');
		} else if ( this.current_edit_record['wage_source_type_id'] === 30 ) {

			if ( this.edit_view_form_item_dic['wage_source_type_id'].css( 'display' ) === 'block' ) {
				this.edit_view_form_item_dic['wage_source_contributing_shift_policy_id'].css('display', 'block');
				this.edit_view_form_item_dic['time_source_contributing_shift_policy_id'].css('display', 'block');
			}
			this.edit_view_form_item_dic['wage_group_id'].css('display', 'none');
		}
		this.editFieldResize();

	},


	onFormItemChange: function( target ) {
		this.is_changed = true;
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'pay_type_id' ) {
			this.onPayTypeChange();
		}

		if ( key === 'wage_source_type_id' ) {
			this.onWageSourceTypeChange();
		}


		if ( key === 'accrual_policy_account_id' ) {
			this.onAccrualAccountChange();
		}

		this.validate();
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

			new SearchField( {label: $.i18n._( 'Pay Type' ),
				in_column: 1,
				field: 'pay_type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Deposit to Accrual Policy' ),
				in_column: 1,
				field: 'accrual_policy_account_id',
				layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),
//
//			new SearchField( {label: $.i18n._( 'Pay Stub Account' ),
//				in_column: 1,
//				field: 'pay_stub_entry_account_id',
//				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
//				api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
//				multiple: true,
//				basic_search: true,
//				adv_search: false,
//				form_item_type: FormItemType.AWESOME_BOX} ),

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
				form_item_type: FormItemType.AWESOME_BOX} )];
	}


} );

PayFormulaPolicyViewController.loadView = function() {

	Global.loadViewSource( 'PayFormulaPolicy', 'PayFormulaPolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};