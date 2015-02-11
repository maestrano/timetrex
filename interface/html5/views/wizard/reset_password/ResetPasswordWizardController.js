ResetPasswordWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Reset Password' );
		this.steps = 1;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	buildCurrentStepUI: function() {

		var $this = this;
		this.content_div.empty();

		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( this.default_data.message );
				this.content_div.append( label );

				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );

				var user_name = this.getText();

				user_name.text( this.default_data.user_name );

				form_item_label.text( $.i18n._( 'User Name' ) + ':' );
				form_item_input_div.append( user_name );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var current_password = this.getPasswordInput( 'current_password' );

				form_item_label.text( $.i18n._( 'Current Password' ) + ':' );
				form_item_input_div.append( current_password );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var new_password = this.getPasswordInput( 'new_password' );

				form_item_label.text( $.i18n._( 'New Password' ) + ':' );
				form_item_input_div.append( new_password );

				this.content_div.append( form_item );

				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var confirm_password = this.getPasswordInput( 'confirm_password' );

				form_item_label.text( $.i18n._( 'New Password (Confirm)' ) + ':' );
				form_item_input_div.append( confirm_password );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][current_password.getField()] = current_password;
				this.stepsWidgetDic[this.current_step][new_password.getField()] = new_password;
				this.stepsWidgetDic[this.current_step][confirm_password.getField()] = confirm_password;

				break;

		}
	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			default:

				for ( var key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) continue;

					current_step_data[key] = current_step_ui[key].getValue();
				}
				break;
		}

	},

	buildCurrentStepData: function() {

	},

	onCloseClick: function() {

		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;
		LocalCacheData.extra_filter_for_next_open_view = null;

	},

	onDoneClick: function() {

		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();

		var current_password = this.stepsDataDic[1].current_password;
		var new_password = this.stepsDataDic[1].new_password;
		var confirm_password = this.stepsDataDic[1].confirm_password;

		this.stepsWidgetDic[1].current_password.clearErrorStyle();
		this.stepsWidgetDic[1].new_password.clearErrorStyle();
		this.stepsWidgetDic[1].confirm_password.clearErrorStyle();

		if ( !current_password ) {
			this.stepsWidgetDic[1].current_password.setErrorStyle( $.i18n._( 'Current password can\'t be empty' ), true );
		} else if ( !new_password ) {
			this.stepsWidgetDic[1].new_password.setErrorStyle( $.i18n._( 'New password can\'t be empty' ), true );
		} else if ( new_password !== confirm_password ) {
			this.stepsWidgetDic[1].new_password.setErrorStyle( $.i18n._( 'New password does not match' ), true );
		} else {

			var api = new (APIFactory.getAPIClass( 'APIAuthentication' ))();

			api.changePassword( this.default_data.user_name,
				current_password,
				new_password,
				confirm_password
				, {onResult: function( result ) {

					if ( !result.isValid() ) {
						TAlertManager.showErrorAlert( result );
					} else {
						$this.onCloseClick();

						if ( $this.call_back ) {
							$this.call_back();
						}
					}

				}} )
		}

	}


} );

ResetPasswordWizardController.type = '';