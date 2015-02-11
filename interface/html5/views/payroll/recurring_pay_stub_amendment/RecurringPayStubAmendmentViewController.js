RecurringPayStubAmendmentViewController = BaseViewController.extend( {
	el: '#recurring_pay_stub_amendment_view_container',
	type_array: null,
	filtered_status_array: null,
	frequency_array: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'RecurringPayStubAmendmentEditView.html';
		this.permission_id = 'pay_stub_amendment';
		this.viewId = 'RecurringPayStubAmendment';
		this.script_name = 'RecurringPayStubAmendmentView';
		this.table_name_key = 'recurring_ps_amendment';
		this.context_menu_name = $.i18n._( 'Recurring PS Amendment' );
		this.navigation_label = $.i18n._( 'Recurring PS Amendment' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRecurringPayStubAmendment' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RecurringPayStubAmendment' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'filtered_status', 'status_id', this.api, function() {
			$this.basic_search_field_ui_dic['status_id'].setSourceData( $this.filtered_status_array );
		} );
		this.initDropDownOption( 'frequency' );
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

	uniformVariable: function( records ) {

		if ( records.type_id === 20 ) {
			records.amount = records.percent_amount;
		}

		return records;
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

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;
		var allow_multiple_selection = false;

		this.setTabLabels( {
			'tab_recurring_ps_amendment': $.i18n._( 'Recurring PS Amendment' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRecurringPayStubAmendment' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_AMENDMENT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_recurring_ps_amendment = this.edit_view_tab.find( '#tab_recurring_ps_amendment' );

		var tab_recurring_ps_amendment_column1 = tab_recurring_ps_amendment.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_ps_amendment_column1 );

		// Status
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.filtered_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_recurring_ps_amendment_column1, '' );

		// Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_ps_amendment_column1 );

		form_item_input.parent().width( '45%' );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'description', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_recurring_ps_amendment_column1 );

		form_item_input.parent().width( '45%' );

		// Frequency

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'frequency_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.frequency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Frequency' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// Employee(s)
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		var default_args = {};
		default_args.permission_section = 'recurring_ps_amendment';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// Pay Stub Amendment
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Pay Stub Amendment' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_recurring_ps_amendment_column1 );

		// Pay Stub Account
		var args = {};
		var filter_data = {};
		filter_data.type_id = [10, 20, 30, 50, 60, 65, 80];
		args.filter_data = filter_data;

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
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// Amount Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Amount Type' ), form_item_input, tab_recurring_ps_amendment_column1 );

		// Fixed

		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'rate', width: 149, hasKeyEvent: true} );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_recurring_ps_amendment_column1, '', null, true, null, null, true );

		// Units
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'units', width: 149, hasKeyEvent: true} );
		this.addEditFieldToColumn( $.i18n._( 'Units' ), form_item_input, tab_recurring_ps_amendment_column1, '', null, true, null, null, true );

		// Amount

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'amount', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_recurring_ps_amendment_column1, '', null, true );

		// Percent

		//Percent
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'percent_amount', width: 79} );
		this.addEditFieldToColumn( $.i18n._( 'Percent' ), form_item_input, tab_recurring_ps_amendment_column1, '', null, true );

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
		this.addEditFieldToColumn( $.i18n._( 'Percent of' ), form_item_input, tab_recurring_ps_amendment_column1, '', null, true );

		// Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'ps_amendment_description', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_recurring_ps_amendment_column1, '' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Frequency' ),
				in_column: 2,
				field: 'frequency_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	}


} );

RecurringPayStubAmendmentViewController.loadView = function() {

	Global.loadViewSource( 'RecurringPayStubAmendment', 'RecurringPayStubAmendmentView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
