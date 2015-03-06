InOutViewController = BaseViewController.extend( {

	type_array: null,

	job_api: null,
	job_item_api: null,

	old_type_status: {},

	show_job_ui: false,
	show_job_item_ui: false,
	show_branch_ui: false,
	show_department_ui: false,
	show_good_quantity_ui: false,
	show_bad_quantity_ui: false,
	show_transfer_ui: false,
	show_node_ui: false,

	initialize: function() {

		if ( Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		this._super( 'initialize' );

		this.permission_id = 'punch';
		this.viewId = 'InOut';
		this.script_name = 'InOutView';
		this.table_name_key = 'punch';
		this.context_menu_name = $.i18n._( 'In/Out' );
		this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();

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

		//Tried to fix  Cannot call method 'getJobItem' of null. Use ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
		}

		this.render();
		this.buildContextMenu();

		this.initPermission();

		this.initData();

	},

	addPermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'punch_in_out' ) ) {
			return true;
		}

		return false;

	},

	setFocusToFirstInput: function() {
		if ( !this.is_viewing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}
				var widget = this.edit_view_ui_dic[key];

				if ( widget.hasClass( 't-text-input' ) && widget.is( ':visible' ) === true && !widget.attr( 'readonly' ) ) {
					widget.focus();
					widget[0].select();

					break;
				}

			}
		}
	},

	jobUIValidate: function() {
		if ( PermissionManager.validate( "job", 'enabled' ) &&
			PermissionManager.validate( "punch", 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_quantity' ) &&
			PermissionManager.validate( "punch", 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	transferUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_transfer' ) ) {
			return true;
		}
		return false;
	},

	noteUIValidate: function() {
		if ( PermissionManager.validate( "punch", 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
	initPermission: function() {
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

		if ( this.transferUIValidate() ) {
			this.show_transfer_ui = true;
		} else {
			this.show_transfer_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_node_ui = true;
		} else {
			this.show_node_ui = false;
		}

		var result = new (APIFactory.getAPIClass( 'APICompany' ))().isBranchAndDepartmentAndJobAndJobItemEnabled( {async: false} );

		//tried to fix Unable to get property 'getResult' of undefined or null reference, added if(!result)
		if ( !result ) {
			this.show_branch_ui = false;
			this.show_department_ui = false;
			this.show_job_ui = false;
			this.show_job_item_ui = false;
		} else {
			result = result.getResult();
			if ( !result.branch ) {
				this.show_branch_ui = false;
			}

			if ( !result.department ) {
				this.show_department_ui = false;
			}

			if ( !result.job ) {
				this.show_job_ui = false;
			}

			if ( !result.job_item ) {
				this.show_job_item_ui = false;
			}
		}

		if ( !this.show_job_ui && !this.show_job_item_ui ) {
			this.show_bad_quantity_ui = false;
			this.show_good_quantity_ui = false;
		}

	},

	render: function() {
		this._super( 'render' );
	},

	initOptions: function( callBack ) {

		var options = [
			{option_name: 'type'},
			{option_name: 'status'}
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );

	},

	getUserPunch: function( callBack ) {
		var $this = this;

		var station_id = LocalCacheData.getStationID();

		if ( !station_id ) {
			station_id = $.cookie( 'StationID' );
		}
		var api_station = new (APIFactory.getAPIClass( 'APIStation' ))();

		if ( station_id ) {
			api_station.getCurrentStation( station_id, '10', {onResult: function( result ) {
				doNext( result );
			}} );
		} else {
			api_station.getCurrentStation( '', '10', {onResult: function( result ) {
				doNext( result );
			}} );
		}

		function doNext( result ) {
			var res_data = result.getResult();
			$.cookie( 'StationID', res_data, {expires: 10000, path: LocalCacheData.cookie_path} );
			LocalCacheData.setStationID( res_data );

			$this.api.getUserPunch( {onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result.isValid() ) {
					TAlertManager.showErrorAlert( result );
					$this.onCancelClick();
					return
				}

				if ( Global.isSet( result_data ) ) {
					callBack( result_data );
				} else {
					$this.onCancelClick();
				}

			}} );

		}

	},

	openEditView: function() {

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'InOut', 'InOutEditView.html' );
				}

				$this.getUserPunch( function( result ) {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this.current_edit_record = result;
					$this.setEditViewWidgetsMode();
					$this.initEditView();

				} );

			} );

		}

	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'transfer':
				this.onTransferChanged( c_value );
				break;
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ) );
				}
				break;
			case 'job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? (target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '') : '' );
				}
				break;

			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.onJobQuickSearch( key, c_value );
				}
				break;

		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onTransferChanged: function( value ) {

		var type_id_widget = this.edit_view_ui_dic['type_id'];
		var status_id_widget = this.edit_view_ui_dic['status_id'];
		if ( value ) {

			type_id_widget.setEnabled( false );
			status_id_widget.setEnabled( false );

			this.old_type_status.type_id = type_id_widget.getValue();
			this.old_type_status.status_id = status_id_widget.getValue();

			type_id_widget.setValue( 10 );
			status_id_widget.setValue( 10 );

			this.current_edit_record.type_id = 10;
			this.current_edit_record.status_id = 10;

		} else {
			type_id_widget.setEnabled( true );
			status_id_widget.setEnabled( true );

			if ( this.old_type_status.hasOwnProperty( 'type_id' ) ) {
				type_id_widget.setValue( this.old_type_status.type_id );
				status_id_widget.setValue( this.old_type_status.status_id );

				this.current_edit_record.type_id = this.old_type_status.type_id;
				this.current_edit_record.status_id = this.old_type_status.status_id;
			}

		}
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

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value, user_id: this.current_edit_record.user_id, status_id: "10"};

			this.job_api.getJob( args, {onResult: function( result ) {

				var result_data = result.getResult();

				if ( result_data.length > 0 ) {
					$this.edit_view_ui_dic['job_id'].setValue( result_data[0].id );
					$this.current_edit_record.job_id = result_data[0].id;
					$this.setJobItemValueWhenJobChanged( result_data[0] );

				} else {
					$this.edit_view_ui_dic['job_id'].setValue( '' );
					$this.current_edit_record.job_id = false;
					$this.setJobItemValueWhenJobChanged( null );

				}

			}} );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value, job_id: this.current_edit_record.job_id, status_id: "10"};

			this.job_item_api.getJobItem( args, {onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data.length > 0 ) {
					$this.edit_view_ui_dic['job_item_id'].setValue( result_data[0].id );
					$this.current_edit_record.job_item_id = result_data[0].id;

				} else {
					$this.edit_view_ui_dic['job_item_id'].setValue( '' );
					$this.current_edit_record.job_item_id = false;
				}

			}} );
		}

	},

	//Make sure this.current_edit_record is updated before validate
	validate: function() {

		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		this.api.setUserPunch( record, true, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onSaveClick: function() {

		var $this = this;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save';
		this.api.setUserPunch( record, {onResult: function( result ) {

			if ( result.isValid() ) {
				var result_data = result.getResult();
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( result_data > 0 ) {
					$this.refresh_id = result_data
				}

				$this.current_edit_record = null;
				$this.removeEditView();

				if ( LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
					LocalCacheData.current_open_primary_controller.search();
				}

			} else {
				$this.setErrorTips( result );
				$this.setErrorMenu();
			}

		}} );
	},

	setErrorMenu: function() {

		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
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

	buildOtherFieldUI: function( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab_punch = this.edit_view_tab.find( '#tab_punch' );
		var tab_punch_column1 = tab_punch.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: field} );
			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab_punch_column1 );

			input_div.insertBefore( this.edit_view_form_item_dic['note'] );

			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		}

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_punch': $.i18n._( 'Punch' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_punch_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {
			field: 'user_id_readonly'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '' );

		// Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'punch_time'} );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1 );

		// Date
//		  punch_date, punch_dates
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'punch_date'} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1 );

		//Transfer

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'transfer'} );

		this.addEditFieldToColumn( $.i18n._( 'Transfer' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_transfer_ui ) {
			this.edit_view_form_item_dic.transfer.hide();
		}

		// Punch

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );

		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_punch_column1 );

		// In/Out

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1 );

		// Branch

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.edit_view_form_item_dic.branch_id.hide();
		}

		// Department

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

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
				field: 'job_id'} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.edit_view_form_item_dic.job_id.hide();
			}

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
				field: 'job_item_id'} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.edit_view_form_item_dic.job_item_id.hide();
			}

		}

		// Quantity

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( {field: 'quantity', width: 40} );
			good.addClass( 'quantity-input' );

			var good_label = $( "<span class='widget-right-label'>" + $.i18n._( 'Good' ) + ": </span>" );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( {field: 'bad_quantity', width: 40} );
			bad.addClass( 'quantity-input' );

			var bad_label = $( "<span class='widget-right-label'>/ " + $.i18n._( 'Bad' ) + ": </span>" );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_punch_column1, '', widgetContainer, true );

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

		form_item_input.TTextArea( {field: 'note', width: '100%' } );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.parent().width( '45%' );

		if ( !this.show_node_ui ) {
			this.edit_view_form_item_dic.note.hide();
		}

	},

	setCurrentEditRecordData: function() {

		// reset old_types, should only be set when type change and transfer is true. fixed bug 1500
		this.old_type_status = {};
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) continue;

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_id_readonly':
						widget.setValue( this.current_edit_record.first_name + ' ' + this.current_edit_record.last_name );
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
					case 'job_quick_search':
						break;
					case 'job_item_quick_search':
						break;
					case 'transfer':
						// do this at last
						break;
					case 'punch_time':
					case 'punch_date':
						widget.setEnabled( false );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		//The API will return if transfer should be enabled/disabled by default.
		this.edit_view_ui_dic['transfer'].setValue( this.current_edit_record['transfer'] );
		//if ( PermissionManager.validate( 'punch', 'edit_transfer' ) && PermissionManager.validate( 'punch', 'default_transfer' ) ) {
		//	this.edit_view_ui_dic['transfer'].setValue( true );
		//	this.current_edit_record['transfer'] = true;
		//} else {
		//	this.edit_view_ui_dic['transfer'].setValue( this.current_edit_record['transfer'] );
		//}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onTransferChanged( this.current_edit_record['transfer'] );
	}

} );

InOutViewController.loadView = function() {

	Global.loadViewSource( 'InOut', 'InOutView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};
