BreakPolicyViewController = BaseViewController.extend( {
	el: '#break_policy_view_container',
	type_array: null,
	auto_detect_type_array: null,

	date_api: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'BreakPolicyEditView.html';
		this.permission_id = 'break_policy';
		this.viewId = 'BreakPolicy';
		this.script_name = 'BreakPolicyView';
		this.table_name_key = 'break_policy';
		this.context_menu_name = $.i18n._( 'Break Policy' );
		this.navigation_label = $.i18n._( 'Break Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIBreakPolicy' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'BreakPolicy' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'auto_detect_type' );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_break_policy': $.i18n._( 'Break Policy' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBreakPolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BREAK_POLICY,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_break_policy = this.edit_view_tab.find( '#tab_break_policy' );

		var tab_break_policy_column1 = tab_break_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_break_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_break_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_break_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_break_policy_column1 );

		//Active After

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'trigger_time', width: 149, need_parser_sec: true} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Active After' ), form_item_input, tab_break_policy_column1, '', widgetContainer );

		// Meal Time
		// Deduction/Addition Time

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'amount', width: 149, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Deduction/Addition Time' ), form_item_input, tab_break_policy_column1, '', widgetContainer, true );

		// Auto-Detect Meals By

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'auto_detect_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.auto_detect_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Auto-Detect Breaks By' ), form_item_input, tab_break_policy_column1 );

		// Minimum Punch Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'minimum_punch_time', width: 149, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Punch Time' ), form_item_input, tab_break_policy_column1, '', widgetContainer, true );

		// Maximum Punch Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_punch_time', width: 149, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Punch Time' ), form_item_input, tab_break_policy_column1, '', widgetContainer, true );

		// Start Window
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'start_window', width: 149, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Window' ), form_item_input, tab_break_policy_column1, '', widgetContainer, true );

		// Window Length

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'window_length', width: 149, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Window Length' ), form_item_input, tab_break_policy_column1, '', widgetContainer, true );

		// Include Any Punched Time for Break
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'include_break_punch_time'} );
		this.addEditFieldToColumn( $.i18n._( 'Include Any Punched Time for Break' ), form_item_input, tab_break_policy_column1, '', null, true );

		// Include Multiple Breaks
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'include_multiple_breaks'} );
		this.addEditFieldToColumn( $.i18n._( 'Include Multiple Breaks' ), form_item_input, tab_break_policy_column1, '', null, true );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayCode' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_break_policy_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_break_policy_column1 );

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

			new SearchField( {label: $.i18n._( 'Auto-Detect Breaks By' ),
				in_column: 1,
				field: 'auto_detect_type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
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
				form_item_type: FormItemType.AWESOME_BOX} )];
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

//		switch ( key ) {
//			case 'trigger_time':
//			case 'amount':
//			case 'minimum_punch_time':
//			case 'maximum_punch_time':
//			case 'window_length':
//			case 'start_window':
//				c_value = this.date_api.parseTimeUnit( target.getValue(), {async: false} ).getResult();
//				break;
//		}

		this.current_edit_record[key] = c_value;

		if ( key === 'type_id' ) {
			this.onTypeChange();
		} else if ( key === 'auto_detect_type_id' ) {
			this.onAutoDetectTypeChange();
		}

		this.editFieldResize( 0 );

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setEditViewDataDone: function(){
		this._super('setEditViewDataDone');
		this.onTypeChange();
		this.onAutoDetectTypeChange();
		this.editFieldResize( 0 );
	},

	onTypeChange: function() {

		if ( this.current_edit_record['type_id'] === 10 || this.current_edit_record['type_id'] === 15 ) {

			this.edit_view_form_item_dic['amount'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Deduction/Addition Time' ) + ": " );
			this.edit_view_form_item_dic['include_break_punch_time'].css( 'display', 'block' );
			this.edit_view_form_item_dic['include_multiple_breaks'].css( 'display', 'block' );

		} else if ( this.current_edit_record['type_id'] === 20 ) {
			this.edit_view_form_item_dic['amount'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Break Time' ) + ": " );
			this.edit_view_form_item_dic['include_break_punch_time'].css( 'display', 'none' );
			this.edit_view_form_item_dic['include_multiple_breaks'].css( 'display', 'none' );
		}

		this.editFieldResize();

	},

	onAutoDetectTypeChange: function() {

		if ( this.current_edit_record['auto_detect_type_id'] === 10 ) {
			this.edit_view_form_item_dic['start_window'].css( 'display', 'block' );
			this.edit_view_form_item_dic['window_length'].css( 'display', 'block' );
			this.edit_view_form_item_dic['minimum_punch_time'].css( 'display', 'none' );
			this.edit_view_form_item_dic['maximum_punch_time'].css( 'display', 'none' );

		} else if ( this.current_edit_record['auto_detect_type_id'] === 20 ) {
			this.edit_view_form_item_dic['start_window'].css( 'display', 'none' );
			this.edit_view_form_item_dic['window_length'].css( 'display', 'none' );
			this.edit_view_form_item_dic['minimum_punch_time'].css( 'display', 'block' );
			this.edit_view_form_item_dic['maximum_punch_time'].css( 'display', 'block' );

		}

		this.editFieldResize();
	}





} );

BreakPolicyViewController.loadView = function() {

	Global.loadViewSource( 'BreakPolicy', 'BreakPolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};