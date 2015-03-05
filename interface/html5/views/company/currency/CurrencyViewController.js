CurrencyViewController = BaseViewController.extend( {
	el: '#currency_view_container',
	status_array: null,
	iso_codes_array: null,
	round_decimal_places_array: null,
	sub_currency_rate_view_controller: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'CurrencyEditView.html';
		this.permission_id = 'currency';
		this.viewId = 'Currency';
		this.script_name = 'CurrencyView';
		this.table_name_key = 'currency';
		this.context_menu_name = $.i18n._( 'Currencies' );
		this.navigation_label = $.i18n._( 'Currency' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APICurrency' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'Currency' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'round_decimal_places', 'round_decimal_places' );
		this.api.getISOCodesArray( '', false, false, {onResult: function( res ) {
			res = res.getResult();
			res = Global.buildRecordArray( res );
			$this.basic_search_field_ui_dic['iso_code'].setSourceData( res );
			$this.iso_codes_array = res;

		}} );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_currency': $.i18n._( 'Currency' ),
			'tab_rates': $.i18n._( 'Rates' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_currency = this.edit_view_tab.find( '#tab_currency' );

		var tab_currency_column1 = tab_currency.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_currency_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_currency_column1, '' );

		// ISO Currency

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'iso_code'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.iso_codes_array ) );
		this.addEditFieldToColumn( $.i18n._( 'ISO Currency' ), form_item_input, tab_currency_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_currency_column1 );

		form_item_input.parent().width( '45%' );

		// Base Currency
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'is_base'} );
		this.addEditFieldToColumn( $.i18n._( 'Base Currency' ), form_item_input, tab_currency_column1 );

		// Conversion Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'conversion_rate', width: 114} );
		this.addEditFieldToColumn( $.i18n._( 'Conversion Rate' ), form_item_input, tab_currency_column1 );

		// Default Currency
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'is_default'} );
		this.addEditFieldToColumn( $.i18n._( 'Default Currency' ), form_item_input, tab_currency_column1 );

		// Auto Update
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'auto_update'} );
		this.addEditFieldToColumn( $.i18n._( 'Auto Update' ), form_item_input, tab_currency_column1 );

		// Decimal Places
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'round_decimal_places'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.round_decimal_places_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Decimal Places' ), form_item_input, tab_currency_column1, '' );

	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		//Handle most cases that one tab and on audit tab
		if ( this.edit_view_tab_selected_index === 1 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_rates' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubCurrencyRateView();
			} else {

				this.edit_view_tab.find( '#tab_rates' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 2 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {

				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

	},


	setTabStatus: function() {

		// exception that edit_view_tab is null
		if ( !this.edit_view_tab ) {
			return;
		}
		//Handle most cases that one tab and on audit tab
		if ( this.is_mass_editing ) {

			$( this.edit_view_tab.find( 'ul li' )[1] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[2] ).hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else {
			// What's the permission the rates tab need to have if it displays?
			$( this.edit_view_tab.find( 'ul li' )[1] ).show();

			if ( this.subAuditValidate() ) {
				$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			} else {
				$( this.edit_view_tab.find( 'ul li' )[2] ).hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

		}

		this.editFieldResize( 0 );
	},

	removeEditView: function() {
		this._super( 'removeEditView' );

		this.sub_currency_rate_view_controller = null;
	},


	initTabData: function() {
		//Handle most case that one tab and one audit tab
		if (  this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_rates' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubCurrencyRateView();
			} else {

				this.edit_view_tab.find( '#tab_rates' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},


	initSubCurrencyRateView: function() {

		var $this = this;
		if ( this.sub_currency_rate_view_controller ) {
			this.sub_currency_rate_view_controller.buildContextMenu( true );
			this.sub_currency_rate_view_controller.setDefaultMenu();
			$this.sub_currency_rate_view_controller.parent_key = 'currency_id';
			$this.sub_currency_rate_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_currency_rate_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_currency_rate_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/company/currency/CurrencyRateViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#tab_rates' );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'CurrencyRate' + 'View', LocalCacheData.current_doing_context_action );
			CurrencyRateViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_currency_rate_view_controller = subViewController;
			$this.sub_currency_rate_view_controller.parent_key = 'currency_id';
			$this.sub_currency_rate_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_currency_rate_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_currency_rate_view_controller.parent_view_controller = $this;
			$this.sub_currency_rate_view_controller.initData();

		}
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'ISO Currency' ),
				in_column: 1,
				field: 'iso_code',
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

CurrencyViewController.loadView = function() {

	Global.loadViewSource( 'Currency', 'CurrencyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};