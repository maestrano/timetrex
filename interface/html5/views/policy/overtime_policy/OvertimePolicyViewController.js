OvertimePolicyViewController = BaseViewController.extend( {
	el: '#overtime_policy_view_container',
	type_array: null,

	branch_selection_type_array: null,
	department_selection_type_array: null,

	job_group_selection_type_array: null,
	job_selection_type_array: null,

	job_group_array: null,
	job_item_group_array: null,

	job_item_group_selection_type_array: null,
	job_item_selection_type_array: null,

	job_group_api: null,
	job_item_group_api: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'OvertimePolicyEditView.html';
		this.permission_id = 'over_time_policy';
		this.viewId = 'OvertimePolicy';
		this.script_name = 'OvertimePolicyView';
		this.table_name_key = 'over_time_policy';
		this.context_menu_name = $.i18n._( 'Overtime Policy' );
		this.navigation_label = $.i18n._( 'Overtime Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIOvertimePolicy' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_group_api = new (APIFactory.getAPIClass( 'APIJobGroup' ))();
			this.job_item_group_api = new (APIFactory.getAPIClass( 'APIJobItemGroup' ))();
		}

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'branch_selection_type' );
		this.initDropDownOption( 'department_selection_type' );
		this.initDropDownOption( 'job_group_selection_type' );
		this.initDropDownOption( 'job_selection_type' );
		this.initDropDownOption( 'job_item_group_selection_type' );
		this.initDropDownOption( 'job_item_selection_type' );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_group_api.getJobGroup( '', false, false, {onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );
				$this.job_group_array = res;

			}} );

			this.job_item_group_api.getJobItemGroup( '', false, false, {onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );
				$this.job_item_group_array = res;

			}} );
		}

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_overtime_policy': $.i18n._( 'Overtime Policy' ),
			'tab_differential_criteria': $.i18n._( 'Differential Criteria' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_overtime_policy = this.edit_view_tab.find( '#tab_overtime_policy' );

		var tab_overtime_policy_column1 = tab_overtime_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_overtime_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_overtime_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_overtime_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingShiftPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'contributing_shift_policy_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_overtime_policy_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_overtime_policy_column1 );

		// Active After
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'trigger_time', width: 149, need_parser_sec: true} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Active After' ), form_item_input, tab_overtime_policy_column1, '', widgetContainer );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayCode' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_overtime_policy_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_overtime_policy_column1 );

		//
		// Tab2 start
		//
		var tab_differential_criteria = this.edit_view_tab.find( '#tab_differential_criteria' );

		var tab_differential_criteria_column1 = tab_differential_criteria.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_differential_criteria_column1 );

		// Branches
		var v_box = $( "<div class='v-box'></div>" )

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'branch_selection_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.branch_selection_type_array ) );

		form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch'} );

		var form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );

		v_box.append( form_item );

		// Exclude Default
		var form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input_2.TCheckbox( {field: 'exclude_default_branch'} );

		form_item = this.putInputToInsideFormItem( form_item_input_2, 'Exclude Default' );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Branches' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

		// Departments
		v_box = $( "<div class='v-box'></div>" )

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'department_selection_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.department_selection_type_array ) );

		form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department'} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );

		v_box.append( form_item );

		// Exclude Default
		form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input_2.TCheckbox( {field: 'exclude_default_department'} );

		form_item = this.putInputToInsideFormItem( form_item_input_2, 'Exclude Default' );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Departments' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			// Job Groups
			v_box = $( "<div class='v-box'></div>" )

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( {field: 'job_group_selection_type_id', set_empty: false} );
			form_item_input.setSourceData( Global.addFirstItemToArray( $this.job_group_selection_type_array ) );

			form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.TREE_COLUMN,
				set_empty: true,
				field: 'job_group'} );

			form_item_input_1.setSourceData( Global.addFirstItemToArray( $this.job_group_array ) );

			form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Job Groups' ), [form_item_input, form_item_input_1], tab_differential_criteria_column1, '', v_box, false, true );

			// Jobs
			v_box = $( "<div class='v-box'></div>" )

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( {field: 'job_selection_type_id', set_empty: false} );
			form_item_input.setSourceData( Global.addFirstItemToArray( $this.job_selection_type_array ) );

			form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJob' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.JOB,
				show_search_inputs: true,
				set_empty: true,
				field: 'job'} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );

			v_box.append( form_item );

			// Exclude Default
			form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input_2.TCheckbox( {field: 'exclude_default_job'} );
			form_item = this.putInputToInsideFormItem( form_item_input_2, 'Exclude Default' );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Jobs' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

			// Task Groups
			v_box = $( "<div class='v-box'></div>" )

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( {field: 'job_item_group_selection_type_id', set_empty: false} );
			form_item_input.setSourceData( Global.addFirstItemToArray( $this.job_item_group_selection_type_array ) );

			form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.TREE_COLUMN,
				set_empty: true,
				field: 'job_item_group'} );

			form_item_input_1.setSourceData( Global.addFirstItemToArray( $this.job_item_group_array ) );
			form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Task Groups' ), [form_item_input, form_item_input_1], tab_differential_criteria_column1, '', v_box, false, true );

			// Tasks
			v_box = $( "<div class='v-box'></div>" )

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( {field: 'job_item_selection_type_id', set_empty: false} );
			form_item_input.setSourceData( Global.addFirstItemToArray( $this.job_item_selection_type_array ) );

			form_item = this.putInputToInsideFormItem( form_item_input, 'Selection Type' )

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.JOB_ITEM,
				show_search_inputs: true,
				set_empty: true,
				field: 'job_item'} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, 'Selection' );
			v_box.append( form_item );

			// Exclude Default
			form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input_2.TCheckbox( {field: 'exclude_default_job_item'} );
			form_item = this.putInputToInsideFormItem( form_item_input_2, 'Exclude Default' );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Tasks' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );
		}
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'branch_selection_type_id' ) {
			this.onBranchSelectionTypeChange();
		}
		if ( key === 'department_selection_type_id' ) {
			this.onDepartmentSelectionTypeChange();
		}
		if ( key === 'job_group_selection_type_id' ) {
			this.onJobGroupSelectionTypeChange();
		}
		if ( key === 'job_selection_type_id' ) {
			this.onJobSelectionTypeChange();
		}
		if ( key === 'job_item_group_selection_type_id' ) {
			this.onJobItemGroupSelectionTypeChange();
		}
		if ( key === 'job_item_selection_type_id' ) {
			this.onJobItemSelectionTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setCurrentEditRecordData: function() {

		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'branch_selection_type_id': 10,
			'department_selection_type_id': 10,
			'job_group_selection_type_id': 10,
			'job_selection_type_id': 10,
			'job_item_group_selection_type_id': 10,
			'job_item_selection_type_id': 10
		} );

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.onBranchSelectionTypeChange();
		this.onDepartmentSelectionTypeChange();
		this.onJobGroupSelectionTypeChange();
		this.onJobSelectionTypeChange();
		this.onJobItemGroupSelectionTypeChange();
		this.onJobItemSelectionTypeChange();

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();

	},

	onBranchSelectionTypeChange: function() {
		if ( this.current_edit_record['branch_selection_type_id'] === 10 ) {
			this.edit_view_ui_dic['branch'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['branch'].setEnabled( true );
		}
	},

	onDepartmentSelectionTypeChange: function() {
		if ( this.current_edit_record['department_selection_type_id'] === 10 ) {
			this.edit_view_ui_dic['department'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['department'].setEnabled( true );
		}
	},

	onJobGroupSelectionTypeChange: function() {

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			if ( this.current_edit_record['job_group_selection_type_id'] === 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_group'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_group'].setEnabled( true );
			}
		}
	},

	onJobSelectionTypeChange: function() {
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			if ( this.current_edit_record['job_selection_type_id'] === 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job'].setEnabled( true );
			}
		}
	},

	onJobItemGroupSelectionTypeChange: function() {
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			if ( this.current_edit_record['job_item_group_selection_type_id'] === 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_item_group'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_item_group'].setEnabled( true );
			}
		}
	},

	onJobItemSelectionTypeChange: function() {
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			if ( this.current_edit_record['job_item_selection_type_id'] === 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_item'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_item'].setEnabled( true );
			}
		}
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
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

			new SearchField( {label: $.i18n._( 'Pay Code' ),
				in_column: 1,
				field: 'pay_code_id',
				layout_name: ALayoutIDs.PAY_CODE,
				api_class: (APIFactory.getAPIClass( 'APIPayCode' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Pay Formula Policy' ),
				in_column: 1,
				field: 'pay_formula_policy_id',
				layout_name: ALayoutIDs.PAY_FORMULA_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIPayFormulaPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Contributing Shift Policy' ),
				in_column: 2,
				field: 'contributing_shift_policy_id',
				layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIContributingShiftPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
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

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		//Handle most cases that one tab and on audit tab
		if ( this.edit_view_tab_selected_index == 1 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			} else {
				this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
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

	initTabData: function() {
		//Handle most case that one tab and one audit tab
		if ( this.edit_view_tab_selected_index == 1 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			} else {
				this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
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
	}


} );

OvertimePolicyViewController.loadView = function() {

	Global.loadViewSource( 'OvertimePolicy', 'OvertimePolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
