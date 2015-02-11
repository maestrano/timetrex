CompanyTaxDeductionViewController = BaseViewController.extend( {

	el: '#company_tax_deduction_view_container', //Must set el here and can only set string, so events can work
	type_array: null,
	status_array: null,
	calculation_array: null,
	account_amount_type_array: null,
	us_eic_filing_status_array: null,
	federal_filing_status_array: null,
	apply_frequency_array: null,
	length_of_service_unit_array: null,
	month_of_year_array: null,
	day_of_month_array: null,
	month_of_quarter_array: null,
	country_array: null,
	province_array: null,
	e_province_array: null,
	company_api: null,
	date_api: null,
	user_deduction_api: null,
	employee_setting_grid: null,
	employee_setting_result: null,
	final_c_id: null, // calculation_id from setDynamic function. use to set employee setting grid
	show_c: false,
	show_p: false,
	show_dc: false,

	provice_district_array: null,

	original_current_record: null, //set when setCurrentEditRecordData, to keep the original data of the edit record

	initialize: function() {

		this._super( 'initialize' );
		this.edit_view_tpl = 'CompanyTaxDeductionEditView.html';
		this.permission_id = 'company_tax_deduction';
		this.viewId = 'CompanyTaxDeduction';
		this.script_name = 'CompanyTaxDeductionView';
		this.table_name_key = 'company_deduction';
		this.context_menu_name = $.i18n._( 'Tax / Deductions' );
		this.navigation_label = $.i18n._( 'Tax / Deductions' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APICompanyDeduction' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.user_deduction_api = new (APIFactory.getAPIClass( 'APIUserDeduction' ))();
		this.month_of_quarter_array = Global.buildRecordArray( {0: 1, 1: 2, 2: 3} );
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.document_object_type_id = 300;

		this.render();
		if ( this.sub_view_mode ) {

			this.invisible_context_menu_dic[ContextMenuIconName.view] = true;
			this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;
			this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
			this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
			this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
			this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;

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

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'calculation' );
		this.initDropDownOption( 'apply_frequency' );
		this.initDropDownOption( 'account_amount_type' );
		this.initDropDownOption( 'account_amount_type' );
		this.initDropDownOption( 'length_of_service_unit' );
		this.initDropDownOption( 'look_back_unit' );
		this.initDropDownOption( 'country', 'country', this.company_api );
		this.initDropDownOption( 'us_eic_filing_status' );
		this.initDropDownOption( 'us_medicare_filing_status' );

		this.initDropDownOption( 'us_eic_filing_status' );
		this.initDropDownOption( 'federal_filing_status' );
		this.initDropDownOption( 'state_dc_filing_status' );
		this.initDropDownOption( 'state_al_filing_status' );
		this.initDropDownOption( 'state_ct_filing_status' );
		this.initDropDownOption( 'state_dc_filing_status' );
		this.initDropDownOption( 'state_de_filing_status' );
		this.initDropDownOption( 'state_nj_filing_status' );
		this.initDropDownOption( 'state_nc_filing_status' );
		this.initDropDownOption( 'state_ma_filing_status' );
		this.initDropDownOption( 'state_ok_filing_status' );
		this.initDropDownOption( 'state_ga_filing_status' );
		this.initDropDownOption( 'state_la_filing_status' );
		this.initDropDownOption( 'state_me_filing_status' );
		this.initDropDownOption( 'state_wv_filing_status' );
		this.initDropDownOption( 'state_filing_status' );

		this.company_api.getOptions( 'district', {
			onResult: function( res ) {
				res = res.getResult();
				$this.district_array = res;
			}
		} );

		this.date_api.getMonthOfYearArray( false, {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = Global.buildRecordArray( res );
			}
		} );
		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = Global.buildRecordArray( res );
			}
		} );

	},

	hideSubViewTabs: function() {

		if ( this.sub_view_mode ) {
			$( this.edit_view_tab.find( 'ul li' )[0] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[1] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[2] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[3] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[4] ).hide();
			$( this.edit_view_tab.find( 'ul li' )[5] ).hide();
		} else {
			$( this.edit_view_tab.find( 'ul li' )[5] ).hide();
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
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 4 ) {

			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else if ( this.edit_view_tab_selected_index === 2 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_employee_setting' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.setEmployeeGridSize();
				this.buildContextMenu( true );
				this.setEditMenu();
			} else {
				this.edit_view_tab.find( '#tab_employee_setting' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}

		} else {
			this.buildContextMenu( true );
			this.setEditMenu();
		}
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || (this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id )) ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.addClass( 'disable-image' );
	},

	saveInsideEditorData: function( callBack ) {
		var $this = this;

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}

		}

		var data = this.employee_setting_grid.getGridParam( 'data' );
		var columns = this.employee_setting_grid.getGridParam( 'colModel' );

		for ( var i = 0; i < data.length; i++ ) {
			var item = data[i];
			for ( var j = 1; j < columns.length; j++ ) {
				var column = columns[j];
				if ( item[column.name] === this.original_current_record[column.name] ) {
					item[column.name] = false;  //Default column setting
				}
			}
		}

		this.user_deduction_api.setUserDeduction( data, {
			onResult: function() {
				if ( Global.isSet( callBack ) ) {
					callBack();
				}
			}
		} );

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
			this.employee_setting_grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		switch ( id ) {
			case ContextMenuIconName.add:
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
			case ContextMenuIconName.download:
				this.onDownloadClick();
				break;

		}

	},
	/* jshint ignore:end */
	onDeleteAndNextClick: function() {
		var $this = this;
		$this.is_add = false;

		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];

			if ( !$this.sub_view_mode ) {
				if ( $this.edit_view ) {
					remove_ids.push( $this.current_edit_record.id );
				}

				if ( result ) {

					ProgressBar.showOverlay();
					$this.api['delete' + $this.api.key_name]( remove_ids, {
						onResult: function( result ) {
							$this.onDeleteAndNextResult( result, remove_ids );

						}
					} );

				} else {
					ProgressBar.closeOverlay();
				}
			} else {
				if ( $this.edit_view ) {
					remove_ids.push( $this.employee_setting_result[0].id );
				}

				if ( result ) {

					ProgressBar.showOverlay();
					$this.user_deduction_api.deleteUserDeduction( remove_ids, {
						onResult: function( result ) {
							$this.onDeleteAndNextResult( result, remove_ids );

						}
					} );

				} else {
					ProgressBar.closeOverlay();
				}
			}

		} );
	},

	onDeleteClick: function() {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( Global.delete_confirm_message, null, function( result ) {

			var remove_ids = [];

			if ( !$this.sub_view_mode ) {
				if ( $this.edit_view ) {
					remove_ids.push( $this.current_edit_record.id );
				} else {
					remove_ids = $this.getGridSelectIdArray().slice();
				}
				if ( result ) {
					ProgressBar.showOverlay();
					$this.api['delete' + $this.api.key_name]( remove_ids, {
						onResult: function( result ) {
							$this.onDeleteResult( result, remove_ids );
						}
					} );

				} else {
					ProgressBar.closeOverlay();
				}
			} else {
				if ( $this.edit_view ) {
					remove_ids.push( $this.employee_setting_result[0].id );
				} else {
					var args = {filter_data: {}};
					var tax_ids = $this.getGridSelectIdArray().slice();
					args.filter_data.company_deduction_id = tax_ids;
					args.filter_data.user_id = $this.parent_value;

					var res = $this.user_deduction_api.getUserDeduction( args, true, {async: false} ).getResult();

					for ( var i = 0; i < res.length; i++ ) {
						var item = res[i];
						remove_ids.push( item.id );

					}

				}

				if ( result ) {
					ProgressBar.showOverlay();
					$this.user_deduction_api.deleteUserDeduction( remove_ids, {
						onResult: function( result ) {
							$this.onDeleteResult( result, remove_ids );
						}
					} );

				} else {
					ProgressBar.closeOverlay();
				}
			}

		} );

	},

	onSaveClick: function() {

		var $this = this;
		var record;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';
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

		record = this.uniformVariable( record );

		if ( !this.sub_view_mode || this.current_edit_record.id ) {
			this.api['set' + this.api.key_name]( record, {
				onResult: function( result ) {

					$this.onSaveResult( result );

				}
			} );

		} else {
			this.user_deduction_api.setUserDeduction( record, {
				onResult: function( result ) {

					$this.onSaveResult( result );

				}
			} );
		}

	},

	uniformVariable: function( record ) {
		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {

			record = [];

			var selected_items = this.edit_view_ui_dic.company_tax_deduction_ids.getValue();
			for ( var i = 0; i < selected_items.length; i++ ) {
				var new_record = {};
				new_record.user_id = this.parent_value;
				new_record.company_deduction_id = selected_items[i].id;
				record.push( new_record );
			}

		}

		return record;
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();

			if ( this.sub_view_mode ) {

				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id; // as add
					$this.saveInsideEditorData( function() {

						$this.search();
						$this.onSaveDone( result );
						$this.current_edit_record = null;
						$this.removeEditView();

					} );
				} else if ( result_data > 0 ) { // as new
					$this.search();
					$this.onSaveDone( result );
					$this.current_edit_record = null;
					$this.removeEditView();
				}

			} else {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id; // as add
				} else if ( result_data > 0 ) { // as new
					$this.refresh_id = result_data;
				}

				$this.saveInsideEditorData( function() {

					$this.search();
					$this.onSaveDone( result );
					$this.current_edit_record = null;
					$this.removeEditView();
				} );
			}

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndContinueResult: function( result ) {

		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onEditClick( $this.refresh_id, true );

				$this.onSaveAndContinueDone( result );

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNewResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) { // as new
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onAddClick( true );

			} );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndCopyResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onCopyAsNewClick: function() {
		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		var selectedId;

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';

			this.employee_setting_grid.clearGridData();
			$this.edit_view_ui_dic.calculation_id.setEnabled( true );
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
				selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {

					$this.onCopyAsNewResult( result );

				}
			} );
		}

	},

	clearEditViewData: function() {

		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			this.edit_view_ui_dic[key].setValue( null );
			this.edit_view_ui_dic[key].clearErrorStyle();
		}

		if ( this.employee_setting_grid ) {
			this.employee_setting_grid.clearGridData();
		}

	},

	onSaveAndNextResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.onRightArrowClick();
				$this.search( false );
				$this.onSaveAndNextDone( result );

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
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
			var tab_eligibility = $this.edit_view_tab.find( '#tab_attachment' );
			var firstColumn = tab_eligibility.find( '.first-column-sub-view' );
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

	},

	setTabStatus: function() {

		this.hideSubViewTabs();

		if ( !this.sub_view_mode ) {
			$( this.edit_view_tab.find( 'ul li a[ref="tab_tax_deductions"]' ) ).parent().show();
		}

		if ( this.is_mass_editing || this.sub_view_mode ) {

			if ( this.is_mass_editing ) {
				$( this.edit_view_tab.find( 'ul li a[ref="tab_employee_setting"]' ) ).parent().hide();
				$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
				this.edit_view_tab.tabs( 'select', 0 );
			}

			if ( this.sub_view_mode ) {

				if ( this.current_edit_record.id ) {
					$( this.edit_view_tab.find( 'ul li a[ref="tab_employee_setting"]' ) ).parent().show();
					this.edit_view_tab.tabs( 'select', 2 );
				} else {
					$( this.edit_view_tab.find( 'ul li a[ref="tab5"]' ) ).parent().show();
					this.edit_view_tab.tabs( 'select', 5 );
				}

			}

		} else {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_employee_setting"]' ) ).parent().show();
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

		this.editFieldResize( 0 );

	},

	initTabData: function() {
		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {
			if ( !this.current_edit_record || !this.current_edit_record.id ) {

				this.edit_view_tab.find( '#tab_employee_setting' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 3 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_attachment' );
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );

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
					province_widget.setSourceData( $this.e_province_array );
					$this.setProvinceVisibility();

				}
			} );
		}
	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {

			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				var widget_2 = this.edit_view_ui_dic['district'];
				this.eSetProvince( c_value );
				widget.setValue( null );
				widget_2.setValue( null );
				this.current_edit_record.province = false;
				this.current_edit_record.district = false;
				this.setDynamicFields( null, true );
				break;
			case 'province':
				widget_2 = this.edit_view_ui_dic['district'];
				this.setDistrict( this.current_edit_record['country'] );
				widget_2.setValue( null );
				this.setDynamicFields( null, true );
				break;
			case 'calculation_id':
				this.setDynamicFields();
				break;
			case 'apply_frequency_id':
				this.onApplyFrequencyChange();
				break;
			case 'minimum_length_of_service_unit_id':
			case 'maximum_length_of_service_unit_id':
				this.onLengthOfServiceChange();
				break;

		}

		if ( key === 'country' ) {
			return;
		}
		if ( !doNotValidate ) {
			this.validate();
		}

	},

	setCurrentEditRecordData: function() {
		var $this = this;

		this.original_current_record = Global.clone( this.current_edit_record );

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.eSetProvince( this.current_edit_record[key] );
						this.setDistrict( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( $this.current_edit_record.id ) {
			$this.edit_view_ui_dic.calculation_id.setEnabled( false );
		} else {
			$this.edit_view_ui_dic.calculation_id.setEnabled( true );
		}

		this.setDynamicFields( function() {
			$this.collectUIDataToCurrentEditRecord();
			$this.onLengthOfServiceChange();
			$this.setEditViewDataDone();
		} );

		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {
			this.initCompanyTaxDeductionData();
		}
	},

	onLengthOfServiceChange: function() {
		if ( this.current_edit_record['minimum_length_of_service_unit_id'] === 50 || this.current_edit_record['maximum_length_of_service_unit_id'] === 50 ) {
			this.edit_view_form_item_dic['length_of_service_contributing_pay_code_policy_id'].css( 'display', 'block' );
		} else {
			this.edit_view_form_item_dic['length_of_service_contributing_pay_code_policy_id'].css( 'display', 'none' );
		}

		this.editFieldResize();
	},

	initCompanyTaxDeductionData: function() {

		var $this = this;
		this.api.getCompanyDeduction( null, true, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.edit_view_ui_dic.company_tax_deduction_ids.setUnselectedGridData( result_data );
			}
		} );
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onApplyFrequencyChange();
		this.initEmployeeSetting();

	},

	setDistrict: function( c ) {
		var $this = this;
		var district_widget = $this.edit_view_ui_dic['district'];

		$this.provice_district_array = [];
		district_widget.setSourceData( $this.provice_district_array );
		if ( c ) {
			var pd_array = this.district_array[c];

			if ( pd_array ) {
				var pd_array_item = pd_array[$this.current_edit_record.province];

				if ( pd_array_item ) {
					$this.provice_district_array = Global.buildRecordArray( pd_array_item );
					district_widget.setSourceData( $this.provice_district_array );
				}

			}
		}

		$this.setDistrictVisibility();
	},

	hideAllDynamicFields: function( keepC, keepP ) {

		if ( !this.edit_view ) {
			return;
		}

		if ( !keepC ) {
			this.show_c = false;
			this.edit_view_form_item_dic.country.hide();
		}

		if ( !keepP ) {
			this.show_p = false;
			this.show_dc = false;
			this.edit_view_form_item_dic.province.hide();
			this.edit_view_form_item_dic.district.hide();
		}

		this.edit_view_form_item_dic.df_0.hide();
		this.edit_view_form_item_dic.df_1.hide();
		this.edit_view_form_item_dic.df_2.hide();
		this.edit_view_form_item_dic.df_3.hide();
		this.edit_view_form_item_dic.df_4.hide();
		this.edit_view_form_item_dic.df_5.hide();
		this.edit_view_form_item_dic.df_6.hide();
		this.edit_view_form_item_dic.df_7.hide();
		this.edit_view_form_item_dic.df_8.hide();
		this.edit_view_form_item_dic.df_9.hide();
		this.edit_view_form_item_dic.df_10.hide();
		this.edit_view_form_item_dic.df_11.hide();
		this.edit_view_form_item_dic.df_12.hide();
		this.edit_view_form_item_dic.df_14.hide();

		if ( !( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) ) {
			this.edit_view_form_item_dic.df_100.hide();
		}

	},

	initEmployeeSetting: function() {

		var $this = this;

		if ( !$this.edit_view ) {
			return;
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			$this.employee_setting_result = [];
			$this.buildEmployeeSettingGrid();
			return;
		}

		var args = {filter_data: {}};

		args.filter_data.company_deduction_id = this.current_edit_record.id;

		if ( this.sub_view_mode ) {
			args.filter_data.user_id = this.parent_value;
		}

		this.user_deduction_api.getUserDeduction( args, true, {
			onResult: function( result ) {

				if ( !$this.edit_view ) {
					return;
				}

				$this.employee_setting_result = result.getResult();
				$this.buildEmployeeSettingGrid();
			}
		} );

	},
	/* jshint ignore:start */
	buildEmployeeSettingGrid: function() {
		var $this = this;

		var grid = this.edit_view.find( '#employee_setting_grid' );
		var column_info_array = [];
		var column_options_string = '';
		var columnOptions = [];

		var column_info = {
			name: 'user_name',
			index: 'user_name',
			label: $.i18n._( 'Employees' ),
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		switch ( this.final_c_id ) {
			case '10':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '15':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Wage Base/Maximum Earnings' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '17':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Annual Fixed Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				break;
			case '18':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Wage Base/Maximum Earnings' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Exempt Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '19':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Annual Fixed Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '20':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '30':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Annual Amount Greater Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Annual Amount Less Than' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Annual Deduction Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '52':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Target Balance/Limit' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '69':
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Value1' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Value2' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Value3' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value4',
					index: 'user_value4',
					label: $.i18n._( 'Value4' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'Value5' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value6',
					index: 'user_value6',
					label: $.i18n._( 'Value6' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value7',
					index: 'user_value7',
					label: $.i18n._( 'Value7' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value8',
					index: 'user_value8',
					label: $.i18n._( 'Value8' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value9',
					index: 'user_value9',
					label: $.i18n._( 'Value9' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value10',
					index: 'user_value10',
					label: $.i18n._( 'Value10' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case '80':
			case '82':
				columnOptions = this.us_eic_filing_status_array;

				for ( var i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );
				break;
			case "100-CA":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Claim Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "100-US":
				columnOptions = this.federal_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "100-CR":
				columnOptions = this.federal_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-CA":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Claim Amount' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-AZ":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Percent' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-AL":
				columnOptions = this.state_al_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-CT":
				columnOptions = this.state_ct_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );
				break;
			case "200-US-DC":
				columnOptions = this.state_dc_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-DE":
				columnOptions = this.state_de_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-NJ":
				columnOptions = this.state_nj_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-NC":
				columnOptions = this.state_nc_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-MA":
				columnOptions = this.state_ma_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-MD":
				columnOptions = this.state_dc_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Country Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-OK":
				columnOptions = this.state_ok_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-GA":
				columnOptions = this.state_ga_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Employee / Spouse Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Dependent Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-IL":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'IL-W-4 Line 1' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'IL-W-4 Line 2' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-OH":
				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-VA":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Age 65/Blind' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-IN":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-LA":
				columnOptions = this.state_la_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Exemptions' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-ME":
				columnOptions = this.state_me_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-WI":
				columnOptions = this.federal_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US-WV":
				columnOptions = this.state_wv_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "200-US":
			case "300-US":
				columnOptions = this.state_filing_status_array;

				for ( i = 0; i < columnOptions.length; i++ ) {

					if ( i !== columnOptions.length - 1 ) {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label + ';';
					} else {
						column_options_string += columnOptions[i].fullValue + ':' + columnOptions[i].label;
					}

				}

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Filing Status' ),
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: column_options_string}
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "300-US-IN":
				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'District / County Name' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Dependents' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value3',
					index: 'user_value3',
					label: $.i18n._( 'County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "300-US-MD":
				column_info = {
					name: 'user_value5',
					index: 'user_value5',
					label: $.i18n._( 'District / County Name' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'Allowances' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;
			case "300-US-PERCENT":
				column_info = {
					name: 'user_value1',
					index: 'user_value1',
					label: $.i18n._( 'District / County Name' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );

				column_info = {
					name: 'user_value2',
					index: 'user_value2',
					label: $.i18n._( 'District / County Rate' ),
					width: 100,
					sortable: false,
					title: false,
					editable: true,
					edittype: 'text'
				};
				column_info_array.push( column_info );
				break;

		}

		if ( !this.employee_setting_grid ) {
			this.employee_setting_grid = grid;

			this.employee_setting_grid = this.employee_setting_grid.jqGrid( {
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
					if ( id && !$this.is_viewing ) {

						if ( $this.select_grid_last_row ) {
							$this.employee_setting_grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						}
						$this.employee_setting_grid.jqGrid( 'editRow', id, true );
						$this.select_grid_last_row = id;
					}
				}

			} );

		} else {

			this.employee_setting_grid.jqGrid( 'GridUnload' );
			this.employee_setting_grid = null;

			grid = this.edit_view.find( '#employee_setting_grid' );
			this.employee_setting_grid = $( grid );
			this.employee_setting_grid = this.employee_setting_grid.jqGrid( {
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
					if ( id && !$this.is_viewing ) {

						if ( $this.select_grid_last_row ) {
							$this.employee_setting_grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						}

						$this.employee_setting_grid.jqGrid( 'editRow', id, true );
						$this.select_grid_last_row = id;
					}
				}
			} );

		}

		$this.setEmployeeSettingGridData( column_info_array );
	},

	setEmployeeSettingGridData: function( column_info_array ) {

		var $this = this;
		var grid_source = [];
		if ( $.type( this.employee_setting_result ) === 'array' ) {
			grid_source = this.employee_setting_result.slice();
		}

		var len = grid_source.length;
		for ( var i = 0; i < len; i++ ) {
			var item = grid_source[i];
			item.user_name = item.first_name + ' ' + item.last_name;

			for ( var j = 1; j < column_info_array.length; j++ ) {

				var column = column_info_array[j];
				if ( !item[column.name] ) {
					item[column.name] = this.current_edit_record[column.name] ? this.current_edit_record[column.name] : '';
				}
			}

		}

		$this.employee_setting_grid.clearGridData();
		$this.employee_setting_grid.setGridParam( {data: grid_source} );
		$this.employee_setting_grid.trigger( 'reloadGrid' );
		this.removeEmployeeSettingNoResultCover();

		this.setEmployeeGridSize();

		if ( grid_source.length < 1 && this.current_edit_record.id ) {
			this.showEmployeeSettingNoResultCover();
		}

	},

	showEmployeeSettingNoResultCover: function() {

		this.removeEmployeeSettingNoResultCover();
		this.employee_setting_no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.employee_setting_no_result_box.NoResultBox( {related_view_controller: this, is_new: false} );
		this.employee_setting_no_result_box.attr( 'id', this.ui_id + 'employee_setting_no_result_box' );
		var grid_div = this.edit_view.find( '.employee-setting-grid-div' );

		grid_div.append( this.employee_setting_no_result_box );

	},

	removeEmployeeSettingNoResultCover: function() {
		if ( this.employee_setting_no_result_box && this.employee_setting_no_result_box.length > 0 ) {
			this.employee_setting_no_result_box.remove();
		}
		this.employee_setting_no_result_box = null;
	},

	setEmployeeGridSize: function() {

		if ( !this.employee_setting_grid ) {
			return;
		}

		var tab_employee_setting = this.edit_view.find( '#tab_employee_setting_content_div' );
		this.employee_setting_grid.setGridWidth( tab_employee_setting.width() );
		this.employee_setting_grid.setGridHeight( tab_employee_setting.height() );

	},

	setCountryVisibility: function() {

		if ( this.show_c ) {
			this.edit_view_form_item_dic.country.show();
		} else {
			this.edit_view_form_item_dic.country.hide();
		}

	},

	setProvinceVisibility: function() {
		if ( this.show_p && this.e_province_array && this.e_province_array.length > 1 ) {
			this.edit_view_form_item_dic.province.show();
		} else {
			this.edit_view_form_item_dic.province.hide();
		}
	},

	setDistrictVisibility: function() {

		if ( this.show_dc && this.provice_district_array && this.provice_district_array.length > 1 ) {
			this.edit_view_form_item_dic.district.show();
		} else {
			this.edit_view_form_item_dic.district.hide();
		}
	},

	setDynamicFields: function( callBack, countryOrP ) {

		var $this = this;
		if ( !this.current_edit_record.calculation_id ) {
			this.current_edit_record.calculation_id = '10';
			this.edit_view_ui_dic.calculation_id.setValue( 10 );
		}

		var c_id = this.current_edit_record.calculation_id;

		if ( !countryOrP ) {
			this.hideAllDynamicFields();
			this.api.isCountryCalculationID( c_id, {
				onResult: function( result_1 ) {
					var res_data_1 = result_1.getResult();

					if ( res_data_1 === true ) {
						$this.show_c = true;
						$this.setCountryVisibility();
						$this.api.isProvinceCalculationID( c_id, {
							onResult: function( result_2 ) {
								var res_data_2 = result_2.getResult();
								if ( res_data_2 === true ) {
									$this.show_p = true;

									if ( $this.current_edit_record.country ) {
										$this.eSetProvince( $this.current_edit_record.country );
									}

									$this.api.isDistrictCalculationID( c_id, {
										onResult: function( result_3 ) {
											var res_data_3 = result_3.getResult();

											if ( res_data_3 === true ) {
												$this.show_dc = true;

												if ( $this.current_edit_record.country ) {
													$this.setDistrict( $this.current_edit_record.country );
												}

											}

											$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, $this.current_edit_record.province, {onResult: getCaResult} );

										}
									} );

								} else {
									$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, '', {onResult: getCaResult} );
								}

							}
						} );

					} else {
						$this.hideAllDynamicFields();

						$this.api.getCombinedCalculationID( c_id, '', '', {onResult: getCaResult} );
					}

				}
			} );
		} else {
			if ( !this.show_p ) {
				$this.hideAllDynamicFields( true, false );
				$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, '', {onResult: getCaResult} );
			} else {
				$this.hideAllDynamicFields( true, true );
				$this.api.getCombinedCalculationID( c_id, $this.current_edit_record.country, $this.current_edit_record.province, {onResult: getCaResult} );
			}
		}

		function getCaResult( result ) {

			if ( !$this.edit_view ) {
				return;
			}

			var result_data = result.getResult();
			$this.final_c_id = result_data;

			switch ( result_data ) {
				case '10':
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );
					break;
				case '15':
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Wage Base/Maximum Earnings' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					break;
				case '17':
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );

					$this.edit_view_form_item_dic.df_4.show();
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Fixed Amount' ) + ": " );
					$this.edit_view_ui_dic.df_4.setField( 'user_value5' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value5 );
					break;
				case '18':
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Wage Base/Maximum Earnings' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Exempt Amount' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );

					break;

				case '19':  //Advanced Percent (Tax Bracket Alt)
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value4 );

					$this.edit_view_form_item_dic.df_4.show();
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Fixed Amount' ) + ": " );
					$this.edit_view_ui_dic.df_4.setField( 'user_value5' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value5 );
					break;
				case '20': //Fixed Amount
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );
					break;
				case '30': //Fixed Amount(Range Bracket)
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Greater Than' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Amount Less Than' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value3 );

					$this.edit_view_form_item_dic.df_4.show();
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Annual Deduction Amount' ) + ": " );
					$this.edit_view_ui_dic.df_4.setField( 'user_value4' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value4 );

					break;

				case '52': //Fixed Amount (w/target)
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Target Balance/Limit' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case '69':

					if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

						$this.edit_view_form_item_dic.df_1.show();
						$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 1' ) + ": " );
						$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
						$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

						$this.edit_view_form_item_dic.df_2.show();
						$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 2' ) + ": " );
						$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
						$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

						$this.edit_view_form_item_dic.df_3.show();
						$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 3' ) + ": " );
						$this.edit_view_ui_dic.df_3.setField( 'user_value3' );
						$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value3 );

						$this.edit_view_form_item_dic.df_4.show();
						$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 4' ) + ": " );
						$this.edit_view_ui_dic.df_4.setField( 'user_value4' );
						$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value4 );

						$this.edit_view_form_item_dic.df_5.show();
						$this.edit_view_form_item_dic.df_5.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 5' ) + ": " );
						$this.edit_view_ui_dic.df_5.setField( 'user_value5' );
						$this.edit_view_ui_dic.df_5.setValue( $this.current_edit_record.user_value5 );

						$this.edit_view_form_item_dic.df_6.show();
						$this.edit_view_form_item_dic.df_6.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 6' ) + ": " );
						$this.edit_view_ui_dic.df_6.setField( 'user_value6' );
						$this.edit_view_ui_dic.df_6.setValue( $this.current_edit_record.user_value6 );

						$this.edit_view_form_item_dic.df_7.show();
						$this.edit_view_form_item_dic.df_7.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 7' ) + ": " );
						$this.edit_view_ui_dic.df_7.setField( 'user_value7' );
						$this.edit_view_ui_dic.df_7.setValue( $this.current_edit_record.user_value7 );

						$this.edit_view_form_item_dic.df_8.show();
						$this.edit_view_form_item_dic.df_8.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 8' ) + ": " );
						$this.edit_view_ui_dic.df_8.setField( 'user_value8' );
						$this.edit_view_ui_dic.df_8.setValue( $this.current_edit_record.user_value8 );

						$this.edit_view_form_item_dic.df_9.show();
						$this.edit_view_form_item_dic.df_9.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 9' ) + ": " );
						$this.edit_view_ui_dic.df_9.setField( 'user_value9' );
						$this.edit_view_ui_dic.df_9.setValue( $this.current_edit_record.user_value9 );

						$this.edit_view_form_item_dic.df_10.show();
						$this.edit_view_form_item_dic.df_10.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Custom Variable 10' ) + ": " );
						$this.edit_view_ui_dic.df_10.setField( 'user_value10' );
						$this.edit_view_ui_dic.df_10.setValue( $this.current_edit_record.user_value10 );

						$this.edit_view_form_item_dic.df_11.show();
						$this.edit_view_form_item_dic.df_11.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Formula' ) + ": " );
						$this.edit_view_ui_dic.df_11.setField( 'company_value1' );
						$this.edit_view_ui_dic.df_11.setValue( $this.current_edit_record.company_value1 );

						$this.edit_view_form_item_dic.df_12.show();
						$this.edit_view_form_item_dic.df_12.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Look Back Period' ) + ": " );
						$this.edit_view_ui_dic.df_12.setField( 'company_value2' );
						$this.edit_view_ui_dic.df_12.setValue( $this.current_edit_record.company_value2 );
						$this.edit_view_ui_dic.df_13.setField( 'company_value3' );
						$this.edit_view_ui_dic.df_13.setValue( $this.current_edit_record.company_value3 );
					} else {
						$this.edit_view_form_item_dic.df_100.show();
						$this.edit_view_ui_dic.df_100.html( Global.getUpgradeMessage() );
					}
					break;

				case '80': //US - Advanced EIC Formula
				case '82':
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.us_eic_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );
					break;
				case '100-CA': //Federal Income Tax Formula -- Ca
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Claim Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );
					break;
				case '100-US':
				case "100-CR" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.federal_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-CA': //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Claim Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );
					break;
				case '200-US-AZ': //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_0.show();
					$this.edit_view_form_item_dic.df_0.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Percent' ) + ": " );
					$this.edit_view_ui_dic.df_0.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_0.setValue( $this.current_edit_record.user_value1 );
					break;
				case '200-US-AL':
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_al_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case '200-US-CT': //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ct_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );
					break;
				case '200-US-DC': //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_dc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case '200-US-DE': //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_de_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-NJ" : //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_nj_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-NC" : //Province/State Income TaxFormula -- CA-AB
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_nc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-MA" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ma_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-OK" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ok_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-GA" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_ga_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Employee / Spouse Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependent Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value3 );
					break;
				case "200-US-IL" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'IL-W-4 Line 1' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'IL-W-4 Line 2' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-OH" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-VA" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Age 65/Blind' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US-IN" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;
				case "200-US-LA" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_la_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value3 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Exemptions' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US-ME" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_me_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US-WI" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.federal_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US-WV" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_wv_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case "200-US-MD" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_dc_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Amount' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value3 );

					break;
				case "300-US" :
					$this.edit_view_form_item_dic.df_14.show();
					$this.edit_view_form_item_dic.df_14.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Filing Status' ) + ": " );
					$this.edit_view_ui_dic.df_14.setSourceData( $this.state_filing_status_array );
					$this.edit_view_ui_dic.df_14.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_14.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.user_value2 );

					break;
				case "300-US-IN" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Dependents' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value2 );

					$this.edit_view_form_item_dic.df_4.show();
					$this.edit_view_form_item_dic.df_4.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ": " );
					$this.edit_view_ui_dic.df_4.setField( 'user_value3' );
					$this.edit_view_ui_dic.df_4.setValue( $this.current_edit_record.user_value3 );
					break;
				case "300-US-MD" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'County Rate' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value1' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value1 );

					$this.edit_view_form_item_dic.df_3.show();
					$this.edit_view_form_item_dic.df_3.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Allowances' ) + ": " );
					$this.edit_view_ui_dic.df_3.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_3.setValue( $this.current_edit_record.user_value2 );
					break;
				case "300-US-PERCENT" :
					$this.edit_view_form_item_dic.df_1.show();
					$this.edit_view_form_item_dic.df_1.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Name' ) + ": " );
					$this.edit_view_ui_dic.df_1.setField( 'company_value1' );
					$this.edit_view_ui_dic.df_1.setValue( $this.current_edit_record.company_value1 );

					$this.edit_view_form_item_dic.df_2.show();
					$this.edit_view_form_item_dic.df_2.find( '.edit-view-form-item-label' ).text( $.i18n._( 'District / County Rate' ) + ": " );
					$this.edit_view_ui_dic.df_2.setField( 'user_value2' );
					$this.edit_view_ui_dic.df_2.setValue( $this.current_edit_record.user_value2 );
					break;

			}

			var key = $this.edit_view_tab_selected_index;
			$this.editFieldResize( key );

			if ( callBack ) {
				callBack();
			}
		}

	},

	onApplyFrequencyChange: function() {
		if ( this.current_edit_record.apply_frequency_id === 10 ||
			this.current_edit_record.apply_frequency_id === 100 ||
			this.current_edit_record === 110 ||
			this.current_edit_record.apply_frequency_id === 120 ||
			this.current_edit_record.apply_frequency_id === 130 ) {

			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );

		} else if ( this.current_edit_record.apply_frequency_id === 20 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		} else if ( this.current_edit_record.apply_frequency_id === 25 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'block' );
		} else if ( this.current_edit_record.apply_frequency_id === 30 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		}

		this.editFieldResize();
	},

	onCalculationChange: function() {

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_tax_deductions': $.i18n._( 'Tax / Deductions' ),
			'tab_eligibility': $.i18n._( 'Eligibility' ),
			'tab_employee_setting': $.i18n._( 'Employee Settings' ),
			'tab_attachment': $.i18n._( 'Attachments' ),
			'tab_audit': $.i18n._( 'Audit' ),
			'tab5': $.i18n._( 'Taxes / Deductions' )
		} );

		this.edit_view.children().eq( 0 ).css( 'min-width', 1170 );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: (APIFactory.getAPIClass( 'APICompanyDeduction' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.COMPANY_DEDUCTION,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_tax_deductions = this.edit_view_tab.find( '#tab_tax_deductions' );

		var tab_tax_deductions_column1 = tab_tax_deductions.find( '.first-column' );
		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_tax_deductions_column1 );

		// Status
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_tax_deductions_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_tax_deductions_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_tax_deductions_column1 );

		form_item_input.parent().width( '45%' );

		//Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'pay_stub_entry_description', width: 300} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_tax_deductions_column1 );

		//Calculation
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'calculation_id', set_empty: false, width: 400} );
		form_item_input.setSourceData( $this.calculation_array );
		this.addEditFieldToColumn( $.i18n._( 'Calculation' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'country', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.country_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Province
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'province'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'Province' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// District
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'district', set_empty: true} );
		form_item_input.setSourceData( Global.addFirstItemToArray( [] ) );
		this.addEditFieldToColumn( $.i18n._( 'District' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Dynamic Field 0

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_0'} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> (" + $.i18n._( '%' ) + ")</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( 'df_0', form_item_input, tab_tax_deductions_column1, '', widgetContainer, true );

		//  Dynamic Field 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_1'} );
		this.addEditFieldToColumn( 'df_1', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_2'} );
		this.addEditFieldToColumn( 'df_2', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_3'} );
		this.addEditFieldToColumn( 'df_3', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_4'} );
		this.addEditFieldToColumn( 'df_4', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_5'} );
		this.addEditFieldToColumn( 'df_5', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_6'} );
		this.addEditFieldToColumn( 'df_6', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 7
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_7'} );
		this.addEditFieldToColumn( 'df_7', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 8
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_8'} );
		this.addEditFieldToColumn( 'df_8', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 9
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_9'} );
		this.addEditFieldToColumn( 'df_9', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 10
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_10'} );
		this.addEditFieldToColumn( 'df_10', form_item_input, tab_tax_deductions_column1, '', null, true );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) ) {
			Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js' );

			// Dynamic Field 11
			form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
			form_item_input.FormulaBuilder( {
				field: 'df_11', onFormulaBtnClick: function() {

					var custom_column_api = new (APIFactory.getAPIClass( 'APIReportCustomColumn' ))();

					custom_column_api.getOptions( 'formula_functions', {
						onResult: function( fun_result ) {
							var fun_res_data = fun_result.getResult();

							$this.api.getOptions( 'formula_variables', {onResult: onColumnsResult} );

							function onColumnsResult( col_result ) {
								var col_res_data = col_result.getResult();

								var default_args = {};
								default_args.functions = Global.buildRecordArray( fun_res_data );
								default_args.variables = Global.buildRecordArray( col_res_data );
								default_args.formula = $this.current_edit_record.company_value1;
								default_args.current_edit_record = Global.clone( $this.current_edit_record );
								default_args.api = $this.api;

								IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
									$this.current_edit_record.company_value1 = val;
									$this.edit_view_ui_dic.df_11.setValue( val );
								} );
							}

						}
					} );
				}
			} );
			this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );

		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
			form_item_input.TTextInput( {field: 'df_11'} );
			this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );

			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'df_100'} );
			this.addEditFieldToColumn( 'Warning', form_item_input, tab_tax_deductions_column1, '', null, true );
		}

		//Dynamic Field 12,13
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'df_12', width: 30} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( ' ' ) + " </span>" );

		var widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( {field: 'df_13'} );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.look_back_unit_array ) );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( 'df_12', [form_item_input, widget_combo_box], tab_tax_deductions_column1, '', widgetContainer, true );

		//Dynamic Field 14
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'df_14', set_empty: true} );
		this.addEditFieldToColumn( 'df_14', form_item_input, tab_tax_deductions_column1, '', null, true );

		//Pay Stub Account

		var default_args = {};
		default_args.filter_data = {};
		default_args.filter_data.type_id = [10, 20, 30, 50, 80];

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_account_id'

		} );

		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_tax_deductions_column1 );

		// Calculation Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'calculation_order', width: 30} );
		this.addEditFieldToColumn( $.i18n._( 'Calculation Order' ), form_item_input, tab_tax_deductions_column1 );

		// Include Pay Stub Accounts
		var v_box = $( "<div class='v-box'></div>" );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'include_account_amount_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.account_amount_type_array ) );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Include Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, false, true );

		// Exclude Pay Stub Accounts
		v_box = $( "<div class='v-box'></div>" );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'exclude_account_amount_type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.account_amount_type_array ) );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Exclude Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, false, true );

		// employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_tax_deductions_column1, '' );

		// Tab1  start

		var tab_eligibility = this.edit_view_tab.find( '#tab_eligibility' );

		var tab_eligibility_column1 = tab_eligibility.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_eligibility_column1 );

		// Apply Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.apply_frequency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Apply Frequency' ), form_item_input, tab_eligibility_column1, '' );

		// Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_month', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_year_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_day_of_month', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Month of Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'apply_frequency_quarter_month', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_quarter_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month of Quarter' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '25/02/2001' ) + ' ' + $.i18n._( '(Leave blank for no start date)' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'ie' ) + ': ' + $.i18n._( '25/02/2001' ) + ' ' + $.i18n._( '(Leave blank for no end date)' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Minimum Length Of Service

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'minimum_length_of_service', width: 30} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( ' ' ) + " </span>" );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( {field: 'minimum_length_of_service_unit_id'} );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.length_of_service_unit_array ) );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );

		// Maximum Length Of Service

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_length_of_service', width: 30} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( ' ' ) + " </span>" );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( {field: 'maximum_length_of_service_unit_id'} );
		widget_combo_box.setSourceData( Global.addFirstItemToArray( $this.length_of_service_unit_array ) );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );

		//Length of Service contributing pay codes.
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIContributingPayCodePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CONTRIBUTING_PAY_CODE_POLICY,
			show_search_inputs: true,
			set_empty: true,
			set_default: true,
			field: 'length_of_service_contributing_pay_code_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Length Of Service Hours Based On' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Minimum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'minimum_user_age', width: 30} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'years' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Maximum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'maximum_user_age', width: 30} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'>" + $.i18n._( 'years' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		//Tab 5

		var tab5 = this.edit_view_tab.find( '#tab5' );

		var tab5_column1 = tab5.find( '.first-column' );

		this.edit_view_tabs[5] = [];

		this.edit_view_tabs[5].push( tab5_column1 );

		//Permissions

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

		var display_columns = ALayoutCache.getDefaultColumn( ALayoutIDs.COMPANY_DEDUCTION ); //Get Default columns base on different layout name
		display_columns = Global.convertColumnsTojGridFormat( display_columns, ALayoutIDs.COMPANY_DEDUCTION ); //Convert to jQgrid format

		form_item_input.ADropDown( {
			field: 'company_tax_deduction_ids',
			display_show_all: false,
			id: 'company_tax_deduction_ids',
			key: 'id',
			display_close_btn: false,
			allow_drag_to_order: false
		} );

		this.addEditFieldToColumn( $.i18n._( 'Taxes / Deductions' ), form_item_input, tab5_column1, '', null, false, true );

		form_item_input.setColumns( display_columns );
//		form_item_input.setUnselectedGridData( [] );

	},

	setEditViewTabHeight: function() {
		this._super( 'setEditViewTabHeight' );

		var tax_grid = this.edit_view_ui_dic.company_tax_deduction_ids;

		tax_grid.setHeight( (this.edit_view_tab.height() - 140) );

	},

	putInputToInsideFormItem: function( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );

		form_item_label_div.attr( 'class', 'edit-view-form-item-sub-label-div' );

		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) + ': ' );
		form_item_input_div.append( form_item_input );

		return form_item;
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_document_view_controller = null;

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Calculation' ),
				in_column: 2,
				field: 'calculation_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];

	}

} );

CompanyTaxDeductionViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'CompanyTaxDeduction', 'SubCompanyTaxDeductionView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_company_tax_deduction_view_controller );
			}

		}

	} );

};
