ScheduleViewController = BaseViewController.extend( {

	el: '#schedule_view_container', //Must set el here and can only set string, so events can work

	user_group_api: null,

	status_array: null,

	user_group_array: null,

	toggle_button: null,

	start_date_picker: null,

	start_date: null,

	end_date: null,

	full_schedule_data: null,

	schedule_columns: null,

	full_format: 'ddd-MMM-DD-YYYY',

	weekly_format: 'ddd, MMM DD',

	final_schedule_data_array: [],

	has_date_array: [],

	no_date_array: [],

	shift_key_name_array: [],

	select_cells_Array: [], //Timesheet grid

	select_all_shifts_array: [], //Timesheet grid.

	select_shifts_array: [], //Timesheet grid.

	select_recurring_shifts_array: [], //Timesheet grid.

	all_employee_btn: null,

	daily_totals_btn: null,

	weekly_totals_btn: null,

	strict_range_btn: null,

	month_date_row_array: null,

	month_date_row_tr_ids: null, // month date tr id in grid table

	month_date_row_position: null, //month date tr position in table

	month_current_header_number: 0, //0 is default column header

	day_header_width: 0,

	day_hour_width: 60,

	select_drag_menu_id: '', //Do drag move or copy

	is_override: false,

	scroll_position: 0,

	selected_user_ids: [],

	pre_total_time: 0,

	is_mass_adding: false,

	calculate_cell_number: 0,

	scroll_interval: null,

	scroll_unit: 0,

	holiday_data_dic: {},

	absence_policy_api: null,

	year_mode_original_date: null, //set this when search for yer mode with use_date_picker true, so Keep select date in ritict mode

	initialize: function() {

		this._super( 'initialize' );
		this.permission_id = 'schedule';
		this.script_name = 'ScheduleView';
		this.viewId = 'Schedule';
		this.table_name_key = 'schedule';
		this.context_menu_name = $.i18n._( 'Schedules' );
		this.navigation_label = $.i18n._( 'Schedule' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APISchedule' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.absence_policy_api = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.currency_api = new (APIFactory.getAPIClass( 'APICurrency' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
		}

		this.api_absence_policy = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		this.scroll_position = 0;

		this.initPermission();
		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	jobUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( "job", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) &&
			( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( "job_item", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	branchUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
	initPermission: function() {
		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

		if ( this.jobUIValidate() ) {
			this.show_job_ui = true;
		} else {
			this.show_job_ui = false;
		}

		if ( this.jobItemUIValidate() ) {
			this.show_job_item_ui = true;
		} else {
			this.show_job_item_ui = false;
		}

		if ( this.branchUIValidate() ) {
			this.show_branch_ui = true;
		} else {
			this.show_branch_ui = false;
		}

		if ( this.departmentUIValidate() ) {
			this.show_department_ui = true;
		} else {
			this.show_department_ui = false;
		}

	},

	//only be call once when open this view
	initData: function() {

		var $this = this;

		//Remove tab if any
		Global.removeViewTab( this.viewId );

		ProgressBar.showOverlay();
		this.initOptions();
		this.default_display_columns = ['branch', 'department'];
		this.getAllColumns( function() {
			$this.initLayout();
		} );

		var date = new Date();

		var format = Global.getLoginUserDateFormat();
		var dateStr = date.format( format );

		if ( !LocalCacheData.last_schedule_selected_date ) {
			if ( LocalCacheData.current_selet_date ) { //Select date get from URL.
				this.setDatePickerValue( Global.strToDate( LocalCacheData.current_selet_date, 'YYYYMMDD' ).format() );
				LocalCacheData.current_selet_date = '';
			} else {
				this.setDatePickerValue( dateStr );
			}
		} else {
			this.setDatePickerValue( LocalCacheData.last_schedule_selected_date );
		}

		this.setMoveOrDropMode( ContextMenuIconName.move );

	},

	setToggleButtonValue: function( val ) {

		this.toggle_button.setValue( val );

		this.setToggleButtonUrl();
	},

	setToggleButtonUrl: function() {

		var mode = this.getMode();
		var default_date = this.start_date_picker.getDefaultFormatValue();
		if ( !this.edit_view ) {
			window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + default_date + '&mode=' + mode;
		}
	},

	setDatePickerValue: function( val ) {

		this.start_date_picker.setValue( val );

		this.setDateUrl();

		LocalCacheData.last_schedule_selected_date = val;

	},

	getAllColumns: function( callBack ) {

		var $this = this;
		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.all_columns = Global.buildColumnArray( columns_result_data );

				$this.api.getOptions( 'group_columns', {
					onResult: function( group_columns_result ) {

						var all_columns = Global.buildColumnArray( columns_result_data );
						var group_columns_result_data = group_columns_result.getResult();

						var final_all_columns = [];

						var all_len = all_columns.length;
						var group_len = group_columns_result_data.length;

						for ( var i = 0; i < group_len; i++ ) {
							var group_column_id = group_columns_result_data[i];
							for ( var j = 0; j < all_len; j++ ) {
								var column = all_columns[j];
								if ( column.value === group_column_id ) {
									final_all_columns.push( column );
									break;
								}
							}
						}

						$this.all_columns = final_all_columns;

						$this.column_selector.setUnselectedGridData( $this.all_columns );
						if ( callBack ) {
							callBack();
						}

					}
				} );

			}
		} );

	},

	initLayout: function() {

		var $this = this;
		$this.getAllLayouts( function() {
			$this.setSelectLayout();

		} );
	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status', '', this.api );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;
				$this.basic_search_field_ui_dic['group_ids'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_ids'].setSourceData( res );

			}
		} );

	},

	getSelectDate: function() {
		return this.start_date_picker.getValue();
	},

	getGridSelectIdArray: function() {

		var result = [];
		var len = this.select_all_shifts_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = this.select_all_shifts_array[i];
			if ( item.id ) {
				result.push( item.id );
			}

		}

		return result;
	},

	getSelectedItem: function() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {

			if ( this.select_all_shifts_array.length > 0 ) {
				selected_item = this.select_all_shifts_array[0];
			}

		}

		if ( selected_item ) {
			return Global.clone( selected_item );
		} else {
			return null;
		}

	},

	onCopyAsNewClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setCurrentEditRecordData(); // Reset data to widgets to reset all widgets stat
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
				var select_shift = Global.clone( $this.select_all_shifts_array[0] );
				select_shift = $this.resetSomeFields( select_shift );
				$this.current_edit_record = select_shift;
				$this.initEditView();
				return;

			}

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( !result_data ) {
						TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
						$this.onCancelClick();
						return;
					}

					$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

					result_data = result_data[0];

					result_data.id = '';

					if ( $this.sub_view_mode && $this.parent_key ) {
						result_data[$this.parent_key] = $this.parent_value;
					}

					$this.current_edit_record = result_data;
					$this.initEditView();

				}
			} );
		}

	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		this.is_viewing = true;
		this.is_edit = false;
		this.is_mass_adding = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {
			if ( this.getMode() === ScheduleViewControllerMode.YEAR ) {
				var select_cell_data = this.select_all_shifts_array[0];

				if ( select_cell_data.length > 1 ) {
					TAlertManager.showAlert( $.i18n._( 'There are more than one Shifts' ) );
					ProgressBar.closeOverlay();
					return
				} else {

					selectedId = select_cell_data[0].id;

					if ( !selectedId ) {
						$this.openEditView();
						var select_shift = Global.clone( select_cell_data[0] );
						select_shift = $this.resetSomeFields( select_shift );
						$this.current_edit_record = select_shift;
						$this.initEditView();
						return;
					}
				}
			} else {
				if ( grid_selected_length > 0 ) {
					selectedId = grid_selected_id_array[0];
				} else {
					$this.openEditView();
					select_shift = Global.clone( $this.select_all_shifts_array[0] );
					select_shift = $this.resetSomeFields( select_shift );
					$this.current_edit_record = select_shift;
					$this.initEditView();
					return;
				}

			}
		}

		$this.openEditView();

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	onMassEditClick: function() {
		var $this = this;

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		LocalCacheData.current_doing_context_action = 'mass_edit';
		this.mass_edit_record_ids = [];

		if ( this.getMode() === ScheduleViewControllerMode.YEAR ) {

			for ( var i = 0; i < this.select_all_shifts_array.length; i++ ) {
				var select_cell_data = this.select_all_shifts_array[i];

				if ( select_cell_data.length > 1 ) {
					TAlertManager.showAlert( $.i18n._( 'There are more than one Shifts in one day' ) );
					ProgressBar.closeOverlay();
					return
				} else {

					if ( select_cell_data[0].id ) {
						grid_selected_id_array.push( select_cell_data[0].id );
					}

				}
			}
			$this.openEditView();
		} else {
			$this.openEditView();
		}

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value )
		} );

		$this.selected_user_ids = [];
		$.each( this.select_all_shifts_array, function( index, value ) {
			var shift = value;
			if ( shift.hasOwnProperty( 'user_id' ) ) {
				$this.selected_user_ids.push( shift.user_id );
			}

		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {

								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.is_mass_editing = true;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		this.is_add = false;
		this.is_edit = true;
		this.is_mass_adding = false;
		LocalCacheData.current_doing_context_action = 'edit';
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {
			if ( this.getMode() === ScheduleViewControllerMode.YEAR ) {

				var select_cell_data = this.select_all_shifts_array[0];

				if ( this.is_viewing ) {
					selectedId = this.current_edit_record.id;
				} else if ( select_cell_data.length > 1 ) {
					TAlertManager.showAlert( $.i18n._( 'There are more than one Shifts' ) );
					ProgressBar.closeOverlay();
					return
				} else {
					selectedId = select_cell_data[0].id;
					this.is_viewing = false;
					if ( !selectedId ) {
						$this.openEditView();
						var select_shift = Global.clone( select_cell_data[0] );
						select_shift = $this.resetSomeFields( select_shift );
						$this.current_edit_record = select_shift;
						$this.initEditView();
						return;
					}
				}

				this.is_viewing = false;
			} else {

				if ( this.is_viewing ) {
					selectedId = this.current_edit_record.id;
				} else if ( grid_selected_length > 0 ) {
					selectedId = grid_selected_id_array[0];
				} else {
					$this.openEditView();
					select_shift = Global.clone( $this.select_all_shifts_array[0] );
					select_shift = $this.resetSomeFields( select_shift );
					$this.current_edit_record = select_shift;
					this.is_viewing = false;
					$this.initEditView();
					return;
				}

				this.is_viewing = false;

			}
		}

		$this.openEditView();
		filter.filter_data = {};
		filter.filter_data.id = [selectedId];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
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

			}
		} );

	},

	onDeleteClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered ' +
		'Are you sure you wish to continue?' ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			} else {

				if ( $this.getMode() === ScheduleViewControllerMode.YEAR ) {

					var len = $this.select_all_shifts_array.length;
					for ( var i = 0; i < len; i++ ) {
						var select_cell_data = $this.select_all_shifts_array[i];

						if ( select_cell_data.length > 1 ) {
							TAlertManager.showAlert( $.i18n._( 'There are more than one Shifts' ) );
							return
						} else {
							if ( select_cell_data[0].id ) {
								remove_ids.push( select_cell_data[0].id );
							}
						}
					}

				} else {
					remove_ids = $this.getGridSelectIdArray();
				}
			}

			if ( result ) {
				ProgressBar.showOverlay();
				$this.api['delete' + $this.api.key_name]( remove_ids, {
					onResult: function( result ) {

						ProgressBar.closeOverlay();

						if ( result.isValid() ) {
							$this.search();
							$this.onDeleteDone( result );
							if ( $this.edit_view ) {
								$this.removeEditView();
							}

						} else {
							TAlertManager.showErrorAlert( result );
						}

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
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
		var drag_and_drop_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Drag & Drop' ),
			id: this.viewId + 'drag_and_drop',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//navigation group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//navigation group
		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var edit = new RibbonSubMenu( {
			label: $.i18n._( 'Edit' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		var mass_edit = new RibbonSubMenu( {
			label: $.i18n._( 'Mass<br>Edit' ),
			id: ContextMenuIconName.mass_edit,
			group: editor_group,
			icon: Icons.mass_edit,
			permission_result: true,
			permission: null
		} );

		var del = new RibbonSubMenu( {
			label: $.i18n._( 'Delete' ),
			id: ContextMenuIconName.delete_icon,
			group: editor_group,
			icon: Icons.delete_icon,
			permission_result: true,
			permission: null
		} );

		var delAndNext = new RibbonSubMenu( {
			label: $.i18n._( 'Delete<br>& Next' ),
			id: ContextMenuIconName.delete_and_next,
			group: editor_group,
			icon: Icons.delete_and_next,
			permission_result: true,
			permission: null
		} );

		var copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.copy,
			group: editor_group,
			icon: Icons.copy_as_new,
			permission_result: true,
			permission: null
		} );

		var copy_as_new = new RibbonSubMenu( {
			label: $.i18n._( 'Copy<br>as New' ),
			id: ContextMenuIconName.copy_as_new,
			group: editor_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var save = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save,
			group: editor_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_and_continue = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Continue' ),
			id: ContextMenuIconName.save_and_continue,
			group: editor_group,
			icon: Icons.save_and_continue,
			permission_result: true,
			permission: null
		} );

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
			permission_result: true,
			permission: null
		} );

		var save_and_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Copy' ),
			id: ContextMenuIconName.save_and_copy,
			group: editor_group,
			icon: Icons.save_and_copy,
			permission_result: true,
			permission: null
		} );

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
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

		var move = new RibbonSubMenu( {
			label: $.i18n._( 'Move' ),
			id: ContextMenuIconName.move,
			group: drag_and_drop_group,
			icon: Icons.move,
			permission_result: true,
			permission: null
		} );

		var copy_drag = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.drag_copy,
			group: drag_and_drop_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var swap = new RibbonSubMenu( {
			label: $.i18n._( 'Swap' ),
			id: ContextMenuIconName.swap,
			group: drag_and_drop_group,
			icon: Icons.swap,
			permission_result: true,
			permission: null
		} );

		var override = new RibbonSubMenu( {
			label: $.i18n._( 'Overwrite' ),
			id: ContextMenuIconName.override,
			group: drag_and_drop_group,
			icon: Icons.override,
			permission_result: true,
			permission: null
		} );

		var edit_employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		var timesheet_view = new RibbonSubMenu( {
			label: $.i18n._( 'Timesheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var find_available = new RibbonSubMenu( {
			label: $.i18n._( 'Find Available' ),
			id: ContextMenuIconName.find_available,
			group: other_group,
			icon: Icons.find_available,
			permission_result: true,
			permission: null
		} );

		var print = new RibbonSubMenu( {
			label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print,
			group: other_group,
			icon: Icons.print,
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var individual_schedules = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Individual Schedules' ),
			id: 'pdf_schedule',
			nav: print
		} );

		if ( PermissionManager.validate( 'schedule', 'view' ) ||
			PermissionManager.validate( 'schedule', 'view_child' ) ) {

			var group_combined = new RibbonSubMenuNavItem( {
				label: $.i18n._( 'Group - Combined' ),
				id: 'pdf_schedule_group_combined',
				nav: print
			} );

			var group_separated = new RibbonSubMenuNavItem( {
				label: $.i18n._( 'Group - Separated' ),
				id: 'pdf_schedule_group',
				nav: print
			} );

			var group_separated_page_breaks = new RibbonSubMenuNavItem( {
				label: $.i18n._( 'Group - Separated (Page Breaks)' ),
				id: 'pdf_schedule_group_pagebreak',
				nav: print
			} );

		}

		return [menu];

	},

	onReportMenuClick: function( id ) {
		this.onNavigationClick( id );
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		if ( !this.checkScheduleData() ) {
			return;
		}

		if ( Global.isSet( menu_name ) ) {
			var id = menu_name;
		} else {
			context_btn = $( context_btn );
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.add:
				this.absence_model = false;
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_new:
				ProgressBar.showOverlay();
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				ProgressBar.showOverlay();
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.mass_edit:
				ProgressBar.showOverlay();
				this.onMassEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick()
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick()
				break;

			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
				break;
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick()
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.move:
			case ContextMenuIconName.drag_copy:
			case ContextMenuIconName.swap:
				this.setMoveOrDropMode( id );
				break;
			case ContextMenuIconName.override:
				this.onOverrideClick();
				break;
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.timesheet:
				this.onNavigationClick( id );
				break;
			case ContextMenuIconName.find_available:
				this.onFindAvailableClick( id );
				break;
		}
	},

	getSelectEmployee: function() {
		var shift = this.select_all_shifts_array[0];

		//Error: Uncaught TypeError: Cannot read property 'user_id' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Schedule&date=20141117&mode=week&a=new&tab=Schedule line 1116
		if ( !shift || shift.user_id == 0 ) {
			shift = {user_id: LocalCacheData.getLoginUser().id};
		} else if ( shift.user_id && shift.user_id != 0 ) {
			shift = {user_id: shift.user_id};
		}

		if ( this.edit_view ) {
			shift.user_id = this.current_edit_record.user_id;
		}

		return shift.user_id;
	},

	onFindAvailableClick: function() {
		var $this = this;
		var args = {};
		args.selected = [];
		var len = this.select_all_shifts_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = this.select_all_shifts_array[i];
			var new_item = {};
			new_item.id = item.id;
			new_item.date_stamp = item.date_stamp;
			new_item.user_id = item.user_id;
			new_item.start_time = item.start_time;
			new_item.end_time = item.end_time;
			new_item.branch_id = item.branch_id;
			new_item.department_id = item.department_id;
			new_item.job_id = item.job_id;
			new_item.job_item_id = item.job_item_id;
			new_item.group_id = item.group_id;
			new_item.title_id = item.title_id;
			new_item.start_date_stamp = item.start_date_stamp;
			args.selected.push( new_item );
		}

		LocalCacheData.extra_filter_for_next_open_view = {};
		LocalCacheData.extra_filter_for_next_open_view.filter_data = args;

		IndexViewController.openWizard( 'FindAvailableWizard', null, function( employee_id ) {
			$this.onFindAvailableClose( employee_id, args.selected );
		} );
	},

	onFindAvailableClose: function( employee_id, shift_array ) {
		var $this = this;
		var len = shift_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = shift_array[i];
			item.user_id = employee_id;

		}

		this.api.setSchedule( shift_array, {
			onResult: function( result ) {
				if ( !result.isValid() ) {
					TAlertManager.showErrorAlert( result );
				}
				$this.search();
			}
		} );
	},

	onNavigationClick: function( iconName ) {

		if ( !this.checkScheduleData() ) {
			return;
		}

		var post_data;

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				IndexViewController.openEditView( this, 'Employee', this.getSelectEmployee() );
				break;
			case ContextMenuIconName.timesheet:
				var filter = {filter_data: {}};
				filter.user_id = this.getSelectEmployee();

				if ( this.edit_view ) {
					filter.base_date = this.current_edit_record.date_stamp;
				} else {
					filter.base_date = this.start_date_picker.getValue();
				}
				Global.addViewTab( this.viewId, 'Schedules', window.location.href );
				IndexViewController.goToView( 'TimeSheet', filter );

				break;
			case 'pdf_schedule':
			case 'pdf_schedule_group_combined':
			case 'pdf_schedule_group':
			case 'pdf_schedule_group_pagebreak':
				filter = Global.convertLayoutFilterToAPIFilter( this.select_layout );

				if ( !filter ) {
					filter = {};
				}
				filter.time_period = {};
				filter.time_period.time_period = 'custom_date';
				filter.time_period.start_date = this.full_schedule_data.schedule_dates.start_display_date;
				filter.time_period.end_date = this.full_schedule_data.schedule_dates.end_display_date;

				if ( filter.time_period.start_date == filter.time_period.end_date ) {
					var new_end_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + 6 ) );
					filter.time_period.end_date = new_end_date.format();
				}

				post_data = {0: filter, 1: iconName};
				this.doFormIFrameCall( post_data );
				break;

		}
	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=APIScheduleSummaryReport&Method=getScheduleSummaryReport' );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		this.sendFormIFrameCall( postData, url, message_id );

	},

	setScheduleGridDragAble: function() {
		var mode = this.getMode();

		switch ( mode ) {
			case ScheduleViewControllerMode.DAY:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.WEEK:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.YEAR:

				break;
		}

		var $this = this;
		//set bottom drag to scroll area
		var grid_div = $( '#gbox_' + this.ui_id + '_grid' );
		$( this.el ).unbind( 'dragover' ).bind( 'dragover', function( e ) {

			var grid_pos = grid_div.offset().top;

			var mouse_y = e.originalEvent.clientY;
			var grid_height = grid_div.height();

			if ( mouse_y > (grid_pos + grid_height) ) {
				$this.scroll_unit = mouse_y - (grid_pos + grid_height);
				if ( !$this.scroll_interval ) {
					$this.scroll_interval = setInterval( function() {
						var div = $this.grid.parent().parent();
						div.scrollTop( div.scrollTop() + $this.scroll_unit );
					}, 50 );
				}
			} else if ( mouse_y < (grid_pos + 15) && mouse_y > (grid_pos - 50) ) {
				$this.scroll_unit = (grid_pos + 15) - mouse_y;
				if ( !$this.scroll_interval ) {
					$this.scroll_interval = setInterval( function() {
						var div = $this.grid.parent().parent();
						div.scrollTop( div.scrollTop() - $this.scroll_unit );
					}, 50 );
				}
			} else {
				clearInterval( $this.scroll_interval );
				$this.scroll_interval = null;
			}

		} );

		$( this.el ).unbind( 'dragend' ).bind( 'dragend', function( e ) {

			if ( $this.scroll_interval ) {
				clearInterval( $this.scroll_interval );
				$this.scroll_interval = null;
			}

		} );

	},

	setFocusToFirstInput: function() {
		if ( !this.is_viewing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}
				var widget = this.edit_view_ui_dic[key];

				if ( widget.hasClass( 't-text-input' ) && widget.is( ':visible' ) === true && !widget.attr( 'readonly' ) ) {
					widget.focus();
					widget[0].select();

					break;
				}

			}
		}
	},

	setWeekModeDragAble: function() {
		var $this = this;
		var position = 0;

		var cells = this.grid.find( "td[role='gridcell']" );

		cells.attr( 'draggable', true );

		if ( ie <= 9 ) {
			cells.bind( 'selectstart', function( event ) {
				this.dragDrop();
				return false;
			} );
		}

		cells.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
			var td = event.target;
			if ( $this.select_all_shifts_array.length < 1 || !$( td ).hasClass( "ui-state-highlight" ) || !$this.select_drag_menu_id ) {
				return false;
			}

			var container = $( "<div class='drag-holder-div'></div>" );

			var len = $this.select_all_shifts_array.length;

			for ( var i = 0; i < len; i++ ) {
				var shift = $this.select_all_shifts_array[i];

				var span = $( "<span class='drag-span'></span>" );

				if ( shift.status_id === 20 ) {
					span.text( $this.getAbsenceCellValue( shift ) );
				} else {
					span.text( shift.start_time + ' - ' + shift.end_time );
				}

				container.append( span );
			}

			$( 'body' ).find( '.drag-holder-div' ).remove();

			$( 'body' ).append( container );

			event.originalEvent.dataTransfer.setData( 'Text', 'schedule' );//JUST ELEMENT references is ok here NO ID

			if ( event.originalEvent.dataTransfer.setDragImage ) {
				event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
			}

			return true;
		} );

		cells.unbind( 'drop' ).bind( 'drop', function( event ) {
			event.preventDefault();
			if ( event.stopPropagation ) {
				event.stopPropagation(); // stops the browser from redirecting.
			}

			var delete_old_items = false;

			var new_shifts_array = [];
			var delete_shifts_array = [];
			var recurring_delete_shifts_array = [];

			var target_cell = event.currentTarget;

			var selected_shifts = $this.select_all_shifts_array;
			var first_target_row_index;
			var first_target_cell_index;

			first_target_row_index = target_cell.parentNode.rowIndex - 1;
			first_target_cell_index = target_cell.cellIndex;

			var row_index_offset = 0;
			var cell_index_offset = 0;

			var first_selected_row_index;
			var first_selected_cell_index;

			var colModel = $this.grid.getGridParam( 'colModel' );

			if ( $this.select_drag_menu_id === ContextMenuIconName.move ) {
				delete_old_items = true;
			} else {
				delete_old_items = false;
			}

			var len = selected_shifts.length;

			for ( var i = 0; i < len; i++ ) {
				var shift = selected_shifts[i];

				shift.branch_id = shift.branch ? shift.branch_id : '';
				shift.department_id = shift.department ? shift.department_id : '';
				shift.job_id = shift.job_id ? shift.job_id : '';
				shift.job_item_id = shift.job_item_id ? shift.job_item_id : '';

				if ( i === 0 ) {
					first_selected_row_index = shift.row_index;
					first_selected_cell_index = shift.cell_index;
				} else {
					row_index_offset = shift.row_index - first_selected_row_index;
					cell_index_offset = shift.cell_index - first_selected_cell_index
				}

				var target_row_index = first_target_row_index + row_index_offset;
				var target_cell_index = first_target_cell_index + cell_index_offset;

				if ( target_cell_index > colModel.length - 1 ) {
					continue
				}

				var target_data = $this.getDataByCellIndex( target_row_index, target_cell_index );
				var target_row = $this.schedule_source[target_row_index];

				if ( target_row ) {

					if ( target_row.type === ScheduleViewControllerRowType.DATE ) {
						break;
					}

					if ( !target_data ) {
						var date_stamp;

						//Error: TypeError: colModel[target_cell_index] is undefined in https://ondemand2001.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141230-153210 line 2 > eval line 1443
						if ( colModel ) {
							if ( $this.getMode() === ScheduleViewControllerMode.MONTH ) {
								date_stamp = $this.getCellRelatedDate( target_row_index, colModel, target_cell_index, colModel[target_cell_index].name ).format();
							} else {
								date_stamp = Global.strToDate( colModel[target_cell_index].name, $this.full_format ).format();
							}
						}

						if ( !date_stamp ) {
							continue;
						}
						target_data = {};

						if ( !target_row.user_id ) { //Only happens in month mode;
							target_data = shift;
							target_data.date_stamp = date_stamp;
							target_data.start_date_stamp = date_stamp;
						} else {
							target_data.user_id = target_row.user_id;
							target_data.branch = target_row.branch;
							target_data.branch_id = target_row.branch ? target_row.branch_id : '';
							target_data.schedule_policy_id = target_row.schedule_policy_id;
							target_data.department_id = target_row.department ? target_row.department_id : '';
							target_data.department = target_row.department;
							target_data.job_id = target_row.job_id ? target_row.job_id : '';
							target_data.job = target_row.job;
							target_data.job_item_id = target_row.job_item_id ? target_row.job_item_id : '';
							target_data.job_item = target_row.job_item;
							target_data.date_stamp = date_stamp;
							target_data.start_date_stamp = date_stamp;
						}

					}

				} else {
					continue;
				}

				var new_shift = Global.clone( shift );

				if ( $this.select_drag_menu_id !== ContextMenuIconName.swap ) {
					new_shift.id = '';
					new_shift.date_stamp = target_data.date_stamp;
					new_shift.start_date_stamp = target_data.start_date_stamp;
					new_shift.user_id = target_data.user_id;
					new_shift.branch_id = target_data.branch ? target_data.branch_id : '';
					new_shift.schedule_policy_id = target_data.schedule_policy_id;
					new_shift.department_id = target_data.department ? target_data.department_id : '';
					new_shift.job_id = target_data.job_id ? target_data.job_id : '';
					new_shift.job_item_id = target_data.job_item_id ? target_data.job_item_id : '';

					if ( $this.is_override ) {
						new_shift.overwrite = true;
					}

					new_shifts_array.push( new_shift );

					if ( shift.id ) {
						delete_shifts_array.push( shift.id );
					} else {
						shift.status_id = '20';
						recurring_delete_shifts_array.push( shift );
					}

				} else {
					var temp_selected_data = Global.clone( new_shift );
					var temp_target_data = Global.clone( target_data );

					if ( !temp_target_data.start_date ) {
						continue;
					}

					for ( var key in target_data ) {
						if ( key !== 'id' &&
							key !== 'user_id' &&
							key !== 'date_stamp' &&
							key !== 'start_date_stamp' &&
							key !== 'branch_id' &&
							key !== 'department_id' &&
							key !== 'job_id' &&
							key !== 'job_item_id' &&
							key !== 'branch' &&
							key !== 'department' &&
							key !== 'job' &&
							key !== 'job_item' &&
							key !== 'schedule_policy_id' ) {

							target_data[key] = temp_selected_data[key];
							new_shift[key] = temp_target_data[key];
						}
					}

					target_data.branch_id = target_data.branch ? target_data.branch_id : '';
					target_data.department_id = target_data.department ? target_data.department_id : '';
					target_data.job_id = target_data.job_id ? target_data.job_id : '';
					target_data.job_item_id = target_data.job_item_id ? target_data.job_item_id : '';

					new_shifts_array.push( target_data );
					new_shifts_array.push( new_shift );
				}

			}

			if ( new_shifts_array.length > 0 ) {
				$this.api.setSchedule( new_shifts_array, {
					onResult: function( res ) {

						if ( res.isValid() ) {

							if ( delete_old_items ) {
								if ( delete_shifts_array.length > 0 ) {

									$this.api.deleteSchedule( delete_shifts_array, {
										onResult: function() {
											if ( recurring_delete_shifts_array.length > 0 ) {
												$this.api.setSchedule( recurring_delete_shifts_array, {
													onResult: function() {
														$this.search();
													}
												} );
											} else {
												$this.search();
											}
										}
									} );
								} else if ( recurring_delete_shifts_array.length > 0 ) {
									$this.api.setSchedule( recurring_delete_shifts_array, {
										onResult: function() {
											$this.search();
										}
									} );
								}
							} else {
								$this.search();
							}

						} else {
							TAlertManager.showErrorAlert( res )
						}

					}
				} );
			}

		} );

		cells.unbind( 'dragenter' ).bind( 'dragenter', function( event ) {
			event.preventDefault();
			var $this = this;
			var target = $( this );

			$( '.schedule-drag-over' ).removeClass( 'schedule-drag-over' );

			$( $this ).addClass( 'schedule-drag-over' )

		} );

		cells.unbind( 'dragover' ).bind( 'dragover', function( event ) {
			event.preventDefault(); //Must prevent tihs

		} );

		cells.unbind( 'dragend' ).bind( 'dragend', function( event ) {

			$( '.schedule-drag-over' ).removeClass( 'schedule-drag-over' );

		} );

	},

	resetSomeFields: function( item ) {
		item.branch_id = item.branch ? item.branch_id : '';
		item.department_id = item.department ? item.department_id : '';
		item.job_id = item.job ? item.job_id : '';
		item.job_item_id = item.job_item ? item.job_item_id : '';

		return item;
	},

	onAddClick: function( doing_save_and_new ) {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_mass_adding = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';

		if ( this.select_cells_Array.length > 1 ) {
			this.is_mass_adding = true;
		}

		$this.openEditView();

		var shift = this.select_all_shifts_array[0];

		if ( !shift ) {
			shift = {user_id: LocalCacheData.getLoginUser().id};

			if ( this.select_cells_Array.length > 0 && this.select_cells_Array[0].hasOwnProperty( 'user_id' ) ) {
				shift.user_id = this.select_cells_Array[0].user_id;
			}

		} else {
			if ( this.getMode() === ScheduleViewControllerMode.YEAR ) {
				if ( shift.length > 0 ) {
					shift = shift[0];
				}
			}
		}

		if ( !doing_save_and_new ) {
			var user_id = shift.user_id;
			var date_str = this.getSelectDate();
		} else {
			user_id = this.current_edit_record.user_id;
			date_str = this.current_edit_record.date_stamp;
		}

		this.api['get' + this.api.key_name + 'DefaultData']( user_id, date_str, {
			onResult: function( result ) {

				var result_data = result.getResult();

				//OPEN option
				if ( user_id === 0 ) {
					result_data.user_id = 0;
				}

				if ( $this.select_all_shifts_array.length >= 1 && !doing_save_and_new ) {

					var select_shift;
					if ( $this.getMode() === ScheduleViewControllerMode.YEAR ) {
						select_shift = Global.clone( $this.select_all_shifts_array[0][0] );
					} else {
						select_shift = Global.clone( $this.select_all_shifts_array[0] );
					}

					select_shift = $this.resetSomeFields( select_shift );
					select_shift.absence_policy_id = false; // don't use select item's value;
					select_shift.total_time = false; // don't use select item's value;

					for ( var key in result_data ) {
						if ( key === 'branch_id' && !select_shift.branch_id ) {
							select_shift[key] = result_data[key];
						} else if ( key === 'department_id' && !select_shift.department_id ) {
							select_shift[key] = result_data[key];
						} else if ( key !== 'department_id' && key !== 'branch_id' ) {
							select_shift[key] = result_data[key];
						}
					}

				} else if ( $this.select_all_shifts_array.length === 0 || doing_save_and_new ) {
					select_shift = result_data;

				}

				if ( !doing_save_and_new ) {
					select_shift.date_stamp = $this.getSelectDate();

				} else {
					var temp_date = Global.strToDate( $this.current_edit_record.date_stamp );
					select_shift.date_stamp = new Date( new Date( temp_date.getTime() ).setDate( temp_date.getDate() + 1 ) ).format();

				}

				if ( !select_shift.start_date_stamp ) {
					select_shift.start_date_stamp = select_shift.date_stamp
				}

				select_shift.id = '';

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = select_shift;
				$this.initEditView();

			}
		} );
	},

	openEditView: function() {

		if ( !this.edit_view ) {
			this.initEditViewUI( 'Schedule', 'ScheduleEditView.html' );
		}

		this.setEditViewWidgetsMode();

	},

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {

		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );

			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {

				if ( !did_clean_dic[tab_id] ) {
					column.find( '.edit-view-form-item-label-div-first-row' ).removeClass( 'edit-view-form-item-label-div-first-row' );
					column.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
					column.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
					did_clean_dic[tab_id] = true;
				}

				var child_length = column.children().length;
				var parent_div = widget.parent().parent();

				if ( child_length === 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				} else if ( parent_div.index() === 0 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-first-row' );
				} else if ( parent_div.index() === child_length - 2 ) {
					parent_div.children().eq( 0 ).addClass( 'edit-view-form-item-label-div-last-row' );
					parent_div.addClass( 'edit-view-form-item-div-last-row' );
				}

				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}

			widget.setValue( '' ); // Set all value back to empty before new value coming.

			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {

					widget.setEnabled( true );

				}
			}

		}

	},

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ) );
				}
				break;
			case 'job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.onJobQuickSearch( key, c_value )
				}
				break;
			case 'status_id':
				this.onTypeChange( true );
				break;
			case 'user_id':
			case 'user_ids':
				this.setEditMenu();
				this.setAbsencePolicyWhenUserChanged();
				return;
				break;
			case 'start_date_stamps':
				this.setEditMenu();
				this.current_edit_record['start_date_stamp'] = c_value;
				break;

		}

		if ( key === 'date_stamp' ||
			key === 'start_date_stamps' ||
			key === 'start_date_stamp' ||
			key === 'start_time' ||
			key === 'end_time' ||
			key === 'schedule_policy_id' ||
			key === 'absence_policy_id' ) {

			if ( this.current_edit_record['date_stamp'] !== '' &&
				this.current_edit_record['start_time'] !== '' &&
				this.current_edit_record['end_time'] !== '' ) {

				var startTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'];
				var endTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'];
				var schedulePolicyId = this.current_edit_record['schedule_policy_id'];
				var user_id = this.current_edit_record.user_id;

				this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, {
					onResult: function( total_time ) {

						//Uncaught TypeError: Cannot set property 'total_time' of null
						if ( !$this.edit_view || !$this.current_edit_record ) {
							return;
						}

						//Fixed exception that total_time is null
						if ( total_time ) {
							total_time = total_time.getResult();
						} else {
							total_time = $this.current_edit_record.total_time ? $this.current_edit_record.total_time : 0;
						}

						$this.current_edit_record.total_time = total_time;
						total_time = Global.secondToHHMMSS( total_time );
						$this.edit_view_ui_dic['total_time'].setValue( total_time );

						$this.onAvailableBalanceChange();

					}
				} );
//

			} else {
				this.onAvailableBalanceChange();
			}

		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	setAbsencePolicyWhenUserChanged: function() {

		var $this = this;
		var absence_widget = $this.edit_view_ui_dic['absence_policy_id'];
		absence_widget.setSourceData( null );
		var old_value = absence_widget.getValue();

		var args = {};
		args.filter_data = {id: old_value};

		args = this.setAbsencePolicyFilter( args );

		if ( old_value ) {

			$this.absence_policy_api.getAbsencePolicy( args, {
				onResult: function( task_result ) {
					var data = task_result.getResult();

					if ( data.length > 0 ) {
						absence_widget.setValue( old_value );
						$this.current_edit_record.absence_policy_id = old_value;
					} else {
						absence_widget.setValue( false );
						$this.current_edit_record.absence_policy_id = false;
					}
					$this.onAvailableBalanceChange();
					$this.validate();

				}
			} )
		} else {
			this.onAvailableBalanceChange();
			this.validate();
		}
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {

		var $this = this;
		var job_item_widget = $this.edit_view_ui_dic['job_item_id'];
		var current_job_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		var args = {};
		args.filter_data = {status_id: 10, job_id: $this.current_edit_record.job_id};
		$this.edit_view_ui_dic['job_item_id'].setDefaultArgs( args );

		if ( current_job_item_id ) {

			var new_arg = Global.clone( args );

			new_arg.filter_data.id = current_job_item_id;
			new_arg.filter_columns = $this.edit_view_ui_dic['job_item_id'].getColumnFilter();
			$this.job_item_api.getJobItem( new_arg, {
				onResult: function( task_result ) {
					var data = task_result.getResult();

					if ( data.length > 0 ) {
						job_item_widget.setValue( current_job_item_id );
						$this.current_edit_record.job_item_id = current_job_item_id;
					} else {
						setDefaultData();
					}

				}
			} )

		} else {
			setDefaultData();
		}

		function setDefaultData() {
			if ( $this.current_edit_record.job_id ) {
				job_item_widget.setValue( job.default_item_id );
				$this.current_edit_record.job_item_id = job.default_item_id;

				if ( job.default_item_id === false || job.default_item_id === 0 ) {
					$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
				}

			} else {
				job_item_widget.setValue( '' );
				$this.current_edit_record.job_item_id = false;
				$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );

			}
		}
	},

	//Make sure this.current_edit_record is updated before validate
	validate: function() {
		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {

			var checkFields = {};

			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						checkFields[key] = widget.getValue();
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var commonRecord = Global.clone( checkFields );
				commonRecord.id = value;
				record.push( commonRecord );

			} );

			$.each( this.select_all_shifts_array, function( index, value ) {

				if ( $this.getMode() === ScheduleViewControllerMode.YEAR ) {
					value = value[0];
				}

				if ( !value.id ) {
					var commonRecord = Global.clone( value );

					for ( var key in checkFields ) {
						commonRecord[key] = checkFields[key];
					}

					record.push( commonRecord );

				}

			} );

		} else {
			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
				$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

				if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
					this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
				}

				record = [];
				for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
					var commonRecord = Global.clone( $this.current_edit_record );
					commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];

					record.push( commonRecord );
				}

				record = this.getRecordsFromUserIDs( record );

			} else {
				record = Global.clone( this.current_edit_record );

				record = this.getRecordsFromUserIDs( [record] );

			}

		}

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {

				$this.validateResult( result );

			}
		} );
	},

	getRecordsFromUserIDs: function( record ) {
		var result = [];

		for ( var j = 0; j < record.length; j++ ) {
			var common_record = record[j];

			if ( common_record.user_ids && common_record.user_ids.length > 0 ) {
				for ( var y = 0; y < common_record.user_ids.length; y++ ) {
					var user_id = common_record.user_ids[y];
					var new_common_record = Global.clone( common_record );
					new_common_record.user_id = user_id;
					result.push( new_common_record );

				}
			} else {

				if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
					common_record.user_id = 0;
				}

				result.push( common_record );

			}

		}

		return result;
	},

	onSaveAndCopy: function() {
		var $this = this;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		record = this.getRecordsFromUserIDs( [record] );

		if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];

				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		}

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {

				var current_date_str = $this.current_edit_record.start_date_stamp;

				if ( $.type( current_date_str ) === 'array' ) {
					current_date_str = current_date_str[current_date_str.length - 1];
				}

				var current_date = Global.strToDate( current_date_str );
				var next_date = new Date( new Date( current_date.getTime() ).setDate( current_date.getDate() + 1 ) );

				$this.current_edit_record.start_date_stamp = next_date.format();

				$this.onSaveAndCopyResult( result );

			}
		} );
	},

	onSaveAndNewClick: function() {
		var $this = this;
		this.is_add = true;
		var record = this.current_edit_record;
		record = this.getRecordsFromUserIDs( [record] );
		LocalCacheData.current_doing_context_action = 'new';

		if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];

				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		}

		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
	},

	onSaveAndContinue: function() {
		var $this = this;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );

		if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];

				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		}

		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	},

	onSaveClick: function() {

		var $this = this;
		var record;
		LocalCacheData.current_doing_context_action = 'save';

		if ( this.is_mass_adding ) {
			record = [];
			$.each( this.select_cells_Array, function( index, value ) {
				if ( value.hasOwnProperty( 'user_id' ) && value.hasOwnProperty( 'date' ) && value.date ) {
					var commonRecord = Global.clone( $this.current_edit_record );
					delete commonRecord.user_ids;
					delete commonRecord.start_dates;
					commonRecord.id = '';
					commonRecord.user_id = value.user_id;
					commonRecord.start_date_stamp = value.date;

					record.push( commonRecord );
				}

			} );

		} else if ( this.is_mass_editing ) {

			var checkFields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						checkFields[key] = widget.getValue();
					}
				}
			}

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {
				var commonRecord = Global.clone( checkFields );
				commonRecord.id = value;
				record.push( commonRecord );

			} );

			$.each( this.select_all_shifts_array, function( index, value ) {

				if ( $this.getMode() === ScheduleViewControllerMode.YEAR ) {
					value = value[0];
				}

				if ( !value.id ) {
					var commonRecord = Global.clone( value );

					for ( var key in checkFields ) {
						commonRecord[key] = checkFields[key];
					}

					record.push( commonRecord );

				}

			} );

		} else if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];

				record.push( commonRecord );
			}

			record = this.getRecordsFromUserIDs( record );

		} else {

			record = this.current_edit_record;
			record = this.getRecordsFromUserIDs( [record] );

		}

		this.api['set' + this.api.key_name]( record, {
			onResult: function( result ) {

				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;
					} else if ( result_data > 0 ) {
						$this.refresh_id = result_data
					}
					$this.search();
					$this.current_edit_record = null;
					$this.removeEditView();

				} else {

					if ( $this.is_mass_adding || ($.type( record ) === 'array' && record.length > 1) ) {

						TAlertManager.showErrorAlert( result );
						$this.search();
						$this.current_edit_record = null;
						$this.removeEditView();

					} else {
						$this.setErrorTips( result );
						$this.setErrorMenu();
					}
				}

			}
		} );
	},

	removeEditView: function() {
		this._super( 'removeEditView' );

		this.selected_user_ids = [];
		this.is_mass_adding = false;
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn );

		if ( this.is_mass_editing || this.is_viewing || this.is_mass_adding || this.isMassEmployeeOrDate() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	isMassEmployeeOrDate: function() {

		if ( this.current_edit_record.start_date_stamps && (this.current_edit_record.start_date_stamps.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamps ) === 'array' && this.current_edit_record.start_date_stamps.length > 1
			) ) {
			return true;
		}

		if ( this.current_edit_record.user_ids && this.current_edit_record.user_ids.length > 1 ) {
			return true;
		}

		return false;
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn );

		if ( this.is_viewing || this.is_mass_editing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndCopyValidate( context_btn );

		if ( this.is_viewing || this.is_mass_editing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}
	},

//	  setEditMenu: function() {
//		  this._super( 'setEditMenu' );
//
//		  var len = this.context_menu_array.length;
//
//		  for ( var i = 0; i < len; i++ ) {
//			  var contextBtn = this.context_menu_array[i];
//			  var id = $( contextBtn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
//
//			  switch ( id ) {
//
//				  case ContextMenuIconName.save_and_continue:
//
//					  this.saveAndContinueValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//				  case ContextMenuIconName.save_and_new:
//
//					  this.saveAndNewValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//
//				  case ContextMenuIconName.save_and_copy:
//
//					  this.saveAndContinueValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//
//			  }
//
//		  }
//	  },

//	  setEditingMenu: function() {
//
//		  this._super( 'setEditingMenu' ); //Set Navigation
//
//		  var len = this.context_menu_array.length;
//
//		  for ( var i = 0; i < len; i++ ) {
//			  var contextBtn = this.context_menu_array[i];
//			  var id = $( contextBtn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
//
//			  switch ( id ) {
//
//				  case ContextMenuIconName.save_and_continue:
//
//					  this.saveAndContinueValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//				  case ContextMenuIconName.save_and_new:
//
//					  this.saveAndNewValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//
//				  case ContextMenuIconName.save_and_copy:
//
//					  this.saveAndContinueValidate( contextBtn );
//
//					  if ( !this.current_edit_record || !this.current_edit_record.id ) {
//						  contextBtn.addClass( 'disable-image' );
//					  }
//					  break;
//
//			  }
//
//		  }
//	  },

	onTypeChange: function( getRate ) {

		if ( parseInt( this.current_edit_record.status_id ) === 20 ) {
			this.edit_view_form_item_dic['absence_policy_id'].css( 'display', 'block' );

		} else {
			this.edit_view_form_item_dic['absence_policy_id'].css( 'display', 'none' );

		}
	},

	setEditViewData: function() {

		var $this = this;
		this._super( 'setEditViewData' ); //Set Navigation
		$this.onTypeChange( false );

	},

	checkOpenPermission: function() {
		if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 && PermissionManager.validate( 'schedule', 'view_open' ) ) {
			return true
		}

		return false;
	},

	buildEditViewUI: function() {

		var $this = this;

		var form_item_input;
		var widgetContainer;

		var close_icon = this.edit_view.find( '.close-icon' );

		close_icon.click( function() {
			$this.onCloseIconClick();
		} );

		this.setTabLabels( {
			'tab_schedule': $.i18n._( 'Schedule' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

//		  this.edit_view_tab.css( 'width', '900' );

		//Tab 0 start

		var tab_schedule = this.edit_view_tab.find( '#tab_schedule' );

		var tab_schedule_column1 = tab_schedule.find( '.first-column' );

		//Employee

		var production_edition_id = LocalCacheData.getCurrentCompany().product_edition_id;
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: !this.checkOpenPermission(),
			set_open: this.checkOpenPermission(),
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '', null, true );

		//Mass Add Employees

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: !this.checkOpenPermission(),
			set_open: this.checkOpenPermission(),
			addition_source_function: (function( target, source_data ) {
				return $this.onEmployeeSourceCreate( target, source_data );
			}),
			field: 'user_ids'
		} );

		default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '', null, true );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_column1 );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date_stamp'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.date_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		//Dates
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( {field: 'start_date_stamps'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.date_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.OPTION_COLUMN,
			show_search_inputs: false,
			set_empty: true,
			field: 'start_dates'
		} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null, true );

		//Start Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'start_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.time_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		//End Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'end_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.time_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		//Total
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'total_time'} );
		form_item_input.css( 'cursor', 'pointer' );
		this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_schedule_column1 );

		//Schedule Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APISchedulePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.SCHEDULE_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'schedule_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Policy' ), form_item_input, tab_schedule_column1 );

		//Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy_id'
		} );

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_schedule_column1, '', null, true );

		//Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'available_balance'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.available_balance_info = $( '<img class="available-balance-info" src="' + Global.getRealImagePath( 'images/infox16x16.png' ) + '">' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_schedule_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.edit_view_form_item_dic.branch_id.hide();
		}

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_schedule_column1, '', null, true );

		if ( !this.show_department_ui ) {
			this.edit_view_form_item_dic.department_id.hide();
		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			//Job
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJob' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) job_coder.setValue( val.manual_id );
				}),
				field: 'job_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_schedule_column1, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.edit_view_form_item_dic.job_id.hide();
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) job_item_coder.setValue( val.manual_id );
				}),
				field: 'job_item_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' )

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_schedule_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.edit_view_form_item_dic.job_item_id.hide();
			}
		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {field: 'note', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_schedule_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.select_shifts_array.length > 0 && this.deleteOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	onAvailableBalanceChange: function() {

		if ( this.current_edit_record.hasOwnProperty( 'absence_policy_id' ) &&
			this.current_edit_record.absence_policy_id && !this.is_mass_editing ) {
			this.getAvailableBalance();
		} else {
			this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
		}
	},

	parserDatesRange: function( date ) {
		var dates = date.split( " - " );
		var resultArray = [];
		var beginDate = Global.strToDate( dates[0] );
		var endDate = Global.strToDate( dates[1] );

		var nextDate = beginDate;

		while ( nextDate.getTime() < endDate.getTime() ) {
			resultArray.push( nextDate.format() );
			nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
		}

		resultArray.push( dates[1] );

		return resultArray;
	},

	getSelectUsersArray: function() {

		var result = [];

		var cells_array = this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];
			date_dic[item.user_id] = true;
		}

		for ( var key in date_dic ) {
			result.push( key );
		}

		return result;
	},

	getSelectDateArray: function() {
		var result = [];

		var cells_array = this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];

			if ( item.date ) {
				date_dic[item.date] = true;
			}

		}

		for ( var key in date_dic ) {
			result.push( key )
		}

		return result;

	},

	getAvailableBalance: function() {
		var $this = this;
		var result;
		var result_data;

		var user_id = this.current_edit_record.user_id;
		var total_time = this.current_edit_record.total_time;
		var last_date_stamp = this.current_edit_record.start_date_stamp;
		this.edit_view_form_item_dic['available_balance'].css( 'display', 'block' );

		//For mass adding case, select multiple cells and click new
		if ( this.is_mass_adding ) {

			if ( this.current_edit_record.user_ids.length > 1 ) {
				this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
				return;
			} else {
				user_id = this.current_edit_record.user_ids[0];
				if ( !user_id ) {
					this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
					return;
				}
			}

			total_time = total_time * this.current_edit_record.start_dates.length;
			last_date_stamp = this.current_edit_record.start_dates[this.current_edit_record.start_dates.length - 1];

		} else {
			//get dates from date ranger
			if ( last_date_stamp.indexOf( ' - ' ) > 0 ||
				$.type( last_date_stamp ) === 'array' ) {

				if ( last_date_stamp.indexOf( ' - ' ) > 0 ) {
					last_date_stamp = this.parserDatesRange( last_date_stamp );
				}

				if ( last_date_stamp.length > 0 ) {
					total_time = total_time * last_date_stamp.length;
					last_date_stamp = last_date_stamp[last_date_stamp.length - 1];
				}

			}

			if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {

				if ( this.current_edit_record.user_ids.length < 1 || this.current_edit_record.user_ids.length > 1 ) {
					this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
					return;
				} else {
					user_id = this.current_edit_record.user_ids[0];
					if ( !user_id ) {
						this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
						return;
					}
				}
			}
		}

		this.api_absence_policy.getProjectedAbsencePolicyBalance(
			this.current_edit_record.absence_policy_id,
			user_id,
			last_date_stamp,
			total_time,
			this.pre_total_time, {
				onResult: function( result ) {
					getBalanceHandler( result );
				}
			}
		);

		function getBalanceHandler( result ) {

			if ( !$this.edit_view || !$this.current_edit_record ) {
				return;
			}
			result_data = result.getResult();

			if ( !result_data ) {
				$this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
				return;
			}

			$this.absence_available_balance_dataList = {};
			$this.absence_available_balance_dataList.available_balance = Global.secondToHHMMSS( result_data.available_balance );
			$this.absence_available_balance_dataList.current_time = Global.secondToHHMMSS( result_data.current_time );
			$this.absence_available_balance_dataList.projected_balance = Global.secondToHHMMSS( result_data.projected_balance );
			$this.absence_available_balance_dataList.projected_remaining_balance = Global.secondToHHMMSS( result_data.projected_remaining_balance );
			$this.absence_available_balance_dataList.remaining_balance = Global.secondToHHMMSS( result_data.remaining_balance );

			$this.edit_view_ui_dic['available_balance'].setValue( $this.absence_available_balance_dataList.projected_remaining_balance );

			$this.available_balance_info.qtip(
				{
					show: {
						when: {event: 'click'},
						delay: 0,
						effect: {type: 'fade', length: 0}
					},

					hide: {
						when: {event: 'unfocus'}
					},

					style: {
						name: 'cream',
						width: 340
					},
					content: '<div style="width:100%;">' +
					'<div style="width:100%; clear: both;"><span style="float:left;">Available Balance: </span><span style="float:right;">' + $this.absence_available_balance_dataList.available_balance + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">Current Time: </span><span style="float:right;">' + $this.absence_available_balance_dataList.current_time + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">Remaining Balance: </span><span style="float:right;">' + $this.absence_available_balance_dataList.remaining_balance + '</span></div>' +
					'<div style="width:100%; height: 20px; clear: both;"></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">Projected Balance by ' + last_date_stamp + ': </span><span style="float:right;">' + $this.absence_available_balance_dataList.projected_balance + '</span></div>' +
					'<div style="width:100%; clear: both;"><span style="float:left;">Projected Remaining Balance:</span><span style="float:right;">' + $this.absence_available_balance_dataList.projected_remaining_balance + '</span></div>' +
					'</div>'

				} );
		}

	},

	buildSearchAndLayoutUI: function() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		//Display Columns

		var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		var column_selector = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );

		this.column_selector = $( column_selector ),

			this.column_selector = this.column_selector.ADropDown( {
				display_show_all: false,
				id: 'column_selector',
				key: 'value',
				allow_drag_to_order: true,
				display_close_btn: false
			} );

		form_item_label.text( $.i18n._( 'Display Columns' ) + ':' );
		form_item_input_div.append( this.column_selector );

		layout_div.append( form_item );

		layout_div.append( "<div class='clear-both-div'></div>" );

		this.column_selector.setColumns( [
			{name: 'label', index: 'label', label: 'Column Name', width: 100, sortable: false}
		] );

		//Save and update layout

		form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) + ': ' );

		this.save_search_as_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
		this.save_search_as_input = $( this.save_search_as_input );
		this.save_search_as_input.TTextInput();

		var save_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Save' ) + "' />" )

		form_item_input_div.append( this.save_search_as_input );
		form_item_input_div.append( save_btn );

		var $this = this;
		save_btn.click( function() {
			$this.onSaveNewLayout();
		} );

		//Previous Saved Layout

		this.previous_saved_layout_div = $( "<div class='previous-saved-layout-div'></div>" );

		form_item_input_div.append( this.previous_saved_layout_div );

		form_item_label = $( "<span style='margin-left: 5px' >" + $.i18n._( 'Previous Saved Searches' ) + ":</span>" );
		this.previous_saved_layout_div.append( form_item_label );

		this.previous_saved_layout_selector = $( "<select style='margin-left: 5px' class='t-select'>" );
		var update_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Update' ) + "' />" );
		var del_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Delete' ) + "' />" );

		update_btn.click( function() {
			$this.onUpdateLayout();
		} );

		del_btn.click( function() {
			$this.onDeleteLayout();
		} );

		this.previous_saved_layout_div.append( this.previous_saved_layout_selector );
		this.previous_saved_layout_div.append( update_btn );
		this.previous_saved_layout_div.append( del_btn );

		layout_div.append( form_item );

		this.previous_saved_layout_div.css( 'display', 'none' );

	},

	setCurrentEditRecordData: function() {

		if ( this.is_mass_adding ) {
			this.edit_view_form_item_dic.start_dates.show();
			this.edit_view_form_item_dic.start_date_stamp.hide();
			this.edit_view_form_item_dic.start_date_stamps.hide();

			this.edit_view_form_item_dic.user_ids.show();
			this.edit_view_form_item_dic.user_id.hide();

			this.edit_view_ui_dic.start_dates.setEnabled( false );
			this.edit_view_ui_dic.user_ids.setEnabled( false );

		} else {
			this.edit_view_form_item_dic.start_dates.hide();

			if ( this.current_edit_record.id || this.is_mass_editing ) {
				this.edit_view_form_item_dic.start_date_stamp.show();
				this.edit_view_form_item_dic.start_date_stamps.hide();

				this.edit_view_form_item_dic.user_ids.hide();
				this.edit_view_form_item_dic.user_id.show();

			} else {
				this.edit_view_form_item_dic.start_date_stamps.show();
				this.edit_view_form_item_dic.start_date_stamp.hide();
				this.current_edit_record.start_date_stamps = this.current_edit_record.start_date_stamp;

				this.edit_view_form_item_dic.user_ids.show();
				this.edit_view_form_item_dic.user_id.hide();
			}

		}

		this.pre_total_time = 0;

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_ids':
						if ( widget.is( ':visible' ) ) {
							var user_array = this.getSelectUsersArray();
							this.current_edit_record[key] = user_array;
							widget.setValue( user_array );
						}
						break;
					case 'start_dates':
						var date_array = this.getSelectDateArray();
						this.current_edit_record[key] = date_array;
						date_array = Global.buildRecordArray( date_array );

						widget.setSourceData( date_array );
						widget.setValue( date_array );
						break;
					case 'total_time':
						//Don't set when copy as new
						if ( this.current_edit_record.id ) {
							this.pre_total_time = this.current_edit_record[key];
						}
						var startTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'];
						var endTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'];
						var schedulePolicyId = this.current_edit_record['schedule_policy_id'];
						var user_id = this.current_edit_record.user_id;
						var total_time = this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, {async: false} ).getResult();
						this.current_edit_record.total_time = total_time;
						widget.setValue( Global.secondToHHMMSS( total_time ) );
						break;
					case 'job_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							var args = {};
							args.filter_data = {status_id: 10, user_id: this.current_edit_record.user_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							args = {};
							args.filter_data = {status_id: 10, job_id: this.current_edit_record.job_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.onAvailableBalanceChange();
		this.setEditViewDataDone();

	},

	setDefaultMenuMassEditIcon: function( context_btn, grid_selected_length, is_year_mode ) {

		if ( !this.editPermissionValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		var grid_selected_id_array = [];

		if ( this.getMode() === ScheduleViewControllerMode.YEAR ) {

			for ( var i = 0; i < this.select_all_shifts_array.length; i++ ) {
				var select_cell_data = this.select_all_shifts_array[i];

				if ( !select_cell_data[0].id ) {
					continue;
				}
				grid_selected_id_array.push( select_cell_data[0].id );
			}

		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
		}

		if ( this.select_all_shifts_array.length < 2 ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}

	},

	setAbsencePolicyFilter: function( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		if ( !this.is_mass_editing ) {

			if ( !this.current_edit_record || !this.current_edit_record.id ) {
				filter.filter_data.user_id = this.current_edit_record.user_ids;
			} else {
				filter.filter_data.user_id = this.current_edit_record.user_id;
			}

		} else {

			if ( this.edit_view_ui_dic.user_id.isChecked() ) {
				filter.filter_data.user_id = this.current_edit_record.user_id;
			} else {
				filter.filter_data.user_id = this.selected_user_ids;
			}

		}

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	},

	setDefaultMenuCopyIcon: function( context_btn, grid_selected_length, is_year_mode ) {
		if ( !this.copyPermissionValidate() || is_year_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuSaveAndAddIcon: function( context_btn, grid_selected_length, is_year_mode ) {
		if ( (!this.addPermissionValidate() && !this.editPermissionValidate()) || is_year_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSaveAndCopyIcon: function( context_btn, grid_selected_length, is_year_mode ) {
		if ( (!this.addPermissionValidate() && !this.editPermissionValidate()) || is_year_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuCopyAsNewIcon: function( context_btn, grid_selected_length, is_year_mode ) {
		if ( (!this.copyAsNewPermissionValidate()) || is_year_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing || this.is_mass_adding ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}

				continue;
			}

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.import_icon:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.find_available:
					this.setEditMenuFindAvailableIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand0.timetrex.com/interface/html5/#!m=Client line 308
		if ( !this.context_menu_array ) {
			return;
		}

		var len = this.context_menu_array.length;

		var grid_selected_length = this.select_all_shifts_array.length;

		var is_year_mode = (this.getMode() === ScheduleViewControllerMode.YEAR) ? true : false;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'disable-image' );
			context_btn.removeClass( 'invisible-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length, is_year_mode );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length, is_year_mode );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length, is_year_mode );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length, is_year_mode );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length, is_year_mode );
					break;
				case ContextMenuIconName.move:
					if ( !this.movePermissionValidate() || is_year_mode ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
					if ( !this.movePermissionValidate() || is_year_mode ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.drag_copy:
					if ( !this.copyPermissionValidate() || is_year_mode ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.swap:
					if ( !this.editPermissionValidate() || is_year_mode ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.override:
					if ( (!this.editPermissionValidate() && !this.movePermissionValidate() && !this.copyPermissionValidate())
						|| is_year_mode ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuEditTimesheetIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.find_available:
					this.setDefaultMenuFindAvailabletIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuFindAvailableIcon: function( context_btn ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuFindAvailabletIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editChildPermissionValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditTimesheetIcon: function( context_btn, grid_selected_length, pId ) {

		if ( this.select_cells_Array.length === 1 && this.select_cells_Array[0].user_id != 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.select_cells_Array.length === 1 && this.select_cells_Array[0].user_id != 0 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	onUpdateLayout: function() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.strict_range_btn.getValue();
		args.data.weeklyTotals = this.weekly_totals_btn.getValue();
		args.data.dailyTotals = this.daily_totals_btn.getValue();
		args.data.showAllEmp = this.all_employee_btn.getValue();

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSaveNewLayout: function( default_layout_name ) {

		if ( Global.isSet( default_layout_name ) ) {
			var layout_name = default_layout_name
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.strict_range_btn.getValue();
		args.data.weeklyTotals = this.weekly_totals_btn.getValue();
		args.data.dailyTotals = this.daily_totals_btn.getValue();
		args.data.showAllEmp = this.all_employee_btn.getValue();

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onClearSearch: function() {
		var do_update = false;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
			var layout_name = BaseViewController.default_layout_name;
			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			do_update = true;

		} else {

			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;

			this.column_selector.setSelectGridData( this.default_display_columns );

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		if ( do_update ) {
			var args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.display_columns = selected_display_columns;
			args.data.filter_data = filter_data;

		}

		args.data.mode = this.getMode();

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSearch: function() {

		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;

		this.getSearchPanelFilter();

		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
			var layout_name = BaseViewController.default_layout_name;

		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.strict_range_btn.getValue();
		args.data.weeklyTotals = this.weekly_totals_btn.getValue();
		args.data.dailyTotals = this.daily_totals_btn.getValue();
		args.data.showAllEmp = this.all_employee_btn.getValue();

		ProgressBar.showOverlay();
		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.need_select_layout_name = layout_name;

					$this.initLayout();
				}

			}
		} );

	},

	setSelectLayout: function() {

		var $this = this;

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
			this.select_layout.data.display_columns = this.default_display_columns;
		}

		var layout_data = this.select_layout.data;

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.mode ) {
			$this.setToggleButtonValue( LocalCacheData.all_url_args.mode );
		} else {
			if ( Global.isSet( layout_data.mode ) ) {
				$this.setToggleButtonValue( layout_data.mode.toLowerCase() );

			} else {
				$this.setToggleButtonValue( ScheduleViewControllerMode.WEEK );
			}
		}

		if ( Global.isSet( layout_data.strictRange ) ) {
			$this.strict_range_btn.setValue( layout_data.strictRange );
		}

		if ( Global.isSet( layout_data.weeklyTotals ) ) {
			$this.weekly_totals_btn.setValue( layout_data.weeklyTotals );
		}

		if ( Global.isSet( layout_data.dailyTotals ) ) {
			$this.daily_totals_btn.setValue( layout_data.dailyTotals );
		}

		if ( Global.isSet( layout_data.showAllEmp ) ) {
			$this.all_employee_btn.setValue( layout_data.showAllEmp );
		}

		//Set Display Column in layout panel
		this.column_selector.setSelectGridData( display_columns );

		//Set Previoous Saved layout combobox in layout panel
		var layouts_array = this.search_panel.getLayoutsArray();

		this.previous_saved_layout_selector.empty();
		if ( layouts_array && layouts_array.length > 0 ) {
			this.previous_saved_layout_div.css( 'display', 'inline' );

			var len = layouts_array.length;
			for ( var i = 0; i < len; i++ ) {
				var item = layouts_array[i];
				this.previous_saved_layout_selector.append( '<option value="' + item.id + '">' + item.name + '</option>' )
			}
			$( this.previous_saved_layout_selector.find( 'option' ) ).filter( function() {
				return $( this ).attr( 'value' ) === $this.select_layout.id;
			} ).prop( 'selected', true ).attr( 'selected', true );

		} else {
			this.previous_saved_layout_div.css( 'display', 'none' );
		}

		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;

			this.setDatePickerValue( LocalCacheData.default_filter_for_next_open_view.select_date );

			this.select_layout.data.mode = 'Week';
			$this.setToggleButtonValue( layout_data.mode.toLowerCase() );

			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		this.setSearchPanelFilter( true );

		this.search( false, true );

		this.setDefaultMenu( true );

	},

	getMode: function() {
		return this.toggle_button.getValue()
	},

	search: function( setDefaultMenu, use_date_picker_date ) {

		this.setActionsButtonStatus();
		this.final_schedule_data_array = [];

		var $this = this;
		var filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var start_date_string;

		var mode = this.getMode();

		if ( this.all_employee_btn.getValue() ) {
			filter_data.include_all_users = true;
		}

		var strict = this.strict_range_btn.getValue();

		if ( use_date_picker_date || !this.end_date ) {
			start_date_string = this.start_date_picker.getValue();

			if ( mode === ScheduleViewControllerMode.YEAR ) {
				this.year_mode_original_date = start_date_string;
			}

		} else {

			if ( strict ) {
				if ( mode === ScheduleViewControllerMode.YEAR ) {
					start_date_string = this.year_mode_original_date ? this.year_mode_original_date : this.start_date_picker.getValue();
				} else if ( mode === ScheduleViewControllerMode.MONTH ) {
					start_date_string = new Date( new Date( $this.end_date.getTime() ).setDate( $this.end_date.getDate() - 15 ) ).format();
				} else {
					start_date_string = $this.end_date.format();
				}

			} else {
				start_date_string = $this.start_date.format();
			}

		}

		LocalCacheData.last_schedule_selected_date = start_date_string;

		this.api.getCombinedSchedule( {filter_data: filter_data}, start_date_string, mode, strict, {
			onResult: function( result ) {
				$this.full_schedule_data = result.getResult();

				//Error: Unable to get property 'start_display_date' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/ line 3805
				if ( $this.full_schedule_data === true || !$this.full_schedule_data || !$this.full_schedule_data.schedule_dates ) {
					return;
				}

				$this.start_date = Global.strToDate( $this.full_schedule_data.schedule_dates.start_display_date );
				$this.end_date = Global.strToDate( $this.full_schedule_data.schedule_dates.end_display_date );
				$this.buildCalendars();

				if ( setDefaultMenu ) {
					$this.setDefaultMenu( true );
				}

				$this.searchDone();

			}
		} )

	},

	getLastDateOfMonthTime: function() {
		var last_date_row = this.month_date_row_array[this.month_date_row_array.length - 1];

		return last_date_row['6_time'];

	},

	setActionsButtonStatus: function() {
		var mode = this.getMode();

		this.weekly_totals_btn.setEnable( true );
		this.strict_range_btn.setEnable( true );
		this.daily_totals_btn.setEnable( true );
		this.all_employee_btn.setEnable( true );
		switch ( mode ) {
			case ScheduleViewControllerMode.DAY:
				this.weekly_totals_btn.setEnable( false );
				this.strict_range_btn.setEnable( false );
				break;
			case ScheduleViewControllerMode.WEEK:
				break;
			case ScheduleViewControllerMode.MONTH:
				break;
			case ScheduleViewControllerMode.YEAR:

				this.daily_totals_btn.setEnable( false );
				break;
		}
	},

	onOverrideClick: function() {

		var override_icon = $( '#' + ContextMenuIconName.override );

		if ( override_icon.hasClass( 'selected-menu' ) ) {
			override_icon.removeClass( 'selected-menu' );
			this.is_override = false;
		} else {
			override_icon.addClass( 'selected-menu' );
			this.is_override = true;
		}

	},

	setMoveOrDropMode: function( id ) {

		var drag_copy_icon = $( '#' + ContextMenuIconName.drag_copy );
		var move_icon = $( '#' + ContextMenuIconName.move );
		var swap_icon = $( '#' + ContextMenuIconName.swap );
		var override_icon = $( '#' + ContextMenuIconName.override );

		drag_copy_icon.removeClass( 'selected-menu' );
		move_icon.removeClass( 'selected-menu' );
		swap_icon.removeClass( 'selected-menu' );

		var drag_invisible = false;
		var move_invisible = false;
		var swap_invisible = false;

		if ( !this.copyPermissionValidate() ) {
			drag_invisible = true;
		}

		if ( !this.movePermissionValidate() ) {
			move_invisible = true;
		}

		if ( !this.editPermissionValidate() ) {
			swap_invisible = true;
		}

		if ( move_invisible && id === ContextMenuIconName.move ) {
			drag_copy_icon.addClass( 'selected-menu' );
		} else {
			$( '#' + id ).addClass( 'selected-menu' );
		}

		if ( drag_invisible && move_invisible ) {
			this.select_drag_menu_id = null;
		} else {
			this.select_drag_menu_id = id;
		}

	},

	setHolidayDataDic: function() {

		if ( this.full_schedule_data.holiday_data ) {
			for ( var i = 0; i < this.full_schedule_data.holiday_data.length; i++ ) {
				var item = this.full_schedule_data.holiday_data[i];
				var standard_date = Global.strToDate( item.date_stamp ).format( this.weekly_format );
				this.holiday_data_dic[standard_date] = item;
			}
		}
	},

	buildCalendars: function() {

		var $this = this;
		this.setHolidayDataDic();
		this.buildScheduleColumns();
		this.buildScheduleSource();

		this.buildScheduleGrid();
		this.showGridBorders();

		var start = 0;
		var page = 1;
		var page_num = 10;

		//this.grid will be empty when first time int this function, so put this judge here instead at begin.
		//Error: Uncaught TypeError: Cannot call method 'clearGridData' of null in https://ondemand1.timetrex.com/interface/html5/index.php?desktop=1#!m=Schedule&date=20150118&mode=week line 6944
		if ( !this.grid ) {
			return;
		}

		this.grid.clearGridData();

		var j = 0;

		$this.grid.css( 'opacity', 0 );

		addGridData();

		var interval = setInterval( function() {
			if ( j < $this.schedule_source.length ) {
				addGridData()
			} else {
				doNext();
			}
		}, 10 );

		$this.setGridSize();

		function addGridData() {
			for ( var i = j; i < j + 200; i++ ) {

				if ( i < $this.schedule_source.length ) {
					var item = $this.schedule_source[i];
					$this.grid.addRowData( i + 1, item );
				} else {
					break;
				}

			}

			j = i;
		}

		function doNext() {

			$this.grid.css( 'opacity', 1 );
			clearInterval( interval );

			if ( this.getMode !== ScheduleViewControllerMode.YEAR ) {
				$this.setScheduleGridRowSpan();
			}

			$this.highLightSelectDay();

			$this.setGridColumnsWidth();

			$this.setGridSize();

			//Only work when month mode
			$this.setMonthDateRowPosition();

			//Only work when year mode
			$this.setYearGroupHeader();

			$this.setScheduleGridDragAble();

			$this.setScrollPosition();

			$this.autoOpenEditViewIfNecessary();

			$this.setMonthDateRowBackGround();

			$this.setWeeklyTotalHeader();

			//set right click menu to list view grid
			$this.initRightClickMenu();

		}

	},

	setWeeklyTotalHeaderSize: function() {
		var show_weekly_total = this.weekly_totals_btn.getValue();

		if ( !show_weekly_total ) {
			return;
		}

		var size_tr = $( '.size-tr' );

		if ( size_tr.length === 0 ) {
			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
		var current_trs = table.find( '.ui-jqgrid-labels' );
		var len = current_trs.children().length;

		for ( var i = 0; i < len; i++ ) {
			var item = current_trs.children().eq( i );
			size_tr.children().eq( i ).width( parseInt( item[0].style.width ) );
		}

	},

	setWeeklyTotalHeader: function() {
		var show_weekly_total = this.weekly_totals_btn.getValue();

		if ( !show_weekly_total || !this.weekly_totals_btn.getEnabled() ) {
			$( '.size-tr' ).remove();
			$( '.group-tr' ).remove();

			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );

		var size_tr = $( '<tr class="size-tr" >' +
		'</tr>' );

		var new_tr = $( '<tr class="group-column-tr group-tr" >' +
		'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
		'<span class="group-column-label"></span>' +
		'</th>' );

		var current_trs = table.find( '.ui-jqgrid-labels' );
		createSizeColumns();
		table.children( 0 ).prepend( size_tr );

		var column_length = this.grid.getGridParam( 'colModel' ).length;
		createColumn( column_length - 4, '' );
		createColumn( 4, $.i18n._( 'Total' ) );
		new_tr.insertAfter( size_tr );

		function createSizeColumns() {

			var len = current_trs.children().length;

			for ( var i = 0; i < len; i++ ) {
				var td = $( '<th class="" style="border-right: 1px solid #dddddd" >' + '</th>' );
				var item = current_trs.children().eq( i );
				td.width( item.width() );
				td.height( 0 );
				size_tr.append( td );
			}

		}

		function createColumn( end_index, text ) {

			var pay_period_th = new_th.clone();

			pay_period_th.children( 0 ).text( text );
			pay_period_th.attr( 'colspan', end_index );

			new_tr.append( pay_period_th );
		}
	},

	setMonthDateRowBackGround: function() {
		if ( this.getMode() === ScheduleViewControllerMode.MONTH ) {
			$( this.el ).find( '.month-date-cell' ).parent().css( 'background-color', '#375979' );
		}
	},

	setScrollPosition: function() {
		if ( this.scroll_position > 0 ) {
			this.grid.parent().parent().scrollTop( this.scroll_position );
		}
	},

	setYearGroupHeaderSize: function() {
		if ( this.getMode() !== ScheduleViewControllerMode.YEAR ) {
			return;
		}
	},

	setYearGroupHeader: function() {

		if ( this.getMode() !== ScheduleViewControllerMode.YEAR ) {
			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
		var new_tr = $( '<tr class="group-column-tr" >' +
		'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
		'<span class="group-column-label"></span>' +
		'</th>' );

		var default_tr = new_tr.clone();
		$( table.children()[0] ).prepend( default_tr );

		//Build first row to correct width for span columns which in second row, table width decided by first row
		var datesTHs = table.find( 'th' );
		var len = datesTHs.length;
		for ( var i = 0; i < len; i++ ) {
			var th = $( datesTHs[i] );

			var default_th = th.clone();
			default_th.attr( 'id', '' );
			default_th.empty();
			default_th.attr( 'row', '' );
			default_th.height( 0 );
			default_tr.append( default_th );

		}

		var first_tr = $( $( table.children()[0] ).children()[0] );

		//Create group column header
		default_tr = new_tr.clone();
		default_th = new_th.clone();
		default_tr.append( default_th );
		default_tr.insertAfter( first_tr );

		var current_month = null;
		var current_date = null;
		var same_month_count = 0;

		for ( i = 1; i < len; i++ ) {
			th = $( datesTHs[i] );
			var id_split_array = th.attr( 'id' ).split( '_' );
			var date_str = id_split_array[id_split_array.length - 1];

			if ( date_str === 'shifts' || date_str === 'absences' || date_str === 'total_time' || date_str === 'total_time_wage' ) {
				var month = '-1';
			} else {
				month = Global.strToDate( date_str, this.full_format ).getMonth();
			}

			if ( !Global.isSet( current_month ) ) {
				current_month = month;
				current_date = date_str;
				same_month_count = 1;

			} else {
				if ( month === current_month && i !== len - 1 ) {
					same_month_count = same_month_count + 1;

				} else {

					if ( i === len - 1 ) {
						same_month_count = same_month_count + 1;
					}

					default_th = new_th.clone();
					default_th.children( 0 ).text( Global.strToDate( current_date, this.full_format ).format( 'MMM' ) );
					default_th.attr( 'colspan', same_month_count );
					default_tr.append( default_th );

					current_month = month;
					current_date = date_str;
					same_month_count = 1;

					if ( month === '-1' ) {
						default_th = new_th.clone();
						default_th.children( 0 ).text( '' );
						default_th.attr( 'colspan', len - i );
						default_tr.append( default_th );
						break;
					}

				}
			}

		}

	},

	setMonthDateRowPosition: function() {

		if ( this.getMode() !== ScheduleViewControllerMode.MONTH ) {
			return;
		}

		var $this = this;
		this.month_date_row_position = {};
		var i = 0;
		for ( var key in this.month_date_row_tr_ids ) {
			this.month_date_row_position[i] = this.grid.find( '#' + key ).position().top;
			i = i + 1;
		}

		this.grid.parent().parent().scroll( function() {
			var top = $( this ).scrollTop();

			var start_day = $this.start_date.getDay();

			if ( top < $this.month_date_row_position[0] && $this.month_current_header_number !== 0 ) {

				//Deal with header before first date row.
				$this.month_date_row_tr_ids = {};
				$this.month_current_header_number = 0;
				for ( var i = 0; i < 7; i++ ) {
					var current_date = new Date( new Date( $this.start_date.getTime() ).setDate( $this.start_date.getDate() + i ) );
					var header_text = current_date.format( $this.weekly_format );
					var header_container = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + data_field );
					var data_field = ((start_day + i) % 7);
					var header = $( $this.el ).find( '#jqgh_' + $this.ui_id + '_grid_' + data_field );
					header.text( header_text );
					header_container.removeClass( 'highlight-header' );

				}

				$this.highLightSelectDay();

			} else if ( top > $this.month_date_row_position[0] && top < $this.month_date_row_position[1] && $this.month_current_header_number !== 1 ) {
				setHeaderText( 0, 1 );

			} else if ( top > $this.month_date_row_position[1] && top < $this.month_date_row_position[2] && $this.month_current_header_number !== 2 ) {
				setHeaderText( 1, 2 );
			} else if ( top > $this.month_date_row_position[2] && top < $this.month_date_row_position[3] && $this.month_current_header_number !== 3 ) {
				setHeaderText( 2, 3 );
			} else if ( top > $this.month_date_row_position[3] && $this.month_current_header_number !== 4 ) {
				setHeaderText( 3, 4 );
			}

			function setHeaderText( index, headerNumber ) {
				$this.month_date_row_tr_ids = {};
				$this.month_current_header_number = headerNumber;
				var date_row = $this.month_date_row_array[index];
				for ( var i = 0; i < 7; i++ ) {
					var data_field = ((start_day + i) % 7);
					var header = $( $this.el ).find( '#jqgh_' + $this.ui_id + '_grid_' + data_field );
					var header_container = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + data_field );
					header_container.removeClass( 'highlight-header' );
					var full_date = date_row[data_field + '_full_date'];
					var date_cell = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + full_date );

					if ( date_cell.hasClass( 'highlight-header' ) ) {
						header_container.addClass( 'highlight-header' );
					}
					header.html( date_row[data_field] );

				}
			}

		} )

	},

	setGridSize: function() {

		if ( !this.grid || !this.grid.is( ':visible' ) ) {
			return;
		}

		var mode = this.getMode();

		var min_width = this.grid_total_width;
		var show_weekly_total = this.weekly_totals_btn.getValue();
		var common_display_columns = this.buildDisplayColumns( this.select_layout.data.display_columns );

		switch ( mode ) {
//			case 'week':
//			case 'month':
//				min_width = this.schedule_columns.length * 125;
//				if ( show_weekly_total ) {
//					min_width = (this.schedule_columns.length - 4) * 125 + 300;
//				}
//				break;
			case 'year':
				min_width = (this.schedule_columns.length - 1) * 25 + 125;
				if ( show_weekly_total ) {
					min_width = (this.schedule_columns.length - 5) * 25 + 270 + 125;
				}
				break;
//			case 'day':
//
//				min_width = (this.schedule_columns.length - 1) * 125 + this.day_header_width;
//				break;
		}

		if ( Global.bodyWidth() > Global.app_min_width ) {

			if ( min_width < Global.bodyWidth() - 14 ) {
				min_width = Global.bodyWidth() - 14
			}

		} else {
			if ( min_width < Global.app_min_width - 14 ) {
				min_width = Global.app_min_width - 14
			}
		}

		this.grid.setGridWidth( min_width );

		var grid_top_border = $( this.el ).find( '.schedule-top-border' );
		var grid_bottom_border = $( this.el ).find( '.schedule-top-border' );

		grid_top_border.width( min_width );
		grid_bottom_border.width( min_width );

		if ( mode !== ScheduleViewControllerMode.YEAR ) {
			this.grid.setGridHeight( $( this.el ).height() - this.search_panel.height() - 90 - 32 );
		} else {
			this.grid.setGridHeight( $( this.el ).height() - this.search_panel.height() - 90 - 32 - 30 );
		}

		this.setWeeklyTotalHeaderSize();

	},

	setGridColumnsWidth: function() {

		var col_model = this.grid.getGridParam( 'colModel' );
		var grid_data = this.grid.getGridParam( 'data' );
		this.grid_total_width = 0;

		//Error: Unable to get property 'length' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/ line 4328
		if ( !col_model ) {
			return;
		}

		for ( var i = 0; i < col_model.length; i++ ) {
			var col = col_model[i];
			var field = col.name;
			var longest_words = '';

			for ( var j = 0; j < grid_data.length; j++ ) {
				var row_data = grid_data[j];

				if ( col.fixed ) {
					break;
				}

				if ( !row_data.hasOwnProperty( field ) ) {
					break;
				}
				var current_words = row_data[field];

				if ( !current_words ) {
					current_words = '';
				}

				if ( !longest_words ) {
					longest_words = current_words.toString();
				} else {
					if ( current_words && current_words.length > longest_words.length ) {
						longest_words = current_words.toString();
					}
				}

			}

			var width;
			if ( col.fixed ) {
				width = col.width;
				this.grid_total_width += width;
				this.grid.setColProp( field, {widthOrg: width} );

			} else if ( longest_words ) {
				var width_test = $( '<span id="width_test" />' );
//				width_test.css( 'font-family', "'Lucida Grande', 'Lucida Sans', Arial, sans-serif" );
				width_test.css( 'font-size', '11' );
				width_test.css( 'font-weight', 'normal' );
				$( 'body' ).append( width_test );
//				var temp_words = longest_words.replace( new RegExp( ' ', "gm" ), 'A' );
//				temp_words = temp_words.toUpperCase();
				width_test.text( longest_words );

				width = width_test.width();
				width_test.text( col.label );

				if ( this.getMode() != ScheduleViewControllerMode.YEAR ) {
					var header_width = width_test.width();

					if ( header_width > width ) {
						width = header_width + 20;
					}

					if ( i !== col_model.length - 1 ) {
						this.grid_total_width += width;

						this.grid.setColProp( field, {widthOrg: width} );
					} else {
						this.grid_total_width += (width + 20);

						this.grid.setColProp( field, {widthOrg: (width + 20)} );
					}

				} else {
					if ( width < 20 ) {
						width = 20;
					}

					this.grid_total_width += width;

					this.grid.setColProp( field, {widthOrg: width} );
				}

				width_test.remove();

			}
		}

		var gw = this.grid.getGridParam( 'width' );

		if ( this.grid_total_width > gw ) {
			gw = this.grid_total_width;
		}

		this.grid.setGridWidth( gw );

	},

	buildScheduleSource: function() {
		this.no_date_array = [];
		this.has_date_array = [];
		this.final_schedule_data_array = [];
		var mode = this.getMode();

		this.no_date_array = this.buildNoDateArray();
		this.has_date_array = this.buildHasDateArray();
		var has_date_temp_array_length = this.has_date_array.length;
		var sort_fields = this.buildSortFields();
		this.has_date_array.sort( Global.m_sort_by( sort_fields ) );
		this.no_date_array.sort( Global.m_sort_by( sort_fields ) );

		if ( mode !== ScheduleViewControllerMode.MONTH ) {
			this.final_schedule_data_array = this.no_date_array.concat( this.has_date_array );
		}

		switch ( this.getMode() ) {
			case ScheduleViewControllerMode.WEEK:
				this.buildWeeklySource();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.buildMonthSource();
				break;
			case ScheduleViewControllerMode.YEAR:
				this.buildYearlySource();
				break;
			case ScheduleViewControllerMode.DAY:
				this.buildDailySource();
				break;
		}

	},

	buildShiftKey: function( shift ) {

		var key = '';
		for ( var i = 0; i < this.shift_key_name_array.length; i++ ) {
			var field_name = this.shift_key_name_array[i];
			var column_name = this.shift_key_name_array[i].replace( '_id', '' ); //judge if shit has correct field value
			if ( column_name !== 'user' ) {
				if ( shift[column_name] ) {
					key = shift[field_name] + '-' + key;
				} else {
					key = 0 + '-' + key
				}
			} else {
				key = shift[field_name] + '-' + key;
			}
		}
		return key;
	},

	buildMonthSource: function() {
		var $this = this;

		var date_row_index = 0;
		var month_week_data_index = 0;
		var date_row = this.month_date_row_array[date_row_index];
		var start_day = this.start_date.getDay(); //start from first date row, not include column

		var first_day_time = date_row[start_day + '_time'];
		var month_week_data_array = [];

		var has_date_array = this.has_date_array.slice();

		for ( var j = 0; j < 5; j++ ) {
			var current_week_array = [];
			var len = has_date_array.length;
			for ( var i = 0; i < len; i++ ) {

				var shift = has_date_array[i];
				var date = Global.strToDate( shift.date_stamp );
				var time = date.getTime();

				if ( time < first_day_time ) {

					has_date_array.splice( i, 1 );

					i = i - 1;
					len = len - 1;

					current_week_array.push( shift );
				}
			}

			current_week_array = this.no_date_array.slice().concat( current_week_array )

			month_week_data_array[month_week_data_index] = current_week_array;

			if ( date_row_index < 3 ) {
				date_row_index = date_row_index + 1;
				month_week_data_index = month_week_data_index + 1;
				date_row = this.month_date_row_array[date_row_index];
				first_day_time = date_row[start_day + '_time'];
			} else {
				month_week_data_index = month_week_data_index + 1;

				//Don't use this.end_date because the end date may larger than the last day of this month. Use the last date in date row
				var end_date_time = this.getLastDateOfMonthTime();
				var end_date = new Date( end_date_time );
				first_day_time = new Date( end_date.setDate( end_date.getDate() + 1 ) ).getTime();
			}
		}

		buildMonthWeeklyData( -1, month_week_data_array[0] );
		buildMonthWeeklyData( 0, month_week_data_array[1] );
		buildMonthWeeklyData( 1, month_week_data_array[2] );
		buildMonthWeeklyData( 2, month_week_data_array[3] );

		buildMonthWeeklyData( 3, month_week_data_array[4] );
//		  buildEmptyRow( 3 );

		function buildMonthWeeklyData( rowIndex, source_array ) {

			if ( source_array.length < 1 ) {
				buildEmptyRow( rowIndex );
				return;
			}

			var map = {};
			var len = source_array.length;
			var push_to_last = false;

			if ( rowIndex === $this.month_date_row_array.length - 1 ) {
				push_to_last = true;
			} else {
				var date_row = $this.month_date_row_array[rowIndex + 1];
				var index = 0;
			}

			for ( var i = 0; i < len; i++ ) {
				var shift = source_array[i];
				var date_string;
				if ( shift.date_stamp ) {
					var date = Global.strToDate( shift.date_stamp );

					date_string = date.getDay();
				}

				var key = $this.buildShiftKey( shift );

				// each row of schedule data, start from first row
				if ( !map[key] ) {
					var row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;

					var display_columns = $this.select_layout.data.display_columns;
					var display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						var field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string >= 0 ) {
						if ( shift.status_id === 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					if ( !push_to_last ) {
						index = $this.schedule_source.indexOf( date_row );
						$this.schedule_source.splice( index, 0, row );
						map[key] = [index];
					} else {
						$this.schedule_source.push( row );
						map[key] = [$this.schedule_source.length - 1];
					}

				} else {
					// if one row already created, go to here to create cells in this row

					var find_position = false;
					for ( var x = 0; x < map[key].length; x++ ) {
						var row_index = map[key][x];
						row = $this.schedule_source[row_index];
						if ( row[date_string] ) {
							continue;
						} else {

							if ( date_string >= 0 ) {
								if ( shift.status_id === 20 ) {

									row[date_string] = $this.getAbsenceCellValue( shift );
								} else {
									row[date_string] = shift.start_time + ' - ' + shift.end_time;
								}
								row[date_string + '_data'] = shift;
							}

							find_position = true;
							break;
						}
					}

					if ( !find_position ) {
						row = {};
						row.user_full_name = shift.user_full_name;
						row.last_name = shift.last_name;
						row.user_id = shift.user_id;
						row.branch_id = shift.branch_id;
						row.department_id = shift.department_id;
						row.schedule_policy_id = shift.schedule_policy_id;
						row.job_id = shift.job_id;
						row.job_item_id = shift.job_item_id;

						display_columns = $this.select_layout.data.display_columns;
						display_columns_len = display_columns.length;

						for ( var j = 0; j < display_columns_len; j++ ) {
							field_name = display_columns[j];
							row[field_name] = shift[field_name] ? shift[field_name] : '';
						}

						if ( date_string >= 0 ) {
							if ( shift.status_id === 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;

						}

						if ( !push_to_last ) {
							index = $this.schedule_source.indexOf( date_row );
							$this.schedule_source.splice( index, 0, row );
							map[key].push( index );
						} else {
							$this.schedule_source.push( row );
							map[key].push( $this.schedule_source.length - 1 );
						}

					}

				}

			}

		}

		function buildEmptyRow( date_row_index ) {

			if ( date_row_index === -1 ) {
				var index = 0;
			} else {
				var date_row = $this.month_date_row_array[date_row_index];
				index = $this.schedule_source.indexOf( date_row ) + 1;
			}

			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.splice( index, 0, row );
		}

		this.showDailyTotal();
		this.showWeeklyTotal();

	},

	buildYearlySource: function() {
		var $this = this;
		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date.format( this.full_format );
			}

			var key = shift.user_id;

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {
					row[date_string] = shift.status_id === 10 ? 'S' : 'A';
					row[date_string + '_data'] = [shift];
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1]
			} else {

				// if one row already created, go to here to create cells in this row
				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {

						if ( row[date_string] !== 'A' ) {

							row[date_string] = shift.status_id === 10 ? 'S' : 'A';

						}
						row[date_string + '_data'].push( shift );
					} else {

						if ( date_string ) {

							row[date_string] = shift.status_id === 10 ? 'S' : 'A';
							row[date_string + '_data'] = [shift];
						}
						break;
					}
				}

			}

		}

		function buildEmptyRow() {

			var row = {};
			row.user_full_name = '';
			row.last_name = '';
			row.user_id = '';
			row.branch_id = '';
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showWeeklyTotal();

	},

	buildDailyHeaders: function() {

		var $this = this;
		var col_model = this.schedule_columns;
		var label_column = col_model[this.select_layout.data.display_columns.length + 1];

		var first_time = -1;
		var last_time = -1;

		var first_date_time = '';
		var last_date_time = '';

		var first_time_str = '';
		var last_time_str = '';

		var len = this.has_date_array.length;

		if ( len === 0 ) {

			var res = this.api.getScheduleDefaultData( {async: false} );
			var data = res.getResult();
			var selected_date_str = $this.getSelectDate();
			first_date_time = selected_date_str + ' ' + data.start_time;
			last_date_time = selected_date_str + ' ' + data.end_time;

			doNext();

			return;
		}
		for ( var i = 0; i < len; i++ ) {
			var item = this.has_date_array[i];

			if ( first_time === -1 ) {
				first_time = item.start_time_stamp;
				first_date_time = item.start_date;
				first_time_str = item.start_time;
			} else if ( item.start_time_stamp < first_time ) {
				first_time = item.start_time_stamp;
				first_date_time = item.start_date;
				first_time_str = item.start_time;
			}

			if ( last_time === -1 ) {
				last_time = item.end_time_stamp;
				last_date_time = item.end_date;
				last_time_str = item.end_time;
			} else if ( item.end_time_stamp > last_time ) {
				last_time = item.end_time_stamp;
				last_date_time = item.end_date;
				last_time_str = item.end_time;
			}
		}

		first_date_time = Global.getStandardDateTimeStr( first_date_time, first_time_str );
		last_date_time = Global.getStandardDateTimeStr( last_date_time, last_time_str );

		doNext();

		function doNext() {
			var current_date_time = new Date( Global.strToDateTime( first_date_time ).getTime() - 3600000 );
			last_date_time = Global.strToDateTime( last_date_time );
			var min = current_date_time.getMinutes() * 60000;

			var time_span = $( "<span class='day_hour_span'></span>" );

			if ( min > 0 ) {
				current_date_time = new Date( current_date_time.getTime() - min );
			}

			$this.day_mode_start_date_time = current_date_time;

			var time_offset = (last_date_time.getTime() - current_date_time.getTime()) / 3600000;
			var header_container = $( "<div class='day_hour_div'></div>" );

			var day_column = $( "<div class='day-column'></div>" );

			var day = $this.start_date.format( $this.weekly_format );

			day = $this.setHolidayHeader( day, true );
			day_column.text( day );

			var time_columns = $( "<div></div>" );

			var time_string = '';
			for ( var i = 0; i < time_offset; i++ ) {
				var current_hour_text = time_span.clone();
				current_hour_text = current_hour_text.text( current_date_time.format( 'hh:mm A' ) );
				if ( i < time_offset - 1 ) {
					time_string = time_string + current_date_time.format( 'hh:mm A' ) + '|';
				} else {
					time_string = time_string + current_date_time.format( 'hh:mm A' );
				}
				time_columns.append( current_hour_text );
				current_date_time = new Date( current_date_time.getTime() + 3600000 );
			}

			header_container.append( day_column );
			header_container.append( time_columns );

			$this.buildTotalShiftDic( time_string );

			label_column.label = header_container[0].outerHTML;
			if ( Math.ceil( time_offset ) > time_offset ) {
				label_column.width = $this.day_hour_width * Math.ceil( time_offset );
			} else {
				label_column.width = $this.day_hour_width * time_offset + 1
			}

			$this.day_header_width = label_column.width;

		}

	},

	//Build dic that contains all daily hours
	buildTotalShiftDic: function( timeHeader ) {
		var label_array = timeHeader.split( "|" );
		this.total_shifts_dic = {};
		for ( var i = 0; i < label_array.length; i++ ) {
			var shift_item = {sort_order: i, key: label_array[i], value: 0}
			this.total_shifts_dic[label_array[i]] = shift_item;
		}
	},

	buildDailySource: function() {

		var $this = this;
		this.buildDailyHeaders();

		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date.format( this.full_format );
			}

			var key = this.buildShiftKey( shift );

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;
				row.total = Global.secondToHHMMSS( shift.total_time );

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {
					if ( shift.status_id === 20 ) {

						row[date_string] = $this.getAbsenceCellValue( shift );
					} else {
						row[date_string] = shift.start_time + ' - ' + shift.end_time;
					}
					row[date_string + '_data'] = shift;
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1]
			} else {
				// if one row already created, go to here to create cells in this row

				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {
						continue;
					} else {

						if ( date_string ) {
							if ( shift.status_id === 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;
						}

						find_position = true;
						break;
					}
				}

				if ( !find_position ) {
					row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;
					row.total = Global.secondToHHMMSS( shift.total_time );

					display_columns = this.select_layout.data.display_columns;
					display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						var field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string ) {
						if ( shift.status_id === 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					this.schedule_source.push( row );
					map[key].push( this.schedule_source.length - 1 );
				}

			}

		}

		function buildEmptyRow() {

			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showDailyTotal();

	},

	buildWeeklySource: function() {
		var $this = this;
		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date.format( this.full_format );
			}

			var key = this.buildShiftKey( shift );

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {

					if ( shift.status_id === 20 ) {

						row[date_string] = $this.getAbsenceCellValue( shift );
					} else {
						row[date_string] = shift.start_time + ' - ' + shift.end_time;
					}

					row[date_string + '_data'] = shift;
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1]
			} else {
				// if one row already created, go to here to create cells in this row

				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {
						continue;
					} else {

						if ( date_string ) {
							if ( shift.status_id === 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;
						}

						find_position = true;
						break;
					}
				}

				if ( !find_position ) {
					row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;

					display_columns = this.select_layout.data.display_columns;
					display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string ) {

						if ( shift.status_id === 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					this.schedule_source.push( row );
					map[key].push( this.schedule_source.length - 1 );
				}

			}

		}

		function buildEmptyRow() {

			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showDailyTotal();
		this.showWeeklyTotal();

	},

	getAbsenceCellValue: function( shift ) {

		var result;
		if ( shift.absence_policy ) {
			if ( shift.note ) {
				result = '*' + shift.absence_policy;
			} else {
				result = shift.absence_policy;
			}
		} else {
			if ( shift.note ) {
				result = '*' + 'N/A';
			} else {
				result = 'N/A';
			}
		}

		return result;

	},

	buildSortFields: function() {
		var sort_by_fields = [];

		//Error: Uncaught TypeError: Cannot read property 'data' of null in https://ondemand1.timetrex.com/interface/html5/#!m=Schedule line 5169
		if ( this.select_layout && this.select_layout.data ) {
			var display_columns = this.select_layout.data.display_columns;

			if ( this.getMode() !== ScheduleViewControllerMode.YEAR ) {
				var display_columns_len = display_columns.length;
				if ( display_columns_len > 0 ) {
					sort_by_fields = display_columns.slice();
				}

			}
		}

		sort_by_fields.push( 'user_full_name' );

		sort_by_fields.push( {name: 'start_time_stamp', primer: parseFloat, reverse: false} );

		return sort_by_fields;

	},

	buildNoDateArray: function() {
		var records = [];
		var sort_array = [];
		var sort_item;
		var schedule_data = this.full_schedule_data.schedule_data

		for ( var date_key in schedule_data ) {
			sort_item = {};
			sort_item.value = schedule_data[date_key];
			sort_item.sort_key = date_key;
			sort_array.push( sort_item );
		}

		var len = sort_array.length;

		for ( var i = 0; i < len; i++ ) {
			var date_item = sort_array[i];
			for ( var item_key    in date_item.value ) {
				var item = date_item.value[item_key];
				item = this.replaceFalseToEmptyStringForSortFields( item );

				if ( !item.date_stamp ) {
					records.push( date_item.value[item_key] );
				}

			}

		}
		return records;
	},

	buildHasDateArray: function() {
		var records = [];
		var sort_array = [];
		var sort_item;
		var schedule_data = this.full_schedule_data.schedule_data

		for ( var date_key in schedule_data ) {
			sort_item = {};
			sort_item.value = schedule_data[date_key];
			sort_item.sort_key = date_key;
			sort_array.push( sort_item );
		}

		var len = sort_array.length;

		for ( var i = 0; i < len; i++ ) {
			var date_item = sort_array[i];
			for ( var item_key    in date_item.value ) {
				var item = date_item.value[item_key];
				item = this.replaceFalseToEmptyStringForSortFields( item );

				if ( item.date_stamp ) {
					records.push( date_item.value[item_key] );
				}

			}

		}
		return records;
	},

	replaceFalseToEmptyStringForSortFields: function( item ) {
		if ( !item.branch ) {
			item.branch = '';
		}

		if ( !item.department ) {
			item.department = '';
		}

		if ( !item.default_branch ) {
			item.default_branch = '';
		}

		if ( !item.default_department ) {
			item.default_department = '';
		}

		if ( !item.job ) {
			item.job = '';
		}

		if ( !item.job_item ) {
			item.job_item = '';
		}

		if ( !item.title ) {
			item.title = '';
		}

		return item;
	},

	buildScheduleGrid: function() {

		var $this = this;

		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		if ( !this.grid ) {

			this.grid = grid;

			this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				hoverrows: false,
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				onSelectRow: function( row_id, flag, e ) {
					var row_tr = $( this ).find( '#' + row_id );
					row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

//					$this.onSelectRow( 'timesheet_grid', row_id, this );
					return false;

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );

				},

				colNames: [],
				colModel: this.schedule_columns,
				viewrecords: true

			} );

		} else {

			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );
			this.grid.jqGrid( {
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {
					var row_tr = $( this ).find( '#' + row_id );
					row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );
//					$this.onSelectRow( 'timesheet_grid', row_id, this );
					return false;

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );

				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				hoverrows: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: this.schedule_columns,
				viewrecords: true
			} );

		}

		this.grid.parent().parent().css( 'overflow-x', 'hidden' );
		this.grid.parent().parent().scrollLeft( 1000 );

		this.grid.parent().parent().scroll( function( e ) {
			$this.scroll_position = $( e.target ).scrollTop();
		} );

		this.bindGridColumnEvents();

	},

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindGridColumnEvents: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		//Exception taht display column not existed, not sure when this will happen, but may there will be a second time load if this happen
		if ( !display_columns ) {
			return;
		}

		var len = display_columns.length;

		this.t_grid_header_array = [];

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			var column_header = $( $( this.el ).find( '#gbox_' + this.ui_id + '_grid' ).find( 'div #jqgh_' + this.ui_id + '_grid_' + column_info.name ) );

			this.t_grid_header_array.push( column_header.TGridHeader() );
			column_header.bind( 'click', onColumnHeaderClick );
		}

		var $this = this;

		function onColumnHeaderClick( e ) {
			var field = $( this ).attr( 'id' );
			field = field.substring( 10 + $this.ui_id.length + 1, field.length );

			if ( field === 'cb' || field === 'punch_info' ) { //first column, check box column.
				return;
			}

			var date;
			var mode = $this.getMode();

			if ( mode === ScheduleViewControllerMode.MONTH ) {
				var colModel = $this.grid.getGridParam( 'colModel' );
				date = $this.getCellRelatedDate( 1, colModel, $( this ).parent().index(), field );
			} else {
				date = Global.strToDate( field, $this.full_format );
			}

			if ( date && date.getYear() > 0 ) {
				$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				$this.highLightSelectDay();
			}

		}

	},

	buildMonthCell: function( cell_value, related_data, row ) {
		var col_models = this.grid.getGridParam( 'colModel' );
		var col_model = related_data.colModel;
		var content_div = $( "<div class='schedule-content-div'></div>" );
		var time_span = $( "<span class='schedule-time' ></span>" );
		var item = row[col_model.name + '_data'];
		var full_date_str = row[col_model.name + '_full_date'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:
				if ( cell_value && Global.isSet( cell_value.total_time ) ) {
					var total_span = $( "<span class='schedule-time total'></span>" );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences );
					total_span.text( Global.secondToHHMMSS( cell_value.total_time ) + ' = ' + currency + cell_value.total_time_wage );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );

				} else if ( cell_value ) {
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( cell_value );
					content_div.prepend( time_span );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

				} else if ( Date.parse( col_model.index ) ) {

					total_span = $( "<span class='schedule-time total'></span>" );
					currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: 0 A: 0' );
					total_span.text( '00:00 = ' + currency + '0.00' );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );

				}

				break;
			case ScheduleViewControllerRowType.DATE:
				time_span.addClass( 'date' );
				content_div.addClass( 'month-date-cell' );
				if ( cell_value ) {

					time_span.html( cell_value );

					content_div.attr( 'id', this.ui_id + '_grid_' + full_date_str )

				} else {
					time_span.addClass( 'empty-date' );
					time_span.text( '.' );
				}

				content_div.prepend( time_span );

				break;
			case ScheduleViewControllerRowType.EMPTY:

				if ( !Global.isSet( cell_value ) ) {
					time_span.text( $.i18n._( '' ) );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:
				if ( Global.isSet( item ) ) {

					if ( (!Global.isSet( item.id ) || !item.id) ) {
						time_span.addClass( 'no-id' );
					}

					if ( item.status_id === 20 ) {
						time_span.addClass( 'red' );
					}

					if ( item.user_id === 0 ) {
						content_div.addClass( 'yellow-outline' );
					}

				}

				if ( Global.isSet( cell_value ) ) {
					if ( Global.isSet( item ) && item.note ) {
						cell_value = '*' + cell_value;
					}
					time_span.text( cell_value );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

				} else {
					time_span.text( '' );
				}
				content_div.prepend( time_span );
				break;

		}

		return content_div.get( 0 ).outerHTML;
	},

	buildYearCell: function( cell_value, related_data, row ) {
		var col_models = this.grid.getGridParam( 'colModel' );
		var col_model = related_data.colModel;
		var content_div = $( "<div class='schedule-content-div'></div>" );
		var time_span = $( "<span class='schedule-time'></span>" );
		var item = row[col_model.index + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.EMPTY:
				if ( !Global.isSet( cell_value ) ) {
					time_span.text( $.i18n._( 'E' ) );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:

				if ( col_model.index >= 0 ) {

					if ( cell_value === 'A' ) {
						time_span.addClass( 'absence-cell' )
					} else {
						time_span.addClass( 'shift-cell' )
					}
				}

				if ( Global.isSet( cell_value ) ) {
					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}

				if ( related_data.pos === col_models.length - 1 ) {
					content_div.css( 'padding-right', '15px' );
				}
				content_div.prepend( time_span );
				break;

		}

		return content_div.get( 0 ).outerHTML;
	},

	buildDayCell: function( cell_value, related_data, row ) {
		var $this = this;
		var col_model = related_data.colModel;
		var content_div = $( "<div class='schedule-content-div'></div>" );
		var time_span = $( "<span class='schedule-time'></span>" );
		var item = row[col_model.index + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:

				if ( cell_value && Global.isSet( cell_value.total_time ) ) {
					var total_div = $( "<div  style='text-align: left'></div>" );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences + ' ' + Global.secondToHHMMSS( cell_value.total_time ) + ' = ' + currency + cell_value.total_time_wage );
					content_div.prepend( time_span );

					if ( cell_value.total_shifts_dic ) {
						var shifts_array = [];
						for ( var key in cell_value.total_shifts_dic ) {
							shifts_array.push( cell_value.total_shifts_dic[key] );
						}

						shifts_array = shifts_array.sort( function( a, b ) {

							return a.sort_order - b.sort_order;

						} );

						for ( var i = 0; i < shifts_array.length; i++ ) {
							var item = shifts_array[i];
							var span = $( '<span class="day_hour_span"></span>' );
							span.text( item.value );
							total_div.append( span );
						}
						content_div.prepend( total_div );
					}

					content_div.css( 'height', 'auto' );

				} else if ( cell_value ) {
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( cell_value );
					content_div.prepend( time_span );
				} else if ( Date.parse( col_model.index ) ) {

					currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: 0 A: 0 00:00 = ' + currency + '0.00' );

					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );

				}

				break;
			case ScheduleViewControllerRowType.EMPTY:
				if ( !Global.isSet( cell_value ) ) {
					time_span.text( $.i18n._( '' ) );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:

				if ( Global.isSet( item ) ) {
					content_div.removeClass( 'schedule-content-div' ).addClass( 'schedule-content-day-div' );

					if ( related_data.rowId % 2 === 0 ) {
						time_span.removeClass( 'schedule-time' ).addClass( 'schedule-day-time even' );
					} else {
						time_span.removeClass( 'schedule-time' ).addClass( 'schedule-day-time' );
					}
//
					time_span.width( $this.getDayShiftWidth( item ) );
					time_span.css( 'left', $this.getDayShiftOffset( item ) );
//
					if ( !Global.isSet( item.id ) || !item.id ) {
						time_span.addClass( 'no-day-id' )
					}

					if ( item.status_id === 20 ) {
						time_span.removeClass( 'even' );
						time_span.addClass( 'red-bg' );
					}

					if ( item.user_id === 0 ) {
						content_div.addClass( 'yellow-outline' );
					}
				}

				if ( Global.isSet( cell_value ) ) {
					if ( Global.isSet( item ) && item.note ) {
						cell_value = '*' + cell_value;
					}
					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}
				content_div.prepend( time_span );
				break;

		}

		return content_div.get( 0 ).outerHTML;
	},

	getDayShiftOffset: function( shift ) {

		var start_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.start_date, shift.start_time ) );
		var offset = (start_date_time.getTime() - this.day_mode_start_date_time.getTime()) / 3600000;

		return offset * this.day_hour_width;
	},

	getDayShiftWidth: function( shift ) {
		var start_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.start_date, shift.start_time ) );
		var end_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.end_date, shift.end_time ) );
		var offset = (end_date_time.getTime() - start_date_time.getTime()) / 3600000;

		return offset * this.day_hour_width;
	},

	buildWeekCell: function( cell_value, related_data, row ) {

		var col_models = this.grid.getGridParam( 'colModel' );

		var col_model = related_data.colModel;
		var content_div = $( "<div class='schedule-content-div'></div>" );
		var time_span = $( "<span class='schedule-time'></span>" );
		var item = row[col_model.index + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:
				if ( cell_value && Global.isSet( cell_value.total_time ) ) {
					var total_span = $( "<span class='schedule-time total'></span>" );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences );
					total_span.text( Global.secondToHHMMSS( cell_value.total_time ) + ' = ' + currency + cell_value.total_time_wage );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );

				} else if ( cell_value ) {
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( cell_value );
					content_div.prepend( time_span );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

				} else if ( Date.parse( col_model.index ) ) {

					total_span = $( "<span class='schedule-time total'></span>" );
					currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( "<span class='schedule-time total'></span>" );
					time_span.text( 'S: 0 A: 0' );
					total_span.text( '00:00 = ' + currency + '0.00' );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );

				}

				break;
			case ScheduleViewControllerRowType.EMPTY:
				if ( !Global.isSet( cell_value ) ) {
					time_span.text( $.i18n._( '' ) );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:

				if ( Global.isSet( item ) ) {

					if ( (!Global.isSet( item.id ) || !item.id) ) {
						time_span.addClass( 'no-id' );
					}

					if ( item.status_id === 20 ) {
						time_span.addClass( 'red' );
					}

					if ( item.user_id === 0 ) {
						content_div.addClass( 'yellow-outline' );
					}

				}

				if ( Global.isSet( cell_value ) ) {

					if ( Global.isSet( item ) && item.note ) {
						cell_value = '*' + cell_value;
					}

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}

				content_div.prepend( time_span );

				break;

		}

		return content_div.get( 0 ).outerHTML;
	},

	onCellFormat: function( cell_value, related_data, row ) {
		var $this = this;

		switch ( this.getMode() ) {
			case ScheduleViewControllerMode.WEEK:
				return this.buildWeekCell( cell_value, related_data, row );
				break;
			case ScheduleViewControllerMode.MONTH:
				return this.buildMonthCell( cell_value, related_data, row );
				break;
			case ScheduleViewControllerMode.YEAR:
				return this.buildYearCell( cell_value, related_data, row );
				break;
			case ScheduleViewControllerMode.DAY:
				return this.buildDayCell( cell_value, related_data, row );
				break;

		}

		return '';

	},

	onSelectRow: function( grid_id, row_id, target ) {
		var $this = this;
		var row_tr = $( target ).find( '#' + row_id );
		row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

		var cells_array = $this.select_cells_Array;
		var len = $this.select_cells_Array.length;

		this.select_all_shifts_array = [];
		this.select_shifts_array = [];
		this.select_recurring_shifts_array = [];
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.addClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

			if ( info.shift ) {
				if ( Global.isSet( info.shift.start_date ) ) { //date + time number
					info.shift.start_date_num = Global.strToDateTime( info.shift.start_date ).getTime();

				} else {
					info.shift.start_date_num = info.time_stamp_num; //Uer time_stamp_num from cell select setting, a date number
				}

				info.shift.row_index = info.row_id - 1;
				info.shift.cell_index = info.cell_index - 1;

				this.select_all_shifts_array.push( info.shift );

				if ( info.shift.id ) {
					this.select_shifts_array.push( info.shift )
				} else {
					this.select_recurring_shifts_array.push( info.shift )
				}

				this.select_all_shifts_array.sort( function( a, b ) {

					return Global.compare( a, b, 'start_date_num' );

				} );
			}

		}

		this.setDefaultMenu();

	},

	getCellRelatedDate: function( row_index, col_model, cell_index, data_field ) {

		var date;
		var date_row_1_index = this.schedule_source.indexOf( this.month_date_row_array[0] );
		var date_row_2_index = this.schedule_source.indexOf( this.month_date_row_array[1] );
		var date_row_3_index = this.schedule_source.indexOf( this.month_date_row_array[2] );
		var date_row_4_index = this.schedule_source.indexOf( this.month_date_row_array[3] );

		if ( row_index < date_row_1_index ) {
			date = Global.strToDate( col_model[cell_index].index, this.full_format );
		} else if ( row_index >= date_row_1_index && row_index < date_row_2_index ) {
			date = Global.strToDate( this.month_date_row_array[0][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_2_index && row_index < date_row_3_index ) {
			date = Global.strToDate( this.month_date_row_array[1][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_3_index && row_index < date_row_4_index ) {
			date = Global.strToDate( this.month_date_row_array[2][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_4_index ) {
			date = Global.strToDate( this.month_date_row_array[3][data_field + '_full_date'], this.full_format );
		}

		return date;
	},

	getDataByCellIndex: function( row_index, cell_index ) {
		var $this = this;
		var row = $this.schedule_source[row_index];
		var colModel = $this.grid.getGridParam( 'colModel' );

		//Error: TypeError: row is undefined in https://ondemand2001.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-134330 line 2 > eval line 5952
		//Error: TypeError: colModel[cell_index] is undefined in https://ondemand2001.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-134330 line 2 > eval line 5951
		if ( !colModel || !colModel[cell_index] || !row ) {
			return null;
		}

		var data_field = colModel[cell_index].name;
		var data = row[data_field + '_data'];

		return data;

	},

	onCellSelect: function( grid_id, row_id, cell_index, cell_val, target, e ) {

		if ( cell_index < 0 ) {
			return;
		}

		var $this = this;
		var len = 0;
		var row;
		var colModel;
		var data_field;
		var shift;
		var cells_array = [];
		var date;
		var mode = this.getMode();

		cells_array = $this.select_cells_Array;

		len = $this.select_cells_Array.length;

		row = $this.schedule_source[row_id - 1];

		var row_index = $this.schedule_source.indexOf( row );

		colModel = $this.grid.getGridParam( 'colModel' );

		data_field = colModel[cell_index].name;

		shift = row[data_field + '_data'];

		if ( mode === ScheduleViewControllerMode.MONTH ) {
			date = $this.getCellRelatedDate( row_index, colModel, cell_index, data_field );
		} else {
			date = Global.strToDate( data_field, this.full_format );
		}

		if ( date.getTime() < -1 ) {
			date = new Date();
		}

		//Clean all select cells first
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			var row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', false );
		}

		if ( date ) {
			var date_str = date.format();
			var time_stamp_num = Date.parse( date_str ).getTime();
		} else {
			date_str = '';
			time_stamp_num = 0;
		}

		// Add multiple selection if click cell and hold ctrl or command
		if ( e.ctrlKey || e.metaKey ) {
			var found = false;
			for ( i = 0; i < len; i++ ) {
				info = cells_array[i];
				if ( row_id === info.row_id && cell_index === info.cell_index ) {
					cells_array.splice( i, 1 );
					found = true;
					break;
				}
			}

			if ( !found ) {

				cells_array.push( {
					row_id: row_id,
					cell_index: cell_index,
					cell_val: cell_val,
					shift: shift,
					date: date_str,
					time_stamp_num: time_stamp_num,
					user_id: row.user_id
				} );

				$this.select_cells_Array = cells_array;

				this.select_cells_Array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );

			}
		} else if ( e.shiftKey ) {

			var start_row_index = row_id;
			var start_cell_index = cell_index;

			var end_row_index = row_id;
			var end_cell_index = cell_index;

			for ( i = 0; i < len; i++ ) {
				info = cells_array[i];

				if ( parseInt( info.row_id ) < parseInt( start_row_index ) ) {
					start_row_index = info.row_id;
				} else if ( parseInt( info.row_id ) > parseInt( end_row_index ) ) {
					end_row_index = info.row_id;
				}

				if ( parseInt( info.cell_index ) < parseInt( start_cell_index ) ) {
					start_cell_index = info.cell_index;
				} else if ( parseInt( info.cell_index ) > parseInt( end_cell_index ) ) {
					end_cell_index = info.cell_index;
				}
			}

			start_row_index = parseInt( start_row_index );
			end_row_index = parseInt( end_row_index );
			cells_array = [];

			for ( i = start_row_index; i <= end_row_index; i++ ) {
				var r_index = i;
				for ( var j = start_cell_index; j <= end_cell_index; j++ ) {
					var c_index = j;

					row_tr = $( target ).find( '#' + r_index );

					cell_td = $( row_tr.find( 'td' )[c_index] );

					cell_val = cell_td[0].outerHTML;

					row = $this.schedule_source[r_index - 1];

					row_index = $this.schedule_source.indexOf( row );

					colModel = $this.grid.getGridParam( 'colModel' );

					data_field = colModel[c_index].name;

					shift = row[data_field + '_data'];

					if ( mode === ScheduleViewControllerMode.MONTH ) {
						date = $this.getCellRelatedDate( row_index, colModel, c_index, data_field )
					} else {
						date = Global.strToDate( data_field, this.full_format );
					}

					if ( date && date.getTime() > 0 ) {
						date_str = date.format();
						time_stamp_num = Date.parse( date_str ).getTime();
					} else {
						date_str = '';
						time_stamp_num = 0;
					}

					cells_array.push( {
						row_id: r_index.toString(),
						cell_index: c_index,
						cell_val: cell_val,
						shift: shift,
						date: date_str,
						time_stamp_num: time_stamp_num,
						user_id: row.user_id
					} );

				}
			}

			$this.select_cells_Array = cells_array;

			this.select_cells_Array.sort( function( a, b ) {

				return Global.compare( a, b, 'time_stamp_num' );

			} );

		} else {

			cells_array = [
				{
					row_id: row_id,
					cell_index: cell_index,
					cell_val: cell_val,
					shift: shift,
					date: date_str,
					time_stamp_num: time_stamp_num,
					user_id: row.user_id
				}
			];

			$this.select_cells_Array = cells_array;

			if ( date && date.getYear() > 0 ) {
				this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				this.highLightSelectDay();
			}

		}

		$this.onSelectRow( grid_id, row_id, target );

		var target_row_tr = $( target ).find( '#' + row_id );
		var target_row_td = $( target_row_tr.find( 'td' )[cell_index] );
		if ( target_row_td.attr( 'infor_column' ) ) {
			var target_row_index = target_row_tr.index();
			var rowspan = parseInt( target_row_td.attr( 'rowspan' ) );

			if ( isNaN( rowspan ) ) {
				rowspan = 1;
			}

			var last_row = target_row_tr.parent().children().eq( (target_row_index + rowspan - 1) );
			var last_td = last_row.children().eq( (last_row.children().length - 1) );

			var last_row_id = last_row.attr( 'id' );
			var last_cell_id = (last_row.children().length - 1);
			var last_cell_value = last_td.find( '.schedule-time' ).text();

			$this.onCellSelect( 'timesheet_grid', last_row_id,
				last_cell_id,
				last_cell_value,
				this.grid,
				{shiftKey: true} );

		}

	},

	highLightSelectDay: function() {

		var mode = this.getMode();

		$( '.highlight-header' ).removeClass( 'highlight-header' ); //Clean all hight light header or date row

		if ( mode === ScheduleViewControllerMode.MONTH ) {
			var select_date = Global.strToDate( this.start_date_picker.getValue() );
			var select_day = select_date.getDay(); // column index is day number for month mode
			select_date = select_date.format( this.full_format );

			var header = $( '#' + this.ui_id + '_grid_' + select_day );
			var header_text = $( header.children()[1] ).text();	 //get current column header value

			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date ); //get date row

			if ( this.highlight_header.length !== 1 ) {
				this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_day ); //get header
			} else {
				if ( header_text === this.highlight_header.text() ) {
					$( '.highlight-header' ).removeClass( 'highlight-header' );
					header.addClass( 'highlight-header' );
				}
			}

		} else {
			select_date = Global.strToDate( this.start_date_picker.getValue() );
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date );
		}

		if ( mode !== ScheduleViewControllerMode.DAY ) {

			if ( mode === ScheduleViewControllerMode.MONTH ) {
				this.highlight_header.parent().addClass( 'highlight-header' );
			}

			this.highlight_header.addClass( 'highlight-header' );

		}

	},

	buildAllModeCommonColumns: function() {
		var $this = this;
		this.shift_key_name_array = ['user_id'];
		var display_columns = this.buildDisplayColumns( this.select_layout.data.display_columns )

		var len = display_columns.length;

		if ( this.getMode() !== ScheduleViewControllerMode.YEAR ) {
			for ( var i = 0; i < len; i++ ) {
				var column = display_columns[i];
				var column_info = {
					resizable: false,
					name: column.value,
					index: column.value,
					label: column.label,
					width: 122,
					sortable: false,
					title: false,
					fixed: false,
					formatter: function() {

						return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
					},
					cellattr: function( rowId, tv, rawObject, cm, rdata ) {
						var field_name = cm.index;

						return 'class=' + field_name + '_cell infor_column=true';
					}
				};
				this.schedule_columns.push( column_info );
				this.shift_key_name_array.push( column.value + '_id' );
			}
		}

		var column_width = 122;
//		var set_fixed_width = false;

//		if ( this.schedule_columns.length === 0 ) {
//			column_width = 200;
//		}

		var employee_column = {
			resizable: false,
			fixed: false,
			name: 'user_full_name',
			index: 'user_full_name',
			label: 'Employee',
			width: column_width,
			sortable: false,
			title: false,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
			},
			cellattr: function( rowId, tv, rawObject, cm, rdata ) {
				var field_name = cm.index;
				return 'class=' + field_name + '_cell infor_column=true';
			}
		};

		this.schedule_columns.push( employee_column );
	},

	buildMonthRows: function() {

		var month_days = (this.end_date.getTime() - this.start_date.getTime()) / 86400000 + 1;
		var start_day = new Date( this.start_date.getTime() ).getDay();
		this.schedule_source = [];
		var start_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + 7 ) );

		this.month_date_row_array = [];

		var row_num = 1;
		var z = 0; //day offset

		while ( row_num < 5 ) {
			var current_day = start_day;
			var row = this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.DATE;

			for ( var i = 0; i < 7; i++ ) {
				var current_date = new Date( new Date( start_date.getTime() ).setDate( start_date.getDate() + z ) );

				if ( current_date.getTime() > this.end_date.getTime() ) {
					break
				}

				row[current_day] = current_date.format( this.weekly_format );

				row[current_day] = this.setHolidayHeader( row[current_day] );
				row[current_day + '_full_date'] = current_date.format( this.full_format );
				row[current_day + '_time'] = current_date.getTime();

				current_day = current_day + 1;

				if ( current_day === 7 ) {
					current_day = 0;
				}

				z = z + 1;
			}

			this.month_date_row_array.push( row );
			row_num = row_num + 1;

			this.schedule_source.push( row )

		}

	},

	getEmptyWeeklyRow: function() {
		var row = {};
		row.user_full_name = '';
		row.last_name = '';
		row.user_id = '';
		row.branch_id = '';
		row.department_id = '';
		row.schedule_policy_id = '';
		row.job_id = '';
		row.job_item_id = '';

		var display_columns = this.select_layout.data.display_columns;
		var display_columns_len = display_columns.length;

		for ( var j = 0; j < display_columns_len; j++ ) {
			var field_name = display_columns[j];
			row[field_name] = '';
		}

		return row;
	},

	buildMonthColumns: function() {
		var $this = this;
		this.schedule_columns = [];
		this.buildAllModeCommonColumns();

//		var current_date = new Date( this.start_date.getTime() );

		this.month_date_row_tr_ids = {};
		for ( var i = 0; i < 7; i++ ) {
			var temp_start_date = new Date( this.start_date.getTime() );
			var current_date = new Date( temp_start_date.setDate( temp_start_date.getDate() + i ) );
			var start_day = current_date.getDay();
			var header_text = current_date.format( this.weekly_format );
			var data_field = start_day;
			if ( data_field === 7 ) {
				data_field = 0;
			}

			header_text = this.setHolidayHeader( header_text );

			var full_data_field = current_date.format( this.full_format );

			var column_info = {
				resizable: false,
				name: data_field,
				index: full_data_field,
				label: header_text,
				width: 122,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				cellattr: function( rowId, tv, rawObject, cm, rdata ) {
					if ( rawObject.type === ScheduleViewControllerRowType.DATE ) {
						$this.month_date_row_tr_ids[rowId] = true
					}
				},
				fixed: false
			};
			this.schedule_columns.push( column_info );

		}

		this.buildWeeklyTotalColumns();

		this.buildMonthRows();
	},

	getDayByDayNum: function( day ) {

		var day;
		switch ( day ) {
			case 0:
				day = 'S';
				break;
			case 1:
				day = 'M';
				break;
			case 2:
				day = 'T';
				break;
			case 3:
				day = 'W';
				break;
			case 4:
				day = 'S';
				break;
			case 5:
				day = 'F';
				break;
			case 6:
				day = 'S';
				break;
		}

		return day;

	},

	buildYearColumns: function() {
		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		var current_date = new Date( this.start_date.getTime() );
		var end_date = new Date( this.end_date.getTime() );

		var i = 0;
		while ( current_date.format( this.full_format ) !== end_date.format( this.full_format ) ) {

			current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var header_text = current_date.format( this.weekly_format );
			var data_field = current_date.format( this.full_format );

			var day = this.getDayByDayNum( current_date.getDay() );

			var column_info = {
				name: data_field,
				index: i,
				label: "<span class='year-day'>" + day + "</span>" +
				"<span class='year-date'>" + current_date.getDate() + "</span>",
				width: 20,
				sortable: false,
				title: false,

				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				}
			};
			this.schedule_columns.push( column_info );

			i = i + 1;
		}

		this.buildWeeklyTotalColumns();

	},

	buildDayColumns: function() {

		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		var current_date = new Date( this.start_date.getTime() );
		var header_text = current_date.format( this.weekly_format );
		var data_field = current_date.format( this.full_format );

		var column_info = {
			resizable: false,
			name: data_field,
			index: data_field,
			label: 'daily_header_replace',
			fixed: true,
			width: 122,
			sortable: false,
			title: false,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
			}
		};

		this.schedule_columns.push( column_info );

		column_info = {
			resizable: false,
			name: 'total',
			index: 'total',
			label: 'Total Time',
			width: 122,
			sortable: false,
			title: false,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
			}
		};

		this.schedule_columns.push( column_info );

	},

	buildWeekColumns: function() {

		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		//Error: Uncaught TypeError: Cannot read property 'getTime' of null in https://ondemand1.timetrex.com/interface/html5/#!m=Schedule&date=20141208&mode=week line 6580
		if ( !this.start_date ) {
			return;
		}

		var current_date = new Date( this.start_date.getTime() );
		var end_date = new Date( this.end_date.getTime() );

		var i = 0;
		var set_fixed_width = false;
//		if ( this.schedule_columns.length === 1 ) {
//			set_fixed_width = false;
//		}

		while ( current_date.format( this.full_format ) !== end_date.format( this.full_format ) ) {

			current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );

			var header_text = current_date.format( this.weekly_format );
			var data_field = current_date.format( this.full_format );

			header_text = this.setHolidayHeader( header_text );

			var column_info = {
				resizable: false,
				name: data_field,
				index: data_field,
				label: header_text,
				width: 122,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				fixed: set_fixed_width
			};

			this.schedule_columns.push( column_info );

			i = i + 1;

		}

		this.buildWeeklyTotalColumns();

		return this.schedule_columns;
	},

	setHolidayHeader: function( header_text, inLine ) {

		if ( this.holiday_data_dic ) {
			if ( this.holiday_data_dic[header_text] ) {

				if ( inLine ) {
					header_text = header_text + ' (' + this.holiday_data_dic[header_text].name + ')';
				} else {
					header_text = header_text + '<br>' + this.holiday_data_dic[header_text].name;
				}

			}
		}

		return header_text;

	},

	buildWeeklyTotalColumns: function() {
		var $this = this;
		var show_weekly_total = this.weekly_totals_btn.getValue();

		var is_fixed = false;

		if ( show_weekly_total ) {
			var shifts_column = {
				name: 'shifts',
				index: 'shifts',
				label: 'Shifts',
				width: 50,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				fixed: is_fixed
			};

			var absences_column = {
				name: 'absences',
				index: 'absences',
				label: 'Absences',
				width: 70,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				fixed: is_fixed
			};

			var total_time = {
				name: 'total_time',
				index: 'total_time',
				label: 'Total Time',
				width: 70,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				fixed: is_fixed
			};

			var total_time_wage = {
				name: 'total_time_wage',
				index: 'total_time_wage',
				label: 'Wages',
				width: 90,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] )
				},
				fixed: is_fixed
			};

			this.schedule_columns.push( shifts_column );
			this.schedule_columns.push( absences_column );
			this.schedule_columns.push( total_time );
			this.schedule_columns.push( total_time_wage );
		}
	},

	buildScheduleColumns: function() {
		this.shift_key_name_array = ['user_id'];
		this.schedule_columns = [];

		var mode = this.getMode();

		switch ( mode ) {
			case ScheduleViewControllerMode.WEEK:
				this.buildWeekColumns();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.buildMonthColumns();
				break;
			case ScheduleViewControllerMode.YEAR:
				this.buildYearColumns();
				break;
			case ScheduleViewControllerMode.DAY:
				this.buildDayColumns();
				break;
		}

	},

	setScheduleGridRowSpan: function() {

		var $this = this;

		var display_columns = this.select_layout.data.display_columns;

		var display_columns_len = display_columns.length;

		for ( var i = 0; i < display_columns_len; i++ ) {
			var column_name = display_columns[i];

			startSet( column_name );
		}

		startSet( 'user_full_name' );

		function startSet( key ) {
			var cells = $this.grid.find( '.' + key + '_cell' );

			var len = cells.length;

			var count = 0;

			var last_val = null;

			var last_cell = null;

			var need_remove_cells = [];

			var start = len - 1;

			setRows();

			function setRows() {
				for ( var i = start; i >= 0; i-- ) {
					var cell = $( cells[i] );
					var cell_val = $( cells[i] ).children().eq( 0 ).children().eq( 0 ).text();

					if ( i === len - 1 ) {
						last_val = cell_val;
						count = count + 1;
						last_cell = cell;
					} else if ( last_val !== cell_val ) {
						last_val = cell_val;
						last_cell.attr( 'rowspan', count );

						for ( var j = 0; j < need_remove_cells.length; j++ ) {
							var need_removed_cell = need_remove_cells[j];
//							need_removed_cell.addClass( 'need-remove' );
							var node = need_removed_cell[0];
							if ( node.parentNode ) {
								node.style.display = 'none';
							}

						}

						need_removed_cell = [];
						count = 1;
						last_cell = cell;

					} else if ( i === 0 ) {
						count = count + 1;
						need_remove_cells.push( last_cell );
						if ( count > 1 ) {
							cell.attr( 'rowspan', count );
							for ( j = 0; j < need_remove_cells.length; j++ ) {
								need_removed_cell = need_remove_cells[j];
//								need_removed_cell.addClass( 'need-remove' );
								node = need_removed_cell[0];
								if ( node.parentNode ) {
									node.style.display = 'none';
								}

							}
						}

					} else {
						count = count + 1;
						need_remove_cells.push( last_cell );
						last_cell = cell;
					}

				}

//				var removed_cells = $this.grid.find( '.need-remove' );
//				var len_1 = removed_cells.length;
//
//				var removed_start = len_1 - 1;

//				removeRows();

//				function removeRows() {
//
//					for ( var y = removed_start; y >= 0; y-- ) {
//						var removed_cell = removed_cells[y];
//
//						if ( removed_cell ) {
//							removed_cell.style.display = 'none';
//						}
//
////						if ( y > 0 && y % 10 === 0 ) {
////							removed_start = y - 1;
////							sleep();
////							break;
////
////						}
//
//					}
//				}

//				function sleep() {
//					setTimeout( function() {
//						removeRows()
//					}, 100 )
//				}

			}

		}

	},

	onSetSearchFilterFinished: function() {

	},

	onBuildBasicUIFinished: function() {
	},

	onBuildAdvUIFinished: function() {

	},

	events: {},

	setDateUrl: function() {
		var $this = this;
		if ( !$this.edit_view ) {

			var mode = this.getMode();
			var default_date = $this.start_date_picker.getDefaultFormatValue();

			if ( mode ) {
				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&mode=' + mode;
			} else {

				if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.mode ) {
					$this.setToggleButtonValue( LocalCacheData.all_url_args.mode );
					mode = this.getMode();
					window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&mode=' + mode;
				} else {
					window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date;
				}
			}

		}
	},

	reSetURL: function() {
		var mode = this.getMode();
		if ( mode ) {
			window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode;
		} else {
			window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue();
		}

		LocalCacheData.all_url_args = null;
	},

	setURL: function() {
		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}

		if ( this.canSetURL() ) {
			var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.edit_view_tab_selected_index ).text() : '';
			tab_name = tab_name.replace( /\/|\s+/g, '' );

			var mode = this.getMode();

			if ( this.current_edit_record && this.current_edit_record.id ) {
				if ( a ) {

					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + a + '&id=' + this.current_edit_record.id +
					'&tab=' + tab_name );

				} else {
					Global.setURLToBrowser( window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&id=' + this.current_edit_record.id );

				}

				Global.trackView( this.viewId, LocalCacheData.current_doing_context_action );

			} else {
				if ( a ) {

					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + 'new' +
						'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + a +
						'&tab=' + tab_name );
					}

				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode );
				}
			}

		}

	},

	render: function() {
		var $this = this;
		this._super( 'render' );

		var control_bar = $( this.el ).find( '.control-bar' );
		var date_chooser_div = control_bar.find( '.date-chooser-div' );
		var action_chooser_div = control_bar.find( '.action-chooser-div' );

		//Create Start Date Picker
		this.start_date_picker = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		this.start_date_picker.TDatePicker( {field: 'start_date'} );
		var date_chooser = $( "<span class='label'>" + $.i18n._( 'Date' ) + ":</span>" +
		"<img class='left-arrow arrow' src=" + Global.getRealImagePath( 'images/left_arrow.png' ) + ">" +
		"<div class='date-picker-div'></div>" +
		"<img class='right-arrow arrow' src=" + Global.getRealImagePath( 'images/right_arrow.png' ) + ">" );

		date_chooser_div.append( date_chooser );
		date_chooser_div.find( '.date-picker-div' ).append( this.start_date_picker );

		var date_left_arrow = date_chooser_div.find( '.left-arrow' );
		var date_right_arrow = date_chooser_div.find( '.right-arrow' );

		date_left_arrow.bind( 'click', function() {

			var mode = $this.getMode();

			var select_date = $this.start_date;
			var new_date;

			if ( !select_date ) {
				return;
			}

			switch ( mode ) {
				case ScheduleViewControllerMode.DAY:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
					break;
				case ScheduleViewControllerMode.WEEK:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 7 ) );
					break;
				case ScheduleViewControllerMode.MONTH:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
					break;
				case ScheduleViewControllerMode.YEAR:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 32 ) );
					break;

			}
			$this.setDatePickerValue( new_date.format() );
			$this.setDateUrl();

			$this.search( false, true );

		} );

		date_right_arrow.bind( 'click', function() {

			var mode = $this.getMode();
			var select_date = $this.end_date;
			var new_date;
			if ( !select_date ) {
				return;
			}

			switch ( mode ) {
				case ScheduleViewControllerMode.DAY:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
					break;
				case ScheduleViewControllerMode.WEEK:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
					break;
				case ScheduleViewControllerMode.MONTH:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
					break;
				case ScheduleViewControllerMode.YEAR:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
					break;

			}

			$this.setDatePickerValue( new_date.format() );
			$this.setDateUrl();

			$this.search( false, true );

		} );

		this.start_date_picker.bind( 'formItemChange', function() {
			$this.setDateUrl();
			$this.search( false, true );
		} );

		//Create action switch buttons
		this.all_employee_btn = action_chooser_div.find( '#all_employee' );
		this.daily_totals_btn = action_chooser_div.find( '#daily_totals' );
		this.weekly_totals_btn = action_chooser_div.find( '#weekly_totals' );
		this.strict_range_btn = action_chooser_div.find( '#strict_range' );

		this.all_employee_btn = this.all_employee_btn.SwitchButton( {
			icon: SwitchButtonIcon.all_employee,
			tooltip: $.i18n._( 'Show Unscheduled Employees' )
		} );
		this.daily_totals_btn = this.daily_totals_btn.SwitchButton( {
			icon: SwitchButtonIcon.daily_total,
			tooltip: $.i18n._( 'Daily Totals' )
		} );
		this.weekly_totals_btn = this.weekly_totals_btn.SwitchButton( {
			icon: SwitchButtonIcon.weekly_total,
			tooltip: $.i18n._( 'Weekly Totals' )
		} );
		this.strict_range_btn = this.strict_range_btn.SwitchButton( {
			icon: SwitchButtonIcon.strict_range,
			tooltip: $.i18n._( 'Strict Range' )
		} );

		this.all_employee_btn.click( function() {
			$this.onShowEmployeeClick();
		} );

		this.daily_totals_btn.click( function() {
			$this.onDailyTotalsClick();
		} );

		this.weekly_totals_btn.click( function() {

			$this.onWeeklyTotalClick();
		} );

		this.strict_range_btn.setValue( true );

		this.strict_range_btn.click( function() {
			$this.onStrictRangeClick();
		} );

		//Create weekly toggle buttons

		this.toggle_button = $( this.el ).find( '.toggle-button-div' );
		var data_provider = [
			{label: $.i18n._( 'Day' ), value: 'day'},
			{label: $.i18n._( 'Week' ), value: 'week'},
			{label: $.i18n._( 'Month' ), value: 'month'},
			{label: $.i18n._( 'Year' ), value: 'year'}
		];

		this.toggle_button = this.toggle_button.TToggleButton( {data_provider: data_provider} );

		this.toggle_button.bind( 'change', function( e, result ) {
			$this.scroll_position = 0;
			$this.select_all_shifts_array = [];
			$this.select_shifts_array = [];
			$this.select_recurring_shifts_array = [];

			$this.setToggleButtonUrl();
			$this.search( true, true );

		} );

	},

	onShowEmployeeClick: function() {
		this.search();
	},

	onStrictRangeClick: function() {
		this.search( false, true );
	},

	onWeeklyTotalClick: function() {

		if ( !this.checkScheduleData() ) {
			return;
		}

		this.buildCalendars();
	},

	checkScheduleData: function() {
		if ( this.full_schedule_data === true ) {
			return false
		}

		return true;
	},

	onDailyTotalsClick: function() {

		var mode = this.getMode();

		//Error: Uncaught TypeError: Cannot call method 'clearGridData' of null in https://ondemand1.timetrex.com/interface/html5/index.php?desktop=1#!m=Schedule&date=20150118&mode=week line 6944
		if ( !this.checkScheduleData() || !this.grid ) {
			return;
		}

		switch ( mode ) {
			case ScheduleViewControllerMode.MONTH:
				this.buildMonthColumns(); // recreate columns to get date row ids correct
				this.buildScheduleSource();
				break;
			default:
				this.buildScheduleSource();
				break;
		}

		this.grid.clearGridData();
		this.grid.setGridParam( {data: this.schedule_source} );
		this.grid.trigger( 'reloadGrid' );
		this.setScheduleGridRowSpan();
		this.setMonthDateRowPosition();

	},

	showWeeklyTotal: function() {
		var show_weekly_total = this.weekly_totals_btn.getValue();

		if ( !show_weekly_total ) {
			return;
		}

		var shifts = 0;
		var absences = 0;
		var total_time = 0;
		var total_wage = 0;
//		  var is_date_row = false;
//		  var current_key = [];

		var mode = this.getMode();

		var len = this.schedule_source.length;

		for ( var i = 0; i < len; i++ ) {
			var row = this.schedule_source[i];

			if ( row.type === ScheduleViewControllerRowType.DATE ) {
				continue;
			}

			for ( var key in row ) {

				var data = Global.isSet( row[key] ) ? row[key] : '';

				if ( Global.isSet( data.user_id ) || (Global.isArray( data ) && mode === ScheduleViewControllerMode.YEAR) ) {

					if ( mode === ScheduleViewControllerMode.YEAR ) {
						var data_len = data.length;
						for ( var j = 0; j < data_len; j++ ) {
							var item = data[j];

							if ( Global.isSet( item.user_id ) ) {
								total_time = item.total_time + total_time;
								total_wage = parseFloat( item.total_time_wage ) + total_wage;

								if ( item.status_id === 10 ) {
									shifts = shifts + 1;
								} else {
									absences = absences + 1;
								}
							}

						}
					} else {
						total_time = data.total_time + total_time;
						total_wage = parseFloat( data.total_time_wage ) + total_wage;

						if ( data.status_id === 10 ) {
							shifts = shifts + 1;
						} else {
							absences = absences + 1;
						}
					}

				} else if ( Global.isSet( data.shifts ) ) {
					total_time = data.total_time + total_time;
					total_wage = parseFloat( data.total_time_wage ) + total_wage;

					shifts = shifts + data.shifts;
					absences = absences + data.absences;
				} else if ( row.type !== ScheduleViewControllerRowType.DATE ) {

				} else {
//					  current_key[key] = row[key];
				}
			}

			row.total_time = Global.secondToHHMMSS( total_time );
			row.total_time_wage = LocalCacheData.getCurrentCurrencySymbol() + total_wage.toFixed( 2 );
			//ViewManagerUtil.getTimeUnit(totalTime);
			row.shifts = shifts;
			row.absences = absences;

			total_time = 0;
			total_wage = 0;
			shifts = 0;
			absences = 0;
		}

	},

	buildTotalShiftsValues: function( total_shifts_dic, currentItem ) {
		var start_date = Global.strToDateTime( currentItem.start_date );
		var end_date = Global.strToDateTime( currentItem.end_date );
		var start_time_min = start_date.getMinutes();
		var end_time_min = end_date.getMinutes();

		start_date.setMinutes( 0 );
		end_date.setMinutes( 0 );

		var start_time = start_date.format( 'hh:mm A' );
		var end_time = end_date.format( 'hh:mm A' );

//		var start_time_number = parseInt( start_time.split( ":" )[ 0 ] );
//		var end_time_number = parseInt( end_time.split( ":" )[ 0 ] );

		var time_offset = Math.ceil( getTimeOffset( start_date, end_date ) / 60 / 60 );

		var rest_time;
		if ( time_offset < 1 ) {

			total_shifts_dic[end_time].value = total_shifts_dic[end_time].value + Number( ( end_time_min / 60 ).toFixed( 0 ) )
		} else {
			if ( start_time_min == 0 ) {

				total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1;
			} else {
				total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + Number( ( ( 60 - start_time_min ) /
				60 ).toFixed( 2 ) );
			}

			for ( var i = 1; i < time_offset; i++ ) {
				start_date.setHours( start_date.getHours() + 1 );
				start_time = start_date.format( 'hh:mm A' );

				if ( !total_shifts_dic.hasOwnProperty( start_time ) )
					continue;

				if ( i == time_offset - 1 ) {
					if ( end_time_min > 0 ) {
						total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + Number( ( end_time_min /
						60 ).toFixed( 2 ) )
					} else {
						total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1
					}
				} else {
					total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1
				}

			}

		}

		function getTimeOffset( startDate, endDate ) {
			if ( !startDate ) {
				startDate = new Date();
			}
			var sec = (endDate.getTime() - startDate.getTime()) / 1000;
			return sec.toFixed( 0 );
		}

	},

	showDailyTotal: function() {
		var show_daily_total = this.daily_totals_btn.getValue();

		if ( !show_daily_total ) {
			return;
		}

		var start = true;
		var total_row = null;
		var column_keys = [];
		var over_all_total_row = {};

		var display_columns = this.select_layout.data.display_columns;
		var display_columns_len = display_columns.length;

		for ( var i = 0; i < display_columns_len; i++ ) {
			var column_name = display_columns[i];

			column_keys.push( {key: column_name, row: null, value: null} );

		}

		var column_keys_len = column_keys.length;

		for ( i = 0; i < this.schedule_source.length; i++ ) {
			var row = this.schedule_source[i];
			if ( start ) {

				for ( var j = 0; j < column_keys_len; j++ ) {
					var column_key = column_keys[j];
					total_row = {type: ScheduleViewControllerRowType.TOTAL};
					total_row[column_key.key] = 'Totals';
					column_key.row = total_row;
					column_key.value = row[column_key.key];

				}

				over_all_total_row = {type: ScheduleViewControllerRowType.TOTAL};
				over_all_total_row.user_full_name = 'Overall Totals'
			}

			for ( var y = column_keys_len - 1; y >= 0; y-- ) {

				column_key = column_keys[y];

				if ( ( row[column_key.key] !== column_key.value && i > 0 ) && !start && i !== 0 ) {

					this.schedule_source.splice( i, 0, column_key.row );
					i = i + 1;
					total_row = {type: ScheduleViewControllerRowType.TOTAL};
					total_row[column_key.key] = 'Totals';
					column_key.row = total_row;
					column_key.value = row[column_key.key];
				}

			}

			if ( start ) {
				start = false;
			}
			if ( row.type === ScheduleViewControllerRowType.DATE ) { //do not calculate date row
				this.schedule_source.splice( i, 0, over_all_total_row );
				over_all_total_row = {type: ScheduleViewControllerRowType.TOTAL};
				over_all_total_row.user_full_name = 'Overall Totals';
				i = i + 1;

				continue;
			}

			for ( var key in row ) {

				var data = Global.isSet( row[key] ) ? row[key] : '';
				for ( var x = 0; x < column_keys_len; x++ ) {

					column_key = column_keys[x];
					total_row = column_key.row;

					//Total rows for each columns
					if ( data && Global.isSet( data.user_id ) ) {

						var no_data_key = key.replace( '_data', '' );

						var total_row_key_data = total_row[no_data_key];

						if ( !total_row_key_data || !Global.isSet( total_row_key_data.total_time ) ) {
							total_row[no_data_key] = {};
							total_row_key_data = total_row[no_data_key];
							total_row_key_data.total_time = 0;
							total_row_key_data.total_shifts_dic = Global.clone( this.total_shifts_dic );
						}

						if ( !Global.isSet( total_row_key_data.shifts ) ) {
							total_row_key_data.shifts = 0;
						}

						if ( !Global.isSet( total_row_key_data.absences ) ) {
							total_row_key_data.absences = 0;
						}

						if ( !Global.isSet( total_row_key_data.total_time_wage ) ) {
							total_row_key_data.total_time_wage = 0;
						}

						var row_data = row[no_data_key + '_data'];
						total_row_key_data.total_time = row_data.total_time + total_row_key_data.total_time;

						total_row_key_data.total_time_wage = parseFloat( parseFloat( row_data.total_time_wage ) + parseFloat( total_row_key_data.total_time_wage ) ).toFixed( 2 );
//
						if ( row_data.status_id === 10 ) {
							total_row_key_data.shifts = total_row_key_data.shifts + 1;
							if ( this.getMode() === ScheduleViewControllerMode.DAY ) {
								this.buildTotalShiftsValues( total_row_key_data.total_shifts_dic, row[key] );
							}

						} else if ( row_data.status_id === 20 ) {
							total_row_key_data.absences = total_row_key_data.absences + 1;
						}

					}

				}

				//Total rows for all employees

				if ( data && Global.isSet( data.user_id ) ) {

					no_data_key = key.replace( '_data', '' );

					total_row_key_data = over_all_total_row[no_data_key];

					if ( !total_row_key_data || !Global.isSet( total_row_key_data.total_time ) ) {
						over_all_total_row[no_data_key] = {};
						total_row_key_data = over_all_total_row[no_data_key];
						total_row_key_data.total_time = 0;
						total_row_key_data['total_shifts_dic'] = Global.clone( this.total_shifts_dic );
					}

					if ( !Global.isSet( total_row_key_data.shifts ) ) {
						total_row_key_data.shifts = 0;
					}

					if ( !Global.isSet( total_row_key_data.absences ) ) {
						total_row_key_data.absences = 0;
					}

					if ( !Global.isSet( total_row_key_data.total_time_wage ) ) {
						total_row_key_data.total_time_wage = 0;
					}

					row_data = row[no_data_key + '_data'];
					total_row_key_data.total_time = row_data.total_time + total_row_key_data.total_time;

					total_row_key_data.total_time_wage = parseFloat( parseFloat( row_data.total_time_wage ) + parseFloat( total_row_key_data.total_time_wage ) ).toFixed( 2 );
					if ( row_data.status_id === 10 ) {
						total_row_key_data.shifts = total_row_key_data.shifts + 1;
						if ( this.getMode() === ScheduleViewControllerMode.DAY ) {
							this.buildTotalShiftsValues( total_row_key_data.total_shifts_dic, row[key] );
						}
					} else if ( row_data.status_id === 20 ) {
						total_row_key_data.absences = total_row_key_data.absences + 1;
					}

				}
			}

			if ( i === this.schedule_source.length - 1 ) {
				for ( j = column_keys.length - 1; j >= 0; j-- ) {
					this.schedule_source.push( column_keys[j].row )
				}
				this.schedule_source.push( over_all_total_row );
				break;

			}

		}

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		var $this = this;

		var default_args = {permission_section: 'punch'};
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Schedule Branch' ),
				in_column: 1,
				field: 'schedule_branch_ids',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Schedule Department' ),
				in_column: 1,
				field: 'department_ids',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 1,
				field: 'default_branch_ids',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 1,
				field: 'default_department_ids',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_ids',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 2,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: ALayoutIDs.JOB,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJob' )) : null,
				multiple: true,
				basic_search: false,
				adv_search: (this.show_job_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: ALayoutIDs.JOB_ITEM,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJobItem' )) : null,
				multiple: true,
				basic_search: false,
				adv_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Include Employees' ),
				in_column: 3,
				field: 'include_user_ids',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				default_args: default_args,
				addition_source_function: (function( target, source_data ) {
					return $this.onEmployeeSourceCreate( target, source_data );
				}),
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Exclude Employees' ),
				in_column: 3,
				field: 'exclude_user_ids',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				default_args: default_args,
				addition_source_function: (function( target, source_data ) {
					return $this.onEmployeeSourceCreate( target, source_data );
				}),
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	},

	onEmployeeSourceCreate: function( target, source_data ) {

		if ( !this.checkOpenPermission() ) {
			return source_data;
		}

		var display_columns = target.getDisplayColumns();

		var first_item = {};
		$.each( display_columns, function( index, content ) {

			first_item.id = '0';
			first_item[content.name] = Global.open_item;

			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in https://ondemand1.timetrex.com/interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}
		source_data.unshift( first_item );

		return source_data;
	},

	addOPenField: function( target, source_data ) {
		var open_field = {};

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#schedule_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	}

} );

var ScheduleViewControllerRowType = function() {

}

ScheduleViewControllerRowType.TOTAL = 1;
ScheduleViewControllerRowType.DATE = 2;
ScheduleViewControllerRowType.EMPTY = 3;

var ScheduleViewControllerMode = function() {

}

ScheduleViewControllerMode.DAY = 'day';
ScheduleViewControllerMode.WEEK = 'week';
ScheduleViewControllerMode.MONTH = 'month';
ScheduleViewControllerMode.YEAR = 'year';

ScheduleViewController.loadView = function( container ) {

	Global.loadViewSource( 'Schedule', 'ScheduleView.css' );

	Global.loadViewSource( 'Schedule', 'ScheduleView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( container ) ) {
			container.html( template );
		} else {
			Global.contentContainer().html( template );
		}

	} );

}