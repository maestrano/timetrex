InstallWizardController = BaseWizardController.extend( {

	el: '.install-wizard',

	type_array: null,

	country_array: null,

	external_installer: null,

	edit_view_error_ui_dic: {},

	initialize: function() {
		this._super( 'initialize' );
		this.title_1 = $( this.el ).find( '.title-1' );
		this.steps = 5;
		this.script_name = 'wizard_install';
		this.wizard_id = 'InstallWizard';
		this.api = new (APIFactory.getAPIClass( 'APIInstall' ))();
		ServiceCaller.extra_url = '&disable_db=1';
		if ( _.size( LocalCacheData.all_url_args ) > 0  ) {
			var url_args = LocalCacheData.all_url_args;
			this.current_step = url_args.a;
			this.external_installer = url_args.external_installer;
		} else {
			this.current_step = 'license';
			this.external_installer = 0;
		}

		this.render();
	},

	render: function() {
		var $this = this;
		var title = $( this.el ).find( '.title' );

		this.next_btn = $( this.el ).find( '.forward-btn' );
		this.back_btn = $( this.el ).find( '.back-btn' );
		this.done_btn = $( this.el ).find( '.done-btn' );

		title.text( $.i18n._( 'Install Wizard' ) );

//		$( this.el ).css( {
//				width: $(window).width() - 4 ,
//				height: $(window).height() - 20
////				height: $(document ).height(),
////				'border-radius': '20px',
////				margin: 0,
////				top: 0,
////				left: 0
////				bottom: 0
//			}
//		);

		$( this.el ).css( {left:  ( Global.bodyWidth() - $(this.el ).width() )/2} );

		this.content_div.css( {height: $(this.el ).height() - 145} );

		$( window ).resize( function() {
			$( $this.el ).css( {left:  ( Global.bodyWidth() - $($this.el ).width() )/2} );
			$this.content_div.css( {height: $($this.el ).height() - 145} );
		} );

		this.initCurrentStep();
	},

	initCurrentStep: function( step ) {

		var $this = this;

		if ( step ){}
		else {
			step = this.current_step;
		}

		ProgressBar.showOverlay();

		switch( step ) {
			case 'license':
				this.api.getLicense( {onResult: function( res ){
					$this.stepsDataDic[$this.current_step] = res.getResult();
					$this._initCurrentStep();
				}} );
				break;
			case 'requirements':
				this.api.getRequirements( this.external_installer, {onResult: function( res ) {
					if ( res.isValid() ) {
						if ( res.getResult().action ) {
							$this.onNextClick();
						} else {
							$this.stepsDataDic[$this.current_step] = res.getResult();
							$this._initCurrentStep();
						}
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'databaseSchema':
				ServiceCaller.extra_url = false;
				this.api.getDatabaseSchema( {onResult: function( res ) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'databaseConfig':
				this.api.getDatabaseConfig( {onResult: function( res ) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this.type_array = res.getResult().type_options;
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'postUpgrade':
				this.api.postUpgrade( {onResult: function( res ) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}

				}} );
				break;
			case 'systemSettings':
				this.api.getSystemSettings( {onResult: function(res) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'company':
				this.api.getCompany( {onResult: function( res ) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'user':
				var company_id = '';
				if ( Global.isSet( this.stepsDataDic[this.current_step] ) ) {
					company_id = this.stepsDataDic[this.current_step]['company_id'];
				}
				this.api.getUser( company_id, {onResult: function( res ) {
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'installDone':
				var upgrade = '';
				if ( Global.isSet( $this.stepsDataDic[$this.current_step] ) ) {
					upgrade = $this.stepsDataDic[$this.current_step]['upgrade'];
				}
				this.api.installDone( upgrade, {onResult: function( res ){
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'maintenanceJobs':
				this.api.getMaintenanceJobs(  {onResult: function( res ){
					if ( res.isValid() ) {
						$this.stepsDataDic[$this.current_step] = res.getResult();
						$this._initCurrentStep();
					} else {
						$this.current_step = 'license';
						$this.initCurrentStep();
					}
				}} );
				break;
		}


	},

	_initCurrentStep: function( step ) {
		var $this = this;
		$this.setButtonsStatus(); // set button enabled or disabled
		$this.buildCurrentStepUI();
		$this.buildCurrentStepData();
		$this.setCurrentStepValues();
	},

	setButtonsStatus: function() {
		if ( this.current_step === 'license' ) {
			Global.setWidgetEnabled( this.back_btn, false );
			Global.setWidgetEnabled( this.next_btn, false );
		} else {
			Global.setWidgetEnabled( this.back_btn, true );
			Global.setWidgetEnabled( this.next_btn, true );
		}

		if ( this.current_step !== 'installDone' ) {
			Global.setWidgetEnabled( this.done_btn, false );
//			Global.setWidgetEnabled( this.next_btn, true );
		} else {
			Global.setWidgetEnabled( this.done_btn, true );
			Global.setWidgetEnabled( this.next_btn, false );
		}
	},

	//Create each page UI
	buildCurrentStepUI: function() {

		var $this = this;
		var step_title = this.content_div.find( '.step-title > .wizard-label' );
		var license = this.content_div.find( '.license' );
		var requirements = this.content_div.find( '.requirements' );
		var databaseConfig = this.content_div.find( '.databaseConfig' );
		var databaseSchema =  this.content_div.find( '.databaseSchema' );
		var postUpgrade = this.content_div.find( '.postUpgrade' );
		var systemSettings = this.content_div.find( '.systemSettings' );
		var company = this.content_div.find( '.company' );
		var user = this.content_div.find( '.user' );
		var installDone = this.content_div.find( '.installDone' );
		var maintenanceJobs = this.content_div.find( '.maintenanceJobs' );

		var form_item_input;
		var stepData = this.stepsDataDic[this.current_step];
		this.content_div.find('.step' ).hide();
		this.content_div.find('.content-handle-btn' ).empty();

		if ( Global.isSet( this.stepsWidgetDic[this.current_step] ) ) {

		} else {
			this.stepsWidgetDic[this.current_step] = {};
		}

		switch ( this.current_step ) {
			case 'license':
				license.empty();
				this.title_1.text( $.i18n._( 'License Acceptance' ) );
				if ( stepData['install_mode'] ) {
					if ( stepData['license_text'] ) {
						step_title.text( $.i18n._( 'Please read through the following license and if you agree and accept it, click the ' + '"' + $.i18n._('I Accept') + '"' + 'checkbox at the bottom.' ) );

						form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
						form_item_input.TTextArea( {field: 'license_text', width: '65%', height: '80%'} );
						this.stepsWidgetDic[this.current_step][form_item_input.getField()] = form_item_input;

						license.append( form_item_input );
					} else {
						license.append( stepData['error_message'] );
					}

					license.append( "<br>" );

					form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
					form_item_input.TCheckbox( {field: 'license_accept'} );
					form_item_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
						if ( target.getValue() ) {
							Global.setWidgetEnabled( $this.next_btn, true );
						} else {
							Global.setWidgetEnabled( $this.next_btn, false );
						}
					} );

					this.stepsWidgetDic[this.current_step][form_item_input.getField()] = form_item_input;

					license.append( form_item_input );

					var accept_label = "<span> " + $.i18n._( 'I Accept' ) + "</span>";

					license.append( accept_label );

				} else {
					var license_html = $.i18n._('The installer has already been run, as a safety measure it has been disabled from running again. If you are absolutely sure you want to run it again, or upgrade your system, please go to your timetrex.ini.php file and set "installer_enabled" to "TRUE". The line should look like') + ':';
					license_html = license_html + '<br>';
					license_html = license_html + '<br>';
					license_html = license_html + '"' + '<b>installer_enabled = TRUE</b>' +  '"';
					license_html = license_html + '<br>';
					license_html = license_html + '<br>';
					license_html = license_html + $.i18n._('After this change has been made, you can click the "Start" button below to begin your installation. ');
					license_html = license_html + '<b>' + $.i18n._('After the installation is complete, you will want to change') + ' "installer_enabled" ' + $.i18n._('to') + ' "FALSE".' + '</b>';
					license_html = license_html + '<br>';
					license_html = license_html + '<br>';
					license_html = license_html + $.i18n._('For help, please visit') + ' <a href="http://www.timetrex.com">www.timetrex.com</a> ';
					license.append( license_html );

					var ribbon_button_box = this.getRibbonButtonBox();

					var ribbon_btn = $( '<li><button class="ribbon-sub-menu-icon" id="re-check">' + $.i18n._('Re-Check') + '</button></li>' );
					ribbon_btn.unbind( 'click' ).bind( 'click', function() {
						ProgressBar.showOverlay();
						$this.api.getLicense( {onResult: function( res ){
							$this.stepsDataDic[$this.current_step] = res.getResult();
							$this._initCurrentStep();
						}} );

					} );

					ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

					this.content_div.find('.content-handle-btn' ).html( ribbon_button_box );

				}

				// set size;
				this.content_div.find('.license' ).css( {height: this.content_div.height() - 50} );
				$( window ).resize( function() {
					$this.content_div.find('.license' ).css( {height: $this.content_div.height() - 50} );
				} );

				license.show();
				break;

			case 'requirements':

				var step_title_htm = $.i18n._( 'In order for your') + ' '
					+ $.i18n._( stepData.application_name ) + ' '
					+ $.i18n._('installation to function properly, please ensure all of the system check items listed below are marked as') + ' '
					+ '<b>OK</b>' + '. '
					+ $.i18n._('If any are red, please take the necessary steps to fix them.');
				if ( stepData.check_all_requirements != 0 ) {
					step_title_htm = step_title_htm
						+ '<p style="background-color: #FFFF00">'
						+ $.i18n._('For installation support, please join our community ')
						+ '<a href="http://forums.timetrex.com" target="_blank">' + $.i18n._('forums') + '</a>'
						+ $.i18n._(' or contact a TimeTrex support expert for ')
						+ '<a href="http://www.timetrex.com/setup_support.php" target="_blank">' + $.i18n._('Implementation Support Services') + '</a></p>';

				}

				if ( stepData.check_all_requirements == 1 ) {
					Global.setWidgetEnabled( this.next_btn, false );
				}

				if ( _.size( stepData.extended_error_messages ) > 0 ) {
					Global.setWidgetEnabled( this.next_btn, false );
				}

				step_title.html( step_title_htm );

				this.title_1.text( $.i18n._( 'System Check Acceptance' ) );

				// timetrex version, php version.
				var requirements_column1 = requirements.find( '.first-column' );
				requirements_column1.empty();

				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'timetrex_version'} );
				this.addEditFieldToColumn( $.i18n._( 'TimeTrex Version' ), form_item_input, requirements_column1, '' );

				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'php_version'} );
				this.addEditFieldToColumn( $.i18n._( 'PHP Version' ), form_item_input, requirements_column1, '' );

				requirements.find( '.first-label' ).empty();

				form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
				form_item_input.SeparatedBox( {label: $.i18n._( 'PHP Requirements' )} );
				this.addEditFieldToColumn( null, form_item_input, requirements.find( '.first-label' ) );

				var requirements_column2 = requirements.find( '.second-column' );
				requirements_column2.empty();
				if ( stepData.check_all_requirements == 0 ) {

					requirements_column2.html( $.i18n._('All System Requirements have been met successfully') + '!' );
					requirements_column2.addClass('all-ok');
				} else {
					// php requirements

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'database_engine'} );
					this.addEditFieldToColumn( $.i18n._( 'Database Engine' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'bcmath'} );
					this.addEditFieldToColumn( $.i18n._( 'BCMATH Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'mbstring'} );
					this.addEditFieldToColumn( $.i18n._( 'MBSTRING Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'gettext'} );
					this.addEditFieldToColumn( $.i18n._( 'GETTEXT Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'soap'} );
					this.addEditFieldToColumn( $.i18n._( 'SOAP Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'gd'} );
					this.addEditFieldToColumn( $.i18n._( 'GD Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'json'} );
					this.addEditFieldToColumn( $.i18n._( 'JSON Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					if ( stepData.tt_product_edition >= 20 ) {
						form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
						form_item_input.TText( {field: 'mcrypt'} );
						this.addEditFieldToColumn( $.i18n._( 'MCRYPT Enabled' ), form_item_input, requirements_column2, '', null, true, true );
					}

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'simplexml'} );
					this.addEditFieldToColumn( $.i18n._( 'SimpleXML Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'zip'} );
					this.addEditFieldToColumn( $.i18n._( 'ZIP Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'mail'} );
					this.addEditFieldToColumn( $.i18n._( 'MAIL Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'pear'} );
					this.addEditFieldToColumn( $.i18n._( 'PEAR Enabled' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'safe_mode'} );
					this.addEditFieldToColumn( $.i18n._( 'Safe Mode Turned Off' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'magic_quotes'} );
					this.addEditFieldToColumn( $.i18n._( 'Magic Quotes GPC Turned Off' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'disk_space'} );
					this.addEditFieldToColumn( $.i18n._( 'Disk Space' ), form_item_input, requirements_column2, '', null, true, true );

					// other requirements
					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'memory_limit'} );
					this.addEditFieldToColumn( $.i18n._( 'Memory Limit' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'base_url'} );
					this.addEditFieldToColumn( $.i18n._( 'Base URL' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'base_dir'} );
					this.addEditFieldToColumn( $.i18n._( 'PHP Open BaseDir' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'cli_executable'} );
					this.addEditFieldToColumn( $.i18n._( 'PHP CLI Executable' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'cli_requirements'} );
					this.addEditFieldToColumn( $.i18n._( 'PHP CLI Requirements' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'config_file'} );
					this.addEditFieldToColumn( $.i18n._( 'Writable' ) + ' ' + $.i18n._(stepData.application_name) + ' ' + $.i18n._( 'Configuration File' ) + "<br>" + "(timetrex.ini.php)", form_item_input, requirements_column2, '', null, true, true );


					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'cache_dir'} );
					this.addEditFieldToColumn( $.i18n._( 'Writable Cache Directory' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'storage_dir'} );
					this.addEditFieldToColumn( $.i18n._( 'Writable Storage Directory' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'log_dir'} );
					this.addEditFieldToColumn( $.i18n._( 'Writable Log Directory' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'empty_cache_dir'} );
					this.addEditFieldToColumn( $.i18n._( 'Empty Cache Directory' ), form_item_input, requirements_column2, '', null, true, true );


					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'file_permission'} );
					this.addEditFieldToColumn( $.i18n._( 'File Permissions' ), form_item_input, requirements_column2, '', null, true, true );

					form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
					form_item_input.TText( {field: 'file_checksums'} );
					this.addEditFieldToColumn( $.i18n._( 'File CheckSums' ), form_item_input, requirements_column2, '', null, true, true );
				}

				// forth column

				var requirements_column4 = requirements.find( '.forth-column' );
				requirements_column4.empty();

				if ( _.size( stepData.extended_error_messages ) > 0 ) {
					var error_html = '<span><b>' + $.i18n._('Detailed Error Messages') + '</b></span>';
					error_html = error_html + '<br>';
					error_html = error_html + '<br>';
					for( var i in stepData.extended_error_messages ) {
						var errors = stepData['extended_error_messages'][i];
						for ( var key in errors ) {
							error_html = error_html + errors[key];
							error_html = error_html + '<br>';
						}
					}

					requirements_column4.html( error_html );
					requirements_column4.addClass('dataError');
				}

				// fifth column
				var requirements_column5 = requirements.find( '.fifth-column' );
				requirements_column5.empty();

				var columns_html = $.i18n._('Your') + ' ' + $.i18n._(stepData['application_name']) + ' ' + $.i18n._('configuration file') + ' ';
				columns_html = columns_html + '(timetrex.ini.php)';
				columns_html = columns_html + ' ' + $.i18n._('is located at') + ':';
				columns_html = columns_html + '<br>';
				columns_html = columns_html + '<p><b>' + stepData['config_file_loc'] + '</b></p>';
				columns_html = columns_html + '<br>';
				columns_html = columns_html + $.i18n._('Your PHP configuration file') + ' ' + '(php.ini)' + ' ' + $.i18n._('is located at') + ':';
				columns_html = columns_html + '<br>';

				if ( stepData['php_include_path'] ) {
					columns_html = columns_html + '<p><b>' + stepData['php_config_file'] + '</b>' + ', ' + $.i18n._('the include path is') + ': ' + '"' + '<b>' + stepData['php_include_path'] + '</b>' + '"' + '</p>';
				} else {
					columns_html = columns_html + '<p><b>' + stepData['php_config_file'] + '</b>' + ', ' + $.i18n._('the include path is') + ': ' + '""' + '</p>';
				}

				columns_html = columns_html + $.i18n._('Detailed') + '&nbsp;';
				columns_html = columns_html + '<a href="phpinfo.php" target="_blank">' + $.i18n._('PHP Information') + '</a>';

				if ( stepData.check_all_requirements == 0 ) {

				} else {
					requirements_column5.html( columns_html );
				}

				var ribbon_button_box = this.getRibbonButtonBox();

//				ribbon_button_box.css( 'width', '96%' );
				var ribbon_btn = $( '<li><button class="ribbon-sub-menu-icon" id="re-check">' + $.i18n._('Re-Check') + '</button></li>' );
				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					ProgressBar.showOverlay();
					$this.api.getRequirements( $this.external_installer, {onResult: function( res ) {
						if ( res.isValid() ) {
							$this.stepsDataDic[$this.current_step] = res.getResult();
							$this._initCurrentStep();
						}
					}} );

				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

				this.content_div.find('.content-handle-btn' ).html( ribbon_button_box );

				requirements.find( '.first-column' ).css('border', '1px solid #C7C7C7');

				// set size;
				this.content_div.find('.s-require' ).css( {height: this.content_div.height() - step_title.height() - 163} );
				$( window ).resize( function() {
					$this.content_div.find('.s-require' ).css( {height: $this.content_div.height() - step_title.height() - 163} );
				} );

				requirements.show();
				break;
			case 'user':
				step_title.html( $.i18n._( 'Please enter the administrator user name and password' )
						+'<br>'
						+'<br>'
						+'<b>' + '*' + $.i18n._('IMPORTANT') + '*' + ':' + '</b>' + ' '
						+ $.i18n._('Please write this information down, as you will need it later to login to') + ' '
						+ $.i18n._( stepData.application_name )

				);
				this.title_1.text( $.i18n._( 'Administrator Login' ) );
				var user_column1 = user.find('.first-column');
				user_column1.empty();

				// User Name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'user_name', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, user_column1 );

				// Password
				form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

				form_item_input.TTextInput( {field: 'password', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Password' ), form_item_input, user_column1 );

				// Password(confirm)
				form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

				form_item_input.TTextInput( {field: 'password2', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Password(confirm)' ), form_item_input, user_column1 );


				// First Name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'first_name', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, user_column1 );

				// Last Name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'last_name', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, user_column1 );

				// Email
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'work_email', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Email' ), form_item_input, user_column1 );
				user.find( '.first-column' ).css('border', '1px solid #C7C7C7');

				this.content_div.find('.user' ).css( {height: this.content_div.height() - step_title.height() - 15} );
				$( window ).resize( function() {
					$this.content_div.find('.user' ).css( {height: $this.content_div.height() - step_title.height() - 15} );
				} );

				user.show();
				break;

			case 'company':
				step_title.text( $.i18n._( 'Please enter your company information below.' ) );
				this.title_1.text( $.i18n._( 'Company Information' ) );
				var company_column1 = company.find('.first-column');
				company_column1.empty();

				// Company Full Name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'name', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Company Full Name' ), form_item_input, company_column1 );

				// Company Short Name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'short_name', width: 200} );

				var widgetContainer = $( "<div class='widget-h-box'></div>" );
				var label = $( "<span class='widget-right-label'> ( " + $.i18n._( 'ie' ) + ': ' + $.i18n._('America Online') + ' = ' + $.i18n._('AOL') + ', ' +  $.i18n._('no spaces') + " )</span>" );

				widgetContainer.append( form_item_input );
				widgetContainer.append( label );

				this.addEditFieldToColumn( $.i18n._( 'Company Short Name' ), form_item_input, company_column1, '', widgetContainer, null, true );

				// Industry
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: 'industry_id'} );
				form_item_input.setSourceData( Global.addFirstItemToArray( this.stepsDataDic[this.current_step]['industry_options'] ) );
				this.addEditFieldToColumn( $.i18n._( 'Industry' ), form_item_input, company_column1 );

				// Address (Line 1)
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'address1', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Address (Line 1)' ), form_item_input, company_column1 );

				// Address (Line 2)
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'address2', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Address (Line 2)' ), form_item_input, company_column1 );

				// City
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'city', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, company_column1 );

				// Country
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: 'country'} );
				form_item_input.setSourceData( Global.addFirstItemToArray( this.stepsDataDic[this.current_step]['country_options'] ) );
				this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, company_column1 );

				// Province / State
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: 'province'} );
				this.addEditFieldToColumn( $.i18n._( 'Province / State' ), form_item_input, company_column1 );

				// Postal / ZIP Code
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'postal_code', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Postal / ZIP Code' ), form_item_input, company_column1 );

				// Phone
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'work_phone', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Phone' ), form_item_input, company_column1 );
				company.find( '.first-column' ).css('border', '1px solid #C7C7C7');

				this.content_div.find('.company' ).css( {height: this.content_div.height() - step_title.height() - 15} );
				$( window ).resize( function() {
					$this.content_div.find('.company' ).css( {height: $this.content_div.height() - step_title.height() - 15} );
				} );

				company.show();
				break;

			case 'systemSettings':
				step_title.text( $.i18n._( 'Please enter your site configuration information below. If you are unsure of the fields, we suggest that you use the default values.' ) );
				this.title_1.text( $.i18n._( 'System Settings' ) );
				var systemSettings_column1 = systemSettings.find('.first-column');
				systemSettings_column1.empty();
				// URL
				var widgetContainer = $( "<div class='widget-h-box'></div>" );

				var form_item_host_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_host_input.TText( {field: 'host_name'} );
				form_item_host_input.setValue( 'http://' + this.stepsDataDic[this.current_step]['host_name'] );
				widgetContainer.append( form_item_host_input );

				var form_item_url_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_url_input.TTextInput( {field: 'base_url', width: 400} );

				widgetContainer.append( form_item_url_input );


				var ie_label = $( "<span class='widget-right-label'>( " + $.i18n._( 'No trailing slash' ) + " )</span>" );

				widgetContainer.append( ie_label );
				this.addEditFieldToColumn( $.i18n._( 'URL' ), [form_item_host_input, form_item_url_input], systemSettings_column1, '', widgetContainer, null, true );

				// Log Directory
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'log_dir', width: 400} );
				this.addEditFieldToColumn( $.i18n._( 'Log Directory' ), form_item_input, systemSettings_column1 );

				// Storage Directory
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'storage_dir', width: 400} );

				var widgetContainer = $( "<div class='widget-h-box'></div>" );
				var label = $( "<span class='widget-right-label'> ( " + $.i18n._( 'for things like attachments, logos, etc...' ) + " )</span>" );

				widgetContainer.append( form_item_input );
				widgetContainer.append( label );

				this.addEditFieldToColumn( $.i18n._( 'Storage Directory' ), form_item_input, systemSettings_column1, '', widgetContainer, null, true );


				// Cache Directory
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'cache_dir', width: 400} );
				this.addEditFieldToColumn( $.i18n._( 'Cache Directory' ), form_item_input, systemSettings_column1 );
				systemSettings.find( '.first-column' ).css('border', '1px solid #C7C7C7');

				systemSettings.find('.edit-view-form-item-input-div' ).css('width', '700');
				systemSettings.find('.edit-view-form-item-label-div' ).css( {width: systemSettings_column1.width() - 900, 'min-width': 120} );

				this.content_div.find('.systemSettings' ).css( {height: this.content_div.height() - step_title.height() - 15} );
				$( window ).resize( function() {
					systemSettings.find('.edit-view-form-item-label-div' ).css( {width: systemSettings_column1.width() - 900, 'min-width': 120} );
					$this.content_div.find('.systemSettings' ).css( {height: $this.content_div.height() - step_title.height() - 15} );
				} );

				systemSettings.show();
				break;
			case 'databaseConfig':

				databaseConfig.find( '.step-tip' ).text( $.i18n._( 'Privileged Database User Name / Password. This is only used to create the database schema if the above user does not have permissions to do so.' ) );

				var step_title_htm = '';

				if ( stepData.database_engine == false ) {
					step_title_htm = step_title_htm
						+ '<p style="background-color: #ff0000">'
						+ $.i18n._( 'Your MySQL database does not support the' ) + ' '
						+ '<b>' + $.i18n._('InnoDB') + '</b>' + ' '
						+ $.i18n._( 'storage engine which is required for' ) + ' '
						+ $.i18n._( stepData.application_name ) + ' '
						+ $.i18n._( 'to use transactions and ensure data integrity. Please add' ) + ' '
						+ '<b>' + $.i18n._('InnoDB') + '</b>' + ' '
						+ $.i18n._( 'support to MySQL before continuing.' )
						+ '</p>';
				}

				step_title_htm = step_title_htm + $.i18n._( 'Please enter your database configuration information below. If you are unsure, use the default values.' )

				step_title.html(step_title_htm);
				this.title_1.text( $.i18n._( 'Database Configuration' ) );

				var databaseConfig_column1 = databaseConfig.find('.first-column');
				databaseConfig_column1.empty();

				// database type
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: 'type'} );
				form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
				this.addEditFieldToColumn( $.i18n._( 'Database Type' ), form_item_input, databaseConfig_column1 );

				// host name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'host', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Host Name' ), form_item_input, databaseConfig_column1 );

				// database name
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'database_name', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Database Name' ), form_item_input, databaseConfig_column1 );

				// User Name for Payroll and Time Management
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'user', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'User Name for Payroll and Time Management' ), form_item_input, databaseConfig_column1 );

				// Password for Payroll and Time Management
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'password', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Password for Payroll and Time Management' ), form_item_input, databaseConfig_column1 );

				var databaseConfig_column2 = databaseConfig.find('.second-column');
				databaseConfig_column2.empty();

				// Privileged Database User Name

				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'priv_user', width: 200} );

				var widgetContainer = $( "<div class='widget-h-box'></div>" );
				var label = $( "<span class='widget-right-label'>( " + $.i18n._( 'ie: root, postgres' ) + " )</span>" );

				widgetContainer.append( form_item_input );
				widgetContainer.append( label );

				this.addEditFieldToColumn( $.i18n._( 'Privileged Database User Name' ), form_item_input, databaseConfig_column2, '', widgetContainer );

				// Privileged Database User Password
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'priv_password', width: 200} );
				this.addEditFieldToColumn( $.i18n._( 'Privileged Database User Password' ), form_item_input, databaseConfig_column2 );

				var ribbon_button_box = this.getRibbonButtonBox();
				var ribbon_btn = $( '<li><button class="ribbon-sub-menu-icon" id="testConnection">' + $.i18n._('Test Connnection') + '</button></li>' );
				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$data = {};
					for ( var key in $this.stepsWidgetDic[$this.current_step] ) {
						var widget = $this.stepsWidgetDic[$this.current_step][key];
						$data[key] = widget.getValue();
					}
					ProgressBar.showOverlay();
					$this.api.testConnection( $data, {onResult: function( res ) {
						if ( res.isValid() ) {
							var result = res.getResult();
							var step_title_htm = '';
							if ( result.database_engine == false ) {
								step_title_htm = step_title_htm
									+ '<p style="background-color: #ff0000">'
									+ $.i18n._( 'Your MySQL database does not support the' ) + ' '
									+ '<b>' + $.i18n._('InnoDB') + '</b>' + ' '
									+ $.i18n._( 'storage engine which is required for' ) + ' '
									+ $.i18n._( stepData.application_name ) + ' '
									+ $.i18n._( 'to use transactions and ensure data integrity. Please add' ) + ' '
									+ '<b>' + $.i18n._('InnoDB') + '</b>' + ' '
									+ $.i18n._( 'support to MySQL before continuing.' )
									+ '</p>';
							}
							if ( result.test_connection !== null ){
								if ( result.test_connection === true ) {
									step_title_htm = step_title_htm
										+ '<p>'
										+ $.i18n._( 'Connection test to your database as a non-privileged user has' ) + ' '
										+ '<b>' + $.i18n._('SUCCEEDED') + '</b>' + '!' + ' '
										+ $.i18n._( 'You may continue.' )
										+ '</p>';
								} else if ( result.test_connection === false ) {
									step_title_htm = step_title_htm
										+ '<p style="background-color: #ff0000">'
										+ $.i18n._( 'Connection test to your database as a non-privileged user has' ) + ' '
										+ '<b>' + $.i18n._('FAILED') + '</b>' + '!' + ' '
										+ $.i18n._( 'Please correct them and try again.' )
										+ '</p>';
								}
							}

							if ( result.test_priv_connection !== null ) {
								if ( result.test_priv_connection === false ) {
									step_title_htm = step_title_htm
										+ '<p style="background-color: #ff0000">'
										+ $.i18n._( 'Connection test to your database as a privileged user has' ) + ' '
										+ '<b>' + $.i18n._('FAILED') + '</b>' + '!' + ' '
										+ $.i18n._( 'Please correct the user name/password and try again.' )
										+ '</p>';
								}
							}

							TAlertManager.showAlert( step_title_htm );

						}
					}} );

				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

				this.content_div.find('.content-handle-btn' ).html( ribbon_button_box );
				databaseConfig.find( '.first-column' ).css('border', '1px solid #C7C7C7');

				this.content_div.find('.databaseConfig' ).css( {height: this.content_div.height() - step_title.height() - 65} );
				$( window ).resize( function() {
					$this.content_div.find('.databaseConfig' ).css( {height: $this.content_div.height() - step_title.height() - 65} );
				} );

				databaseConfig.show();
				break;
			case 'databaseSchema':
				this.title_1.text( $.i18n._( 'Database Configuration' ) );
				if ( stepData.upgrade ) {
					databaseSchema.find( '.s-label' ).text( $.i18n._('Upgrading database, please wait...') );
				} else {
					databaseSchema.find( '.s-label' ).text( $.i18n._('Initializing database, please wait...') );
				}

				step_title.empty();
				databaseSchema.show();
				break;
			case 'postUpgrade':
				step_title.empty();
				this.title_1.text( $.i18n._( 'Upgrade Complete' ) );
				postUpgrade.html( '<b>' + $.i18n._('Congratulations! ') + '</b>'
						+ $.i18n._('You have successfully upgraded') + ' '
						+ $.i18n._( stepData.application_name ) + ' '
						+ $.i18n._( 'to' ) + ' '
						+ '<b>' + stepData.application_version + '</b>'
						+ '<br>'
					    + '<br>'
						+ '<b>' + $.i18n._('Note') + ':' + '</b>' + ' '
						+ $.i18n._( 'In order to access new features you may need to re-apply the' ) + ' '
						+ '<b>' + $.i18n._('Administrator') + '</b>' + ' '
						+ $.i18n._('permission preset to each administrator employee in') + ' '
						+ $.i18n._( stepData.application_name ) + '.'

				);
				postUpgrade.show();
				break;
			case 'installDone':
				step_title.empty();
				this.title_1.text( $.i18n._( 'Done!' ) );
				if ( stepData.upgrade == 1 ) {
					installDone.html( '<b>' + $.i18n._('Congratulations! ') + '</b>' + ' '
						+ $.i18n._('You have successfully upgraded') + ' ' + $.i18n._( stepData.application_name )
						+ '<br>'
						+ '<br>'
						+ $.i18n._('You may now') + ' '
						+ '<a href="/interface/html5/#!m=Login">' + $.i18n._('login') + '</a>' + ' '
						+ $.i18n._('with the user name/password that you created earlier.')
					);
				} else {
					installDone.html( '<b>' + $.i18n._('Congratulations!') + ' ' + '</b>' + ' '
						+ $.i18n._('You have successfully installed') + ' ' + $.i18n._( stepData.application_name )
						+ '<br>'
						+ '<br>'
						+ $.i18n._('You may now') + ' '
						+ '<a href="/interface/html5/#!m=Login">' + $.i18n._('login') + '</a>' + ' '
						+ $.i18n._('with the user name/password that you created earlier.')
					);
				}
				installDone.show();
				break;
			case 'maintenanceJobs':
				step_title.empty();
				this.title_1.text( $.i18n._( 'Maintenance Jobs' ) );
				var maintenanceJob_html = $.i18n._( stepData.application_name ) + ' '
					+ '<b>' + $.i18n._('requires') + '</b>' + ' '
					+ $.i18n._('that maintenance jobs be run regularly throughout the day.')
					+ '<br>'
					+ '<p style="color: #ff0000">'
					+ '<b>'
					+ $.i18n._('This is extremely important and without these maintenance jobs running') + ' '
					+ $.i18n._( stepData.application_name ) + ' '
					+ $.i18n._( 'will fail to operate correctly.' )
					+ '</b>'
					+ '</p>'
					+ '<br>'
					+ '<br>'
					+ '<div style="background-color: #eee; width: 100%;">'
					+ '<br>';

				if ( stepData.php_os == 'WINNT' ) {
					maintenanceJob_html  = maintenanceJob_html + $.i18n._('In Windows simply run this command as Administrator.') + '<br><br>' + stepData.schedule_maintenance_job_command;
				} else {
					maintenanceJob_html  = maintenanceJob_html + $.i18n._( 'In most Linux distributions, you can run the following command' );
					if ( stepData.is_sudo_installed == false ) {
						maintenanceJob_html  = maintenanceJob_html +  + ' ' + $.i18n._('as root');
					}
					maintenanceJob_html  = maintenanceJob_html + ':' + '<br>'  + '<b>';

					if ( stepData.is_sudo_installed ) {
						maintenanceJob_html  = maintenanceJob_html + 'sudo';
					}

					maintenanceJob_html  = maintenanceJob_html + ' crontab -u ' + stepData.web_server_user + ' -e';

					maintenanceJob_html  = maintenanceJob_html + '</b>'
						+ '<br>'
						+ '<br>'
						+ $.i18n._('Then add the following line to the bottom of the file') + ':'
						+ '<br>'
						+ '<b>' + '* * * * * php ' + stepData.cron_file + ' > /dev/null 2>&1' + '</b>'
						+ '<br>'
						+ '<br>'
						+ '</div>';

				}

				maintenanceJobs.html( maintenanceJob_html );

				maintenanceJobs.show();
				break;

		}
	},

	onFormItemChange: function( target ) {
		var widgets = this.stepsWidgetDic[this.current_step];

		var key = target.getField();
		var c_value = target.getValue();
		switch( key ) {
			case 'country':
				this.api.getProvinceOptions( c_value, {onResult: function(res ) {
					if ( res.isValid() ) {
						widgets.province.setSourceData( [] );
						widgets.province.setSourceData( Global.addFirstItemToArray( res.getResult() ) );
					}
				}} );
				break;
			case 'type':
				if ( c_value == 'mysqli' || c_value == 'mysqlt' ) {
					var message = $.i18n._( 'WARNING: Using MySQL is NOT recommended if you have more or plan on growing to more than 25 employees, if you have employees in multiple timezones, or if you plan on using this system for mission critical purposes.');
					message = message + '<br>';
					message = message + '<br>';
					message = message + $.i18n._('MySQL lacks proper timezone support, is orders of magnitude slower in processing some of the complex queries that are required and it lacks support for DDL transactions, so if an error occurs during an upgrade your data will become corrupt and you must restore from backup.');
					message = message + '<br>';
					message = message + '<br>';
					message = message + $.i18n._('We recommend using PostgreSQL instead as it does not exhibit any of these shortcomings. You have been warned!');
					TAlertManager.showAlert( message );
				}
				break;
		}

	},

	onBackClick: function() {
		var $this = this;
		this.saveCurrentStep();
		switch( this.current_step ) {
			case 'license':
				break;
			case 'requirements':
				this.current_step = 'license';
				this.initCurrentStep();
				break;
			case 'databaseConfig':
				this.current_step = 'requirements';
				this.initCurrentStep();
				break;
			case 'postUpgrade':
			case 'systemSettings':
				this.current_step = 'databaseConfig';
				this.initCurrentStep();
				break;
			case 'company':
				this.current_step = 'systemSettings';
				this.initCurrentStep();
				break;
			case 'maintenanceJobs':
			case 'installDone':
				this.current_step = 'user';
				this.initCurrentStep();
				break;
			case 'user':
				this.current_step = 'company';
				this.initCurrentStep();
				break;
		}
	},

	onNextClick: function() {
		var $this = this;
		this.saveCurrentStep();
		switch( this.current_step ) {
			case 'license':
				// the next interface is system requirements, so set the current step to the requirements
				this.current_step = 'requirements';
				this.initCurrentStep();
				break;
			case 'requirements':
				if ( this.external_installer == 1 ) {
					this.current_step = 'databaseSchema';
					this.initCurrentStep();
				} else {
					this.current_step = 'databaseConfig';
					this.initCurrentStep();
				}
				break;
			case 'databaseConfig':
				// need to save the database configure first.
				$data = {};
				for ( var key in this.stepsWidgetDic[this.current_step] ) {
					var widget = this.stepsWidgetDic[this.current_step][key];
					$data[key] = widget.getValue();
				}

				TAlertManager.showConfirmAlert( $.i18n._('Installing/Upgrading the TimeTrex database may take up to 10 minutes. Please do not stop the process in any way, including pressing STOP or BACK in your web browser, doing so may leave your database in an unusable state.'), null, function( result ) {
					if ( result ) {
						$this.api.createDatabase( $data, {onResult: function( res ) {
							if ( res.isValid() ) {
								if ( res.getResult().next_page ) {
									$this.current_step = res.getResult().next_page;
									$this.initCurrentStep();
								} else {
									$this.stepsDataDic[$this.current_step] = res.getResult();
									$this._initCurrentStep();
								}

							}
						}} );
					}
				} );
				break;
			case 'postUpgrade':
				this.current_step = 'installDone';
				this.stepsDataDic[this.current_step] = {upgrade: 1};
				this.initCurrentStep();
				break;
			case 'maintenanceJobs':
				this.current_step = 'installDone';
				this.initCurrentStep();
				break;
			case 'systemSettings':
				$data = {};
				for ( var key in this.stepsWidgetDic[this.current_step] ) {
					var widget = this.stepsWidgetDic[this.current_step][key];
					$data[key] = widget.getValue();
				}

				this.api.setSystemSettings( $data, this.external_installer, {onResult: function( res ) {
					if ( res.isValid() ) {

						$this.current_step = 'company';
						$this.initCurrentStep();
					}
				}} );
				break;
			case 'company':
				$data = {};
				for ( var key in this.stepsWidgetDic[this.current_step] ) {
					var widget = this.stepsWidgetDic[this.current_step][key];
					$data[key] = widget.getValue();
				}

				this.api.setCompany( $data, {onResult: function( res ) {
					if ( res.isValid() ) {

						var company_id = res.getResult();
						$this.current_step = 'user';
						$this.stepsDataDic[$this.current_step] = {company_id: company_id};
						$this.initCurrentStep();

					} else {
						$this.setErrorTips( res );
					}
				}} );

				break;
			case 'user':
				$data = {};
				for ( var key in this.stepsWidgetDic[this.current_step] ) {
					var widget = this.stepsWidgetDic[this.current_step][key];
					$data[key] = widget.getValue();
				}

				$data = $.extend( {}, $data, this.stepsDataDic[$this.current_step] );
				this.api.setUser( $data, this.external_installer, {onResult: function( res ) {
					if ( res.isValid() ) {
						var next_page = res.getResult().next_page;
						$this.current_step = next_page;
						$this.initCurrentStep();
					} else {
						$this.setErrorTips( res );
					}
				}} );
				break;
		}

	},


	buildCurrentStepData: function() {
		var args = {};
		var $this = this;
		var stepData = this.stepsDataDic[this.current_step];
		var widgets = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 'systemSettings':
			case 'databaseConfig':
			case 'license':
			case 'user':
				for ( var key in widgets ) {
					if ( stepData[key] ) {
						widgets[key].setValue( stepData[key] );
					}
				}
				ProgressBar.closeOverlay();
				break;
			case 'company':
				var country = widgets.country.getValue();
				this.api.getProvinceOptions( country, {onResult: function(res ) {
					if ( res.isValid() ) {
						widgets.province.setSourceData( Global.addFirstItemToArray( res.getResult() ) );
					}
					ProgressBar.closeOverlay();
				}} );
				break;
			case 'requirements':
				for( var key in widgets ) {
					var edit_view_form_item_dic = $(this.edit_view_form_item_dic[key] );
					if ( stepData[key] == 0 ) {
					} else {
						edit_view_form_item_dic.show();
					}
					var widget = widgets[key];
					widget.removeClass( 't-text' );
					widget.addClass( 'custom-t-text' );
					switch( key ) {
						case 'timetrex_version':
							if (stepData[key].check_timetrex_version == 0  ) {
								widget.html( $.i18n._("OK") + "(v" + stepData[key].current_timetrex_version + ")"  );
							} else if ( stepData[key].check_timetrex_version == 1 ) {
								widget.html( $.i18n._('Unable to Check Latest Version') );
								widget.addClass('dataWarning');
							} else if ( stepData[key].check_timetrex_version == 2 ) {
								widget.html(
									$.i18n._( 'A Newer Version of TimeTrex is Available.' ) + ' '
										+ '<a href="http://www.timetrex.com/download.php">' + $.i18n._('Download') + 'v' + stepData[key].latest_timetrex_version +  + ' ' + $.i18n._('Now') + ' ' + '</a>' );
								widget.addClass('dataWarning');
							}
							break;
						case 'php_version':
							if ( stepData[key].check_php_version == 0 ) {
								widget.html( $.i18n._('OK') + ' (v' + stepData[key].php_version + ')'  );
							} else if ( stepData[key].check_php_version == 1 ) {
								widget.html( $.i18n._('Invalid') + ' (v' + stepData[key].php_version + ')'  );
								widget.addClass('dataError');
							} else if ( stepData[key].check_php_version == 2 ) {
								widget.html( $.i18n._('Unsupported') + ' (v' + stepData[key].php_version + ')'  );
								widget.addClass('dataWarning');
							}
							break;
						case 'database_engine':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] == 1) {
								widget.html( $.i18n._( 'Invalid, PGSQL or MySQLi PHP extensions are required' ) );
								widget.addClass( 'dataError' );
							} else if ( stepData[key] == 2 ) {
								widget.html( $.i18n._( 'Unsupported, upgrade to MySQLi PHP extension instead.' ) );
								widget.addClass( 'dataWarning' );
							}
  							break;
						case 'bcmath':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( setpData[key] == 1) {
								widget.html( $.i18n._('Warning: Not Installed. (BCMATH extension must be enabled)') );
								widget.addClass( 'dataError' );
							}
							break;
						case 'mbstring':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (MBSTRING extension must be enabled)') );
								widget.addClass( 'dataError' );
							}
							break;
						case 'gettext':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] > 0 ) {
								widget.html( $.i18n._( 'Warning: Not Installed. (GETTEXT extension must be enabled)' ) );
								widget.addClass( 'dataError' );
							}
							break;
						case 'soap':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (SOAP extension must be enabled)') )
								widget.addClass( 'dataError' );
							}
							break;
						case 'gd':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (GD extension must be enabled)') );
								widget.addClass( 'dataError' );
							}
							break;
						case 'json':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (JSON extension must be enabled)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'mcrypt':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (MCRYPT extension must be enabled)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'simplexml':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (SimpleXML extension must be enabled)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'zip':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (ZIP extension must be enabled)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'mail':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Warning: Not Installed. (MAIL extension must be enabled)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'pear':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								if ( stepData.php_os == 'WINNT' ) {
									widget.html( $.i18n._('Warning: Not Installed.')
										+ '(' + $.i18n._('try running') + ': ' + '"<b>go-pear.bat</b>"' + ')'
									)
								} else {
									widget.html( $.i18n._('Warning: Not Installed.')
										+ '('
										+ $.i18n._('install the PEAR RPM or package from') + ' '
										+ '<a href=\"http://pear.php.net\">http://pear.php.net</a>'
										+ ')'
									)
								}
								widget.addClass( 'dataError' );
							}
							break;
						case 'safe_mode':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Safe Mode is On. (Please disable it in php.ini)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'magic_quotes':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('magic_quotes_gpc is On. (Please disable it in php.ini)')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'disk_space':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							}  else if ( stepData[key] == 1 ) {
								widget.html( $.i18n._('Not enough disk space available, please free up disk space and try again.')   );
								widget.addClass( 'dataError' );
							}
							break;
						case 'memory_limit':
							if ( stepData[key].check_php_memory_limit == 0 ) {
								var str = $.i18n._('OK');
								if ( stepData[key].memory_limit > 0 ) {
									str += '(' + stepData[key].memory_limit + 'M' + ')';
								}
								widget.html(  str );
								edit_view_form_item_dic.hide();
							}  else if ( stepData[key].check_php_memory_limit == 1 ) {
								widget.html( $.i18n._('Warning') + ': ' + stepData[key].memory_limit + 'M' +  ' (' + $.i18n._('Set this to 128M or higher') + ')'  );
								widget.addClass( 'dataError' );
							}
							break;
						case 'base_url':
							if ( stepData[key].check_base_url == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning') + ': ' + key + ' ' + $.i18n._('in') + ' timetrex.ini.php' + $.i18n._('is incorrect, perhaps it should be') + ' ' + stepData[key].recommended_base_url + ' ' + $.i18n._('instead') );
								widget.addClass( 'dataError' );
							}
							break;
						case 'base_dir':
							if ( stepData[key].check_php_open_base_dir == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: PHP open_basedir setting') + ' ' + '(' + stepData[key].php_open_base_dir + ') ' + $.i18n._('does not include directory of PHP CLI binary') + ' ' + '(' + stepData[key].php_cli_directory + ')'  );
								widget.addClass( 'dataError' );
							}
							break;
						case 'cli_executable':
							if ( stepData[key].check_php_cli_binary == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: PHP CLI') + ' ' + '(' + stepData[key].php_cli + ')' + ' ' + $.i18n._('does not exist or is not executable.') );
								widget.addClass( 'dataError' );
							}
							break;
						case 'cli_requirements':
							if ( stepData[key].check_php_cli_requirements == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: PHP CLI requirements failed while executing') + '<br>'
									+ stepData[key].php_cli_requirements_command + '<br>'
									+ $.i18n._('Likely caused by having two PHP.INI files with different settings.')
								);
								widget.addClass( 'dataError' );
							}
							break;
						case 'config_file':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else {
								widget.html( $.i18n._('Warning: Not writable')   );
								widget.addClass( 'dataError' );
							}
  							break;
						case 'cache_dir':
							if ( stepData[key].check_writable_cache_directory == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: Not writable') + ' (' + stepData[key].cache_dir + ')' );
								widget.addClass( 'dataError' );
							}
							break;
						case 'storage_dir':
							if ( stepData[key].check_writable_storage_directory == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: Not writable') + ' (' + stepData[key].storage_path + ')' );
								widget.addClass( 'dataError' );
							}
							break;
						case 'log_dir':
							if ( stepData[key].check_writable_log_directory == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: Not writable') + ' (' + stepData[key].log_path + ')' );
								widget.addClass( 'dataError' );
							}
							break;
						case 'empty_cache_dir':
							if ( stepData[key].check_clean_cache_directory == 0 ) {
								widget.html( $.i18n._('OK')   );
								edit_view_form_item_dic.hide();
							} else {
								widget.html( $.i18n._('Warning: Please delete all files/directories in') + ': ' + ' <b>' + stepData[key].cache_dir + '</b>' );
								widget.addClass( 'dataError' );
							}
							break;
						case 'file_permission':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else {
								widget.html( $.i18n._( 'Warning: File permissions are invalid, some') + ' ' + $.i18n._(stepData.application_name) + ' ' + $.i18n._('files are not readable/writable. See detailed error messages below.') )
								widget.addClass( 'dataError' );
							}
							break;
						case 'file_checksums':
							if ( stepData[key] == 0 ) {
								widget.html( $.i18n._('OK')   );
							} else {
								widget.html( $.i18n._( 'Warning' ) + ': ' + $.i18n._('File checksums do not match, some') + ' ' + $.i18n._(stepData.application_name) + ' ' + $.i18n._('files may be corrupted, missing, or not installed properly. See detailed error messages below.'));
								widget.addClass( 'dataError' );
							}
							break;

					}

				}
				ProgressBar.closeOverlay();
				break;
			case 'databaseSchema':
				this.api.setDatabaseSchema( this.external_installer, {onResult: function( res ) {
					if ( res.isValid() ) {
						var result = res.getResult();
						if ( result.next_page ) {
							$this.current_step = result.next_page;
							switch( result.next_page ) {
								case 'postUpgrade':
									$this.initCurrentStep();
									break;
								case 'systemSettings':
									if ( result.action ) {
										$this.onNextClick(); // skip system setting to company information directly.
									} else {
										$this.initCurrentStep();
									}
									break;
							}
						}
					}
				}} );
				break;
			default:
				ProgressBar.closeOverlay();
				break;

		}

	},


	setErrorTips: function( result ) {
		this.clearErrorTips();

		var details = result.getDetails();
		var error_list = details[0];

		for ( var key in error_list ) {

			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}

			if ( !Global.isSet( this.stepsWidgetDic[this.current_step][key] ) ) {
				continue;
			}

			if ( this.stepsWidgetDic[this.current_step][key].is( ':visible' ) ) {

				this.stepsWidgetDic[this.current_step][key].setErrorStyle( error_list[key], true );

			} else {

				this.stepsWidgetDic[this.current_step][key].setErrorStyle( error_list[key] );
			}

			this.edit_view_error_ui_dic[key] = this.stepsWidgetDic[this.current_step][key];

		}

	},

	clearErrorTips: function() {

		for ( var key in this.edit_view_error_ui_dic ) {

			//Error: Uncaught TypeError: Cannot read property 'clearErrorStyle' of undefined in https://ondemand2001.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 1779
			if ( !this.edit_view_error_ui_dic.hasOwnProperty( key ) || !this.edit_view_error_ui_dic[key] ) {
				continue;
			}

			this.edit_view_error_ui_dic[key].clearErrorStyle();
		}

		this.edit_view_error_ui_dic = {};
	},

	onDoneClick: function() {
		this.cleanStepsData();

//		if ( this.call_back ) {
//			this.call_back();
//		}
//		var baseURI = $("*").context.baseURI;

		var loc = window.location;
		var currentURL = loc.protocol + '//' + loc.host + loc.pathname;
		window.location.href = currentURL + '#!m=Login';
		$( this.el ).remove();

	},

	onCloseClick: function() {
		if ( this.script_name ) {
			this.saveCurrentStep();
		}
		var loc = window.location;
		var currentURL = loc.protocol + '//' + loc.host + loc.pathname;
		window.location.href = currentURL + '#!m=Login';
		$( this.el ).remove();
	},

	cleanStepsData: function() {
		this.stepsDataDic = {};
		this.current_step = 'license';
	}




} );
