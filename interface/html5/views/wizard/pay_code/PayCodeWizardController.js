PayCodeWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Migrate Pay Codes' );
		this.steps = 2;
		this.current_step = 1;
		$(this.el ).width( 1010 );

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
				label.html( $.i18n._( 'This wizard will migrate data associated with one pay code to another pay code without recalculating timesheets or otherwise affecting employees time or wages. ' ) + '<br><br>' +
							'<span style="color: #ff0000; font-weight: bold;">' + $.i18n._('WARNING') + ': ' + '</span>' +
							$.i18n._( 'This operation can not be reversed once complete.' ) );

				this.content_div.append( label );
				break;
			case 2:
				var content = $($( this.el ).find( '.wizard-content' ));

				var content_clone = content.clone();

				this.stepsWidgetDic[this.current_step] = {};

				// Select Source Pay Codes

				var first_hr = content_clone.find('.first-hr');
				first_hr.find( '.wizard-item-label > span' ).text( $.i18n._( 'Select Source Pay Codes' ) + ': ' );
				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayCode' )), true, ALayoutIDs.PAY_CODE, 'source_pay_code_ids' );
				first_hr.find( '.wizard-item-widget' ).append( a_combobox );

				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				// Select Destination Pay Code

				var second_hr = content_clone.find('.second-hr');
				second_hr.find( '.wizard-item-label > span' ).text( $.i18n._( 'Select Destination Pay Code' ) + ': ' );
				a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayCode' )), false, ALayoutIDs.PAY_CODE, 'dest_pay_code_id' );
				second_hr.find( '.wizard-item-widget' ).append( a_combobox );

				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				content_clone.appendTo( this.content_div );

				break;
		}
	},

	buildCurrentStepData: function() {

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();
		var source_pay_code_ids = this.stepsDataDic[2].source_pay_code_ids;
		var dest_pay_code_id = this.stepsDataDic[2].dest_pay_code_id;

		var pay_code_api = new (APIFactory.getAPIClass( 'APIPayCode' ))();

		pay_code_api.migratePayCode( source_pay_code_ids, dest_pay_code_id, {onResult: function( result ) {
			var result_data = result.getResult();
			if ( result_data ) {
				$this.onCloseClick();

				if ( $this.call_back ) {
					$this.call_back();
				}
			} else {
				TAlertManager.showErrorAlert( result );
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
				if ( current_step_data.source_pay_code_ids ) {
					current_step_ui.source_pay_code_ids.setValue( current_step_data.source_pay_code_ids );
				}
				if ( current_step_data.dest_pay_code_id ) {
					current_step_ui.dest_pay_code_id.setValue( current_step_data.dest_pay_code_id );
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
				current_step_data.source_pay_code_ids = current_step_ui.source_pay_code_ids.getValue();
				current_step_data.dest_pay_code_id = current_step_ui.dest_pay_code_id.getValue();
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

		this.stepsDataDic[2] = {};

		if ( this.getDefaultData( 'source_pay_code_ids' ) ) {
			this.stepsDataDic[2].source_pay_code_ids = this.getDefaultData( 'source_pay_code_ids' );
		}

		if ( this.getDefaultData( 'dest_pay_code_id' ) ) {
			this.stepsDataDic[2].dest_pay_code_id = this.getDefaultData( 'dest_pay_code_id' );
		}

	}


} );