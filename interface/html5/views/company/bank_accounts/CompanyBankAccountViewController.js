CompanyBankAccountViewController = BaseViewController.extend( {
	ach_transaction_type_array: null,
	company_api: null,
	initialize: function() {

		if ( Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		this._super( 'initialize' );

		this.permission_id = 'user';
		this.viewId = 'CompanyBankAccount';
		this.script_name = 'BankAccountView';
		this.table_name_key = 'bank_account';
		this.edit_view_tpl = 'CompanyBankAccountEditView.html';
		this.context_menu_name = $.i18n._( 'Bank Accounts' );
		this.api = new (APIFactory.getAPIClass( 'APIBankAccount' ))();
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
			{option_name: 'ach_transaction_type', field_name: 'ach_transaction_type_id', api: this.api}
		];
		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );

	},

	uniformVariable: function( records ) {

		if ( !Global.isSet( records ) ) {
			records = {};
		}
		records.user_id = false;
		delete records.institution1;
		delete records.institution2;

		return records;
	},

	getBankAccountDefaultData: function( callBack ) {
		var $this = this;

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_id = false;

		$this.api['get' + $this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();
			if ( Global.isSet( result_data[0] ) ) {
				callBack( result_data[0] );
			} else {
				$this.api[ 'get' + $this.api.key_name + 'DefaultData']( {onResult: function( result ) {
					var result_data = result.getResult();
					callBack( result_data );
				}} );
			}

		}} );
	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		var c_value = target.getValue();

		switch ( key ) {
			case 'institution1':
			case 'institution2':
				this.current_edit_record['institution'] = c_value;
				break;

		}

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	openEditView: function() {

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}

				$this.getBankAccountDefaultData( function( result ) {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this.current_edit_record = result;
					$this.setEditViewWidgetsMode();
					$this.initEditView();

				} );

			} );

		}

	},

	setCurrentEditRecordData: function() {
		var $this = this;

		//Set current edit record data to all widgets
		if ( Global.isSet( this.current_edit_record['institution'] ) ) {
			this.current_edit_record['institution1'] = this.current_edit_record['institution'];
			this.current_edit_record['institution2'] = this.current_edit_record['institution'];
		}

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'company_name':
						var filter = {};
						filter.filter_data = {};
						filter.filter_data.id = this.current_edit_record['company_id'];
						this.company_api['get' + this.company_api.key_name]( filter, {onResult: function( result ) {
							var result_data = result.getResult();
							$this.edit_view_ui_dic['company_name'].setValue( result_data[0].name );
						}} );
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

		for ( i = 0; i < len; i++ ) {
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

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.setBankAccount();

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_bank_account': $.i18n._( 'Bank Account' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		//Tab 0 start

		var tab_bank_account = this.edit_view_tab.find( '#tab_bank_account' );

		var tab_bank_account_column1 = tab_bank_account.find( '.first-column' );
		var tab_bank_account_column2 = tab_bank_account.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_bank_account_column1 );
		this.edit_view_tabs[0].push( tab_bank_account_column2 );

		// Company
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'company_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_bank_account_column1, '' );

		// the case country is US
		// Account Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'institution1'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.ach_transaction_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Account Type' ), form_item_input, tab_bank_account_column1, '', null, true );

		// the case country is CA
		// Institution Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT_NO_AUTO );
		form_item_input.TTextInput( {field: 'institution2', width: 30} );
		this.addEditFieldToColumn( $.i18n._( 'Institution Number' ), form_item_input, tab_bank_account_column1, '', null, true );

		// Routing Number( US ), Bank Transit( CA )
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT_NO_AUTO );
		form_item_input.TTextInput( {field: 'transit', width: 100} );
		this.addEditFieldToColumn( $.i18n._( 'Routing Number' ), form_item_input, tab_bank_account_column1, '', null, true );

		// Account Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT_NO_AUTO );
		form_item_input.TTextInput( {field: 'account', width: 120} );
		this.addEditFieldToColumn( $.i18n._( 'Account Number' ), form_item_input, tab_bank_account_column1, '' );

	},

	setBankAccount: function() {
		var tab_bank_account = this.edit_view_tab.find( '#tab_bank_account' );
		var tab_bank_account_column2 = tab_bank_account.find( '.second-column' );
		var $this = this;

		if ( LocalCacheData.getCurrentCompany().country === 'CA' ) {
			$this.edit_view_form_item_dic['institution1'].css( 'display', 'none' );
			$this.edit_view_form_item_dic['institution2'].css( 'display', 'block' );

			$this.edit_view_form_item_dic['transit'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Bank Transit' ) + ': ' );

			tab_bank_account_column2.html( "<img src = " + ServiceCaller.rootURL + LocalCacheData.getLoginData().base_url + "images/check_zoom_sm_canadian.jpg" + " />" );

		} else if ( LocalCacheData.getCurrentCompany().country === 'US' ) {

			$this.edit_view_form_item_dic['institution1'].css( 'display', 'block' );
			$this.edit_view_form_item_dic['institution2'].css( 'display', 'none' );

			$this.edit_view_form_item_dic['transit'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Routing Number' ) + ': ' );

			tab_bank_account_column2.html( "<img src = " + ServiceCaller.rootURL + LocalCacheData.getLoginData().base_url + "images/check_zoom_sm_us.jpg" + " />" );

		}

		tab_bank_account_column2.css( 'border', 'none' );
	}


} );