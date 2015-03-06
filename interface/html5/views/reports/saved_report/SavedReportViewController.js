SavedReportViewController = BaseViewController.extend( {
	el: '#saved_report_view_container',

	sub_report_schedule_view_controller: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'SavedReportEditView.html';
		this.permission_id = 'report';
		this.viewId = 'SavedReport';
		this.script_name = 'UserReportDataView';
		this.table_name_key = 'user_report_data';
		this.context_menu_name = $.i18n._( 'Reports' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserReportData' ))();

		this.render();
		if ( this.sub_view_mode ) {
			this.invisible_context_menu_dic[ContextMenuIconName.view] = true;
			this.buildContextMenu( true );

			//call init data in parent view, don't call initData
		} else {
			this.buildContextMenu();
			if ( !this.sub_view_mode ) {
				this.initData();
			}
			this.setSelectRibbonMenuIfNecessary();
		}

	},

	onDeleteResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();

		if ( result.isValid() ) {
			$this.search();
			$this.onDeleteDone( result );

			if ( $this.edit_view ) {
				$this.removeEditView();
			}

			if ( this.sub_view_mode && this.parent_view_controller ) {
				this.parent_view_controller.onSavedReportDelete();
			}

		} else {
			TAlertManager.showErrorAlert( result );
		}
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.share_report:

				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					var default_data = [];
					if ( this.edit_view && this.current_edit_record.id ) {
						default_data.push( this.current_edit_record.id );
					} else if ( !this.edit_view ) {
						default_data = this.getGridSelectIdArray()
					}
					IndexViewController.openWizard( 'ShareReportWizard', default_data );
				} else {
					TAlertManager.showAlert( Global.getUpgradeMessage() );
				}

				break;
		}
	},

	onGridDblClickRow: function() {
		ProgressBar.showOverlay();

		if ( this.sub_view_mode ) {
			this.onEditClick();
		} else {
			this.onViewClick();
		}

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

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'Report' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.hr_reports,
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

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		//menu group
		var share_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Share' ),
			id: this.viewId + 'Share',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var share = new RibbonSubMenu( {
			label: $.i18n._( 'Share<br>Report' ),
			id: ContextMenuIconName.share_report,
			group: share_group,
			icon: Icons.copy_as_new,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_report_schedule_view_controller = null;

	},

	onViewClick: function( editId, noRefreshUI ) {
		var grid_selected_id_array = this.getGridSelectIdArray();
		var id = grid_selected_id_array[0];
		var report_name = this.grid.jqGrid( 'getCell', id, 'script_name' );
		LocalCacheData.current_doing_context_action = 'view';
		LocalCacheData.default_edit_id_for_next_open_edit_view = id;

		//FIXME: Don't use report name that users see, use script_name instead as it won't change.
		switch ( report_name ) {
			case 'Accrual Balance Summary':
			case 'Accrual Balance Summary Report':
				IndexViewController.openReport( this, 'AccrualBalanceSummaryReport' );
				break;
			case 'Form W2':
			case 'Form W2 Report':
				IndexViewController.openReport( this, 'FormW2Report' );
				break;
			case 'Exception Summary':
			case 'Exception Summary Report':
				IndexViewController.openReport( this, 'ExceptionSummaryReport' );
				break;
			case 'Audit Trail':
			case 'Audit Trail Report':
				IndexViewController.openReport( this, 'AuditTrailReport' );
				break;
			case 'T4 Summary':
			case 'T4 Summary Report':
				IndexViewController.openReport( this, 'T4SummaryReport' );
				break;
			case 'T4A Summary':
			case 'T4A Summary Report':
				IndexViewController.openReport( this, 'T4ASummaryReport' );
				break;
			case 'Remittance Summary':
			case 'Remittance Summary Report':
				IndexViewController.openReport( this, 'RemittanceSummaryReport' );
				break;
			case 'TimeSheet Summary':
			case 'TimeSheet Summary Report':
				IndexViewController.openReport( this, 'TimesheetSummaryReport' );
				break;
			case 'Punch Summary':
			case 'Punch Summary Report':
				IndexViewController.openReport( this, 'PunchSummaryReport' );
				break;
			case 'Schedule Summary':
			case 'Schedule Summary Report':
				IndexViewController.openReport( this, 'ScheduleSummaryReport' );
				break;
			case 'Pay Stub Summary':
			case 'Pay Stub Summary Report':
				IndexViewController.openReport( this, 'PayStubSummaryReport' );
				break;
			case 'General Ledger Summary':
			case 'General Ledger Summary Report':
				IndexViewController.openReport( this, 'GeneralLedgerSummaryReport' );
				break;
			case 'Form 941':
			case 'Form 941 Report':
				IndexViewController.openReport( this, 'Form941Report' );
				break;
			case 'Tax Summary':
			case 'Tax Summary Report':
				IndexViewController.openReport( this, 'TaxSummaryReport' );
				break;
			case 'Form 940':
			case 'Form 940 Report':
				IndexViewController.openReport( this, 'Form940Report' );
				break;
			case 'Form 1099-Misc':
			case 'Form 1099-Misc Report':
				IndexViewController.openReport( this, 'Form1099MiscReport' );
				break;
			case 'Invoice Transaction':
			case 'Invoice Transaction Report':
				IndexViewController.openReport( this, 'InvoiceTransactionSummaryReport' );
				break;
			case 'TimeSheet Detail':
			case 'TimeSheet Detail Report':
				IndexViewController.openReport( this, 'TimesheetDetailReport' );
				break;
			case 'Affordable Care':
			case 'Affordable Care Report':
				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					IndexViewController.openReport( this, 'AffordableCareReport' );
				} else {
					TAlertManager.showAlert( Global.getUpgradeMessage() );
				}
				break;
			case 'Job Detail':
			case 'Job Detail Report':
			case 'Job Analysis':
			case 'Job Analysis Report':
				IndexViewController.openReport( this, 'JobAnalysisReport' );
				break;
			case 'Job Summary':
			case 'Job Summary Report':
				IndexViewController.openReport( this, 'JobSummaryReport' );
				break;
			case 'Job Information':
			case 'Job Information Report':
			case 'Job Report':
				IndexViewController.openReport( this, 'JobInformationReport' );
				break;
			case 'Task Information':
			case 'Task Information Report':
			case 'Job Item Report':
				IndexViewController.openReport( this, 'JobItemInformationReport' );
				break;
			case 'Employee Summary':
			case 'Employee Summary Report':
				IndexViewController.openReport( this, 'UserSummaryReport' );
				break;
			case 'Qualification Summary':
			case 'Qualification Summary Report':
				IndexViewController.openReport( this, 'UserQualificationReport' );
				break;
			case 'Recruitment Detail':
			case 'Recruitment Detail Report':
				IndexViewController.openReport( this, 'UserRecruitmentDetailReport' );
				break;
			case 'Recruitment Summary':
			case 'Recruitment Summary Report':
				IndexViewController.openReport( this, 'UserRecruitmentSummaryReport' );
				break;
			case 'Review Summary':
			case 'Review Summary Report':
				IndexViewController.openReport( this, 'KPIReport' );
				break;
			case 'Expense Summary':
			case 'Expense Summary Report':
				IndexViewController.openReport( this, 'ExpenseSummaryReport' );
				break;
			case 'Payroll Export':
			case 'Payroll Export Report':
				IndexViewController.openReport( this, 'PayrollExportReport' );
				break;
			case 'Whos In Summary':
			case 'Whos In Summary Report':
				IndexViewController.openReport( this, 'ActiveShiftReport' );
				break;
			default:
				ProgressBar.closeOverlay();
				Global.log( 'ERROR: Saved Report name not defined: ' + report_name );
				break;
		}
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );
		var $this = this;

		this.setTabLabels( {
			'tab_report': $.i18n._( 'Report' ),
			'tab_schedule': $.i18n._( 'Schedule' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUserReportData' )),
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.SAVED_REPORT,
				navigation_mode: true,
				show_search_inputs: true} );

			this.setNavigation();

		}

		//Tab 0 start

		var tab_report = this.edit_view_tab.find( '#tab_report' );

		var tab_report_column1 = tab_report.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_report_column1 );

		// Name

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_report_column1, '' );
		form_item_input.parent().width( '45%' );

		// Default

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input.TCheckbox( {field: 'is_default'} );
		this.addEditFieldToColumn( $.i18n._( 'Default' ), form_item_input, tab_report_column1 );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextInput( {field: 'description', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_report_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [
			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Description' ),
				field: 'description',
				basic_search: true,
				adv_search: false,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX} )

		];
	},

	initSubReportScheduleView: function() {

		var $this = this;

		if ( this.sub_report_schedule_view_controller ) {
			this.sub_report_schedule_view_controller.buildContextMenu( true );
			this.sub_report_schedule_view_controller.setDefaultMenu();
			$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadViewSource( 'ReportSchedule', 'ReportScheduleViewController.js', function() {

			var tab = $this.edit_view_tab.find( '#tab_schedule' );

			var firstColumn = tab.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'ReportSchedule' + 'View' );
			ReportScheduleViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_report_schedule_view_controller = subViewController;
			$this.sub_report_schedule_view_controller.parent_key = 'user_report_data_id';
			$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_report_schedule_view_controller.parent_view_controller = $this;
			$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
		}
	},

	uniformVariable: function( records ) {

		if ( records.hasOwnProperty( 'data' ) && records.data.hasOwnProperty( 'config' ) && records.data.config.hasOwnProperty( 'filter' ) ) {
			records.data.config.filter_ = records.data.config.filter;
		}

		return records;
	},

	onSaveDone: function( result ) {
		//onSaveDoneCallback is set in Report controller
		if ( this.parent_view_controller && this.parent_view_controller.onSaveDoneCallback ) {
			this.parent_view_controller.onSaveDoneCallback( result, this.current_edit_record );
		}
	},

	onSaveAndContinueDone: function( result ) {
		this.onSaveDone( result );
	},

	onSaveAndNextDone: function( result ) {
		this.onSaveDone( result );
	},

	//Call this from setEditViewData
	initTabData: function() {

		//Handle most case that one tab and one audit tab
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubReportScheduleView();
			} else {
				this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	onTabShow: function( e, ui ) {

		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );
		if ( !this.current_edit_record ) {
			return;
		}

		//Handle most cases that one tab and on audit tab
		if ( this.edit_view_tab_selected_index === 1 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubReportScheduleView();
			} else {
				this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 2 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}
	},

	setTabStatus: function() {
		//Handle most cases that one tab and on audit tab
		if ( !this.current_edit_record || !this.current_edit_record.id ) {

		} else {
			$( this.edit_view_tab.find( 'ul li' )[1] ).show();

		}

		this.editFieldResize( 0 );
	},

	onAddClick: function( reportData ) {

		ProgressBar.closeOverlay();
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();
		$this.current_edit_record = reportData;
		$this.initEditView();

	},

	onAddResult: function( result ) {
//		  var $this = this;
//		  var result_data = result.getResult();
//
//		  if ( !result_data ) {
//			  result_data = [];
//		  }
//
//		  result_data.company = LocalCacheData.current_company.name;
//
//		  if ( $this.sub_view_mode && $this.parent_key ) {
//			  result_data[$this.parent_key] = $this.parent_value;
//		  }
//
//		  $this.current_edit_record = result_data;
//		  $this.initEditView();
	}

} );

SavedReportViewController.loadView = function() {

	Global.loadViewSource( 'SavedReport', 'SavedReportView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};

SavedReportViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'SavedReport', 'SubSavedReportView.html', function( result ) {

		var args = { };
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_saved_report_controller );
			}

		}

	} );

}
