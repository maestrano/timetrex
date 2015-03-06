JobItemAmendmentViewController = BaseViewController.extend( {
	el: '#job_item_amendment_view_container',

	current_default_record: {},

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'JobItemAmendmentEditView.html';
		this.permission_id = 'job_item';
		this.viewId = 'JobItemAmendment';
		this.script_name = 'JobItemAmendmentView';
		this.table_name_key = 'job_item_amendment';
		this.context_menu_name = $.i18n._( 'Task Amendments' );
		this.navigation_label = $.i18n._( 'Task Amendment' ) + ':';

		this.api = new (APIFactory.getAPIClass( 'APIJobItemAmendment' ))();

		this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
		this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'JobItemAmendment' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_task_amendment': $.i18n._( 'Task Amendment' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIJobItemAmendment' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.JOB_ITEM_AMENDMENT,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_task_amendment = this.edit_view_tab.find( '#tab_task_amendment' );

		var tab_task_amendment_column1 = tab_task_amendment.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_task_amendment_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Job
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIJob' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.JOB,
			show_search_inputs: true,
			set_empty: false,
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
		this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_task_amendment_column1, '', widgetContainer );

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
			field: 'item_id'
		} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
		job_item_coder.addClass( 'job-coder' );

		widgetContainer.append( job_item_coder );
		widgetContainer.append( form_item_input );
		this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_task_amendment_column1, '', widgetContainer );

		//Estimated Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'estimate_time', width: 150, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + " </span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Estimated Time' ), form_item_input, tab_task_amendment_column1, '', widgetContainer, true, true );

		//Billable Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'billable_rate', width: 150} );
		this.addEditFieldToColumn( $.i18n._( 'Billable Rate' ), form_item_input, tab_task_amendment_column1 );

		//Minimum Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'minimum_time', width: 150, need_parser_sec: true} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().time_unit_format_display + " </span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Billable Time' ), form_item_input, tab_task_amendment_column1, '', widgetContainer );

		//Estimated Quantity
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'estimate_quantity', width: 150} );
		this.addEditFieldToColumn( $.i18n._( 'Estimated Quantity' ), form_item_input, tab_task_amendment_column1 );

		//Estimated Bad Quantity
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'estimate_bad_quantity', width: 150} );
		this.addEditFieldToColumn( $.i18n._( 'Estimated Bad Quantity' ), form_item_input, tab_task_amendment_column1 );

		//Bad Quantity Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'bad_quantity_rate', width: 150} );
		this.addEditFieldToColumn( $.i18n._( 'Bad Quantity Rate' ), form_item_input, tab_task_amendment_column1 );

	},

	/* jshint ignore:start */
	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets

		if ( Global.isFalseOrNull( this.current_edit_record['user_id'] ) ) {
			this.current_edit_record['user_id'] = LocalCacheData.getLoginUser().id;
		}

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'item_id':
						var args = {};
						args.filter_data = {job_id: this.current_edit_record.job_id};
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['item_id'] ? this.current_edit_record['item_id'] : 0 );
						break;
					default:
						if ( Global.isSet( this.current_default_record[key] ) ) {
							this.current_edit_record[key] = this.current_default_record[key];
						}
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		this.current_default_record = {};
		this._super( 'setEditViewDataDone' );
	},

	/* jshint ignore:start */
	onFormItemChange: function( target ) {
		var $this = this;

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		var amount_arr = false;

		switch ( key ) {
			case 'job_id':
				this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				this.setJobItemValueWhenJobChanged( target.getValue( true ) );
				break;
			case 'item_id':
				this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				this.setJobItemAmendmentDefaultData();
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				this.onJobQuickSearch( key, c_value );
				break;

		}

		this.validate();
	},

	validate: function() {

		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {

			if ( $this.sub_view_mode && $this.parent_key ) {
				record[$this.parent_key] = $this.parent_value;
			}

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

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value};

			this.job_api.getJob( args, {onResult: function( result ) {

				var result_data = result.getResult();

				if ( result_data.length > 0 ) {
					$this.edit_view_ui_dic['job_id'].setValue( result_data[0].id );
					$this.current_edit_record.job_id = result_data[0].id;
					$this.setJobItemValueWhenJobChanged( result_data[0] );

				} else {
					$this.edit_view_ui_dic['job_id'].setValue( '' );
					$this.current_edit_record.job_id = false;
					$this.setJobItemValueWhenJobChanged( false );
				}

			}} );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value};

			this.job_item_api.getJobItem( args, {onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data.length > 0 ) {
					$this.edit_view_ui_dic['job_item_id'].setValue( result_data[0].id );
					$this.current_edit_record.job_item_id = result_data[0].id;
					$this.setJobItemAmendmentDefaultData();

				} else {
					$this.edit_view_ui_dic['job_item_id'].setValue( '' );
					$this.current_edit_record.job_item_id = false;
				}

			}} );
		}

	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {

		var $this = this;
		var job_item_widget = $this.edit_view_ui_dic['item_id'];
		var current_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		var args = {};
		args.filter_data = {job_id: $this.current_edit_record.job_id};
		$this.edit_view_ui_dic['item_id'].setDefaultArgs( args );

		if ( current_item_id ) {

			var new_arg = Global.clone( args );

			new_arg.filter_data.id = current_item_id;
			new_arg.filter_columns = $this.edit_view_ui_dic['item_id'].getColumnFilter();
			$this.job_item_api.getJobItem( new_arg, {onResult: function( task_result ) {

				var data = task_result.getResult();

				if ( data.length > 0 ) {
					job_item_widget.setValue( current_item_id );
					$this.current_edit_record.item_id = current_item_id;

					$this.setJobItemAmendmentDefaultData();
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
				$this.current_edit_record.item_id = job.default_item_id;

				if ( job.default_item_id === false || job.default_item_id === 0 ) {
					$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
				} else {
					$this.setJobItemAmendmentDefaultData();
				}

			} else {
				job_item_widget.setValue( '' );
				$this.current_edit_record.item_id = false;
				$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );

			}
		}
	},

	setJobItemAmendmentDefaultData: function() {
		var $this = this;
		var job_item_id;
		if ( this.current_edit_record.item_id > 0 && this.is_add ) {
			job_item_id = this.current_edit_record.item_id;
			this.api['get' + this.api.key_name + 'DefaultData']( job_item_id, {onResult: function( result ) {
				var result_data = result.getResult();
				if ( !result_data ) {
					result_data = [];
				}
				$this.current_default_record = result_data;
				$this.setCurrentEditRecordData();

			}} );
		}
	}

	/* jshint ignore:end */

} );

JobItemAmendmentViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'JobItemAmendment', 'SubJobItemAmendmentView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_job_item_amendment_view_controller );
			}
		}
	} );
};
