EmployeeViewController = BaseViewController.extend( {

	el: '#employee_view_container', //Must set el here and can only set string, so events can work
	user_api: null,
	user_group_api: null,
	company_api: null,
	hierarchyControlAPI: null,
	status_array: null,
	sex_array: null,
	user_group_array: null,
	country_array: null,
	province_array: null,

	e_province_array: null,

	sub_wage_view_controller: null,
	sub_user_contact_view_controller: null,
	sub_accrual_policy_user_modifier_view_controller: null,
	sub_log_view_controller: null,
	sub_company_tax_view_controller: null,

	sub_job_application_view_controller: null,

	sub_user_skill_view_controller: null,
	sub_user_education_view_controller: null,

	sub_user_membership_view_controller: null,

	sub_user_license_view_controller: null,

	sub_user_language_view_controller: null,

	sub_user_review_control_view_controller: null,

	hierarchy_options_dic: null,

	hierarchy_ui_model: null,

	show_hierarchy: false,

	select_company_id: null,

	initialize: function() {
		this._super( 'initialize' );

		this.edit_view_tpl = 'EmployeeEditView.html';
		this.permission_id = 'user';
		this.viewId = 'Employee';
		this.script_name = 'EmployeeView';
		this.table_name_key = 'users';
		this.document_object_type_id = 100;
		this.context_menu_name = $.i18n._( 'Employees' );
		this.navigation_label = $.i18n._( 'Employee' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.select_company_id = LocalCacheData.getCurrentCompany().id;

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();

		}
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.hierarchyControlAPI = new (APIFactory.getAPIClass( 'APIHierarchyControl' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		if ( this.edit_only_mode ) {
			this.invisible_context_menu_dic[ContextMenuIconName.timesheet] = true; //Hide some context menus
			this.invisible_context_menu_dic[ContextMenuIconName.schedule] = true; //Hide some context menus
			this.invisible_context_menu_dic[ContextMenuIconName.pay_stub] = true; //Hide some context menus
			this.invisible_context_menu_dic[ContextMenuIconName.pay_stub_amendment] = true; //Hide some context menus
		}

		this.initPermission();
		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();

	},

	jobUIValidate: function() {
		if ( PermissionManager.validate( "job", 'enabled' ) &&
			(PermissionManager.validate( 'job', 'view' ) ||
			PermissionManager.validate( 'job', 'view_child' ) ||
			PermissionManager.validate( 'job', 'view_own' )) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function() {

		if ( PermissionManager.validate( "job_item", 'enabled' ) &&
			(PermissionManager.validate( 'job_item', 'view' ) ||
			PermissionManager.validate( 'job_item', 'view_child' ) ||
			PermissionManager.validate( 'job_item', 'view_own' )) ) {
			return true;
		}
		return false;
	},

	//Speical permission check for views, need override
	initPermission: function() {
		this._super( 'initPermission' );

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

		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedule' ),
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

		var pay_stub_amendments = new RibbonSubMenu( {
			label: $.i18n._( 'Pay Stub<br>Amendments' ),
			id: ContextMenuIconName.pay_stub_amendment,
			group: navigation_group,
			icon: Icons.pay_stub_amendment,
			permission_result: true,
			permission: null
		} );

		var import_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Import' ),
			id: ContextMenuIconName.import_icon,
			group: other_group,
			icon: Icons.import_icon,
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVEmployee' ),
			permission: null
		} );

		return [menu];

	},

	openEditView: function( id ) {
		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {
				if ( !$this.edit_view ) {
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}

				$this.getEmployeeData( id, function( result ) {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this.current_edit_record = result;
					$this.setEditViewWidgetsMode();
					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

			this.setEditViewWidgetsMode();
		}

	},

	getEmployeeData: function( id, callBack ) {
		if ( typeof id === 'object' ) {

			id.id = '';
			id.company = LocalCacheData.current_company.name;

			callBack( id );
		} else {
			var filter = {};
			filter.filter_data = {};
			filter.filter_data.id = [id];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {
					var result_data = result.getResult();

					if ( !result_data ) {
						result_data = [];
					}
					result_data = result_data[0];

					callBack( result_data );

				}
			} );
		}

	},

	initOptions: function( callBack ) {

		var options = [
			{option_name: 'status'},
			{option_name: 'sex'},
			{option_name: 'country', field_name: 'country', api: this.company_api}
		];

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );

	},

	initDropDownOptions: function( options, callBack ) {

		var $this = this;
		var len = options.length + 2;
		var complete_count = 0;
		var option_result = [];
		if ( this.hierarchyPermissionValidate() ) {

			this.hierarchyControlAPI.getHierarchyControlOptions( {
				onResult: function( res ) {

					$this.hierarchy_options_dic = {};
					var data = res.getResult();

					for ( var key in data ) {
						var source = Global.buildRecordArray( data[key] );
						$this.hierarchy_options_dic[key] = source;
					}
					$this.hierarchyControlAPI.getOptions( 'object_type', {
						onResult: function( res_1 ) {

							var data_1 = res_1.getResult();
							if ( data_1 ) {

								var array = [];

								for ( var key in data_1 ) {
									array.push( {id: key, value: data_1[key]} );
								}

								array.sort( function( a, b ) {

									if ( parseInt( a["id"] ) < parseInt( b['id'] ) ) {
										return true;
									} else {
										return false;
									}

								} );

								$this.hierarchy_ui_model = array;

							}

							if ( data ) {
								$this.show_hierarchy = true;
							} else {
								$this.show_hierarchy = false;
							}

							complete_count = complete_count + 1;

							if ( complete_count === len ) {

								callBack( option_result );
							}
						}
					} );

				}
			} );

		} else {
			this.show_hierarchy = false;
			complete_count = complete_count + 1;
		}

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.edit_only_mode ) {
					if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
						$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
						$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
					}

				}

				$this.user_group_array = res;

				complete_count = complete_count + 1;

				if ( complete_count === len ) {

					callBack( option_result );
				}

			}
		} );

		for ( var i = 0; i < len - 2; i++ ) {
			var option_info = options[i];

			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result );
			}
		}

	},
	/* jshint ignore:start */
	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

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
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length, 'pay_stub' );
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	/* jshint ignore:end */

	setDefaultMenuPayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	onTabShow: function( e, ui ) {
		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );

		if ( !this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_tab_selected_index === 3 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_wage' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubWageView();
			} else {
				this.edit_view_tab.find( '#tab_wage' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 4 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_tax' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubCompanyTaxView();
			} else {
				this.edit_view_tab.find( '#tab_tax' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 5 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_contacts' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubUserContactView();
			} else {
				this.edit_view_tab.find( '#tab_contacts' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 6 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_applications' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubJobApplicationView();
			} else {
				this.edit_view_tab.find( '#tab_applications' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab_selected_index === 7 ) {

			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

				if ( this.current_edit_record.id ) {
					this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.initSubAccrualPolicyUserModifier();
				} else {
					this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}

			} else {
				this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}

		} else if ( this.edit_view_tab_selected_index === 8 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_qualifications' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubQualificationView();
			} else {
				this.edit_view_tab.find( '#tab_qualifications' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 9 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_reviews' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubUserReviewControlView();
			} else {
				this.edit_view_tab.find( '#tab_reviews' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 10 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 11 ) {

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

	initSubQualificationView: function() {
		var $this = this;

		Global.loadScriptAsync( 'views/hr/qualification/UserSkillViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.first-sub-view' );
			Global.trackView( 'Sub' + 'UserSkill' + 'View' );
			UserSkillViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserSkillView );

		} );

		Global.loadScriptAsync( 'views/hr/qualification/UserEducationViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.second-sub-view' );
			Global.trackView( 'Sub' + 'UserEducation' + 'View' );
			UserEducationViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserEducationView );

		} );

		Global.loadScriptAsync( 'views/hr/qualification/UserMembershipViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.third-sub-view' );
			Global.trackView( 'Sub' + 'UserMembership' + 'View' );
			UserMembershipViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserMembershipView );

		} );

		Global.loadScriptAsync( 'views/hr/qualification/UserLicenseViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.forth-sub-view' );
			Global.trackView( 'Sub' + 'UserLicense' + 'View' );
			UserLicenseViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserLicenseView );

		} );

		Global.loadScriptAsync( 'views/hr/qualification/UserLanguageViewController.js', function() {
			var tab_qualifications = $this.edit_view_tab.find( '#tab_qualifications' );
			var firstColumn = tab_qualifications.find( '.first-column-sub-view' ).find( '.fifth-sub-view' );
			Global.trackView( 'Sub' + 'UserLanguage' + 'View' );
			UserLanguageViewController.loadSubView( firstColumn, beforeLoadView, afterLoadUserLanguageView );

		} );

		function beforeLoadView() {

		}

		function afterLoadUserSkillView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Skills' ) );
			$this.sub_user_skill_view_controller = subViewController;
			$this.sub_user_skill_view_controller.parent_key = 'user_id';
			$this.sub_user_skill_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_skill_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_skill_view_controller.parent_view_controller = $this;
			$this.sub_user_skill_view_controller.initData();
		}

		function afterLoadUserLicenseView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Licenses' ) );
			$this.sub_user_license_view_controller = subViewController;
			$this.sub_user_license_view_controller.parent_key = 'user_id';
			$this.sub_user_license_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_license_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_license_view_controller.parent_view_controller = $this;
			$this.sub_user_license_view_controller.initData();
		}

		function afterLoadUserLanguageView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Languages' ) );
			$this.sub_user_language_view_controller = subViewController;
			$this.sub_user_language_view_controller.parent_key = 'user_id';
			$this.sub_user_language_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_language_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_language_view_controller.parent_view_controller = $this;
			$this.sub_user_language_view_controller.initData();
		}

		function afterLoadUserEducationView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Education' ) );
			$this.sub_user_education_view_controller = subViewController;
			$this.sub_user_education_view_controller.parent_key = 'user_id';
			$this.sub_user_education_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_education_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_education_view_controller.parent_view_controller = $this;
			$this.sub_user_education_view_controller.initData();
		}

		function afterLoadUserMembershipView( subViewController ) {
			$( subViewController.el ).find( '.sub-view-title' ).text( $.i18n._( 'Memberships' ) );
			$this.sub_user_membership_view_controller = subViewController;
			$this.sub_user_membership_view_controller.parent_key = 'user_id';
			$this.sub_user_membership_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_membership_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_membership_view_controller.parent_view_controller = $this;
			$this.sub_user_membership_view_controller.initData();
		}

	},

	initSubCompanyTaxView: function() {
		var $this = this;

		if ( this.sub_company_tax_view_controller ) {
			this.sub_company_tax_view_controller.buildContextMenu( true );
			this.sub_company_tax_view_controller.setDefaultMenu();
			$this.sub_company_tax_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_company_tax_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_company_tax_view_controller.initData();
			return;
		}

		Global.loadViewSource( 'CompanyTaxDeduction', 'CompanyTaxDeductionViewController.js', function() {
			var tab_employee = $this.edit_view_tab.find( '#tab_tax' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'UserContact' + 'View' );
			CompanyTaxDeductionViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_company_tax_view_controller = subViewController;
			$this.sub_company_tax_view_controller.parent_key = 'include_user_id';
			$this.sub_company_tax_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_company_tax_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_company_tax_view_controller.parent_view_controller = $this;
			$this.sub_company_tax_view_controller.initData();
		}

	},

	initSubUserReviewControlView: function() {
		var $this = this;

		if ( this.sub_user_review_control_view_controller ) {
			this.sub_user_review_control_view_controller.buildContextMenu( true );
			this.sub_user_review_control_view_controller.setDefaultMenu();
			$this.sub_user_review_control_view_controller.parent_key = 'user_id';
			$this.sub_user_review_control_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_review_control_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_review_control_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/hr/kpi/UserReviewControlViewController.js', function() {
			var tab_employee = $this.edit_view_tab.find( '#tab_reviews' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'UserReviewControl' + 'View' );
			UserReviewControlViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_review_control_view_controller = subViewController;
			$this.sub_user_review_control_view_controller.parent_key = 'user_id';
			$this.sub_user_review_control_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_review_control_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_review_control_view_controller.parent_view_controller = $this;
			$this.sub_user_review_control_view_controller.initData();
		}

	},

	initSubJobApplicationView: function() {
		var $this = this;

		if ( this.sub_job_application_view_controller ) {
			this.sub_job_application_view_controller.buildContextMenu( true );
			this.sub_job_application_view_controller.setDefaultMenu();
			$this.sub_job_application_view_controller.parent_key = 'interviewer_user_id';
			$this.sub_job_application_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_job_application_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_job_application_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/hr/recruitment/JobApplicationViewController.js', function() {
			var tab_applications = $this.edit_view_tab.find( '#tab_applications' );
			var firstColumn = tab_applications.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'JobApplication' + 'View' );
			JobApplicationViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_job_application_view_controller = subViewController;
			$this.sub_job_application_view_controller.parent_key = 'interviewer_user_id';
			$this.sub_job_application_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_job_application_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_job_application_view_controller.parent_view_controller = $this;
			$this.sub_job_application_view_controller.initData();
		}

	},

	initSubUserContactView: function() {
		var $this = this;

		if ( this.sub_user_contact_view_controller ) {
			this.sub_user_contact_view_controller.buildContextMenu( true );
			this.sub_user_contact_view_controller.setDefaultMenu();
			$this.sub_user_contact_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_contact_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_contact_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/employees/user_contact/UserContactViewController.js', function() {
			var tab_employee = $this.edit_view_tab.find( '#tab_contacts' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'UserContact' + 'View' );
			UserContactViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_contact_view_controller = subViewController;
			$this.sub_user_contact_view_controller.parent_key = 'user_id';
			$this.sub_user_contact_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_user_contact_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_contact_view_controller.parent_view_controller = $this;
			$this.sub_user_contact_view_controller.initData();
		}

	},

	initSubWageView: function() {

		var $this = this;

		if ( this.sub_wage_view_controller ) {
			this.sub_wage_view_controller.buildContextMenu( true );
			this.sub_wage_view_controller.setDefaultMenu();
			$this.sub_wage_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_wage_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_wage_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadScriptAsync( 'views/company/wage/WageViewController.js', function() {

			var tab_employee = $this.edit_view_tab.find( '#tab_wage' );

			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'Wage' + 'View' );
			WageViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_wage_view_controller = subViewController;
			$this.sub_wage_view_controller.parent_key = 'user_id';
			$this.sub_wage_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_wage_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_wage_view_controller.parent_view_controller = $this;
			$this.sub_wage_view_controller.initData(); //Init data in this parent view
		}
	},

	initSubAccrualPolicyUserModifier: function() {

		var $this = this;

		if ( this.sub_accrual_policy_user_modifier_view_controller ) {
			this.sub_accrual_policy_user_modifier_view_controller.buildContextMenu( true );
			this.sub_accrual_policy_user_modifier_view_controller.setDefaultMenu();
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'user_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadScriptAsync( 'views/policy/accrual_policy/AccrualPolicyUserModifierViewController.js', function() {

			var tab_employee = $this.edit_view_tab.find( '#tab_accruals' );

			var firstColumn = tab_employee.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'AccrualPolicyUserModifier' + 'View' );
			AccrualPolicyUserModifierViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView( tpl ) {

			var args = {parent_view: 'employee'};

			return _.template( tpl, args );

		}

		function afterLoadView( subViewController ) {

			$this.sub_accrual_policy_user_modifier_view_controller = subViewController;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_key = 'user_id';
			$this.sub_accrual_policy_user_modifier_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_policy_user_modifier_view_controller.parent_view_controller = $this;
			$this.sub_accrual_policy_user_modifier_view_controller.initData(); //Init data in this parent view
		}
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		if ( parseInt( key ) > 0 ) {

			if ( !this.current_edit_record.hierarchy_control ) {
				this.current_edit_record.hierarchy_control = {};
			}

			this.current_edit_record.hierarchy_control[key] = target.getValue();
		} else {
			this.current_edit_record[key] = target.getValue();
		}

		switch ( key ) {
			case 'first_name':
				var widget = this.edit_view_ui_dic['first_name_1'];
				widget.setValue( target.getValue() );
				break;
			case 'last_name':
				widget = this.edit_view_ui_dic['last_name_1'];
				widget.setValue( target.getValue() );
				break;
			case 'first_name_1':
				widget = this.edit_view_ui_dic['first_name'];
				widget.setValue( target.getValue() );
				break;
			case 'last_name_1':
				widget = this.edit_view_ui_dic['last_name'];
				widget.setValue( target.getValue() );
				break;
			case 'country':
				widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
			case 'default_job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ) );
				}
				break;
			case 'default_job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.onJobQuickSearch( key, target.getValue() );
				}
				break;
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
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
		var job_item_widget = $this.edit_view_ui_dic['default_job_item_id'];
		var current_job_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		var args = {};
		args.filter_data = {
			status_id: 10,
			job_id: $this.current_edit_record.default_job_id,
			company_id: $this.select_company_id
		};
		$this.edit_view_ui_dic['default_job_item_id'].setDefaultArgs( args );

		if ( current_job_item_id ) {

			var new_arg = Global.clone( args );

			new_arg.filter_data.id = current_job_item_id;
			new_arg.filter_columns = $this.edit_view_ui_dic['default_job_item_id'].getColumnFilter();
			new_arg.filter_data.company_id = $this.select_company_id;
			$this.job_item_api.getJobItem( new_arg, {
				onResult: function( task_result ) {
					var data = task_result.getResult();

					if ( data.length > 0 ) {
						job_item_widget.setValue( current_job_item_id );
						$this.current_edit_record.default_job_item_id = current_job_item_id;
					} else {
						setDefaultData();
					}

				}
			} )

		} else {
			setDefaultData();
		}

		function setDefaultData() {

			if ( $this.current_edit_record.default_job_id ) {
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

	hierarchyPermissionValidate: function( p_id, selected_item ) {

		if ( PermissionManager.validate( 'hierarchy', 'edit' ) ||
			PermissionManager.validate( 'user', 'edit_hierarchy' ) ) {

			return true;
		}

		return false;

	},

	setTabStatus: function() {
		if ( this.is_mass_editing ) {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_hierarchy"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_wage"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_tax"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_contacts"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_applications"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_accruals"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_qualifications"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_reviews"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else {

			if ( this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_hierarchy"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_hierarchy"]' ) ).parent().hide();
			}

			if ( PermissionManager.checkTopLevelPermission( 'Wage' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_wage"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_wage"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}
			if ( PermissionManager.checkTopLevelPermission( 'UserTaxDeduction' ) &&
				this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_tax"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_tax"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}
			if ( PermissionManager.checkTopLevelPermission( 'UserContact' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_contacts"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_contacts"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}
			if ( PermissionManager.subJobApplicationValidate( 'JobApplication' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_applications"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_applications"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( PermissionManager.checkTopLevelPermission( 'AccrualPolicy' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_accruals"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_accruals"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( PermissionManager.checkTopLevelPermission( 'Qualification' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_qualifications"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_qualifications"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( PermissionManager.checkTopLevelPermission( 'UserReviewControl' ) && this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_reviews"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_reviews"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( this.subDocumentValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( this.subAuditValidate() ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().show();
			} else {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

		}

	},

	/* jshint ignore:start */
	setCurrentEditRecordData: function() {

		var dont_set_dic = {};
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) || key === 'hierarchy_control' ) {
				switch ( key ) {
					case 'first_name':
					case 'last_name':
						dont_set_dic[key + '_1'] = true;
						var brother = this.edit_view_ui_dic[key + '_1'];
						brother.setValue( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'country':
						this.eSetProvince( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'hierarchy_control':
						if ( this.show_hierarchy ) {
							for ( var h_key in this.current_edit_record.hierarchy_control ) {
								var value = this.current_edit_record.hierarchy_control[h_key];
								widget = this.edit_view_ui_dic[h_key];
								dont_set_dic[h_key] = true;
								widget.setValue( value );
							}
						}

						break;
					case 'default_job_id':
						var args = {};
						args.filter_data = {status_id: 10, user_id: this.current_edit_record.id};
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'default_job_item_id':
						args = {};
						args.filter_data = {status_id: 10, job_id: this.current_edit_record.default_job_id};
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					default:
						if ( !dont_set_dic[key] ) {
							widget.setValue( this.current_edit_record[key] );
							break;
						}
						break;
				}

			}
		}

		if ( this.current_edit_record.id ) {
			this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + this.current_edit_record.id );

			if ( this.is_viewing ) {
				this.file_browser.setEnable( false );
			} else {
				this.file_browser.setEnable( true );
			}

		} else {
			this.file_browser.setImage( null );
			this.file_browser.setEnable( false );
		}

//		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && PermissionManager.checkTopLevelPermission( 'Companies' ) && !this.is_mass_editing ) {
//			this.edit_view_ui_dic['company_id'].setEnabled( true );
//		} else {
//			this.edit_view_ui_dic['company_id'].setEnabled( false );
//		}

		this.edit_view_ui_dic['company_id'].setEnabled( false );

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},
	/* jshint ignore:end */


	onSaveDone: function( result ) {
		if ( this.edit_only_mode && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
			LocalCacheData.current_open_primary_controller.updateSelectUserAndRefresh( this.current_edit_record );
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';
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
			filter.filter_data.id = [selectedId];
			filter.filter_data.company_id = this.select_company_id;

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {
					$this.onCopyAsNewResult( result );

				}
			} );
		}

	},

	onAddClick: function() {
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		LocalCacheData.current_doing_context_action = 'new';
		$this.openEditView();

		$this.api['get' + $this.api.key_name + 'DefaultData']( this.select_company_id, {
			onResult: function( result ) {
				$this.onAddResult( result );

			}
		} );

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
		filter.filter_data.company_id = this.select_company_id;

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
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

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
		filter.filter_data.company_id = this.select_company_id;

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

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

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
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];
		filter.filter_data.company_id = this.select_company_id;

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
			filter.filter_data = {company_id: this.select_company_id}; // search by company_id
			filter.filter_data.id = [this.refresh_id];

			this.last_select_ids = filter.filter_data.id;

		} else {
			this.last_select_ids = this.getGridSelectIdArray();
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();
				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover()
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );
				}
				if ( $this.refresh_id > 0 ) {
					$this.refresh_id = null;
					var grid_source_data = $this.grid.getGridParam( 'data' );
					var len = grid_source_data.length;

					if ( $.type( grid_source_data ) !== 'array' ) {
						grid_source_data = [];
					}

					var found = false;
					var new_record = result_data[0];

					//Error: Uncaught TypeError: Cannot read property 'id' of undefined in https://ondemand1.timetrex.com/interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 4851
					if ( new_record ) {
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
						}

						if ( !found ) {
//					$this.grid.addRowData( new_record.id, new_record, 0 );
							$this.grid.clearGridData();
							$this.grid.setGridParam( {data: grid_source_data.concat( new_record )} );
							$this.grid.trigger( 'reloadGrid' );
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
					$this.grid.trigger( 'reloadGrid' );

					$this.reSelectLastSelectItems();

				}

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

			}
		} );

		if ( filter && filter.filter_data && filter.filter_data.company_id ) {
			this.select_company_id = filter.filter_data.company_id;

			this.user_group_api.getUserGroup( {filter_data: {company_id: this.select_company_id}}, false, false, {
				onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );

					if ( !$this.edit_only_mode ) {
						if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
							$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
							$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
						}

					}

					$this.user_group_array = res;

				}
			} );

		} else {
			this.select_company_id = LocalCacheData.getCurrentCompany().id;
		}

	},

	//Call this from setEditViewData
	initTabData: function() {

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 3 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_wage' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubWageView();
			} else {
				this.edit_view_tab.find( '#tab_wage' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab_selected_index === 4 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_tax' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubCompanyTaxView();
			} else {
				this.edit_view_tab.find( '#tab_tax' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 5 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_contacts' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubUserContactView();
			} else {
				this.edit_view_tab.find( '#tab_contacts' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 6 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_applications' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubJobApplicationView();
			} else {
				this.edit_view_tab.find( '#tab_applications' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 7 ) {
			if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

				if ( this.current_edit_record.id ) {
					this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.initSubAccrualPolicyUserModifier();
				} else {
					this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
					this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
					this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
				}

			} else {
				this.edit_view_tab.find( '#tab_accruals' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
				this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );

			}
		} else if ( this.edit_view_tab_selected_index === 8 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_qualifications' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubQualificationView();
			} else {
				this.edit_view_tab.find( '#tab_qualifications' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 9 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_reviews' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubUserReviewControlView();
			} else {
				this.edit_view_tab.find( '#tab_reviews' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 10 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 11 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		this._super( 'onContextMenuClick', context_btn, menu_name );

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
			case ContextMenuIconName.import_icon:
				ProgressBar.showOverlay();
				this.onImportClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.pay_stub_amendment:
				this.onNavigationClick( id );
				break;

		}
	},

	onImportClick: function() {

		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'user', function() {
			$this.search();
		} );
	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var base_date = new Date().format();

		if ( $this.edit_view && $this.current_edit_record.id ) {
			user_ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				user_ids.push( grid_selected_row.id );
			} );
		}

		switch ( iconName ) {
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, 'Employees', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = {value: user_ids};
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, 'Employees', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case ContextMenuIconName.pay_stub:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, 'Employees', window.location.href );
					IndexViewController.goToView( 'PayStub', filter );
				}
				break;
			case ContextMenuIconName.pay_stub_amendment:
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( this.viewId, 'Employees', window.location.href );
					IndexViewController.goToView( 'PayStubAmendment', filter );
				}
				break;

		}

	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_user_contact_view_controller = null;
		this.sub_wage_view_controller = null;
		this.sub_company_tax_view_controller = null;
		this.sub_document_view_controller = null;
		this.sub_accrual_policy_user_modifier_view_controller = null;
		this.sub_user_review_control_view_controller = null;
		this.sub_job_application_view_controller = null;
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_employee': $.i18n._( 'Employee' ),
			'tab_contact_info': $.i18n._( 'Contact Info' ),
			'tab_hierarchy': $.i18n._( 'Hierarchy' ),
			'tab_wage': $.i18n._( 'Wage' ),
			'tab_tax': $.i18n._( 'Tax' ),
			'tab_contacts': $.i18n._( 'Contacts' ),
			'tab_applications': $.i18n._( 'Applications' ),
			'tab_accruals': $.i18n._( 'Accruals' ),
			'tab_qualifications': $.i18n._( 'Qualifications' ),
			'tab_reviews': $.i18n._( 'Reviews' ),
			'tab_attachment': $.i18n._( 'Attachment' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				id: this.script_name + '_navigation',
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.USER,
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start

		var tab_employee = this.edit_view_tab.find( '#tab_employee' );

		var tab_employee_column1 = tab_employee.find( '.first-column' );
		var tab_employee_column2 = tab_employee.find( '.second-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_employee_column1 );

//		Company
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICompany' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.COMPANY,
			show_search_inputs: true,
			set_empty: true,
			field: 'company_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_employee_column1 );
		form_item_input.setEnabled( false );

////		//Company`
////
//		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
//		form_item_input.TText( {field: 'company'} );
//		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_employee_column1, '' );

		//Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_employee_column1 );

		//Permission Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPermissionControl' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			show_search_inputs: true,
			field: 'permission_control_id',
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Permission Group' ), form_item_input, tab_employee_column1 );

		//Pay Period Schedule

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD_SCHEDULE,
			show_search_inputs: true,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			set_empty: true,
			field: 'pay_period_schedule_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_employee_column1 );

		//Policy Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPolicyGroup' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.POLICY_GROUP,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			show_search_inputs: true,
			set_empty: true,
			field: 'policy_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy Group' ), form_item_input, tab_employee_column1 );

		//Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			show_search_inputs: true,
			field: 'currency_id',
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_employee_column1 );

		//User Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'user_name', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, tab_employee_column1 );

		//Password

		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

		form_item_input.TTextInput( {field: 'password', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Password' ), form_item_input, tab_employee_column1 );

		//Password Confirm

		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );

		form_item_input.TTextInput( {field: 'password_confirm', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Confirm Password' ), form_item_input, tab_employee_column1 );

		//Employee Number

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'employee_number', width: 90} );
		this.addEditFieldToColumn( $.i18n._( 'Employee Number' ), form_item_input, tab_employee_column1 );

		//Title

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.JOB_TITLE,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			show_search_inputs: true,
			set_empty: true,
			field: 'title_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Title' ), form_item_input, tab_employee_column1, '' );

		//Second Column Start

		//First Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'first_name', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_employee_column2, '' );

		//Last Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'last_name', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_employee_column2 );

		//Quick Punch ID

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'phone_id', width: 90} );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch ID' ), form_item_input, tab_employee_column2 );

		//Quick Punch Password

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'phone_password', width: 90} );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch Password' ), form_item_input, tab_employee_column2 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'default_branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), form_item_input, tab_employee_column2 );

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			show_search_inputs: true,
			set_empty: true,
			field: 'default_department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Department' ), form_item_input, tab_employee_column2 );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJob' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				customSearchFilter: (function( args ) {
					return $this.setCompanyIdFilter( args );
				}),
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) job_coder.setValue( val.manual_id );
				}),
				field: 'default_job_id'
			} );

			var widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Default Job' ), [form_item_input, job_coder], tab_employee_column2, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.edit_view_form_item_dic.default_job_id.hide();
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				customSearchFilter: (function( args ) {
					return $this.setCompanyIdFilter( args );
				}),
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {
					if ( val ) job_item_coder.setValue( val.manual_id );
				}),
				field: 'default_job_item_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Default Task' ), [form_item_input, job_item_coder], tab_employee_column2, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.edit_view_form_item_dic.default_job_item_id.hide();
			}
		}
		//Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_group_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Group' ), form_item_input, tab_employee_column2 );

		// Ethnicity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIEthnicGroup' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ETHNIC_GROUP,
			customSearchFilter: (function( args ) {
				return $this.setCompanyIdFilter( args );
			}),
			show_search_inputs: true,
			field: 'ethnic_group_id',
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Ethnicity' ), form_item_input, tab_employee_column2 );

		//Hire Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'hire_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Hire Date' ), form_item_input, tab_employee_column2 );

		//Termination Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'termination_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Termination Date' ), form_item_input, tab_employee_column2 );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( {field: 'tag', object_type_id: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_employee_column2, '', null, null, true );

		//Tab 1 start

		var tab_contact_info = this.edit_view_tab.find( '#tab_contact_info' );

		var tab_contact_info_column1 = tab_contact_info.find( '.first-column' );
		var tab_contact_info_column2 = tab_contact_info.find( '.second-column' );

		// Photo

		if ( typeof FormData == "undefined" ) {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

			this.file_browser = form_item_input.TImageBrowser( {field: '', default_width: 128, default_height: 128} );

			this.file_browser.bind( 'imageChange', function( e, target ) {
				new ServiceCaller().uploadFile( target.getValue(), 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + $this.current_edit_record.id );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			} )
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );

			this.file_browser = form_item_input.TImageAdvBrowser( {
				field: '', default_width: 128, default_height: 128, callBack: function( form_data ) {
					new ServiceCaller().uploadFile( form_data, 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
						onResult: function( result ) {

							if ( result.toLowerCase() === 'true' ) {
								$this.file_browser.setImage( ServiceCaller.userPhoto + '&object_id=' + $this.current_edit_record.id );
							} else {
								TAlertManager.showAlert( result, 'Error' );
							}
						}
					} );

				}
			} );
		}

		this.addEditFieldToColumn( $.i18n._( 'Photo' ), this.file_browser, tab_contact_info_column1, '', null, false, true );

		//First Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'first_name_1', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'First Name' ), form_item_input, tab_contact_info_column1 );

		//Middle Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'middle_name', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Middle Name' ), form_item_input, tab_contact_info_column1 );

		//Last Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'last_name_1', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Last Name' ), form_item_input, tab_contact_info_column1 );

		//Sex
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'sex_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.sex_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Gender' ), form_item_input, tab_contact_info_column1 );

		//Home Address(Line 1)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'address1', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address(Line 1)' ), form_item_input, tab_contact_info_column1 );
		form_item_input.parent().width( '45%' );

		//Home Address(Line 2)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'address2', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Home Address(Line 2)' ), form_item_input, tab_contact_info_column1 );

		form_item_input.parent().width( '45%' );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'city', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_contact_info_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'country', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_contact_info_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'province'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_contact_info_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'postal_code', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_contact_info_column1, '' );

		//Column 2

		//Work Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_contact_info_column2, '' );

		//Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_phone_ext'} );
		form_item_input.css( 'width', '50' );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_contact_info_column2 );

		//Home Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'home_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Home Phone' ), form_item_input, tab_contact_info_column2 );

		//Mobile Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'mobile_phone', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Mobile Phone' ), form_item_input, tab_contact_info_column2 );

		//Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'fax_phone', width: 200} );

		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_contact_info_column2 );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'work_email', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_contact_info_column2 );

		//Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'home_email', width: 200} );
		this.addEditFieldToColumn( $.i18n._( 'Home Email' ), form_item_input, tab_contact_info_column2 );

		//Birth Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'birth_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Birth Date' ), form_item_input, tab_contact_info_column2 );

		//Sin
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'sin', width: 90} );
		this.addEditFieldToColumn( $.i18n._( 'SIN / SSN' ), form_item_input, tab_contact_info_column2 );

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'note', width: '100%'} );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_contact_info_column2, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Tab 2 start
		var tab_hierarchy = this.edit_view_tab.find( '#tab_hierarchy' );

		var tab_hierarchy_column1 = tab_hierarchy.find( '.first-column' );

		this.edit_view_tabs[2] = [];
		this.edit_view_tabs[2].push( tab_hierarchy_column1 );

		if ( this.show_hierarchy ) {

			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'none' );

			var len = this.hierarchy_ui_model.length;

			for ( var i = 0; i < len; i++ ) {
				var ui_model = this.hierarchy_ui_model[i];
				var options = this.hierarchy_options_dic[ui_model.id];
				if ( options ) {
					form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
					form_item_input.TComboBox( {field: ui_model.id} );
					form_item_input.setSourceData( options );
					this.addEditFieldToColumn( ui_model.value, form_item_input, tab_hierarchy_column1 );
				}

			}

		} else {

			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).NoHierarchyBox( {related_view_controller: this} );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'block' );
		}

	},

	initCountryList: function() {

	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	},

	eSetProvince: function( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );

		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}
					$this.e_province_array = Global.buildRecordArray( res );

					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}

					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
	},

	onSetSearchFilterFinished: function() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}

	},

	onBuildBasicUIFinished: function() {
		var basicSearchTabPanel = this.search_panel.find( 'div #basic_search' );
	},

	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	events: {},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var $this = this;
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Company' ),
				in_column: 1,
				field: 'company_id',
				layout_name: ALayoutIDs.COMPANY,
				api_class: (APIFactory.getAPIClass( 'APICompany' )),
				multiple: false,
				custom_first_label: Global.default_item,
				basic_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				adv_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
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
				label: $.i18n._( 'First Name' ),
				in_column: 1,
				field: 'first_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				field: 'last_name',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Home Phone' ),
				field: 'home_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				object_type_id: 200,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee Number' ),
				field: 'employee_number',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Gender' ),
				in_column: 2,
				field: 'sex_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				customSearchFilter: (function( args ) {
					return $this.setCompanyIdFilter( args );
				}),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				customSearchFilter: (function( args ) {
					return $this.setCompanyIdFilter( args );
				}),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 3,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				customSearchFilter: (function( args ) {
					return $this.setCompanyIdFilter( args );
				}),
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 3,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 3,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: false,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'SIN/SSN' ),
				field: 'sin',
				basic_search: false,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} )
		];

	},

	setCompanyIdFilter: function( args ) {

		if ( !args ) {
			args = {filter_data: {company_id: this.select_company_id}};

		} else {
			if ( !args.filter_data ) {
				args.filter_data = {company_id: this.select_company_id};
			} else {
				args.filter_data.company_id = this.select_company_id;
			}
		}

		return args;

	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#employee_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	},

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value, company_id: $this.select_company_id};

			this.job_api.getJob( args, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['default_job_id'].setValue( result_data[0].id );
						$this.current_edit_record.default_job_id = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0] );

					} else {
						$this.edit_view_ui_dic['default_job_id'].setValue( '' );
						$this.current_edit_record.default_job_id = false;
						$this.setJobItemValueWhenJobChanged( false );
					}

				}
			} );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value, company_id: $this.select_company_id};

			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['default_job_item_id'].setValue( result_data[0].id );
						$this.current_edit_record.default_job_item_id = result_data[0].id;

					} else {
						$this.edit_view_ui_dic['default_job_item_id'].setValue( '' );
						$this.current_edit_record.default_job_item_id = false;
					}

				}
			} );
		}

	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.company_id = true;
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
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

	initSubDocumentView: function() {
		var $this = this;

		if ( this.sub_document_view_controller ) {
			this.sub_document_view_controller.buildContextMenu( true );
			this.sub_document_view_controller.setDefaultMenu();
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/document/DocumentViewController.js', function() {
			var tab_contact_info = $this.edit_view_tab.find( '#tab_attachment' );
			var firstColumn = tab_contact_info.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Document' + 'View' );
			DocumentViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_document_view_controller = subViewController;
			$this.sub_document_view_controller.parent_key = 'object_id';
			$this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_document_view_controller.document_object_type_id = $this.document_object_type_id;
			$this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_document_view_controller.parent_view_controller = $this;
			$this.sub_document_view_controller.initData();
		}

	}

} );

EmployeeViewController.loadView = function() {

	Global.loadViewSource( 'Employee', 'EmployeeView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};
