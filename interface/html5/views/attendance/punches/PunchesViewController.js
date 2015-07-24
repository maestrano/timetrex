PunchesViewController = BaseViewController.extend( {
	el: '#punches_view_container',

	user_api: null,
	user_group_api: null,
	api_station: null,
	type_array: null,

	actual_time_label: null,

	is_mass_adding: false,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'PunchesEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'Punches';
		this.script_name = 'PunchesView';
		this.table_name_key = 'punch';
		this.context_menu_name = $.i18n._( 'Punches' );
		this.navigation_label = $.i18n._( 'Punch' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();

		}
		this.api_station = new (APIFactory.getAPIClass( 'APIStation' ))();

		this.initPermission();
		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();

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

		if ( PermissionManager.validate( "job_item", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
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

	noteUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	stationValidate: function() {
		if ( PermissionManager.validate( 'station', 'enabled' ) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
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

		if ( this.stationValidate() ) {
			this.show_station_ui = true;
		} else {
			this.show_station_ui = false;
		}

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'type' );

		this.initDropDownOption( 'status', 'status_id', this.api, null, 'status_array' );

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, null, 'user_status_array' );

		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();

			res = Global.buildTreeRecord( res );
			$this.user_group_array = res;
			$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
			$this.adv_search_field_ui_dic['group_id'].setSourceData( res );

		}} );

	},

	onEditStationDone: function() {
		this.setStation();
	},

	setStation: function() {

		var $this = this;
		var arg = {filter_data: {id: this.current_edit_record.station_id}};

		this.api_station.getStation( arg, {onResult: function( result ) {

			$this.station = result.getResult()[0];

			var widget = $this.edit_view_ui_dic['station_id'];
			widget.setValue( $this.station.type + '-' + $this.station.description );

		}} );
	},

	uniformVariable: function( records ) {

		if ( !records.hasOwnProperty( 'time_stamp' ) ) {
			records.time_stamp = false;
		}

		return records;
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_punch': $.i18n._( 'Punch' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPunch' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PUNCH,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_punch_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'punch';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '', null, true );

		// Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'punch_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.actual_time_label = $( "<span class='widget-right-label'></span>" );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'punch_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.date_format_display ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( {field: 'punch_dates'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.date_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		// Punch

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( {field: 'disable_rounding'} );

		var label = $( "<span class='widget-right-label'>" + $.i18n._( 'Disable Rounding' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( check_box );

		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );

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
			field: 'branch_id'
		} );
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
			field: 'department_id'
		} );
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
				field: 'job_id'
			} );

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
			job_item_coder.addClass( 'job-coder' )

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.edit_view_form_item_dic.job_item_id.hide();
			}

		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			// Quantity

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

		if ( !this.show_note_ui ) {
			this.edit_view_form_item_dic.note.hide();
		}

		// Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'station_id'} );
		form_item_input.css( 'cursor', 'pointer' );
		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.click( function() {
			if ( $this.current_edit_record.station_id && $this.show_station_ui ) {
				IndexViewController.openEditView( $this, 'Station', $this.current_edit_record.station_id );
			}

		} );

		if ( this.show_station_ui ) {
			form_item_input.css( 'cursor', 'pointer' );
		}

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( {field: 'punch_image'} );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );

		if ( this.is_mass_editing ) {
			this.edit_view_form_item_dic.punch_image.hide();
			this.edit_view_form_item_dic.user_id.hide();
		}

	},

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {

		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
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

			widget.setValue( '' ); // Set all value back to empty before new value coming.

			switch ( key ) {
				case 'punch_dates':
					if ( this.is_mass_adding ) {
						widgetContainer.css( 'display', 'block' );
						widget.css( 'opacity', 1 );
						break;
					} else {
						widgetContainer.css( 'display', 'none' );
						widget.css( 'opacity', 0 );
						break;
					}
					break;
				case 'punch_date':
					if ( this.is_mass_adding ) {
						widgetContainer.css( 'display', 'none' );
						widget.css( 'opacity', 0 );
						break;
					} else {
						widgetContainer.css( 'display', 'block' );
						widget.css( 'opacity', 1 );
						break;
					}
					break;
			}

			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {

					widget.setEnabled( true );

				}
			}

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

			record.id = this.mass_edit_record_ids[0];
			record = this.uniformVariable( record );

		} else if ( this.is_mass_adding ) {

			record = this.setMassAddingRecord();

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	setMassAddingRecord: function() {
		var record = [];
		var dates_array = this.current_edit_record.punch_dates;

		if ( dates_array.indexOf( ' - ' ) > 0 ) {
			dates_array = this.parserDatesRange( dates_array );
		}

		for ( var i = 0; i < dates_array.length; i++ ) {
			var common_record = Global.clone( this.current_edit_record );
			delete common_record.punch_dates;
			common_record.punch_date = dates_array[i];
			var user_id = this.current_edit_record.user_id;

			if ( Global.isArray( user_id ) ) {
				for ( var j = 0; j < user_id.length; j++ ) {
					var final_record = Global.clone( common_record );
					final_record.user_id = this.current_edit_record.user_id[j];
					final_record = this.uniformVariable( final_record );
					record.push( final_record );
				}
			} else {
				common_record = this.uniformVariable( common_record );
				record.push( common_record );
			}

		}

		return record;
	},

	parserDatesRange: function( date ) {
		var dates = date.split( " - " );
		var resultArray = [];
		var beginDate = Global.strToDate( dates[ 0 ] );
		var endDate = Global.strToDate( dates[ 1 ] );

		var nextDate = beginDate;

		while ( nextDate.getTime() < endDate.getTime() ) {
			resultArray.push( nextDate.format() );
			nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
		}

		resultArray.push( dates[ 1 ] );

		return resultArray;
	},

	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array = [this.current_edit_record['punch_date']];
						this.current_edit_record.punch_dates = date_array;
						widget.setValue( date_array );
						break;
					case 'country': //popular case
						this.eSetProvince( this.current_edit_record[key] );

						widget.setValue( this.current_edit_record[key] );
						break;
					case 'enable_email_notification_message':
						widget.setValue( this.current_edit_record[key] );
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
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					case 'station_id':
						if ( this.current_edit_record[key] ) {
							this.setStation();
						} else {
							widget.setValue( 'N/A' );
						}
						break;
					case 'punch_image':
						var station_form_item = this.edit_view_form_item_dic['station_id'];
						if ( this.current_edit_record['has_image'] ) {
							this.edit_view_form_item_dic['punch_image'].show();
							this.removeLastRowClass( station_form_item );
							widget.setValue( ServiceCaller.fileDownloadURL + '?object_type=punch_image&parent_id=' + this.current_edit_record.user_id + '&object_id=' + this.current_edit_record.id );

						} else {
							this.edit_view_form_item_dic['punch_image'].hide();
							this.addLastRowClass( station_form_item );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		} else {
			actual_time_value = $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.time_format_display )
		}

		this.actual_time_label.text( actual_time_value );

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
		this.isEditChange();

	},

	isEditChange: function() {

		if ( this.current_edit_record.id || this.is_mass_editing ) {
			this.edit_view_ui_dic['user_id'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_id'].setEnabled( true );
		}
	},

	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone: function() {
		// Remove this on 14.9.14 because adding tab url support, ned set url when tab index change and
		// need know what's current doing action. See if this cause any problem
		//LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );

		if ( !this.is_mass_adding ) {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( false );
		} else {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( true );
		}

	},

	initSubLogView: function( tab_id ) {

		var $this = this;
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {

				filter['table_name_object_id'] = {
					'punch': [this.parent_edit_record.id],
					'punch_control': [this.parent_edit_record.punch_control_id]
				};

				return filter;
			};
			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/core/log/LogViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {
				filter['table_name_object_id'] = {
					'punch': [this.parent_edit_record.id],
					'punch_control': [this.parent_edit_record.punch_control_id]
				};

				return filter;
			};
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();

		}
	},

//	showNoResultCover: function() {
//
//		this.removeNoResultCover();
//		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
//		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: false} );
//		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );
//
//		var grid_div = $( this.el ).find( '.grid-div' );
//
//		grid_div.append( this.no_result_box );
//
//		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
//	},

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

	onAddResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;
		result_data.punch_date = (new Date()).format();

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		var default_args = {permission_section: 'punch'};
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
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

			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				default_args: default_args,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Title' ),
				in_column: 1,
				field: 'title_id',
				layout_name: ALayoutIDs.USER_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Punch Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Punch Department' ),
				in_column: 2,
				field: 'department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
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

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} )];
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
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
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

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
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

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		var ttimport = new RibbonSubMenu( {
			label: $.i18n._( 'Import' ),
			id: ContextMenuIconName.import_icon,
			group: other_group,
			icon: Icons.import_icon,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	onAddClick: function() {
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_mass_adding = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		$this.api['get' + $this.api.key_name + 'DefaultData']( {onResult: function( result ) {
			$this.onAddResult( result );

		}} );

	},

	onContextMenuClick: function( context_btn, menu_name ) {
		if ( Global.isSet( menu_name ) ) {
			var id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.add:
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.mass_edit:
				ProgressBar.showOverlay();
				this.onMassEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_new:
				ProgressBar.showOverlay();
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				ProgressBar.showOverlay();
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.import_icon:
				this.onNavigationClick( id );
				break;

		}
	},

	setDefaultMenu: function( doNotSetFocus ) {


        //Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
        if (!this.context_menu_array) {
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
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function() {

		this.selectContextMenu();
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}

				continue;
			}

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuNavViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuNavEditIcon( context_btn, 'user' );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	onNavigationClick: function( iconName ) {
		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				filter = {filter_data: {}};
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id;
					filter.base_date = this.current_edit_record.punch_date;
					Global.addViewTab( this.viewId, 'Punches', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						var selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {onResult: function( result ) {

							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							filter.user_id = result_data.user_id;
							filter.base_date = result_data.punch_date;

							Global.addViewTab( $this.viewId, 'Punches', window.location.href );
							IndexViewController.goToView( 'TimeSheet', filter );

						}} );
					}

				}

				break;

			case ContextMenuIconName.edit_employee:
				filter = {filter_data: {}};
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							IndexViewController.openEditView( $this, 'Employee', result_data.user_id );

						}} );
					}

				}
				break;
			case ContextMenuIconName.import_icon:
				IndexViewController.openWizard( 'ImportCSVWizard', 'punch', function() {
					$this.search();
				} );
				break;
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

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDateOrMassUser() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		this.is_add = true;
		this.is_mass_adding = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
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

	isMassDateOrMassUser: function() {
		if ( this.is_mass_adding ) {
			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
				return true
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length > 1 ) {
				return true
			}

			return false;
		}

		return false;
	},

	onSaveAndCopy: function() {
		var $this = this;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();
		} else {
			record = this.uniformVariable( record );
		}

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
			$this.onSaveAndCopyResult( result );

		}} );
	},

	onSaveAndNewClick: function() {
		var $this = this;
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';
		if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();
		} else {
			record = this.uniformVariable( record );
		}
		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {
			$this.onSaveAndNewResult( result );

		}} );
	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		this.is_mass_adding = false;
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

			$this.api['getOptions']( 'unique_columns', {onResult: function( result ) {
				$this.unique_columns = result.getResult();
				$this.api['getOptions']( 'linked_columns', {onResult: function( result1 ) {
					$this.linked_columns = result1.getResult();

					if ( $this.sub_view_mode && $this.parent_key ) {
						result_data[$this.parent_key] = $this.parent_value;
					}

					$this.current_edit_record = result_data;
					$this.initEditView();

				}} );

			}} );

		}} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		this.is_mass_adding = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			result_data = result_data[0];

			if ( !result_data ) {
				TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
				$this.onCancelClick();
				return;
			}

			if ( $this.sub_view_mode && $this.parent_key ) {
				result_data[$this.parent_key] = $this.parent_value;
			}

			$this.current_edit_record = result_data;

			$this.initEditView();

		}} );

	},

	onSaveAndContinue: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'save_and_continue';

		if ( this.is_mass_adding ) {

			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
				this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length === 1 ) {
				this.current_edit_record.user_id = this.current_edit_record.user_id[0];
			}

		}

		this.current_edit_record = this.uniformVariable( this.current_edit_record );

		this.api['set' + this.api.key_name]( this.current_edit_record, {onResult: function( result ) {
			if ( result.isValid() ) {
				var result_data = result.getResult();
				var refresh_id;
				if ( result_data === true ) {
					refresh_id = $this.current_edit_record.id;

				} else if ( result_data > 0 ) {
					refresh_id = result_data
				}
				$this.search();
				$this.onEditClick( refresh_id );

				$this.onSaveAndContinueDone( result );
			} else {
				$this.setErrorTips( result );
				$this.setErrorMenu();
			}

		}} );
	},

	onFormItemChange: function( target, doNotValidate ) {

		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'user_id':
			case 'punch_dates':
				this.setEditMenu();
				break;
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
					this.onJobQuickSearch( key, c_value );
				}
				break;
			default:
				this.current_edit_record[key] = c_value;
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	onSaveClick: function() {
		var $this = this;
		var record;
		var key;
		var rows;
		var i;
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				record.push( common_record );

			} );
		} else if ( this.is_mass_adding ) {
			record = this.setMassAddingRecord();

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {

			$this.onSaveResult( result );

		}} );
	}



} );

PunchesViewController.loadView = function() {

	Global.loadViewSource( 'Punches', 'PunchesView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};