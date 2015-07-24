TimeSheetViewController = BaseViewController.extend( {

	el: '#timesheet_view_container', //Must set el here and can only set string, so events can work
	status_array: null,
	type_array: null,

	employee_nav: null,

	start_date_picker: null,

	full_timesheet_data: null, //full timesheet data

	full_format: 'ddd-MMM-DD-YYYY',

	weekly_format: 'ddd, MMM DD',

	start_date: null,

	end_date: null,

	select_cells_Array: [], //Timesheet grid

	select_punches_array: [], //Timesheet grid.

	absence_select_cells_Array: [], //Absence grid

	accumulated_time_cells_array: [],

	timesheet_data_source: null,

	accumulated_time_source: null,

	accumulated_time_grid: null,

	accumulated_time_source_map: null,

	branch_grid: null,

	branch_source_map: null,

	branch_source: null,

	department_grid: null,

	department_source_map: null,

	department_source: null,

	job_grid: null,

	job_source_map: null,

	job_source: null,

	job_item_grid: null,

	job_item_source_map: null,

	job_item_source: null,

	premium_grid: null,

	premium_source_map: null,

	premium_source: null,

	absence_grid: null,

	absence_source: null,

	absence_original_source: null,

	accumulated_total_grid: null,

	accumulated_total_grid_source_map: null,

	accumulated_total_grid_source: null,

	punch_note_grid: null,

	punch_note_grid_source: null,

	verification_grid: null,

	verification_grid_source: null,

	grid_dic: null,

	pay_period_map: null,

	pay_period_data: null,

	timesheet_verify_data: null,

	api_timesheet: null,

	api_user_date_total: null,

	api_date: null,

	api_station: null,

	absence_model: false,

	select_drag_menu_id: '', //Do drag move or copy

	is_mass_adding: false,

	department_cell_count: 0,

	branch_cell_count: 0,

	premium_cell_count: 0,

	job_cell_count: 0,

	task_cell_count: 0,

	absence_cell_count: 0,

	punch_note_account: 0,

	show_navigation_box: true,

	station: null,

	scroll_position: 0,

	job_api: null,
	job_item_api: null,

	api_absence_policy: null,

	pre_total_time: null,

	absence_available_balance_dataList: {},

	available_balance_info: null,

	show_job_ui: false,
	show_job_item_ui: false,
	show_branch_ui: false,
	show_department_ui: false,
	show_good_quantity_ui: false,
	show_bad_quantity_ui: false,
	show_note_ui: false,
	show_station_ui: false,

	show_absence_job_ui: false,
	show_absence_job_item_ui: false,
	show_absence_branch_ui: false,
	show_absence_department_ui: false,

	holiday_data_dic: {},

	grid_div: null,

	actual_time_label: null,

	column_maps: null,

	accmulated_order_map: {},

	url_args_before_set_date_url: {},

	initialize: function() {

		this._super( 'initialize' );
		this.permission_id = 'punch';
		this.viewId = 'TimeSheet';
		this.script_name = 'TimeSheetView';
		this.context_menu_name = $.i18n._( 'TimeSheet' );
		this.navigation_label = $.i18n._( 'TimeSheet' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
		this.api_timesheet = new (APIFactory.getAPIClass( 'APITimeSheet' ))();
		this.api_user_date_total = new (APIFactory.getAPIClass( 'APIUserDateTotal' ))();
		this.api_date = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.api_station = new (APIFactory.getAPIClass( 'APIStation' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
		}
		this.api_absence_policy = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
//		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;

		this.scroll_position = 0;

		this.grid_dic = {};
		this.initPermission();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

//	getRightClickMenuItems: function() {
//
//		var $this = this;
//		var items = {};
//		var len = this.context_menu_array.length;
//		for ( var i = 0; i < len; i++ ) {
//			var context_btn = this.context_menu_array[i];
//			var label = context_btn.text();
//
//			label = this.replaceRightClickLabel(label);
//
//
//			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
//
//			if ( context_btn.hasClass( 'invisible-image' ) ) {
//				continue;
//			}
//
//			items[id] = {name: label, icon: id, disabled: function( key ) {
//				return $this.getContextIconByName( key );
//			}};
//
//			if ( id === ContextMenuIconName.cancel ) {
//				items['sepl'] = '---------'
//			}
//
//		}
//
//		return items;
//	},

	onSubViewRemoved: function() {
		this.search();

		if ( !this.edit_view ) {
			this.setDefaultMenu();
		} else {
			this.setEditMenu();
		}

	},

	setScrollPosition: function() {
		if ( this.scroll_position > 0 ) {
			this.grid_div.scrollTop( this.scroll_position );
		}
	},

	jobUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( "job", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	//Refresh to clear warnning messages after saving from employee edit view
	updateSelectUserAndRefresh: function( new_item ) {

		this.employee_nav.updateSelectItem( new_item );

		this.search();
	},

	branchUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	noteUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	stationValidate: function() {
		if ( PermissionManager.validate( 'station', 'enabled' ) ) {
			return true;
		}
		return false;
	},

	/* jshint ignore:start */
	//Speical permission check for views, need override
	initPermission: function() {
		this._super( 'initPermission' );

		if ( !PermissionManager.validate( 'punch', 'view' ) && !PermissionManager.validate( 'punch', 'view_child' ) ) {
			this.show_navigation_box = false;
			this.show_search_tab = false;
		} else {
			this.show_navigation_box = true;
			this.show_search_tab = true;
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

		if ( this.goodQuantityUIValidate() ) {
			this.show_good_quantity_ui = true;
		} else {
			this.show_good_quantity_ui = false;
		}

		if ( this.badQuantityUIValidate() ) {
			this.show_bad_quantity_ui = true;
		} else {
			this.show_bad_quantity_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_note_ui = true;
		} else {
			this.show_note_ui = false;
		}

		if ( this.stationValidate() ) {
			this.show_station_ui = true;
		} else {
			this.show_station_ui = false;
		}

		if ( this.jobUIValidate( 'absence' ) ) {
			this.show_absence_job_ui = true;
		} else {
			this.show_absence_job_ui = false;
		}

		if ( this.jobItemUIValidate( 'absence' ) ) {
			this.show_absence_job_item_ui = true;
		} else {
			this.show_absence_job_item_ui = false;
		}

		if ( this.branchUIValidate( 'absence' ) ) {
			this.show_absence_branch_ui = true;
		} else {
			this.show_absence_branch_ui = false;
		}

		if ( this.departmentUIValidate( 'absence' ) ) {
			this.show_absence_department_ui = true;
		} else {
			this.show_absence_department_ui = false;
		}

	},
	/* jshint ignore:end */

	ownerOrChildPermissionValidate: function( p_id, permission_name, selected_item ) {
		var field;
		if ( permission_name && permission_name.indexOf( 'child' ) > -1 ) {
			field = 'is_child';
		} else {
			field = 'is_owner';
		}

		var user = this.getSelectEmployee( true );

		if ( PermissionManager.validate( p_id, permission_name ) && (!user || !Global.isSet( user[field] ) || ( user && user[field] ) ) ) {
			return true;
		}

		return false;
	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );

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
			label: $.i18n._( 'New<br>Punch' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var add_absence = new RibbonSubMenu( {
			label: $.i18n._( 'New<br>Absence' ),
			id: ContextMenuIconName.add_absence,
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

		var drag_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.drag_copy,
			group: drag_and_drop_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var edit_accumulated_time = new RibbonSubMenu( {
			label: $.i18n._( 'Accumulated<br>Time' ),
			id: ContextMenuIconName.accumulated_time,
			group: navigation_group,
			icon: Icons.timesheet,
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

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedules' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var pay_stub_view = new RibbonSubMenu( {
			label: $.i18n._( 'Pay<br>Stubs' ),
			id: ContextMenuIconName.pay_stub,
			group: navigation_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var edit_pay_period = new RibbonSubMenu( {
			label: $.i18n._( 'Edit Pay<br>Period' ),
			id: ContextMenuIconName.edit_pay_period,
			group: navigation_group,
			icon: Icons.pay_period,
			permission_result: true,
			permission: null
		} );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) ) {
			var map = new RibbonSubMenu( {
				label: $.i18n._( 'Map' ),
				id: ContextMenuIconName.map,
				group: navigation_group,
				icon: Icons.map,
				permission_result: true,
				permission: null
			} );
		}

		var re_cal_timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'ReCalculate<br>TimeSheet' ),
			id: ContextMenuIconName.re_calculate_timesheet,
			group: other_group,
			icon: Icons.re_cal_timesheet,
			permission_result: true,
			permission: null
		} );

		var generate_pay_stub = new RibbonSubMenu( {
			label: $.i18n._( 'Generate<br>Pay Stub' ),
			id: ContextMenuIconName.generate_pay_stub,
			group: other_group,
			icon: Icons.re_cal_pay_stub,
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

		var summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Summary' ),
			id: 'print_summary',
			nav: print
		} );

		var detail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Detailed' ),
			id: 'print_detailed',
			nav: print
		} );

		return [menu];

	},

	openEditView: function() {
		if ( !this.edit_view ) {
			this.initEditViewUI( 'TimeSheet', 'TimeSheetEditView.html' );
		}

		this.setEditViewWidgetsMode();
	},

	/* jshint ignore:start */
	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {

		var did_clean = false;
		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];

			var column = widget.parent().parent().parent();
			if ( !column.hasClass( 'v-box' ) ) {

				if ( !did_clean ) {
					column.find( '.edit-view-form-item-label-div-first-row' ).removeClass( 'edit-view-form-item-label-div-first-row' );
					column.find( '.edit-view-form-item-label-div-last-row' ).removeClass( 'edit-view-form-item-label-div-last-row' );
					column.find( '.edit-view-form-item-div-last-row' ).removeClass( 'edit-view-form-item-div-last-row' );
					did_clean = true;
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

			widget.setValue( '' );

			if ( this.absence_model ) {

				switch ( key ) {
					case 'punch_date':
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'quantity':
					case 'station_id':
					case 'has_image':
						widgetContainer.css( 'display', 'none' );
						widget.css( 'opacity', 0 );
						break;

					case 'punch_dates':
						if ( this.is_mass_adding ) {
							widgetContainer.css( 'display', 'block' );
							widget.css( 'opacity', 1 );
							break;
						} else {
							widgetContainer.css( 'display', 'none' );
							widget.css( 'opacity', 0 );
							break;
						}
						break;
					case 'date_stamp':
						if ( this.is_mass_adding ) {
							widgetContainer.css( 'display', 'none' );
							widget.css( 'opacity', 0 );
							break;
						} else {
							widgetContainer.css( 'display', 'block' );
							widget.css( 'opacity', 1 );
							break;
						}
						break;
					case 'total_time':
					case 'src_object_id':
					case 'override':
						widgetContainer.css( 'display', 'block' );
						widget.css( 'opacity', 1 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

				}

			} else {

				switch ( key ) {
					case 'punch_dates':
						if ( this.is_mass_adding ) {
							widgetContainer.css( 'display', 'block' );
							widget.css( 'opacity', 1 );
							break;
						} else {
							widgetContainer.css( 'display', 'none' );
							widget.css( 'opacity', 0 );
							break;
						}
						break;
					case 'punch_date':
						if ( this.is_mass_adding ) {
							widgetContainer.css( 'display', 'none' );
							widget.css( 'opacity', 0 );
							break;
						} else {
							widgetContainer.css( 'display', 'block' );
							widget.css( 'opacity', 1 );
							break;
						}
						break;
					case 'quantity':

						if ( this.show_good_quantity_ui && this.show_bad_quantity_ui ) {
							widgetContainer.css( 'display', 'block' );
							widget.css( 'opacity', 1 );
						}
						break;
					case 'station':
						widgetContainer.css( 'display', 'block' );
						widget.css( 'opacity', 1 );
						break;
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'has_image':
						widgetContainer.css( 'display', 'block' );
						widget.css( 'opacity', 1 );
						break;
					case 'date_stamp':
					case 'total_time':
					case 'src_object_id':
					case 'override':
						widgetContainer.css( 'display', 'none' );
						widget.css( 'opacity', 0 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

				}
			}

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

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_punch': this.absence_model ? $.i18n._( 'Absence' ) : $.i18n._( 'Punch' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			layout_name: ALayoutIDs.TIMESHEET
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'first_last_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '' );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'punch_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.actual_time_label = $( "<span class='widget-right-label'></span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'punch_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.date_format_display ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( {field: 'punch_dates'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + LocalCacheData.loginUserPreference.date_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Absence Model
		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'date_stamp'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.date_format_display ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Absence Model
		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'total_time'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.time_unit_format_display ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Absence Model
		//Absence Policy TYpe
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'src_object_id'
		} );

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_punch_column1, '', null, true );

		//Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'available_balance'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.available_balance_info = $( '<img class="available-balance-info" src="' + Global.getRealImagePath( 'images/infox16x16.png' ) + '">' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), [form_item_input], tab_punch_column1, '', widgetContainer, true );

		//Punch Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( {field: 'disable_rounding'} );

		label = $( "<span class='widget-right-label'>" + $.i18n._( 'Disable Rounding' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( check_box );

		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );

		//In Out (Status)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1, '', null, true );

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
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.absence_model ) {
			if ( !this.show_branch_ui ) {
				this.edit_view_form_item_dic.branch_id.hide();
			}
		} else {
			if ( !this.show_absence_branch_ui ) {
				this.edit_view_form_item_dic.branch_id.hide();
			}
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
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.absence_model ) {
			if ( !this.show_department_ui ) {
				this.edit_view_form_item_dic.department_id.hide();
			}
		} else {
			if ( !this.show_absence_department_ui ) {
				this.edit_view_form_item_dic.department_id.hide();
			}
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

					if ( val ) {
						job_coder.setValue( val.manual_id );
					}
				}),
				field: 'job_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_ui ) {
					this.edit_view_form_item_dic.job_id.hide();
				}
			} else {
				if ( !this.show_absence_job_ui ) {
					this.edit_view_form_item_dic.job_id.hide();
				}
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

					if ( val ) {
						job_item_coder.setValue( val.manual_id );
					}
				}),
				field: 'job_item_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_item_ui ) {
					this.edit_view_form_item_dic.job_item_id.hide();
				}
			} else {
				if ( !this.show_absence_job_item_ui ) {
					this.edit_view_form_item_dic.job_item_id.hide();
				}
			}

		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			//Quanitity

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( {field: 'quantity'} );
			good.addClass( 'quantity-input' );

			var good_label = $( "<span class='widget-right-label'>" + $.i18n._( 'Good' ) + ": </span>" );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( {field: 'bad_quantity'} );
			bad.addClass( 'quantity-input' );

			var bad_label = $( "<span class='widget-right-label'>/ " + $.i18n._( 'Bad' ) + ": </span>" );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_bad_quantity_ui && !this.show_good_quantity_ui ) {
				this.edit_view_form_item_dic.quantity.hide();
			} else {
				if ( !this.show_bad_quantity_ui ) {
					bad_label.hide();
					bad.hide();
				}

				if ( !this.show_good_quantity_ui ) {
					good_label.hide();
					good.hide();
				}
			}
		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {field: 'note', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_punch_column1, '', null, true, true );
		form_item_input.parent().width( '45%' );

		if ( !this.show_note_ui ) {
			this.edit_view_form_item_dic.note.hide();
		}

		//Absence Mode
		//Override
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'override'} );
		this.addEditFieldToColumn( $.i18n._( 'Override' ), form_item_input, tab_punch_column1, '', null, true, true );

		//Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'station_id'} );

		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.click( function() {
			if ( $this.current_edit_record.station_id && $this.show_station_ui ) {
				IndexViewController.openEditView( $this, 'Station', $this.current_edit_record.station_id );
			}

		} );

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( {field: 'punch_image'} );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );

	},

	/* jshint ignore:end */

	onEditStationDone: function() {
		this.setStation();
	},

	setAbsencePolicyFilter: function( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		filter.filter_data.user_id = this.current_edit_record.user_id;

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	},

	onSetSearchFilterFinished: function() {

	},

	onBuildBasicUIFinished: function() {
	},

	onBuildAdvUIFinished: function() {

	},

	events: {},

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

	validate: function() {

		var $this = this;

		var record = this.current_edit_record;
		var i;
		if ( this.is_mass_editing ) {
			record = [];
			var len = this.mass_edit_record_ids.length;
			for ( i = 0; i < len; i++ ) {
				var temp_item = Global.clone( this.current_edit_record );
				temp_item.id = this.mass_edit_record_ids[i];
				record.push( temp_item );
			}
		}

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		if ( !this.absence_model ) {

			this.api['validate' + this.api.key_name]( record, {
				onResult: function( result ) {

					$this.validateResult( result );

				}
			} );

		} else {

			this.api_user_date_total['validate' + this.api_user_date_total.key_name]( record, {
				onResult: function( result ) {
					$this.clearErrorTips(); //Always clear error

					if ( result.isValid() ) {
						$this.setEditMenu();
					} else {
						$this.setErrorTips( result );
						$this.setErrorMenu();

					}

				}
			} );

		}

	},
	/* jshint ignore:start */
	onFormItemChange: function( target, doNotValidate ) {

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
					this.onJobQuickSearch( key, c_value );
				}
				break;
			case 'punch_dates':
				this.setEditMenu();
				break;

		}

		if ( this.absence_model ) {
			if ( key === 'total_time' ) {
				c_value = this.api_date.parseTimeUnit( c_value, {async: false} ).getResult();
				this.current_edit_record[key] = c_value;
			} else {
				this.current_edit_record[key] = c_value;
			}

			if ( key !== 'override' ) {
				this.edit_view_ui_dic.override.setValue( true );
				this.current_edit_record.override = true;
			}

		} else {
			this.current_edit_record[key] = c_value;
		}

		if ( !doNotValidate ) {
			if ( this.absence_model ) {
				if ( key === 'total_time' ||
					key === 'date_stamp' ||
					key === 'punch_dates' ||
					key === 'src_object_id' ) {
					this.onAvailableBalanceChange();
				}
			}
			this.validate();
		}

	},
	/* jshint ignore:end */

	buildSearchAndLayoutUI: function() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) + ': ' );

		this.save_search_as_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
		this.save_search_as_input = $( this.save_search_as_input );
		this.save_search_as_input.TTextInput();

		var save_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Save' ) + "' />" );

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

	checkTimesheetData: function() {
		if ( this.full_timesheet_data === true ) {
			return false;
		}

		return true;
	},

	render: function() {

		var $this = this;
		this._super( 'render' );

		var control_bar = $( this.el ).find( '.control-bar' );
		var date_chooser_div = control_bar.find( '.date-chooser-div' );
		var employee_nav_div = control_bar.find( '.employee-nav-div' );

		if ( !this.show_navigation_box ) {
			employee_nav_div.css( 'display', 'none' );
		} else {
			employee_nav_div.css( 'display', 'block' );
		}

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

			//Error: TypeError: $this.timesheet_columns is undefined in https://ondemand2001.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}

			var select_date = Global.strToDate( $this.timesheet_columns[1].index, $this.full_format );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 6 ) );

			$this.setDatePickerValue( new_date.format() );
			$this.search();

		} );

		date_right_arrow.bind( 'click', function() {
			//Error: TypeError: $this.timesheet_columns is undefined in https://ondemand2001.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}
			var select_date = Global.strToDate( $this.timesheet_columns[7].index, $this.full_format );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );

			$this.setDatePickerValue( new_date.format() );
			$this.search();

		} );

		this.start_date_picker.bind( 'formItemChange', function() {
			$this.setDatePickerValue( $this.start_date_picker.getValue() );
			$this.search();
		} );

		//Create Employee Navigation

		var label = employee_nav_div.find( '.navigation-label' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var navigation_widget_div = employee_nav_div.find( '.navigation-widget-div' );

		this.employee_nav = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		var default_args = {permission_section: 'punch'};
		this.employee_nav = this.employee_nav.AComboBox( {
			id: 'employee_navigation',
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			init_data_immediately: true,
			default_args: default_args,
			show_search_inputs: true
		} );

		navigation_widget_div.append( this.employee_nav );

		this.employee_nav.bind( 'formItemChange', function() {

			//Error: Uncaught TypeError: Cannot read property 'user_id' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20141216&user_id=41446 line 1595
			var current_user_id = '';
			if ( LocalCacheData.all_url_args ) {
				current_user_id = LocalCacheData.all_url_args.user_id;
			}
			var selected_user_id = $this.getSelectEmployee();

			if ( !$this.edit_view ) {
				var default_date = $this.start_date_picker.getDefaultFormatValue();
				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&user_id=' + $this.getSelectEmployee();
			}
			/* jshint ignore:start */
			if ( current_user_id != selected_user_id ) {
				$this.search();

			}
			/* jshint ignore:end */

			$this.setEmployeeNavArrowsStatus();
			$this.absence_model = false;
			$this.setDefaultMenu();

		} );

		this.employee_nav.bind( 'initSourceComplete', function() {
			$this.setEmployeeNavArrowsStatus();
		} );

		left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
		right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );

		right_click.click( function() {

			if ( right_click.hasClass( 'disabled' ) ) {
				return;
			}

			var selected_index = $this.employee_nav.getSelectIndex();
			var source_data = $this.employee_nav.getSourceData();

			var next_select_item;
			if ( selected_index < source_data.length - 1 ) {
				next_select_item = $this.employee_nav.getItemByIndex( selected_index + 1 );

			} else {
				next_select_item = $this.employee_nav.getItemByIndex( 0 );
			}

			$this.employee_nav.setValue( next_select_item );

			if ( !$this.edit_view ) {
				var default_date = $this.start_date_picker.getDefaultFormatValue();
				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&user_id=' + $this.getSelectEmployee();
			}

			$this.search();

			$this.setEmployeeNavArrowsStatus();

		} );

		left_click.click( function() {

			if ( left_click.hasClass( 'disabled' ) ) {
				return;
			}

			var selected_index = $this.employee_nav.getSelectIndex();
			var source_data = $this.employee_nav.getSourceData();

			var next_select_item;
			if ( selected_index > 0 ) {
				next_select_item = $this.employee_nav.getItemByIndex( selected_index - 1 );

			} else {
				next_select_item = $this.employee_nav.getItemByIndex( source_data.length - 1 );

			}

			$this.employee_nav.setValue( next_select_item );

			if ( !$this.edit_view ) {
				var default_date = $this.start_date_picker.getDefaultFormatValue();
				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&user_id=' + $this.getSelectEmployee();
			}

			$this.search();
			$this.setEmployeeNavArrowsStatus();

		} );

		label.text( $.i18n._( 'Employee' ) + ':' );

	},

	setEmployeeNavArrowsStatus: function() {
		var $this = this;
		var employee_nav_div = $( this.el ).find( '.employee-nav-div' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var selected_index = $this.employee_nav.getSelectIndex();
		var source_data = $this.employee_nav.getSourceData();

		right_click.removeClass( 'disabled' );
		left_click.removeClass( 'disabled' );

		//Error: Uncaught TypeError: Cannot read property 'length' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20150102&user_id=null line 1698
		if ( !source_data || selected_index === source_data.length - 1 ) {
			right_click.addClass( 'disabled' );
		}

		if ( !source_data || selected_index === 0 ) {
			left_click.addClass( 'disabled' );
		}
	},

	onClearSearch: function() {
		var do_update = false;
		var default_layout_id;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
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

			//Error: Uncaught TypeError: Cannot read property 'setSelectGridData' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20141213&user_id=29715 line 1738
			if ( this.column_selector ) {
				this.column_selector.setSelectGridData( this.default_display_columns );
			}

			//Error: Uncaught TypeError: Cannot read property 'setValue' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20150125&user_id=53288 line 1742
			if ( this.sort_by_selector ) {
				this.sort_by_selector.setValue( null );
			}

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

		var filter_data = this.getValidSearchFilter();

		var args;
		if ( do_update ) {
			args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.filter_data = filter_data;

		}

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

	onUpdateLayout: function() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.filter_data = filter_data;

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
		var layout_name;
		if ( Global.isSet( default_layout_name ) ) {
			layout_name = default_layout_name;
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.filter_data = filter_data;

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
		var default_layout_id;
		var layout_name;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
			layout_name = BaseViewController.default_layout_name;

		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.filter_data = filter_data;

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

	search: function( setDefaultMenu ) {

		this.accumulated_time_cells_array = []; //reset array since the select cell is clean
		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};

		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.premium_source = [];
		this.absence_source = [];
		this.accumulated_total_grid_source = [];
		this.punch_note_grid_source = [];
		this.verification_grid_source = [];

		this.branch_cell_count = 0;
		this.department_cell_count = 0;
		this.premium_cell_count = 0;
		this.job_cell_count = 0;
		this.task_cell_count = 0;
		this.absence_cell_count = 0;
		this.punch_note_account = 0;

		var $this = this;
		var filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();

		LocalCacheData.last_timesheet_selected_date = start_date_string;
		LocalCacheData.last_timesheet_selected_user = this.getSelectEmployee( true );

		var args = {filter_data: filter_data};

		ProgressBar.showOverlay();

		//Error: TypeError: this.api_timesheet.getTimeSheetData is not a function in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-155153 line 2 > eval line 1885
		if ( !this.api_timesheet || !this.api_timesheet || typeof(this.api_timesheet.getTimeSheetData) !== 'function' ) {
			return;
		}

		this.api_timesheet.getTimeSheetData( user_id, start_date_string, args, {
			onResult: function( result ) {

				$this.full_timesheet_data = result.getResult();

				if ( $this.full_timesheet_data === true || !$this.full_timesheet_data.hasOwnProperty( 'timesheet_dates' ) ) {
					return;
				}
				$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
				$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
				$this.buildCalendars();

				if ( setDefaultMenu ) {
					$this.setDefaultMenu( true );
				}

				$this.searchDone();

			}
		} );

	},

	buildVerificationGrid: function() {
		var $this = this;

		var columns = [];
		var grid;
		if ( !Global.isSet( this.verification_grid ) ) {
			grid = $( this.el ).find( '#verification_grid' );

			grid.attr( 'id', this.ui_id + '_verification_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_verification_grid' );
		}

		var column = {
			name: 'pay_period',
			index: 'pay_period',
			label: $.i18n._('Pay Period'),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		column = {
			name: 'verification',
			index: 'verification',
			label: $.i18n._('Window'),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		if ( !this.verification_grid ) {

			this.verification_grid = grid;

			this.verification_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );

		} else {

			this.verification_grid.jqGrid( 'GridUnload' );
			this.verification_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_verification_grid' );
			this.verification_grid = $( grid );
			this.verification_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );
		}

		this.grid_dic.verification_grid = this.verification_grid;
	},

	buildPunchNoteGrid: function() {
		var $this = this;

		var columns = [];
		var grid;
		if ( !Global.isSet( this.punch_note_grid ) ) {
			grid = $( this.el ).find( '#punch_note_grid' );

			//Grid's id is ScriptName + _grid
			grid.attr( 'id', this.ui_id + '_punch_note_grid' );

			grid = $( this.el ).find( '#' + this.ui_id + '_punch_note_grid' );
		}

		//if only put one column in grid. There is a UI bug
		var punch_in_out_column = {
			name: '',
			index: '',
			label: ' ',
			width: 0,
			sortable: false,
			title: false,
			hidden: true
		};
		columns.push( punch_in_out_column );

		punch_in_out_column = {name: 'note', index: 'note', label: ' ', width: 100, sortable: false, title: false};
		columns.push( punch_in_out_column );

		if ( !this.punch_note_grid ) {

			this.punch_note_grid = grid;

			this.punch_note_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );

		} else {

			this.punch_note_grid.jqGrid( 'GridUnload' );
			this.punch_note_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_punch_note_grid' );
			this.punch_note_grid = $( grid );
			this.punch_note_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );

		}

		this.grid_dic.punch_note_grid = this.punch_note_grid;
		this.setGridHeaderBar( 'punch_note_grid', 'Punch Notes' );
	},

	getAccumulatedTotalGridPayperiodHeader: function() {
		this.pay_period_header = $.i18n._( 'No Pay Period' );

		var pay_period_id = this.timesheet_verify_data.pay_period_id;

		if ( pay_period_id && this.pay_period_data ) {

			for ( var key in this.pay_period_data ) {
				var pay_period = this.pay_period_data[key];
				if ( pay_period.id === pay_period_id ) {
					var start_date = Global.strToDate( pay_period.start_date ).format();
					var end_date = Global.strToDate( pay_period.end_date ).format();
					this.pay_period_header = start_date + ' ' + $.i18n._( 'to' ) + ' ' + end_date;
					break;
				}
			}
		}
	},

	buildAccumulatedTotalGrid: function() {
		var $this = this;

		var columns = [];

		var grid;
		if ( !Global.isSet( this.accumulated_total_grid ) ) {
			grid = $( this.el ).find( '#accumulated_total_grid' );

			grid.attr( 'id', this.ui_id + '_accumulated_total_grid' );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_accumulated_total_grid' );
		}

		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		columns.push( punch_in_out_column );

		var start_date_str = this.start_date.format( Global.getLoginUserDateFormat() );
		var end_date_str = this.end_date.format( Global.getLoginUserDateFormat() );

//		this.pay_period_header = $.i18n._( 'No Pay Period' );
//
//		var pay_period_id = this.timesheet_verify_data.pay_period_id;
//
//		if ( pay_period_id && this.pay_period_data ) {
//
//			for ( var key in this.pay_period_data ) {
//				var pay_period = this.pay_period_data[key];
//				if ( pay_period.id === pay_period_id ) {
//					var start_date = Global.strToDate( pay_period.start_date ).format();
//					var end_date = Global.strToDate( pay_period.end_date ).format();
//					this.pay_period_header = start_date + ' to ' + end_date;
//					break;
//				}
//			}
//		}

		this.getAccumulatedTotalGridPayperiodHeader();

		var column_1 = {
			name: 'week',
			index: 'week',
			label: start_date_str + ' '+ $.i18n._('to')+' ' + end_date_str,
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		var column_2 = {
			name: 'pay_period',
			index: 'pay_period',
			label: this.pay_period_header,
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};

		columns.push( column_1 );
		columns.push( column_2 );

		if ( !this.accumulated_total_grid ) {

			this.accumulated_total_grid = grid;

			this.accumulated_total_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );

		} else {

			this.accumulated_total_grid.jqGrid( 'GridUnload' );
			this.accumulated_total_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_accumulated_total_grid' );
			this.accumulated_total_grid = $( grid );
			this.accumulated_total_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );

		}

		this.grid_dic.accumulated_total_grid = this.accumulated_total_grid;

		var accumulated_total_grid_title = $( this.el ).find( '.accumulated-total-grid-title' );
		accumulated_total_grid_title.css( 'display', 'block' );

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

			var date = Global.strToDate( field, $this.full_format );

			if ( date && date.getYear() > 0 ) {
				$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );

				$this.highLightSelectDay();
				$this.reLoadSubGridsSource();
			}

		}

	},

	buildAbsenceGrid: function() {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.absence_grid ) ) {
			grid = $( this.el ).find( '#absence_grid' );

			grid.attr( 'id', this.ui_id + '_absence_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_absence_grid' );
		}

		if ( !this.absence_grid ) {

			this.absence_grid = grid;

			this.absence_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				hoverrows: false,
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				ondblClickRow: function() {
					$this.onGridDblClickRow( 'absence' );
				},
				onSelectRow: function( row_id, flag, e ) {

					$this.onSelectRow( 'absence_grid', row_id, this );

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );

				},

				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this.absence_grid.jqGrid( 'GridUnload' );
			this.absence_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_absence_grid' );
			this.absence_grid = $( grid );
			this.absence_grid.jqGrid( {
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {

					$this.onSelectRow( 'absence_grid', row_id, this );

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );

				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				hoverrows: false,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}

		this.grid_dic.absence_grid = this.absence_grid;

		this.setGridHeaderBar( 'absence_grid', 'Absence' );

		this.bindGridColumnEvents();
	},

	buildTimeSheetGrid: function() {

		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

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

					$this.onSelectRow( 'timesheet_grid', row_id, this );

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );

				},

				colNames: [],
				colModel: this.timesheet_columns,
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

					$this.onSelectRow( 'timesheet_grid', row_id, this );

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
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}

		this.grid_dic.timesheet_grid = this.grid;

		this.grid_div.scroll( function( e ) {
			$this.scroll_position = $this.grid_div.scrollTop();
		} );

	},

	buildAccumulatedGrid: function() {

		var grid_id = 'accumulated_time_grid';
		var title = $.i18n._( 'Accumulated Time' );

		var $this = this;
		var grid;
		if ( !Global.isSet( this[grid_id] ) ) {
			grid = $( this.el ).find( '#' + grid_id );

			grid.attr( 'id', this.ui_id + '_' + grid_id );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
		}

		if ( !this[grid_id] ) {

			this[grid_id] = grid;

			this[grid_id].jqGrid( {
				data: [],
				datatype: 'local',
				scrollOffset: 0,
				hoverrows: false,
				sortable: false,
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {

					$this.onSelectRow( 'accumulated_grid', row_id, this );

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );

				},
				ondblClickRow: function() {
					$this.onAccumulatedTimeClick();
				},
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this[grid_id].jqGrid( 'GridUnload' );
			this[grid_id] = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
			this[grid_id] = $( grid );
			this[grid_id].jqGrid( {
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {

					$this.onSelectRow( 'accumulated_grid', row_id, this );

				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {

					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );

				},
				ondblClickRow: function() {
					$this.onAccumulatedTimeClick();
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				hoverrows: false,
				scrollOffset: 0,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}
		this.grid_dic[grid_id] = this[grid_id];

		this.setGridHeaderBar( grid_id, title );
	},

	buildSubGrid: function( grid_id, title ) {

		var $this = this;
		var grid;
		if ( !Global.isSet( this[grid_id] ) ) {
			grid = $( this.el ).find( '#' + grid_id );

			grid.attr( 'id', this.ui_id + '_' + grid_id );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
		}

		if ( !this[grid_id] ) {

			this[grid_id] = grid;

			this[grid_id].jqGrid( {
				data: [],
				datatype: 'local',
				scrollOffset: 0,
				hoverrows: false,
				sortable: false,
				altRows: true,
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this[grid_id].jqGrid( 'GridUnload' );
			this[grid_id] = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
			this[grid_id] = $( grid );
			this[grid_id].jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				hoverrows: false,
				scrollOffset: 0,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}
		this.grid_dic[grid_id] = this[grid_id];

		this.setGridHeaderBar( grid_id, title );

	},

	setGridSExpendOrCollapseStatus: function( grid_id, title ) {
		var grid = this.grid_dic[grid_id];
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_bar = table.find( '.title-bar' );
		this.setGridHeight( grid_id );

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
			grid.setGridHeight( 0 );
		}

		this.updateGridHeaderBar( grid_id, title );

	},

	//Show expend and collapse button in grid title bar
	setGridExpendButton: function( grid_id, title ) {
		var $this = this;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_bar = table.find( '.title-bar' );

		var img = $( "<img>" );
		img.addClass( 'grid-expend-btn' );

		if ( !Global.isSet( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] ) ||
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {

			img.attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;

		} else {
			img.attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
		}

		title_bar.append( img );

		this.setGridSExpendOrCollapseStatus( grid_id, title );

		img.click( function( e ) {

			if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {
				$( this ).attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
				$this.setGridSExpendOrCollapseStatus( grid_id, title );
			} else {
				$( this ).attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;
				$this.setGridSExpendOrCollapseStatus( grid_id, title );

			}
		} );

	},

	updateGridHeaderBar: function( grid_id, description ) {
		var label = description;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_span = table.find( '.title-span' );
		var count = 0;

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
			switch ( grid_id ) {
				case 'branch_grid':
					label = label + ' (' + (this.branch_cell_count) + ')';
					break;
				case 'department_grid':
					label = label + ' (' + (this.department_cell_count) + ')';
					break;
				case 'job_item_grid':
					label = label + ' (' + (this.task_cell_count) + ')';
					break;
				case 'job_grid':
					label = label + ' (' + (this.job_cell_count) + ')';
					break;
				case 'premium_grid':
					label = label + ' (' + (this.premium_cell_count) + ')';
					break;
				case 'absence_grid':
					label = label + ' (' + (this.absence_cell_count) + ')';
					break;
				case 'punch_note_grid':
					label = label + ' (' + (this.punch_note_account) + ')';
					break;
			}
		}

		title_span.text( label );
	},

	setGridHeaderBar: function( grid_id, description ) {
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		table.empty();

		var label = $.i18n._( description );

		var title_bar = $( "<div class='title-bar'><span class='title-span'>" + label + "</span></div>" );
		table.append( title_bar );
	},

	buildCalendars: function() {
		var $this = this;

		this.pay_period_data = this.full_timesheet_data.pay_period_data;
		this.pay_period_map = this.full_timesheet_data.timesheet_dates.pay_period_date_map;
		this.timesheet_verify_data = this.full_timesheet_data.timesheet_verify_data;
		this.grid_div = $( this.el ).find( '.timesheet-grid-div' );

		this.buildTimeSheetsColumns();

		this.buildTimeSheetGrid();

		this.buildAccumulatedGrid();

		this.buildSubGrid( 'branch_grid', 'Branch' );
		this.buildSubGrid( 'department_grid', 'Department' );
		this.buildSubGrid( 'job_grid', 'Job' );
		this.buildSubGrid( 'job_item_grid', 'Task' );
		this.buildSubGrid( 'premium_grid', 'Premium' );

		this.buildAbsenceGrid();

		this.showGridBorders();

		this.buildAccumulatedTotalGrid();

		this.buildPunchNoteGrid();

		this.buildVerificationGrid();

		//TimeSheet grid
		this.buildTimeSheetSource(); //Create punch data

		this.buildTimeSheetRequests();

		//Accumulated Time, Branch, Department, Job, Task, Pre
		this.buildSubGridsSource();

		//Make sure exception rows goes after Lanuch and break create from buildSubGridsSource
		this.buildTimeSheetExceptions();
//
		//Absence Grid source
		this.buildAbsenceSource(); //Create punch data

		//Show punch notes in a grid
		this.buildPunchNoteGridSource();

		//buildVerificationGridSource

		this.buildVerificationGridSource();

		this.setGridExpendButton( 'accumulated_time_grid', $.i18n._( 'Accumulated Time' ) );
		this.setGridExpendButton( 'branch_grid', $.i18n._( 'Branch' ) );
		this.setGridExpendButton( 'department_grid', $.i18n._( 'Department' ) );
		this.setGridExpendButton( 'job_grid', $.i18n._( 'Job' ) );
		this.setGridExpendButton( 'job_item_grid', $.i18n._( 'Task' ) );
		this.setGridExpendButton( 'premium_grid', $.i18n._( 'Premium' ) );
		this.setGridExpendButton( 'absence_grid', $.i18n._( 'Absence' ) );
		this.setGridExpendButton( 'punch_note_grid', $.i18n._( 'Punch Notes' ) );

		this.grid.clearGridData();
		this.grid.setGridParam( {data: this.timesheet_data_source} );
		this.grid.trigger( 'reloadGrid' );

		this.markRegularRow( this.accumulated_time_source );
		this.accumulated_time_grid.clearGridData();
		this.accumulated_time_grid.setGridParam( {data: this.accumulated_time_source} );
		this.accumulated_time_grid.trigger( 'reloadGrid' );

		this.branch_grid.clearGridData();
		this.branch_grid.setGridParam( {data: this.branch_source} );
		this.branch_grid.trigger( 'reloadGrid' );

		this.department_grid.clearGridData();
		this.department_grid.setGridParam( {data: this.department_source} );
		this.department_grid.trigger( 'reloadGrid' );

		this.job_grid.clearGridData();
		this.job_grid.setGridParam( {data: this.job_source} );
		this.job_grid.trigger( 'reloadGrid' );

		this.job_item_grid.clearGridData();
		this.job_item_grid.setGridParam( {data: this.job_item_source} );
		this.job_item_grid.trigger( 'reloadGrid' );

		this.premium_grid.clearGridData();
		this.premium_grid.setGridParam( {data: this.premium_source} );
		this.premium_grid.trigger( 'reloadGrid' );

		this.absence_grid.clearGridData();
		this.absence_grid.setGridParam( {data: this.absence_source} );
		this.absence_grid.trigger( 'reloadGrid' );

		if ( this.accumulated_total_grid_source.length === 0 ) {
			this.accumulated_total_grid_source.push();
		}

		this.markRegularRow( this.accumulated_total_grid_source );
		this.accumulated_total_grid.clearGridData();
		this.accumulated_total_grid.setGridParam( {data: this.accumulated_total_grid_source} );
		this.accumulated_total_grid.trigger( 'reloadGrid' );

		this.punch_note_grid.clearGridData();
		this.punch_note_grid.setGridParam( {data: this.punch_note_grid_source} );
		this.punch_note_grid.trigger( 'reloadGrid' );

		this.verification_grid.clearGridData();
		this.verification_grid.setGridParam( {data: this.verification_grid_source} );
		this.verification_grid.trigger( 'reloadGrid' );

		this.setTimeSheetGridPayPeriodHeaders();

		this.setTimeSheetGridHolidayHeaders();

		this.setAccumulatedTotalGridPayPeriodHeaders();

		this.setGridSize();

		this.highLightSelectDay();

		this.autoOpenEditViewIfNecessary();

		this.setScrollPosition();

		$this.initRightClickMenu();
		$this.initRightClickMenu( RightClickMenuType.ABSENCE_GRID );

		$this.showWarningMessageIfAny();

	},

	showWarningMessageIfAny: function() {
		var $this = this;
		var warning_bar = $( this.el ).find( '.timesheet-warning-title-bar' );
		var timesheet_grid_div = $( this.el ).find( '#gbox_' + this.ui_id + '_grid' );

		var user = this.getSelectEmployee( true );

		if ( !user.pay_period_schedule_id || !user.policy_group_id || !payPeriodCheck() ) {
			if ( warning_bar.length === 0 ) {
				warning_bar = $( "<div class='timesheet-warning-title-bar'><span class='p-message'></span><span class='g-message'></span><span class='pp-message'></span></div>" );
				warning_bar.insertBefore( timesheet_grid_div );
			}

			if ( !user.pay_period_schedule_id ) {
				warning_bar.children().eq( 0 ).html( $.i18n._( 'WARNING: Employee is not assigned to a pay period schedule.' ) );
			} else {
				warning_bar.children().eq( 0 ).html( '' );
			}

			if ( !user.policy_group_id ) {
				warning_bar.children().eq( 1 ).html( $.i18n._( 'WARNING: Employee is not assigned to a policy group.' ) );
			} else {
				warning_bar.children().eq( 1 ).html( '' );
			}

			if ( !payPeriodCheck() ) {
				warning_bar.children().eq( 2 ).html( $.i18n._( 'WARNING: Employees has days not assigned to a pay period. Please perform a pay period import to correct.' ) );
			} else {
				warning_bar.children().eq( 2 ).html( '' );
			}

		} else {
			if ( warning_bar.length > 0 ) {
				warning_bar.remove();
			}
		}

		function payPeriodCheck() {

			for ( var i = 0; i < 7; i++ ) {
				var select_date = new Date( new Date( $this.start_date.getTime() ).setDate( $this.start_date.getDate() + i ) );

				var select_date_str = select_date.format();
				var hire_date = $this.getSelectEmployee( true ).hire_date;
				var termination_date = $this.getSelectEmployee( true ).termination_date;

				if ( select_date.getTime() < new Date().getTime() && !$this.getPayPeriod( select_date_str ) &&
					(!hire_date || select_date.getTime() >= Global.strToDate( hire_date ).getTime()) &&
					(!termination_date || select_date.getTime() <= Global.strToDate( termination_date ).getTime()) ) {
					return false;
				}

			}

			return true;
		}

	},

	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'edit':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onEditClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.all_url_args.t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}

				break;
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.all_url_args.t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				if ( !this.edit_view ) {
					this.onAddClick();
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	getWeekDayIndexFromADate: function( date_string ) {

		var len = this.timesheet_columns.length;

		for ( var i = 1; i < len; i++ ) {
			var column = this.timesheet_columns[i];
			var column_date_string = Global.strToDate( column.index, this.full_format ).format();
			if ( date_string === column_date_string ) {
				return i;
			}
		}

		return 7;
	},

	setAccumulatedTotalGridPayPeriodHeaders: function() {

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_accumulated_total_grid]' )[0] );

		var new_tr = $( '<tr class="group-column-tr" >' +
		'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
		'<span class="group-column-label"></span>' +
		'</th>' );

		var default_th = new_th.clone();

		var week_th = new_th.clone();

		var pay_period_th = new_th.clone();

		week_th.children( 0 ).text( $.i18n._( 'Week' ) );
		pay_period_th.children( 0 ).text( $.i18n._( 'Pay Period' ) );

		new_tr.append( default_th );
		new_tr.append( week_th );
		new_tr.append( pay_period_th );

		table.children( 0 ).prepend( new_tr );
	},

	setTimeSheetGridHolidayHeaders: function() {
		var holiday_name_map = {};

		if ( this.full_timesheet_data.holiday_data ) {
			for ( var i = 0; i < this.full_timesheet_data.holiday_data.length; i++ ) {
				var item = this.full_timesheet_data.holiday_data[i];
				var standard_date = Global.strToDate( item.date_stamp ).format( this.full_format );

				var cell = $( 'div[id="jqgh_' + this.ui_id + '_grid_' + standard_date + '"]' );
				if ( cell && !holiday_name_map[item.name] ) {
					cell.html( cell.html() + '<br>' + item.name );
					holiday_name_map[item.name] = true;
				}

			}
		}
	},

	setTimeSheetGridPayPeriodHeaders: function() {

		var $this = this;

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );

		var size_tr = $( '<tr class="size-tr" role="row" style="height: 0px;" >' +
		'</tr>' );

		var new_tr = $( '<tr class="group-column-tr" >' +
		'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
		'<span class="group-column-label"></span>' +
		'</th>' );

		var current_trs = table.find( '.ui-jqgrid-labels' );
		createSizeColumns();
		table.children( 0 ).prepend( size_tr );

		var default_th;
		if ( this.pay_period_data.length === 0 ) {
			default_th = new_th.clone();
			new_tr.append( default_th );
			createNoPayPeriodColumns( 7 );
			new_tr.insertAfter( size_tr );
			return;
		}

		var current_end_index = 0;

		var last_pay_period_id;

		var column_number = 0;
		var pay_period;

		var map_array = [];

		for ( var y = 0; y < this.column_maps.length; y++ ) {
			var p_key = this.column_maps[y];

			var pay_period_id = this.pay_period_map[p_key];

			if ( !pay_period_id ) {
				pay_period_id = -1;
			}

			map_array.push( {date: p_key, time_stamp: Global.strToDate( p_key ).getTime(), id: pay_period_id} );
		}

		default_th = new_th.clone();
		new_tr.append( default_th );

		for ( var j = 0; j < map_array.length; j++ ) {

//			current_end_index = current_end_index + 1;
			if ( !last_pay_period_id ) {
				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = column_number + 1;
			} else if ( last_pay_period_id !== map_array[j].id ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}

				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = 1;

			} else {
				column_number = column_number + 1;
			}

			if ( j === map_array.length - 1 && column_number > 0 ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}
			}
		}

		function createTh() {

			var start_date = Global.strToDate( pay_period.start_date ).format();
			var end_date = Global.strToDate( pay_period.end_date ).format();
			var colspan = column_number;
			var pay_period_th = new_th.clone();

			pay_period_th.children( 0 ).text( start_date + ' '+ $.i18n._('to')+' ' + end_date );
			pay_period_th.attr( 'colspan', colspan );

			/* jshint ignore:start */
			if ( pay_period.status_id == 12 || pay_period.status_id == 20 ) {
				pay_period_th.css( 'background', '#EC0000' );
			} else if ( pay_period.status_id == 30 ) {
				pay_period_th.css( 'background', '#EED614' );
			}
			/* jshint ignore:end */

			new_tr.append( pay_period_th );
		}

		function getPayPeriod( id ) {

			for ( var key in $this.pay_period_data ) {
				var pay_period = $this.pay_period_data[key];
				if ( pay_period.id === id ) {
					return pay_period;
				}
			}
		}

		new_tr.insertAfter( size_tr );

		function createNoPayPeriodColumns( end_index ) {

			var pay_period_th = new_th.clone();

			pay_period_th.children( 0 ).text( $.i18n._( 'No Pay Period' ) );
			pay_period_th.attr( 'colspan', end_index );

			new_tr.append( pay_period_th );
		}

		function createSizeColumns() {

			var len = current_trs.children().length;

			for ( var i = 0; i < len; i++ ) {
				var th = $( '<td class=""  role="gridcell">' + '</td>' );
				var item = current_trs.children().eq( i );
				th.width( item.width() );
				th.height( 0 );
				size_tr.append( th );
			}

		}

	},

	setPayPeriodHeaderSize: function() {
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

	highLightSelectDay: function() {

		if ( this.highlight_header ) {
			this.highlight_header.removeClass( 'highlight-header' );
		}

		var select_date = Global.strToDate( this.start_date_picker.getValue() );
		select_date = select_date.format( this.full_format );

		this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date );

		this.highlight_header.addClass( 'highlight-header' );

	},

	/* jshint ignore:start */
	setGridHeight: function( grid_id ) {
		var grid = this.grid_dic[grid_id];
		var len = 0;

		switch ( grid_id ) {
			case 'timesheet_grid':
				len = this.timesheet_data_source.length;
				break;
			case 'accumulated_time_grid':
				len = this.accumulated_time_source.length;
				break;
			case 'branch_grid':
				len = this.branch_source.length;
				break;
			case 'department_grid':
				len = this.department_source.length;
				break;
			case 'job_grid':
				len = this.job_source.length;
				break;
			case 'job_item_grid':
				len = this.job_item_source.length;
				break;
			case 'premium_grid':
				len = this.premium_source.length;
				break;
			case 'absence_grid':
				len = this.absence_source.length;
				break;
			case 'accumulated_total_grid':
				len = this.accumulated_total_grid_source.length;
				grid.setGridWidth( 500 );
				break;
			case 'punch_note_grid':
				len = this.punch_note_grid_source.length;

				if ( this.verification_grid_source.length !== 0 ) {
					if ( Global.bodyWidth() > Global.app_min_width ) {
						grid.setGridWidth( Global.bodyWidth() - 14 - 520 - 400 );
					} else if ( grid_id !== 'punch_note_grid' ) {
						grid.setGridWidth( Global.app_min_width - 14 - 520 - 400 );
					}
				} else {
					if ( Global.bodyWidth() > Global.app_min_width ) {
						grid.setGridWidth( Global.bodyWidth() - 14 - 535 );
					} else if ( grid_id !== 'punch_note_grid' ) {
						grid.setGridWidth( Global.app_min_width - 14 - 535 );
					}
				}

				break;
			case 'verification_grid':
				len = this.verification_grid_source.length;
				grid.setGridWidth( 400 );
		}

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ||
			grid_id === 'timesheet_grid' ||
			grid_id === 'accumulated_total_grid' ||
			grid_id === 'punch_note_grid' ||
			grid_id === 'verification_grid' ) {
			grid.setGridHeight( len * 23 );
		} else {
			grid.setGridHeight( 0 );

		}

		//dont't show scroll bar of grid
		grid.parent().parent().css( 'overflow', 'hidden' );

		//Do not show grid if no data in it
		if ( len === 0 && grid_id !== 'accumulated_total_grid' && grid_id !== 'verification_grid' ) {
			grid.parent().parent().parent().parent().hide();
		} else {
			grid.parent().parent().parent().parent().show();
		}
	},
	/* jshint ignore:end */

	setGridSize: function() {

		if ( !this.grid || !this.grid.is( ':visible' ) ) {
			return;
		}

		for ( var key in this.grid_dic ) {

			var grid = this.grid_dic[key];

			if ( Global.bodyWidth() > Global.app_min_width ) {
				grid.setGridWidth( Global.bodyWidth() - 14 );
			} else if ( key !== 'punch_note_grid' ) {
				grid.setGridWidth( Global.app_min_width - 14 );
			}

			this.setGridHeight( key );
		}

		this.grid_div.height( $( this.el ).height() - this.search_panel.height() - 74 );

		this.setPayPeriodHeaderSize();

	},

	onCellFormat: function( cell_value, related_data, row ) {

		var col_model = related_data.colModel;
		var row_id = related_data.rowid;
		var content_div = $( "<div class='punch-content-div'></div>" );
		var punch_info;
		if ( related_data.pos === 0 ) {

			if ( row.type === TimeSheetViewController.TOTAL_ROW ) {
				punch_info = $( "<span class='total' style='font-size: 11px'></span>" );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}

				return punch_info.get( 0 ).outerHTML;
			} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {

				punch_info = $( "<span class='top-line-span' style='font-size: 11px'></span>" );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}

				return punch_info.get( 0 ).outerHTML;
			}

//			else if ( row.type === TimeSheetViewController.ACCUMULATED_TIME_ROW && row.is_override_row ) {
//
//				punch_info = $( "<span class='absence-override' style='font-size: 11px'></span>" );
//				if ( Global.isSet( cell_value ) ) {
//					punch_info.text( cell_value );
//				} else {
//					punch_info.text( '' );
//				}
//
//				return  punch_info.get( 0 ).outerHTML;
//			}

			return cell_value;

		}
		var ex_span;
		var i;
		var time_span;
		var punch;
		var break_span;
		var related_punch;
		var exception;
		var len;
		var text;
		var ex;
		var data;
		if ( row.type === TimeSheetViewController.PUNCH_ROW ) {
			punch = row[col_model.name + '_data'];
			related_punch = row[col_model.name + '_related_data'];
			time_span = $( "<span class='punch-time'></span>" );
			break_span = $( "<span class='punch-break'></span>" );

			if ( punch ) {
				exception = punch.exception;

				if ( punch.type_id === 20 ) {

					break_span.text( 'L' );
				} else if ( punch.type_id === 30 ) {

					break_span.text( 'B' );
				}

				if ( punch.note ) {
					cell_value = '*' + cell_value;
				}

				var label_suffix = '';

				if ( punch.latitude && punch.longitude ) {
					label_suffix = 'G';
				}

				if ( punch.has_image ) {
					label_suffix = label_suffix + 'F';

				}

				if ( label_suffix ) {
					cell_value = cell_value + ' ' + label_suffix;
				}

				if ( punch.tainted ) {
					time_span.css( 'color', '#ff0000' );
				}

				content_div.append( break_span );

			} else if ( related_punch ) {
				exception = related_punch.exception;
			}

			if ( Global.isSet( cell_value ) ) {

				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

			if ( exception ) {
				len = exception.length;
				text = '';
				for ( i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( "<span class='punch-exceptions'></span>" );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );
					content_div.prepend( ex_span );
				}
			} else {
				ex_span = $( "<span class='punch-exceptions'></span>" );
				ex_span.text( ' ' );
				content_div.prepend( ex_span );
			}

		} else if ( row.type === TimeSheetViewController.EXCEPTION_ROW ) {
			exception = row[col_model.name + '_exceptions'];

			if ( Global.isSet( exception ) ) {
				len = exception.length;
				text = '';
				for ( i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( "<span class='punch-exceptions-center'></span>" );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );

					content_div.append( ex_span );
				}
			}

		} else if ( row.type === TimeSheetViewController.REQUEST_ROW ) {
			time_span = $( "<span class='request'></span>" );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.TOTAL_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( "<span class='total'></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {
			content_div.addClass( 'top-line' );

			data = row[col_model.name + '_data'];

			time_span = $( "<span ></span>" );
			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ABSENCE_ROW ) {

			var absence = row[col_model.name + '_data'];
			time_span = $( "<span></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( absence ) {

					if ( absence.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( absence.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ACCUMULATED_TIME_ROW ) {

			data = row[col_model.name + '_data'];
			time_span = $( "<span></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else {
			time_span = $( "<span class='punch-time'></span>" );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		}

		return content_div.get( 0 ).outerHTML;
	},

	onSelectRow: function( grid_id, row_id, target ) {
		var $this = this;
		var row_tr = $( target ).find( '#' + row_id );
		row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

		var cells_array = [];
		var len = 0;

		if ( grid_id === 'timesheet_grid' ) {
			cells_array = $this.select_cells_Array;
			len = $this.select_cells_Array.length;
			$this.absence_select_cells_Array = [];
		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;
			len = $this.absence_select_cells_Array.length;
			$this.select_cells_Array = [];
		} else if ( grid_id === 'accumulated_grid' ) {
			cells_array = $this.accumulated_time_cells_array;
			len = $this.accumulated_time_cells_array.length;

		}

		this.select_punches_array = [];
		/* jshint ignore:start */
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.addClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

			if ( info.punch ) {

				if ( Global.isSet( info.punch.time_stamp ) ) { //date + time number
					var date = Global.strToDate( info.punch.punch_date ).format( 'MM-DD-YYYY' );
					var date_time = date + ' ' + info.punch.punch_time;

					info.punch.time_stamp_num = Global.strToDateTime( date_time ).getTime();
				} else {
					info.punch.time_stamp_num = info.time_stamp_num; //Uer time_stamp_num from cell select setting, a date number
				}

				this.select_punches_array.push( info.punch );

				this.select_punches_array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );
			}

		}
		/* jshint ignore:end */

		this.setDefaultMenu();

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
		var punch;
		var related_punch;
		var cells_array = [];
		$this.absence_model = false;
		var date;

		if ( grid_id === 'timesheet_grid' ) {

			cells_array = $this.select_cells_Array;

			len = $this.select_cells_Array.length;

			row = $this.timesheet_data_source[row_id - 1];

			if ( row.type === TimeSheetViewController.REQUEST_ROW ) {

				var filter = {filter_data: {}};
				filter.filter_data.user_id = this.getSelectEmployee();
				filter.filter_data.start_date = $this.full_timesheet_data.timesheet_dates.start_display_date;
				filter.filter_data.end_date = $this.full_timesheet_data.timesheet_dates.end_display_date;

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );
				IndexViewController.goToView( 'RequestAuthorization', filter );
				return;
			}

			colModel = $this.grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			punch = row[data_field + '_data'];

			related_punch = row[data_field + '_related_data'];

			date = Global.strToDate( data_field, this.full_format );

			$this.absence_grid.trigger( 'reloadGrid' );

			$this.setTimesheetGridDragAble();

		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;

			len = $this.absence_select_cells_Array.length;

			row = $this.absence_source[row_id - 1];

			colModel = $this.absence_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			punch = row[data_field + '_data'];

			date = Global.strToDate( data_field, this.full_format );

			$this.absence_model = true;

			$this.grid.trigger( 'reloadGrid' );

		} else if ( grid_id === 'accumulated_grid' ) {

			cells_array = $this.accumulated_time_cells_array;

			len = $this.accumulated_time_cells_array.length;

			row = $this.accumulated_time_source[row_id - 1];

			colModel = $this.accumulated_time_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );

		}

		var info;
		var row_tr;
		var cell_td;
		//Clean all select cells first
		for ( var i = 0; i < len; i++ ) {
			info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', false );
		}

		var date_str;
		var time_stamp_num;
		// Add multiple selectiend_display_date if click cell and hold ctrl or command
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

			date_str = date.format();
			time_stamp_num = date.getTime();

			if ( !found ) {

				if ( grid_id === 'timesheet_grid' ) {

					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					} );

					$this.select_cells_Array = cells_array;

					this.select_cells_Array.sort( function( a, b ) {

						return Global.compare( a, b, 'time_stamp_num' );

					} );

				} else if ( grid_id === 'absence_grid' ) {
					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: row.punch_info_id
					} );
					$this.absence_select_cells_Array = cells_array;

					this.absence_select_cells_Array.sort( function( a, b ) {

						return Global.compare( a, b, 'time_stamp_num' );

					} );
				} else if ( grid_id === 'accumulated_grid' ) {
					cells_array = [
						{
							row_id: row_id,
							cell_index: cell_index,
							cell_val: cell_val,
							date: date_str,
							time_stamp_num: time_stamp_num
						}
					];
					$this.accumulated_time_cells_array = cells_array;
				}

			}
		} else if ( e.shiftKey ) {

			var start_row_index = row_id;
			var start_cell_index = cell_index;

			var end_row_index = row_id;
			var end_cell_index = cell_index;

			for ( i = 0; i < len; i++ ) {
				info = cells_array[i];

				if ( info.row_id < start_row_index ) {
					start_row_index = info.row_id;
				} else if ( info.row_id > end_row_index ) {
					end_row_index = info.row_id;
				}

				if ( info.cell_index < start_cell_index ) {
					start_cell_index = info.cell_index;
				} else if ( info.cell_index > end_cell_index ) {
					end_cell_index = info.cell_index;
				}
			}

			cells_array = [];

			for ( i = start_row_index; i <= end_row_index; i++ ) {
				var r_index = i;
				for ( var j = start_cell_index; j <= end_cell_index; j++ ) {
					var c_index = j;

					row_tr = $( target ).find( '#' + r_index );

					cell_td = $( row_tr.find( 'td' )[c_index] );

					cell_val = cell_td[0].outerHTML;

					if ( grid_id === 'timesheet_grid' ) {

						row = $this.timesheet_data_source[r_index - 1];

						colModel = $this.grid.getGridParam( 'colModel' );

						data_field = colModel[c_index].name;

						punch = row[data_field + '_data'];

						related_punch = row[data_field + '_related_data'];

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index.toString(),
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							related_punch: related_punch,
							date: date_str,
							time_stamp_num: time_stamp_num
						} );

					} else if ( grid_id === 'absence_grid' ) {

						row = $this.absence_source[row_id - 1];

						colModel = $this.absence_grid.getGridParam( 'colModel' );

						data_field = colModel[c_index].name;

						punch = row[data_field + '_data'];

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index.toString(),
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							date: date_str,
							time_stamp_num: time_stamp_num,
							src_object_id: row.punch_info_id
						} );
					} else if ( grid_id === 'accumulated_grid' ) {
						cells_array = [
							{
								row_id: row_id,
								cell_index: cell_index,
								cell_val: cell_val,
								date: date_str,
								time_stamp_num: time_stamp_num
							}
						];
						$this.accumulated_time_cells_array = cells_array;
					}

				}
			}

			if ( grid_id === 'timesheet_grid' ) {
				$this.select_cells_Array = cells_array;

				this.select_cells_Array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );
			} else if ( grid_id === 'absence_grid' ) {
				$this.absence_select_cells_Array = cells_array;

				this.absence_select_cells_Array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );
			} else if ( grid_id === 'accumulated_grid' ) {
				$this.accumulated_time_cells_array = cells_array;

				this.accumulated_time_cells_array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );
			}

		} else {
			date_str = date.format();
			time_stamp_num = date.getTime();
			if ( grid_id === 'timesheet_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];

				$this.select_cells_Array = cells_array;

			} else if ( grid_id === 'absence_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: row.punch_info_id
					}
				];
				$this.absence_select_cells_Array = cells_array;
			} else if ( grid_id === 'accumulated_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];
				$this.accumulated_time_cells_array = cells_array;
			}

			if ( date && date.getYear() > 0 ) {
				this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				this.highLightSelectDay();
				this.reLoadSubGridsSource();
			}

		}

	},

	get_selected_punch_array: function() {

	},

	buildTimeSheetRequests: function() {
		var request_array = this.full_timesheet_data.request_data;
		var len = request_array.length;
		var request_row_index = null;

		for ( var i = 0; i < len; i++ ) {
			var request = request_array[i];

			var date_string = Global.strToDate( request.date_stamp ).format( this.full_format );

			var row;
			//Build Exception row at bottom
			if ( !request_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Requests' );
				row.user_id = request.user_id;
				row[date_string] = request.status;
				row[date_string + '_request'] = request;

				row.type = TimeSheetViewController.REQUEST_ROW;
				this.timesheet_data_source.push( row );
				request_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[request_row_index];
				if ( !Global.isSet( row[date_string + '_request'] ) ) {
					row[date_string] = request.status;
					row[date_string + '_request'] = request;
				} else {

					if ( $.type( row[date_string + '_request'] ) === 'array' ) {
						row[date_string + '_request'].push( request );

					} else {
						row[date_string + '_request'] = [row[date_string + '_request']];
						row[date_string + '_request'].push( request );
					}

					row[date_string] = calDAndA( row[date_string + '_request'] );
				}
			}

		}

		function calDAndA( array ) {
			var len = array.length;
			var a = 0;
			var d = 0;
			var p = 0;

			var label = '';

			for ( var i = 0; i < len; i++ ) {
				var item = array[i];

				if ( item.status_id === 50 ) {
					a = a + 1;
				} else if ( item.status_id === 55 ) {
					d = d + 1;
				} else if ( item.status_id === 30 ) {
					p = p + 1;
				}

			}

			if ( p > 0 ) {
				label = 'P: ' + p;
			}

			if ( a > 0 ) {
				label += ' A: ' + p;
			}

			if ( d > 0 ) {
				label += ' D: ' + d;
			}

			return label;

		}

	},

	buildTimeSheetExceptions: function() {
		var exception_array = this.full_timesheet_data.exception_data;

		var len = exception_array.length;
		var timesheet_data_source_len = this.timesheet_data_source.length;
		var exception_row_index = null;
		for ( var i = 0; i < len; i++ ) {
			var ex = exception_array[i];
			var date_string = Global.strToDate( ex.date_stamp ).format( this.full_format );
			var row;
			//Build Exception row at bottom
			if ( !exception_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Exceptions' );
				row.user_id = ex.user_id;
				row[date_string] = '';
				row[date_string + '_exceptions'] = [ex];

				row.type = TimeSheetViewController.EXCEPTION_ROW;
				this.timesheet_data_source.push( row );
				exception_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[exception_row_index];
				if ( !Global.isSet( row[date_string + '_exceptions'] ) ) {
					row[date_string + '_exceptions'] = [ex];
				} else {
					row[date_string + '_exceptions'].push( ex );
				}
			}

			var punch;
			var j;
			if ( !Global.isFalseOrNull( ex.punch_id ) ) {

				for ( j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch.id === ex.punch_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}

			} else if ( !Global.isFalseOrNull( ex.punch_control_id ) ) {
				for ( j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch.punch_control_id === ex.punch_control_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}
			}
		}
	},

	// Make sure Totle_time go to last item
	sortAccumulatedTotalData: function() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_total_grid_source.sort( Global.m_sort_by( sort_fields ) );

	},

	// Make sure Totle_time go to last item
	sortAccumulatedTimeData: function() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_time_source.sort( Global.m_sort_by( sort_fields ) );

	},

	reLoadSubGridsSource: function() {

		if ( this.full_timesheet_data.timesheet_verify_data.pay_period_id === this.pay_period_map[this.getSelectDate()] ||
			( !Global.isSet( this.full_timesheet_data.timesheet_verify_data.pay_period_id ) && !this.pay_period_map[this.getSelectDate()])
		) {

			return;
		}

		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};
//
		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.premium_source = [];
//		this.absence_source = [];
		this.accumulated_total_grid_source = [];
		this.verification_grid_source = [];

		var $this = this;
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();

//		this.search(); //should use getTimeSheetTotalData when it's fixed
		this.api_timesheet.getTimeSheetTotalData( user_id, start_date_string, {
			onResult: function( result ) {

				result = result.getResult();
				$this.full_timesheet_data.accumulated_user_date_total_data = result.accumulated_user_date_total_data;
				$this.full_timesheet_data.meal_and_break_total_data = result.meal_and_break_total_data;
				$this.full_timesheet_data.pay_period_accumulated_user_date_total_data = result.pay_period_accumulated_user_date_total_data;
				$this.full_timesheet_data.timesheet_verify_data = result.timesheet_verify_data;
				$this.full_timesheet_data.pay_period_data = result.pay_period_data;
				$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;

				$this.buildSubGridsSource();

				$this.buildAccumulatedTotalGrid();
				$this.buildVerificationGridSource();

				$this.accumulated_time_grid.clearGridData();
				$this.accumulated_time_grid.setGridParam( {data: $this.accumulated_time_source} );
				$this.accumulated_time_grid.trigger( 'reloadGrid' );

				$this.branch_grid.clearGridData();
				$this.branch_grid.setGridParam( {data: $this.branch_source} );
				$this.branch_grid.trigger( 'reloadGrid' );

				$this.department_grid.clearGridData();
				$this.department_grid.setGridParam( {data: $this.department_source} );
				$this.department_grid.trigger( 'reloadGrid' );

				$this.job_grid.clearGridData();
				$this.job_grid.setGridParam( {data: $this.job_source} );
				$this.job_grid.trigger( 'reloadGrid' );

				$this.job_item_grid.clearGridData();
				$this.job_item_grid.setGridParam( {data: $this.job_item_source} );
				$this.job_item_grid.trigger( 'reloadGrid' );

				$this.premium_grid.clearGridData();
				$this.premium_grid.setGridParam( {data: $this.premium_source} );
				$this.premium_grid.trigger( 'reloadGrid' );

				if ( $this.accumulated_total_grid_source.length === 0 ) {
					$this.accumulated_total_grid_source.push();
				}

				$this.accumulated_total_grid.clearGridData();
				$this.accumulated_total_grid.setGridParam( {data: $this.accumulated_total_grid_source} );
				$this.accumulated_total_grid.trigger( 'reloadGrid' );

				$this.punch_note_grid.clearGridData();
				$this.punch_note_grid.setGridParam( {data: $this.punch_note_grid_source} );
				$this.punch_note_grid.trigger( 'reloadGrid' );

				$this.verification_grid.clearGridData();
				$this.verification_grid.setGridParam( {data: $this.verification_grid_source} );
				$this.verification_grid.trigger( 'reloadGrid' );

				$this.setGridSize();

			}
		} );
	},

	//
	buildAccmulatedOrderMap: function( total ) {

		if ( !total ) {
			return;
		}
		for ( var key in total ) {

			for ( var key1 in total[key] ) {
				this.accmulated_order_map[key1] = total[key][key1].order;
			}

		}

	},

	buildSubGridsSource: function() {

		var accumulated_user_date_total_data = this.full_timesheet_data.accumulated_user_date_total_data;
		var meal_and_break_total_data = this.full_timesheet_data.meal_and_break_total_data;
		var pay_period_accumulated_user_date_total_data = this.full_timesheet_data.pay_period_accumulated_user_date_total_data;

		this.accmulated_order_map = {};

		// Save the order, will do sort after all data prepared.
		if ( accumulated_user_date_total_data.total ) {
			this.buildAccmulatedOrderMap( accumulated_user_date_total_data.total );
		}

		if ( pay_period_accumulated_user_date_total_data ) {
			this.buildAccmulatedOrderMap( pay_period_accumulated_user_date_total_data );
		}

		//Build Accumulated Total Grid Pay_period column data
		var accumulated_time = pay_period_accumulated_user_date_total_data.accumulated_time;
		var premium_time = pay_period_accumulated_user_date_total_data.premium_time;
		var absence_time = pay_period_accumulated_user_date_total_data.absence_time_taken;

		if ( Global.isSet( accumulated_time ) ) {
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		} else {
			accumulated_time = {total: {label: $.i18n._( 'Total Time' ), total_time: '0'}};
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		}

		if ( Global.isSet( premium_time ) ) {
			this.buildSubGridsData( premium_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
		}

		if ( Global.isSet( absence_time ) ) {
			this.buildSubGridsData( absence_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
		}

		//Build Accumulated Total Grid Pay_period column data end

		var column_len = this.timesheet_columns.length;
		accumulated_time = {total: {label: $.i18n._( 'Total Time' ), total_time: '0'}};
		var date_string;
		var date;
		for ( var i = 1; i < column_len; i++ ) {
			date_string = this.timesheet_columns[i].name;

			this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );

		}

		this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );

		for ( var key in accumulated_user_date_total_data ) {


			//Build Accumulated Total Grid week column data
			if ( key === 'total' ) {
				var total_result = accumulated_user_date_total_data.total;
				accumulated_time = total_result.accumulated_time;
				premium_time = total_result.premium_time;
				absence_time = total_result.absence_time_taken;

				if ( Global.isSet( accumulated_time ) ) {

					this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
				}

				if ( Global.isSet( premium_time ) ) {
					this.buildSubGridsData( premium_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
				}

				if ( Global.isSet( absence_time ) ) {
					this.buildSubGridsData( absence_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
				}

				continue;
			}

			//Build Accumulated Total Grid week column data end
			//Build all sub grids data

			date = Global.strToDate( key );
			date_string = date.format( this.full_format );

			accumulated_time = accumulated_user_date_total_data[key].accumulated_time;
			var branch_time = accumulated_user_date_total_data[key].branch_time;
			var department_time = accumulated_user_date_total_data[key].department_time;
			var job_time = accumulated_user_date_total_data[key].job_time;
			var job_item_time = accumulated_user_date_total_data[key].job_item_time;
			premium_time = accumulated_user_date_total_data[key].premium_time;

			if ( Global.isSet( accumulated_time ) ) {
				this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );
			}

			if ( Global.isSet( branch_time ) ) {

				this.buildSubGridsData( branch_time, date_string, this.branch_source_map, this.branch_source, 'branch_time' );
			}

			if ( Global.isSet( department_time ) ) {

				this.buildSubGridsData( department_time, date_string, this.department_source_map, this.department_source, 'department_time' );
			}

			if ( Global.isSet( job_time ) ) {

				this.buildSubGridsData( job_time, date_string, this.job_source_map, this.job_source, 'job_time' );
			}

			if ( Global.isSet( job_item_time ) ) {

				this.buildSubGridsData( job_item_time, date_string, this.job_item_source_map, this.job_item_source, 'job_item_time' );
			}

			if ( Global.isSet( premium_time ) ) {

				this.buildSubGridsData( premium_time, date_string, this.premium_source_map, this.premium_source, 'premium_time' );
			}

		}

		this.sortAccumulatedTotalData();
		this.sortAccumulatedTimeData();

		if ( Global.isSet( meal_and_break_total_data ) ) {

			for ( key  in meal_and_break_total_data ) {

				date = Global.strToDate( key );
				date_string = date.format( this.full_format );

				this.buildBreakAndLunchData( meal_and_break_total_data[key], date_string );

			}

		}

	},

	buildBreakAndLunchData: function( array, date_string ) {
		var row;
		for ( var key in array ) {
			if ( !this.accumulated_time_source_map[key] ) {
				row = {};
				row.punch_info = array[key].break_name;
				array[key].key = key;
				row[date_string] = Global.secondToHHMMSS( array[key].total_time ) + ' (' + array[key].total_breaks + ')';
				row[date_string + '_data'] = array[key];
				this.timesheet_data_source.push( row );
				this.accumulated_time_source_map[key] = row;
			} else {
				row = this.accumulated_time_source_map[key];
				if ( !row[date_string] ) {
					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time ) + ' (' + array[key].total_breaks + ')';

					row[date_string + '_data'] = array[key];
				}

			}
		}

	},

	addCellCount: function( key ) {
		switch ( key ) {
			case 'branch_time':
				this.branch_cell_count = this.branch_cell_count + 1;
				break;
			case 'department_time':
				this.department_cell_count = this.department_cell_count + 1;
				break;

			case 'premium_time':
				this.premium_cell_count = this.premium_cell_count + 1;
				break;
			case 'job_time':
				this.job_cell_count = this.job_cell_count + 1;
				break;
			case 'job_item_time':
				this.task_cell_count = this.task_cell_count + 1;
				break;

		}
	},

	markRegularRow: function( source ) {

		var len = source.length;

		for ( var i = 0; i < source.length; i++ ) {
			var row = source[i];

			if ( row.key && row.key.indexOf( 'regular_time' ) === 0 ) {
				row.type = TimeSheetViewController.REGULAR_ROW;
				return;
			}
		}
	},

	buildSubGridsData: function( array, date_string, map, result_array, parent_key ) {
		var row;
		for ( var key  in array ) {
			if ( !map[key] ) {
				row = {};
				row.parent_key = parent_key;
				row.key = key;

				if ( parent_key === 'accumulated_time' ) {

					if ( key === 'total' || key === 'worked_time' ) {
						row.type = TimeSheetViewController.TOTAL_ROW;
					} else {
						row.type = TimeSheetViewController.ACCUMULATED_TIME_ROW;
					}

					if ( array[key].override ) {
						row.is_override_row = true;
					}

				}

				if ( this.accmulated_order_map[key] ) {
					row.order = this.accmulated_order_map[key];
				}

				row.punch_info = array[key].label;

				var key_array = key.split( '_' );
				var no_id = false;
				if ( key_array.length > 1 && key_array[1] == '0' ) {
					no_id = true;
				}

				array[key].key = key;
				row[date_string] = Global.secondToHHMMSS( array[key].total_time );
				row[date_string + '_data'] = array[key];

				//if id == 0, put the row as first row.
				if ( no_id ) {
					result_array.unshift( row );
				} else {
					result_array.push( row );
				}

				map[key] = row;
			} else {
				row = map[key];
				if ( row[date_string] && key === 'total' ) { //Override total cell data since we set all to 00:00 at beginning
					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time );
					row[date_string + '_data'] = array[key];

					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}

				} else {

					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time );
					row[date_string + '_data'] = array[key];

					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}
				}

			}

			this.addCellCount( parent_key )
		}

	},

	timeSheetVerifyPermissionValidate: function() {
		if ( PermissionManager.validate( 'punch', 'verify_time_sheet' ) &&
			this.timesheet_verify_data.hasOwnProperty( 'pay_period_verify_type_id' ) &&
			this.timesheet_verify_data.pay_period_verify_type_id !== 10 ) {
			return true;
		}

		return false;
	},

	buildVerificationGridSource: function() {

		var $this = this;
		var verify_action_bar = $( this.el ).find( '.verification-action-bar' );
		var verify_grid_div = $( this.el ).find( '.verification-grid-div' );
		var verify_btn = $( this.el ).find( '.verify-button' );
		var verify_title = $( this.el ).find( '.verification-grid-title' );
		var verify_des = $( this.el ).find( '.verify-description' );

		if ( this.timeSheetVerifyPermissionValidate() &&
			Global.isSet( this.timesheet_verify_data.pay_period_id ) &&
			Global.isSet( this.timesheet_verify_data.pay_period_verify_type_id ) &&
			this.timesheet_verify_data.pay_period_verify_type_id !== '10' ) {

			if ( !this.timesheet_verify_data.display_verify_button ) {
				verify_btn.css( 'display', 'none' );
				verify_title.css( 'display', 'none' );
			} else {
				verify_btn.css( 'display', 'inline-block' );
				verify_title.css( 'display', 'block' );
			}

			verify_grid_div.css( 'display', 'block' );
			verify_des.text( this.timesheet_verify_data.verification_status_display );

			if ( this.timesheet_verify_data.verification_box_color ) {
				verify_action_bar.css( 'background', this.timesheet_verify_data.verification_box_color );
			} else {
				verify_action_bar.css( 'background', '#ffffff' );
			}

			verify_btn.unbind( 'click' ).bind( 'click', function() {
				TAlertManager.showConfirmAlert( $this.timesheet_verify_data.verification_confirmation_message, '', function( flag ) {

					if ( flag ) {
						$this.api_timesheet.verifyTimeSheet( $this.getSelectEmployee(), $this.timesheet_verify_data.pay_period_id,

							{
								onResult: function( result ) {

									if ( result.isValid() ) {
										$this.search()
									} else {
										TAlertManager.showErrorAlert( result )
									}

								}

							} );
					}

				} );
			} );

		} else {

			verify_btn.css( 'display', 'none' );
			verify_grid_div.css( 'display', 'none' );
			return;

		}

		var verification_data = this.timesheet_verify_data.verification_window_dates.start + ' '+ $.i18n._('to')+' ' + this.timesheet_verify_data.verification_window_dates.end

		var pay_period_data = this.pay_period_header;

		this.verification_grid_source.push( {pay_period: pay_period_data, verification: verification_data} );

	},

	buildPunchNoteGridSource: function() {
		this.punch_note_grid_source = [];
		var punch_array = this.full_timesheet_data.punch_data;
		var absence_array = this.full_timesheet_data.user_date_total_data;
		var len = punch_array.length;
		var len1 = absence_array.length;
		var last_control_id = '';
		var date;
		var date_string;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			date = Global.strToDate( punch.date_stamp );
			date_string = date.format();
			if ( punch.note && punch.punch_control_id !== last_control_id ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( {note: date_string + ' @ ' + punch.punch_time + ': ' + punch.note} );
				last_control_id = punch.punch_control_id;
			}
		}
		for ( var x = 0; x < len1; x++ ) {
			var absence = absence_array[x];
			date = Global.strToDate( absence.date_stamp );
			date_string = date.format();
			if ( absence.note ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( {note: date_string + ' @ ' + Global.secondToHHMMSS( absence.total_time ) + ': ' + absence.note} );
			}
		}
	},

	buildAbsenceSource: function() {

		var map = {};
		this.absence_source = [];
		this.absence_original_source = [];
		var absence_array = this.full_timesheet_data.user_date_total_data;
		var len = absence_array.length;
		var row;

		for ( var i = 0; i < len; i++ ) {

			var absence = absence_array[i];

			if ( absence.object_type_id !== 50 ) {
				continue;
			}
			this.absence_original_source.push( absence );
			var date = Global.strToDate( absence.date_stamp );
			var date_string = date.format( this.full_format );
			var key = absence.src_object_id + '-' + absence.pay_code_id;

			if ( !map[key] ) {
				row = {};
				row.type = TimeSheetViewController.ABSENCE_ROW;
				row.punch_info = absence.name; //Was: absence.absence_policy
				row.punch_info_id = absence.src_object_id;
				row.user_id = absence.user_id;
				row[date_string] = Global.secondToHHMMSS( absence.total_time );
				row[date_string + '_data'] = absence;
				this.absence_source.push( row );
				map[key] = row
			} else {
				row = map[key];
				if ( row[date_string] ) {
					row = {};
					row.type = TimeSheetViewController.ABSENCE_ROW;
					row.punch_info = absence.name; //Was: absence.absence_policy
					row.punch_info_id = absence.src_object_id;
					row.user_id = absence.user_id;
					row[date_string] = Global.secondToHHMMSS( absence.total_time );

					row[date_string + '_data'] = absence;
					this.absence_source.push( row );
					map[key] = row;

				} else {

					this.lastDayIsOverride( date, row, absence );
					row[date_string] = Global.secondToHHMMSS( absence.total_time );
					row[date_string + '_data'] = absence;
				}

			}

			this.absence_cell_count = this.absence_cell_count + 1;

		}

		if ( this.absence_source.length === 0 ) {
			row = {};
			row.punch_info = '';
			row.user_id = this.getSelectEmployee();
			this.absence_source.push( row );
		}

	},

	lastDayIsOverride: function( current_date, row, current_data ) {

		var last_date = new Date( new Date( current_date.getTime() ).setDate( current_date.getDate() - 1 ) );

		var date_str = last_date.format( this.full_format );

		var data = row[date_str + '_data'];

		if ( data && data.override && current_data.src_object_id === data.src_object_id ) {
			return true;
		}

		return false;
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

	buildTimeSheetSource: function() {

		this.timesheet_data_source = [];
		var punch_array = this.full_timesheet_data.punch_data;
		var len = punch_array.length;
		var row;
		var new_row;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			var date = Global.strToDate( punch.date_stamp );
			var date_string = date.format( this.full_format );

			var current_select_date_string = this.start_date_picker.getValue();
			current_select_date_string = Global.strToDate( current_select_date_string ).format( this.full_format );

			var punch_status_id = punch.status_id;

			if ( i === 0 ) {
				row = {};
				row.punch_info = punch.status;
				row.user_id = punch.user_id;
				row[date_string] = punch.punch_time;
				row[date_string + '_data'] = punch;
				row[date_string + '_related_data'] = null;
				row.status_id = punch_status_id;
				row.type = TimeSheetViewController.PUNCH_ROW;
				this.timesheet_data_source.push( row );

				if ( punch_status_id === 10 ) {

					var our_row = {};
					our_row.punch_info = $.i18n._( 'Out' );
					our_row.user_id = punch.user_id;
					our_row[date_string] = '';
					our_row[date_string + '_data'] = null;
					our_row[date_string + '_related_data'] = punch;
					our_row.status_id = 20;
					our_row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.push( our_row );

				} else {
					new_row = {};
					new_row.punch_info = $.i18n._( 'In' );
					new_row.user_id = punch.user_id;
					new_row[date_string] = '';
					new_row[date_string + '_data'] = null;
					new_row[date_string + '_related_data'] = punch;
					new_row.status_id = 10;
					new_row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
				}

			} else {

				var find_position = false;
				var timesheet_data_source_len = this.timesheet_data_source.length;
				for ( var j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];
					if ( row[date_string] ) {
						continue;
					} else if ( !row[date_string] && row[date_string + '_related_data'] ) {
						var related_punch = row[date_string + '_related_data'];

						if ( related_punch.punch_control_id === punch.punch_control_id ) {
							row[date_string] = punch.punch_time;
							row[date_string + '_data'] = punch;
							find_position = true;
							break;
						}
					} else if ( !row[date_string] && !row[date_string + '_related_data'] && punch.status_id === row.status_id ) {
						row[date_string] = punch.punch_time;
						row[date_string + '_data'] = punch;
						row[date_string + '_related_data'] = null;
						find_position = true;

						if ( punch.status_id === 10 ) {
							new_row = this.timesheet_data_source[j + 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						} else {
							new_row = this.timesheet_data_source[j - 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						}

						break;
					}
				}

				//Need add a new row
				if ( !find_position ) {
					row = {};
					row.punch_info = punch.status;
					row.user_id = punch.user_id;
					row[date_string] = punch.punch_time;
					row[date_string + '_data'] = punch;
					row[date_string + '_related_data'] = null;
					row.status_id = punch_status_id;
					row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.push( row );

					if ( punch_status_id === 10 ) {

						new_row = {};
						new_row.punch_info = $.i18n._( 'Out' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 20;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						this.timesheet_data_source.push( new_row );

					} else {
						new_row = {};
						new_row.punch_info = $.i18n._( 'In' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 10;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
					}
				}
			}
		}

		row = {};
		row.punch_info = $.i18n._( 'In' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 10;
		row.type = TimeSheetViewController.PUNCH_ROW;
		this.timesheet_data_source.push( row );

		row = {};
		row.punch_info = $.i18n._( 'Out' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 20;
		row.type = TimeSheetViewController.PUNCH_ROW;
		this.timesheet_data_source.push( row );

	},

	buildTimeSheetsColumns: function() {

		this.timesheet_columns = [];

		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		this.timesheet_columns.push( punch_in_out_column );

		//save full week columns map use to build no pey period column
		this.column_maps = [];
		for ( var i = 0; i < 7; i++ ) {

			var current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var header_text = current_date.format( this.weekly_format );

			var header_text_array = header_text.split( ',' );
			var header_text_array_2 = header_text_array[1].split( ' ' );

			header_text = $.i18n._( header_text_array[0] ) + ', ' + $.i18n._( header_text_array_2[1] ) + ' ' + header_text_array_2[2];

			var data_field = current_date.format( this.full_format );

			this.column_maps.push( current_date.format() );

			var column_info = {
				resizable: false,
				name: data_field,
				index: data_field,
				label: header_text,
				width: 100,
				sortable: false,
				title: false,
				formatter: this.onCellFormat
			};
			this.timesheet_columns.push( column_info );
		}

		return this.timesheet_columns;

	},

	initLayout: function() {

		var $this = this;
		$this.getAllLayouts( function() {
			$this.setSelectLayout();
			//set right click menu to list view grid

		} );
	},

	setSelectLayout: function() {
		var $this = this;

		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
		}

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

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.

		this.search( true ); // get punches base on userid, data and filter

	},

	//Start Drag
	setTimesheetGridDragAble: function() {

		var $this = this;

		var position = 0;

		var cells = this.grid.find( "td[role='gridcell']" );
//
		cells.attr( 'draggable', true );

		if ( ie <= 9 ) {
			cells.bind( 'selectstart', function( event ) {
				this.dragDrop();
				return false;
			} );
		}

		cells.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {

			var td = event.target;

			if ( $this.select_punches_array.length < 1 || !$( td ).hasClass( "ui-state-highlight" ) || !$this.select_drag_menu_id ) {
				return false;
			}

			var container = $( "<div class='drag-holder-div'></div>" );

			var len = $this.select_punches_array.length;

			for ( var i = 0; i < len; i++ ) {
				var punch = $this.select_punches_array[i];

				var span = $( "<span class='drag-span'></span>" );
				span.text( punch.status + ': ' + punch.time_stamp );
				container.append( span );
			}

			$( 'body' ).find( '.drag-holder-div' ).remove();

			$( 'body' ).append( container );

			event.originalEvent.dataTransfer.setData( 'Text', 'timesheet' );//JUST ELEMENT references is ok here NO ID

			if ( event.originalEvent.dataTransfer.setDragImage ) {
				event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
			}

			return true;

		} );

		cells.unbind( 'dragover' ).bind( 'dragover', function( e ) {

			var event = e.originalEvent;

			event.preventDefault();
			var $this = this;
			var target = $( this );

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );

			$( $this ).addClass( 'timesheet-drag-over' );

			//judge which area mouse on in the target cell and set proper style, Keep checking this in drag event.
			if ( event.pageY - target.offset().top <= 8 ) {
				position = -1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-top' );
			} else if ( event.pageY - target.offset().top >= target.height() - 5 ) {
				position = 1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-bottom' );
			} else {
				position = 0;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-center' );
			}

		} );

		cells.unbind( 'dragend' ).bind( 'dragend', function( event ) {

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );
			$( 'body' ).find( '.drag-holder-div' ).remove();

		} );

		cells.unbind( 'drop' ).bind( 'drop', function( event ) {

			event.preventDefault();
			if ( event.stopPropagation ) {
				event.stopPropagation(); // stops the browser from redirecting.
			}

			$( this ).removeClass( 'drag-over-top drag-over-center drag-over-bottom timesheet-drag-over' );
			var target_cell = event.currentTarget;
			var i = 0; //start index;

			//Error: Uncaught TypeError: Cannot read property 'punch_date' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=TimeSheet&date=20141118&user_id=32916 line 4563
			if ( !$this.select_punches_array || !$this.select_punches_array[i] ) {
				return;
			}

			var punch = $this.select_punches_array[i];

			var punch_date = Global.strToDate( punch.punch_date );

			var row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];

			//Error: Uncaught TypeError: Cannot read property 'status_id' of undefined in https://peocanada.timetrex.com/interface/html5/#!m=TimeSheet&date=20150108&user_id=1068 line 5174
			if ( !row ) {
				return;
			}

			var colModel = $this.grid.getGridParam( 'colModel' );

			var data_field = colModel[target_cell.cellIndex].name;

			var target_punch = row[data_field + '_data'];

			var target_related_punch = row[data_field + '_related_data'];

			var target_column_date = Global.strToDate( data_field, $this.full_format )

			var first_select_date = punch_date;

			var time_offset = target_column_date.getTime() - punch_date.getTime();

			var target_column_date_str = target_column_date.format();

			savePunch();

			function savePunch() {

				//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20141229&user_id=39555 line 5207
				if ( !$this.select_punches_array ) {
					return;
				}

				$this.api_date.parseDateTime( target_column_date_str, {
					onResult: function( date_num_result ) {

						var date_num = date_num_result.getResult();

						var new_pinch_id = punch.id;
						var target_id = false;
						var target_status_id = row.status_id;
						var action_type = $this.select_drag_menu_id === ContextMenuIconName.move ? 1 : 0;

						if ( target_punch ) {
							target_id = target_punch.id;
							target_status_id = false;
						} else if ( target_related_punch ) {
							target_id = target_related_punch.id;

							if ( target_related_punch.status_id === 10 ) {
								position = 1;
							} else {
								position = -1;
							}
							target_status_id = false;
						}

						var api_punch_control = new (APIFactory.getAPIClass( 'APIPunchControl' ))();

						api_punch_control.dragNdropPunch( new_pinch_id, target_id, target_status_id, position, action_type, date_num, {
							onResult: function( result ) {

								var result_data = result.getResult();

								if ( result.isValid() ) {

									i = i + 1;

									if ( i > $this.select_cells_Array.length - 1 ) {
										$this.search();
										return;
									}

									while ( !$this.select_punches_array[i].date_stamp ) {
										i = i + 1;

										if ( i > $this.select_cells_Array.length - 1 ) {
											$this.search();
											return;
										}
									}

									position = 1; //put next punch below last one

									var last_date_string = target_column_date_str;

									punch = $this.select_punches_array[i];

									punch_date = Global.strToDate( punch.punch_date );

									row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];

									colModel = $this.grid.getGridParam( 'colModel' );

									data_field = colModel[target_cell.cellIndex].name;

									time_offset = punch_date.getTime() - first_select_date.getTime();

									//drop column date
									target_column_date = Global.strToDate( data_field, $this.full_format );

									//Real target column date str
									target_column_date_str = new Date( target_column_date.getTime() + time_offset ).format();

									target_punch = {id: result_data};

									target_related_punch = null;

									if ( target_column_date_str !== last_date_string ) {
										position = 0;
										target_punch = null;
									}

									savePunch();

								} else {

									TAlertManager.showAlert( $.i18n._( 'Unable to drag and drop punch to the specified location' ) );

									if ( i > 0 ) {
										$this.search();
									}
								}

							}
						} )

					}
				} );

			}
		} );

	},

	initData: function() {

		var $this = this;
		Global.removeViewTab( this.viewId );
		var loginUser = LocalCacheData.getLoginUser();
		this.initOptions();
		ProgressBar.showOverlay();

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.employee_nav.setValue( LocalCacheData.default_filter_for_next_open_view.user_id );
			this.setDatePickerValue( LocalCacheData.default_filter_for_next_open_view.base_date );
		} else {
			if ( !LocalCacheData.last_timesheet_selected_user ) {
				//Default set current login user as select Employee
				this.employee_nav.setValue( loginUser );
			} else {
				this.employee_nav.setValue( LocalCacheData.last_timesheet_selected_user );
			}

			if ( LocalCacheData.all_url_args.user_id ) {
				this.employee_nav.setValue( LocalCacheData.all_url_args.user_id );
			}

			if ( !LocalCacheData.last_timesheet_selected_date ) { //Saved current select date in cache. so still select last select date when go to other view and back

				if ( LocalCacheData.current_selet_date ) { //Select date get from URL.
					this.setDatePickerValue( Global.strToDate( LocalCacheData.current_selet_date, 'YYYYMMDD' ).format() );
					LocalCacheData.current_selet_date = '';
				} else {
					var date = new Date();
					var format = Global.getLoginUserDateFormat();
					var dateStr = date.format( format );
					this.setDatePickerValue( dateStr );
				}

			} else {
				this.setDatePickerValue( LocalCacheData.last_timesheet_selected_date );
			}
		}

		$this.initLayout();

		this.setMoveOrDropMode( ContextMenuIconName.move );

	},

	setDatePickerValue: function( val ) {
		this.start_date_picker.setValue( val );

		var default_date = this.start_date_picker.getDefaultFormatValue();

		if ( !this.edit_view &&
			(window.location.href.indexOf( 'date=' + default_date ) === -1 || window.location.href.indexOf( 'user_id=' + this.getSelectEmployee() === -1 )) ) {

			var location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + default_date + '&user_id=' + this.getSelectEmployee();

			if ( LocalCacheData.all_url_args ) {
				for ( var key in LocalCacheData.all_url_args ) {
					if ( key === 'm' || key === 'date' || key === 'user_id' ) {
						continue;
					}
					location = location + '&' + key + '=' + LocalCacheData.all_url_args[key];

				}
			}

			window.location = location;

		}

		LocalCacheData.last_timesheet_selected_date = val;

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Punch Branch' ),
				in_column: 1,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Punch Department' ),
				field: 'department_id',
				in_column: 1,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: ALayoutIDs.JOB,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJob' )) : null,
				multiple: true,
				basic_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: ALayoutIDs.JOB_ITEM,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJobItem' )) : null,
				multiple: true,
				basic_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	getSelectEmployee: function( full_item ) {
		var user;
		if ( this.show_navigation_box ) {
			user = this.employee_nav.getValue( full_item );
		} else {
			if ( full_item ) {
				user = LocalCacheData.getLoginUser();
			} else {
				user = LocalCacheData.getLoginUser().id;
			}
		}

		return user;
	},

	getSelectDate: function() {
		return this.start_date_picker.getValue();
	},

	onDeleteAndNextClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();

		TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered ' +
		'Are you sure you wish to continue?' ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			}

			if ( result ) {

				ProgressBar.showOverlay();
				current_api['delete' + current_api.key_name]( remove_ids, {
					onResult: function( result ) {
						ProgressBar.closeOverlay();
						if ( result.isValid() ) {
							$this.search();
							$this.onRightArrowClick();
						} else {

						}

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	onDeleteClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered ' +
		'Are you sure you wish to continue?' ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			} else {
				var len = $this.select_punches_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = $this.select_punches_array[i];
					remove_ids.push( item.id );
				}
			}
			if ( result ) {
				ProgressBar.showOverlay();
				current_api['delete' + current_api.key_name]( remove_ids, {
					onResult: function( result ) {
						ProgressBar.closeOverlay();
						if ( result.isValid() ) {
							$this.search();

							if ( $this.edit_view ) {
								$this.removeEditView();
							}

						}

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	reSetURL: function() {
		window.location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee();
		LocalCacheData.all_url_args = null;
	},

	onSaveAndContinue: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_adding && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
			this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
		}

		current_api['set' + current_api.key_name]( this.current_edit_record, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					var refresh_id;
					if ( result_data === true ) {
						refresh_id = $this.current_edit_record.id;

					} else if ( result_data > 0 ) {
						refresh_id = result_data
					}
					$this.search();
					$this.onEditClick( refresh_id );

					$this.onSaveAndContinueDone( result );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	onSaveAndNextClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'save_and_next';
		current_api['set' + current_api.key_name]( this.current_edit_record, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;
					} else if ( result_data > 0 ) {
						$this.refresh_id = result_data
					}
					$this.onRightArrowClick();
					$this.search( false );
					$this.onSaveAndNextDone( result );

				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	onViewClick: function( editId, type ) {
		var $this = this;
		$this.is_viewing = true;
		LocalCacheData.current_doing_context_action = 'view';
		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}

		$this.openEditView();

		var current_api = this.getCurrentAPI();

		var filter = {};
		var selected_id;
		if ( Global.isSet( editId ) ) {
			selected_id = editId;
		} else {

			if ( this.select_punches_array.length > 0 ) {
				selected_id = this.select_punches_array[0].id;
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selected_id];

		current_api['get' + current_api.key_name]( filter, {
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

	buildOtherFieldUI: function( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab_punch = this.edit_view_tab.find( '#tab_punch' );
		var tab_punch_column1 = tab_punch.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: field} );
			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab_punch_column1 );

			input_div.insertBefore( this.edit_view_form_item_dic['note'] );

			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		}

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	onMassEditClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		this.is_mass_adding = false;
		this.is_viewing = false;

		var current_api = this.getCurrentAPI();

		var filter = {};
		this.mass_edit_record_ids = [];

		$.each( this.select_punches_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value.id )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		current_api['getCommon' + current_api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				current_api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						current_api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {

								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								if ( !Global.isSet( result_data.time_stamp ) ) {
									result_data.time_stamp = false;
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

	initSubLogView: function( tab_id ) {

		var $this = this;
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {

				if ( !$this.absence_model ) {
					filter['table_name_object_id'] = {
						'punch': [this.parent_edit_record.id],
						'punch_control': [this.parent_edit_record.punch_control_id]
					};
				} else {
					filter['table_name'] = 'user_date_total';
					filter['object_id'] = this.parent_edit_record.id;

				}

				return filter;
			};
			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/core/log/LogViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {
				if ( !$this.absence_model ) {
					filter['table_name_object_id'] = {
						'punch': [this.parent_edit_record.id],
						'punch_control': [this.parent_edit_record.punch_control_id]
					};
				} else {
					filter['table_name'] = 'user_date_total';
					filter['object_id'] = this.parent_edit_record.id;

				}

				return filter;
			};
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();

		}
	},

	onEditClick: function( editId, type ) {

		var $this = this;
		var selected_id;
		if ( Global.isSet( editId ) ) {
			selected_id = editId
		} else {
			if ( this.is_viewing ) {
				selected_id = this.current_edit_record.id;
			} else if ( this.select_punches_array.length > 0 ) {
				selected_id = this.select_punches_array[0].id;
			} else {
				return;
			}

		}
		this.is_mass_adding = false;
		this.is_viewing = false;
		LocalCacheData.current_doing_context_action = 'edit';
		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}

		$this.openEditView();

		var current_api = this.getCurrentAPI();

		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selected_id];

		current_api['get' + current_api.key_name]( filter, {
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

	setURL: function() {
		var t = this.absence_model ? 'absence' : 'punch';
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

		var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.edit_view_tab_selected_index ).text() : '';
		tab_name = tab_name.replace( /\/|\s+/g, '' );

		//Error: Unable to get property 'id' of undefined or null reference in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=8.0.0-20141117-132941 line 2234
		if ( this.current_edit_record && this.current_edit_record.id ) {

			if ( a ) {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&a=' + a + '&id=' + this.current_edit_record.id + '&t=' + t +
				'&tab=' + tab_name );

			} else {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&id=' + this.current_edit_record.id + '&t=' + t );
			}

			Global.trackView();

		} else {

			if ( a ) {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&a=' + a + '&t=' + t +
				'&tab=' + tab_name );
			} else {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&t=' + t );
			}

		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		if ( !this.checkTimesheetData() ) {
			return;
		}
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

		switch ( id ) {
			case ContextMenuIconName.add:
				this.absence_model = false;
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.add_absence:
				this.absence_model = true;
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
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
				break;
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.move:
			case ContextMenuIconName.drag_copy:
				this.setMoveOrDropMode( id );
				break;
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.edit_pay_period:
				this.onNavigationClick( id );
				break;
			case ContextMenuIconName.re_calculate_timesheet:
			case ContextMenuIconName.generate_pay_stub:
				this.onWizardClick( id );
				break;
			case ContextMenuIconName.map:
				this.onMapClick( id );
				break;
			case ContextMenuIconName.accumulated_time:
				this.onAccumulatedTimeClick( id );
				break;
		}
	},

	getPayPeriod: function( date ) {

		var current_date = this.getSelectDate();

		//if pass a date in, use the date
		if ( date ) {
			current_date = date;
		}

		if ( this.pay_period_map && this.pay_period_map[current_date] && parseInt( this.pay_period_map[current_date] ) > 0 ) {
			return this.pay_period_map[current_date];
		} else {
			return null;
		}
	},

	onNavigationClick: function( iconName ) {

		if ( !this.checkTimesheetData() ) {
			return;
		}

		var post_data;

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				IndexViewController.openEditView( this, 'Employee', this.getSelectEmployee() );
				break;
			case ContextMenuIconName.edit_pay_period:
				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_id )
				}
				break;
			case ContextMenuIconName.schedule:
				var filter = {filter_data: {}};
				var include_users = {value: [this.getSelectEmployee()]};
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = this.getSelectDate();

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );

				break;
			case ContextMenuIconName.pay_stub:
				filter = {filter_data: {}};
				var users = {value: [this.getSelectEmployee()]};
				filter.filter_data.user_id = users;

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );
				IndexViewController.goToView( 'PayStub', filter );

				break;
			case 'print_summary':

				filter = {time_period: {}};
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = {0: filter, 1: 'pdf_timesheet'};
				this.doFormIFrameCall( post_data );
				break;
			case 'print_detailed':
				filter = {time_period: {}};
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = {0: filter, 1: 'pdf_timesheet_detail'};
				this.doFormIFrameCall( post_data );
				break;

		}
	},

	doFormIFrameCall: function( postData ) {

		var url = ServiceCaller.getURLWithSessionId( 'Class=APITimesheetDetailReport&Method=getTimesheetDetailReport' );

		var message_id = UUID.guid();

		url = url + '&MessageID=' + message_id;

		this.sendFormIFrameCall( postData, url, message_id );

	},

	onAccumulatedTimeClick: function() {

		var select_date = Global.strToDate( this.getSelectDate() ).format( 'YYYYMMDD' );

		IndexViewController.openEditView( this, 'UserDateTotalParent', select_date );

	},

	onMapClick: function() {
		var punch;
		if ( this.edit_view ) {
			punch = this.current_edit_record;
		} else {
			punch = this.select_punches_array[0];
		}

		var latitude = punch.latitude;
		var longitude = punch.longitude;

		var url = "https://maps.google.com/maps?f=q&hl=en&geocode=&q=" + latitude + "," + longitude + "&ll=" + latitude + "," + longitude + "&ie=UTF8&z=16&om=1 ";

		window.open( url, '_blank' );
	},

	onWizardClick: function( iconName ) {

		var $this = this;
		switch ( iconName ) {
			case ContextMenuIconName.re_calculate_timesheet:
				var default_data = {};
				default_data.user_id = this.getSelectEmployee();

				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = pay_period_id;
				}
				IndexViewController.openWizard( 'ReCalculateTimeSheetWizard', default_data, function() {

					$this.onReCalTimeSheetDone();
				} );
				break;
			case ContextMenuIconName.generate_pay_stub:

				default_data = {};
				default_data.user_id = this.getSelectEmployee();

				pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = pay_period_id;
				}
				IndexViewController.openWizard( 'GeneratePayStubWizard', default_data, function() {
					$this.search();
				} );
				break;
		}

	},

	onReCalTimeSheetDone: function() {
		this.initData();
	},

	setMoveOrDropMode: function( id ) {

		var drag_copy_icon = $( '#' + ContextMenuIconName.drag_copy );
		var move_icon = $( '#' + ContextMenuIconName.move );
		drag_copy_icon.removeClass( 'selected-menu' );
		move_icon.removeClass( 'selected-menu' );

		var drag_invisible = false;
		var move_invisible = false;

		if ( !this.copyPermissionValidate() ) {
			drag_invisible = true;
		}

		if ( !this.movePermissionValidate() ) {
			move_invisible = true;
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

	getSelectDateArray: function() {

		var result = [];

		var cells_array = this.absence_model ? this.absence_select_cells_Array : this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];
			date_dic[item.date] = true;
		}

		for ( var key in date_dic ) {
			result.push( key )
		}

		if ( result.length === 0 ) {
			result = [this.getSelectDate()];
		}

		return result;

	},

	onAddClick: function( doing_save_and_new ) {

		var $this = this;
		this.is_viewing = false;
		this.is_mass_adding = true;
		LocalCacheData.current_doing_context_action = 'new';
		var punch_control_id = null;
		var pre_punch_id = null;
		var related_punch = null;
		var date = this.getSelectDate();

		if ( !this.absence_model ) {
			if ( this.select_cells_Array.length === 1 ) {
				var select_item = this.select_cells_Array[0];
				if ( select_item.related_punch ) {
					related_punch = select_item.related_punch;
					punch_control_id = select_item.related_punch.punch_control_id;
				} else {
					var current_column_field = Global.strToDate( select_item.date ).format( this.full_format );

					if ( this.timesheet_data_source && this.timesheet_data_source[select_item.row_id - 2] ) {
						var pre_punch = this.timesheet_data_source[select_item.row_id - 2][current_column_field + '_data'];
					}

					if ( pre_punch ) {
						pre_punch_id = pre_punch.id;
					}

				}

			}
			// To use proper context menu for each punch or abseonce mode.
			this.setDefaultMenu();
			$this.openEditView();

			if ( doing_save_and_new ) {
				date = this.current_edit_record.punch_date;
				related_punch = null;
				if ( this.current_edit_record.status_id === 10 ) {
					punch_control_id = this.current_edit_record.punch_control_id;
				} else {
					punch_control_id = null;
				}

			}

			this.api['get' + this.api.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				punch_control_id,
				pre_punch_id,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						if ( !$this.is_mass_adding && related_punch ) {
							result_data.punch_date = related_punch.punch_date;
							result_data.punch_time = related_punch.punch_time;

							if ( related_punch.status_id === 10 ) {
								result_data.status_id = 20;
							} else {
								result_data.status_id = 10;
							}
						} else {
							result_data.punch_date = $this.getSelectDate();
							var select_cell_item = $this.select_cells_Array[0];
							if ( select_cell_item ) {
								if ( select_cell_item.row_id % 2 !== 0 ) {
									result_data.status_id = 10;
								} else {
									result_data.status_id = 20;
								}
							}

						}

						// Set in or out base on first item select row
						if ( $this.is_mass_adding ) {
							var first_item = $this.select_cells_Array[0];

							if ( !first_item || first_item.row_id % 2 !== 0 ) {
								result_data.status_id = 10;
							} else {
								result_data.status_id = 20;
							}
						}

						if ( doing_save_and_new ) {
							result_data.punch_date = $this.current_edit_record.punch_date;

							if ( $this.current_edit_record.status_id === 10 ) {
								result_data.status_id = 20;
							} else {
								result_data.status_id = 10;
							}

						}

						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		} else { //Absence model branch

			if ( doing_save_and_new ) {
				date = this.current_edit_record.date_stamp;
			}
			// To use proper context menu for each punch or abseonce mode.
			$this.setDefaultMenu();
			$this.openEditView();
			this.api_user_date_total['get' + this.api_user_date_total.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						if ( Global.isSet( $this.absence_select_cells_Array[0] ) ) {
							result_data.src_object_id = $this.absence_select_cells_Array[0].src_object_id;
						}

						result_data.object_type_id = 50;

						result_data.date_stamp = $this.getSelectDate();
						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		}

	},

	removeEditView: function() {
		this._super( 'removeEditView' );
		if ( this.absence_select_cells_Array.length > 0 ) {
			this.absence_model = true;
		} else {
			this.absence_model = false;
		}
		this.setDefaultMenu();
	},

	isMassDate: function() {
		//Error: Unable to get property 'punch_dates' of undefined or null reference in https://peocanada.timetrex.com/interface/html5/ line 6300
		if ( this.is_mass_adding && this.current_edit_record && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
			return true;
		}

		return false;
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDate() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	onSaveAndNewClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'new';

		var record = this.current_edit_record;

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api['set' + current_api.key_name]( record, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					$this.search( false );
					$this.onAddClick( true );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	onCopyAsNewClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		this.is_mass_adding = true;

		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record.id = '';

			if ( !this.absence_model ) {

				this.current_edit_record.punch_control_id = '';

				if ( this.current_edit_record.status_id === 10 ) {
					this.current_edit_record.status_id = 20;

				} else {
					this.current_edit_record.status_id = 10;
				}

				this.edit_view_ui_dic['status_id'].setValue( this.current_edit_record.status_id );
			}

			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();
		}

	},

	onSaveAndCopy: function() {
		var $this = this;
		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		this.clearNavigationData();
		current_api['set' + current_api.key_name]( record, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					$this.search( false );
					$this.onCopyAsNewClick();
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	getCurrentAPI: function() {
		var current_api = this.api;

		if ( this.absence_model ) {
			current_api = this.api_user_date_total;
		}

		return current_api;
	},

	onSaveClick: function() {

		var $this = this;
		var record;
		LocalCacheData.current_doing_context_action = 'save';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
		}

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api['set' + current_api.key_name]( record, {
			onResult: function( result ) {

				if ( result.isValid() ) {
					$this.search();
					$this.current_edit_record = null;
					$this.removeEditView();

				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	},

	getOtherFieldTypeId: function() {
		var res = 15;

		if ( this.absence_model ) {
			res = 0;
		}

		return res;
	},

	setEditViewData: function() {
		var $this = this;

		this.is_changed = false;

		var navigation_div = this.edit_view.find( '.navigation-div' );

		if ( Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			navigation_div.css( 'display', 'block' );
			//Set Navigation Awesomebox
			//init navigation only when open edit view
			if ( !this.absence_model ) {

				this.navigation.AComboBox( {
					id: this.script_name + '_navigation',
					layout_name: ALayoutIDs.TIMESHEET
				} );
				this.navigation.setSourceData( this.full_timesheet_data.punch_data );
			} else {
				this.navigation.AComboBox( {
					id: this.script_name + '_navigation',
					layout_name: ALayoutIDs.ABSENCE
				} );
				this.navigation.setSourceData( this.absence_original_source );
			}

			this.navigation.setValue( this.current_edit_record );

		} else {
			navigation_div.css( 'display', 'none' );
		}

		for ( var key in this.edit_view_ui_dic ) {

			//Set all UI field to current edit record, we need validate all UI fielld when save and validate
			if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
				$this.current_edit_record[key] = false;
			}
		}

		if ( this.is_mass_editing ) {
			for ( key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.setMassEditMode ) ) {
					widget.setMassEditMode( true );
				}

			}

			$.each( this.unique_columns, function( index, value ) {

				if ( Global.isSet( $this.edit_view_ui_dic[value] ) && Global.isSet( $this.edit_view_ui_dic[value].setEnabled ) ) {
					$this.edit_view_ui_dic[value].setEnabled( false );
				}

			} );

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

		var tab_0_label = this.edit_view.find( 'a[ref=tab_punch]' );

		if ( this.absence_model ) {
			tab_0_label.text( $.i18n._( 'Absence' ) );
		} else {
			tab_0_label.text( $.i18n._( 'Punch' ) );
		}

		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var args;
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array = this.getSelectDateArray();
						this.current_edit_record.punch_dates = date_array;
						widget.setValue( date_array );
						break;
					case 'first_last_name':
						var select_employee = this.getSelectEmployee( true ); //Get full item
						widget.setValue( select_employee['first_name'] + ' ' + select_employee['last_name'] );
						break;
					case 'total_time':
						if ( this.absence_model ) {
							var result = Global.secondToHHMMSS( this.current_edit_record[key] );
							widget.setValue( result );
						}
						break;
					case 'station_id':
						if ( this.current_edit_record[key] ) {
							this.setStation();
						} else {
							widget.setValue( 'N/A' );
						}
						break;
					case 'punch_image':
						var station_form_item = this.edit_view_form_item_dic['station_id'];
						if ( this.current_edit_record['has_image'] ) {
							this.edit_view_form_item_dic['punch_image'].show();
							this.removeLastRowClass( station_form_item );
							widget.setValue( ServiceCaller.fileDownloadURL + '?object_type=punch_image&parent_id=' + this.current_edit_record.user_id + '&object_id=' + this.current_edit_record.id );

						} else {
							this.edit_view_form_item_dic['punch_image'].hide();
							this.addLastRowClass( station_form_item );
						}
						break;
					case 'job_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							args = {};
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

		if ( this.absence_model ) {

			if ( this.current_edit_record.id ) {
				this.pre_total_time = this.current_edit_record.total_time;
			} else {
				this.pre_total_time = 0;
			}
		} else {
			this.pre_total_time = 0;
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		} else {

			actual_time_value = $.i18n._( 'ie' ) + ': ' + $.i18n._( '' + LocalCacheData.loginUserPreference.time_format_display );

		}

		this.actual_time_label.text( actual_time_value );

		this.onAvailableBalanceChange();

		this.setEditMenu(); //To make sure save & continue icon disabled correct when multi dates

		this.setEditViewDataDone();
	},

	onAvailableBalanceChange: function() {
		if ( this.current_edit_record.hasOwnProperty( 'src_object_id' ) &&
			this.current_edit_record.src_object_id && !this.is_mass_editing ) {
			this.getAvailableBalance();
		} else {
			this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
		}
		this.editFieldResize();
	},

	getAvailableBalance: function() {

		var $this = this;
		var result_data;
		if ( this.absence_model ) {

			var last_date_stamp = this.current_edit_record.date_stamp;
			var total_time = this.current_edit_record.total_time;

			if ( this.is_mass_adding ) {

				last_date_stamp = this.current_edit_record.punch_dates;
				//get dates from date ranger
				if ( last_date_stamp && last_date_stamp.indexOf( ' - ' ) > 0 ||
					$.type( last_date_stamp ) === 'array' ) {

					if ( last_date_stamp.indexOf( ' - ' ) > 0 ) {
						last_date_stamp = this.parserDatesRange( last_date_stamp );
					}

					if ( last_date_stamp.length > 0 ) {
						total_time = total_time * last_date_stamp.length;
						last_date_stamp = last_date_stamp[last_date_stamp.length - 1];
					}

				}

			}
			this.api_absence_policy.getProjectedAbsencePolicyBalance(
				this.current_edit_record.src_object_id,
				this.getSelectEmployee(),
				last_date_stamp,
				total_time,
				this.pre_total_time, {
					onResult: function( result ) {
						getBalanceHandler( result );
					}
				}
			);

			function getBalanceHandler( result ) {
				result_data = result.getResult();
				//Error: TypeError: this.edit_view_ui_dic.available_balance is undefined in https://ondemand1.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-091433 line 2 > eval line 6570
				if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['available_balance'] ) {
					return;
				}
				if ( !result_data ) {
					$this.edit_view_form_item_dic['available_balance'].css( 'display', 'none' );
					return;
				}
				$this.edit_view_form_item_dic['available_balance'].css( 'display', 'block' );
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
							delay: 10,
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

		}
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {
		//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
		if ( !this.current_edit_record ) {
			return;
		}
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
					//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
					if ( !$this.current_edit_record ) {
						return;
					}
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

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in https://ondemand3.timetrex.com/interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
		if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['job_id'] ) {
			return;
		}

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value, user_id: this.current_edit_record.user_id, status_id: "10"};

			this.job_api.getJob( args, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_id = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0] );
					} else {
						$this.edit_view_ui_dic['job_id'].setValue( '' );
						$this.current_edit_record.job_id = false;
						$this.setJobItemValueWhenJobChanged( false );
					}

				}
			} );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value, job_id: this.current_edit_record.job_id, status_id: "10"};

			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_item_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_item_id = result_data[0].id;

					} else {
						$this.edit_view_ui_dic['job_item_id'].setValue( '' );
						$this.current_edit_record.job_item_id = false;
					}

				}
			} );
		}

	},

	setStation: function() {

		var $this = this;
		var arg = {filter_data: {id: this.current_edit_record.station_id}};

		this.api_station.getStation( arg, {
			onResult: function( result ) {

				$this.station = result.getResult()[0];

				var widget = $this.edit_view_ui_dic['station_id'];
				if ( $this.station ) {
					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20140925 line 6017
					if ( widget ) {
						widget.setValue( $this.station.type + '-' + $this.station.description );
					}

				} else {
					if ( widget ) {
						widget.setValue( 'N/A' );
					}

					return;
				}

				if ( PermissionManager.validate( 'station', 'view' ) ||
					(PermissionManager.validate( 'station', 'view_child' ) && $this.station.is_child ) ||
					(PermissionManager.validate( 'station', 'view_own' ) && $this.station.is_owner ) ) {
					$this.show_station_ui = true;
				} else {
					$this.show_station_ui = false;
				}

				if ( $this.show_station_ui ) {
					var form_item_input = $this.edit_view_ui_dic['station_id'];
					form_item_input.css( 'cursor', 'pointer' );
				}

			}
		} );
	},

	getSelectedItem: function() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {

			if ( this.select_punches_array.length > 0 ) {
				selected_item = this.select_punches_array[0];
			} else {
				selected_item = null;
			}
		}

		return Global.clone( selected_item );
	},

	addPermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'add' ) && this.editPermissionValidate( p_id ) ) {
			return true;
		}

		return false;

	},

	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		var len = this.context_menu_array.length;

		var grid_selected_length = this.select_punches_array.length;

		var p_id = this.absence_model ? 'absence' : 'punch';

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'disable-image' );
			context_btn.removeClass( 'invisible-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.add_absence:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length, 'absence' );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.copy:
				case ContextMenuIconName.drag_copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.move:
					if ( !this.movePermissionValidate( p_id ) ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn, grid_selected_length, p_id );

					break;
				case ContextMenuIconName.print:
					this.setDefaultMenuPrintIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.re_calculate_timesheet:
					this.setDefaultMenuReCalculateTimesheet( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.accumulated_time:
					this.setDefaultMenuAccumulatedTimeIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenuScheduleIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	reCalculateEditPermissionValidate: function() {

		var p_id = this.permission_id;

		if ( PermissionManager.validate( p_id, 'edit' ) || this.ownerOrChildPermissionValidate( p_id, 'edit_child' ) ) {

			return true;
		}
	},

	setDefaultMenuReCalculateTimesheet: function( context_btn, grid_selected_length, pId ) {

		if ( !this.reCalculateEditPermissionValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayPeriodSchedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuPayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuEditPayPeriodIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.getPayPeriod() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuAccumulatedTimeIcon: function( context_btn, pId ) {

		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	editOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'edit' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'edit_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'edit_child' )) ) {

			return true;

		}

		return false;

	},

	viewOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if (
			PermissionManager.validate( p_id, 'view' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'view_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'view_child' )) ) {

			return true;

		}

		return false;

	},

	deleteOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'delete' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'delete_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'delete_child' )) ) {

			return true;

		}

		return false;

	},

	editChildPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !PermissionManager.validate( p_id, 'enabled' ) ) {
			return false;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) ||
			this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) ) {

			return true;
		}

		return false;

	},

	onReportMenuClick: function( id ) {
		this.onNavigationClick( id );
	},

	setDefaultMenuPrintIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.removeClass( 'disable-image' );
	},

	setEditMenuMapIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		var punch = this.current_edit_record;
		if ( !punch || !punch.latitude || !punch.longitude ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuMapIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			var punch = this.select_punches_array[0];
			if ( !punch.latitude || !punch.longitude ) {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndNextIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyAndAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;

		var p_id = this.absence_model ? 'absence' : 'punch';

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing ) {
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

			//no need reset invisible-image, inhert from default menu
//			context_btn.removeClass( 'invisible-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.add_absence:
					this.setEditMenuAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.copy:
				case ContextMenuIconName.drag_copy:
					this.setEditMenuCopyIcon( context_btn, p_id )
					break;
				case ContextMenuIconName.move:
					if ( !this.movePermissionValidate( p_id ) ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.map:
					this.setEditMenuMapIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.accumulated_time:
					this.setDefaultMenuAccumulatedTimeIcon( context_btn );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#timesheet_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	}

} );

TimeSheetViewController.PUNCH_ROW = 1;
TimeSheetViewController.EXCEPTION_ROW = 2;
TimeSheetViewController.REQUEST_ROW = 3;
TimeSheetViewController.TOTAL_ROW = 4;
TimeSheetViewController.REGULAR_ROW = 5;
TimeSheetViewController.ABSENCE_ROW = 6;
TimeSheetViewController.ACCUMULATED_TIME_ROW = 7;

TimeSheetViewController.loadView = function() {

//	Global.loadViewSource( 'TimeSheet', 'TimeSheetView.css' );

	Global.loadViewSource( 'TimeSheet', 'TimeSheetView.html', function( result ) {

		var args = {
			accumulated_time: $.i18n._( 'Accumulated Time' ),
			verify: $.i18n._( 'Verify' ),
			timesheet_verification: $.i18n._( 'TimeSheet Verification' )
		};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

}
