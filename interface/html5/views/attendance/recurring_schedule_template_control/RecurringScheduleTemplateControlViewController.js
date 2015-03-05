RecurringScheduleTemplateControlViewController = BaseViewController.extend( {
	el: '#recurring_schedule_template_control_view_container',

	sub_document_view_controller: null,

	document_object_type_id: null,

	recurring_schedule_template_api: null,

	schedule_api: null,

	recurring_schedule_status_array: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'RecurringScheduleTemplateControlEditView.html';
		this.permission_id = 'recurring_schedule_template';
		this.viewId = 'RecurringScheduleTemplateControl';
		this.script_name = 'RecurringScheduleTemplateControlView';
		this.table_name_key = 'recurring_schedule_template_control';
		this.context_menu_name = $.i18n._( 'Recurring Templates' );
		this.navigation_label = $.i18n._( 'Recurring Template' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' ))();
		this.schedule_api = new (APIFactory.getAPIClass( 'APISchedule' ))();
		this.recurring_schedule_template_api = new (APIFactory.getAPIClass( 'APIRecurringScheduleTemplate' ))();

		this.document_object_type_id = 10;

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RecurringScheduleTemplateControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status', null, this.recurring_schedule_template_api, function( res ) {
			res = res.getResult();
			$this.recurring_schedule_status_array = Global.buildRecordArray( res );
		} );

	},

	setTabStatus: function() {

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

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_recurring_template': $.i18n._( 'Recurring Template' ),
			'tab_attachment': $.i18n._( 'Attachments' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		var form_item_input;

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		this.edit_view_tab.css( 'max-width', 'none' );

		if ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
			this.edit_view_tab.css( 'min-width', '1380px' );
		} else if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
			this.edit_view_tab.css( 'min-width', '1150px' );
		} else {
			this.edit_view_tab.css( 'min-width', '1050px' );
		}

		//Tab 0 start

		var tab_recurring_template = this.edit_view_tab.find( '#tab_recurring_template' );

		var tab_recurring_template_column1 = tab_recurring_template.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_template_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_template_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'description', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_recurring_template_column1 );

		form_item_input.parent().width( '45%' );

		//Inside editor

		var inside_editor_div = tab_recurring_template.find( '.inside-editor-div' );

		var args = {
			week: $.i18n._( 'Week' ),
			status: $.i18n._( 'Status' ),
			week_names: 'S&nbsp;&nbsp;M&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;T&nbsp&nbsp;F&nbsp;&nbsp;S',
			shift_time: $.i18n._( 'Shift Time' ),
			total: $.i18n._( 'Total' ),
			schedule_policy: $.i18n._( 'Schedule Policy' ),
			branch_department: $.i18n._( 'Branch/Department' ),
			job_task: $.i18n._( 'Job/Task' ),
			open_shift_multiplier: $.i18n._( 'Open Shift Multiplier' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {title: $.i18n._( 'NOTE: To set different In/Out times for each day of the week, add additional weeks all with the same week number.' ),
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			parent_controller: this,
			api: this.recurring_schedule_template_api,
			render: getRender(),
			render_args: args,
			row_render: getRowRender()

		} );

		function getRender() {
			var render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender_1.html';
			if ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender.html';
			} else if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRender_2.html';
			}

			return render;
		}

		function getRowRender() {
			var render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow_1.html';
			if ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow.html';
			} else if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				render = 'views/attendance/recurring_schedule_template_control/RecurringScheduleTemplateControlViewInsideEditorRow_2.html';
			}

			return render;
		}

		inside_editor_div.append( this.editor );

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_document_view_controller = null;

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.initInsideEditorData();
	},

	initInsideEditorData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			$this.editor.removeAllRows();
			$this.editor.getDefaultData();

		} else {

			args.filter_data.recurring_schedule_template_control_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			$this.recurring_schedule_template_api['get' + $this.recurring_schedule_template_api.key_name]( args, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}
				var data = res.getResult();
				$this.editor.setValue( data );

			}} );

		}

	},

	insideEditorAddRow: function( data, index ) {

		var form_item_input;

		var $this = this;
		if ( !data ) {
			this.getDefaultData( index );
		} else {
			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = {}; //Save each row's widgets

			//Build row widgets

			// Week
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: 'week', width: 40} );
			form_item_input.setValue( data.week ? data.week : 1 );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );
			form_item_input.attr( 'recurring_schedule_template_id', (data.id && this.parent_controller.current_edit_record.id) ? data.id : '' );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Status

			var widgetContainer = $( "<div class='recurring-template-status-div'></div>" );

			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( {field: 'status_id'} );
			form_item_input.setSourceData( Global.addFirstItemToArray( this.parent_controller.recurring_schedule_status_array ) );
			form_item_input.setValue( data.status_id ? data.status_id : 10 );
			widgets[form_item_input.getField()] = form_item_input;

			form_item_input.bind( 'formItemChange', function( e, target ) {
				if ( target.getValue() === 10 ) {
					widgets['absence_policy_id'].hide();
				} else if ( target.getValue() === 20 ) {
					widgets['absence_policy_id'].show();
				}
			} )

			widgetContainer.append( form_item_input );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.ABSENCES_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'absence_policy_id'
			} );

			form_item_input.css( 'position', 'absolute' );
			form_item_input.css( 'left', '0' );
			form_item_input.setValue( data.absence_policy_id ? data.absence_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;
			this.setWidgetEnableBaseOnParentController( form_item_input );
			widgetContainer.append( form_item_input );

			row.children().eq( 1 ).append( widgetContainer );

			// sun mon tue wed thu fri sat
			var widgetContainer2 = $( "<div class='widget-h-box'></div>" );
			// Sun
			var form_item_sun_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sun_checkbox.TCheckbox( {field: 'sun'} );
			form_item_sun_checkbox.setValue( data.sun ? data.sun : false );
			widgets[form_item_sun_checkbox.getField()] = form_item_sun_checkbox;
			widgetContainer2.append( form_item_sun_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_sun_checkbox );
			// Mon
			var form_item_mon_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_mon_checkbox.TCheckbox( {field: 'mon'} );
			form_item_mon_checkbox.setValue( data.mon ? data.mon : false );
			widgets[form_item_mon_checkbox.getField()] = form_item_mon_checkbox;
			widgetContainer2.append( form_item_mon_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_mon_checkbox );
			// Tue
			var form_item_tue_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_tue_checkbox.TCheckbox( {field: 'tue'} );
			form_item_tue_checkbox.setValue( data.tue ? data.tue : false );
			widgets[form_item_tue_checkbox.getField()] = form_item_tue_checkbox;
			widgetContainer2.append( form_item_tue_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_tue_checkbox );
			// Wed
			var form_item_wed_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_wed_checkbox.TCheckbox( {field: 'wed'} );
			form_item_wed_checkbox.setValue( data.wed ? data.wed : false );
			widgets[form_item_wed_checkbox.getField()] = form_item_wed_checkbox;
			widgetContainer2.append( form_item_wed_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_wed_checkbox );
			// Thu
			var form_item_thu_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_thu_checkbox.TCheckbox( {field: 'thu'} );
			form_item_thu_checkbox.setValue( data.thu ? data.thu : false );
			widgets[form_item_thu_checkbox.getField()] = form_item_thu_checkbox;
			widgetContainer2.append( form_item_thu_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_thu_checkbox );
			// Fri
			var form_item_fri_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_fri_checkbox.TCheckbox( {field: 'fri'} );
			form_item_fri_checkbox.setValue( data.fri ? data.fri : false );
			widgets[form_item_fri_checkbox.getField()] = form_item_fri_checkbox;
			widgetContainer2.append( form_item_fri_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_fri_checkbox );
			// Sat
			var form_item_sat_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sat_checkbox.TCheckbox( {field: 'sat'} );
			form_item_sat_checkbox.setValue( data.sat ? data.sat : false );
			widgets[form_item_sat_checkbox.getField()] = form_item_sat_checkbox;
			widgetContainer2.append( form_item_sat_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_sat_checkbox );

			row.children().eq( 2 ).append( widgetContainer2 );

			// Shift Time
			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var divContainer1 = $( "<div></div>" );

			var label_1 = $( "<span class='widget-right-label' style='margin-right: 15px;'> " + $.i18n._( 'In' ) + ': ' + " </span>" );
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: 'start_time', width: 65} );
			form_item_input.setValue( data.start_time ? data.start_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {

				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets )
			} )

			widgets[form_item_input.getField()] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			var divContainer2 = $( "<div style='margin-top: 5px'></div>" );

			var label_2 = $( "<span class='widget-right-label'> " + $.i18n._( 'Out' ) + ': ' + " </span>" );
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: 'end_time', width: 65} );
			form_item_input.setValue( data.end_time ? data.end_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets )
			} );

			widgets[form_item_input.getField()] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 3 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Total
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'total_time'} );
			form_item_input.setValue( data.total_time ? Global.secondToHHMMSS( data.total_time ) : '' ); //
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 4 ).append( form_item_input );

			// Schedule Policy

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APISchedulePolicy' )),
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.SCHEDULE_POLICY,
				show_search_inputs: true,
				set_empty: true,
				field: 'schedule_policy_id'
			} );

			form_item_input.setValue( data.schedule_policy_id ? data.schedule_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 5 ).append( form_item_input );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets )
			} );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Branch / Department

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			divContainer1 = $( "<div></div>" );

			label_1 = $( "<span class='widget-right-label' style='float: left; height: 24px; line-height: 24px; min-width: 74px;'> " + $.i18n._( 'Branch' ) + ': ' + " </span>" );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.BRANCH,
				show_search_inputs: true,
				set_any: true,
				field: 'branch_id',
				custom_first_label: Global.default_item
			} );
			form_item_input.setValue( data.branch_id ? data.branch_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );

			divContainer2 = $( "<div style='margin-top: 5px; float: left'></div>" );

			label_2 = $( "<span class='widget-right-label' style='float: left; height: 24px; line-height: 24px; min-width: 74px;'> " + $.i18n._( 'Department' ) + ': ' + " </span>" );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				width: 132,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.DEPARTMENT,
				show_search_inputs: true,
				set_any: true,
				field: 'department_id',
				custom_first_label: Global.default_item
			} );
			form_item_input.setValue( (data.department_id) ? data.department_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 6 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Job/Task

			if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

				widgetContainer = $( "<div class='widget-h-box'></div>" );

				divContainer1 = $( "<div></div>" );

				label_1 = $( "<span class='widget-right-label' style='float: left; height: 24px; line-height: 24px; min-width: 32px;'> " + $.i18n._( 'Job' ) + ': ' + " </span>" );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJob' )),
					width: 132,
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB,
					show_search_inputs: true,
					set_any: true,
					field: 'job_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_id ? data.job_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer1.append( label_1 );
				divContainer1.append( form_item_input );

				widgetContainer.append( divContainer1 );

				divContainer2 = $( "<div style='margin-top: 5px; float: left'></div>" );

				label_2 = $( "<span class='widget-right-label' style='float: left; height: 24px; line-height: 24px; min-width: 32px;'> " + $.i18n._( 'Task' ) + ': ' + " </span>" );
				this.setWidgetEnableBaseOnParentController( form_item_input );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
					width: 132,
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.JOB_ITEM,
					show_search_inputs: true,
					set_any: true,
					field: 'job_item_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_item_id ? data.job_item_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer2.append( label_2 );
				divContainer2.append( form_item_input );

				widgetContainer.append( divContainer2 );

				row.children().eq( 7 ).append( widgetContainer );
				this.setWidgetEnableBaseOnParentController( form_item_input );

			}

			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				// Open Shift Multiplier
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'open_shift_multiplier', width: 20} );
				form_item_input.setValue( data.open_shift_multiplier ? data.open_shift_multiplier : 1 );
				widgets[form_item_input.getField()] = form_item_input;

				if ( LocalCacheData.getCurrentCompany().product_edition_id < 20 ) {
					row.children().eq( 7 ).append( form_item_input );
				} else {
					row.children().eq( 8 ).append( form_item_input );
				}

				this.setWidgetEnableBaseOnParentController( form_item_input );
			}

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

			if ( widgets.status_id.getValue() === 10 ) {
				widgets.absence_policy_id.css( 'display', 'none' );
			} else if ( widgets.status_id.getValue() === 20 ) {
				widgets.absence_policy_id.css( 'display', 'block' );
			}

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();
		}

	},

	onRowChanges: function( row_widgets ) {

		var startTime = row_widgets.start_time.getValue();
		var endTime = row_widgets.end_time.getValue();
		var schedulePolicyId = row_widgets.schedule_policy_id.getValue();

		if ( startTime !== '' && endTime !== '' && schedulePolicyId !== '' ) {
			var total_time = this.schedule_api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, {async: false} ).getResult();
			row_widgets.total_time.setValue( Global.secondToHHMMSS( total_time ) );
		}
	},

	insideEditorGetValue: function( current_edit_item_id ) {

		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];

			var data = {
				tue: row.tue.getValue(),
				fri: row.fri.getValue(),
				total_time: null,
				absence_policy_id: row.absence_policy_id.getValue(),
				status_id: row.status_id.getValue(),
				mon: row.mon.getValue(),
				schedule_policy_id: row.schedule_policy_id.getValue(),
				week: row.week.getValue(),
				thu: row.thu.getValue(),
				department_id: row.department_id.getValue(),
				start_time: row.start_time.getValue(),
				branch_id: row.branch_id.getValue(),
				end_time: row.end_time.getValue(),
				sun: row.sun.getValue(),
				sat: row.sat.getValue(),
				wed: row.wed.getValue(),
				id: row.week.attr( 'recurring_schedule_template_id' )
			};

			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				data.open_shift_multiplier = row.open_shift_multiplier.getValue();
			}

			if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

				data.job_id = row.job_id.getValue();
				data.job_item_id = row.job_item_id.getValue();

			}

			data.recurring_schedule_template_control_id = current_edit_item_id;
			result.push( data );

		}

		return result;
	},

	insideEditorSetValue: function( val ) {
		var len = val.length;
		this.removeAllRows();

		if ( len > 0 ) {
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.getDefaultData();
		}

	},

	insideEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].week.attr( 'recurring_schedule_template_id' );
		if ( remove_id > 0 ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );

		this.removeLastRowLine();
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data
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

	onSaveAndContinueResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data
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
				$this.refresh_id = result_data
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
				$this.refresh_id = result_data
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
			var result_data = result.getResult()
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data
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
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				this.editor.rows_widgets_array[i].week.attr( 'recurring_schedule_template_id', '' );
			}
			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
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

	saveInsideEditorData: function( callBack ) {

		var $this = this;
		var data = this.editor.getValue( this.refresh_id );
		var remove_ids = $this.editor.delete_ids;

		if ( remove_ids.length > 0 ) {
			$this.recurring_schedule_template_api.deleteRecurringScheduleTemplate( remove_ids, {onResult: function( res ) {
				$this.editor.delete_ids = [];
			}} )
		}

		$this.recurring_schedule_template_api.setRecurringScheduleTemplate( data, {onResult: function( res ) {
			var res_data = res.getResult();
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}} )

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

			new SearchField( {label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Template' ),
				in_column: 1,
				field: 'id',
				layout_name: ALayoutIDs.RECURRING_TEMPLATE_CONTROL,
				api_class: (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' )),
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
				form_item_type: FormItemType.AWESOME_BOX} )];
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

		var recurring_schedule = new RibbonSubMenu( {
			label: $.i18n._( 'Recurring<br>Schedules' ),
			id: ContextMenuIconName.recurring_schedule,
			group: navigation_group,
			icon: Icons.recurring_schedule,
			permission_result: true,
			permission: null
		} );

		return [menu];

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
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
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
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.save_and_new:
				ProgressBar.showOverlay();
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				ProgressBar.showOverlay();
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.recurring_schedule:
				this.onNavigationClick( id );
				break;

		}
	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {filter_data: {}};

		var recurring_schedule_template_control_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			recurring_schedule_template_control_ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				recurring_schedule_template_control_ids.push( grid_selected_row.id );
			} );
		}

		filter.filter_data.recurring_schedule_template_control_id = recurring_schedule_template_control_ids;

		switch ( iconName ) {

			case ContextMenuIconName.recurring_schedule:

				Global.addViewTab( this.viewId, 'Recurring Templates', window.location.href );
				IndexViewController.goToView( 'RecurringScheduleControl', filter );

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
				case ContextMenuIconName.recurring_schedule:
					this.setDefaultMenuRecurringScheduleIcon( context_btn, grid_selected_length );
					break;

			}

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenuRecurringScheduleIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'RecurringScheduleControl' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
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
				case ContextMenuIconName.recurring_schedule:
					this.setEditMenuRecurringScheduleIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuRecurringScheduleIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}

	}

} );

RecurringScheduleTemplateControlViewController.loadView = function() {

	Global.loadViewSource( 'RecurringScheduleTemplateControl', 'RecurringScheduleTemplateControlView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};