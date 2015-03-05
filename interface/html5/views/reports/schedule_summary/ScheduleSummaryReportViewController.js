ScheduleSummaryReportViewController = ReportBaseViewController.extend( {

	initialize: function() {
		this.__super( 'initialize' );
		this.script_name = 'ScheduleSummaryReport';
		this.viewId = 'ScheduleSummaryReport';
		this.context_menu_name = $.i18n._( 'Schedule Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'ScheduleSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIScheduleSummaryReport' ))();
		this.buildContextMenu();

	},

	openEditView: function() {

		var $this = this;
		$this.initOptions( function( result ) {

			for ( var i = 0; i < $this.setup_fields_array.length; i++ ) {
				var item = $this.setup_fields_array[i];
				if ( item.value === 'status_id' ) {
					item.value = 'filter';
				}
			}

			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.view_file );
			}

			$this.getReportData( function( result ) {
				// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.

				var edit_item = result[0];
				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					for ( var i = 0; i < result.length; i++ ) {
						if ( result[i].id === parseInt( LocalCacheData.default_edit_id_for_next_open_edit_view ) ) {
							edit_item = result[i];
						}
					}
					LocalCacheData.default_edit_id_for_next_open_edit_view = null;
				}

				if ( result && result.length > 0 ) {
					$this.current_saved_report = edit_item;
					$this.saved_report_array = result;
				} else {
					$this.current_saved_report = {};
					$this.saved_report_array = [];
				}

				if ( $this.current_saved_report.hasOwnProperty( 'data' ) && $this.current_saved_report.data.hasOwnProperty( 'config' ) && $this.current_saved_report.data.config.hasOwnProperty( 'filter_' ) ) {
					$this.current_saved_report.data.config.filter = $this.current_saved_report.data.config.filter_;
					delete $this.current_saved_report.data.config.filter_;
				}
				$this.current_edit_record = {};
				$this.visible_report_values = {};
				$this.setEditViewWidgetsMode();
				$this.initEditView();

			} );

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
		var schedule_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Schedule' ),
			id: this.script_name + 'Schedule',
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

		var print = new RibbonSubMenu( {label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print,
			group: schedule_group,
			icon: 'print-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true} );

		var pdf_schedule = new RibbonSubMenuNavItem( {label: $.i18n._( 'Individual Schedules' ),
			id: 'pdf_schedule',
			nav: print
		} );

		var pdf_schedule_group_combined = new RibbonSubMenuNavItem( {label: $.i18n._( 'Group - Combined' ),
			id: 'pdf_schedule_group_combined',
			nav: print
		} );

		var pdf_schedule_group = new RibbonSubMenuNavItem( {label: $.i18n._( 'Group - Separated' ),
			id: 'pdf_schedule_group',
			nav: print
		} );

		var pdf_schedule_group_pagebreak = new RibbonSubMenuNavItem( {label: $.i18n._( 'Group - Separated (Page Breaks)' ),
			id: 'pdf_schedule_group_pagebreak',
			nav: print
		} );
		return [menu];

	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.status_id );
	},

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		var key = target.getField();

		if ( this.visible_report_widgets && this.visible_report_widgets[key] ) {
			if ( key === 'sort' ) {
				this.visible_report_values[key] = target.getValue( true );

			} else if ( key === 'time_period' ) {
				var time_period = target.getValue();
				this.visible_report_values[key] = {time_period: time_period};

				this.onTimePeriodChange( target );

			} else if ( key === 'filter' ) {
				var filter = target.getValue();
				this.visible_report_values[key] = {status_id: filter};

			} else if ( key === 'start_date' || key === 'end_date' || key === 'pay_period_id' || key === 'pay_period_schedule_id' ) {
				time_period = this.visible_report_values['time_period'];
				time_period[key] = target.getValue();

			} else {
				this.visible_report_values[key] = target.getValue();
			}

		} else {
			this.current_edit_record[key] = target.getValue();
		}

		if ( key === 'template' ) {
			$this.onTemplateChange( this.current_edit_record[key] );
			$this.setEditMenu(); //clean error, set edit menu
		} else {

			if ( this.edit_view_tab_selected_index === 0 ) {
				if ( !doNotValidate ) {
					this.validate();
				}
			}

		}

	}

} );