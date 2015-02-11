ReportBaseViewController = BaseViewController.extend( {

	view_file: '',

	page_orientation_array: null,
	font_size_array: null,
	chart_display_mode_array: null,
	chart_type_array: null,
	templates_array: null,
	setup_fields_array: null,

	log_action_id_array: null,

	log_table_name_id_array: null,

	time_period_array: null,

	visible_report_widgets: null,

	visible_report_values: null,

	visible_report_widgets_order_fix: null,

	status_id_array: null,

	api_user_report: null,

	current_saved_report: null, // Current saved report if any

	saved_report_array: null,

	sub_saved_report_view_controller: null,

	sub_custom_column_view_controller: null,

	need_refresh_display_columns: false, //When custom column changed. Set this to true.

	ReportMissedField: null,

	include_form_setup: false,

	show_empty_message: false,

	do_validate_after_create_ui: false, //Do validate if there is a saved report

	form_setup_changed: false,

	initialize: function() {
		this._super( 'initialize' );
		this.permission_id = 'report';
		this.api_user_report = new (APIFactory.getAPIClass( 'APIUserReportData' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.save] = true;

		LocalCacheData.current_open_report_controller = this;

	},

	render: function() {

	},

	openEditView: function() {

		var $this = this;
		$this.initOptions( function() {

			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.view_file );
			}

			$this.do_validate_after_create_ui = true;

			$this.getReportData( function( result ) {
				// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.

				var edit_item = result[0];
				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					for ( var i = 0; i < result.length; i++ ) {
						if ( result[i].id === parseInt( LocalCacheData.default_edit_id_for_next_open_edit_view ) ) {
							edit_item = result[i];
						}
					}

					LocalCacheData.default_edit_id_for_next_open_edit_view = null;
				}

				if ( result && result.length > 0 ) {
					$this.current_saved_report = edit_item;
					$this.saved_report_array = result;
				} else {
					$this.current_saved_report = null;
					$this.saved_report_array = [];
				}

				$this.current_edit_record = {};
				$this.visible_report_values = {};
				$this.setEditViewWidgetsMode();
				$this.initEditView();

			} );

		} );

	},

	setDefaultConfigData: function() {

		var $this = this;
		this.api.getOtherConfig( {onResult: function( config_result ) {

			if ( $this.current_saved_report &&
				$this.current_saved_report.data &&
				$this.current_saved_report.data.config &&
				$this.current_saved_report.data.config.other
				) {
				//do nothing
			} else {

				config_result = config_result.getResult();
				for ( var key in config_result ) {
					if ( $this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						$this.edit_view_ui_dic[key].setValue( config_result[ key ] );
						$this.current_edit_record[key] = config_result[ key ];
					}
				}

			}
		}} );

		this.api.getChartConfig( {onResult: function( config_result ) {

			if ( $this.current_saved_report &&
				$this.current_saved_report.data &&
				$this.current_saved_report.data.config &&
				$this.current_saved_report.data.config.chart
				) {
				//do nothing
			} else {

				config_result = config_result.getResult();
				for ( var key in config_result ) {
					if ( $this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						$this.edit_view_ui_dic[key].setValue( config_result[ key ] );
						$this.current_edit_record[key] = config_result[ key ];
					}
				}

			}
		}} )

	},

	setTabStatus: function() {
		//Handle most cases that one tab and on audit tab
	},

	//Call this from setEditViewData
	initTabData: function() {

		//Handle most case that one tab and one audit tab
//		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
//
//		}
	},

	getReportData: function( callBack ) {

		var $this = this;
		var args = {};
		args.filter_data = {script: this.script_name};
		this.api_user_report.getUserReportData( args, {onResult: function( result ) {

			var res_data = result.getResult();
			$this.pager_data = result.getPagerData();

			callBack( res_data );
		}} );

	},

	initOptions: function( callBack ) {
		var options = [
			{option_name: 'page_orientation'},
			{option_name: 'font_size'},
			{option_name: 'chart_display_mode'},
			{option_name: 'chart_type'},
			{option_name: 'templates'},
			{option_name: 'setup_fields'}

		];

		this.initDropDownOptions( options, function( result ) {

			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
		} );

	},

	//Call this from setEditViewData
	initEditViewData: function() {

		var $this = this;
		var navigation_div = this.edit_view.find( '.navigation-div' );

//		if ( this.saved_report_array.length > 0 ) {
//
//
//		} else {
//			navigation_div.css( 'display', 'none' );
//		}

		navigation_div.css( 'display', 'block' );
		//Set Navigation Awesomebox

		//init navigation only when open edit view
		if ( !this.navigation.getSourceData() ) {
			this.navigation.setSourceData( this.saved_report_array );
			this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
			this.navigation.setPagerData( this.pager_data );

			var default_args = {};
			default_args.filter_data = {script: this.script_name};
			this.navigation.setDefaultArgs( default_args );
		}

		this.navigation.setValue( this.current_saved_report );

//		for ( var key in this.edit_view_ui_dic ) {
//			if ( this.edit_view_ui_dic.hasOwnProperty( key ) ) {
//				var widget = this.edit_view_ui_dic[key];
//				//Set all UI field to current edit reocrd, we need validate all UI fielld when save and validate
//				if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
//
//					var value = widget.getValue();
//					if ( !value || value === '0' || (Global.isArray( value ) && value.length === 0) ) {
//						$this.current_edit_record[key] = false;
//					} else {
//						$this.current_edit_record[key] = value;
//					}
//
//				}
//			}
//
//		}

		this.setUIWidgetFieldsToCurrentEditRecord();

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

	onRightArrowClick: function() {
		var $this = this;
		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();
		var next_select_item;

		if ( selected_index < source_data.length - 1 ) {
			next_select_item = this.navigation.getItemByIndex( selected_index + 1 );

		} else {
//			next_select_item = this.navigation.getItemByIndex( 0 );

			this.onCancelClick();
			return;
		}

		this.navigation.setValue( next_select_item );

		$this.current_saved_report = next_select_item;

		$this.current_edit_record = {};
		$this.visible_report_values = {};
		$this.initEditView();

	},

	onLeftArrowClick: function() {
		var $this = this;
		var selected_index = this.navigation.getSelectIndex();
		var source_data = this.navigation.getSourceData();
		var next_select_item;

		if ( selected_index > 0 ) {
			next_select_item = this.navigation.getItemByIndex( selected_index - 1 );
		} else {
//			next_select_item = this.navigation.getItemByIndex( source_data.length - 1 );

			this.onCancelClick();
			return;
		}

		this.navigation.setValue( next_select_item );
		$this.current_saved_report = next_select_item;
		$this.current_edit_record = {};
		$this.visible_report_values = {};
		$this.initEditView();
	},

	//Call this after initEditViewUI, usually after current_edit_record is set
	initEditView: function() {
		var $this = this;
		var current_url = window.location.href;
		if ( current_url.indexOf( '&sm' ) > 0 ) {
			current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
		}
		if ( $this.current_saved_report && $this.current_saved_report.id ) {

			current_url = current_url + '&sm=' + $this.viewId + '&sid=' + $this.current_saved_report.id;

		} else {
			current_url = current_url + '&sm=' + $this.viewId;
		}

		Global.setURLToBrowser( current_url );

		this._super( 'initEditView' );

	},

	setNavigation: function() {

		var $this = this;

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
			var next_select_item_id = target.getValue();
			$this.edit_view_error_ui_dic = {};
			if ( !next_select_item_id ) {
				$this.current_saved_report = null;
				$this.saved_report_array = [];
				$this.current_edit_record = {};
				$this.visible_report_values = {};

				$this.do_validate_after_create_ui = true;
				$this.initEditView();
				return;
			}

			if ( next_select_item_id !== $this.current_edit_record.id ) {
				$this.current_saved_report = target.getValue( true );
				$this.current_edit_record = {};
				$this.visible_report_values = {};

				$this.initEditView();
			}

		} );

	},

	initSubCustomColumnView: function( callBack ) {

		var $this = this;

		if ( this.sub_custom_column_view_controller ) {
			$this.sub_custom_column_view_controller.edit_only_mode = false;
			$this.sub_custom_column_view_controller.buildContextMenu( true );
			$this.sub_custom_column_view_controller.parent_value = this.script_name;
			$this.sub_custom_column_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadViewSource( 'CustomColumn', 'CustomColumnViewController.js', function() {

			var tabIndex = 3;
			if ( $this.include_form_setup ) {
				tabIndex = 4;
			}

			var tab = $this.edit_view_tab.find( '#tab' + tabIndex );

			var firstColumn = tab.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'ReportSchedule' + 'View' );
			CustomColumnViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_custom_column_view_controller = subViewController;
			$this.sub_custom_column_view_controller.parent_key = 'script';
			$this.sub_custom_column_view_controller.parent_value = $this.script_name;
			$this.sub_custom_column_view_controller.parent_view_controller = $this;
			$this.sub_custom_column_view_controller.edit_only_mode = false;

			//init complete
			if ( callBack ) {
				callBack(); // Call back decide call init or not
			} else {
				$this.sub_custom_column_view_controller.initData(); //Init data in this parent view
			}
		}
	},

	onSavedReportDelete: function() {
		this.refreshNav();
	},

	initSubSavedReportView: function( callBack ) {

		var $this = this;

		if ( this.sub_saved_report_view_controller ) {
			$this.sub_saved_report_view_controller.edit_only_mode = false;
			$this.sub_saved_report_view_controller.buildContextMenu( true );
			$this.sub_saved_report_view_controller.parent_value = this.script_name;
			$this.sub_saved_report_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadViewSource( 'SavedReport', 'SavedReportViewController.js', function() {
			var tabIndex = 4;
			if ( $this.include_form_setup ) {
				tabIndex = 5;
			}

			var tab = $this.edit_view_tab.find( '#tab' + tabIndex );

			var firstColumn = tab.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'ReportSchedule' + 'View' );
			SavedReportViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_saved_report_view_controller = subViewController;
			$this.sub_saved_report_view_controller.parent_key = 'script';
			$this.sub_saved_report_view_controller.parent_value = $this.script_name;
			$this.sub_saved_report_view_controller.parent_view_controller = $this;
			$this.sub_saved_report_view_controller.edit_only_mode = false;

			//init complete
			if ( callBack ) {
				callBack(); // Call back decide call init or not
			} else {
				$this.sub_saved_report_view_controller.initData(); //Init data in this parent view
			}
		}
	},

	buildEditViewUI: function() {
		var $this = this;

		var navigation_div = this.edit_view.find( '.navigation-div' );
		var label = navigation_div.find( '.navigation-label' );
		var left_click = navigation_div.find( '.left-click' );
		var right_click = navigation_div.find( '.right-click' );
		var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );

		this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		navigation_widget_div.append( this.navigation );

		left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
		right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );

		label.text( this.navigation_label );

		navigation_widget_div.append( this.navigation );

		var close_icon = this.edit_view.find( '.close-icon' );

		close_icon.click( function() {
			$this.onCloseIconClick();
		} );

		var tab_0_label = this.edit_view.find( 'a[ref=tab_report]' );
		var tab_1_label = this.edit_view.find( 'a[ref=tab_setup]' );
		var tab_2_label = this.edit_view.find( 'a[ref=tab_chart]' );
		var tab_3_label = this.edit_view.find( 'a[ref=tab3]' );
		var tab_4_label = this.edit_view.find( 'a[ref=tab4]' );

		if ( this.include_form_setup ) {

			var tab_5_label = this.edit_view.find( 'a[ref=tab5]' );

			tab_0_label.text( $.i18n._( 'Report' ) );
			tab_1_label.text( $.i18n._( 'Setup' ) );
			tab_2_label.text( $.i18n._( 'Chart' ) );
			tab_3_label.text( $.i18n._( 'Form Setup' ) );
			tab_4_label.text( $.i18n._( 'Custom Columns' ) );
			tab_5_label.text( $.i18n._( 'Saved Reports' ) );

			this.buildFormSetupUI();

		} else {
			tab_0_label.text( $.i18n._( 'Report' ) );
			tab_1_label.text( $.i18n._( 'Setup' ) );
			tab_2_label.text( $.i18n._( 'Chart' ) );
			tab_3_label.text( $.i18n._( 'Custom Columns' ) );
			tab_4_label.text( $.i18n._( 'Saved Reports' ) );
		}

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserReportData' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER_REPORT_DATA,
			navigation_mode: true,
			show_search_inputs: true,
			set_empty: true,
			always_search_full_columns: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_report = this.edit_view_tab.find( '#tab_report' );

		var tab0_column1 = tab_report.find( '.first-column' );

		// Template
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'template', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.templates_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Template' ), form_item_input, tab0_column1 );

		//Tab 1 start
		var tab_setup = this.edit_view_tab.find( '#tab_setup' );
		var tab1_column1 = tab_setup.find( '.first-column' );

		//Fields
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			key: 'value',
			layout_name: ALayoutIDs.OPTION_COLUMN,
			allow_drag_to_order: true,
			set_empty: true,
			field: 'setup_field'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Fields' ), form_item_input, tab1_column1, '' );
		this.setup_fields_array.shift();
		form_item_input.setSourceData( this.setup_fields_array );

		//Page Orientation
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'page_orientation', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.page_orientation_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Page Orientation' ), form_item_input, tab1_column1 );

		//Font Size
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'font_size', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.font_size_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Font Size' ), form_item_input, tab1_column1 );

		//Disable Grand Total
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'disable_grand_total'} );
		this.addEditFieldToColumn( $.i18n._( 'Disable Grand Total' ), form_item_input, tab1_column1 );

		//Show Duplicate Values
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'show_duplicate_values'} );
		this.addEditFieldToColumn( $.i18n._( 'Show Duplicate Values' ), form_item_input, tab1_column1 );

		//Maximum Pages
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_page_limit', width: 50} );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Pages' ), form_item_input, tab1_column1 );

		//Tab 2 start
		var tab_chart = this.edit_view_tab.find( '#tab_chart' );
		var tab2_column1 = tab_chart.find( '.first-column' );

		//Enable
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'enable'} );
		this.addEditFieldToColumn( $.i18n._( 'Enable' ), form_item_input, tab2_column1, '' );

		//Display
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'display_mode', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.chart_display_mode_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Display' ), form_item_input, tab2_column1 );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.chart_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab2_column1 );

		//Chart Sub-Totals
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'include_sub_total'} );
		this.addEditFieldToColumn( $.i18n._( 'Chart Sub-Totals' ), form_item_input, tab2_column1 );

		//Consistent Axis Scales
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'axis_scale_static'} );
		this.addEditFieldToColumn( $.i18n._( 'Consistent Axis Scales' ), form_item_input, tab2_column1 );

		//Consistent Axis Scales
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'combine_columns'} );
		this.addEditFieldToColumn( $.i18n._( 'Combine Columns' ), form_item_input, tab2_column1, '' );

	},

	buildFormSetupUI: function() {
		//Need always override
	},

	buildSelectTemplateData: function() {
		var template = this.current_saved_report.data.template;
		var config = this.current_saved_report.data.config;

		var result = {};

		for ( var i = 0; i < template.length; i++ ) {
			var item = template[i];
			if ( item === 'sort' ) {
				result[item] = config[item + '_'];
			} else {
				result[item] = config[item];
			}

		}

		return result;
	},
	/* jshint ignore:start */
	setCurrentEditRecordData: function() {

		var $this = this;

		if ( LocalCacheData.default_filter_for_next_open_view ) {

			this.do_validate_after_create_ui = false;
			this.current_edit_record['template'] = LocalCacheData.default_filter_for_next_open_view.template;
			$this.onTemplateChange( this.current_edit_record['template'] );
			LocalCacheData.default_filter_for_next_open_view = null;

		} else {
			if ( this.current_saved_report && this.current_saved_report.data ) {
				var select_template_data = this.buildSelectTemplateData();
				this.setSelectTemplate( select_template_data );

				this.current_edit_record['template'] = this.current_saved_report.data.config.template;

				for ( var other_key in this.current_saved_report.data.config.other ) {

					if ( !this.current_saved_report.data.config.other.hasOwnProperty( other_key ) ) {
						continue;
					}

					this.current_edit_record[other_key] = this.current_saved_report.data.config.other[other_key];
				}

				for ( var chart_key in this.current_saved_report.data.config.chart ) {

					if ( !this.current_saved_report.data.config.chart.hasOwnProperty( chart_key ) ) {
						continue;
					}

					this.current_edit_record[chart_key] = this.current_saved_report.data.config.chart[chart_key];
				}
			} else {

				//If no any saved report, use default setup fields
				var default_setup_fields = this.api.getOptions( 'default_setup_fields', {async: false} );

				$this.current_edit_record.setup_field = default_setup_fields.getResult();
				$this.buildReportUIBaseOnSetupFields();
			}
		}

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) )    continue;

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_id':
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'country': //popular case
						this.eSetProvince( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( this.include_form_setup ) {
			this.api.getCompanyFormConfig( {onResult: function( result ) {
				var res_Data = result.getResult();

				$this.setFormSetupData( res_Data );

			}} );
		}

		this.collectUIDataToCurrentEditRecord();

		this.setDefaultConfigData();
		this.setEditViewDataDone();

	},
	/* jshint ignore:end */
	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function() {
//		LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );

		if ( this.do_validate_after_create_ui ) {
			this.validate();
			this.do_validate_after_create_ui = false;
		}

	},
	/* jshint ignore:start */
	onFormItemChange: function( target, doNotDoValidate ) {
		var $this = this;
		this.setIsChanged( target );
		var key = target.getField();
		var time_period;
		var skill_expiry_date;
		var membership_renewal_date;
		var license_expiry_date;

		if ( this.visible_report_widgets && this.visible_report_widgets[key] ) {
			if ( key === 'sort' ) {
				this.visible_report_values[key] = target.getValue( true );

			} else if ( key === 'time_period' ) {

				time_period = target.getValue();

				if ( !this.visible_report_values[key] || this.visible_report_values[key].time_period !== time_period ) {
					this.visible_report_values[key] = {time_period: time_period};
					this.onTimePeriodChange( target );
				}

			} else if ( key === 'start_date' || key === 'end_date' || key === 'pay_period_id' || key === 'pay_period_schedule_id' ) {
				time_period = this.visible_report_values['time_period'];
				time_period[key] = target.getValue();

			} else if ( key === 'membership_renewal_date' ) {
				membership_renewal_date = target.getValue();
				this.visible_report_values[key] = {time_period: membership_renewal_date};

				this.onMembershipRenewalDateChange( target );
			} else if ( key === 'start_date_1' || key === 'end_date_1' || key === 'pay_period_id_1' || key === 'pay_period_schedule_id_1' ) {
				membership_renewal_date = this.visible_report_values['membership_renewal_date'];
				membership_renewal_date[key.replace( '_1', '' )] = target.getValue();

			} else if ( key === 'skill_expiry_date' ) {
				skill_expiry_date = target.getValue();
				this.visible_report_values[key] = {time_period: skill_expiry_date};

				this.onSkillExpiryDate( target );
			} else if ( key === 'start_date_2' || key === 'end_date_2' || key === 'pay_period_id_2' || key === 'pay_period_schedule_id_2' ) {
				skill_expiry_date = this.visible_report_values['skill_expiry_date'];
				skill_expiry_date[key.replace( '_2', '' )] = target.getValue();

			} else if ( key === 'license_expiry_date' ) {
				license_expiry_date = target.getValue();
				this.visible_report_values[key] = {time_period: license_expiry_date};

				this.onLicenseExpiryDate( target );
			} else if ( key === 'start_date_3' || key === 'end_date_3' || key === 'pay_period_id_3' || key === 'pay_period_schedule_id_3' ) {
				license_expiry_date = this.visible_report_values['license_expiry_date'];
				license_expiry_date[key.replace( '_3', '' )] = target.getValue();

			} else {

				if ( target.hasClass( 't-checkbox' ) ) {
					this.visible_report_values[key] = target.getValue();
				} else {
					var value = target.getValue()
					if ( value && ($.type( value ) !== 'array' || value.length > 0) ) {
						this.visible_report_values[key] = target.getValue();
					} else {
						delete this.visible_report_values[key];
					}
				}

			}

		} else {
			this.current_edit_record[key] = target.getValue();
		}

		if ( key === 'template' ) {
			$this.onTemplateChange( this.current_edit_record[key] );
			$this.setEditMenu(); //clean error, set edit menu
		} else {

			if ( this.edit_view_tab_selected_index === 0 ) {
				if ( !doNotDoValidate ) {
					this.validate();
				}

			}

		}

		if ( this.include_form_setup && this.edit_view_tab_selected_index === 3 ) {
			this.form_setup_changed = true;
		}

	},
	/* jshint ignore:end */
	//Create first tab widget base on select template
	onTemplateChange: function( templateId ) {
		var $this = this;
		this.api.getTemplate( templateId, {onResult: function( result ) {
			var result_data = result.getResult();
			$this.setSelectTemplate( result_data );

		}} );
	},

	setSelectTemplate: function( result_data ) {
		var $this = this;
		var result = Global.buildRecordArray( result_data );

		var len = result.length;
		$this.current_edit_record.setup_field = [];
		$this.visible_report_values = {};

		for ( var i = 0; i < len; i++ ) {
			var item = result[i];

			if ( item.value === 'template' ) {
				continue;
			}

			$this.visible_report_values[item.value] = item.label; // set value to model
			$this.current_edit_record.setup_field.push( item.value );

		}

		$this.createUI( result );

	},

	getFieldLabel: function( field ) {
		var len = this.setup_fields_array.length;

		for ( var i = 0; i < len; i++ ) {
			var setup_field = this.setup_fields_array[i];
			if ( setup_field.value === field ) {
				return setup_field.label;
			}
		}
	},

	//Create widgets,
	createUI: function( uiModel ) {

		this.cleanUI();
		var $this = this;
		var len = uiModel.length;
		var tab_report = this.edit_view_tab.find( '#tab_report' );
		var tab0_column1 = tab_report.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab0_column1 );
		this.visible_report_widgets = {}; //report tab widgets
		this.edit_view_form_item_dic = {}; //Only keep report tab form item

		this.visible_report_widgets_order_fix = {};

		var last_time_visible_values = this.visible_report_values;

		this.visible_report_values = {};

		var order_fix = 1001;

		for ( var i = 0; i < len; i++ ) {
			var model = uiModel[i];

			var field = '';
			var value = '';

			//Value, label object
			if ( model.value ) {
				field = model.value;

			} else { //Mode is string
				field = model;
			}

			value = last_time_visible_values[field];
			var widget = this.getUIWidget( field );

			//Dont add field is it's not in setup fields.
			if ( !widget || !this.getFieldLabel( field ) ) {
				continue;
			}

			//Add widget first
			if ( field === 'time_period' ||
				field === 'membership_renewal_date' ||
				field === 'skill_expiry_date' ||
				field == 'license_expiry_date' ) {
				this.addEditFieldToColumn( $.i18n._( this.getFieldLabel( field ) ), widget, tab0_column1, '', null, true, true );
				$this.edit_view_form_item_dic[field].attr( 'id', 'report_' + field + '_div' );

			} else {
				this.addEditFieldToColumn( $.i18n._( this.getFieldLabel( field ) ), widget, tab0_column1, '', null, true );
			}

			//Then set Value
			if ( value ) {

				if ( field === 'time_period' ) {

					widget.setValue( value.time_period );
					$this.onTimePeriodChange( widget, value );
				} else if ( field === 'membership_renewal_date' ) {
					widget.setValue( value.time_period );
					$this.onMembershipRenewalDateChange( widget, value );
				} else if ( field === 'skill_expiry_date' ) {
					widget.setValue( value.time_period );
					$this.onSkillExpiryDate( widget, value );
				} else if ( field === 'license_expiry_date' ) {
					widget.setValue( value.time_period );
					$this.onLicenseExpiryDate( widget, value );
				} else if ( field === 'filter' ) {
					$this.setFilterValue( widget, value );
				} else if ( field === 'sort' ) {
					widget.setValue( value );
				} else {
					widget.setValue( value );
				}

			}

			// then init source options

			this.initSourceData( field, widget );

			delete this.current_edit_record[field];

			if ( widget.hasClass( 't-checkbox' ) ) {
				this.visible_report_values[field] = value;
			} else if ( value ) {
				this.visible_report_values[field] = value;
			}

			this.visible_report_widgets[field] = widget;

			this.visible_report_widgets_order_fix[field] = order_fix;

			order_fix = order_fix + 1;

		}

		this.setEditViewWidgetsMode();

		this.need_refresh_display_columns = false;

		this.editFieldResize( 0 );

	},

	onTabIndexChange: function( e, ui ) {

	},
	/* jshint ignore:start */
	onTabShow: function( e, ui ) {
		var $this = this;
		var key = ui.index;

		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		var last_index = this.edit_view_tab_selected_index;

		this.edit_view_tab_selected_index = ui.index;

		if ( !this.include_form_setup ) {
			if ( (last_index === 1 || this.need_refresh_display_columns) && ui.index === 0 ) {
				this.buildReportUIBaseOnSetupFields();
				this.buildContextMenu( true );
				this.setEditMenu();
			} else if ( ui.index === 1 ) {
				this.edit_view_ui_dic.setup_field.setValue( this.current_edit_record.setup_field );
				this.buildContextMenu( true );
				this.setEditMenu();
			} else if ( ui.index === 2 ) {
				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
				} else {
					this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
				}
			} else if ( ui.index === 3 ) {
				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					this.edit_view_tab.find( '#tab3' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.initSubCustomColumnView();
				} else {
					this.edit_view_tab.find( '#tab3' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

				}

			} else if ( ui.index === 4 ) {
				this.initSubSavedReportView();
			} else {
				this.buildContextMenu( true );
				this.setEditMenu();
			}
		} else {
			if ( (last_index === 1 || this.need_refresh_display_columns) && ui.index === 0 ) {
				this.buildReportUIBaseOnSetupFields();
				this.buildContextMenu( true );
				this.setEditMenu();
			} else if ( ui.index === 1 ) {
				this.edit_view_ui_dic.setup_field.setValue( this.current_edit_record.setup_field );
				this.buildContextMenu( true );
				this.setEditMenu();
			} else if ( ui.index === 2 ) {
				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
				} else {
					this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
				}
			} else if ( ui.index === 4 ) {
				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.initSubCustomColumnView();
				} else {
					this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

				}
			} else if ( ui.index === 5 ) {
				this.initSubSavedReportView();
			} else {
				this.buildContextMenu( true );
				this.setEditMenu();
			}

			if ( last_index === 3 && this.form_setup_changed ) {
				this.form_setup_changed = false;
				TAlertManager.showConfirmAlert( $.i18n._( 'You have modified Form Setup data without saving, would you like to save your Form Setup data now?' ), '', function( flag ) {
					if ( flag ) {
						$this.onSaveSetup();
					}
				} );
			}

		}

	},
	/* jshint ignore:end */
	cleanUI: function() {
		for ( var key in this.edit_view_form_item_dic ) {

			if ( !this.edit_view_form_item_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var html_item = this.edit_view_form_item_dic[key];
			html_item.remove();
		}

		var tab_report = this.edit_view_tab.find( '#tab_report' );

		var tab0_column1 = tab_report.find( '.first-column' );

		var clear_both_div = tab0_column1.find( '.clear-both-div' );

		clear_both_div.remove();

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_custom_column_view_controller = null;
		this.sub_saved_report_view_controller = null;
		LocalCacheData.current_open_report_controller = null;

	},
	/* jshint ignore:start */
	//Get Widget base on field
	getUIWidget: function( field ) {

		var widget;

		switch ( field ) {
			case 'columns':
			case 'sub_total':
			case 'group':
			case 'user_review_control_type_id':
			case 'user_review_control_status_id':
			case 'severity_id':
			case 'term_id':
			case 'kpi_type_id':
			case 'kpi_status_id':
			case 'fluency_id':
			case 'qualification_type_id':
			case 'proficiency_id':
			case 'competency_id':
			case 'ownership_id':
			case 'invoice_status_id':
			case 'user_status_id':
			case 'pay_stub_status_id':
			case 'filter':
			case 'pay_period_time_sheet_verify_status_id':
			case 'job_status_id':
			case 'job_item_status_id':
			case 'client_status_id':
			case 'product_type_id':
			case 'custom_filter':
			case 'log_action_id':
			case 'log_table_name_id':
			case 'accrual_type_id':
			case 'accrual_policy_type_id':
			case 'exception_policy_severity_id':
			case 'exception_policy_type_id':
			case 'expense_policy_require_receipt_id':
			case 'expense_policy_type_id':
			case 'user_expense_payment_method_id':
			case 'user_expense_status_id':
			case 'job_applicant_sex_id':
			case 'job_applicant_status_id':
			case 'job_application_status_id':
			case 'job_application_type_id':
			case 'job_vacancy_employment_status_id':
			case 'job_vacancy_level_id':
			case 'job_vacancy_status_id':
			case 'job_vacancy_type_id':
			case 'job_vacancy_wage_type_id':
				widget = this.getSimpleTComboBox( field );
				break;
			case 'sort':
				widget = this.getSortComboBox( field );
				break;
			case 'license_expiry_date':
			case 'membership_renewal_date':
			case 'skill_expiry_date':
				widget = this.getComboBox( field );
				break;
			case 'user_group_id':
			case 'qualification_group_id':
			case 'kpi_group_id':
			case 'job_group_id':
			case 'job_item_group_id':
			case 'client_group_id':
			case 'product_group_id':
				widget = this.getTreeModeAComboBox( field );
				break;
			case 'time_period':
				widget = this.getSimpleTComboBox( field, false );
				break;
			case 'user_tag':
			case 'review_tag':
			case 'job_tag':
			case 'job_item_tag':
				widget = this.getTag( field );
				break;
			case 'include_user_id':
			case 'exclude_user_id':
			case 'client_sales_contact_id':
			case 'created_by_id':
			case 'updated_by_id':
			case 'include_reviewer_user_id':
			case 'exclude_reviewer_user_id':
			case 'job_applicant_interviewer_user_id':
			case 'job_application_interviewer_user_id':
				widget = this.getTComboBox( field, ALayoutIDs.USER, (APIFactory.getAPIClass( 'APIUser' )) );
				break;
			case 'user_title_id':
				widget = this.getTComboBox( field, ALayoutIDs.USER_TITLE, (APIFactory.getAPIClass( 'APIUserTitle' )) );
				break;
			case 'default_branch_id':
			case 'schedule_branch_id':
			case 'punch_branch_id':

				widget = this.getTComboBox( field, ALayoutIDs.BRANCH, (APIFactory.getAPIClass( 'APIBranch' )) );
				break;
			case 'default_department_id':
			case 'schedule_department_id':
			case 'punch_department_id':
				widget = this.getTComboBox( field, ALayoutIDs.DEPARTMENT, (APIFactory.getAPIClass( 'APIDepartment' )) );
				break;
			case 'include_job_id':
			case 'exclude_job_id':
				widget = this.getTComboBox( field, ALayoutIDs.JOB, (APIFactory.getAPIClass( 'APIJob' )) );
				break;
			case 'include_job_item_id':
			case 'exclude_job_item_id':
				widget = this.getTComboBox( field, ALayoutIDs.JOB_ITEM, (APIFactory.getAPIClass( 'APIJobItem' )) );
				break;
			case 'currency_id':
				widget = this.getTComboBox( field, ALayoutIDs.CURRENCY, (APIFactory.getAPIClass( 'APICurrency' )) );
				break;
			case 'include_no_data_rows':
			case 'exclude_ytd_adjustment':
			case 'show_child_expenses':
				widget = this.getCheckBox( field );
				break;
			case 'accrual_policy_id':
				widget = this.getTComboBox( field, ALayoutIDs.ACCRUAL_POLICY, (APIFactory.getAPIClass( 'APIAccrualPolicy' )) );
				break;
			case 'pay_period_id':
				widget = this.getTComboBox( field, ALayoutIDs.PAY_PERIOD, (APIFactory.getAPIClass( 'APIPayPeriod' )) );
				break;
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					widget = this.getTComboBox( field, ALayoutIDs.JOB, (APIFactory.getAPIClass( 'APIJob' )) );
				}
				break;
			case 'job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					widget = this.getTComboBox( field, ALayoutIDs.JOB_ITEM, (APIFactory.getAPIClass( 'APIJobItem' )) );
				}
				break;
			case 'expense_policy_id':
				widget = this.getTComboBox( field, ALayoutIDs.EXPENSE_POLICY, (APIFactory.getAPIClass( 'APIExpensePolicy' )) );
				break;
			case 'pay_stub_entry_account_id':
				widget = this.getTComboBox( field, ALayoutIDs.PAY_STUB_ACCOUNT, (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )) );
				break;
			case 'product_id':
			case 'exclude_product_id':
			case 'include_product_id':
				widget = this.getTComboBox( field, ALayoutIDs.PRODUCT, (APIFactory.getAPIClass( 'APIProduct' )) );
				break;
			case 'job_client_id':
			case 'exclude_client_id':
			case 'include_client_id':
				widget = this.getTComboBox( field, ALayoutIDs.CLIENT, (APIFactory.getAPIClass( 'APIClient' )) );
				break;
			case 'company_deduction_id':
				widget = this.getTComboBox( field, ALayoutIDs.COMPANY_DEDUCTION, (APIFactory.getAPIClass( 'APICompanyDeduction' )) );
				break;
			case 'qualification_id':
				widget = this.getTComboBox( field, ALayoutIDs.QUALIFICATION, (APIFactory.getAPIClass( 'APIQualification' )) );
				break;
			case 'kpi_id':
				widget = this.getTComboBox( field, ALayoutIDs.KPI, (APIFactory.getAPIClass( 'APIKPI' )) );
				break;
			case 'job_applicant_id':
				widget = this.getTComboBox( field, ALayoutIDs.JOB_APPLICANT, (APIFactory.getAPIClass( 'APIJobApplicant' )) );
				break;
			case 'job_vacancy_id':
				widget = this.getTComboBox( field, ALayoutIDs.JOB_VACANCY, (APIFactory.getAPIClass( 'APIJobVacancy' )) );
				break;
			case 'accrual_policy_account_id':
				widget = this.getTComboBox( field, ALayoutIDs.ACCRUAL_POLICY_ACCOUNT, (APIFactory.getAPIClass( 'APIAccrualPolicyAccount' )) );
				break;

			default:

				if ( !Global.isSet( ReportBaseViewController.ReportMissedField ) ) {
					ReportBaseViewController.ReportMissedField = {};
				}

				ReportBaseViewController.ReportMissedField[field] = true;

				break;

		}

		return widget;
	},
	/* jshint ignore:end */
	getTag: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		widget.TTagInput( {field: field} );

		return widget;
	},

	getTreeModeAComboBox: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		widget = widget.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: field
		} );

		return widget;
	},

	getCheckBox: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.CHECKBOX );

		widget = widget.TCheckbox( {
			field: field
		} );

		return widget;
	},

	getTComboBox: function( field, layoutName, apiClass ) {

		var widget = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		widget = widget.AComboBox( {
			api_class: apiClass,
			allow_multiple_selection: true,
			layout_name: layoutName,
			show_search_inputs: true,
			set_empty: true,
			field: field
		} );

		return widget;
	},

	getSortComboBox: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		widget = widget.AComboBox( {
			field: field,
			allow_drag_to_order: true,
			allow_multiple_selection: true,
			set_empty: true,
			layout_name: ALayoutIDs.SORT_COLUMN
		} );

		return widget;
	},

	getSimpleTComboBox: function( field, allowMultiple ) {

		if ( !Global.isSet( allowMultiple ) ) {
			allowMultiple = true;
		}

		var widget = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		widget = widget.AComboBox( {
			field: field,
			set_empty: true,
			allow_multiple_selection: allowMultiple,
			layout_name: ALayoutIDs.OPTION_COLUMN,
			key: 'value'
		} );

		return widget;

	},

	getComboBox: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		widget = widget.TComboBox( {
			field: field,
			set_empty: true
		} );

		return widget;
	},
	/* jshint ignore:start */
	initSourceData: function( field, widget ) {

		var api_instance = null;
		var option = '';
		var $this = this;

		switch ( field ) {
			case 'sort':
				api_instance = this.api;
				option = 'columns';

				api_instance.getOptions( option, {onResult: function( result ) {
					onResult( result );
				} } );

				break;
			case 'kpi_group_id':
				new (APIFactory.getAPIClass( 'APIKPIGroup' ))().getKPIGroup( '', false, false, {onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;

			case 'qualification_group_id':
				new (APIFactory.getAPIClass( 'APIQualificationGroup' ))().getQualificationGroup( '', false, false, {onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'product_group_id':
				new (APIFactory.getAPIClass( 'APIProductGroup' ))().getProductGroup( '', false, false, {onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'client_group_id':
				new (APIFactory.getAPIClass( 'APIClientGroup' ))().getClientGroup( '', false, false, {onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'user_group_id':
				new (APIFactory.getAPIClass( 'APIUserGroup' ))().getUserGroup( '', false, false, {onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'job_group_id':
				new (APIFactory.getAPIClass( 'APIJobGroup' ))().getJobGroup( '', false, false, {onResult: function( res ) {
					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'job_item_group_id':
				new (APIFactory.getAPIClass( 'APIJobItemGroup' ))().getJobItemGroup( '', false, false, {onResult: function( res ) {
					res = res.getResult();
					res = Global.buildTreeRecord( res );
					widget.setSourceData( res );

				}} );
				break;
			case 'job_vacancy_employment_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				option = 'employment_status';
				break;
			case 'job_vacancy_level_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				option = 'level';
				break;
			case 'job_vacancy_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				option = 'status';
				break;
			case 'job_vacancy_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				option = 'type';
				break;
			case 'job_vacancy_wage_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				option = 'wage_type';
				break;
			case 'job_application_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobApplication' ))();
				option = 'status';
				break;
			case 'job_application_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobApplication' ))();
				option = 'type';
				break;
			case 'job_applicant_sex_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobApplicant' ))();
				option = 'sex';
				break;
			case 'job_applicant_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobApplicant' ))();
				option = 'status';
				break;
			case 'user_review_control_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
				option = 'type';
				break;
			case 'user_review_control_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
				option = 'status';
				break;
			case 'severity_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
				option = 'severity';
				break;
			case 'term_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
				option = 'term';
				break;
			case 'kpi_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIKPI' ))();
				option = 'status';
				break;
			case 'kpi_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIKPI' ))();
				option = 'type';
				break;
			case 'proficiency_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserSkill' ))();
				option = 'proficiency';
				break;
			case 'fluency_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserLanguage' ))();
				option = 'fluency';
				break;
			case 'competency_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserLanguage' ))();
				option = 'competency';
				break;
			case 'user_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUser' ))();
				option = 'status';
				break;
			case 'pay_stub_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIPayStub' ))();
				option = 'status';
				break;
			case 'ownership_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserMembership' ))();
				option = 'ownership';
				break;
			case 'license_expiry_date':
				api_instance = this.api;
				option = 'license_expiry_date';
				break;
			case 'membership_renewal_date':
				api_instance = this.api;
				option = 'membership_renewal_date';
				break;
			case 'skill_expiry_date':
				api_instance = this.api;
				option = 'skill_expiry_date';
				break;
			case 'group':
			case 'sub_total':

				api_instance = this.api;
				option = 'static_columns';

				break;
			case 'pay_period_time_sheet_verify_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APITimeSheetVerify' ))();
				option = 'status';
				break;
			case 'job_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJob' ))();
				option = 'status';

				break;
			case 'custom_filter':

				api_instance = this.api;
				option = 'report_custom_filters';

				break;

			case 'log_action_id':

				api_instance = new (APIFactory.getAPIClass( 'APILog' ))();
				option = 'action';

				break;
			case 'log_table_name_id':

				api_instance = new (APIFactory.getAPIClass( 'APILog' ))();
				option = 'table_name';

				break;
			case 'filter':
				if ( this.script_name === 'ScheduleSummaryReport' ) {
					api_instance = new (APIFactory.getAPIClass( 'APISchedule' ))();
					option = 'status';
				} else if ( this.script_name === 'InvoiceTransactionSummaryReport' ) {
					api_instance = new (APIFactory.getAPIClass( 'APITransaction' ))();
					option = 'type';
				} else if ( this.script_name === 'PayStubSummaryReport' ) {
					api_instance = new (APIFactory.getAPIClass( 'APIPayStub' ))();
					option = 'status';
				}

				break;
			case 'accrual_policy_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIAccrualPolicy' ))();
				option = 'type';
				break;
			case 'accrual_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIAccrual' ))();
				option = 'type';
				break;
			case 'qualification_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIQualification' ))();
				option = 'type';
				break;
			case 'exception_policy_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIExceptionPolicy' ))();
				option = 'type';
				break;
			case 'exception_policy_severity_id':
				api_instance = new (APIFactory.getAPIClass( 'APIExceptionPolicy' ))();
				option = 'severity';
				break;
			case 'expense_policy_require_receipt_id':
				api_instance = new (APIFactory.getAPIClass( 'APIExpensePolicy' ))();
				option = 'require_receipt';
				break;
			case 'expense_policy_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIExpensePolicy' ))();
				option = 'type';
				break;
			case 'user_expense_payment_method_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserExpense' ))();
				option = 'payment_method';
				break;
			case 'user_expense_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIUserExpense' ))();
				option = 'status';
				break;
			case 'job_item_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIJobItem' ))();
				option = 'status';
				break;
			case 'client_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIClient' ))();
				option = 'status';
				break;
			case 'invoice_status_id':
				api_instance = new (APIFactory.getAPIClass( 'APIInvoice' ))();
				option = 'status';
				break;
			case 'invoice_transaction_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APITransaction' ))();
				option = 'type';
				break;
			case 'product_type_id':
				api_instance = new (APIFactory.getAPIClass( 'APIProduct' ))();
				option = 'type';
				break;
			default:
				//Don't deal with awesomebox with api
				if ( widget.getAPI && widget.getAPI() ) {
					return;
				}

				//Text Input or other no options widget
				if ( !widget.setSourceData ) {
					return;
				}

				field.replace( '_id', '' );

				api_instance = this.api;
				option = field;

				break;
		}

		if ( api_instance ) {

			if ( this.need_refresh_display_columns && option === 'columns' ) {
				api_instance.getOptions( option, {noCache: true, onResult: function( result ) {

					onResult( result );
				} } );
			} else {
				api_instance.getOptions( option, {onResult: function( result ) {
					onResult( result );
				} } );
			}

		}

		function onResult( result ) {

			var res_data = result.getResult();
			res_data = Global.buildRecordArray( res_data );
			if ( field === 'sort' ) {
				res_data = $this.buildSortSelectorUnSelectColumns( res_data );
			} else if ( field === 'time_period' ) {
				this.time_period_array = res_data;
			}

			widget.setSourceData( res_data );

		}

	},
	/* jshint ignore:end */
	getDatePicker: function( field ) {
		var widget = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		widget.TDatePicker( {field: field} );

		return widget;
	},

	putInputToInsideFormItem: function( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) + ': ' );
		form_item_input_div.append( form_item_input );

		return form_item;
	},

	onLicenseExpiryDate: function( target, defaultValue ) {

		var $this = this;
		var value = target.getValue();

		this.visible_report_widgets.license_expiry_date = null;
		this.visible_report_widgets.start_date_3 = null;
		this.visible_report_widgets.end_date_3 = null;
		this.visible_report_widgets.pay_period_id_3 = null;
		this.visible_report_widgets.pay_period_schedule_id_3 = null;

		if ( value === 'custom_date' ) {
			buildCustomDateUI();
		} else if ( value === 'custom_pay_period' ) {
			buildPayPeriodUI();
		} else if ( value === 'this_pay_period' || value === 'last_pay_period' || value === 'to_last_pay_period' || value === 'to_this_pay_period' ) {
			buildPayPeriodScheduleUI();
		} else {
			buildDefaultUI();
		}

		function buildPayPeriodScheduleUI() {
			var form_item_div = ($this.edit_view).find( '#report_license_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'license_expiry_date', false, false );
			$this.initSourceData( 'license_expiry_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_schedule_id_3', ALayoutIDs.PAY_PERIOD_SCHEDULE, (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )) );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period Schedule' ) );

			$this.visible_report_widgets.license_expiry_date = time_period;
			$this.visible_report_widgets.pay_period_schedule_id_3 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_schedule_id );
			}

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 120 );

			form_input_div.append( v_box );
		}

		function buildPayPeriodUI() {
			var form_item_div = ($this.edit_view).find( '#report_license_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'license_expiry_date', false, false );
			$this.initSourceData( 'license_expiry_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_id_3', ALayoutIDs.PAY_PERIOD, (APIFactory.getAPIClass( 'APIPayPeriod' )) );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period' ) );

			$this.visible_report_widgets.license_expiry_date = time_period;
			$this.visible_report_widgets.pay_period_id_3 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_id );
			}

			form_input_div.append( v_box );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

		}

		function buildDefaultUI() {
			var form_item_div = ($this.edit_view).find( '#report_license_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var time_period = $this.getSimpleTComboBox( 'license_expiry_date', false, false );

			form_input_div.append( time_period );

			time_period.setValue( value );

			$this.initSourceData( 'license_expiry_date', time_period );

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			$this.visible_report_widgets.license_expiry_date = time_period;

		}

		function buildCustomDateUI() {
			var form_item_div = ($this.edit_view).find( '#report_license_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'license_expiry_date', false, false );
			$this.initSourceData( 'license_expiry_date', time_period );
			time_period.setValue( value );

			var start_date = $this.getDatePicker( 'start_date_3' );

			var end_date = $this.getDatePicker( 'end_date_3' );

			if ( defaultValue ) {
				start_date.setValue( defaultValue.start_date );
				end_date.setValue( defaultValue.end_date );
			}

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( start_date, $.i18n._( 'Start Date' ) );
			var form_item3 = $this.putInputToInsideFormItem( end_date, $.i18n._( 'End Date' ) );

			$this.visible_report_widgets.license_expiry_date = time_period;
			$this.visible_report_widgets.start_date_3 = start_date;
			$this.visible_report_widgets.end_date_3 = end_date;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			start_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			end_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item3 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

			form_input_div.append( v_box );

		}

	},

	onSkillExpiryDate: function( target, defaultValue ) {

		var $this = this;
		var value = target.getValue();

		this.visible_report_widgets.skill_expiry_date = null;
		this.visible_report_widgets.start_date_2 = null;
		this.visible_report_widgets.end_date_2 = null;
		this.visible_report_widgets.pay_period_id_2 = null;
		this.visible_report_widgets.pay_period_schedule_id_2 = null;

		if ( value === 'custom_date' ) {
			buildCustomDateUI();
		} else if ( value === 'custom_pay_period' ) {
			buildPayPeriodUI();
		} else if ( value === 'this_pay_period' || value === 'last_pay_period' || value === 'to_last_pay_period' || value === 'to_this_pay_period' ) {
			buildPayPeriodScheduleUI();
		} else {
			buildDefaultUI();
		}

		function buildPayPeriodScheduleUI() {
			var form_item_div = ($this.edit_view).find( '#report_skill_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'skill_expiry_date', false, false );
			$this.initSourceData( 'skill_expiry_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_schedule_id_2', ALayoutIDs.PAY_PERIOD_SCHEDULE, (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )) );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period Schedule' ) );

			$this.visible_report_widgets.skill_expiry_date = time_period;
			$this.visible_report_widgets.pay_period_schedule_id_2 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_schedule_id );
			}

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 120 );

			form_input_div.append( v_box );
		}

		function buildPayPeriodUI() {
			var form_item_div = ($this.edit_view).find( '#report_skill_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'skill_expiry_date', false, false );
			$this.initSourceData( 'skill_expiry_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_id_2', ALayoutIDs.PAY_PERIOD, (APIFactory.getAPIClass( 'APIPayPeriod' )) );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period' ) );

			$this.visible_report_widgets.skill_expiry_date = time_period;
			$this.visible_report_widgets.pay_period_id_2 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_id );
			}

			form_input_div.append( v_box );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

		}

		function buildDefaultUI() {
			var form_item_div = ($this.edit_view).find( '#report_skill_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var time_period = $this.getSimpleTComboBox( 'skill_expiry_date', false, false );

			form_input_div.append( time_period );

			time_period.setValue( value );

			$this.initSourceData( 'skill_expiry_date', time_period );

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			$this.visible_report_widgets.skill_expiry_date = time_period;

		}

		function buildCustomDateUI() {
			var form_item_div = ($this.edit_view).find( '#report_skill_expiry_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'skill_expiry_date', false, false );
			$this.initSourceData( 'skill_expiry_date', time_period );
			time_period.setValue( value );

			var start_date = $this.getDatePicker( 'start_date_2' );

			var end_date = $this.getDatePicker( 'end_date_2' );

			if ( defaultValue ) {
				start_date.setValue( defaultValue.start_date );
				end_date.setValue( defaultValue.end_date );
			}

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( start_date, $.i18n._( 'Start Date' ) );
			var form_item3 = $this.putInputToInsideFormItem( end_date, $.i18n._( 'End Date' ) );

			$this.visible_report_widgets.skill_expiry_date = time_period;
			$this.visible_report_widgets.start_date_2 = start_date;
			$this.visible_report_widgets.end_date_2 = end_date;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			start_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			end_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item3 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

			form_input_div.append( v_box );

		}

	},

	onMembershipRenewalDateChange: function( target, defaultValue ) {

		var $this = this;
		var value = target.getValue();

		this.visible_report_widgets.membership_renewal_date = null;
		this.visible_report_widgets.start_date_1 = null;
		this.visible_report_widgets.end_date_1 = null;
		this.visible_report_widgets.pay_period_id_1 = null;
		this.visible_report_widgets.pay_period_schedule_id_1 = null;

		if ( value === 'custom_date' ) {
			buildCustomDateUI();
		} else if ( value === 'custom_pay_period' ) {
			buildPayPeriodUI();
		} else if ( value === 'this_pay_period' || value === 'last_pay_period' || value === 'to_last_pay_period' || value === 'to_this_pay_period' ) {
			buildPayPeriodScheduleUI();
		} else {
			buildDefaultUI();
		}

		function buildPayPeriodScheduleUI() {
			var form_item_div = ($this.edit_view).find( '#report_membership_renewal_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'membership_renewal_date', false, false );
			$this.initSourceData( 'membership_renewal_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_schedule_id_1', ALayoutIDs.PAY_PERIOD_SCHEDULE, (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )) );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period Schedule' ) );

			$this.visible_report_widgets.membership_renewal_date = time_period;
			$this.visible_report_widgets.pay_period_schedule_id_1 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_schedule_id );
			}

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 120 );

			form_input_div.append( v_box );
		}

		function buildPayPeriodUI() {
			var form_item_div = ($this.edit_view).find( '#report_membership_renewal_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'membership_renewal_date', false, false );
			$this.initSourceData( 'membership_renewal_date', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_id_1', ALayoutIDs.PAY_PERIOD, (APIFactory.getAPIClass( 'APIPayPeriod' )) );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period' ) );

			$this.visible_report_widgets.membership_renewal_date = time_period;
			$this.visible_report_widgets.pay_period_id_1 = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_id );
			}

			form_input_div.append( v_box );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

		}

		function buildDefaultUI() {
			var form_item_div = ($this.edit_view).find( '#report_membership_renewal_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var time_period = $this.getSimpleTComboBox( 'membership_renewal_date', false, false );

			form_input_div.append( time_period );

			time_period.setValue( value );

			$this.initSourceData( 'membership_renewal_date', time_period );

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			$this.visible_report_widgets.membership_renewal_date = time_period;

		}

		function buildCustomDateUI() {
			var form_item_div = ($this.edit_view).find( '#report_membership_renewal_date_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'membership_renewal_date', false, false );
			$this.initSourceData( 'membership_renewal_date', time_period );
			time_period.setValue( value );

			var start_date = $this.getDatePicker( 'start_date_1' );

			var end_date = $this.getDatePicker( 'end_date_1' );

			if ( defaultValue ) {
				start_date.setValue( defaultValue.start_date );
				end_date.setValue( defaultValue.end_date );
			}

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( start_date, $.i18n._( 'Start Date' ) );
			var form_item3 = $this.putInputToInsideFormItem( end_date, $.i18n._( 'End Date' ) );

			$this.visible_report_widgets.membership_renewal_date = time_period;
			$this.visible_report_widgets.start_date_1 = start_date;
			$this.visible_report_widgets.end_date_1 = end_date;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			start_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			end_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item3 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 62 );

			form_input_div.append( v_box );

		}

	},

	onTimePeriodChange: function( target, defaultValue ) {
		var $this = this;
		var value = target.getValue();

		this.visible_report_widgets.time_period = null;
		this.visible_report_widgets.start_date = null;
		this.visible_report_widgets.end_date = null;
		this.visible_report_widgets.pay_period_id = null;
		this.visible_report_widgets.pay_period_schedule_id = null;

		if ( value === 'custom_date' ) {
			buildCustomDateUI();
		} else if ( value === 'custom_pay_period' ) {
			buildPayPeriodUI();
		} else if ( value === 'this_pay_period' || value === 'last_pay_period' || value === 'to_last_pay_period' || value === 'to_this_pay_period' ) {
			buildPayPeriodScheduleUI();
		} else {
			buildDefaultUI();
		}

		function buildPayPeriodScheduleUI() {
			var form_item_div = ($this.edit_view).find( '#report_time_period_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'time_period', false, false );
			$this.initSourceData( 'time_period', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_schedule_id', ALayoutIDs.PAY_PERIOD_SCHEDULE, (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )) );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period Schedule' ) );

			$this.visible_report_widgets.time_period = time_period;
			$this.visible_report_widgets.pay_period_schedule_id = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_schedule_id );
			}

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 120 );

			form_input_div.append( v_box );
		}

		function buildPayPeriodUI() {
			var form_item_div = ($this.edit_view).find( '#report_time_period_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'time_period', false, false );
			$this.initSourceData( 'time_period', time_period );
			time_period.setValue( value );

			var pay_period = $this.getTComboBox( 'pay_period_id', ALayoutIDs.PAY_PERIOD, (APIFactory.getAPIClass( 'APIPayPeriod' )) );

			pay_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( pay_period, $.i18n._( 'Pay Period' ) );

			$this.visible_report_widgets.time_period = time_period;
			$this.visible_report_widgets.pay_period_id = pay_period;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			if ( defaultValue ) {
				pay_period.setValue( defaultValue.pay_period_id );
			}

			form_input_div.append( v_box );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 70 );

		}

		function buildDefaultUI() {
			var form_item_div = ($this.edit_view).find( '#report_time_period_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var time_period = $this.getSimpleTComboBox( 'time_period', false, false );

			form_input_div.append( time_period );

			time_period.setValue( value );

			$this.initSourceData( 'time_period', time_period );

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			$this.visible_report_widgets.time_period = time_period;

		}

		function buildCustomDateUI() {
			var form_item_div = ($this.edit_view).find( '#report_time_period_div' );
			var form_input_div = $( form_item_div.children()[1] );
			form_input_div.empty();

			var v_box = $( "<div class='v-box'></div>" );

			var time_period = $this.getSimpleTComboBox( 'time_period', false, false );
			$this.initSourceData( 'time_period', time_period );
			time_period.setValue( value );

			var start_date = $this.getDatePicker( 'start_date' );

			var end_date = $this.getDatePicker( 'end_date' );

			if ( defaultValue ) {
				start_date.setValue( defaultValue.start_date );
				end_date.setValue( defaultValue.end_date );
			}

			var form_item = $this.putInputToInsideFormItem( time_period, $.i18n._( 'Section' ) );
			var form_item2 = $this.putInputToInsideFormItem( start_date, $.i18n._( 'Start Date' ) );
			var form_item3 = $this.putInputToInsideFormItem( end_date, $.i18n._( 'End Date' ) );

			$this.visible_report_widgets.time_period = time_period;
			$this.visible_report_widgets.start_date = start_date;
			$this.visible_report_widgets.end_date = end_date;

			time_period.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			start_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			end_date.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				$this.onFormItemChange( target );
			} );

			v_box.append( form_item );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item2 );
			v_box.append( "<div class='clear-both-div'></div>" );
			v_box.append( form_item3 );
			v_box.append( "<div class='clear-both-div'></div>" );

			$this.setEditFieldSize( v_box.find( '.edit-view-form-item-sub-label-div > span' ), 62 );

			form_input_div.append( v_box );

		}

	},

	//Override this if more than one tab
	setErrorTips: function( result, dont_switch_tab ) {
		this.clearErrorTips();

		var details = result.getDetails();
		var error_list = details[0];

		var found_in_current_tab = false;

		var tab_report = this.edit_view_tab.find( '#tab_report' );
		var tab_setup = this.edit_view_tab.find( '#tab_setup' );

		for ( var key in error_list ) {

			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}

			if ( !Global.isSet( this.edit_view_ui_dic[key] ) ) {
				continue;
			}

			if ( key === 'time_period' ||
				key === 'membership_renewal_date' ||
				key === 'skill_expiry_date' ||
				key === 'license_expiry_date' ) {
				if ( this.visible_report_widgets[key] && this.visible_report_widgets[key].is( ':visible' ) ) {
					this.visible_report_widgets[key].setErrorStyle( error_list[key], true );
					found_in_current_tab = true;
				} else if ( this.visible_report_widgets[key] ) {
					this.visible_report_widgets[key].setErrorStyle( error_list[key] );
				}
				this.edit_view_error_ui_dic[key] = this.visible_report_widgets[key];
			} else {
				if ( this.edit_view_ui_dic[key].is( ':visible' ) ) {

					this.edit_view_ui_dic[key].setErrorStyle( error_list[key], true );
					found_in_current_tab = true;

				} else {

					this.edit_view_ui_dic[key].setErrorStyle( error_list[key] );
				}
				this.edit_view_error_ui_dic[key] = this.edit_view_ui_dic[key];
			}

		}

		if ( !found_in_current_tab ) {

			this.showEditViewError( result );

		}
	},

	buildSortSelectorUnSelectColumns: function( display_columns ) {
		var fina_array = [];
		var i = 100;
		$.each( display_columns, function( index, content ) {
			var new_content = $.extend( {}, content );
			new_content.id = i; //Need
			new_content.sort = 'asc';
			fina_array.push( new_content );
			i = i + 1;
		} );

		return fina_array;
	},

	setEditViewWidgetsMode: function() {

		var did_clean_dic = {};

		for ( var key in this.edit_view_ui_dic ) {
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );

			var column = widget.parent().parent().parent();

			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {

				if ( !did_clean_dic[tab_id] ) {
					column.find( '.edit-view-form-item-label-div-first-row' ).removeClass( 'edit-view-form-item-label-div-first-row' );
					column.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
					column.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
					did_clean_dic[tab_id] = true;
				}

				var child_length = column.children().length;
				var parent_div = widget.parent().parent();

				if ( child_length === 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				} else if ( parent_div.index() === 0 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
				} else if ( parent_div.index() === child_length - 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				}

				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}

		}

	},

	buildReportUIBaseOnSetupFields: function() {
		var setup_field = this.current_edit_record.setup_field;
		if ( setup_field && setup_field.length > 0 ) {
			this.createUI( setup_field );
		}

	},

	getFormValues: function() {

		var other = {};

		other.page_orientation = this.current_edit_record.page_orientation;
		other.font_size = this.current_edit_record.font_size;
		other.disable_grand_total = this.current_edit_record.disable_grand_total;
		other.maximum_page_limit = this.current_edit_record.maximum_page_limit;
		other.show_duplicate_values = this.current_edit_record.show_duplicate_values;

		if ( this.current_saved_report ) {
			other.report_name = this.current_saved_report.name;
		}

		return other;
	},

	getChartValues: function() {

		var chart = {};

		chart.enable = this.current_edit_record.enable;
		chart.display_mode = this.current_edit_record.display_mode;
		chart.type = this.current_edit_record.type;
		chart.include_sub_total = this.current_edit_record.include_sub_total;
		chart.axis_scale_static = this.current_edit_record.axis_scale_static;
		chart.combine_columns = this.current_edit_record.combine_columns;

		return chart;
	},

	convertSortValues: function( sort ) {

		var result = [];
		for ( var i = 0; i < sort.length; i++ ) {
			var item = sort[i];

			if ( !Global.isSet( item.fullValue ) ) {
				result = sort;
				break;
			} else {
				var new_item = {};
				new_item[item.value] = item.sort;
				result.push( new_item );
			}
		}

		return result;
	},

	addOrderFix: function( report ) {

		var new_report_fields = {};

		for ( var key in report ) {

			if ( !report.hasOwnProperty( key ) ) {
				continue;
			}

			var order_fix = this.visible_report_widgets_order_fix[key];

			if ( order_fix > 0 ) {
				new_report_fields['-' + order_fix + '-' + key ] = report[key];
			}
		}

		return new_report_fields;
	},

	//Make sure this.current_edit_record is updated before validate
	validate: function() {
		var $this = this;
		var other = this.getFormValues();
		var chart = this.getChartValues();
		var report = this.visible_report_values;
		if ( report.sort ) {
			report.sort = this.convertSortValues( report.sort );
		}

		report = this.addOrderFix( report );

		var config = report;
		config['-' + 1000 + '-' + 'template'] = this.current_edit_record.template;
		config.other = other;
		config.chart = chart;

		if ( report.sort ) {
			report.sort = this.convertSortValues( report.sort );
		}
		this.api['validateReport']( config, 'pdf', {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onViewExcelClick: function() {

		var config = this.getPostReportJson();
		var post_data = {0: config, 1: 'csv'};

		if ( this.include_form_setup ) {

			if ( this.show_empty_message ) {
				TAlertManager.showAlert( $.i18n._( 'Setup data for this report has not been completed yet. Please click on the Form Setup tab to do so now.' ) );
				return;
			}

			config.form = this.getFormSetupData( true );
		}

		this.doFormIFrameCall( post_data );
	},

	getPostReportJson: function( noPreFix ) {

		var other = this.getFormValues();
		var chart = this.getChartValues();
		var report = this.visible_report_values;

		if ( report.sort ) {
			report.sort = this.convertSortValues( report.sort );

			if ( noPreFix ) { //no pre fix means save to userReport, use sort_ to match flex format
				report.sort_ = report.sort;

				delete report.sort;
			}

		}

		if ( !noPreFix ) {
			report = this.addOrderFix( report );
		}

		var config = report;
		if ( !noPreFix ) {
			config['-' + 1000 + '-' + 'template'] = this.current_edit_record.template;
		} else {
			config['template'] = this.current_edit_record.template;
		}

		config.other = other;
		config.chart = chart;

		return config;
	},

	onContextMenuClick: function( context_btn, menu_name ) {

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
			case ContextMenuIconName.view:
				this.onViewClick();
				break;
			case ContextMenuIconName.export_excel:
				this.onViewExcelClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.save_existed_report: //All report view
				this.onSaveExistedReportClick();
				break;
			case ContextMenuIconName.save_new_report: //All report view
				this.onSaveNewReportClick();
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},

	onSaveSetup: function() {
		var $this = this;
		var form_setup = this.getFormSetupData();

		this.api.setCompanyFormConfig( form_setup, {onResult: function( result ) {

			if ( result.isValid() ) {
				$this.show_empty_message = false;
				$this.form_setup_changed = false;

				TAlertManager.showAlert( $.i18n._( 'Form setup has been saved successfully' ) );
			} else {
				TAlertManager.showAlert( $.i18n._( 'Form setup save failed, Please try again' ) );
			}

		}} );

	},

	getFormSetupData: function() {
		//Always need override
	},

	onViewClick: function( key ) {

		if ( !key ) {
			key = 'pdf';
		}

		var config = this.getPostReportJson();
		var post_data = {0: config, 1: key};

		if ( this.include_form_setup ) {
			if ( this.show_empty_message ) {
				TAlertManager.showAlert( $.i18n._( 'Setup data for this report has not been completed yet. Please click on the Form Setup tab to do so now.' ) );
				return;
			}
			config.form = this.getFormSetupData( true );
		}

		this.doFormIFrameCall( post_data );
	},

	setEditMenuViewIcon: function( context_btn, pId ) {

	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=' + this.api.className + '&Method=' + 'get' + this.api.key_name );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		var tempForm = $( "<form></form>" );
		tempForm.attr( 'id', 'temp_form' );
		tempForm.attr( 'method', 'POST' );
		tempForm.attr( 'action', url );
		tempForm.attr( 'target', is_browser_iOS ? '_blank' : 'hideReportIFrame' ); //hideReportIFrame
		tempForm.css( 'display', 'none' );
		var hideInput = $( "<input type='hidden' name='json'>" );
		hideInput.attr( 'value', JSON.stringify( postData ) );
		tempForm.append( hideInput );
		tempForm.appendTo( 'body' );
		tempForm.css( 'display', 'none' );
		tempForm.submit();
		tempForm.remove();

		if ( !is_browser_iOS ) {
			ProgressBar.showProgressBar( message_id, true );
		}

	},

	onSaveNewReportClick: function() {
		var $this = this;
		var config = this.getPostReportJson( true );
		var select_field = this.current_edit_record.setup_field;

		if ( config.template ) {
			select_field.unshift( 'template' );
		}

		var report_data = {};
		report_data.data = {};
		report_data.data.config = config;
		report_data.data.template = select_field;
		report_data.script = this.script_name;

		if ( !this.sub_saved_report_view_controller ) {
			this.initSubSavedReportView( function() {
				$this.sub_saved_report_view_controller.edit_only_mode = true;
				$this.sub_saved_report_view_controller.onAddClick( report_data );
			} );
		} else {
			$this.sub_saved_report_view_controller.edit_only_mode = true;
			$this.sub_saved_report_view_controller.buildContextMenu( true );
			$this.sub_saved_report_view_controller.onAddClick( report_data );
		}
	},

	onSaveExistedReportClick: function() {
		var $this = this;
		var config = this.getPostReportJson( true );
		var select_field = this.current_edit_record.setup_field;

		if ( config.template ) {
			select_field.unshift( 'template' );
		}

		var report_data = this.current_saved_report;

		if ( !report_data ) {
			report_data = {};
		}

		report_data.data = {};
		report_data.data.config = config;
		report_data.data.template = select_field;

		if ( !report_data.script ) {
			report_data.script = this.script_name;
		}

		if ( !this.sub_saved_report_view_controller ) {
			this.initSubSavedReportView( function() {
				$this.sub_saved_report_view_controller.edit_only_mode = true;
				$this.sub_saved_report_view_controller.onAddClick( report_data );
			} );
		} else {
			$this.sub_saved_report_view_controller.edit_only_mode = true;
			$this.sub_saved_report_view_controller.buildContextMenu( true );
			$this.sub_saved_report_view_controller.onAddClick( report_data );
		}
	},

	onSaveDoneCallback: function( result, current_edit_record ) {

		var new_id = result.getResult();

		if ( $.type( new_id ) !== 'number' ) {
			new_id = current_edit_record.id;
		}
		this.refreshNav( new_id );
	},

	refreshNav: function( newId ) {

		var $this = this;

		this.navigation.setSourceData( null );

		$this.getReportData( function( result ) {
			// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.

			if ( result && result.length > 0 ) {

				if ( $.type( newId ) === 'number' ) {
					for ( var i = 0; i < result.length; i++ ) {
						var item = result[i];

						if ( item.id === newId ) {
							$this.current_saved_report = result[i];
							break;
						}
					}

				} else {
					$this.current_saved_report = result[0];
				}

				$this.saved_report_array = result;
			} else {
				$this.current_saved_report = null;
				$this.saved_report_array = [];
			}

			$this.current_edit_record = {};
			$this.visible_report_values = {};
			$this.setEditViewWidgetsMode();
			$this.initEditView();

		} );
	}

} );

ReportBaseViewController.ReportMissedField = null;