ReCalculateTimeSheetWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'TimeSheet ReCalculation Wizard' );
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
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel()
				label.text( $.i18n._( 'Recalculating timesheets is only required when policies have been modified and need to be applied retroactively.' ) );

				this.content_div.append( label );
				break;
			case 2:
				label = this.getLabel()
				label.text( $.i18n._( 'Select one or more pay periods' ) )

				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriod' )), true, ALayoutIDs.PAY_PERIOD, 'pay_period_id' );
				var div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );

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

	buildCurrentStepData: function() {

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();
		var pay_period_ids = this.stepsDataDic[2].pay_period_id;
		var user_ids = this.stepsDataDic[3].user_id;

		var timesheet_api = new (APIFactory.getAPIClass( 'APITimeSheet' ))();

		timesheet_api.reCalculateTimeSheet( pay_period_ids, user_ids, {onResult: function( result ) {
			$this.onCloseClick();

			if ( $this.call_back ) {
				$this.call_back();
			}

		}} );
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
				break;
			case 3:

				if ( current_step_data.user_id ) {
					current_step_ui.user_id.setValue( current_step_data.user_id );
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
				break;
			case 2:
				current_step_data.pay_period_id = current_step_ui.pay_period_id.getValue();
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