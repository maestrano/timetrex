EmployeeBankAccountViewController = BaseViewController.extend( {
	el: '#bank_account_view_container',
	user_api: null,
	user_group_api: null,
	ach_transaction_type_array: null,

	bank_account_img_dic: {},

	initialize: function() {

		this._super( 'initialize' );
		this.edit_view_tpl = 'EmployeeBankAccountEditView.html';
		this.permission_id = 'user';
		this.viewId = 'EmployeeBankAccount';
		this.script_name = 'BankAccountView';
		this.table_name_key = 'bank_account';
		this.context_menu_name = $.i18n._( 'Bank Accounts' );
		this.navigation_label = $.i18n._( 'Bank Account' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIBankAccount' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;

		this.render();

		this.buildContextMenu();

		//call init data in parent view

		this.initData();

		//this.setSelectRibbonMenuIfNecessary( 'UserContact' )

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

		var import_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Import' ),
			id: ContextMenuIconName.import_icon,
			group: other_group,
			icon: Icons.import_icon,
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVEmployeeBankAccount' ),
			permission: null
		} );

		return [menu];

	},

	initOptions: function() {

		var $this = this;

		this.initDropDownOption( 'ach_transaction_type' );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();
				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );

	},

	setDefaultMenuDeleteAndNextIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	/* jshint ignore:start */

	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	/* jshint ignore:end */

	onContextMenuClick: function( context_btn, menu_name ) {

		this._super( 'onContextMenuClick', context_btn, menu_name );

		var id;

		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.import_icon:
				ProgressBar.showOverlay();
				this.onImportClick();
				break;

		}
	},

	onImportClick: function() {
		var $this = this;

		IndexViewController.openWizard( 'ImportCSVWizard', 'bank_account', function() {
			$this.search();
		} );
	},

	buildSearchFields: function() {

		var default_args = {permission_section: 'bank_account'};

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				multiple: true,
				default_args: default_args,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'First Name' ),
				in_column: 1,
				field: 'first_name',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				in_column: 1,
				field: 'last_name',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: false,
				//api_class: (APIFactory.getAPIClass( 'APIUserGroup' )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_bank_account': $.i18n._( 'Bank Account' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: (APIFactory.getAPIClass( 'APIBankAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BANK_ACCOUNT,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_bank_account = this.edit_view_tab.find( '#tab_bank_account' );

		var tab_bank_account_column1 = tab_bank_account.find( '.first-column' );
		var tab_bank_account_column2 = tab_bank_account.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_bank_account_column1 );
		this.edit_view_tabs[0].push( tab_bank_account_column2 );

		// Account Type

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'institution1'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.ach_transaction_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Account Type' ), form_item_input, tab_bank_account_column1, '', null, true );

		//Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'bank_account';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_bank_account_column1, '' );

		// Institution Number

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'institution2', width: 30} );
		this.addEditFieldToColumn( $.i18n._( 'Institution Number' ), form_item_input, tab_bank_account_column1, '', null, true );

		// Routing Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'transit', width: 100} );
		this.addEditFieldToColumn( $.i18n._( 'Routing Number' ), form_item_input, tab_bank_account_column1, '', null, true );

		// Account Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'account', width: 120} );
		this.addEditFieldToColumn( $.i18n._( 'Account Number' ), form_item_input, tab_bank_account_column1, '' );

		tab_bank_account_column2.html( "<img src = '' />" );

		tab_bank_account_column2.css( 'border', 'none' );

		this.bank_account_img_dic = tab_bank_account_column2;

	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		var filter = {};

		switch ( key ) {
			case 'user_id':
				filter.filter_data = {};
				filter.filter_data.id = [c_value];
				this.setBankAccount( filter );
				break;
			case 'institution1':
			case 'institution2':
				this.current_edit_record['institution'] = c_value;
				break;

		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setBankAccount: function( filter ) {

		var $this = this;
		if ( Global.isFalseOrNull( filter.filter_data.id[0] ) ) {
			filter.filter_data.id = [LocalCacheData.getLoginUser().id];
		}

		this.user_api['getUser']( filter, {
			onResult: function( result ) {
				if ( !$this.edit_view ) {
					return;
				}

				var result_data = result.getResult();
				result_data = result_data[0];

				if ( result_data.country === 'CA' ) {
					$this.edit_view_form_item_dic['institution1'].css( 'display', 'none' );
					$this.edit_view_form_item_dic['institution2'].css( 'display', 'block' );
					$this.edit_view_form_item_dic['transit'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Bank Transit' ) + ': ' );

					$this.bank_account_img_dic.find( 'img' ).attr( 'src', ServiceCaller.rootURL + LocalCacheData.getLoginData().base_url + 'images/check_zoom_sm_canadian.jpg' );
				} else if ( result_data.country === 'US' ) {
					$this.edit_view_form_item_dic['institution1'].css( 'display', 'block' );
					$this.edit_view_form_item_dic['institution2'].css( 'display', 'none' );
					$this.edit_view_form_item_dic['transit'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Routing Number' ) + ': ' );

					$this.bank_account_img_dic.find( 'img' ).attr( 'src', ServiceCaller.rootURL + LocalCacheData.getLoginData().base_url + 'images/check_zoom_sm_us.jpg' );
				}

			}
		} );
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets
		if ( Global.isSet( this.current_edit_record['institution'] ) ) {
			this.current_edit_record['institution1'] = this.current_edit_record['institution'];
			this.current_edit_record['institution2'] = this.current_edit_record['institution'];
		}

		//Set current edit record data to all widgets
		var filter = {};
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_id':
						filter.filter_data = {};
						filter.filter_data.id = [this.current_edit_record[key]];
						widget.setValue( this.current_edit_record[key] );
						this.setBankAccount( filter );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

} );

EmployeeBankAccountViewController.loadView = function() {
	Global.loadViewSource( 'EmployeeBankAccount', 'BankAccountView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( result );
	} );
};