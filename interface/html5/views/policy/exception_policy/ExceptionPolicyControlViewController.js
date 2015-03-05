ExceptionPolicyControlViewController = BaseViewController.extend( {
	el: '#exception_policy_control_view_container',
	severity_array: null,
	email_notification_array: null,
	editor: null,
	api_exception_policy: null,
	date_api: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'ExceptionPolicyControlEditView.html';
		this.permission_id = 'exception_policy';
		this.viewId = 'ExceptionPolicyControl';
		this.script_name = 'ExceptionPolicyControlView';
		this.table_name_key = 'exception_policy_control';
		this.context_menu_name = $.i18n._( 'Exception Policy' );
		this.navigation_label = $.i18n._( 'Exception Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIExceptionPolicyControl' ))();
		this.api_exception_policy = new (APIFactory.getAPIClass( 'APIExceptionPolicy' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'ExceptionPolicyControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'severity', 'severity_id', this.api_exception_policy );
		this.initDropDownOption( 'email_notification', 'email_notification_id', this.api_exception_policy );

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;


		this.setTabLabels( {
			'tab_exception_policy': $.i18n._( 'Exception Policy' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIExceptionPolicyControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.HIERARCHY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_exception_policy = this.edit_view_tab.find( '#tab_exception_policy' );

		var tab_exception_policy_column1 = tab_exception_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_exception_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_exception_policy_column1, 'first_last' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_exception_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Inside editor

		var inside_editor_div = tab_exception_policy.find( '.inside-editor-div' );
		var args = { active: $.i18n._( 'Active' ),
			code: $.i18n._( 'Code' ),
			name: $.i18n._( 'Name' ),
			severity: $.i18n._( 'Severity' ),
			grace: $.i18n._( 'Grace' ),
			'watch_window': $.i18n._( 'Watch Window' ),
			'email_notification': $.i18n._( 'Email Notification' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {title: '',
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			updateAllRows: this.insideEditorUpdateAllRows,
			parent_controller: this,
			render: 'views/policy/exception_policy/ExceptionPolicyControlInsideEditorRender.html',
			render_args: args,
			row_render: 'views/policy/exception_policy/ExceptionPolicyControlInsideEditorRow.html'
		} );

		inside_editor_div.append( this.editor );

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
	},

	onCopyAsNewClick: function() {
		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				this.editor.rows_widgets_array[i].current_edit_item.id = '';
			}
			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var selectedId;
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

			this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {

				$this.onCopyAsNewResult( result );

			}} );
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

		var exception_control_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
		this.copied_record_id = '';

		if ( !exception_control_id ) {

			this.api_exception_policy.getExceptionPolicyDefaultData( args, true, {onResult: function( res ) {

				if ( !$this.edit_view ) {
					return;
				}

				var data = res.getResult();
				var array_data = [];
				for ( var key in data ) {

					if ( !data.hasOwnProperty( key ) ) {
						continue;
					}

					data[key].id = '';
					array_data.push( data[ key ] );
				}

				array_data.sort( function( a, b ) {
						if ( a.type_id > b.type_id ) {
							return true;
						} else {
							return false;
						}
					}
				);

				$this.editor.setValue( array_data );

			}} );

		} else {

			args.filter_data.exception_policy_control_id = exception_control_id;

			this.api_exception_policy.getExceptionPolicyDefaultData( args, true, {onResult: function( res ) {

				if ( !$this.edit_view ) {
					return;
				}

				var data = res.getResult();
				var array_data = [];

				for ( var key in data ) {

					if ( !data.hasOwnProperty( key ) ) {
						continue;
					}

					data[key].id = '';
					array_data.push( data[ key ] );
				}

				array_data.sort( function( a, b ) {
						if ( a.type_id > b.type_id ) {
							return true;
						} else {
							return false;
						}
					}
				);

				$this.editor.setValue( array_data );

				var ep_filter = {};
				ep_filter.filter_data = {exception_policy_control_id: exception_control_id};

				$this.api_exception_policy.getExceptionPolicy( ep_filter, true, {onResult: function( ep_res ) {

					if ( !$this.edit_view ) {
						return;
					}

					var data = ep_res.getResult();
					var array_data = [];
					for ( var key in data ) {

						if ( !data.hasOwnProperty( key ) ) {
							continue;
						}

						array_data.push( data[ key ] );
					}

					array_data.sort( function( a, b ) {
							if ( a.type_id > b.type_id ) {
								return true;
							} else {
								return false;
							}
						}
					);

					$this.editor.setValue( array_data );

				} } );

			}} );
		}

	},

	insideEditorUpdateAllRows: function( val ) {
		var len = this.rows_widgets_array.length;
		for ( var i = 0; i < len; i++ ) {
			var c_row = this.rows_widgets_array[i];
			var c_row_data = c_row.current_edit_item;

			var len1 = val.length;

			for ( var j = 0; j < len1; j++ ) {
				var new_row = val[j];

				if ( new_row.type_id === c_row_data.type_id ) {
					c_row.current_edit_item = new_row;

					if ( !this.parent_controller.current_edit_record.id ) {
						c_row.current_edit_item.id = '';
					}

					c_row.active.setValue( new_row.active );
					c_row.severity_id.setValue( new_row.severity_id );

					if ( new_row.is_enabled_grace ) {
						c_row.grace.setValue( new_row.grace );
					}

					if ( new_row.is_enabled_watch_window ) {
						c_row.watch_window.setValue( new_row.watch_window );
					}

					c_row.email_notification_id.setValue( new_row.email_notification_id );

					val.splice( j, 1 );

					break;

				}
			}

		}

	},

	insideEditorSetValue: function( val ) {
		var len = val.length;

		if ( len === 0 ) {
			return;
		}

		if ( !val[0].id ) {
			this.removeAllRows();
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.updateAllRows( val );
		}

	},

	insideEditorAddRow: function( data, index ) {
		if ( !data ) {
			data = {};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		//Active
		var form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'active'} );
		form_item_input.setValue( data.active );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );
		form_item_input.attr( 'exception_policy_id', (data.id && this.parent_controller.current_edit_record.id) ? data.id : '' );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'type_id'} );
		form_item_input.setValue( data.type_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( form_item_input );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'name'} );
		form_item_input.setValue( data.name );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( form_item_input );

		//Severity
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'severity_id', set_empty: false } );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		form_item_input.setSourceData( Global.addFirstItemToArray( this.parent_controller.severity_array ) );
		form_item_input.setValue( data.severity_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( form_item_input );

		if ( data.is_enabled_grace ) {
			//Grace
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: 'grace', width: 90, need_parser_sec: true} );
			form_item_input.setValue( data.grace );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 4 ).append( form_item_input );

			this.setWidgetEnableBaseOnParentController( form_item_input );

		}

		if ( data.is_enabled_watch_window ) {
			//Watch Window
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: 'watch_window', width: 90, need_parser_sec: true} );
			form_item_input.setValue( data.watch_window );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 5 ).append( form_item_input );

			this.setWidgetEnableBaseOnParentController( form_item_input );
		}

		//Email Notification
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'email_notification_id', set_empty: false } );
		form_item_input.setSourceData( this.parent_controller.email_notification_array );
		form_item_input.setValue( data.email_notification_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 6 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Save current set item
		widgets.current_edit_item = data;

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

		this.removeLastRowLine();

	},

	insideEditorGetValue: function( current_edit_item_id ) {

		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = row.current_edit_item;
			data.exception_policy_control_id = current_edit_item_id;
			data.active = row.active.getValue();
			data.severity_id = row.severity_id.getValue();
			if ( data.is_enabled_grace ) {
				data.grace = row.grace.getValue();
			}

			if ( data.is_enabled_watch_window ) {
				data.watch_window = row.watch_window.getValue();
			}

			data.email_notification_id = row.email_notification_id.getValue();

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

			$this.saveInsideEditorData( function() {
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

	saveInsideEditorData: function( callBack ) {

		var data = this.editor.getValue( this.refresh_id );

		this.api_exception_policy.setExceptionPolicy( data, {onResult: function( res ) {

			if ( Global.isSet( callBack ) ) {
				callBack();
			}

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
	}


} );

ExceptionPolicyControlViewController.loadView = function() {

	Global.loadViewSource( 'ExceptionPolicyControl', 'ExceptionPolicyControlView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};