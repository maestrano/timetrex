UserEducationViewController = BaseViewController.extend( {
	el: '#user_education_view_container',
	qualification_group_array: null,
	qualification_group_api: null,
	qualification_api: null,

	document_object_type_id: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'UserEducationEditView.html';
		this.permission_id = 'user_education';
		this.viewId = 'UserEducation';
		this.script_name = 'UserEducationVoew';
		this.table_name_key = 'user_education';
		this.context_menu_name = $.i18n._( 'Education' );
		this.navigation_label = $.i18n._( 'Education' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserEducation' ))();
		this.qualification_api = new (APIFactory.getAPIClass( 'APIQualification' ))();
		this.qualification_group_api = new (APIFactory.getAPIClass( 'APIQualificationGroup' ))();

		this.document_object_type_id = 126;
		this.render();

		if ( !this.sub_view_mode ) {
			this.buildContextMenu();
			this.initData();
			this.setSelectRibbonMenuIfNecessary( 'UserEducation' );
		}


	},

	initOptions: function() {
		var $this = this;

		this.qualification_group_api.getQualificationGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();

			res = Global.buildTreeRecord( res );
			$this.qualification_group_array = res;

			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}} );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [20];
		args.filter_data = filter_data;
		this.qualification_api.getQualification( args, {onResult: function( res ) {
			res = res.getResult();
			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['qualification_id'] ) {
				$this.basic_search_field_ui_dic['qualification_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['qualification_id'].setSourceData( res );
			}

		}} );
	},

	setGridSize: function() {
		if ( (!this.grid || !this.grid.is( ':visible' )) ) {

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
			this.grid.setGridHeight( ($( this.el ).height() - this.search_panel.height() - 90) );

		}

	},

	showNoResultCover: function( show_new_btn ) {

		show_new_btn = this.ifContextButtonExist( ContextMenuIconName.add );

		if ( this.sub_view_mode ) {
			show_new_btn = true;
			this.grid.setGridHeight( 150 );
		}

		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: show_new_btn} );
		this.no_result_box.attr( 'id', this.ui_id + '_no_result_box' );

		var grid_div = $( this.el ).find( '.grid-div' );

		grid_div.append( this.no_result_box );

		this.initRightClickMenu( RightClickMenuType.NORESULTBOX );
	},

	onGridSelectRow: function() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		} else {
			this.buildContextMenu();
		}
		this.setDefaultMenu();
	},

	onGridSelectAll: function() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		}
		this.setDefaultMenu();
	},

	cancelOtherSubViewSelectedStatus: function() {
		switch( true ) {
			case typeof( this.parent_view_controller.sub_user_skill_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_skill_view_controller.unSelectAll();
			case typeof( this.parent_view_controller.sub_user_license_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_license_view_controller.unSelectAll();
			case typeof( this.parent_view_controller.sub_user_membership_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_membership_view_controller.unSelectAll();
			case typeof( this.parent_view_controller.sub_user_language_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_language_view_controller.unSelectAll();
				break;
		}
	},

	onAddClick: function() {

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		}

		this._super( 'onAddClick' );
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

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			$this.unique_columns = {};

			$this.linked_columns = {};

			if ( !result_data ) {
				result_data = [];
			}

			if ( $this.sub_view_mode && $this.parent_key ) {
				result_data[$this.parent_key] = $this.parent_value;
			}

			$this.current_edit_record = result_data;
			$this.initEditView();

		}} );

	},

	resizeSubGridHeight: function( length ) {
		var height = ( length * 26 >= 200 ) ? 200 : length * 26;
		this.grid.setGridHeight( height );
	},

	setTabStatus: function() {
		//Handle most cases that one tab and on audit tab
		if ( this.is_mass_editing ) {

			$( this.edit_view_tab.find( 'ul li a[ref="tab_attachment"]' ) ).parent().hide();
			$( this.edit_view_tab.find( 'ul li a[ref="tab_audit"]' ) ).parent().hide();
			this.edit_view_tab.tabs( 'select', 0 );

		} else {
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

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_education': $.i18n._( 'Education' ),
			'tab_attachment': $.i18n._( 'Attachments' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserEducation' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER_Education,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_education = this.edit_view_tab.find( '#tab_education' );

		var tab_education_column1 = tab_education.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_education_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			field: 'user_id',
			set_empty: true,
			show_search_inputs: true
		} );
		var default_args = {};
		default_args.permission_section = 'user_education';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_education_column1, '' );

		// Course
		var args = {};
		var filter_data = {};
		filter_data.type_id = [20];
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIQualification' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.QUALIFICATION,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualification_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Course' ), form_item_input, tab_education_column1 );

		//Institute
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'institute', width: 300} );
		this.addEditFieldToColumn( $.i18n._( 'Institute' ), form_item_input, tab_education_column1 );

		//Major/Specialization
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'major', width: 300} );
		this.addEditFieldToColumn( $.i18n._( 'Major/Specialization' ), form_item_input, tab_education_column1 );

		//Minor
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'minor', width: 300} );
		this.addEditFieldToColumn( $.i18n._( 'Minor' ), form_item_input, tab_education_column1 );

		//
		// Grade/Score
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'grade_score', width: 50} );
		this.addEditFieldToColumn( $.i18n._( 'Grade/Score' ), form_item_input, tab_education_column1 );

		// Graduation Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'graduate_date'} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Graduation Date' ), form_item_input, tab_education_column1, '', widgetContainer );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_education_column1, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_education_column1, '', widgetContainer );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( {field: 'tag', object_type_id: 252} );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_education_column1, '', null, null, true );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'user_education';

		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Course' ),
				in_column: 1,
				field: 'qualification_id',
				layout_name: ALayoutIDs.QUALIFICATION,
				api_class: (APIFactory.getAPIClass( 'APIQualification' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Institute' ),
				in_column: 1,
				field: 'institute',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				object_type_id: 252,
				form_item_type: FormItemType.TAG_INPUT} ),

			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Major/Specialization' ),
				in_column: 2,
				field: 'major',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Minor' ),
				in_column: 2,
				field: 'minor',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Graduation Date' ),
				in_column: 3,
				field: 'graduate_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'Start Date' ),
				in_column: 3,
				field: 'start_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'End Date' ),
				in_column: 3,
				field: 'end_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} )
		];
	},

	onTabShow: function( e, ui ) {

		var key = this.edit_view_tab_selected_index;
		this.editFieldResize( key );
		if ( !this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_tab_selected_index === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
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

	initTabData: function() {

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubDocumentView();
			} else {
				this.edit_view_tab.find( '#tab_attachment' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		} else if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 2 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_document_view_controller = null;

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
			var tab_attachment = $this.edit_view_tab.find( '#tab_attachment' );
			var firstColumn = tab_attachment.find( '.first-column-sub-view' );
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

UserEducationViewController.loadView = function() {

	Global.loadViewSource( 'UserEducation', 'UserEducationView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};


UserEducationViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserEducation', 'SubUserEducationView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_education_view_controller );
			}
		}
	} );
};