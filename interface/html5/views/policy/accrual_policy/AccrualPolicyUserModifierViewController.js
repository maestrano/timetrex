AccrualPolicyUserModifierViewController = BaseViewController.extend( {

	el: '#accrual_policy_user_modifier_view_container', //Must set el here and can only set string, so events can work

	user_api: null,

	parent_view: null,

	result_details: null,

	initialize: function() {
		if ( Global.isSet( this.options.sub_view_mode ) ) {
			this.sub_view_mode = this.options.sub_view_mode;
		}

		if ( Global.isSet( this.options.parent_view ) ) {
			this.parent_view = this.options.parent_view;
		}

		if ( this.parent_view === 'employee' ) {
			this.context_menu_name = $.i18n._( 'Accruals' );
			this.navigation_label = $.i18n._( 'Accrual' ) + ':';
		} else if ( this.parent_view === 'accrual_policy' ) {

			this.context_menu_name = $.i18n._( 'Employee Settings' );
			this.navigation_label = $.i18n._( 'Employee Accrual Modifier' ) + ':';
		}

		this._super( 'initialize' );
		this.edit_view_tpl = 'AccrualPolicyUserModifierEditView.html';
		this.permission_id = 'accrual_policy';
		this.script_name = 'AccrualPolicyUserModifierView';
		this.viewId = 'AccrualPolicyUserModifier';
		this.table_name_key = 'accrual_policy_user_modifier';

		this.api = new (APIFactory.getAPIClass( 'APIAccrualPolicyUserModifier' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.add] = true;

		this.render();
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary();

	},



	onAddClick: function() {
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		var user_id;

		if ( $this.sub_view_mode && $this.parent_key ) {
			switch( $this.parent_key ) {
				case 'user_id':
					user_id = $this.parent_value;
					break;
				case 'accrual_policy_id':
					user_id = false;
					break;
			}
		} else {
			user_id = false;
		}

		$this.api['get' + $this.api.key_name + 'DefaultData']( user_id, {onResult: function( result ) {
			$this.onAddResult( result );

		}} );

	},


	setSelectLayout: function( column_start_from ) {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		var column_info_array = [];

		var column_info;

		if ( !this.select_layout ) { //Set to default layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
			this.select_layout.data.display_columns = this.default_display_columns;
		}
		var layout_data = this.select_layout.data;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( !this.sub_view_mode ) {

			//Set Display Column in layout panel
			this.column_selector.setSelectGridData( display_columns );

			//Set Sort by awesomebox in layout panel
			this.sort_by_selector.setSourceData( this.buildSortSelectorUnSelectColumns( display_columns ) );
			this.sort_by_selector.setValue( this.buildSortBySelectColumns() );

			//Set Previoous Saved layout combobox in layout panel
			var layouts_array = this.search_panel.getLayoutsArray();

			this.setPreviousSavedSearchSourcesAndValue( layouts_array );

		}

		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

//		for ( i = start_from; i < len; i++ ) {
//			var view_column_data = display_columns[i];
//
//			var column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 100, sortable: false, title: false};
//			column_info_array.push( column_info );
//		}

		if ( this.parent_view === 'accrual_policy' ) {
			column_info = {name: 'full_name', index: 'full_name', label: $.i18n._('Employee'), width:50, sortable: false, title: false};
			column_info_array.push( column_info );

		} else if ( this.parent_view === 'employee' ) {
			column_info = {name: 'accrual_policy', index: 'accrual_policy', label: $.i18n._('Accrual Policy'), width:50, sortable: false, title: false};
			column_info_array.push( column_info );
		}

		column_info = {name: 'length_of_service_date', index: 'length_of_service_date', label: $.i18n._('Length of Service Date'), width:80, sortable: false, title: false};
		column_info_array.push( column_info );

		column_info = {name: 'length_of_service_modifier', index: 'length_of_service_modifier', label: $.i18n._('Length of Service Modifier'), width:90, sortable: false, title: false};
		column_info_array.push( column_info );

		column_info = {name: 'accrual_rate_modifier', index: 'accrual_rate_modifier', label: $.i18n._('Accrual Rate Modifier'), width:80, sortable: false, title: false};
		column_info_array.push( column_info );

		column_info = {name: 'maximum_time_modifier', index: 'maximum_time_modifier', label: $.i18n._('Accrual Total Maximum Modifier'), width:110, sortable: false, title: false};
		column_info_array.push( column_info );

//		column_info = {name: 'minimum_time_modifier', index: 'minimum_time_modifier', label: 'Accrual Total Minimum Modifier', width:110, sortable: false, title: false};
//		column_info_array.push( column_info );

		column_info = {name: 'rollover_time_modifier', index: 'rollover_time_modifier', label: $.i18n._('Annual Maximum Rollover Modifier'), width:110, sortable: false, title: false};
		column_info_array.push( column_info );


		if ( !this.grid ) {
			this.grid = grid;

			this.grid = this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: (Global.bodyWidth() - 14),
				rowNum: 10000,
				colNames: [],
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				onSelectAll: function() {
					$this.onGridSelectAll();
				},
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				colModel: column_info_array,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true

			} );

		} else {

			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );

			this.grid = this.grid.jqGrid( {
				altRows: true,
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				data: [],
				rowNum: 10000,
				onSelectAll: function() {
					$this.onGridSelectAll();
				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				sortable: false,
				datatype: 'local',
				width: (Global.bodyWidth() - 14),
				colNames: [],
				colModel: column_info_array,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true
			} );

		}

		$this.setGridSize();

		//Add widget on UI and bind events. Next set data in it in search result.
		if ( LocalCacheData.paging_type === 0 ) {
			if ( this.paging_widget.parent().length > 0 ) {
				this.paging_widget.remove();
			}

			this.paging_widget.css( 'width', this.grid.width() );
			this.grid.append( this.paging_widget );

			this.paging_widget.click( $.proxy( this.onPaging, this ) );

		} else {
			if ( this.paging_widget.parent().length < 1 ) {
				$( this.el ).find( '.total-number-div' ).append( this.paging_widget );
				$( this.el ).find( '.bottom-div' ).append( this.paging_widget_2 );

				this.paging_widget.bind( 'paging', $.proxy( this.onPaging2, this ) );
				this.paging_widget_2.bind( 'paging', $.proxy( this.onPaging2, this ) );
			}

		}

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		if ( !this.sub_view_mode ) {
			this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.
		}

		this.showGridBorders();

	},

	getFilterColumnsFromDisplayColumns: function() {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.accrual_policy_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;

		//Fixed possible exception -- Error: Unable to get property 'length' of undefined or null reference in https://villa.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 5031
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();

		if ( selectedId > 0 ) {

			var filter = {};

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}} );
		} else {

			var result_data = this.getRecordFromGridById( selectedId );

			result_data.id = '';

			if ( $this.sub_view_mode && $this.parent_key ) {
				result_data[$this.parent_key] = $this.parent_value;
			}

			$this.current_edit_record = result_data;
			$this.initEditView();
		}

	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {

			$this.is_add = false;
			var result_data = result.getResult();

			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( result_data > 0 ) {
					$this.refresh_id = result_data;
				}

				if ( Global.isSet( $this.refresh_id ) === false ) {
					$this.result_details = result.getDetails();
				}

				$this.search();
			}

			$this.onSaveDone( result );
			$this.current_edit_record = null;
			$this.removeEditView();

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},


	search: function( set_default_menu, page_action, page_number, callBack ) {
		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {

				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}

			}

		} else {
			filter.filter_page = 1;
		}

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}

		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		if ( this.refresh_id > 0 ) {
			filter.filter_data = {};
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {

			if ( Global.isSet( this.result_details ) && this.result_details.length > 0 ) {
				this.result_details = $.map( this.result_details, function( n ) {
					return n === true ? 0 : n;
				} );
				this.last_select_ids = Global.concatArraysUniqueWithSort( this.result_details, this.getGridSelectIdArray() );
			} else {
				this.last_select_ids = this.getGridSelectIdArray();
			}

		}

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();
			var len;

			if ( !Global.isArray( result_data ) ) {
				$this.showNoResultCover()
			} else {
				$this.removeNoResultCover();
				if ( Global.isSet( $this.__createRowId ) ) {
					result_data = $this.__createRowId( result_data );
				}

				result_data = Global.formatGridData( result_data, $this.api.key_name );

				len = result_data.length;
			}
			if ( $this.refresh_id > 0 ) {
				$this.refresh_id = null;
				var grid_source_data = $this.grid.getGridParam( 'data' );
				len = grid_source_data.length;

				if ( $.type( grid_source_data ) !== 'array' ) {
					grid_source_data = [];
				}

				var found = false;
				var new_record = result_data[0];

				//Error: Uncaught TypeError: Cannot read property 'id' of undefined in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
				if ( new_record ) {
					var new_grid_source_data = [];

					for ( var i = 0; i < len; i++ ) {
						var record = grid_source_data[i];

						//Fixed === issue. The id set by jQGrid is string type.
						if ( !isNaN( parseInt( record.id ) ) ) {
							record.id = parseInt( record.id );
						}

						if ( record.id == new_record.id ) {
							$this.grid.setRowData( new_record.id, new_record );
							found = true;
							break
						}

						if ( record.id < 0 && record.user_id == new_record.user_id ) {

						} else {
							new_grid_source_data.push( record );
						}
					}

					if ( !found ) {
//						$this.grid.addRowData( new_record.id, new_record, 0 );
						$this.grid.clearGridData();
						$this.grid.setGridParam( {data: new_grid_source_data.concat( new_record )} );

						if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGridHeight ) ) {
							len = Global.isSet( len ) ? len : 0;
							$this.resizeSubGridHeight( len + 1 );
						}

						$this.grid.trigger( 'reloadGrid' );
						$this.reSelectLastSelectItems();
					}
				}

			} else {
				//Set Page data to widget, next show display info when setDefault Menu
				$this.pager_data = result.getPagerData();

				//CLick to show more mode no need this step
				if ( LocalCacheData.paging_type !== 0 ) {
					$this.paging_widget.setPagerData( $this.pager_data );
					$this.paging_widget_2.setPagerData( $this.pager_data );
				}

				if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
					var current_data = $this.grid.getGridParam( 'data' );
					result_data = current_data.concat( result_data );
				}
				$this.grid.clearGridData();
				$this.grid.setGridParam( {data: result_data} );

				if ( $this.sub_view_mode && Global.isSet( $this.resizeSubGridHeight ) ) {
					$this.resizeSubGridHeight( len );
				}

				$this.grid.trigger( 'reloadGrid' );
				$this.reSelectLastSelectItems();

			}

			$this.result_details = null;

			$this.setGridCellBackGround(); //Set cell background for some views

			ProgressBar.closeOverlay(); //Add this in initData

			if ( set_default_menu ) {
				$this.setDefaultMenu( true );
			}

			if ( LocalCacheData.paging_type === 0 ) {
				if ( !$this.pager_data || $this.pager_data.is_last_page ) {
					$this.paging_widget.css( 'display', 'none' );
				} else {
					$this.paging_widget.css( 'display', 'block' );
				}
			}

			if ( callBack ) {
				callBack( result );
			}

			// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
			if ( set_default_menu ) {
				$this.autoOpenEditViewIfNecessary();
			}

			$this.searchDone();

		}} );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_employee_accrual_modifier': this.parent_view === 'employee'?$.i18n._( 'Accrual' ):$.i18n._( 'Employee Accrual Modifier' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

//		var tab_0_label = this.edit_view.find( 'a[ref=tab_employee_accrual_modifier]' );
//		var tab_1_label = this.edit_view.find( 'a[ref=tab_audit]' );
//
//		if ( this.parent_view === 'accrual_policy' ) {
//			this.context_menu_name = $.i18n._( 'Employee Accrual Modifier' );
//			$( '.ribbonTabLabel' ).find("a[ref=" + this.viewId + "ContextMenu" + "]").text( $.i18n._( 'Employee Accrual Modifier' ) );
//
//			tab_0_label.text( $.i18n._( 'Employee Accrual Modifier' ) );
//
//		} else if ( this.parent_view === 'employee' ) {
//			tab_0_label.text( $.i18n._( 'Accrual' ) );
//		}
//
//		tab_1_label.text( $.i18n._( 'Audit' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicyUserModifier' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE,
			show_search_inputs: true,
			navigation_mode: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_employee_accrual_modifier = this.edit_view_tab.find( '#tab_employee_accrual_modifier' );

		var tab_employee_accrual_modifier_column1 = tab_employee_accrual_modifier.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_employee_accrual_modifier_column1 );

		var form_item_input;

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'

		} );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );


		// Accrual Policy

		var default_args = {};
		default_args.filter_data = {};
		default_args.filter_data.type_id = [20, 30];

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy_id'

		} );
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Policy' ), form_item_input, tab_employee_accrual_modifier_column1, '' );


		// Length of Service Date

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'length_of_service_date', width: 120} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + ' (' + $.i18n._('Leave blank for hire date') + ')' + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Length of Service Date' ), form_item_input, tab_employee_accrual_modifier_column1, '', widgetContainer );


		//Modifier Rates
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Modifier Rates' )} );
		this.addEditFieldToColumn( null, form_item_input, tab_employee_accrual_modifier_column1, '', null, true, false, 'separated_1' );


		// Length of Service Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'length_of_service_modifier', width: 40} );

		this.addEditFieldToColumn( $.i18n._( 'Length of Service' ), form_item_input, tab_employee_accrual_modifier_column1 );


		// Accrual Rate Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'accrual_rate_modifier', width: 40} );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_employee_accrual_modifier_column1 );


		// Accrual Total Maximum Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_time_modifier', width: 40} );

		this.addEditFieldToColumn( $.i18n._( 'Accrual Total Maximum' ), form_item_input, tab_employee_accrual_modifier_column1 );


		// Accrual Total Minimum Modifier
//		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
//		form_item_input.TTextInput( {field: 'minimum_time_modifier', width: 40} );
//
//		this.addEditFieldToColumn( $.i18n._( 'Accrual Total Minimum' ), form_item_input, tab_employee_accrual_modifier_column1 );


		//Annual Maximum Rollover Modifier
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'rollover_time_modifier', width: 40} );

		this.addEditFieldToColumn( $.i18n._( 'Annual Maximum Rollover' ), form_item_input, tab_employee_accrual_modifier_column1 );

		//Modifier Rates
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( {label: $.i18n._( 'Current Milestone' ) + ': '} );
		this.addEditFieldToColumn( null, form_item_input, tab_employee_accrual_modifier_column1, '', null, true, false, 'separated_2' );


		// Accrual Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'accrual_rate'} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// Accrual Total Maximum Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'maximum_time'} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Total Maximum Time' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

		// Accrual Maximum Rollover
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'rollover_time'} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Maximum Rollover' ), form_item_input, tab_employee_accrual_modifier_column1, '', null, true );

	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.setAccrualPolicyDataFromUserModifier();
		this.getAccrualPolicyDataFromUserModifier();
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		switch( key ) {
			case 'user_id':
				if ( this.is_add ) {
					this.api['get' + this.api.key_name + 'DefaultData']( c_value, {onResult: function( result ) {
						var result_data = result.getResult();

						if ( !result_data ) {
							result_data = [];
						}

						if ( $this.sub_view_mode && $this.parent_key === 'accrual_policy_id' ) {
							result_data[$this.parent_key] = $this.parent_value;
						}

						$this.current_edit_record = result_data;
						$this.setCurrentEditRecordData();
						$this.validate();

					}} );
				} else {
					this.current_edit_record[key] = c_value;
					this.validate();
					this.getAccrualPolicyDataFromUserModifier();
				}
				break;
			default :
				this.current_edit_record[key] = c_value;
				this.validate();
				this.getAccrualPolicyDataFromUserModifier();
				break;
		}


	},

	getAccrualPolicyDataFromUserModifier: function() {
		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );
		this.api['getAccrualPolicyDataFromUserModifier']( record, {onResult: function( result ) {
			var result_data = result.getResult();
			$this.setAccrualPolicyDataFromUserModifier( result_data );
		}} );


	},


	setAccrualPolicyDataFromUserModifier: function( result_data ) {
		var $this = this;
		if ( Global.isSet( result_data ) && !Global.isFalseOrNull( result_data ) ) {

			$this.edit_view_form_item_dic['separated_2'].find('.label' ).text( $.i18n._( 'Current Milestone' ) + ': ' + result_data.milestone_number );
			$this.edit_view_ui_dic['accrual_rate'].setValue( Global.secondToHHMMSS( result_data.accrual_rate ) );
			$this.edit_view_ui_dic['maximum_time'].setValue( Global.secondToHHMMSS( result_data.maximum_time ) );
			$this.edit_view_ui_dic['rollover_time'].setValue( Global.secondToHHMMSS( result_data.rollover_time ) );

			$this.edit_view_form_item_dic['separated_2'].css('display', 'block');
			$this.edit_view_form_item_dic['accrual_rate'].css('display', 'block');
			$this.edit_view_form_item_dic['maximum_time'].css('display', 'block');
			$this.edit_view_form_item_dic['rollover_time'].css('display', 'block');

		} else {

			if ( !$this.is_edit ) {

				$this.edit_view_form_item_dic['separated_2'].css('display', 'none');
				$this.edit_view_form_item_dic['accrual_rate'].css('display', 'none');
				$this.edit_view_form_item_dic['maximum_time'].css('display', 'none');
				$this.edit_view_form_item_dic['rollover_time'].css('display', 'none');
			}

		}
	},

	removeEditView: function() {
		this._super('removeEditView');

		if ( this.parent_view === 'accrual_policy' ) {

			this.context_menu_name = $.i18n._( 'Employee Settings' );
			$( '.ribbonTabLabel' ).find("a[ref=" + this.viewId + "ContextMenu" + "]").text( $.i18n._( 'Employee Settings' ) );
		}

	},


	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		$this.is_viewing = true;
		$this.is_edit = false;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( editId ) ) {
			var selectedId = editId
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];


		if ( this.sub_view_mode && this.parent_key ) {
			filter.filter_data[this.parent_key] = this.parent_value;
		}

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();
			if ( !result_data ) {
				result_data = [];
			}

			result_data = result_data[0];

			if ( !result_data ) {
				TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
				$this.onCancelClick();
				return;
			}

			$this.current_edit_record = result_data;

			$this.initEditView();

		}} );

	},


	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		if ( this.sub_view_mode && this.parent_key ) {
			filter.filter_data[this.parent_key] = this.parent_value;
		}

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			$this.api['getOptions']( 'unique_columns', {onResult: function( result ) {
				$this.unique_columns = result.getResult();
				$this.api['getOptions']( 'linked_columns', {onResult: function( result1 ) {
					$this.linked_columns = result1.getResult();
					if ( $this.sub_view_mode && $this.parent_key ) {
						result_data[$this.parent_key] = $this.parent_value;
					}
					$this.current_edit_record = result_data;
					$this.initEditView();

				}} );

			}} );

		}} );

	},

	validate: function() {
		var $this = this;

		var record;

		if ( this.is_mass_editing ) {

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {

				var check_fields = {};
				if ( value < 0 ) {
					check_fields = $this.getRecordFromGridById( value );
				} else {
					check_fields.id = value;
				}

				for( var key in $this.edit_view_ui_dic ) {

					if ( !$this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						continue;
					}

					var widget = $this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							switch( key ) {
								case 'user_id':
//									if ( value > 0 ) {
//										check_fields[key] = $this.current_edit_record[key];
//									}
									break;
								default:
									check_fields[key] = widget.getValue();
									break;
							}
						}
					}
				}

				var common_record = Global.clone( check_fields );
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );


//			for ( var key in this.edit_view_ui_dic ) {
//
//				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
//					continue;
//				}
//
//				var widget = this.edit_view_ui_dic[key];
//
//				if ( Global.isSet( widget.isChecked ) ) {
//					if ( widget.isChecked() && widget.getEnabled() ) {
//						record[key] = widget.getValue();
//					}
//
//				}
//			}

		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );

		}} );
	},

	onSaveClick: function() {
		var $this = this;
		var record;
//		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {

				var check_fields = {};
				if ( value < 0 ) {
					check_fields = $this.getRecordFromGridById( value );
				} else {
					check_fields.id = value;
				}

				for( var key in $this.edit_view_ui_dic ) {
					var widget = $this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() ) {
							switch( key ) {
								case 'user_id':
//									if ( value > 0 ) {
//										check_fields[key] = $this.current_edit_record[key];
//									}
									break;
								default:
									check_fields[key] = $this.current_edit_record[key];
									break;
							}
						}
					}
				}

				var common_record = Global.clone( check_fields );
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['set' + this.api.key_name]( record, {onResult: function( result ) {

			$this.onSaveResult( result );

		}} );
	}


} );

AccrualPolicyUserModifierViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'AccrualPolicyUserModifier', 'SubAccrualPolicyUserModifierView.html', function( result ) {
//		var args = { };
//		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			var	template = beforeViewLoadedFun( result );
		}

		if ( Global.isSet( container ) ) {
			container.html( template );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_accrual_policy_user_modifier_view_controller );
			}

		}

	} );

};