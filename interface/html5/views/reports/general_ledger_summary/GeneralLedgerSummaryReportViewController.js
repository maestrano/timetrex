GeneralLedgerSummaryReportViewController = ReportBaseViewController.extend( {

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'GeneralLedgerSummaryReport';
		this.viewId = 'GeneralLedgerSummaryReport';
		this.context_menu_name = $.i18n._( 'General Ledger Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'GeneralLedgerSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIGeneralLedgerSummaryReport' ))();
		this.buildContextMenu();

	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
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
		var export_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Export' ),
			id: this.viewId + 'Export',
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

		var exports = new RibbonSubMenu( {label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.print_checks,
			group: export_group,
			icon: 'export-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true} );

		var export_general_ledger_result = new (APIFactory.getAPIClass( 'APIPayStub' ))().getOptions( 'export_general_ledger', {async: false} ).getResult();

		export_general_ledger_result = Global.buildRecordArray( export_general_ledger_result );

		for ( var i = 0; i < export_general_ledger_result.length; i++ ) {
			var item = export_general_ledger_result[i];
			var btn = new RibbonSubMenuNavItem( {label: item.label,
				id: item.value,
				nav: exports
			} );
		}

		return [menu];

	}



} );