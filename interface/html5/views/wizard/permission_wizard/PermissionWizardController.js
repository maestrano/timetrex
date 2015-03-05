PermissionWizardController = BaseWizardController.extend( {

	el: '.wizard',
	api_permission: null,

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Permission Wizard' );
		this.steps = 3;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );
		this.api_permission = new (APIFactory.getAPIClass( 'APIPermission' ))();
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
				label.text( $.i18n._( 'Choose role and permission actions' ) + ':' );

				this.content_div.append( label );

				//Role
				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );

				var combobox = this.getComboBox( 'role', false );

				form_item_label.text( $.i18n._( 'Role' ) + ': ' );
				form_item_input_div.append( combobox );

				this.content_div.append( form_item );

				//Permission
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var permission = this.getAComboBox( null, true, ALayoutIDs.OPTION_COLUMN, 'permission', false, 'value' );

				form_item_label.text( $.i18n._( 'Permissions' ) + ': ' );
				form_item_input_div.append( permission );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;
				this.stepsWidgetDic[this.current_step][permission.getField()] = permission;

				break;
			case 2:

				label = this.getLabel();
				label.text( $.i18n._( 'Choose modules and sub-modules' ) );

				this.content_div.append( label );

				//Modules
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var modules = this.getAComboBox( null, true, ALayoutIDs.OPTION_COLUMN, 'section_group', false, 'value' );

				form_item_label.text( $.i18n._( 'Modules' ) + ': ' );
				form_item_input_div.append( modules );

				this.content_div.append( form_item );

				form_item.bind( 'formItemChange', function( e, target ) {
					var select_value = target.getValue();
					$this.setSection( select_value, true );

				} );

				//Sub Modules
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );

				var sub_modules = this.getAComboBox( null, true, ALayoutIDs.OPTION_COLUMN, 'section', false, 'value' );

				form_item_label.text( $.i18n._( 'Sub-Modules' ) + ': ' );
				form_item_input_div.append( sub_modules );

				this.content_div.append( form_item );

				this.stepsWidgetDic[this.current_step][modules.getField()] = modules;
				this.stepsWidgetDic[this.current_step][sub_modules.getField()] = sub_modules;

				break;
			case 3:

				label = this.getLabel();
				label.text( $.i18n._( 'Would you like to allow, deny or simply highlight the chosen permissions' ) + ':' );

				this.content_div.append( label );

				var radio_1 = $( '<input type="radio" name="action" id="allow" value="allow"><label for="allow">Allow </label>' );
				var radio_2 = $( '<input type="radio" name="action" id="deny" value="deny"><label for="deny">Deny </label>' );
				var radio_3 = $( '<input type="radio" name="action" id="highlight" value="highlight"><label for="highlight">Highlight Only </label>' );

				this.content_div.append( radio_1 );
				this.content_div.append( radio_2 );
				this.content_div.append( radio_3 );

				this.stepsWidgetDic[this.current_step]['allow'] = radio_1;
				this.stepsWidgetDic[this.current_step]['deny'] = radio_2;
				this.stepsWidgetDic[this.current_step]['highlight'] = radio_3;

				break;

		}

	},

	setSection: function( section_group_id, select_all ) {
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		this.api_permission.getSectionBySectionGroup( section_group_id, {onResult: function( result ) {
			var data = Global.buildRecordArray( result.getResult() );
			current_step_ui.section.setSourceData( data );
			if ( select_all ) {
				current_step_ui.section.setValue( data );
			}

		}} );
	},

	buildCurrentStepData: function() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 1:

				this.api_permission.getOptions( 'preset', {onResult: function( result ) {
					var data = Global.buildRecordArray( result.getResult() );

					data.unshift( {value: 0, label: $.i18n._( 'CUSTOM' )} );

					current_step_ui.role.setSourceData( data );

					if ( current_step_data ) {
						current_step_ui.role.setValue( current_step_data.role );
					}

				}} );

				this.api_permission.getOptions( 'common_permissions', {onResult: function( result ) {
					var data = Global.buildRecordArray( result.getResult() );

					current_step_ui.permission.setSourceData( data );

					if ( current_step_data ) {
						current_step_ui.permission.setValue( current_step_data.permission );
					} else {
						current_step_ui.permission.setValue( data );
					}

				}} );

				break;

			case 2:

				this.api_permission.getOptions( 'section_group', {onResult: function( result ) {

					var data = Global.buildRecordArray( result.getResult() );

					current_step_ui.section_group.setSourceData( data );

					if ( current_step_data ) {

						if ( current_step_data.section_group && current_step_data.section_group.length > 0 ) {
							current_step_ui.section_group.setValue( current_step_data.section_group );
							$this.setSection( current_step_data.section_group );
							current_step_ui.section.setValue( current_step_data.section );
						}

					} else {
						current_step_ui.section_group.setValue( data[0].value );
						$this.setSection( data[0].value, true );

					}

				}} );

				break;

			case 3:

				if ( current_step_data ) {
					current_step_ui[current_step_data.action].attr( 'checked', 'checked' );
				} else {
					current_step_ui['allow'].attr( 'checked', 'checked' );

				}
				break;

		}

	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();

		var preset = this.stepsDataDic[1].role;
		var sections = this.stepsDataDic[2].section;
		var permission = this.stepsDataDic[1].permission;
		var permission_status = this.stepsDataDic[3].action;

		this.api_permission.filterPresetPermissions( preset, sections, permission, {onResult: function( result ) {

			if ( !result.isValid ) {
				TAlertManager.showErrorAlert( result );
				$this.onCloseClick();
			} else {

				$this.onCloseClick();
				if ( $this.call_back ) {
					$this.call_back( result.getResult(), permission_status );
				}
			}
		}} );

	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 3:
				for ( key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) continue;

					if ( current_step_ui[key].attr( 'checked' ) || current_step_ui[key][0].checked === true ) {
						current_step_data.action = key;
					}

				}
				break;

			default:

				for ( var key in current_step_ui ) {
					if ( !current_step_ui.hasOwnProperty( key ) ) continue;

					current_step_data[key] = current_step_ui[key].getValue();
				}
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

	}


} );