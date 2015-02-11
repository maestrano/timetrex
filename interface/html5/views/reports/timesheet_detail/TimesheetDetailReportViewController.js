TimesheetDetailReportViewController = ReportBaseViewController.extend( {

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'TimesheetDetailReport';
		this.viewId = 'TimesheetDetailReport';
		this.context_menu_name = $.i18n._( 'Timesheet Detail' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'TimesheetDetailReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APITimesheetDetailReport' ))();
		this.buildContextMenu();

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
		var timesheet_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Timesheet' ),
			id: this.viewId + 'Timesheet',
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

		var print = new RibbonSubMenu( {
			label: $.i18n._( 'Print Timesheet' ),
			id: ContextMenuIconName.print_timesheet,
			group: timesheet_group,
			icon: Icons.print,
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Summary' ),
			id: 'pdf_timesheet',
			nav: print
		} );

		var detail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Detailed' ),
			id: 'pdf_timesheet_detail',
			nav: print
		} );

		return [menu];

	},

	onReportMenuClick: function( id ) {

		this.onViewClick( id );
	}




} );