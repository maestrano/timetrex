PayrollExportReportViewController = ReportBaseViewController.extend( {

	export_type_array: null,

	export_policy_array: null,

	export_setup_ui_dic: null,

	export_setup_data: null,

	export_grid: null,

	select_grid_last_row: null,

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'PayrollExportReport';
		this.viewId = 'PayrollExportReport';
		this.context_menu_name = $.i18n._( 'Payroll Export' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'PayrollExportReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayrollExportReport' ))();
		this.include_form_setup = true;
		this.export_setup_data = {};

		this.buildContextMenu();

	},

	initOptions: function( callBack ) {
		var $this = this;
		var options = [
			{option_name: 'page_orientation'},
			{option_name: 'font_size'},
			{option_name: 'chart_display_mode'},
			{option_name: 'chart_type'},
			{option_name: 'templates'},
			{option_name: 'setup_fields'},
			{option_name: 'export_type'}
		];

		this.initDropDownOptions( options, function( result ) {

			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.

		} );

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
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var saved_report_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Saved Report' ),
			id: this.viewId + 'SavedReport',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var form_setup_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Form' ),
			id: this.viewId + 'Form',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var export_icon = new RibbonSubMenu( {
			label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.export_export,
			group: editor_group,
			icon: Icons.export_export,
			permission_result: true,
			permission: null
		} );

		var excel = new RibbonSubMenu( {
			label: $.i18n._( 'Excel' ),
			id: ContextMenuIconName.export_excel,
			group: editor_group,
			icon: Icons.export_excel,
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

		var save_existed_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save_existed_report,
			group: saved_report_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_new_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save as New' ),
			id: ContextMenuIconName.save_new_report,
			group: saved_report_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		var save_setup = new RibbonSubMenu( {
			label: $.i18n._( 'Save Setup' ),
			id: ContextMenuIconName.save_setup,
			group: form_setup_group,
			icon: Icons.save_setup,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},
	onTabIndexChange: function() {

		// Don't do anything in this sub class
	},

	setEditMenuViewIcon: function() {
		// Don't do anything in this sub class
	},
	/* jshint ignore:start */
	onContextMenuClick: function( context_btn, menu_name ) {
		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		if ( this.select_grid_last_row ) {
			this.export_grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		switch ( id ) {
			case ContextMenuIconName.view:
				this.onViewClick();
				break;
			case ContextMenuIconName.export_excel:
				this.onViewExcelClick();
				break;
			case ContextMenuIconName.export_export:
				this.onViewClick( 'payroll_export' );
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.save_existed_report: //All report view
				this.onSaveExistedReportClick();
				break;
			case ContextMenuIconName.save_new_report: //All report view
				this.onSaveNewReportClick();
				break;
			case ContextMenuIconName.timesheet_view: //All report view
				this.onViewClick( 'pdf_timesheet' );
				break;
			case ContextMenuIconName.timesheet_view_detail: //All report view
				this.onViewClick( 'pdf_timesheet_detail' );
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},
	/* jshint ignore:end */
	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab3' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Export Format

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'export_type', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.export_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Export Format' ), form_item_input, tab3_column1, '' );

		form_item_input.bind( 'formItemChange', function( e, target ) {
			$this.onExportChange( target.getValue() );
		} );

	},

	onExportChange: function( type ) {
		var $this = this;

		this.removeCurrentExportUI();

		ProgressBar.showOverlay(); //End when set grid data complete

		this.api.getOptions( 'export_policy', {
			noCache: true, onResult: function( result ) {
				$this.export_policy_array = result.getResult();

				switch ( type ) {
					case 'adp':
						$this.api.getOptions( 'adp_hour_column_options', {
							onResult: function( result ) {

								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result.getResult() );

							}
						} );
						break;
					case 'va_munis':
						$this.api.getOptions( 'export_columns', true, {
							onResult: function( result ) {

								var result_data = result.getResult();

								if ( !result_data.hasOwnProperty( '0' ) ) {
									result_data[0] = '-- Custom --';
								}

								$this.buildAdditionalInputBox( type );
								$this.buildGrid( type, result_data );

							}
						} );
						break;
					case 'ceridian_insync':
					case 'paychex_preview_advanced_job':
					case 'paychex_preview':
					case 'quickbooks':
					case 'quickbooks_advanced':
					case 'csv':
					case 'csv_advanced':
						$this.buildAdditionalInputBox( type );
						$this.buildGrid( type );
						break;
					default:
						$this.buildGrid( type );
						break;
				}

			}
		} );

	},
	/* jshint ignore:start */
	buildGrid: function( type, columnOptions ) {

		var $this = this;

		var grid = this.edit_view.find( '#export_grid' );
		var column_info_array = [];
		var column_options_string = '';

		var column_info = {
			name: 'column_id',
			index: 'column_id',
			label: 'Hours',
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		var hour_code_label = '';

		switch ( type ) {
			case 'adp':

				columnOptions = Global.buildRecordArray( columnOptions.adp_hour_column_options );

				for ( var i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: 'ADP Hours',
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				hour_code_label = 'ADP Hours Code';

				break;
			case 'paychex_preview_advanced_job':
			case 'paychex_preview':
				hour_code_label = 'Paychex Hours Code';
				break;
			case 'paychex_online':
				hour_code_label = 'Earning Code';
				break;
			case 'ceridian_insync':
				hour_code_label = 'Ceridian Hours Code';
				break;
			case 'millenium':
				hour_code_label = 'Millenium Hours Code';
				break;
			case 'quickbooks_advanced':
			case 'quickbooks':
				hour_code_label = 'Quickbooks Payroll Item Name';
				break;
			case 'surepayroll':
				hour_code_label = 'Payroll Code';
				break;
			case 'chris21':
				hour_code_label = 'Chris21 Hours Code';
				break;
			case 'va_munis':

				columnOptions = Global.buildRecordArray( columnOptions );

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'hour_column',
					index: 'hour_column',
					label: 'Columns',
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				hour_code_label = 'Hours Code';
				break;
			case 'csv':
			case 'csv_advanced':
				hour_code_label = 'Hours Code';
				break;

		}

		column_info = {
			name: 'hour_code', index: 'hour_code', label: hour_code_label, width: 100, sortable: false, title: false,
			editable: true, edittype: 'text'
		};
		column_info_array.push( column_info );

		if ( !this.export_grid ) {
			this.export_grid = grid;

			this.export_grid = this.export_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: 0,
				rowNum: 10000,
				colNames: [],
				colModel: column_info_array,
				viewrecords: true,
				editurl: 'clientArray',
				onSelectRow: function( id ) {
					if ( id ) {

						if ( $this.select_grid_last_row ) {
							$this.export_grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						}
						$this.export_grid.jqGrid( 'editRow', id, true );
						$this.select_grid_last_row = id;
					}
				}

			} );

		} else {

			this.export_grid.jqGrid( 'GridUnload' );
			this.export_grid = null;

			grid = this.edit_view.find( '#export_grid' );
			this.export_grid = $( grid );
			this.export_grid = this.export_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				datatype: 'local',
				width: 0,
				colNames: [],
				colModel: column_info_array,
				viewrecords: true,
				editurl: 'clientArray',
				onSelectRow: function( id ) {
					if ( id ) {

						if ( $this.select_grid_last_row ) {
							$this.export_grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						}
						$this.export_grid.jqGrid( 'editRow', id, true );
						$this.select_grid_last_row = id;
					}
				}
			} );

		}

		$this.setExportGridData( type ); //Set Grid size at final

	},
	/* jshint ignore:end */
	setExportGridData: function( type ) {

		var $this = this;

		var grid_data = Global.buildRecordArray( this.export_policy_array );
		var export_columns = null;
		var len = grid_data.length;
		var grid_source = [];

		this.api.getOptions( 'default_hour_codes', {
			noCache: true,
			onResult: function( result ) {

				var res_data = result.getResult();
				var default_columns = [];
				if ( res_data[type] && res_data[type].columns ) {
					default_columns = res_data[type].columns;
				}

				if ( $this.export_setup_data.export_columns && $this.export_setup_data.export_columns[type] ) {
					export_columns = $this.export_setup_data.export_columns[type].columns;
					doNext( export_columns, default_columns );
				} else {
					if ( res_data[type] && res_data[type].columns ) {
						doNext( default_columns );
					}
				}

				$this.setExportGridSize();
			}
		} );

		function doNext( export_columns, default_columns ) {
			var hour_code;
			var hour_column;
			for ( var i = 0; i < len; i++ ) {
				var row = grid_data[i];
				var column_id = row.label;

				var export_column_value = export_columns[row.value];

				if ( !export_column_value ) {

					if ( default_columns ) {
						export_column_value = default_columns[row.value]
					} else {
						export_column_value = {};
					}

				}

				hour_column = export_column_value.hour_column;
				hour_code = export_column_value.hour_code;

				switch ( type ) {
					case 'adp':
					case 'va_munis':

						if ( !hour_column ) {
							hour_column = '0';
						}

						var row_data = {
							id: i + 200,
							column_id: column_id,
							hour_column: hour_column,
							hour_code: hour_code,
							column_id_key: row.value
						};
						break;
					default:
						row_data = {id: i + 200, column_id: column_id, hour_code: hour_code, column_id_key: row.value};
						break;
				}

				grid_source.push( row_data );
			}

			$this.export_grid.clearGridData();
			$this.export_grid.setGridParam( {data: grid_source} );
			$this.export_grid.trigger( 'reloadGrid' );
			ProgressBar.closeOverlay();

		}

	},
	/* jshint ignore:start */
	setExportGridSize: function() {

		if ( !this.export_grid || !this.export_grid.is( ':visible' ) ) {
			return;
		}

		var tab3 = this.edit_view.find( '#tab3_content_div' );
		var first_row = this.edit_view.find( '.first-row' );
		this.export_grid.setGridWidth( tab3.width() );
		this.export_grid.setGridHeight( tab3.height() - first_row.height() );

	},
	/* jshint ignore:end */
	onTabShow: function( e, ui ) {

		var key = ui.index;

		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		var last_index = this.edit_view_tab_selected_index;

		this.edit_view_tab_selected_index = ui.index;

		if ( (last_index === 1 || this.need_refresh_display_columns) && ui.index === 0 ) {
			this.buildReportUIBaseOnSetupFields();
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( ui.index === 1 ) {
			this.edit_view_ui_dic.setup_field.setValue( this.current_edit_record.setup_field );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( ui.index === 2 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			} else {
				this.edit_view_tab.find( '#tab_chart' ).find( '.first-column' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
			}
		} else if ( ui.index === 3 ) {
			this.setExportGridSize();
			this.buildContextMenu( true );
			this.setEditMenu();
		} else if ( ui.index === 4 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
				this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
				this.initSubCustomColumnView();
			} else {
				this.edit_view_tab.find( '#tab4' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}
		} else if ( ui.index === 5 ) {
			this.initSubSavedReportView();
		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}

	},

	buildAdditionalInputBox: function( type ) {

		var $this = this;

		this.export_setup_ui_dic = {};

		var tab3 = this.edit_view_tab.find( '#tab3' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		switch ( type ) {
			case 'adp':

				//Company code
				var code = 'company_code';
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: code} );

				var h_box = $( "<div class='h-box'></div>" );

				var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( {field: code + '_text'} );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Company Code' ), [form_item_input, text_box], tab3_column1, '', h_box, true );

				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_company_code_options', {
					onResult: function( result ) {

						var result_data = result.getResult();

						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Batch ID
				var code1 = 'batch_id';
				var form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( {field: code1} );

				h_box = $( "<div class='h-box'></div>" );

				var text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( {field: code1 + '_text'} );

				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'Batch ID' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );

				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'adp_batch_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				// Temp Department

				var code2 = 'temp_dept';
				var form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {field: code2} );

				h_box = $( "<div class='h-box'></div>" );

				var text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( {field: code2 + '_text'} );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Temp Department' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );

				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'adp_temp_dept_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];
				break;
			case 'va_munis':
				//Department
				code = 'department';
				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( {field: code} );

				h_box = $( "<div class='h-box'></div>" );

				text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box.css( 'margin-left', '10px' );
				text_box.TTextInput( {field: code + '_text'} );

				h_box.append( form_item_input );
				h_box.append( text_box );

				this.addEditFieldToColumn( $.i18n._( 'Department' ), [form_item_input, text_box], tab3_column1, '', h_box, true );

				form_item_input.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box.css( 'display', 'inline' );
						text_box.setValue( $this.export_setup_data[code] );
					} else {
						text_box.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();

						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}
						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

						form_item_input.setValue( $this.export_setup_data[code] );
						form_item_input.trigger( 'formItemChange', [form_item_input, true] );

					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Employee Number
				code1 = 'employee_number';
				form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( {field: code1} );

				h_box = $( "<div class='h-box'></div>" );

				text_box1 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box1.css( 'margin-left', '10px' );
				text_box1.TTextInput( {field: code1 + '_text'} );

				h_box.append( form_item_input1 );
				h_box.append( text_box1 );

				this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), [form_item_input1, text_box1], tab3_column1, '', h_box, true );

				form_item_input1.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box1.css( 'display', 'inline' );
						text_box1.setValue( $this.export_setup_data[code1] );
					} else {
						text_box1.css( 'display', 'none' );
					}
				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
						form_item_input1.trigger( 'formItemChange', [form_item_input1, true] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				// Long GL Account

				code2 = 'gl_account';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {field: code2} );

				h_box = $( "<div class='h-box'></div>" );

				text_box2 = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				text_box2.css( 'margin-left', '10px' );
				text_box2.TTextInput( {field: code2 + '_text'} );

				h_box.append( form_item_input2 );
				h_box.append( text_box2 );
				this.addEditFieldToColumn( $.i18n._( 'Long GL Account' ), [form_item_input2, text_box2], tab3_column1, '', h_box, true );

				form_item_input2.bind( 'formItemChange', function( e, target ) {
					if ( target.getValue() === 0 ) {
						text_box2.css( 'display', 'inline' );
						text_box2.setValue( $this.export_setup_data[code2] );
					} else {
						text_box2.css( 'display', 'none' );
					}

				} );

				$this.api.getOptions( 'export_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();

						if ( !result_data.hasOwnProperty( '0' ) ) {
							result_data[0] = '-- Custom --';
						}

						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
						form_item_input2.trigger( 'formItemChange', [form_item_input2, true] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];
				break;
			case 'ceridian_insync':
				// Employer Number
				code = 'employer_number';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: code} );
				this.addEditFieldToColumn( $.i18n._( 'Employer Number' ), form_item_input, tab3_column1, '', null, true );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];
				break;
			case 'paychex_preview':
				// Client Number
				code = 'client_number';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: code} );
				this.addEditFieldToColumn( $.i18n._( 'Client Number' ), form_item_input, tab3_column1, '', null, true );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];
				break;

			case 'paychex_preview_advanced_job':
				// Client Number
				code = 'client_number_adv';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: code} );
				this.addEditFieldToColumn( $.i18n._( 'Client Number' ), form_item_input, tab3_column1, '', null, true );
				form_item_input.setValue( $this.export_setup_data['client_number'] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Job
				code1 = 'job_columns';
				form_item_input1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input1.AComboBox( {
					field: code1,
					allow_multiple_selection: true,
					layout_name: ALayoutIDs.OPTION_COLUMN,
					key: 'value',
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'Job' ), form_item_input1, tab3_column1, '', null, true );

				new (APIFactory.getAPIClass( 'APIJobDetailReport' ))().getOptions( 'static_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				//State
				code2 = 'state_columns';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {
					field: code2,
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'State' ), form_item_input2, tab3_column1, '', null, true );

				new (APIFactory.getAPIClass( 'APIJobDetailReport' ))().getOptions( 'static_columns', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				// Include Override Rates
				code = 'include_hourly_rate';
				form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
				form_item_input.TCheckbox( {field: code} );
				this.addEditFieldToColumn( $.i18n._( 'Include Override Dates' ), form_item_input, tab3_column1, '', null, true );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				break;
			case 'quickbooks':
			case 'quickbooks_advanced':

				// Company Name
				code = 'company_name';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: code} );

				var containerWithTextTip = this.buildWidgetContainerWithTextTip( form_item_input, '(Exactly as shown in Quickbooks)' );

				this.addEditFieldToColumn( $.i18n._( 'Company Name' ), form_item_input, tab3_column1, '', containerWithTextTip, true );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				// Company Created Time
				code = 'company_created_date';
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: code} );

				containerWithTextTip = this.buildWidgetContainerWithTextTip( form_item_input, '(Exactly as shown in exported timer list)' );

				this.addEditFieldToColumn( $.i18n._( 'Company Created Time' ), form_item_input, tab3_column1, '', containerWithTextTip, true );
				form_item_input.setValue( $this.export_setup_data[code] );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];

				//Map PROJ Field To
				code1 = 'proj';
				form_item_input1 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input1.TComboBox( {
					field: code1
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map PROJ Field To' ), form_item_input1, tab3_column1, '', null, true );

				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input1.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input1.setValue( $this.export_setup_data[code1] );
					}
				} );

				$this.export_setup_ui_dic[code1] = $this.edit_view_form_item_dic[code1];
				delete $this.edit_view_form_item_dic[code1];

				//Map ITEM Field To
				code2 = 'item';
				form_item_input2 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input2.TComboBox( {
					field: code2
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map ITEM Field To' ), form_item_input2, tab3_column1, '', null, true );

				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input2.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input2.setValue( $this.export_setup_data[code2] );
					}
				} );

				$this.export_setup_ui_dic[code2] = $this.edit_view_form_item_dic[code2];
				delete $this.edit_view_form_item_dic[code2];

				//Map ITEM Field To
				var code3 = 'job';
				var form_item_input3 = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input3.TComboBox( {
					field: code3
				} );

				this.addEditFieldToColumn( $.i18n._( 'Map JOB Field To' ), form_item_input3, tab3_column1, '', null, true );

				this.api.getOptions( 'quickbooks_proj_options', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input3.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input3.setValue( $this.export_setup_data[code3] );
					}
				} );

				$this.export_setup_ui_dic[code3] = $this.edit_view_form_item_dic[code3];
				delete $this.edit_view_form_item_dic[code3];

				break;
			case 'csv_advanced':
				//Export Columns
				code = 'csv_export_columns';
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					field: code,
					allow_multiple_selection: true,
					layout_name: ALayoutIDs.OPTION_COLUMN,
					key: 'value',
					set_empty: true
				} );

				this.addEditFieldToColumn( $.i18n._( 'Export Columns' ), form_item_input, tab3_column1, '', null, true );

				this.api.getOptions( 'export_columns', 'csv_advanced', {
					onResult: function( result ) {
						var result_data = result.getResult();
						form_item_input.setSourceData( Global.buildRecordArray( result_data ) );
						form_item_input.setValue( $this.export_setup_data[code] );
					}
				} );

				$this.export_setup_ui_dic[code] = $this.edit_view_form_item_dic[code];
				delete $this.edit_view_form_item_dic[code];
				break;
			default:
				break;

		}

		this.setEditViewWidgetsMode();
		this.editFieldResize( 3 );
	},

	buildEditViewUI: function() {
		this.__super( 'buildEditViewUI' );

		var tab_3_label = this.edit_view.find( 'a[ref=tab3]' );
		tab_3_label.text( $.i18n._( 'Export Setup' ) );
	},

	removeCurrentExportUI: function() {

		for ( var key in this.export_setup_ui_dic ) {
			var html_item = this.export_setup_ui_dic[key];
			html_item.remove();
		}

		//Error: Unable to get property 'find' of undefined or null reference in https://ondemand3.timetrex.com/interface/html5/ line 1033
		if ( !this.edit_view_tab ) {
			return;
		}

		var tab3 = this.edit_view_tab.find( '#tab3' );
		var tab3_column1 = tab3.find( '.first-column' );
		var clear_both_div = tab3_column1.find( '.clear-both-div' );

		clear_both_div.remove();
	},

	getExportColumns: function( type ) {

		var columns = {};

		var source = this.export_grid.getGridParam( 'data' );

		var len = source.length;

		for ( var i = 0; i < len; i++ ) {
			var item = source[i];
			columns[item.column_id_key] = {};
			columns[item.column_id_key].hour_code = item.hour_code;

			if ( type === 'adp' || type === 'va_munis' ) {
				columns[item.column_id_key].hour_column = item.hour_column;
			}

		}

		return columns;

	},
	/* jshint ignore:start */
	getFormSetupData: function( for_view ) {
		var other = {};

		if ( !for_view ) {
			other.export_type = this.current_edit_record.export_type;
			other.export_columns = {};
			other.export_columns[other.export_type] = {columns: this.getExportColumns( other.export_type )};

			switch ( other.export_type ) {

				case 'adp':
					if ( !this.edit_view_ui_dic.company_code.getValue() ) {
						other.company_code = this.edit_view_ui_dic.company_code_text.getValue();
					} else {
						other.company_code = this.edit_view_ui_dic.company_code.getValue();
					}

					if ( !this.edit_view_ui_dic.batch_id.getValue() ) {
						other.batch_id = this.edit_view_ui_dic.batch_id_text.getValue();
					} else {
						other.batch_id = this.edit_view_ui_dic.batch_id.getValue();
					}

					if ( !this.edit_view_ui_dic.temp_dept.getValue() ) {
						other.temp_dept = this.edit_view_ui_dic.temp_dept_text.getValue();
					} else {
						other.temp_dept = this.edit_view_ui_dic.temp_dept.getValue();
					}

					break;
				case 'paychex_preview':
					other.client_number = this.edit_view_ui_dic.client_number.getValue();
					break;
				case 'paychex_preview_advanced_job':
					other.client_number = this.edit_view_ui_dic.client_number_adv.getValue();
					other.job_columns = this.edit_view_ui_dic.job_columns.getValue();
					other.state_columns = this.edit_view_ui_dic.state_columns.getValue();
					other.include_hourly_rate = this.edit_view_ui_dic.include_hourly_rate.getValue();
					break;
				case 'ceridian_insync':
					other.employer_number = this.edit_view_ui_dic.employer_number.getValue();
					break;
				case 'quickbooks':
				case 'quickbooks_advanced':
					other.company_name = this.edit_view_ui_dic.company_name.getValue();
					other.company_created_date = this.edit_view_ui_dic.company_created_date.getValue();
					other.proj = this.edit_view_ui_dic.proj.getValue();
					other.item = this.edit_view_ui_dic.item.getValue();
					other.job = this.edit_view_ui_dic.job.getValue();
					break;
				case 'va_munis':
					if ( !this.edit_view_ui_dic.department.getValue() ) {
						other.department = this.edit_view_ui_dic.department_text.getValue();
					} else {
						other.department = this.edit_view_ui_dic.company_code.getValue();
					}

					if ( !this.edit_view_ui_dic.employee_number.getValue() ) {
						other.employee_number = this.edit_view_ui_dic.employee_number_text.getValue();
					} else {
						other.employee_number = this.edit_view_ui_dic.employee_number.getValue();
					}

					if ( !this.edit_view_ui_dic.gl_account.getValue() ) {
						other.gl_account = this.edit_view_ui_dic.gl_account_text.getValue();
					} else {
						other.gl_account = this.edit_view_ui_dic.gl_account.getValue();
					}
					break;
				case 'csv_advanced':
					other.csv_export_columns = this.edit_view_ui_dic.csv_export_columns.getValue();
					break;

			}
		} else {
			other.export_type = this.current_edit_record.export_type;
			other[other.export_type] = {columns: this.getExportColumns( other.export_type )};

			switch ( other.export_type ) {
				case 'adp':

					if ( !this.edit_view_ui_dic.company_code.getValue() ) {
						other[other.export_type].company_code = 0;
						other[other.export_type].company_code_value = this.edit_view_ui_dic.company_code_text.getValue();
					} else {
						other[other.export_type].company_code = this.edit_view_ui_dic.company_code.getValue();
					}

					if ( !this.edit_view_ui_dic.batch_id.getValue() ) {
						other[other.export_type].batch_id = 0;
						other[other.export_type].batch_id_value = this.edit_view_ui_dic.batch_id_text.getValue();
					} else {
						other[other.export_type].batch_id = this.edit_view_ui_dic.batch_id.getValue();
					}

					if ( !this.edit_view_ui_dic.temp_dept.getValue() ) {
						other[other.export_type].temp_dept = 0;
						other[other.export_type].temp_dept_value = this.edit_view_ui_dic.temp_dept_text.getValue();
					} else {
						other[other.export_type].temp_dept = this.edit_view_ui_dic.temp_dept.getValue();
					}

					break;
				case 'paychex_preview':
					other[other.export_type].client_number = this.edit_view_ui_dic.client_number.getValue();
					break;
				case 'paychex_preview_advanced_job':
					other[other.export_type].client_number = this.edit_view_ui_dic.client_number_adv.getValue();
					other[other.export_type].job_columns = this.edit_view_ui_dic.job_columns.getValue();
					other[other.export_type].state_columns = this.edit_view_ui_dic.state_columns.getValue();
					other[other.export_type].include_hourly_rate = this.edit_view_ui_dic.include_hourly_rate.getValue();
					break;
				case 'ceridian_insync':
					other[other.export_type].employer_number = this.edit_view_ui_dic.employer_number.getValue();
					break;
				case 'quickbooks':
				case 'quickbooks_advanced':
					other[other.export_type].company_name = this.edit_view_ui_dic.company_name.getValue();
					other[other.export_type].company_created_date = this.edit_view_ui_dic.company_created_date.getValue();
					other[other.export_type].proj = this.edit_view_ui_dic.proj.getValue();
					other[other.export_type].item = this.edit_view_ui_dic.item.getValue();
					other[other.export_type].job = this.edit_view_ui_dic.job.getValue();
					break;
				case 'va_munis':
					if ( !this.edit_view_ui_dic.department.getValue() ) {
						other[other.export_type].department = 0;
						other[other.export_type].department_value = this.edit_view_ui_dic.department_text.getValue();
					} else {
						other[other.export_type].department = this.edit_view_ui_dic.company_code.getValue();
					}

					if ( !this.edit_view_ui_dic.employee_number.getValue() ) {
						other[other.export_type].employee_number = 0;
						other[other.export_type].employee_number_value = this.edit_view_ui_dic.employee_number_text.getValue();
					} else {
						other[other.export_type].employee_number = this.edit_view_ui_dic.employee_number.getValue();
					}

					if ( !this.edit_view_ui_dic.gl_account.getValue() ) {
						other[other.export_type].gl_account = 0;
						other[other.export_type].gl_account_value = this.edit_view_ui_dic.gl_account_text.getValue();
					} else {
						other[other.export_type].gl_account = this.edit_view_ui_dic.gl_account.getValue();
					}
					break;
				case 'csv_advanced':
					other[other.export_type].export_columns = this.edit_view_ui_dic.csv_export_columns.getValue();
					break;

			}

		}

		return other;
	},
	/* jshint ignore:end */

	setFormSetupData: function( res_Data ) {

		this.export_setup_data = res_Data;

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {

			if ( res_Data.export_type ) {

				this.edit_view_ui_dic.export_type.setValue( res_Data.export_type );
				this.current_edit_record.export_type = res_Data.export_type;
			}

		}

		this.onExportChange( res_Data.export_type );
	}

} );
