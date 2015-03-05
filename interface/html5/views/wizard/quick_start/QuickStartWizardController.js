QuickStartWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Quick Start Wizard' );
		this.steps = 5;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	//Create each page UI
	buildCurrentStepUI: function() {
		var $this = this;
		this.content_div.empty();
		this.stepsWidgetDic[this.current_step] = {};
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Welcome to' ) + ' ' + LocalCacheData.getApplicationName() + ', ' + $.i18n._( 'this Quick Start Wizard will walk you through the initial setup by asking you a few basic questions about your company.' ) );

				var guide_label = $( '<div><span class="clear-both-div">' + $.i18n._( 'Press' ) + '<button style="display: inline" class="forward-btn"><span style="opacity: 0">.</span></button> ' + $.i18n._( 'below to continue' ) + '</span></div>' );

				this.content_div.append( label );

				this.content_div.append( guide_label );
				break;

			case 2:
				Global.setWidgetEnabled( this.next_btn, false );
				label = this.getLabel();
				label.text( $.i18n._( 'Please choose the preferred settings that you would like to use to display information throughout' + ' ' + LocalCacheData.getApplicationName() + '.' ) );

				this.content_div.append( label );

				//Time Zone
				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );

				var combobox = this.getComboBox( 'time_zone', true );

				form_item_label.text( $.i18n._( 'Timezone' ) + ': ' );
				form_item_input_div.append( combobox );

				this.content_div.append( form_item );

				//Date Format
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var date_format = this.getComboBox( 'date_format', true );

				form_item_label.text( $.i18n._( 'Date Format' ) + ': ' );
				form_item_input_div.append( date_format );

				this.content_div.append( form_item );

				//Time Format
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var time_format = this.getComboBox( 'time_format', true );

				form_item_label.text( $.i18n._( 'Time Format' ) + ': ' );
				form_item_input_div.append( time_format );

				this.content_div.append( form_item );

				//Calendar Starts On
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var calendar_starts_on = this.getComboBox( 'start_week_day', true );

				form_item_label.text( $.i18n._( 'Calendar Starts On' ) + ': ' );
				form_item_input_div.append( calendar_starts_on );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;
				this.stepsWidgetDic[this.current_step][date_format.getField()] = date_format;
				this.stepsWidgetDic[this.current_step][time_format.getField()] = time_format;
				this.stepsWidgetDic[this.current_step][calendar_starts_on.getField()] = calendar_starts_on;
				break;

			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Pay period schedules are critical to the operation of' ) + ' ' + LocalCacheData.getApplicationName() + ' ' + $.i18n._( 'regardless if you use it for processing payroll or not. Please select the pay period frequency and enter the start date, end date and transaction date (date the employees are paid) for your next four pay periods. Based on this information' ) + ' ' + LocalCacheData.getApplicationName() + ' ' + $.i18n._( 'will automatically create subsequent pay periods for you.' ) );

				this.content_div.append( label );

				//Pay Period Frequency
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var frequency = this.getComboBox( 'type_id', false );

				form_item_label.text( $.i18n._( 'Pay Period Frequency' ) + ': ' );
				form_item_input_div.append( frequency );

				frequency.bind( 'formItemChange', function( e, target ) {
					$this.onFrequencyChange( target )
				} );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][frequency.getField()] = frequency;

				//Example Dates
				var grid_id = 'example_dates';
				var grid_div = $( "<div style='float: left; width: 100%'  class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setDateGrid( grid_id, grid_div, 300 );

				break;
			case 4:
				label = this.getLabel();
				label.text( $.i18n._( 'To help determine how' ) + ' ' + LocalCacheData.getApplicationName() + ' ' + $.i18n._( 'should be initially setup, please select one or more locations that your employees reside within.' ) );
				this.content_div.append( label );

				guide_label = $( '<div><span class="clear-both-div">' + $.i18n._( 'Click' ) + ' <button style="display: inline" class="plus-icon"><span style="opacity: 0">.</span></button> ' + $.i18n._( 'icon to add additional locations' ) + '</span></div>' );
				this.content_div.append( guide_label );

				//Inside editor

				var args = { country: $.i18n._( 'Country' ),
					province: $.i18n._( 'Province' )
				};

				var editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

				editor.InsideEditor( {title: '',
					addRow: this.insideEditorAddRow,
					removeRow: this.insideEditorRemoveRow,
					getValue: this.insideEditorGetValue,
					setValue: this.insideEditorSetValue,
					parent_controller: this,
					render: 'views/wizard/quick_start/QuickStartInsideEditorRender.html',
					render_args: args,
					row_render: 'views/wizard/quick_start/QuickStartInsideEditorRow.html'
				} );

				editor.addClass( 'wizard-inside-editor' );

				this.content_div.append( editor );

				this.stepsWidgetDic[this.current_step]['country'] = editor;
				break;
			case 5:
				label = this.getLabel();
				label.text( LocalCacheData.getApplicationName() + ' ' + $.i18n._( 'is now setup and ready for you to start adding employees and tracking their attendance.' ) );
				this.content_div.append( label );

				guide_label = $( '<div><span class="clear-both-div">' + $.i18n._( 'Click the' ) + ' <button style="display: inline" class="done-btn"><span style="opacity: 0">.</span></button> ' + $.i18n._( 'icon below to begin doing that now' ) + '</span></div>' );
				this.content_div.append( guide_label );

				break;

		}
	},

	initInsideEditorData: function() {
		var $this = this;

		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		var editor = current_step_ui['country'];
		var args = {};
		args.filter_data = {};

		if ( !current_step_data ) {
			editor.removeAllRows();
			editor.addRow();

		} else {
			editor.setValue( current_step_data.country );

		}

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
			this.addRow();
		}

	},

	insideEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex - 1;
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();
	},

	insideEditorAddRow: function( data, index ) {

		var form_item_input;

		var $this = this;
		if ( !data ) {
			data = {country: LocalCacheData.getCurrentCompany().country, province: LocalCacheData.getCurrentCompany().province};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		// Country

		var widgetContainer = $( "<ul style='list-style: none; padding: 0; margin: 0;'></ul>" );

		var widgetContainer1 = $( "<li class='widget-h-box' style='float: left; margin-right: 10px'></li>" );

		var country = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		country.TComboBox( {field: 'country', set_empty: true} );

		var country_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		country_api.getOptions( 'country', {onResult: function( result ) {
			var result_data = result.getResult();
			country.setSourceData( Global.buildRecordArray( result_data ) );

			country.setValue( data.country );

		}} );

		widgets[country.getField()] = country;

		country.bind( 'formItemChange', function( e, target ) {
			$this.parent_controller.setProvince( {country: target.getValue(), province: ''}, province );

		} );

		widgetContainer1.append( country );

		widgetContainer.append( widgetContainer1 );

		row.children().eq( 0 ).append( widgetContainer );

		// Province

		widgetContainer = $( "<ul style='list-style: none; padding: 0; margin: 0;'></ul>" );

		widgetContainer1 = $( "<li class='widget-h-box' style='float: left; margin-right: 10px'></li>" );

		var province = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		province.TComboBox( {field: 'province', set_empty: true} );

		this.parent_controller.setProvince( data, province );

		widgets[province.getField()] = province;

		widgetContainer1.append( province );

		widgetContainer.append( widgetContainer1 );

		row.children().eq( 1 ).append( widgetContainer );

		if ( typeof index != 'undefined' ) {

			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( (index), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		widgets.current_edit_item = data;

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();

	},

	setProvince: function( val, province ) {
		var $this = this;

		if ( !val.country ) {
			province.setSourceData( [] );
			province.setValue( 0 );
			return;

		}

		var country_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		country_api.getOptions( 'province', val.country, {onResult: function( res ) {
			res = res.getResult();

			province.setSourceData( Global.buildRecordArray( res ) );

			province.setValue( val.province );

		}} );
	},

	onFrequencyChange: function( target ) {

		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid = current_step_ui['example_dates'];
		var data = grid.getGridParam( 'data' );

		var first_cell_val;

		//rror: Uncaught TypeError: Cannot read property 'start_date' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Schedule&date=20141201&mode=week line 332 
		if ( data && data[0] ) {
			first_cell_val = data[0].start_date;
		}

		if ( first_cell_val ) {
			this.setDefaultDates( true );
		}

	},

	setDateGrid: function( gridId, grid_div, height ) {
		var $this = this;

		this.content_div.append( grid_div );

		var grid = grid_div.find( '#' + gridId );

		if ( !height ) {
			height = 370;
		}

		this.getGridColumns( gridId, function( result ) {

			grid = grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				height: height,
				rowNum: 10000,
				colNames: [],
				colModel: result,
				viewrecords: true,
				editurl: 'clientArray',

				onSelectRow: function( id ) {
					if ( id ) {

					}
				}

			} );

			$this.stepsWidgetDic[$this.current_step][gridId] = grid;

			$this.setGridSize( grid );

			$this.setGridGroupColumns( gridId );

		} );
	},

	onTextInputRender: function( cell_value, related_data, row ) {

		var col_model = related_data.colModel;
		var row_id = related_data.rowId;

		var date_picker = $( '<div custom_cell="true" render_type="date_picker" id="' + row_id + '_' + col_model.name + '" class="t-date-picker-div"><input class="t-date-picker" type="text" value="' + cell_value + '" /><img id="tDatePickerIcon" class="t-date-picker-icon"/></div>' );

		return date_picker.get( 0 ).outerHTML;
	},

	onCloseClick: function() {
		var $this = this;
		if ( !LocalCacheData.getCurrentCompany().is_setup_complete ) {
			TAlertManager.showConfirmAlert( $.i18n._( 'Would you like to be reminded to complete the Quick Start Wizard next time you login?' ), '', function( flag ) {
				if ( !flag ) {
					var company = {};
					company.id = LocalCacheData.getCurrentCompany().id;
					company.is_setup_complete = true;

					var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
					LocalCacheData.getCurrentCompany().is_setup_complete = true;

					company_api.setCompany( company, {onResult: function() {

					}} );
				}

				$( $this.el ).remove();
				LocalCacheData.current_open_wizard_controller = null;
			} )
		} else {
			$( $this.el ).remove();
			LocalCacheData.current_open_wizard_controller = null;

		}
	},

	onStep3DatePickerChange: function( target ) {

		var $this = this;
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid = current_step_ui['example_dates'];
		var target_id = target.attr( 'id' );
		var row_id = target_id.split( '_' )[0];
		var field = target_id.substring( target_id.indexOf( '_' ) + 1, target_id.length );
		var data = grid.getGridParam( 'data' );
		var target_val = target.getValue();

		var first_cell_val = data[0].start_date;

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var row_data = data[i];
			if ( (i + 1) === parseInt( row_id ) ) {
				row_data[field] = target_val;

				if ( i === 0 && field === 'start_date' ) {

					if ( first_cell_val && first_cell_val !== target_val ) {
						$this.setDefaultDates( true );
					} else if ( !first_cell_val ) {
						$this.setDefaultDates();
					}

				}

				break;
			}

		}

	},

	setDefaultDates: function( show_alert ) {
		var $this = this;
		if ( show_alert ) {
			TAlertManager.showConfirmAlert( $.i18n._( 'Would you like to pre-populate all date fields based on the first start date' ), '', function( flag ) {
				if ( flag ) {
					doNext();
				}
			} )
		} else {
			doNext();
		}

		function doNext() {

			var type_id = $this.stepsWidgetDic[3].type_id.getValue();
			var grid = $this.stepsWidgetDic[3].example_dates;
			var data = grid.getGridParam( 'data' );
			var first_date = data[0].start_date;

			var api = new (APIFactory.getAPIClass( 'APIPayPeriodSchedule' ))();

			api.detectPayPeriodScheduleDates( type_id, first_date, {onResult: function( result ) {
				var res_data = result.getResult();
				grid.clearGridData();
				grid.setGridParam( {data: res_data} );
				grid.trigger( 'reloadGrid' );

				$this.setStep3CellDatePickers( grid );

			}} );
		}

	},

	getGridColumns: function( gridId, callBack ) {
		var column_info_array = [];
		var $this = this;

		switch ( gridId ) {
			case 'example_dates':

				var column_info = {name: 'start_date', index: 'start_date', label: 'Start Date', width: 100, sortable: false, title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onTextInputRender( cell_value, related_data, row );
					}};
				column_info_array.push( column_info );

				column_info = {name: 'end_date', index: 'end_date', label: 'End Date', width: 100, sortable: false, title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onTextInputRender( cell_value, related_data, row );
					}};
				column_info_array.push( column_info );

				column_info = {name: 'transaction_date', index: 'transaction_date', label: 'Transaction Date', width: 100, sortable: false, title: false,
					formatter: function( cell_value, related_data, row ) {
						return $this.onTextInputRender( cell_value, related_data, row );
					}};
				column_info_array.push( column_info );

				break;

		}

		callBack( column_info_array );

	},

	buildCurrentStepData: function() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		var api_return_count = 0;

		switch ( this.current_step ) {
			case 2:

				Global.setWidgetEnabled( this.next_btn, false );
				Global.setWidgetEnabled( this.back_btn, false );
				var api_user_preference = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
				var api_current_user = new (APIFactory.getAPIClass( 'APICurrentUser' ))();

				//Time ZOne
				api_user_preference.getOptions( 'time_zone', {onResult: function( result ) {

					current_step_ui['time_zone'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( current_step_data ) {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					}

					step2OptionsCallBack();

				}} );

				//Time Format
				api_user_preference.getOptions( 'time_format', {onResult: function( result ) {

					current_step_ui['time_format'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( current_step_data ) {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					}

					step2OptionsCallBack();

				}} );

				//Calendar Starts On
				api_user_preference.getOptions( 'start_week_day', {onResult: function( result ) {

					current_step_ui['start_week_day'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( current_step_data ) {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					}

					step2OptionsCallBack();

				}} );

				api_current_user.getCurrentUserPreference( {onResult: function( result ) {
					var res_data = result.getResult();
					setStep2Values( res_data );

				}} );

				break;
			case 3:
				var api_pp_schedule = new (APIFactory.getAPIClass( 'APIPayPeriodSchedule' ))();
				api_pp_schedule.getOptions( 'type', {onResult: function( result ) {
					var res_data = Global.buildRecordArray( result.getResult() );
					res_data.splice( 0, 1 );

					current_step_ui['type_id'].setSourceData( res_data );

					if ( current_step_data ) {
						var array = current_step_data['example_dates'];

						var grid = current_step_ui['example_dates'];
						grid.clearGridData();
						grid.setGridParam( {data: array} );
						grid.trigger( 'reloadGrid' );
						$this.setStep3CellDatePickers( grid );
						current_step_ui['type_id'].setValue( current_step_data['type_id'] );

					} else {
						buildStep3EmptySource();
					}

				}} );

				break;
			case 4:
				$this.initInsideEditorData();
				break;
		}

		function step2OptionsCallBack() {
			api_return_count = api_return_count + 1;
			if ( api_return_count === 4 ) {
				Global.setWidgetEnabled( $this.next_btn, true );
				Global.setWidgetEnabled( $this.back_btn, true );
			}
		}

		function setStep2Values( user_preference ) {

			var t = current_step_ui['time_zone'];
			var d = current_step_ui['date_format'];
			var tf = current_step_ui['time_format'];
			var s = current_step_ui['start_week_day'];

			if ( user_preference.language === "en" ) {
				api_user_preference.getOptions( 'date_format', {onResult: function( result ) {

					current_step_ui['date_format'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( current_step_data ) {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					} else {
						t.setValue( user_preference.time_zone );
						d.setValue( user_preference.date_format );
						tf.setValue( user_preference.time_format );
						s.setValue( user_preference.start_week_day );
					}

				}} );
			} else {
				api_user_preference.getOptions( 'other_date_format', {onResult: function( result ) {

					current_step_ui['date_format'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( current_step_data ) {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					} else {
						t.setValue( user_preference.time_zone );
						d.setValue( user_preference.date_format );
						tf.setValue( user_preference.time_format );
						s.setValue( user_preference.start_week_day );
					}

				}} );
			}

			step2OptionsCallBack();

		}

		function buildStep3EmptySource() {
			var array = [
				{ start_date: '', end_date: '', transaction_date: '' },
				{ start_date: '', end_date: '', transaction_date: '' },
				{ start_date: '', end_date: '', transaction_date: '' },
				{ start_date: '', end_date: '', transaction_date: '' }
			];

			var grid = current_step_ui['example_dates'];
			grid.clearGridData();
			grid.setGridParam( {data: array} );
			grid.trigger( 'reloadGrid' );

			$this.setStep3CellDatePickers( grid );

		}
	},

	setStep3CellDatePickers: function( grid ) {
		var inputs = grid.find( 'div[custom_cell="true"]' );
		var $this = this;
		for ( var i = 0; i < inputs.length; i++ ) {
			var input = $( inputs[i] ).TDatePicker( {width: 290} );

			input.bind( 'formItemChange', function( e, target ) {
				$this.onStep3DatePickerChange( target );
			} );

		}

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		$( $this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;

		IndexViewController.goToView( 'Employee' );
	},

	insideEditorGetValue: function() {

		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = {
				country: row.country.getValue(),
				province: row.province.getValue()
			};

			result.push( data );

		}

		return result;
	},

	onNextClick: function() {

		var $this = this;

		this.saveCurrentStep( function( result ) {
			Global.setWidgetEnabled( $this.next_btn, true );
			Global.setWidgetEnabled( $this.back_btn, true );
			if ( result ) {
				$this.current_step = $this.current_step + 1;
				$this.initCurrentStep();
			}
		} );

	},

	onBackClick: function() {
		var $this = this;

		this.saveCurrentStep( function( result ) {
			Global.setWidgetEnabled( $this.next_btn, true );
			Global.setWidgetEnabled( $this.back_btn, true );
			if ( result ) {
				$this.current_step = $this.current_step - 1;
				$this.initCurrentStep();
			}
		} );
	},

	saveCurrentStep: function( callBack ) {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				callBack( true );
				break;
			case 2:
				Global.setWidgetEnabled( this.next_btn, false );
				Global.setWidgetEnabled( this.back_btn, false );
				current_step_data.time_zone = current_step_ui.time_zone.getValue();
				current_step_data.time_format = current_step_ui.time_format.getValue();
				current_step_data.date_format = current_step_ui.date_format.getValue();
				current_step_data.start_week_day = current_step_ui.start_week_day.getValue();
				var api_current_user = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
				var api_user_preference = new (APIFactory.getAPIClass( 'APIUserPreference' ))();

				api_current_user.getCurrentUserPreference( {onResult: function( result ) {
					var res_data = result.getResult();

					res_data.date_format = current_step_data.date_format;
					res_data.time_format = current_step_data.time_format;
					res_data.time_zone = current_step_data.time_zone;
					res_data.start_week_day = current_step_data.start_week_day;

					api_user_preference.setUserPreference( res_data, {onResult: function() {

						Global.updateUserPreference( function() {
							callBack( true );
						} );
					}} );

				}} );

				break;
			case 3:
				Global.setWidgetEnabled( this.next_btn, false );
				Global.setWidgetEnabled( this.back_btn, false );

				current_step_data.type_id = current_step_ui.type_id.getValue();
				current_step_data.example_dates = current_step_ui.example_dates.getGridParam( 'data' );

				var api_pp_schedule = new (APIFactory.getAPIClass( 'APIPayPeriodSchedule' ))();

				api_pp_schedule.detectPayPeriodScheduleSettings( current_step_data.type_id, current_step_data.example_dates, {onResult: function( result ) {

					result = result.getResult();

					if ( result.hasOwnProperty( 'company_id' ) ) {

						api_pp_schedule.setPayPeriodSchedule( result, {onResult: function( result_1 ) {

							if ( result_1.isValid() ) {
								callBack( true );
							} else {
								TAlertManager.showErrorAlert( result_1 );
								callBack( false );
							}

						}} );
					} else {
						callBack( true );
					}

				}} );

				break;
			case 4:
				Global.setWidgetEnabled( this.next_btn, false );
				Global.setWidgetEnabled( this.back_btn, false );
				current_step_data.country = current_step_ui.country.getValue();
				var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

				company_api.createPresets( current_step_data.country, {onResult: function( result ) {

					if ( result.isValid ) {
						callBack( true );
					} else {
						TAlertManager.showErrorAlert( result );
						callBack( false );
					}
				}} );
				break;
			case 5:
				callBack( true );
				break;
		}

	}

} );