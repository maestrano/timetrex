UserGroupViewController = BaseViewController.extend( {
	el: '#user_group_view_container',
	tree_mode: null,
	grid_table_name: null,
	grid_select_id_array: null,
	//Must set el here and can only set string, so events can work
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'UserGroupEditView.html';
		this.permission_id = 'user';
		this.viewId = 'UserGroup';
		this.script_name = 'UserGroupView';
		this.table_name_key = 'user_group';
		this.context_menu_name = $.i18n._( 'Employee Groups' );
		this.grid_table_name = $.i18n._( 'Employee Groups' );
		this.navigation_label = $.i18n._( 'Employee Groups' ) + ':';
		this.tree_mode = true;
		this.api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;

		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'UserGroup' );

		this.grid_select_id_array = [];
	},

	onDeleteDone: function( result ) {
		this.grid_select_id_array = [];
		this.setDefaultMenu();
	},

	onSaveDone: function( result ) {
		this.grid_select_id_array = [];
	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
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
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();

		var filter = {};

		filter.filter_data = {};

		this.api['get' + this.api.key_name]( filter, false, false, {onResult: function( result ) {
			var result_data = result.getResult();

			result_data = Global.buildTreeRecord( result_data );

			result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

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
			$this.current_edit_record.id = selectedId;

			$this.initEditView();

		}} );

	},

	onCopyAsNewClick: function() {

		LocalCacheData.current_doing_context_action = 'copy_as_new';
		var $this = this;
		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {
			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			filter.filter_data = {};

			this.api['get' + this.api.key_name]( filter, false, false, {onResult: function( result ) {
				var result_data = result.getResult();

				result_data = Global.buildTreeRecord( result_data );

				result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.


				result_data = result_data[0];

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;
				$this.current_edit_record.id = '';

				$this.initEditView();

			}} );
		}

	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		$this.is_viewing = true;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};

		this.api['get' + this.api.key_name]( filter, false, false, {onResult: function( result ) {

			var result_data = result.getResult();

			result_data = Global.buildTreeRecord( result_data );

			result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

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
			$this.current_edit_record.id = selectedId;

			$this.initEditView();

		}} );

	},

	addIdFieldToNavigation: function( array ) {
		$.each( array, function( key, item ) {
			$( item ).each( function( i_key, i_item ) {
				i_item.id = i_item._id_;
			} );
		} );

		return array;
	},

	setEditViewData: function() {

		var $this = this;

		this.is_changed = false;

		if ( !this.edit_only_mode ) {

			var navigation_div = this.edit_view.find( '.navigation-div' );

			if ( Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					this.navigation.setSourceData( Global.addFirstItemToArray( this.grid_current_page_items ) );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( this.current_edit_record );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		for ( var key in this.edit_view_ui_dic ) {

			//Set all UI field to current edit reocrd, we need validate all UI fielld when save and validate
			if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
				$this.current_edit_record[key] = false;
			}

		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
		this.switchToProperTab();
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'parent_id':
						widget.setSourceData( Global.addFirstItemToArray( this.grid_current_page_items ) );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	buildEditViewUI: function() {

		var $this = this;

		//No navigation when edit only mode
		if ( !this.edit_only_mode ) {
			var navigation_div = this.edit_view.find( '.navigation-div' );
			var label = navigation_div.find( '.navigation-label' );

			var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );

			this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			label.text( this.navigation_label );

			navigation_widget_div.append( this.navigation );
		}

		var close_icon = this.edit_view.find( '.close-icon' );

		close_icon.click( function() {
			$this.onCloseIconClick();
		} );

		this.setTabLabels( {
			'tab_employee_group': $.i18n._( 'Employee Group' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			navigation_mode: true,
			show_search_inputs: false
		} );

		this.setNavigation();


		//Tab 0 start

		var tab_employee_group = this.edit_view_tab.find( '#tab_employee_group' );

		var tab_employee_group_column1 = tab_employee_group.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_employee_group_column1 );

		//Parent
		//Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty:true,
			field: 'parent_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Parent' ), form_item_input, tab_employee_group_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_employee_group_column1, '' );

		form_item_input.parent().width( '45%' );

	},

	getAllColumns: function( callBack ) {

		var $this = this;

		this.api.getOptions( 'columns', {onResult: function( columns_result ) {

			var columns_result_data = columns_result.getResult();
			$this.all_columns = Global.buildColumnArray( columns_result_data );

			if ( callBack ) {
				callBack();
			}

		}} );

	},

	initLayout: function() {

		var $this = this;

		$this.getDefaultDisplayColumns( function() {

			$this.setSelectLayout();

			$this.search();
			//set right click menu to list view grid
			$this.initRightClickMenu();

		} );

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

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
			this.select_layout.data.display_columns = this.default_display_columns;
		}
		var layout_data = this.select_layout.data;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );
		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

		var view_column_data = display_columns[0];

		var column_info = {name: view_column_data.value, index: view_column_data.value, label: $this.grid_table_name, width: 100, sortable: false, title: false};

		column_info_array.push( column_info );

		if ( !this.grid ) {

			this.grid = grid;

			this.grid.jqGrid( {
				altRows: true,
				tree_mode: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				colNames: [],
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				colModel: column_info_array,
				viewrecords: true
			} );

		} else {

			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );

			this.grid.jqGrid( {
				altRows: true,
				tree_mode: true,
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				data: [],
				rowNum: 10000,
				sortable: false,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: column_info_array,
				viewrecords: true
			} );

		}

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style

		this.filter_data = this.select_layout.data.filter_data;

		this.showGridBorders();

	},

	setGridSize: function() {

		if ( !this.grid || !this.grid.is( ':visible' ) ) {
			return;
		}

		if ( !this.sub_view_mode ) {
			if ( Global.bodyWidth() > Global.app_min_width ) {
				this.grid.setGridWidth( Global.bodyWidth() - 14 );
			} else {
				this.grid.setGridWidth( Global.app_min_width - 14 );
			}
		} else {

			this.grid.setGridWidth( $( this.el ).parent().width() - 10 );
		}

		if ( !this.sub_view_mode ) {
			this.grid.setGridHeight( $( this.el ).height() - 90 );
		} else {
			this.grid.setGridHeight( $( this.el ).parent().height() );
		}

	},

	getGridSelectIdArray: function() {
		var result = [];
		result = this.grid_select_id_array;

		return result;
	},

	reSetGridTreeData: function( val ) {

		var $this = this;
		var col_model = this.grid.getGridParam( 'colModel' );
		this.grid.jqGrid( 'GridUnload' );
		this.grid = null;

		var grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		this.grid = $( grid );

		this.grid = this.grid.jqGrid( {
			datastr: val,
			altRows: true,
			datatype: 'jsonstring',
			sortable: false,
			width: Global.bodyWidth() - 14,
			colNames: [],
			rowNum: 10000,
			colModel: col_model,
			onSelectRow: function( id ) {
				$this.grid_select_id_array = [id];
				$this.setDefaultMenu();
			},
			ondblClickRow: function() {
				$this.onGridDblClickRow();
			},
			gridview: true,
			treeGrid: true,
			treeGridModel: 'adjacency',
			treedatatype: 'local',
			ExpandColumn: 'name',
			jsonReader: {
				repeatitems: false,
				root: function( obj ) {
					return obj;
				},
				page: function( obj ) {
					return 1;
				},
				total: function( obj ) {
					return 1;
				},
				records: function( obj ) {
					return obj.length;
				}
			}
		} );
	},

	search: function( set_default_menu, page_action, page_number ) {

		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;

			//If sub view controller set custom filters, get it
			if ( Global.isSet( this.getSubViewFilter ) ) {

				this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

			}
		}

		this.last_select_ids = this.getGridSelectIdArray();
		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		this.api['get' + this.api.key_name]( filter, false, false, {onResult: function( result ) {

			var result_data = result.getResult();

			result_data = Global.buildTreeRecord( result_data );

			$this.grid_current_page_items = result_data; // For tree mode only

			if ( !Global.isArray( result_data ) ) {
				$this.showNoResultCover();
			} else {
				$this.removeNoResultCover();
			}

			$this.reSetGridTreeData( result_data );

			$this.setGridSize();

			ProgressBar.closeOverlay(); //Add this in initData
			if ( set_default_menu ) {
				$this.setDefaultMenu();
			}

			if ( LocalCacheData.paging_type === 0 ) {
				if ( !$this.pager_data || $this.pager_data.is_last_page ) {
					$this.paging_widget.css( 'display', 'none' );
				} else {
					$this.paging_widget.css( 'display', 'block' );
				}
			}

			$this.reSelectLastSelectItems();
			$this.autoOpenEditViewIfNecessary();
			$this.searchDone();

		}} );

	},

	getRecordFromGridById: function( id ) {
		var data = this.grid.getGridParam( 'data' );
		var result = null;
		/* jshint ignore:start */
		//id could be string or number.
		$.each( data, function( index, value ) {

			if ( value['_id_'] == id ) {
				result = Global.clone( value );
				return false;
			}

		} );
		/* jshint ignore:end */

		if ( result ) {
			result.id = result['_id_'];
		}
		return result;

	},

	render: function() {

		var $this = this;

		$( window ).resize( function() {
			if ( $this.grid ) {
				$this.setGridSize();

			}

		} );
	}

} );

UserGroupViewController.loadView = function() {
	Global.loadViewSource( 'UserGroup', 'UserGroupView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );
		Global.contentContainer().html( template );
	} );
};