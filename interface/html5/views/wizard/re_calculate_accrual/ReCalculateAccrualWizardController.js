ReCalculateAccrualWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Accrual ReCalculation Wizard' );
		this.steps = 3;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	//Create each page UI
	buildCurrentStepUI: function() {

		this.content_div.empty();
		var $this = this;
		this.stepsWidgetDic[this.current_step] = {};
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Select one or more accrual policies' ) + ':' );

				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIAccrualPolicy' )), true, ALayoutIDs.ACCRUAL_POLICY, 'accrual_policy_id' );
				var div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );
				break;
			case 2:
				label = this.getLabel();
				label.text( $.i18n._( 'Select the date range' ) + ':' );

				this.content_div.append( label );

				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );

				var combobox = this.getComboBox( 'time_period', true );

				form_item_label.text( $.i18n._( 'Time Period' ) + ': ' );
				form_item_input_div.append( combobox );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var start_picker = this.getDatePicker( 'start_date' );

				form_item_label.text( $.i18n._( 'Start Date' ) + ': ' );
				form_item_input_div.append( start_picker );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var end_picker = this.getDatePicker( 'end_date' );

				form_item_label.text( $.i18n._( 'End Date' ) + ': ' );
				form_item_input_div.append( end_picker );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var pay_period = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriod' )), true, ALayoutIDs.PAY_PERIOD, 'pay_period_id' );

				form_item_label.text( $.i18n._( 'Pay Period' ) + ': ' );
				form_item_input_div.append( pay_period );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var pay_period_schedule = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )), true, ALayoutIDs.PAY_PERIOD_SCHEDULE, 'pay_period_schedule_id' );

				form_item_label.text( $.i18n._( 'Pay Period Schedule' ) + ': ' );
				form_item_input_div.append( pay_period_schedule );

				this.content_div.append( form_item );

				combobox.bind( 'formItemChange', function( e, target ) {
					$this.onTimePeriodChange( target );
				} );

				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				this.stepsWidgetDic[this.current_step][start_picker.getField()] = start_picker;
				this.stepsWidgetDic[this.current_step][end_picker.getField()] = end_picker;
				this.stepsWidgetDic[this.current_step][pay_period.getField()] = pay_period;
				this.stepsWidgetDic[this.current_step][pay_period_schedule.getField()] = pay_period_schedule;

				$this.onTimePeriodChange( combobox );

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Select one or more employees' ) + ':' );

				a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIUser' )), true, ALayoutIDs.USER, 'user_id', true );
				div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );
				break;
		}
	},

	onTimePeriodChange: function( target ) {
		var value = target.getValue();

		var start_date_div = this.stepsWidgetDic[this.current_step]['start_date'].parent().parent();
		var end_date_div = this.stepsWidgetDic[this.current_step]['end_date'].parent().parent();
		var pay_period_div = this.stepsWidgetDic[this.current_step]['pay_period_id'].parent().parent();
		var pay_period_schedule_div = this.stepsWidgetDic[this.current_step]['pay_period_schedule_id'].parent().parent();

		if ( value === 'custom_date' ) {
			start_date_div.css( 'display', 'block' );
			end_date_div.css( 'display', 'block' );
			pay_period_div.css( 'display', 'none' );
			pay_period_schedule_div.css( 'display', 'none' );
		} else if ( value === 'custom_pay_period' ) {
			start_date_div.css( 'display', 'none' );
			end_date_div.css( 'display', 'none' );
			pay_period_div.css( 'display', 'block' );
			pay_period_schedule_div.css( 'display', 'none' );
		} else if ( value === 'this_pay_period' || value === 'last_pay_period' || value === 'to_last_pay_period' || value === 'to_this_pay_period' ) {
			start_date_div.css( 'display', 'none' );
			end_date_div.css( 'display', 'none' );
			pay_period_div.css( 'display', 'none' );
			pay_period_schedule_div.css( 'display', 'block' );
		} else {
			start_date_div.css( 'display', 'none' );
			end_date_div.css( 'display', 'none' );
			pay_period_div.css( 'display', 'none' );
			pay_period_schedule_div.css( 'display', 'none' );
		}

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();
		var accrual_policy_id = this.stepsDataDic[1].accrual_policy_id;
		var user_ids = this.stepsDataDic[3].user_id;

		var time_period = {};
		time_period.time_period = this.stepsDataDic[2].time_period;

		for ( var key in this.stepsDataDic[2] ) {
			if ( !this.stepsDataDic[2].hasOwnProperty( [key] ) || key === 'time_period' || key === 'effective_date' ) continue;

			time_period[key] = this.stepsDataDic[2][key];

		}

		var accrual_policy_api = new (APIFactory.getAPIClass( 'APIAccrualPolicy' ))();

		accrual_policy_api.recalculateAccrual( accrual_policy_id, time_period, user_ids, {onResult: function( result ) {
			$this.onCloseClick();

			if ( $this.call_back ) {
				$this.call_back();
			}

		}} );
	},

	buildCurrentStepData: function() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				if ( current_step_data ) {
					for ( var key in current_step_data ) {
						if ( !current_step_data.hasOwnProperty( key ) ) continue;

						current_step_ui[key].setValue( current_step_data[key] );
					}
				}

				break;
			case 2:
				new (APIFactory.getAPIClass( 'APITimesheetSummaryReport' ))().getOptions( 'time_period', {onResult: function( result ) {

					current_step_ui['time_period'].setSourceData( Global.buildRecordArray( result.getResult() ) );

					if ( !current_step_data ) {
						var date = new Date();
						current_step_ui.time_period.setValue( 'last_month' );
					} else {
						for ( var key in current_step_data ) {
							if ( !current_step_data.hasOwnProperty( key ) ) continue;

							current_step_ui[key].setValue( current_step_data[key] );
						}
					}

					$this.onTimePeriodChange( current_step_ui['time_period'] );
				}
				} );
				break;
			case 3:

				if ( !current_step_data ) {
					current_step_ui['user_id'].setValue( -1 );
				} else {
					for ( key in current_step_data ) {
						if ( !current_step_data.hasOwnProperty( key ) ) continue;

						current_step_ui[key].setValue( current_step_data[key] );
					}
				}

				break;
			default:
				for ( key in current_step_data ) {
					if ( !current_step_data.hasOwnProperty( key ) ) continue;

					current_step_ui[key].setValue( current_step_data[key] );
				}
				break;
		}

	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				current_step_data.accrual_policy_id = current_step_ui.accrual_policy_id.getValue();
				break;
			case 2:
				for ( var key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) continue;

					if ( current_step_ui[key].is( ':visible' ) ) {
						current_step_data[key] = current_step_ui[key].getValue();
					}

				}
				break;
			case 3:
				current_step_data.user_id = current_step_ui.user_id.getValue();
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

		this.stepsDataDic[1] = {};

		if ( this.getDefaultData( 'accrual_policy_id' ) ) {
			this.stepsDataDic[1].accrual_policy_id = this.getDefaultData( 'accrual_policy_id' );
		}

	}


} );
