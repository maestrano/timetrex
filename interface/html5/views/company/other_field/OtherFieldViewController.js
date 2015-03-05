OtherFieldViewController = BaseViewController.extend( {
	el: '#other_field_view_container',
	type_array: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'OtherFieldEditView.html';
		this.permission_id = 'other_field';
		this.viewId = 'OtherField';
		this.script_name = 'OtherFieldView';
		this.table_name_key = 'other_field';
		this.context_menu_name = $.i18n._( 'Custom Field' );
		this.navigation_label = $.i18n._( 'Custom Field' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIOtherField' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function( callBack ) {

		this.initDropDownOption( 'type' );
	},

	searchDone: function( result ) {

		this._super( 'searchDone' );
		Global.clearCache( 'getOtherField' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_custom_field': $.i18n._( 'Custom Field' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOtherField' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OTHER_FIELD,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_custom_field = this.edit_view_tab.find( '#tab_custom_field' );

		var tab_custom_field_column1 = tab_custom_field.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_custom_field_column1 );

		// Type
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_custom_field_column1, '' );

		// Other ID1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id1', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID1' ), form_item_input, tab_custom_field_column1 );

		// Other ID2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id2', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID2' ), form_item_input, tab_custom_field_column1 );

		// Other ID3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id3', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID3' ), form_item_input, tab_custom_field_column1 );

		// Other ID4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id4', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID4' ), form_item_input, tab_custom_field_column1 );

		// Other ID5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id5', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID5' ), form_item_input, tab_custom_field_column1, '' );

		// the below are all non-display

		// Other ID6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id6', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID6' ), form_item_input, tab_custom_field_column1, '', null, true );

		// Other ID7
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id7', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID7' ), form_item_input, tab_custom_field_column1, '', null, true );

		// Other ID8
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id8', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID8' ), form_item_input, tab_custom_field_column1, '', null, true );

		// Other ID9
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id9', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID9' ), form_item_input, tab_custom_field_column1, '', null, true );

		// Other ID10
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'other_id10', width: 149} );
		this.addEditFieldToColumn( $.i18n._( 'Other ID10' ), form_item_input, tab_custom_field_column1, '', null, true );

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.hiddenOtherField();
	},

	hiddenOtherField: function() {
		this.edit_view_form_item_dic['other_id6'].css( 'display', 'none' );
		this.edit_view_form_item_dic['other_id7'].css( 'display', 'none' );
		this.edit_view_form_item_dic['other_id8'].css( 'display', 'none' );
		this.edit_view_form_item_dic['other_id9'].css( 'display', 'none' );
		this.edit_view_form_item_dic['other_id10'].css( 'display', 'none' );
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 1,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 1,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )];
	}


} );

OtherFieldViewController.loadView = function() {

	Global.loadViewSource( 'OtherField', 'OtherFieldView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};