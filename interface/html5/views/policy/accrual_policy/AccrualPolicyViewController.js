AccrualPolicyViewController = BaseViewController.extend( {
	el: '#accrual_policy_view_container',
	type_array: null,
	apply_frequency_array: null,
	month_of_year_array: null,
	day_of_month_array: null,
	day_of_week_array: null,
	length_of_service_unit_array: null,
	date_api: null,
	accrual_policy_milestone_api: null,
	accrual_policy_user_modifier_api: null,

	sub_accrual_policy_user_modifier_view_controller: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'AccrualPolicyEditView.html';
		this.permission_id = 'accrual_policy';
		this.viewId = 'AccrualPolicy';
		this.script_name = 'AccrualPolicyView';
		this.table_name_key = 'accrual_policy';
		this.context_menu_name = $.i18n._( 'Accrual Policy' );
		this.navigation_label = $.i18n._( 'Accrual Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIAccrualPolicy' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.accrual_policy_milestone_api = new (APIFactory.getAPIClass( 'APIAccrualPolicyMilestone' ))();
		this.accrual_policy_user_modifier_api = new (APIFactory.getAPIClass( 'APIAccrualPolicyUserModifier' ))();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'AccrualPolicy' );

	},

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
			case ContextMenuIconName.re_calculate_accrual:
				ProgressBar.showOverlay();
				this.onReCalAccrualClick();
				break;

		}

	},

	onReCalAccrualClick: function() {
		var default_data = {};
		var $this = this;

		if ( this.edit_view ) {
			default_data.accrual_policy_id = [this.current_edit_record.id];
		} else {
			default_data.accrual_policy_id = this.getGridSelectIdArray();
		}

		IndexViewController.openWizard( 'ReCalculateAccrualWizard', default_data, function() {
			$this.search();
		} );
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

		//menu group
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

		var copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.copy,
			group: editor_group,
			icon: Icons.copy_as_new,
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

		var recalculate_accrual = new RibbonSubMenu( {
			label: $.i18n._( 'ReCalculate<br>Accrual' ),
			id: ContextMenuIconName.re_calculate_accrual,
			group: other_group,
			icon: Icons.re_cal_timesheet,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'apply_frequency' );
		this.initDropDownOption( 'length_of_service_unit', 'length_of_service_unit_id', this.accrual_policy_milestone_api );
		this.date_api.getMonthOfYearArray( {onResult: function( res ) {
			res = res.getResult();
			$this.month_of_year_array = res;
		}} );
		this.date_api.getDayOfMonthArray( {onResult: function( res ) {
			res = res.getResult();
			$this.day_of_month_array = res;
		}} );
		this.date_api.getDayOfWeekArray( {onResult: function( res ) {
			res = res.getResult();
			$this.day_of_week_array = res;
		}} );
	},

	setDefaultMenu: function( doNotSetFocus ) {

        //Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
        if (!this.context_menu_array) {
            return;
        }

		this._super( 'setDefaultMenu', doNotSetFocus );

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.re_calculate_accrual:
					this.setDefaultMenuReCalAccrualWizardIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function() {

		this._super( 'setEditMenu' );

		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.re_calculate_accrual:
					this.setEditMenuReCalAccrualWizardIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuReCalAccrualWizardIcon: function( context_btn ) {
		if ( PermissionManager.validate( 'accrual_policy', 'enabled' ) &&
			(PermissionManager.validate( 'accrual_policy', 'edit' ) || PermissionManager.validate( 'accrual_policy', 'edit_child' ) || PermissionManager.validate( 'accrual_policy', 'edit_own' ) )
			) {
			context_btn.removeClass( 'invisible-image' );

		} else {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.current_edit_record.id ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	setDefaultMenuReCalAccrualWizardIcon: function( context_btn, grid_selected_length ) {
		if ( PermissionManager.validate( 'accrual_policy', 'enabled' ) &&
			(PermissionManager.validate( 'accrual_policy', 'edit' ) || PermissionManager.validate( 'accrual_policy', 'edit_child' ) || PermissionManager.validate( 'accrual_policy', 'edit_own' ) )
			) {
			context_btn.removeClass( 'invisible-image' );

		} else {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_accrual_policy': $.i18n._( 'Accrual Policy' ),
			'tab_length_of_service_milestones': $.i18n._( 'Length Of Service Milestones' ),
			'tab_employee_settings': $.i18n._( 'Employee Settings' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_accrual_policy = this.edit_view_tab.find( '#tab_accrual_policy' );
		var tab_length_of_service_milestones = this.edit_view_tab.find( '#tab_length_of_service_milestones' );

		var tab_accrual_policy_column1 = tab_accrual_policy.find( '.first-column' );
		var tab_length_of_service_milestones_column1 = tab_length_of_service_milestones.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_accrual_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_accrual_policy_column1, '' );

		form_item_input.parent().width( '45%' );


		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_accrual_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_accrual_policy_column1 );


		// Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingShiftPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		// Accrual Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'accrual_policy_account_id',
		} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_policy_column1 );



		//Length of Service contributing pay codes.
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingPayCodePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_PAY_CODE_POLICY,
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'length_of_service_contributing_pay_code_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Length Of Service Hours Based On' ), form_item_input, tab_length_of_service_milestones_column1, '', null, true );

		//Frequency In Which To Apply Time to Employee Records
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Frequency In Which To Apply Time to Employee Records' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_accrual_policy_column1, '', null, true, false, 'separated_1' );

		//Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.apply_frequency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Frequency' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Employee's Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'apply_frequency_hire_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee\'s Hire Date' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_month'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_year_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_day_of_month'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		//Day Of Week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_day_of_week'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $.extend( {}, $this.day_of_week_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Day Of Week' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		// After Minimum Employed Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'minimum_employed_days', width: 30} );
		this.addEditFieldToColumn( $.i18n._( 'After Minimum Employed Days' ), form_item_input, tab_accrual_policy_column1, '', null, true );

		var tab_accrual_policy_column2 = tab_accrual_policy.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_accrual_policy_column2 );

		//Milestone Rollover Based On
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Milestone Rollover Based On' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_accrual_policy_column2, '', null, true, false, 'separated_2' );

		//Employee's Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'milestone_rollover_hire_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee\'s Hire Date' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'milestone_rollover_month'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_year_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		//Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'milestone_rollover_day_of_month'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_accrual_policy_column2, '', null, true );

		var tab_length_of_service_milestones = this.edit_view_tab.find( '#tab_length_of_service_milestones' );

		//Inside editor

		var inside_editor_div = tab_length_of_service_milestones.find( '.inside-editor-div' );
		var args = {
			length_of_service: $.i18n._( 'Length Of Service' ),
			accrual_rate: $.i18n._( 'Accrual Rate/Year' ),
			accrual_total_maximum: $.i18n._( 'Accrual Total Maximum' ),
			annual_maximum_rollover: $.i18n._( 'Annual Maximum Rollover' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			render: 'views/policy/accrual_policy/AccrualPolicyInsideEditorRender.html',
			render_args: args,
			row_render: 'views/policy/accrual_policy/AccrualPolicyInsideEditorRow.html',
			parent_controller: this
		} );

		inside_editor_div.append( this.editor );

	},

	onMilestoneRolloverHireDate: function() {
		if ( this.current_edit_record['milestone_rollover_hire_date'] === true ) {
			this.edit_view_form_item_dic['milestone_rollover_month'].css( 'display', 'none' );
			this.edit_view_form_item_dic['milestone_rollover_day_of_month'].css( 'display', 'none' );
		} else if ( this.current_edit_record['milestone_rollover_hire_date'] === false ) {
			this.edit_view_form_item_dic['milestone_rollover_month'].css( 'display', 'block' );
			this.edit_view_form_item_dic['milestone_rollover_day_of_month'].css( 'display', 'block' );
		}

		this.editFieldResize();
	},

	onApplyFrequencyHireDate: function() {

		if ( this.current_edit_record['apply_frequency_id'] === 20 ) {

			this.edit_view_form_item_dic['apply_frequency_month'].css( 'display', 'block' );
			this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'block' );

			if ( this.current_edit_record['apply_frequency_hire_date'] === true ) {
				this.edit_view_form_item_dic['apply_frequency_month'].css( 'display', 'none' );
				this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'none' );
			} else {

				this.edit_view_form_item_dic['apply_frequency_month'].css( 'display', 'none' );
				this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'none' );
			}

			this.editFieldResize();
		}


	},

	onApplyFrequencyChange: function( arg ) {

		if ( !Global.isSet( arg ) ) {

			if ( !Global.isSet( this.current_edit_record['apply_frequency_id'] ) ) {

				this.current_edit_record['apply_frequency_id'] = 10;
			}

			arg = this.current_edit_record['apply_frequency_id'];
		}

		this.edit_view_form_item_dic['apply_frequency_hire_date'].css( 'display', 'none' );
		this.edit_view_form_item_dic['apply_frequency_month'].css( 'display', 'none' );
		this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'none' );
		this.edit_view_form_item_dic['apply_frequency_day_of_week'].css( 'display', 'none' );

		if ( arg === 20 ) {
			this.edit_view_form_item_dic['apply_frequency_hire_date'].css( 'display', 'block' );
			this.edit_view_form_item_dic['apply_frequency_month'].css( 'display', 'block' );
			this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'block' );
			this.onApplyFrequencyHireDate();

		} else if ( arg === 30 ) {
			this.edit_view_form_item_dic['apply_frequency_day_of_month'].css( 'display', 'block' );

		} else if ( arg === 40 ) {
			this.edit_view_form_item_dic['apply_frequency_day_of_week'].css( 'display', 'block' );
		}

		this.editFieldResize();

	},

	onTypeChange: function() {

		if ( !Global.isSet( this.current_edit_record['type_id'] ) ) {
			this.current_edit_record['type_id'] = 10;
		}

		if ( this.current_edit_record['type_id'] === 10 ) {
			$( this.edit_view_tab.find( 'ul li' )[1] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[2] ).hide();
			this.edit_view_tab.tabs( 'select', 0 );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.second-column' ).css( 'display', 'none' );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.first-column' ).addClass( 'full-width-column' );
			this.edit_view_form_item_dic['separated_1'].css( 'display', 'none' );
			this.edit_view_form_item_dic['apply_frequency_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['minimum_employed_days'].css( 'display', 'none' );

			this.edit_view_form_item_dic['contributing_shift_policy_id'].css( 'display', 'none' );

			this.onApplyFrequencyChange( false );

		} else if ( this.current_edit_record['type_id'] === 20 ) {

			if ( !this.is_mass_editing ) {
				$( this.edit_view_tab.find( 'ul li' )[1] ).show();
				$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			}
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.second-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.first-column' ).removeClass( 'full-width-column' );
			this.edit_view_form_item_dic['separated_1'].css( 'display', 'block' );
			this.edit_view_form_item_dic['apply_frequency_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['minimum_employed_days'].css( 'display', 'block' );

			this.edit_view_form_item_dic['contributing_shift_policy_id'].css( 'display', 'none' );

			this.onApplyFrequencyChange();

			this.onApplyFrequencyHireDate();

		} else if ( this.current_edit_record['type_id'] === 30 ) {

			if ( !this.is_mass_editing ) {
				$( this.edit_view_tab.find( 'ul li' )[1] ).show();
				$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			}
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.second-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_accrual_policy' ).find( '.first-column' ).removeClass( 'full-width-column' );

			this.edit_view_form_item_dic['contributing_shift_policy_id'].css( 'display', 'block' );


			this.edit_view_form_item_dic['separated_1'].css( 'display', 'block' );
			this.edit_view_form_item_dic['apply_frequency_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['minimum_employed_days'].css( 'display', 'block' );

			this.onApplyFrequencyChange( false );

		}

		this.editFieldResize();
		this.setAccrualRageFormat();

	},

	setAccrualRageFormat: function( type ) {

		var len = this.editor.rows_widgets_array.length;

		for ( var i = 0; i < len; i++ ) {
			var row = this.editor.rows_widgets_array[i];

			if ( this.current_edit_record['type_id'] === 30 ) {
				row['accrual_rate'].setNeedParseSec( false );
				row['accrual_rate_label'].text( $.i18n._( 'ie' ) + " : 0.0192" );
			} else {
				row['accrual_rate'].setNeedParseSec( true );
				row['accrual_rate_label'].text( $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display );
			}

		}

		var td = $( ".inside-editor-render" ).children().eq( 0 ).children().eq( 0 ).children().eq( 1 );

		if ( this.current_edit_record['type_id'] === 30 ) {
			td.text( $.i18n._( 'Accrual Rate/Hour' ) );
		} else {
			td.text( $.i18n._( 'Accrual Rate/Year' ) );
		}

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

		if ( key === 'apply_frequency_id' ) {
			this.onApplyFrequencyChange();
		}

		if ( key === 'apply_frequency_hire_date' ) {
			this.onApplyFrequencyHireDate();
		}

		if ( key === 'milestone_rollover_hire_date' ) {
			this.onMilestoneRolloverHireDate();
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},


	isDisplayLengthOfServiceHoursBasedOn: function() {
		var len = this.editor.rows_widgets_array.length;

		var count = 0;

		for ( var i = 0; i < len; i++ ) {
			var row = this.editor.rows_widgets_array[i];
			if ( row['length_of_service_unit_id'].getValue() === 50 ) {
				count++;
			}

		}

		if ( count === 0 ) {
			this.edit_view_form_item_dic['length_of_service_contributing_pay_code_policy_id'].css('display', 'none');
			this.edit_view_tab.find( '#tab_length_of_service_milestones' ).find( '.first-column' ).css('border', 'none')
		} else {
			this.edit_view_form_item_dic['length_of_service_contributing_pay_code_policy_id'].css('display', 'block');
			this.edit_view_tab.find( '#tab_length_of_service_milestones' ).find( '.first-column' ).css('border', '1px solid #c7c7c7');
		}


	},

	insideEditorSetValue: function( val ) {
		var len = val.length;

		this.removeAllRows();
		for ( var i = 0; i < val.length; i++ ) {
			if ( Global.isSet( val[i] ) ) {
				var row = val[i];
				if ( Global.isSet( this.parent_id ) ) {
					row['id'] = '';
				}
				this.addRow( row );
			}

		}
	},

	insideEditorAddRow: function( data, index ) {

		if ( !data ) {
			data = {};
		}

		var $this = this;
		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		// Length Of Service
		var widgetContainer = $( "<div class='widget-h-box'></div>" );

		var label_1 = $( "<span class='widget-right-label'> " + $.i18n._( 'After' ) + ': ' + " </span>" );
		var label_2 = $( "<span class='widget-right-label'>&nbsp;</span>" );

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'length_of_service', width: 30} );
		form_item_input.setValue( data.length_of_service ? data.length_of_service : 0 );
		form_item_input.attr( 'milestone_id', (data.id && this.parent_controller.current_edit_record.id) ? data.id : '' );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		var form_item_combobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_combobox.TComboBox( {field: 'length_of_service_unit_id'} );
		form_item_combobox.setSourceData( Global.addFirstItemToArray( this.parent_controller.length_of_service_unit_array ) );
		form_item_combobox.setValue( data.length_of_service_unit_id ? data.length_of_service_unit_id : 10 );

		form_item_combobox.bind( 'formItemChange', function( e, target ) {
			$this.parent_controller.isDisplayLengthOfServiceHoursBasedOn();
		} );

		this.setWidgetEnableBaseOnParentController( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_combobox );

		widgetContainer.append( label_1 );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label_2 );
		widgetContainer.append( form_item_combobox );

		widgets[form_item_input.getField()] = form_item_input;
		widgets[form_item_combobox.getField()] = form_item_combobox;
		row.children().eq( 0 ).append( widgetContainer );

		// Accrual Rate/Year

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'accrual_rate', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label;
		if ( this.parent_controller.current_edit_record.type_id === 30 ) {
			form_item_input.setNeedParseSec( false );
			label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + " : 0.0192</span>" );

		} else {
			form_item_input.setNeedParseSec( true );
			label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		}
		form_item_input.setValue( data.accrual_rate ? data.accrual_rate : '0' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		widgets[form_item_input.getField()] = form_item_input;
		widgets[form_item_input.getField() + '_label'] = label;
		row.children().eq( 1 ).append( widgetContainer );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		// Accrual Total Maximum
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_time', width: 90, need_parser_sec: true} );
		form_item_input.setValue( data.maximum_time ? data.maximum_time : '0' );
		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( widgetContainer );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		// Annual Maximum Rollover
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'rollover_time', width: 90, need_parser_sec: true} );
		form_item_input.setValue( data.rollover_time ? data.rollover_time : '0' );
		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( widgetContainer );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		if ( typeof index != 'undefined' ) {

			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( (index), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		if ( this.parent_controller.is_viewing ) {
			row.find( '.control-icon' ).hide();
		}

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();
	},

	insideEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].length_of_service.attr( 'milestone_id' );
		if ( remove_id > 0 ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();

		this.parent_controller.isDisplayLengthOfServiceHoursBasedOn();
	},

	insideEditorGetValue: function( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = {length_of_service: row.length_of_service.getValue(),
				length_of_service_unit_id: row.length_of_service_unit_id.getValue(),
				accrual_rate: row.accrual_rate.getValue(),
				maximum_time: row.maximum_time.getValue(),
				rollover_time: row.rollover_time.getValue()

			};

			data.id = row.length_of_service.attr( 'milestone_id' );

			data.accrual_policy_id = current_edit_item_id;
			result.push( data );

		}

		return result;
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );

		this.onApplyFrequencyChange();

		this.onTypeChange();

		this.onMilestoneRolloverHireDate();

		this.initInsideEditorData();


	},


	initInsideEditorData: function() {

		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			$this.editor.removeAllRows();
			$this.editor.addRow();
			$this.isDisplayLengthOfServiceHoursBasedOn();

		} else {

			args.filter_data.accrual_policy_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			this.accrual_policy_milestone_api.getAccrualPolicyMilestone( args, true, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}

				var data = res.getResult();
				if ( data === true ) { // result is null
					$this.editor.addRow();
				} else if ( data.length > 0 ) {
					$this.editor.setValue( data );
				}

				$this.isDisplayLengthOfServiceHoursBasedOn();

			}} );

		}

//		if ( !accrual_policy_id ) {
//			this.editor.removeAllRows();
//			this.editor.addRow();
//
//		} else {
//			args.filter_data.accrual_policy_id = accrual_policy_id;
//			this.accrual_policy_milestone_api.getAccrualPolicyMilestone( args, true, {onResult: function( res ) {
//				var data = res.getResult();
//				if ( data === true ) { // result is null
//					$this.editor.addRow();
//				} else if ( data.length > 0 ) {
//					$this.editor.setValue( data );
//				}
//
//			}} );
//		}

	},

	saveInsideEditorData: function( result, callBack ) {
		var $this = this;
		var data = this.editor.getValue( this.refresh_id );
		var remove_ids = $this.editor.delete_ids;
		if ( remove_ids.length > 0 ) {
			$this.accrual_policy_milestone_api.deleteAccrualPolicyMilestone( remove_ids, {onResult: function( res ) {
				if ( res.isValid() ) {
					$this.editor.delete_ids = [];
				}
			}} );
		}

		$this.accrual_policy_milestone_api.setAccrualPolicyMilestone( data, {onResult: function( res ) {
			var res_data = res.getResult();
			if ( Global.isSet( result ) ) {
				result();
			}
		}} );

	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id; // as add
			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search();
				$this.onSaveDone( result );
				$this.current_edit_record = null;
				$this.removeEditView();
			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_accrual_policy_user_modifier_view_controller = null;
	},

	onSaveAndContinueResult: function( result ) {

		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onEditClick( $this.refresh_id, true );

				$this.onSaveAndContinueDone( result );

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNewResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onAddClick( true );

			} );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndCopyResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNextResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.onRightArrowClick();
				$this.search( false );
				$this.onSaveAndNextDone( result );

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		this.is_add = true;
		if ( Global.isSet( this.edit_view ) ) {

			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				this.editor.rows_widgets_array[i].length_of_service.attr( 'milestone_id', '' );
			}

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var selectedId;
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
				$this.onCopyAsNewResult( result );

			}} );
		}

	},

	onCopyAsNewResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		this.copied_record_id = result_data.id;

		result_data.id = '';

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
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
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	setTabStatus: function() {
		if ( this.is_mass_editing ) {
			$( this.edit_view_tab.find( 'ul li a[ref="tab_length_of_service_milestones"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_employee_settings"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );
		} else {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_employee_settings"]' ) ).parent().show();
			if ( this.subAuditValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

		}

		this.editFieldResize( 0 );

	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );
		if ( !this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_tab_selected_index === 3 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab_selected_index === 2 ) {

			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

				if ( this.current_edit_record.id ) {
					this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.initSubAccrualPolicyUserModifier();
				} else {
					this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}


			} else {
				this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}



		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

	},

	initTabData: function() {

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {

			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

				if ( this.current_edit_record.id ) {
					this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.initSubAccrualPolicyUserModifier();
				} else {
					this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}

				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );


			} else {
				this.edit_view_tab.find( '#tab_employee_settings' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}



		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 3 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	initSubAccrualPolicyUserModifier: function() {

		var $this = this;

		if ( this.sub_accrual_policy_user_modifier_view_controller ) {
			this.sub_accrual_policy_user_modifier_view_controller.buildContextMenu( true );
			this.sub_accrual_policy_user_modifier_view_controller.setDefaultMenu();
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'accrual_policy_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadScriptAsync( 'views/policy/accrual_policy/AccrualPolicyUserModifierViewController.js', function() {

			var tab_accrual_policy = $this.edit_view_tab.find( '#tab_employee_settings' );

			var firstColumn = tab_accrual_policy.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'AccrualPolicyUserModifier' + 'View' );
			AccrualPolicyUserModifierViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView( tpl ) {
			var args = { parent_view: 'accrual_policy' };

			return _.template( tpl, args );
		}

		function afterLoadView( subViewController ) {

			$this.sub_accrual_policy_user_modifier_view_controller = subViewController;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'accrual_policy_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_view_controller = $this;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
		}
	}


} );

AccrualPolicyViewController.loadView = function() {

	Global.loadViewSource( 'AccrualPolicy', 'AccrualPolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
