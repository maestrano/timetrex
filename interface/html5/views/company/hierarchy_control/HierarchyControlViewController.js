HierarchyControlViewController = BaseViewController.extend( {
	el: '#hierarchy_control_view_container',
	object_type_array: null,
	editor: null,

	hierarchy_level_api: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'HierarchyControlEditView.html';
		this.permission_id = 'hierarchy';
		this.viewId = 'HierarchyControl';
		this.script_name = 'HierarchyControlView';
		this.table_name_key = 'hierarchy_control';
		this.context_menu_name = $.i18n._( 'Hierarchy' );
		this.navigation_label = $.i18n._( 'Hierarchy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIHierarchyControl' ))();
		this.hierarchy_level_api = new (APIFactory.getAPIClass( 'APIHierarchyLevel' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'HierarchyControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'object_type', 'object_type' );

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_hierarchy': $.i18n._( 'Hierarchy' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIHierarchyControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.HIERARCHY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_hierarchy = this.edit_view_tab.find( '#tab_hierarchy' );

		var tab_hierarchy_column1 = tab_hierarchy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_hierarchy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_hierarchy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'description', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_hierarchy_column1 );

		form_item_input.parent().width( '45%' );

		// Objects

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.OPTION_COLUMN,
			show_search_inputs: false,
			set_empty: true,
			key: 'value',
			field: 'object_type'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.object_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Objects' ), form_item_input, tab_hierarchy_column1 );

		// Subordinates

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Subordinates' ), form_item_input, tab_hierarchy_column1, '' );

		//Inside editor

		var inside_editor_div = tab_hierarchy.find( '.inside-editor-div' );
		var args = { level: $.i18n._( 'Level' ),
			superiors: $.i18n._( 'Superiors' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {title: $.i18n._( 'NOTE: Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities.' ),
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			removeRow: this.insideEditorRemoveRow,
			parent_controller: this,
			render: 'views/company/hierarchy_control/HierarchyInsideEditorRender.html',
			render_args: args,
			api: this.hierarchy_level_api,
			row_render: 'views/company/hierarchy_control/HierarchyInsideEditorRow.html'
		} );

		inside_editor_div.append( this.editor );

	},

	insideEditorSetValue: function( val ) {

		var len = val.length;
		this.removeAllRows();

		if ( len > 0 ) {
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.getDefaultData();
		}

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.initInsideEditorData();

	},

	initInsideEditorData: function() {
		var $this = this;

		var args = {};
		args.filter_data = {};
		args.filter_data.hierarchy_control_id = this.current_edit_record.id ? this.current_edit_record.id : ( this.copied_record_id ? this.copied_record_id : '' );

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			this.editor.addRow();
		} else {
			this.hierarchy_level_api.getHierarchyLevel( args, true, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}
				var data = res.getResult();

				$this.editor.setValue( data );

			}} );
		}

	},

	insideEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].current_edit_item.id;
		if ( remove_id > 0 ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();
	},

	insideEditorAddRow: function( data, index ) {
		if ( !data ) {
			data = {};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		//Level
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'level', width: 50} );
		form_item_input.setValue( data.level ? data.level : 1 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Superiors
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			width: 132,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );
		widgets[form_item_input.getField()] = form_item_input;
		form_item_input.setValue( data.user_id ? data.user_id : '' );
		row.children().eq( 1 ).append( form_item_input );

		if ( typeof index != 'undefined' ) {

			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( (index), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		if ( this.parent_controller.is_viewing ) {
			row.find( '.control-icon' ).hide();
		}

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Save current set item
		widgets.current_edit_item = data;

		if ( !this.parent_controller.current_edit_record.id ) {
			widgets.current_edit_item.id = '';
		}

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();
	},

	insideEditorGetValue: function( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = {level: row.level.getValue(), user_id: row.user_id.getValue()};
			data.hierarchy_control_id = current_edit_item_id;
			data.id = row.current_edit_item.id ? row.current_edit_item.id : '';
			result.push( data );

		}

		return result;
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( result, function() {
				$this.search();
				$this.onSaveDone( result );
				$this.current_edit_record = null;
				$this.removeEditView();
			} );

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

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;

			}

			$this.saveInsideEditorData( result, function() {

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

			} else if ( result_data > 0 ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( result, function() {
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
			$this.copied_record_id = $this.refresh_id;

			$this.saveInsideEditorData( result, function() {
				$this.search( false );
				$this.onCopyAsNewClick();
			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	saveInsideEditorData: function( result, callBack ) {

		var $this = this;

		var data = this.editor.getValue( this.refresh_id );

		var remove_ids = this.editor.delete_ids;

		if ( remove_ids.length > 0 ) {
			this.hierarchy_level_api.deleteHierarchyLevel( remove_ids, {onResult: function( res ) {
				$this.editor.delete_ids = [];
			}} );
		}

		this.hierarchy_level_api.ReMapHierarchyLevels( data, {onResult: function( res ) {

			var res_data = res.getResult();
			$this.hierarchy_level_api.setHierarchyLevel( res_data, {onResult: function( re ) {

				callBack( result );
			}} );
		}} );

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
				in_column: 1,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Superior' ),
				in_column: 1,
				field: 'superior_user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Subordinate' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Object Type' ),
				in_column: 2,
				field: 'object_type',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			//
			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	onCopyAsNewResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		this.copied_record_id = result_data.id;
		result_data.id = '';

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	}


} );

HierarchyControlViewController.loadView = function() {

	Global.loadViewSource( 'HierarchyControl', 'HierarchyControlView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};