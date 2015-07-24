UserDateTotalViewController = BaseViewController.extend( {

	el: '#user_date_total_view_container', //Must set el here and can only set string, so events can work

	initialize: function() {

		if ( Global.isSet( this.options.sub_view_mode ) ) {
			this.sub_view_mode = this.options.sub_view_mode;
		}

		this._super( 'initialize' );
		this.edit_view_tpl = 'UserDateTotalEditView.html';
		this.permission_id = 'punch';
		this.script_name = 'UserDateTotalView';
		this.viewId = 'UserDateTotal';
		this.table_name_key = 'user_date_total';
		this.context_menu_name = $.i18n._( 'Accumulated Time' );
		this.navigation_label = $.i18n._( 'Accumulated Time' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserDateTotal' ))();
		$( this.el ).find( '.warning-message' ).text( $.i18n._( 'WARNING: Manually modifying Accumulated Time records may prevent policies from being calculated properly and should only be done as a last resort when instructed to do so by a support representative.' ) )

		this.initPermission();
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary();

	},

	setGridSize: function() {
		if ( (!this.grid || !this.grid.is( ':visible' )) ) {
			return;
		}
		this.grid.setGridWidth( $( this.el ).parent().width() - 2 );
		this.grid.setGridHeight( $( this.el ).parent().parent().parent().height() - 100 );
	},

	setGridCellBackGround: function() {

		var data = this.grid.getGridParam( 'data' );

		//Error: TypeError: data is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.is_override === true ) {
				$( "tr[id='" + item.id + "']" ).addClass( 'user-data-total-override' );
			}
		}
	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'object_type' );

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

		var view = new RibbonSubMenu( {
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

		var mass_edit = new RibbonSubMenu( {
			label: $.i18n._( 'Mass<br>Edit' ),
			id: ContextMenuIconName.mass_edit,
			group: editor_group,
			icon: Icons.mass_edit,
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
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVUserDateTotal' ),
			permission: null
		} );

		return [menu];

	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'object_type_id':
				this.onTypeChange( true );
				break;
			case 'regular_policy_id':
			case 'absence_policy_id':
			case 'overtime_policy_id':
			case 'premium_policy_id':
			case 'break_policy_id':
			case 'meal_policy_id':
				this.current_edit_record.src_object_id = c_value;
				delete this.current_edit_record[key];
				this.onSrcObjectChange( key );
				break;
		}

		if ( key !== 'override' ) {
			this.edit_view_ui_dic.override.setValue( true );
			this.current_edit_record.override = true;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onAddClick: function() {
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		//Error: Uncaught TypeError: undefined is not a function in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 897
		if ( $this.api ) {
			$this.api['get' + $this.api.key_name + 'DefaultData'](
				this.parent_edit_record.user_id,
				this.parent_edit_record.date_stamp, {
					onResult: function( result ) {
						$this.onAddResult( result );
					}
				} );
		}

	},

	onAddResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		if ( !result_data.date_stamp ) {
			result_data.date_stamp = this.parent_edit_record.date_stamp;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	/* jshint ignore:start */
	setDefaultMenu: function( doNotSetFocus ) {


		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=UserDateTotal line 282
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
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
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
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	/* jshint ignore:end */

	initPermission: function() {
		this._super( 'initPermission' );

		if ( this.jobUIValidate() ) {
			this.show_job_ui = true;
		} else {
			this.show_job_ui = false;
		}

		if ( this.jobItemUIValidate() ) {
			this.show_job_item_ui = true;
		} else {
			this.show_job_item_ui = false;
		}

		if ( this.branchUIValidate() ) {
			this.show_branch_ui = true;
		} else {
			this.show_branch_ui = false;
		}

		if ( this.departmentUIValidate() ) {
			this.show_department_ui = true;
		} else {
			this.show_department_ui = false;
		}

		if ( this.goodQuantityUIValidate() ) {
			this.show_good_quantity_ui = true;
		} else {
			this.show_good_quantity_ui = false;
		}

		if ( this.badQuantityUIValidate() ) {
			this.show_bad_quantity_ui = true;
		} else {
			this.show_bad_quantity_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_note_ui = true;
		} else {
			this.show_note_ui = false;
		}

	},

	noteUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	jobUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( "job", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			switch ( key ) {
				case 'user_id':
					var current_widget = this.edit_view_ui_dic['first_last_name'];
					new (APIFactory.getAPIClass( 'APIUser' ))().getUser( {filter_data: {id: this.current_edit_record[key]}}, {
						onResult: function( result ) {

							if ( result.isValid() ) {
								var user_data = result.getResult()[0];
							}

							//Error: Unable to get property 'first_name' of undefined or null reference in https://ondemand2001.timetrex.com/interface/html5/ line 511
							if ( user_data && user_data.first_name ) {
								current_widget.setValue( user_data.first_name + ' ' + user_data.last_name );
							} else {
								current_widget.setValue( '' );
							}

						}
					} );
					break;
				case 'date_stamp':
					widget.setEnabled( false );
					widget.setValue( this.current_edit_record[key] );
					break;
				case 'override':
					//Always default to true
					this.current_edit_record.override = true;
					widget.setValue( true );
					break;
				default:
					if ( widget ) {
						widget.setValue( this.current_edit_record[key] );
					}
					break;
			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );
		this.onTypeChange();
	},

	onTypeChange: function( reset ) {
		this.edit_view_form_item_dic['regular_policy_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['absence_policy_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['overtime_policy_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['premium_policy_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['meal_policy_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['break_policy_id'].css( 'display', 'none' );
		var key = '';
		if ( this.current_edit_record['object_type_id'] === 20 ) {
			key = 'regular_policy_id';
		} else if ( this.current_edit_record['object_type_id'] === 25 || this.current_edit_record['object_type_id'] === 50 ) {
			key = 'absence_policy_id';
		} else if ( this.current_edit_record['object_type_id'] === 30 ) {
			key = 'overtime_policy_id';
		} else if ( this.current_edit_record['object_type_id'] === 40 ) {
			key = 'premium_policy_id';
		} else if ( this.current_edit_record['object_type_id'] === 100 || this.current_edit_record['object_type_id'] === 101 ) {
			key = 'meal_policy_id';
		} else if ( this.current_edit_record['object_type_id'] === 110 || this.current_edit_record['object_type_id'] === 111 ) {
			key = 'break_policy_id';
		}
		if ( key ) {
			this.edit_view_form_item_dic[key].css( 'display', 'block' );
			if ( reset ) {
				this.edit_view_ui_dic[key].setValue( '' );
				this.edit_view_ui_dic['pay_code_id'].setValue( '' );
				this.current_edit_record.src_object_id = false;
				this.current_edit_record.pay_code_id = false;
				this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			} else if ( this.current_edit_record.src_object_id ) {
				this.edit_view_ui_dic[key].setValue( this.current_edit_record.src_object_id );
				this.edit_view_ui_dic['pay_code_id'].setEnabled( false );
			}
		} else {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			this.current_edit_record.src_object_id = false;
		}
		this.editFieldResize();
	},

	onSrcObjectChange: function( key ) {
		var full_value = this.edit_view_ui_dic[key].getValue( true );
		if ( full_value && full_value.pay_code_id ) {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( false );
			this.edit_view_ui_dic['pay_code_id'].setValue( full_value.pay_code_id );
			this.current_edit_record.pay_code_id = full_value.pay_code_id;
		} else {
			this.edit_view_ui_dic['pay_code_id'].setEnabled( true );
			this.edit_view_ui_dic['pay_code_id'].setValue( '' );
			this.current_edit_record.pay_code_id = false;
		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = 0;
		this._super( 'search', set_default_menu, page_action, page_number, callBack )
	},

	getProperObjectType: function() {
		var array = [];

		for ( var i = 0; i < this.object_type_array.length; i++ ) {
			var item = this.object_type_array[i];

			if ( item.value === 20 ||
				item.value === 25 ||
				item.value === 30 ||
				item.value === 40 ||
				item.value === 100 ||
				item.value === 110 ) {
				array.push( item );
			}

		}

		return array;
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_user_date_total': $.i18n._( 'Accumulated Time' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserDateTotal' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE,
			show_search_inputs: true,
			navigation_mode: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_user_date_total = this.edit_view_tab.find( '#tab_user_date_total' );

		var tab_user_date_total_column1 = tab_user_date_total.find( '.first-column' );

		//Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'first_last_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_user_date_total_column1, '' );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'date_stamp'} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_user_date_total_column1 );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'total_time', need_parser_sec: true} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.time_unit_format_display ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_user_date_total_column1, '', widgetContainer, true );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'object_type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( this.getProperObjectType() ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_user_date_total_column1 );

		//Regular Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRegularTimePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.REGULAR_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'regular_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['regular_policy_id'].hide();

		//Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['absence_policy_id'].hide();

		//Overtime Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'overtime_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['overtime_policy_id'].hide();

		//Premium Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPremiumPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PREMIUM_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'premium_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['premium_policy_id'].hide();

		//Meal Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMealPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MEAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'meal_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['meal_policy_id'].hide();

		//Break Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBreakPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BREAK_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'break_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy' ), form_item_input, tab_user_date_total_column1, null, null, true );
		this.edit_view_form_item_dic['break_policy_id'].hide();

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayCode' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_user_date_total_column1 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_user_date_total_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.edit_view_form_item_dic.branch_id.hide();
		}

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_user_date_total_column1, '', null, true );

		if ( !this.show_department_ui ) {
			this.edit_view_form_item_dic.department_id.hide();
		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			//Job
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJob' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) job_coder.setValue( val.manual_id );
				}),
				field: 'job_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_user_date_total_column1, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.edit_view_form_item_dic.job_id.hide();
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) job_item_coder.setValue( val.manual_id );
				}),
				field: 'job_item_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_user_date_total_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.edit_view_form_item_dic.job_item_id.hide();
			}

		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			//Quanitity

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( {field: 'quantity', width: 50} );
			good.addClass( 'quantity-input' );

			var good_label = $( "<span class='widget-right-label'>" + $.i18n._( 'Good' ) + ": </span>" );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( {field: 'bad_quantity', width: 50} );
			bad.addClass( 'quantity-input' );

			var bad_label = $( "<span class='widget-right-label'>/ " + $.i18n._( 'Bad' ) + ": </span>" );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_user_date_total_column1, '', widgetContainer, true );

			if ( !this.show_bad_quantity_ui && !this.show_good_quantity_ui ) {
				this.edit_view_form_item_dic.quantity.hide();
			} else {
				if ( !this.show_bad_quantity_ui ) {
					bad_label.hide();
					bad.hide();
				}

				if ( !this.show_good_quantity_ui ) {
					good_label.hide();
					good.hide();
				}
			}
		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {field: 'note', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_user_date_total_column1, '', null, true, true );
		form_item_input.parent().width( '45%' );

		if ( !this.show_note_ui ) {
			this.edit_view_form_item_dic.note.hide();
		}

		//Override
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'override'} );
		this.addEditFieldToColumn( $.i18n._( 'Override' ), form_item_input, tab_user_date_total_column1, '', null, true, true );

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#user_date_total_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	}

} );

UserDateTotalViewController.loadView = function( container ) {

	Global.loadViewSource( 'UserDateTotal', 'UserDateTotalView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( container ) ) {
			container.html( template );
		} else {
			Global.contentContainer().html( template );
		}

	} );

};

UserDateTotalViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'UserDateTotal', 'SubUserDateTotalView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_date_total_view_controller );
			}

		}

	} );

};
