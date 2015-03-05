ProcessPayrollWizardController = BaseWizardController.extend( {

	el: '.wizard',

	all_columns: null,

	api_pay_period: null,

	alert_message: $.i18n._( 'Please select one or more pay periods in the list above to enable icons.' ),

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Payroll Processing Wizard' );
		this.steps = 9;
		this.current_step = 1;
		this.script_name = 'wizard_process_payroll';
		this.wizard_id = 'ProcessPayrollWizard';
		this.api_pay_period = new (APIFactory.getAPIClass( 'APIPayPeriod' ))();

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initUserGenericData();

	},

	//Create each page UI
	buildCurrentStepUI: function() {

		var $this = this;
		this.content_div.empty();
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Select one or more pay periods to process payroll for' ) + ':' );

				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriod' )), true, ALayoutIDs.PAY_PERIOD, 'pay_period_id' );
				var div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );
				break;
			case 2:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm all requests are authorized' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				var grid_id = 'pending_request';
				var grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				var ribbon_button_box = this.getRibbonButtonBox();
				var request_button = this.getRibbonButton( ContextMenuIconName.request, Global.getRibbonIconRealPath( Icons.request ), $.i18n._( 'Requests' ) );

				request_button.unbind( 'click' ).bind( 'click', function() {

					$this.onNavigationClick( ContextMenuIconName.request );
				} );

				ribbon_button_box.children().eq( 0 ).append( request_button );
				this.content_div.append( ribbon_button_box );

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm that no critical exceptions exist' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'exceptions';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				var ribbon_btn = this.getRibbonButton( ContextMenuIconName.exceptions, Global.getRibbonIconRealPath( Icons.exceptions ), $.i18n._( 'Exceptions' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.exceptions );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				this.content_div.append( ribbon_button_box );
				break;
			case 4:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm timesheets are verified' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'timesheet';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.timesheet_reports, Global.getRibbonIconRealPath( Icons.timesheet_reports ), $.i18n._( 'TimeSheet Summary' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.timesheet_reports );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				this.content_div.append( ribbon_button_box );
				break;
			case 5:
				label = this.getLabel();
				label.text( $.i18n._( 'Lock pay periods to prevent changes' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'lock_pay_period';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.lock, Global.getRibbonIconRealPath( Icons.lock ), $.i18n._( 'Lock' ) );
				var ribbon_btn2 = this.getRibbonButton( ContextMenuIconName.unlock, Global.getRibbonIconRealPath( Icons.unlock ), $.i18n._( 'UnLock' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( ContextMenuIconName.lock );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( ContextMenuIconName.unlock );
				} );

				this.stepsWidgetDic[this.current_step].lock = ribbon_btn;
				this.stepsWidgetDic[this.current_step].unlock = ribbon_btn2;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				this.content_div.append( ribbon_button_box );
				break;
			case 6:
				label = this.getLabel();
				label.text( $.i18n._( 'Create any necessary pay stub amendments' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_amendments';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.pay_stub_amendment, Global.getRibbonIconRealPath( Icons.pay_stub_amendment ), $.i18n._( 'Pay Stub<br>Amendments' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.pay_stub_amendment );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				this.content_div.append( ribbon_button_box );
				break;
			case 7:
				label = this.getLabel();
				label.text( $.i18n._( 'Generate pay stubs' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_generate';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.generate_pay_stub, Global.getRibbonIconRealPath( Icons.re_cal_pay_stub ), $.i18n._( 'Generate Pay Stubs' ) );
				ribbon_btn2 = this.getRibbonButton( ContextMenuIconName.pay_stub, Global.getRibbonIconRealPath( Icons.pay_stubs ), $.i18n._( 'Pay Stubs' ) );
				var ribbon_btn3 = this.getRibbonButton( ContextMenuIconName.pay_stub_summary, Global.getRibbonIconRealPath( Icons.pay_stub_account ), $.i18n._( 'Pay Stub Summary' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );

						return;
					}
					$this.onNavigationClick( ContextMenuIconName.generate_pay_stub );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.pay_stub );
				} );

				ribbon_btn3.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.pay_stub_summary );
				} );

				this.stepsWidgetDic[this.current_step].generate_pay_stub = ribbon_btn;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn3 );

				this.content_div.append( ribbon_button_box );
				break;
			case 8:
				label = this.getLabel();
				label.text( $.i18n._( 'Transfer funds or write checks' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_transfer';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.payroll_export_report, Global.getRibbonIconRealPath( Icons.payroll_export_report ), $.i18n._( 'Payroll Export' ) );
				ribbon_btn2 = this.getRibbonButton( ContextMenuIconName.pay_stub, Global.getRibbonIconRealPath( Icons.pay_stubs ), $.i18n._( 'Pay Stubs' ) );
				ribbon_btn3 = this.getRibbonButton( ContextMenuIconName.pay_stub_summary, Global.getRibbonIconRealPath( Icons.pay_stub_account ), $.i18n._( 'Pay Stub Summary' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.payroll_export_report );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.pay_stub );
				} );

				ribbon_btn3.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( ContextMenuIconName.pay_stub_summary );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn3 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

				this.content_div.append( ribbon_button_box );
				break;
			case 9:
				label = this.getLabel();
				label.text( $.i18n._( 'Close pay period' ) + ':' );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_close';
				grid_div = $( "<div class='grid-div wizard-grid-div'> <table id='" + grid_id + "'></table></div>" );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( ContextMenuIconName.close_pay_period, Global.getRibbonIconRealPath( Icons.close_pay_period ), $.i18n._( 'Close' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( ContextMenuIconName.close_pay_period );
				} );

				this.stepsWidgetDic[this.current_step].close = ribbon_btn;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

				this.content_div.append( ribbon_button_box );
				break;

		}
	},

	onDoneClick: function() {
		this.cleanStepsData();
		LocalCacheData.current_open_wizard_controller = null;
		this.saveAllStepsToUserGenericData( function() {

		} );

		if ( this.call_back ) {
			this.call_back();
		}

		$( this.el ).remove();

	},

	getGridColumns: function( gridId, callBack ) {

		var $this = this;

		if ( this.all_columns ) {
			doNext();
		} else {
			var result = this.api_pay_period.getOptions( 'columns', {async: false} );
			var result_data = result.getResult();
			$this.all_columns = Global.buildColumnArray( result_data );
			doNext();
		}

		function doNext() {

			var len = $this.all_columns.length;
			var column_info_array = [];

			switch ( gridId ) {
				case 'pending_request':

					for ( var i = 0; i < len; i++ ) {
						var column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pending_requests' ) {
							var column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'exceptions':

					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'exceptions_high' ||
							column_data.value == 'exceptions_medium' ||
							column_data.value == 'exceptions_low' ||
							column_data.value == 'exceptions_critical'
							) {

							if ( column_data.value == 'exceptions_high' ||
								column_data.value == 'exceptions_medium' ||
								column_data.value == 'exceptions_low' ||
								column_data.value == 'exceptions_critical' ) {

								column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 50, sortable: false, title: false};
								column_info_array.push( column_info );

							} else {
								column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
								column_info_array.push( column_info );
							}

						}

					}

					break;

				case 'timesheet':

					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'verified_timesheets' ||
							column_data.value == 'pending_timesheets' ||
							column_data.value == 'total_timesheets'
							) {

							if ( column_data.value == 'verified_timesheets' ||
								column_data.value == 'pending_timesheets' ||
								column_data.value == 'total_timesheets' ) {

								column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 50, sortable: false, title: false};
								column_info_array.push( column_info );

							} else {
								column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
								column_info_array.push( column_info );
							}

						}

					}

					break;
				case 'lock_pay_period':
					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'status'
							) {

							column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'pay_stub_amendments':
					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'ps_amendments'
							) {

							column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'pay_stub_generate':
				case 'pay_stub_transfer':

					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pay_stubs'
							) {

							column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'pay_stub_close':
					for ( i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'status' ||
							column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pay_stubs'
							) {

							column_info = {name: column_data.value, index: column_data.value, label: column_data.label, width: 100, sortable: false, title: false};
							column_info_array.push( column_info );

						}

					}

					break;

			}

			callBack( column_info_array );

		}

	},

	setGridGroupColumns: function( gridId ) {

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + gridId + ']' )[0] );

		var new_tr = $( '<tr class="group-column-tr" >' +
			'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
			'<span class="group-column-label"></span>' +
			'</th>' );

		switch ( gridId ) {
			case 'exceptions':

				var ths = table.children( 0 ).children( 0 ).children();

				var default_th = new_th.clone();
				default_th.attr( 'colspan', '1' );
				default_th.width( ths.eq( 0 ).width() );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 1 ).width() * 3 );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '4' );
				default_th.width( ths.eq( 4 ).width() * 4 );

				default_th.children( 0 ).text( $.i18n._( 'Exceptions' ) );

				new_tr.append( default_th );

				table.children( 0 ).prepend( new_tr );
				break;
			case 'timesheet':

				ths = table.children( 0 ).children( 0 ).children();

				default_th = new_th.clone();
				default_th.attr( 'colspan', '1' );
				default_th.width( ths.eq( 0 ).width() );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 1 ).width() * 3 );
				new_tr.append( default_th );

				default_th = new_th.clone();

				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 4 ).width() * 3 );

				default_th.children( 0 ).text( $.i18n._( 'Timesheets' ) );

				new_tr.append( default_th );

				table.children( 0 ).prepend( new_tr );

		}

	},

	onNavigationClick: function( iconName ) {

		var $this = this;
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid;
		var ids;
		var data_array;
		var filter;
		switch ( iconName ) {
			case ContextMenuIconName.exceptions:
				filter = {filter_data: {}};
				grid = current_step_ui.exceptions;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );

				var pay_period_ids = {value: ids };
				filter.filter_data.pay_period_id = pay_period_ids;

				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();
				IndexViewController.goToView( 'Exception', filter );

				break;
			case ContextMenuIconName.request:
				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );

				IndexViewController.goToView( 'Request' );

				this.onCloseClick();
				break;
			case ContextMenuIconName.timesheet_reports:
				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();

				LocalCacheData.default_filter_for_next_open_view = {template: 'by_pay_period_by_employee+verified_time_sheet'};

				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'TimesheetSummaryReport' );

				break;
			case ContextMenuIconName.lock:
				grid = current_step_ui.lock_pay_period;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( var i = 0; i < ids.length; i++ ) {
					var data = {};
					data.id = ids[i];
					data.status_id = 12;
					data_array.push( data );
				}

				this.api_pay_period.setPayPeriod( data_array, {onResult: function( result ) {
					if ( result.isValid() ) {
						$this.buildCurrentStepData();
					} else {
						TAlertManager.showErrorAlert( result );
					}
				}} );
				break;
			case ContextMenuIconName.unlock:
				grid = current_step_ui.lock_pay_period;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( i = 0; i < ids.length; i++ ) {
					data = {};
					data.id = ids[i];
					data.status_id = 10;
					data_array.push( data );
				}

				this.api_pay_period.setPayPeriod( data_array, {onResult: function( result ) {
					if ( result.isValid() ) {
						$this.buildCurrentStepData();
					} else {
						TAlertManager.showErrorAlert( result );
					}
				}} );
				break;
			case ContextMenuIconName.pay_stub_amendment:

				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();

				grid = current_step_ui.pay_stub_amendments;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
				filter = {filter_data: {pay_period_id: {value: ids}}};
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case ContextMenuIconName.generate_pay_stub:

				grid = current_step_ui.pay_stub_generate;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );

				new (APIFactory.getAPIClass( 'APIPayStub' ))().generatePayStubs( ids, {onResult: function( result ) {
					if ( result.isValid() ) {
						var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );

						if ( user_generic_status_batch_id && user_generic_status_batch_id > 0 ) {
							UserGenericStatusWindowController.open( user_generic_status_batch_id, [], function() {
								$this.buildCurrentStepData();
							} );
						}
					}
				}} );

				break;
			case ContextMenuIconName.pay_stub:

				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();

				if ( this.current_step === 7 ) {
					grid = current_step_ui.pay_stub_generate;
				} else {
					grid = current_step_ui.pay_stub_transfer;
				}
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
				filter = {filter_data: {pay_period_id: {value: ids}}};
				IndexViewController.goToView( 'PayStub', filter );
				break;
			case ContextMenuIconName.pay_stub_summary:
				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();
				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'PayStubSummaryReport' );

				break;
			case ContextMenuIconName.payroll_export_report:
				Global.addViewTab( this.wizard_id, 'Process Payroll', window.location.href );
				this.onCloseClick();
				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'PayrollExportReport' );

				break;
			case ContextMenuIconName.close_pay_period:
				grid = current_step_ui.pay_stub_close;
				ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( i = 0; i < ids.length; i++ ) {
					data = {};
					data.id = ids[i];
					data.status_id = 20;
					data_array.push( data );
				}

				this.api_pay_period.setPayPeriod( data_array, {onResult: function( result ) {
					if ( result.isValid() ) {
						$this.buildCurrentStepData();
					} else {
						TAlertManager.showErrorAlert( result );
					}
				}} );
				break;

		}
	},

	onGridSelectRow: function( e ) {
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid;
		var ids;
		if ( this.current_step === 5 ) {
			grid = current_step_ui.lock_pay_period;
			ids = grid.jqGrid( 'getGridParam', 'selarrrow' );

			if ( ids.length < 1 ) {
				current_step_ui.lock.addClass( 'disable-image' );
				current_step_ui.unlock.addClass( 'disable-image' );
			} else {
				current_step_ui.lock.removeClass( 'disable-image' );
				current_step_ui.unlock.removeClass( 'disable-image' );
			}

		} else if ( this.current_step === 7 ) {
			grid = current_step_ui.pay_stub_generate;
			ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
			if ( ids.length < 1 ) {
				current_step_ui.generate_pay_stub.addClass( 'disable-image' );
			} else {
				current_step_ui.generate_pay_stub.removeClass( 'disable-image' );
			}

		} else if ( this.current_step === 9 ) {
			grid = current_step_ui.pay_stub_close;
			ids = grid.jqGrid( 'getGridParam', 'selarrrow' );
			if ( ids.length < 1 ) {
				current_step_ui.close.addClass( 'disable-image' );
			} else {
				current_step_ui.close.removeClass( 'disable-image' );
			}

		}

	},

	setGridAutoHeight: function( grid, length ) {
		if ( length > 0 && length < 10 ) {
			grid.setGridHeight( length * 23 );
		} else if ( length > 10 ) {
			grid.setGridHeight( 230 );
		}
	},

	buildCurrentStepData: function() {

		if ( !this.stepsDataDic[1] ) {
			return;
		}

		var pay_period_id = this.stepsDataDic[1].pay_period_id;

		if ( !pay_period_id || pay_period_id.length == 0 ) {
			pay_period_id = [0]
		}
		var args = {};
		var grid;
		var $this = this;
		var source_data;

		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 2:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pending_requests = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.pending_request;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );

					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 3:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.exceptions_high = true;
				args.filter_columns.exceptions_medium = true;
				args.filter_columns.exceptions_low = true;
				args.filter_columns.exceptions_critical = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.exceptions;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );
					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 4:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.verified_timesheets = true;
				args.filter_columns.pending_timesheets = true;
				args.filter_columns.total_timesheets = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.timesheet;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );

					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 5:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.status = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.lock_pay_period;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );
					$this.setGridSelection( grid, source_data );

					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 6:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.ps_amendments = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.pay_stub_amendments;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );
					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 7:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.pay_stub_generate;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );
					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 8:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.pay_stub_transfer;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );
					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;
			case 9:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.status = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, {onResult: function( result ) {
					source_data = result.getResult();
					grid = current_step_ui.pay_stub_close;
					grid.clearGridData();
					grid.setGridParam( {data: source_data} );
					grid.trigger( 'reloadGrid' );

					$this.setGridSelection( grid, source_data );
					$this.setGridAutoHeight( grid, source_data.length );

				}} );
				break;

		}

	},

	setCurrentStepValues: function() {

		if ( !this.stepsDataDic[this.current_step] ) {
			return;
		} else {
			var current_step_data = this.stepsDataDic[this.current_step];
			var current_step_ui = this.stepsWidgetDic[this.current_step];
		}

		switch ( this.current_step ) {
			case 1:
				if ( current_step_data.pay_period_id ) {
					current_step_ui.pay_period_id.setValue( current_step_data.pay_period_id );
				}
				break;
			case 2:
				break;
			case 3:
				break;
		}
	},

	setGridSelection: function( grid, source_data ) {
		if ( source_data ) {
			$.each( source_data, function( index, content ) {
				grid.jqGrid( 'setSelection', content['id'], false );
			} );
		}
	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		//Error: TypeError: current_step_ui is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20150126-115958 line 2 > eval line 989
		if(!current_step_ui){
			return;
		}
		switch ( this.current_step ) {
			case 1:
				current_step_data.pay_period_id = current_step_ui.pay_period_id.getValue();
				break;
			case 2:
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

//		  this.stepsDataDic[2] = {};
//		  this.stepsDataDic[3] = {};
//
//		  if ( this.getDefaultData( 'user_id' ) ) {
//			  this.stepsDataDic[3].user_id = this.getDefaultData( 'user_id' );
//		  }
//
//		  if ( this.getDefaultData( 'pay_period_id' ) ) {
//			  this.stepsDataDic[2].pay_period_id = this.getDefaultData( 'pay_period_id' );
//		  }

	}


} );