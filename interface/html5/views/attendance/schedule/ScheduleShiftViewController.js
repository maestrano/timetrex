ScheduleShiftViewController = BaseViewController.extend( {
	el: '#schedule_shift_view_container',

	schedule_status_array: null,

	user_status_array: null,

	user_group_array: null,

	user_api: null,
	user_group_api: null,
	date_api: null,
	absence_policy_api: null,

	total_time: null,
	pre_total_time: 0,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'ScheduleShiftEditView.html';
		this.permission_id = 'schedule';
		this.viewId = 'ScheduleShift';
		this.script_name = 'ScheduleShiftView';
		this.table_name_key = 'schedule';
		this.context_menu_name = $.i18n._( 'Schedule Shift' );
		this.navigation_label = $.i18n._( 'Schedule' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APISchedule' ))();
		this.absence_policy_api = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();

		}

		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status', 'status_id', this.api, function( res ) {
			res = res.getResult();

			$this.schedule_status_array = Global.buildRecordArray( res );

		} );

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, function( res ) {
			res = res.getResult();

			$this.user_status_array = Global.buildRecordArray( res );
		} );

		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();

			res = Global.buildTreeRecord( res );
			$this.user_group_array = res;

			$this.adv_search_field_ui_dic['group_ids'].setSourceData( res );

		}} );

	},

	checkOpenPermission: function() {
		if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 && PermissionManager.validate( 'schedule', 'view_open' ) ) {
			return true
		}

		return false;
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_schedule': $.i18n._( 'Schedule' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APISchedule' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.SCHEDULE,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_schedule = this.edit_view_tab.find( '#tab_schedule' );

		var tab_schedule_column1 = tab_schedule.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_schedule_column1 );

		// Employee
		// Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_any: true,
			field: 'user_id',
			custom_first_label: Global.empty_item,
			addition_source_function: (function( target, source_data ) {
				return $this.onEmployeeSourceCreate( target, source_data );
			}),
			added_items: [
				{value: 0, label: Global.open_item}
			]

		} );
		var default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '' );

		// Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.schedule_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_column1 );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'date_stamp'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', widgetContainer );

		// In
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'start_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_schedule_column1, '', widgetContainer );

		// Out

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'end_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_schedule_column1, '', widgetContainer );

		// Total
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'total_time'} );
		this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_schedule_column1 );

		// Schedule Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APISchedulePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.SCHEDULE_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'schedule_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Policy' ), form_item_input, tab_schedule_column1 );

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

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_schedule_column1, '', null, true );

		// Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'available_balance'} );
		form_item_input.setClassStyle( {float: 'left', 'margin-right': '5px' } );

		widgetContainer = $( "<div style='position: relative' class='widget-h-box'></div>" );
		var icon = $( "<img class='balance_icon' src='theme/default/images/infox16x16.png' />" );
		icon.bind( 'click', this.exchangeBalance );
		var balance_content = $( "<div  class='schedule-view-balance-info' ></div>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( icon );
		widgetContainer.append( balance_content );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		// Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_schedule_column1 );

		// Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_schedule_column1 );

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
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_schedule_column1, '', widgetContainer );

			// Task
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
			job_item_coder.addClass( 'job-coder' )

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_schedule_column1, '', widgetContainer );

		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {field: 'note', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_schedule_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

	},

	exchangeBalance: function() {

		var b_div = $( $( this ).next() );
		if ( b_div.css( 'display' ) === 'block' ) {
			b_div.css( 'display', 'none' );
		} else if ( b_div.css( 'display' ) === 'none' ) {
			b_div.css( 'display', 'block' );

		}
	},

	setAbsencePolicyFilter: function( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		filter.filter_data.user_id = this.current_edit_record.user_id;
		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	},

	onEmployeeSourceCreate: function( target, source_data ) {

		if ( !this.checkOpenPermission() ) {
			return source_data;
		}

		var display_columns = target.getDisplayColumns();

		var first_item = {};
		$.each( display_columns, function( index, content ) {

			first_item.id = '0';
			first_item[content.name] = Global.open_item;

			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in https://ondemand1.timetrex.com/interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}
		source_data.unshift( first_item );

		return source_data;
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		var $this = this;
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				addition_source_function: (function( target, source_data ) {
					return $this.onEmployeeSourceCreate( target, source_data );
				}),
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 1,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Department' ),
				in_column: 1,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_ids',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 2,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: ALayoutIDs.JOB,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJob' )) : null,
				multiple: true,
				basic_search: false,
				adv_search: (this.show_job_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: ALayoutIDs.JOB_ITEM,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJobItem' )) : null,
				multiple: true,
				basic_search: false,
				adv_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Schedule Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Schedule Department' ),
				in_column: 2,
				field: 'department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Schedule Policy' ),
				in_column: 2,
				field: 'schedule_policy_id',
				layout_name: ALayoutIDs.SCHEDULE_POLICY,
				api_class: (APIFactory.getAPIClass( 'APISchedulePolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} )];
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {

		var $this = this;
		var job_item_widget = $this.edit_view_ui_dic['job_item_id'];
		var current_job_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		var args = {};
		args.filter_data = {status_id: 10, job_id: $this.current_edit_record.job_id};
		$this.edit_view_ui_dic['job_item_id'].setDefaultArgs( args );

		if ( current_job_item_id ) {

			var new_arg = Global.clone( args );

			new_arg.filter_data.id = current_job_item_id;
			new_arg.filter_columns = $this.edit_view_ui_dic['job_item_id'].getColumnFilter();
			$this.job_item_api.getJobItem( new_arg, {onResult: function( task_result ) {
				var data = task_result.getResult();

				if ( data.length > 0 ) {
					job_item_widget.setValue( current_job_item_id );
					$this.current_edit_record.job_item_id = current_job_item_id;
				} else {
					setDefaultData();
				}

			}} )

		} else {
			setDefaultData();
		}

		function setDefaultData() {
			if ( $this.current_edit_record.job_id ) {
				job_item_widget.setValue( job.default_item_id );
				$this.current_edit_record.job_item_id = job.default_item_id;

				if ( job.default_item_id === false || job.default_item_id === 0 ) {
					$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
				}

			} else {
				job_item_widget.setValue( '' );
				$this.current_edit_record.job_item_id = false;
				$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );

			}
		}
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ) );
				}
				break;
			case 'job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.onJobQuickSearch( key, c_value )
				}
				break;
			case 'status_id':
				this.onStatusChange();
				break;
			case 'user_id':
				this.edit_view_ui_dic['absence_policy_id'].setSourceData( null );
				this.edit_view_ui_dic['absence_policy_id'].setValue( false );
				this.current_edit_record['absence_policy_id'] = false;
				this.getAbsencePolicy( this.current_edit_record[key] );
				break;

		}

		this.getScheduleTotalTime();
		this.getProjectedAbsencePolicyBalance();

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	getProjectedAbsencePolicyBalance: function() {

		$( this.edit_view_form_item_dic['available_balance'].find( ".schedule-view-balance-info" ) ).css( 'display', 'none' );

		if ( this.current_edit_record['absence_policy_id'] && this.edit_view_form_item_dic['absence_policy_id'].css( 'display' ) === 'block' && !this.is_mass_editing ) {
			var user_id;
			if ( Global.isArray( this.current_edit_record['user_id'] ) && this.current_edit_record['user_id'].length === 1 ) {
				user_id = this.current_edit_record['user_id'][0];
			} else if ( this.current_edit_record['user_id'] ) {
				user_id = this.current_edit_record['user_id'];
			} else {
				return;
			}

			var date_stamp = this.current_edit_record['date_stamp'];
			var result_data = this.absence_policy_api.getProjectedAbsencePolicyBalance( this.current_edit_record['absence_policy_id'], user_id, date_stamp, this.total_time, this.pre_total_time, {async: false} ).getResult();

			result_data = result_data ? result_data : { available_balance: 0, current_time: 0, remaining_balance: 0, projected_balance: 0, projected_remaining_balance: 0 };

			this.edit_view_ui_dic['available_balance'].setValue( Global.secondToHHMMSS( result_data.remaining_balance ) );

			var container = $( this.edit_view_form_item_dic['available_balance'].find( ".schedule-view-balance-info" ) );

			var p_str = "<table>";
			for ( var key in result_data ) {

				switch ( key ) {
					case 'available_balance':
						p_str += "<tr>";
						p_str += "<td>" + $.i18n._( "Available Balance" ) + ":" + "</td>";
						p_str += "<td>" + Global.secondToHHMMSS( result_data[key] ) + "</td>";
						p_str += "</tr>";
						break;
					case 'current_time':
						p_str += "<tr>";
						p_str += "<td>" + $.i18n._( "Current Time" ) + ":" + "</td>";
						p_str += "<td>" + Global.secondToHHMMSS( result_data[key] ) + "</td>";
						p_str += "</tr>";
						break;
					case 'remaining_balance':
						p_str += "<tr>";
						p_str += "<td>" + $.i18n._( "Remaining Balance" ) + ":" + "</td>";
						p_str += "<td>" + Global.secondToHHMMSS( result_data[key] ) + "</td>";
						p_str += "</tr>";
						p_str += "<tr><td colspan='2'>&nbsp;</td></tr>";
						break;
					case 'projected_balance':
						p_str += "<tr>";
						p_str += "<td>" + $.i18n._( "Projected Balance by false" ) + ":" + "</td>";
						p_str += "<td>" + Global.secondToHHMMSS( result_data[key] ) + "</td>";
						p_str += "</tr>";
						break;
					case 'projected_remaining_balance':
						p_str += "<tr>";
						p_str += "<td>" + $.i18n._( "Projected Remaining Balance" ) + ":" + "</td>";
						p_str += "<td>" + Global.secondToHHMMSS( result_data[key] ) + "</td>";
						p_str += "</tr>";
						break;
				}

			}

			p_str += "</table>";

			container.html( p_str );

			this.edit_view_form_item_dic['available_balance'].css( 'display', 'block' );

		} else {

			this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
		}
	},

	getAbsencePolicy: function( user_ids ) {
		var args = {filter_data: {}};
		args.filter_data.user_id = user_ids;
		this.edit_view_ui_dic['absence_policy_id'].setDefaultArgs( args );
	},

	uniformVariable: function( records ) {
		var new_records = [];
		if ( Global.isArray( records.user_id ) && records.user_id.length > 0 ) {
			var user_ids = records.user_id;
			for ( var key in user_ids ) {
				var tmp_records = Global.clone( records );
				tmp_records.user_id = user_ids[key];
				new_records.push( tmp_records );
			}
		}
		if ( new_records.length > 0 ) {
			return new_records;
		}
		return records;
	},

	setCurrentEditRecordData: function() {
		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'status_id': 10
		} );

		if ( this.current_edit_record.id || this.is_mass_editing ) {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( false );
		} else {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( true );
		}

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'total_time':
						this.pre_total_time = ( this.is_add ) ? 0 : this.current_edit_record[key];
						break;
					case 'job_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							var args = {};
							args.filter_data = {status_id: 10, user_id: this.current_edit_record.user_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							args = {};
							args.filter_data = {status_id: 10, job_id: this.current_edit_record.job_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'user_id':
						widget.setValue( this.current_edit_record[key] );
						this.getAbsencePolicy( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.getScheduleTotalTime();
		this.getProjectedAbsencePolicyBalance();
		this.setEditViewDataDone();

	},

	getScheduleTotalTime: function() {
		var startTime = ( this.current_edit_record['date_stamp'] ) ? this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'] : ( (this.current_edit_record['start_time']) ? this.current_edit_record['start_time'] : ''  );
		var endTime = ( this.current_edit_record['date_stamp'] ) ? this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'] : ( (this.current_edit_record['end_time']) ? this.current_edit_record['end_time'] : '' );
		var schedulePolicyId = ( this.current_edit_record['schedule_policy_id'] ) ? this.current_edit_record['schedule_policy_id'] : '';
		var user_id = this.current_edit_record.user_id;
		this.total_time = this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, {async: false} ).getResult();
		var total_time = Global.secondToHHMMSS( this.total_time );

		this.edit_view_ui_dic['total_time'].setValue( total_time );

		this.current_edit_record['total_time'] = total_time;
	},

	setEditViewDataDone: function() {

		this._super( 'setEditViewDataDone' );
		this.onStatusChange();
	},

	onStatusChange: function() {

		if ( this.current_edit_record['status_id'] === 10 ) {

			this.edit_view_form_item_dic['absence_policy_id'].css( 'display', 'none' );
//			this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );

		} else if ( this.current_edit_record['status_id'] === 20 ) {
			this.edit_view_form_item_dic['absence_policy_id'].css( 'display', 'block' );
		}

		this.editFieldResize();

	}


} );

ScheduleShiftViewController.loadView = function() {

	Global.loadViewSource( 'ScheduleShift', 'ScheduleShiftView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};