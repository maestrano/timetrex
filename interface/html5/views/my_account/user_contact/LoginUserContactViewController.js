LoginUserContactViewController = BaseViewController.extend( {

	sex_array: null,

	company_api: null,

	initialize: function() {

		if ( Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		this._super( 'initialize' );

		this.permission_id = 'user';
		this.viewId = 'LoginUserContact';
		this.script_name = 'LoginUserContactView';
		this.table_name_key = 'bank_account';
		this.context_menu_name = $.i18n._( 'Contact Information' );
		this.api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.add] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.view] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;

		this.render();
		this.buildContextMenu();

		this.initData();

	},

	render: function() {
		this._super( 'render' );
	},

	initOptions: function( callBack ) {

		var options = [
			{option_name: 'sex', field_name: 'sex_id', api: this.api}
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );

	},

	getUserContactData: function( callBack ) {
		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_data.id = LocalCacheData.loginUser.id;

		$this.api['get' + $this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();
			if ( Global.isSet( result_data[0] ) ) {
				callBack( result_data[0] );
			}

		}} );
	},

	openEditView: function() {
		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {
				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'LoginUserContact', 'LoginUserContactEditView.html' );
				}

				$this.getUserContactData( function( result ) {
					// Waiting for the API returns data to set the current edit record.
					$this.current_edit_record = result;
					$this.setEditViewWidgetsMode();
					$this.initEditView();

				} );

			} );

		}

	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country': //popular case
//						this.eSetProvince( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'sin':
						if ( !this.current_edit_record[key] ) {
							widget.setValue( 'N/A' );
						} else {
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	onSaveClick: function() {

		var $this = this;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save';
		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {

			if ( result.isValid() ) {
				var result_data = result.getResult();
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( result_data > 0 ) {
					$this.refresh_id = result_data;
				}

				$this.current_edit_record = null;
				$this.removeEditView();

			} else {
				$this.setErrorTips( result );
				$this.setErrorMenu();
			}

		}} );
	},

	setErrorMenu: function() {

		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.cancel:
					break;
				default:
					context_btn.addClass( 'disable-image' );
					break;
			}

		}
	},

	buildEditViewUI: function() {
		var $this = this;
		this._super( 'buildEditViewUI' );

		this.setTabLabels( {
			'tab_contact_information': $.i18n._( 'Contact Information' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		//Tab 0 start

		var tab_contact_information = this.edit_view_tab.find( '#tab_contact_information' );

		var tab_contact_information_column1 = tab_contact_information.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_contact_information_column1 );

		// First Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'first_name'} );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_contact_information_column1, '' );

		// Middle Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {field: 'middle_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Middle Name' ), form_item_input, tab_contact_information_column1 );

		// Last Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {field: 'last_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_contact_information_column1 );

		// Gender
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'sex_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.sex_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Gender' ), form_item_input, tab_contact_information_column1 );

		// Home Address (Line 1)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'address1', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address(Line 1)' ), form_item_input, tab_contact_information_column1 );
		form_item_input.parent().width( '45%' );

		// //Home Address(Line 2)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'address2', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address(Line 2)' ), form_item_input, tab_contact_information_column1 );
		form_item_input.parent().width( '45%' );

		// City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'city'} );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_contact_information_column1 );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'country', set_empty: true} );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_contact_information_column1 );

		// Province/State
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'province', set_empty: true} );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_contact_information_column1 );

		// Postal/ZIP Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'postal_code', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_contact_information_column1, '' );

		var tab_contact_information_column2 = tab_contact_information.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_contact_information_column2 );

		// Work Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_contact_information_column2, '' );

		// Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_phone_ext', width: 100} );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_contact_information_column2 );

		// Home Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'home_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Home Phone' ), form_item_input, tab_contact_information_column2 );

		// Mobile Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'mobile_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Mobile Phone' ), form_item_input, tab_contact_information_column2 );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'fax_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_contact_information_column2 );

		// Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_email', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_contact_information_column2 );

		// Home Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'home_email', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Home Email' ), form_item_input, tab_contact_information_column2 );

		//Birth Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {field: 'birth_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Birth Date' ), form_item_input, tab_contact_information_column2 );

		// SIN/SSN
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'sin'} );
		this.addEditFieldToColumn( $.i18n._( 'SIN/SSN' ), form_item_input, tab_contact_information_column2, '' );

	}


} );

LoginUserContactViewController.loadView = function() {

	Global.loadViewSource( 'LoginUserContact', 'LoginUserContactView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};