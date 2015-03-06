PolicyGroupViewController = BaseViewController.extend( {
	el: '#policy_group_view_container',
	sub_document_view_controller: null,
	document_object_type_id: null,
	exception_policy_control_api: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PolicyGroupEditView.html';
		this.permission_id = 'policy_group';
		this.viewId = 'PolicyGroup';
		this.script_name = 'PolicyGroupView';
		this.table_name_key = 'policy_group';
		this.document_object_type_id = 200;
		this.context_menu_name = $.i18n._( 'Policy Group' );
		this.navigation_label = $.i18n._( 'Policy Group' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPolicyGroup' ))();
		this.exception_policy_control_api = new (APIFactory.getAPIClass( 'APIExceptionPolicyControl' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PolicyGroup' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_policy_group': $.i18n._( 'Policy Group' ),
			'tab_attachment': $.i18n._( 'Attachments' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPolicyGroup' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.POLICY_GROUP,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_policy_group = this.edit_view_tab.find( '#tab_policy_group' );

		var tab_policy_group_column1 = tab_policy_group.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_policy_group_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_policy_group_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_policy_group_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		var default_args = {};
		default_args.permission_section = 'policy_group';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_policy_group_column1 );

		// Regular Time Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRegularTimePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.REGULAR_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'regular_time_policy'} );
		this.addEditFieldToColumn( $.i18n._( 'Regular Time Policies' ), form_item_input, tab_policy_group_column1 );

		// Overtime Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'over_time_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Overtime Policies' ), form_item_input, tab_policy_group_column1 );

		// Rounding Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRoundIntervalPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ROUND_INTERVAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'round_interval_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Rounding Policies' ), form_item_input, tab_policy_group_column1 );

		// Meal Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIMealPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.MEAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'meal_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Meal Policies' ), form_item_input, tab_policy_group_column1 );

		// Break Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBreakPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.BREAK_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'break_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Break Policies' ), form_item_input, tab_policy_group_column1 );

		// Accrual Policies

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ACCRUAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Policies' ), form_item_input, tab_policy_group_column1 );

		// Premium Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPremiumPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PREMIUM_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'premium_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Premium Policies' ), form_item_input, tab_policy_group_column1 );

		// Holiday Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIHolidayPolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.HOLIDAY_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'holiday_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Holiday Policies' ), form_item_input, tab_policy_group_column1 );

		if ( LocalCacheData.getCurrentCompany().product_edition_id >= 25 ) {
			// Expense Policies
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIExpensePolicy' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.EXPENSE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'expense_policy'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Expense Policies' ), form_item_input, tab_policy_group_column1 );
		}

		// Exception Policy

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIExceptionPolicyControl' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.EXCEPTION_POLICY_CONTROL,
			show_search_inputs: true,
			set_empty: true,
			field: 'exception_policy_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Exception Policies' ), form_item_input, tab_policy_group_column1 );

		// Absence Policies

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Absence Policies' ), form_item_input, tab_policy_group_column1, '' );

	},

	setTabStatus: function() {
		//Handle most cases that one tab and on audit tab
		if ( this.is_mass_editing ) {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else {
			if ( this.subDocumentValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}
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
		if ( this.edit_view_tab_selected_index === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
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

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		var $this = this;
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'exception_policy_control_id':
//						if ( !Global.isFalseOrNull( this.current_edit_record[key] ) ) {
//							var filter = {};
//							filter.filter_data = {};
//							filter.filter_data.id = [this.current_edit_record[key]];
//							this.exception_policy_control_api['get' + this.exception_policy_control_api.key_name]( filter, {onResult: function(res) {
//								var result = res.getResult();
//								$this.edit_view_ui_dic['exception_policy_control_id'].setSourceData( result );
//								$this.edit_view_ui_dic['exception_policy_control_id'].setValue( $this.current_edit_record['exception_policy_control_id'] );
//							}} );
//						} else {
//							widget.setValue( $this.current_edit_record[key] );
//						}
						widget.setValue( $this.current_edit_record[key] );
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

	initTabData: function() {

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
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

	initSubDocumentView: function() {
		var $this = this;

		if ( this.sub_document_view_controller ) {
			this.sub_document_view_controller.buildContextMenu( true );
			this.sub_document_view_controller.setDefaultMenu();
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/document/DocumentViewController.js', function() {
			var tab_attachment = $this.edit_view_tab.find( '#tab_attachment' );
			var firstColumn = tab_attachment.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'Document' + 'View' );
			DocumentViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_document_view_controller = subViewController;
			$this.sub_document_view_controller.parent_key = 'object_id';
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.document_object_type_id = $this.document_object_type_id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.parent_view_controller = $this;
			$this.sub_document_view_controller.initData();
		}

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_document_view_controller = null;

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'policy_group';

		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Employees' ),
				in_column: 1,
				field: 'user',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Overtime Policy' ),
				in_column: 1,
				field: 'over_time_policy',
				layout_name: ALayoutIDs.OVER_TIME_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Rounding Policies' ),
				in_column: 1,
				field: 'round_interval_policy',
				layout_name: ALayoutIDs.ROUND_INTERVAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIRoundIntervalPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Absence Policies' ),
				in_column: 1,
				field: 'absence_policy',
				layout_name: ALayoutIDs.ABSENCES_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Accrual Policies' ),
				in_column: 2,
				field: 'accrual_policy',
				layout_name: ALayoutIDs.ACCRUAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Premium Policies' ),
				in_column: 2,
				field: 'premium_policy',
				layout_name: ALayoutIDs.PREMIUM_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIPremiumPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Holiday Policies' ),
				in_column: 2,
				field: 'holiday_policy',
				layout_name: ALayoutIDs.HOLIDAY_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIHolidayPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Exception Policy' ),
				in_column: 2,
				field: 'exception_policy_control',
				layout_name: ALayoutIDs.EXCEPTION_POLICY_CONTROL,
				api_class: (APIFactory.getAPIClass( 'APIExceptionPolicyControl' )),
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
	}


} );

PolicyGroupViewController.loadView = function() {

	Global.loadViewSource( 'PolicyGroup', 'PolicyGroupView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};