UserPreferenceViewController = BaseViewController.extend( {
	el: '#user_preference_view_container',
	date_format_array: null,
	other_date_format_array: null,
	js_date_format_array: null,
	flex_date_format_array: null,
	jquery_date_format_array: null,
	time_format_array: null,
	js_time_format_array: null,
	flex_time_format_array: null,
	date_time_format_array: null,
	time_unit_format_array: null,
	time_zone_array: null,
	location_timezone_array: null,
	area_code_timezone_array: null,
	timesheet_view_array: null,
	start_week_day_array: null,
	schedule_icalendar_type_array: null,
	language_array: null,
	user_group_array: null,
	country_array: null,
	province_array: null,

	e_province_array: null,
	user_api: null,
	user_group_api: null,
	company_api: null,

	api_date: null,

	initialize: function() {

		this._super( 'initialize' );
		this.edit_view_tpl = 'UserPreferenceEditView.html';
		this.permission_id = 'user_preference';
		this.viewId = 'UserPreference';
		this.script_name = 'UserPreferenceView';
		this.table_name_key = 'user_preference';
		this.context_menu_name = $.i18n._( 'Preference' );
		this.navigation_label = $.i18n._( 'Employees' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
		this.api_date = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.add] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;

		this.render();

		this.buildContextMenu();

		//call init data in parent view

		this.initData();

		//this.setSelectRibbonMenuIfNecessary( 'UserContact' )

	},

	initOptions: function() {

		var $this = this;
		this.initDropDownOption( 'status', '', this.user_api );
		this.initDropDownOption( 'country', 'country', this.company_api );
		this.initDropDownOption( 'language' );
		this.initDropDownOption( 'date_format' );
		this.initDropDownOption( 'time_format' );
		this.initDropDownOption( 'time_unit_format' );
		this.initDropDownOption( 'time_zone' );
		this.initDropDownOption( 'start_week_day' );
		this.initDropDownOption( 'schedule_icalendar_type' );
		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();
			res = Global.buildTreeRecord( res );
			$this.user_group_array = res;
			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}} );

	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.province_array = Global.buildRecordArray( res );
				$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

			}} );
		}
	},

	eSetProvince: function( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.e_province_array = Global.buildRecordArray( res );

				if ( refresh && $this.e_province_array.length > 0 ) {
					$this.current_edit_record.province = $this.e_province_array[0].value;
					province_widget.setValue( $this.current_edit_record.province );
				}
				province_widget.setSourceData( $this.e_province_array );

			}} );
		}
	},

	onTabShow: function( e, ui ) {

		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );
		if ( !this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_tab_selected_index === 2 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index == 1 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				this.edit_view_tab.find( '#tab_schedule_sync' ).find( '.first-column' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
				this.buildContextMenu( true );
				this.setEditMenu();
			} else {
				this.edit_view_tab.find( '#tab_schedule_sync' ).find( '.first-column' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
			}
		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}
	},

	onSetSearchFilterFinished: function() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}

	},

	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	buildSearchFields: function() {

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'First Name' ),
				in_column: 1,
				field: 'first_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				in_column: 1,
				field: 'last_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee Number' ),
				in_column: 1,
				field: 'employee_number',
				basic_search: false,
				adv_search: true,
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
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 2,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {label: $.i18n._( 'Country' ),
				in_column: 3,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {label: $.i18n._( 'Province/State' ),
				in_column: 3,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} )


		];
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		if ( key === 'schedule_icalendar_type_id' ) {

			this.onStatusChange();
		}

		if ( key === 'country' ) {
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;


		this.setTabLabels( {
			'tab_preference': $.i18n._( 'Preference' ),
			'tab_schedule_sync': $.i18n._( 'Schedule Synchronization' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_preference = this.edit_view_tab.find( '#tab_preference' );

		var tab_preference_column1 = tab_preference.find( '.first-column' );
		var tab_preference_column2 = tab_preference.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_preference_column1 );
		this.edit_view_tabs[0].push( tab_preference_column2 );

		// Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'full_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_preference_column1, '' );

		// Language
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'language', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.language_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_preference_column1 );

		// Date Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'date_format', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.date_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Date Format' ), form_item_input, tab_preference_column1 );

		// Time Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'time_format', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Format' ), form_item_input, tab_preference_column1 );

		// Time Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'time_unit_format', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_unit_format_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Units' ), form_item_input, tab_preference_column1 );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'time_zone', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_zone_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_preference_column1 );

		// Start Weeks on
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'start_week_day'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.start_week_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Start Weeks on' ), form_item_input, tab_preference_column1 );

		// Rows per page
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'items_per_page', width: 50} );
		this.addEditFieldToColumn( $.i18n._( 'Rows per page' ), form_item_input, tab_preference_column1 );

		// Save TimeSheet State
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_save_timesheet_state'} );
		this.addEditFieldToColumn( $.i18n._( 'Save TimeSheet State' ), form_item_input, tab_preference_column1 );

		// Automatically Show Context Menu
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_auto_context_menu'} );
		this.addEditFieldToColumn( $.i18n._( 'Automatically Show Context Menu' ), form_item_input, tab_preference_column1, '' );

		// Email Notifications

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Email Notifications' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_preference_column2 );

		// Exceptions

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_email_notification_exception'} );
		this.addEditFieldToColumn( $.i18n._( 'Exceptions' ), form_item_input, tab_preference_column2 );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_email_notification_message'} );
		this.addEditFieldToColumn( $.i18n._( 'Messages' ), form_item_input, tab_preference_column2 );

		// Pay Stubs
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_email_notification_pay_stub'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stubs' ), form_item_input, tab_preference_column2, '' );

		// Send Notifications to Home Email

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable_email_notification_home'} );
		this.addEditFieldToColumn( $.i18n._( 'Send Notifications to Home Email' ), form_item_input, tab_preference_column2, '' );

		//Tab 1 start

		var tab_schedule_sync = this.edit_view_tab.find( '#tab_schedule_sync' );

		var tab_schedule_sync_column1 = tab_schedule_sync.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_schedule_sync_column1 );

		// schedule icalendar type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'schedule_icalendar_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.schedule_icalendar_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_sync_column1, 'first-last' );

		// Calendar URL
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'calendar_url' } );
		form_item_input.addClass( 'link' );
		this.addEditFieldToColumn( $.i18n._( 'Calendar URL' ), form_item_input, tab_schedule_sync_column1, '', null, true );

		// Shifts Scheduled to Work
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Shifts Scheduled to Work' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'shifts_scheduled_to_work' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm1_working', width: 90, need_parser_sec: true} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm2_working', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Shifts Scheduled Absent

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Shifts Scheduled Absent' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'shifts_scheduled_absent' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm1_absence', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm2_absence', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Modified Shifts

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Modified Shifts' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'modified_shifts' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm1_modified', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'schedule_icalendar_alarm2_modified', width: 90, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>( " + $.i18n._( 'before schedule start time' ) + " )</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			$this.unique_columns = [];
			$this.linked_columns = [];

			$this.current_edit_record = result_data;
			$this.initEditView();

		}} );

	},

	onStatusChange: function() {

		if ( this.current_edit_record.schedule_icalendar_type_id === 0 ) {
			this.edit_view_form_item_dic['calendar_url'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_working'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_working'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_absence'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_absence'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_modified'].css( 'display', 'none' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_modified'].css( 'display', 'none' );
			this.edit_view_form_item_dic['shifts_scheduled_to_work'].css( 'display', 'none' );
			this.edit_view_form_item_dic['shifts_scheduled_absent'].css( 'display', 'none' );
			this.edit_view_form_item_dic['modified_shifts'].css( 'display', 'none' );

		} else {
			this.setCalendarURL();
			this.edit_view_form_item_dic['calendar_url'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_working'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_working'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_absence'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_absence'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm1_modified'].css( 'display', 'block' );
			this.edit_view_form_item_dic['schedule_icalendar_alarm2_modified'].css( 'display', 'block' );
			this.edit_view_form_item_dic['shifts_scheduled_to_work'].css( 'display', 'block' );
			this.edit_view_form_item_dic['shifts_scheduled_absent'].css( 'display', 'block' );
			this.edit_view_form_item_dic['modified_shifts'].css( 'display', 'block' );
		}

		this.editFieldResize();
	},


	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
//					case 'schedule_icalendar_alarm1_working':
//					case 'schedule_icalendar_alarm2_working':
//					case 'schedule_icalendar_alarm1_absence':
//					case 'schedule_icalendar_alarm2_absence':
//					case 'schedule_icalendar_alarm1_modified':
//					case 'schedule_icalendar_alarm2_modified':
//						var result = Global.secondToHHMMSS( this.current_edit_record[key] )
//						widget.setValue( result );
//						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onStatusChange();
	},

	setCalendarURL: function( widget ) {

		if ( !Global.isSet( widget ) ) {
			widget = this.edit_view_ui_dic['calendar_url'];
		}

		if ( this.is_mass_editing ) {
			widget.setValue( 'Not available when mass editing' );
			return;
		}

		this.api['getScheduleIcalendarURL']( this.current_edit_record.user_name, this.current_edit_record.schedule_icalendar_type_id, {onResult: function( result ) {
			var result_data = result.getResult();
			widget.setValue( ServiceCaller.rootURL + result_data );

			widget.unbind( 'click' ); // First unbind all click events, otherwise, when we change the schedule icalendar type this will trigger several times click events.

			widget.click( function() {
				window.open( widget.text() );
			} );

		}} );

	},

	initTabData: function() {
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	onSaveDone: function( result ) {
		var user_id = '';
		user_id = this.current_edit_record.id;

		if ( user_id === LocalCacheData.getLoginUser().id ) {
			Global.updateUserPreference();
		}

	},

	onSaveAndContinueDone: function( result ) {
		this.onSaveDone( result );
	},

	onSaveAndNextDone: function( result ) {
		this.onSaveDone( result );
	},

	validate: function() {
		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}
				}
			}

			if ( this.mass_edit_record_ids.length > 0 ) {
				record.id = this.mass_edit_record_ids[0];
			}

		} else {
			record = this.current_edit_record;
		}
		record.user_id = this.mass_edit_record_ids[0];

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {

			$this.validateResult( result );

		}} );
	}

} );

UserPreferenceViewController.loadView = function() {
	Global.loadViewSource( 'UserPreference', 'UserPreferenceView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( result );
	} );
};