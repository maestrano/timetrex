AboutViewController = BaseViewController.extend( {

	date_api: null,

	employeeActive: [],

	initialize: function() {

		if ( Global.isSet( this.options.edit_only_mode ) ) {
			this.edit_only_mode = this.options.edit_only_mode;
		}

		this._super( 'initialize' );

		this.viewId = 'About';
		this.script_name = 'AboutView';
		this.context_menu_name = $.i18n._( 'About' );
		this.api = new (APIFactory.getAPIClass( 'APIAbout' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();

		this.render();

		this.initData();

	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		this.buildContextMenu( true );
		this.setEditMenu();

	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.script_name + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var check = new RibbonSubMenu( {
			label: $.i18n._( 'Check For Updates' ),
			id: ContextMenuIconName.check_updates,
			group: editor_group,
			icon: Icons.check_updates,
			permission_result: true,
			permission: null
		} );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	onContextMenuClick: function( context_btn, menu_name ) {
		var id;

		var $this = this;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {

			case ContextMenuIconName.check_updates:
				ProgressBar.showOverlay();
				this.onCheckClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;

		}

	},

	onCheckClick: function() {
		var $this = this;
		this.api['isNewVersionAvailable']( {onResult: function( result ) {
			var result_data = result.getResult();
			$this.current_edit_record = result_data.api_retval;
			$this.setEditViewWidgetsMode();
			$this.initEditView();
		}} );
	},

	getAboutData: function( callBack ) {
		var $this = this;
		$this.api['get' + $this.api.key_name]( {onResult: function( result ) {
			var result_data = result.getResult();
			if ( Global.isSet( result_data ) ) {
				callBack( result_data );
			}

		}} );
	},

	openEditView: function() {
		var $this = this;

		if ( $this.edit_only_mode ) {

			this.buildContextMenu();
			if ( !$this.edit_view ) {
				$this.initEditViewUI( 'About', 'AboutEditView.html' );
			}

			$this.getAboutData( function( result ) {
				// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
				$this.current_edit_record = result;
				$this.setEditViewWidgetsMode();
				$this.initEditView();

			} );

		}

	},

	setUIWidgetFieldsToCurrentEditRecord: function() {

	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets

//		this.current_edit_record['new_version'] = 1;
//		this.current_edit_record['license_data'] = {
//				'organization_name': 'ABC Company',
//				'major_version': '3.0',
//				'minor_version': '1.0',
//				'product_name': 'TimeTrex',
//				'active_employee_licenses': 23,
//				'issue_date': false,
//				'expire_date': false,
//				'expire_date_display': false,
//				'registration_key': false,
//				'message': 'sdfffffffffffff',
//				'retval': true
//		};
//		this.current_edit_record['user_counts'] = [
//			{
//				'label': 'May 2014',
//				'max_active_users': '30',
//				'max_inactive_users': '40',
//				'max_deleted_users': '50'
//			},
//			{
//				'label': 'April 2014',
//				'max_active_users': '10',
//				'max_inactive_users': '20',
//				'max_deleted_users': '30'
//			},
//			{
//				'label': 'March 2014',
//				'max_active_users': '1',
//				'max_inactive_users': '2',
//				'max_deleted_users': '3'
//			}
//		];

		for ( var i in this.edit_view_form_item_dic ) {
			this.edit_view_form_item_dic[i].css( 'display', 'none' );
		}

		for ( var key in this.current_edit_record ) {
			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];

			switch ( key ) {
				case 'new_version':
					if ( this.current_edit_record[key] === true ) {

						this.edit_view_form_item_dic['notice'].css( 'display', 'block' );

						var html = '<br><b>' + $.i18n._( 'NOTICE' ) + ':' + '</b> ' + $.i18n._( 'There is a new version of' ) + ' ';
						html += '<b>' + $.i18n._( this.current_edit_record['application_name'] ) + '</b> ' + $.i18n._( 'available' ) + '.';
						html += '<br>' + $.i18n._( 'This version may contain tax table updates necessary for accurate payroll calculation, we recommend that you upgrade as soon as possible.' ) + '<br>';
						html += "" + $.i18n._( 'The latest version can be downloaded from' ) + ":" + " <a href='http://" + this.current_edit_record['organization_url'] + "/?upgrade=1' target='_blank'>";
						html += "<b>" + this.current_edit_record['organization_url'] + "</b></a><br><br>";

						$( this.edit_view_form_item_dic['notice'].find( ".tblDataWarning" ) ).html( html );

					}
					break;
				case 'registration_key':
					if ( Global.isSet( widget ) ) {
						if ( this.current_edit_record[key] === '' || Global.isFalseOrNull( this.current_edit_record[key] ) ) {
							widget.setValue( $.i18n._( 'N/A' ) );
						} else {
							widget.setValue( this.current_edit_record[key] );
						}
					}
					break;
				case 'cron': //popular case
					if ( Global.isSet( widget ) ) {
						if ( this.current_edit_record[key]['last_run_date'] !== '' ) {
							widget.setValue( this.current_edit_record[key]['last_run_date'] );
						} else {
							widget.setValue( $.i18n._( 'Never' ) );
						}
					}
					break;
				case 'license_data':
					if ( Global.isSet( this.current_edit_record[key] ) ) {

						this.edit_view_form_item_dic['license_info'].css( 'display', 'block' );
						this.edit_view_form_item_dic['license_browser'].css( 'display', 'block' );

						if ( Global.isSet( this.current_edit_record[key]['message'] ) && this.current_edit_record[key]['message'] !== '' ) {
							var separated_box = $( this.edit_view_form_item_dic['license_info'].find( '.separated-box' ) );
							separated_box.css( {'font-weight': 'bold', 'background-color': 'red', 'height': 'auto', 'color': '#000000'} );
							separated_box.html( $.i18n._( 'License Information' ) + '<br>' + $.i18n._( 'WARNING' ) + ': ' + this.current_edit_record[key]['message'] );
							$( separated_box.find( 'span' ) ).removeClass( 'label' ).css( {'font-size': 'normal', 'font-weight': 'bold'} );
						}

						if ( Global.isSet( this.current_edit_record[key]['organization_name'] ) && this.current_edit_record[key]['organization_name'] !== '' ) {
							for ( var k in this.current_edit_record[key] ) {
								switch ( k ) {
									case 'major_version':
									case 'minor_version':
										this.edit_view_form_item_dic['_version'].css( 'display', 'block' );
										this.edit_view_ui_dic['_version'].setValue( this.current_edit_record[key]['major_version'] + '.' + this.current_edit_record[key]['minor_version'] + '.X' );
										break;
									default:
										if ( Global.isSet( this.edit_view_ui_dic[k] ) && Global.isSet( this.edit_view_form_item_dic[k] ) ) {
											this.edit_view_form_item_dic[k].css( 'display', 'block' );
											this.edit_view_ui_dic[k].setValue( this.current_edit_record[key][k] );
										}
										break;
								}
							}
						}
					}
					break;
				case 'user_counts':
					if ( this.current_edit_record[key].length > 0 ) {
						this.edit_view_form_item_dic['user_active_inactive'].css( 'display', 'block' );
					}
					break;
				default:
					if ( Global.isSet( widget ) ) {
						widget.setValue( this.current_edit_record[key] );
					}
					break;
			}

		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.setActiveEmployees();
	},

	setActiveEmployees: function() {

		if ( this.employeeActive.length > 0 ) {
			for ( var i in this.employeeActive ) {
				var field = this.employeeActive[i].getField();
				if ( Global.isSet( this.edit_view_form_item_dic[field] ) ) {
					this.edit_view_form_item_dic[field].remove();
				}
			}

			this.employeeActive = [];

		}

		if ( Global.isSet( this.current_edit_record['user_counts'] ) && this.current_edit_record['user_counts'].length > 0 ) {
			var tab_about = this.edit_view_tab.find( '#tab_about' );
			var tab_about_column1 = tab_about.find( '.first-column' );

			for ( var key in this.current_edit_record['user_counts'] ) {

				var item = this.current_edit_record['user_counts'][key];

				var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'active_' + key  } );
				form_item_input.setValue( item['max_active_users'] + ' / ' + item['max_inactive_users'] );

				this.addEditFieldToColumn( $.i18n._( item['label'] ), form_item_input, tab_about_column1, '', null, true );

				this.employeeActive.push( form_item_input );

				this.edit_view_ui_dic['active_' + key ].css( 'opacity', 1 );
			}

			this.editFieldResize( 0 );
		}
	},

	buildEditViewUI: function() {
		var $this = this;
		this._super( 'buildEditViewUI' );

		this.setTabLabels( {
			'tab_about': $.i18n._( 'About' )
		} );

		//Tab 0 start

		var tab_about = this.edit_view_tab.find( '#tab_about' );

		var tab_about_column1 = tab_about.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_about_column1 );

		var form_item_input = $( "<div class='tblDataWarning'></div>" );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'notice' );

		// separate box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'System Information' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1 );

		// Product Edition
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'product_edition' } );
		this.addEditFieldToColumn( $.i18n._( 'Product Edition' ), form_item_input, tab_about_column1 );

		// Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'system_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Version' ), form_item_input, tab_about_column1 );

		// Tax Engine Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'tax_engine_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Tax Engine Version' ), form_item_input, tab_about_column1 );

		// Tax Data Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'tax_data_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Tax Data Version' ), form_item_input, tab_about_column1 );

		// Registration Key
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'registration_key' } );
		this.addEditFieldToColumn( $.i18n._( 'Registration Key' ), form_item_input, tab_about_column1 );

		// Maintenance Jobs Last Ran
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'cron' } );
		this.addEditFieldToColumn( $.i18n._( 'Maintenance Jobs Last Ran' ), form_item_input, tab_about_column1 );

		// separate box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'License Information' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'license_info' );

		// Upload License
		form_item_input = Global.loadWidgetByName( FormItemType.FILE_BROWSER );

		this.file_browser = form_item_input.TImageBrowser( {
			field: 'license_browser',
			name: 'filedata',
			accept_filter: '*',
			changeHandler: function( a ) {
				$this.uploadLicense( this );
			}
		} );

		this.addEditFieldToColumn( $.i18n._( 'Upload License' ), form_item_input, tab_about_column1, '', null, true );

		// Product
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'product_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Product' ), form_item_input, tab_about_column1, '', null, true );

		// Company
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'organization_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_about_column1, '', null, true );

		// Version
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: '_version' } );
		this.addEditFieldToColumn( $.i18n._( 'Version' ), form_item_input, tab_about_column1, '', null, true );

		// Active Employee Licenses
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'active_employee_licenses' } );
		this.addEditFieldToColumn( $.i18n._( 'Active Employee Licenses' ), form_item_input, tab_about_column1, '', null, true );

		// Issue Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'issue_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Issue Date' ), form_item_input, tab_about_column1, '', null, true );

		// Expire Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'expire_date_display' } );
		this.addEditFieldToColumn( $.i18n._( 'Expire Date' ), form_item_input, tab_about_column1, '', null, true );

		// Schema Version
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Schema Version' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1 );

		// Group A
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'schema_version_group_A' } );
		this.addEditFieldToColumn( $.i18n._( 'Group A' ), form_item_input, tab_about_column1 );

		// Group B
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'schema_version_group_B' } );
		this.addEditFieldToColumn( $.i18n._( 'Group B' ), form_item_input, tab_about_column1 );

		// Group T
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'schema_version_group_T' } );
		this.addEditFieldToColumn( $.i18n._( 'Group T' ), form_item_input, tab_about_column1 );

		// Separated Box
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Employees (Active / InActive)' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_about_column1, '', null, true, false, 'user_active_inactive' );

	},

	uploadLicense: function( obj ) {
		var $this = this;
		var file = this.edit_view_ui_dic['license_browser'].getValue();
		$this.api.uploadFile( file, 'object_type=license&object_id=', {onResult: function( res ) {

			if ( res ) {
				$this.openEditView();
				IndexViewController.setNotificationBar( 'login' );
			} else {
				TAlertManager.showAlert( $.i18n._( 'Invalid license file' ) )
			}

		}} );
	}


} );