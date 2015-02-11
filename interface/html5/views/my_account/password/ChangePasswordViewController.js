ChangePasswordViewController = BaseViewController.extend( {

	showPassword: null,

	showPhonePassword: null,

	result_data: [],

	initialize: function() {

		if ( Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		this._super( 'initialize' );

		this.permission_id = 'user';
		this.viewId = 'ChangePassword';
		this.script_name = 'ChangePasswordView';
		this.context_menu_name = $.i18n._( 'Passwords' );
		this.api = new (APIFactory.getAPIClass( 'APIUser' ))();

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

		this.initPermission();

		this.render();

		this.initData();

	},

	initPermission: function() {
		this._super( 'initPermission' );

		if ( PermissionManager.validate( 'user', 'edit_own_password' ) ) {
			this.showPassword = true;
		} else {
			this.showPassword = false;
		}

		if ( PermissionManager.validate( "user", "edit_own_phone_password" ) ) {
			this.showPhonePassword = true;
		} else {
			this.showPhonePassword = false;
		}
	},

	render: function() {
		this._super( 'render' );
	},

	saveValidate: function( context_btn, p_id ) {
		// always show
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_name':
						widget.setValue( LocalCacheData.loginUser.user_name );
						break;
					case 'phone_id':

						if ( !LocalCacheData.loginUser.phone_id ) {
							widget.setValue( $.i18n._( 'Not Specified' ) );
						} else {
							widget.setValue( LocalCacheData.loginUser.phone_id );
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

	openEditView: function() {

		var $this = this;

		if ( $this.edit_only_mode && ( this.showPassword || this.showPhonePassword ) ) {

			$this.buildContextMenu();

			if ( !$this.edit_view ) {
				$this.initEditViewUI( 'ChangePassword', 'ChangePasswordEditView.html' );
			}

			$this.current_edit_record = {};

			$this.setEditViewWidgetsMode();
			$this.initEditView();

		} else {
			return;
		}

	},

	setTabStatus: function() {
		//Handle most cases that one tab and on audit tab
		if ( this.showPassword ) {
//			this.edit_view_tab.tabs( 'select', 0 );
			this.editFieldResize( 0 );
			$( this.edit_view_tab.find( 'ul li a[ref="tab_web_password"]' ) ).parent().show();

		} else {
			$( this.edit_view_tab.find( 'ul li a[ref="tab_web_password"]' ) ).parent().hide();
		}

		if ( this.showPhonePassword ) {
			this.edit_view_tab.tabs( 'select', 1 );
			this.editFieldResize( 1 );
			$( this.edit_view_tab.find( 'ul li a[ref="tab_quick_punch_password"]' ) ).parent().show();
		} else {
			$( this.edit_view_tab.find( 'ul li a[ref="tab_quick_punch_password"]' ) ).parent().hide();
		}

		if ( this.showPassword ) {
			this.edit_view_tab.tabs( 'select', 0 );
		}

	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		//this.current_edit_record[key] = target.getValue();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		this.buildContextMenu( true );
		this.setEditMenu();

	},

	initTabData: function() {

	},

	onSaveClick: function() {

		var $this = this;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save';

		this.clearErrorTips();

		if ( this.edit_view_tab_selected_index === 0 ) {
			if ( !record['web.current_password'] ) {
				TAlertManager.showAlert( $.i18n._( 'Current password can\'t be empty' ) );
				ProgressBar.closeOverlay();
			} else {
				$this.saveWebPassword( record, function( result ) {
					if ( result.isValid() ) {
						$this.current_edit_record = null;
						$this.removeEditView();
					} else {
						$this.showErrorTips( result, 0 );
					}

				} );

			}
		} else if ( this.edit_view_tab_selected_index === 1 ) {
			if ( !record['phone.current_password'] ) {
				TAlertManager.showAlert( $.i18n._( 'Current password can\'t be empty' ) );
				ProgressBar.closeOverlay();
			} else {
				$this.savePhonePassword( record, function( result ) {
					if ( result.isValid() ) {
						$this.current_edit_record = null;
						$this.removeEditView();
					} else {
						$this.showErrorTips( result, 1 );
					}
				} );
			}
		}

	},

	showErrorTips: function( result, index ) {

		var details = result.getDetails();
		var error_list = details;
		var tabKey;

		var found_in_current_tab = false;
		for ( var key in error_list ) {
			if ( parseInt( index ) === 0 ) {

				if ( this.current_edit_record['web.current_password'] ) {
					tabKey = 'web.' + key;
				} else {
					continue;
				}

			}

			if ( parseInt( index ) === 1 ) {
				if ( this.current_edit_record['phone.current_password'] ) {
					tabKey = 'phone.' + key;
				} else {
					continue;
				}

			}

			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}

			if ( !Global.isSet( this.edit_view_ui_dic[tabKey] ) ) {
				continue;
			}

			if ( this.edit_view_ui_dic[tabKey].is( ':visible' ) ) {
				this.edit_view_ui_dic[tabKey].setErrorStyle( error_list[key], true );
				found_in_current_tab = true;
			}

			this.edit_view_error_ui_dic[tabKey] = this.edit_view_ui_dic[tabKey];

		}

		if ( !found_in_current_tab ) {

			this.showEditViewError( result );

		}

	},

	saveWebPassword: function( record, callBack ) {
		var $this = this;
		this.api['changePassword']( record['web.current_password'], record['web.password'], record['web.password2'], 'web', {onResult: function( result ) {
			callBack( result );
		}} );
	},

	savePhonePassword: function( record, callBack ) {
		var $this = this;
		this.api['changePassword']( record['phone.current_password'], record['phone.password'], record['phone.password2'], 'phone', {onResult: function( result ) {
			callBack( result );
		}} );
	},

	buildEditViewUI: function() {
		var $this = this;
		this._super( 'buildEditViewUI' );

		this.setTabLabels( {
			'tab_web_password': $.i18n._( 'Web Password' ),
			'tab_quick_punch_password': $.i18n._( 'Quick Punch Password' )
		} );

		//Tab 0 start

		var tab_web_password = this.edit_view_tab.find( '#tab_web_password' );

		var tab_web_password_column1 = tab_web_password.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_web_password_column1 );

		// User Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'user_name'} );
		this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, tab_web_password_column1, '' );

		// Current Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'web.current_password', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Current Password' ), form_item_input, tab_web_password_column1 );

		// New Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'web.password', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'New Password' ), form_item_input, tab_web_password_column1 );

		// New Password(confirm)
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'web.password2', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'New Password(confirm)' ), form_item_input, tab_web_password_column1, '' );

		//Tab 1 start

		var tab_quick_punch_password = this.edit_view_tab.find( '#tab_quick_punch_password' );

		var tab_quick_punch_password_column1 = tab_quick_punch_password.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_quick_punch_password_column1 );

		// Quick Punch ID
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'phone_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch ID' ), form_item_input, tab_quick_punch_password_column1, '' );

		// Current Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'phone.current_password', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Current Password' ), form_item_input, tab_quick_punch_password_column1 );

		// New Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'phone.password', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'New Password' ), form_item_input, tab_quick_punch_password_column1 );

		// New Password(confirm)
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( {field: 'phone.password2', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'New Password(confirm)' ), form_item_input, tab_quick_punch_password_column1, '' );

	}


} );