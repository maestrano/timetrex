GeneratePayStubWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Generate Pay Stub Wizard' );
		this.steps = 3;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	buildCurrentStepUI: function() {

		this.content_div.empty();

		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel()
				label.text( $.i18n._( "Generate pay stubs for individual employees when manual modifications or a termination occurs. Use Payroll -> Process Payroll if you wish to generate pay stubs for all employees instead." ) )

				this.content_div.append( label );
				break;
			case 2:

				label = this.getLabel()
				label.text( $.i18n._( 'Select one or more pay periods' ) );

				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriod' )), true, ALayoutIDs.PAY_PERIOD, 'pay_period_id' );
				var div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				var label_1 = this.getLabel();
				label_1.text( $.i18n._( 'The selected Pay Period is currently in Post Adjustment state,	 would you like to calculate Pay Stub Amendment adjustments to carry over into the next pay period?' ) )

				var check_box = this.getCheckBox( 'calculate_pay_stub_amendment' );

				this.content_div.append( label );
				this.content_div.append( div );
				this.content_div.append( label_1 );
				this.content_div.append( check_box );

				label_1.hide();
				check_box.hide();

				this.stepsWidgetDic[this.current_step][check_box.getField()] = check_box;
				this.stepsWidgetDic[this.current_step][check_box.getField() + '_label'] = label_1;

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Select one or more employees' ) )

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

	buildCurrentStepData: function() {

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();
		var api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		var pay_period_ids = this.stepsDataDic[2].pay_period_id;
		var user_ids = this.stepsDataDic[3].user_id;
		var cal_pay_stub_amendment = this.stepsDataDic[2].calculate_pay_stub_amendment;

		if ( cal_pay_stub_amendment ) {
			api.generatePayStubs( pay_period_ids, user_ids, cal_pay_stub_amendment, {onResult: onDoneResult} );

		} else {
			api.generatePayStubs( pay_period_ids, user_ids, {onResult: onDoneResult} );

		}

		function onDoneResult( result ) {
			if ( result.isValid() ) {
				var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );

				if ( user_generic_status_batch_id && user_generic_status_batch_id > 0 ) {
					UserGenericStatusWindowController.open( user_generic_status_batch_id, user_ids, function() {
						if ( cal_pay_stub_amendment ) {
							var filter = {filter_data: {}};
							var users = {value: user_ids };
							filter.filter_data.user_id = users;
							IndexViewController.goToView( 'PayStubAmendment', filter );
						}

					} );
				}
			}

			$this.onCloseClick();

			if ( $this.call_back ) {
				$this.call_back();
			}
		}

		$this.onCloseClick();
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
				break;
			case 2:
				if ( current_step_data.pay_period_id ) {
					current_step_ui.pay_period_id.setValue( current_step_data.pay_period_id );
				}

				this.setStep2CheckBoxVisibility( current_step_data.pay_period_id );
				break;
			case 3:

				if ( current_step_data.user_id ) {
					current_step_ui.user_id.setValue( current_step_data.user_id );
				}

				break;
		}
	},

	setStep2CheckBoxVisibility: function( pay_period_ids ) {
		var args = {};
		args.filter_data = {};

		var current_step_ui = this.stepsWidgetDic[this.current_step];

		if ( !pay_period_ids ) {
			current_step_ui[ 'calculate_pay_stub_amendment' ].hide();
			current_step_ui[ 'calculate_pay_stub_amendment_label' ].hide();
			current_step_ui[ 'calculate_pay_stub_amendment' ].setValue( false );
			return;
		}
		args.filter_data.id = pay_period_ids;

		var api = new (APIFactory.getAPIClass( 'APIPayPeriod' ))();

		api.getPayPeriod( args, {onResult: function( result ) {

			var result_data = result.getResult();
			current_step_ui[ 'calculate_pay_stub_amendment' ].show();
			current_step_ui[ 'calculate_pay_stub_amendment_label' ].show();

			var len = result_data.length;

			for ( var i = 0; i < len; i++ ) {
				var item = result_data[i];

				if ( item.status_id !== 30 ) {
					current_step_ui[ 'calculate_pay_stub_amendment' ].hide();
					current_step_ui[ 'calculate_pay_stub_amendment_label' ].hide();
					current_step_ui[ 'calculate_pay_stub_amendment' ].setValue( false );
					break;
				}
			}

		}} )

	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				break;
			case 2:
				current_step_data.pay_period_id = current_step_ui.pay_period_id.getValue();
				current_step_data.calculate_pay_stub_amendment = current_step_ui.calculate_pay_stub_amendment.getValue();
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

		this.stepsDataDic[2] = {};
		this.stepsDataDic[3] = {};

		if ( this.getDefaultData( 'user_id' ) ) {
			this.stepsDataDic[3].user_id = this.getDefaultData( 'user_id' );
		}

		if ( this.getDefaultData( 'pay_period_id' ) ) {
			this.stepsDataDic[2].pay_period_id = this.getDefaultData( 'pay_period_id' );
		}

	}


} );
