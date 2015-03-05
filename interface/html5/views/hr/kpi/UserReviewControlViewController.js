UserReviewControlViewController = BaseViewController.extend( {
	el: '#user_review_control_view_container',
	type_array: null,
	status_array: null,
	term_array: null,
	severity_array: null,

	kpi_group_array: null,

	document_object_type_id: null,

	kpi_group_api: null,

	user_review_api: null,

	kpi_api: null,

	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'UserReviewControlEditView.html';
		this.permission_id = 'user_review';
		this.viewId = 'UserReviewControl';
		this.script_name = 'UserReviewControlView';
		this.table_name_key = 'user_review_control';
		this.context_menu_name = $.i18n._( 'Reviews' );
		this.navigation_label = $.i18n._( 'Review' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
		this.kpi_group_api = new (APIFactory.getAPIClass( 'APIKPIGroup' ))();
		this.user_review_api = new (APIFactory.getAPIClass( 'APIUserReview' ))();
		this.kpi_api = new (APIFactory.getAPIClass( 'APIKPI' ))();
		this.document_object_type_id = 220;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true; //Hide some context menus
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary( 'UserReviewControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'term' );
		this.initDropDownOption( 'severity' );

		this.kpi_group_api.getKPIGroup( false, false, false, {onResult: function( res ) {
			res = res.getResult();

			//Error: Uncaught TypeError: Cannot set property 'name' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=41499&tab=Reviews line 60 
			if ( !res || !res[0] ) {
				$this.kpi_group_array = [];
				return;
			}

			res[0].name = '-- ' + $.i18n._( 'Add KPIs' ) + ' --';

			var all = {};
			all.name = '-- ' + $.i18n._( 'All' ) + ' --';
			all.level = 1;
			all.id = -1;

			if ( res.length === 1 && res[0].hasOwnProperty( 'children' ) ) {
				res[0].children.unshift( all );
			} else {
				res = [
					{children: [all], id: 0, level: 0, name: '-- ' + $.i18n._( 'Add KPIs' ) + ' --'}
				];
			}

			res = Global.buildTreeRecord( res );

			$this.kpi_group_array = res;

		}} );

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
			'tab_review': $.i18n._( 'Review' ),
			'tab_attachment': $.i18n._( 'Attachments' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserReviewControl' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.KPI_REVIEW_CONTROL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_review = this.edit_view_tab.find( '#tab_review' );

		var tab_review_column1 = tab_review.find( '.first-column' );
		var tab_review_column2 = tab_review.find( '.second-column' );
		var tab_review_column4 = tab_review.find( '.forth-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_review_column1 );
		this.edit_view_tabs[0].push( tab_review_column2 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'user_review_control';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_review_column1, '' );

		// Reviewer
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'reviewer_user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Reviewer' ), form_item_input, tab_review_column1 );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_review_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_review_column1 );

		// Terms
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'term_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.term_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Terms' ), form_item_input, tab_review_column1 );

		// Rating
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'rating', width: 50} );
		this.addEditFieldToColumn( $.i18n._( 'Rating' ), form_item_input, tab_review_column1, '' );

		// Severity
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'severity_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.severity_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Severity' ), form_item_input, tab_review_column2, '' );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_review_column2, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_review_column2, '', widgetContainer );

		// Due Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'due_date'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		label = $( "<span class='widget-right-label'> " + $.i18n._( 'ie' ) + ' : ' + LocalCacheData.getLoginUserPreference().date_format_example + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Due Date' ), form_item_input, tab_review_column2, '', widgetContainer );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( {field: 'tag', object_type_id: 320} );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_review_column2, '', null, null, true );

		// Add KPIs from Groups

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.kpi_group_array ) );

		var tab_review_column3 = tab_review.find( '.third-column' ).css( {'float': 'left', 'margin-top': '10px', 'margin-bottom': '10px'} );
		tab_review_column3.find( '.column-form-item-label' ).css( {'float': 'left', 'margin-right': '10px', 'margin-top': '5px'} ).text( $.i18n._( 'Add KPIs from Groups' ) );
		tab_review_column3.find( '.column-form-item-input' ).css( {'float': 'left'} ).append( form_item_input );

		this.edit_view_ui_dic[form_item_input.getField()] = form_item_input;

		form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
			$this.onFormItemChange( target, doNotValidate );
		} );

		// Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( {field: 'note', width: '100%', height: 70 } );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_review_column4, 'first_last', null, null, true );

	},

	initInsideEditorUI: function() {
		//Inside editor
		var tab_review = this.edit_view_tab.find( '#tab_review' );

		var inside_editor_div = tab_review.find( '.inside-editor-div' );

		var args = {
			serial: '#',
			name: $.i18n._( 'Key Performance Indicator' ),
			rating: $.i18n._( 'Rating' ),
			note: $.i18n._( 'Note' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			onFormItemChange: this.onInsideFormItemChange,
			parent_controller: this,
			api: this.user_review_api,
			render: 'views/hr/kpi/UserReviewViewInsideEditorRender.html',
			render_args: args,
			row_render: 'views/hr/kpi/UserReviewViewInsideEditorRow.html'

		} );

		inside_editor_div.append( this.editor );
	},

	/* jshint ignore:start */

	addEditFieldToColumn: function( label, widgets, column, firstOrLastRecord, widgetContainer, saveFormItemDiv, setResizeEvent, saveFormItemDivKey, hasKeyEvent, customLabelWidget ) {

		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );

//		if ( firstOrLastRecord === 'first' ) {
//			form_item_label_div.addClass( 'edit-view-form-item-label-div-first-row' );
//		} else if ( firstOrLastRecord === 'last' ) {
//			form_item_label_div.addClass( 'edit-view-form-item-label-div-last-row' );
//			form_item.addClass( 'edit-view-form-item-div-last-row' );
//		} else if ( firstOrLastRecord === 'first_last' ) {
//			form_item_label_div.addClass( 'edit-view-form-item-label-div-first-row' );
//			form_item_label_div.addClass( 'edit-view-form-item-label-div-last-row' );
//			form_item.addClass( 'edit-view-form-item-div-last-row' );
//		}

		if ( customLabelWidget ) {
			form_item_label.parent().append( customLabelWidget );
			form_item_label.remove();
		} else {
			form_item_label.text( $.i18n._( label ) + ': ' );
		}

		var widget = widgets;

		if ( Global.isArray( widgets ) ) {

			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				widget.css( 'opacity', 0 );

				this.edit_view_ui_dic[widget.getField()] = widget;

				widget.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );

				if ( hasKeyEvent ) {
					widget.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
						$this.onFormItemKeyUp( target );
					} );

					widget.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
						$this.onFormItemKeyDown( target );
					} );
				}
			}
		} else {

			widget.css( 'opacity', 0 );

			this.edit_view_ui_dic[widget.getField()] = widget;

			widget.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );

			if ( hasKeyEvent ) {
				widget.bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				widget.bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
			}

		}

		if ( Global.isSet( widgetContainer ) ) {
			form_item_input_div.append( widgetContainer );

		} else {
			form_item_input_div.append( widget );
		}

		if ( setResizeEvent ) {

			if ( widget.getField() === 'note' ) {

				form_item_input_div.css( 'width', '80%' );
				form_item_label_div.css( 'height', '80' );
				widget.css( {'width': '100%', 'resize': 'none'} );

			} else {

				form_item.bind( 'resize', function() {

					if ( form_item_label_div.height() !== form_item.height() && form_item.height() !== 0 ) {
						form_item_label_div.css( 'height', form_item.height() );
						form_item.unbind( 'resize' );
					}

				} );

				widget.bind( 'setSize', function() {
					form_item_label_div.css( 'height', widget.height() + 5 );
				} );
			}

		}

		if ( saveFormItemDiv ) {

			if ( Global.isArray( widgets ) ) {
				this.edit_view_form_item_dic[widgets[0].getField()] = form_item;
			} else {
				this.edit_view_form_item_dic[widget.getField()] = form_item;
			}

		}

		column.append( form_item );
		column.append( "<div class='clear-both-div'></div>" );

	},

	/* jshint ignore:end */

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'user_review_control';
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

			new SearchField( {label: $.i18n._( 'Reviewer' ),
				in_column: 1,
				field: 'reviewer_user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				object_type_id: 320,
				form_item_type: FormItemType.TAG_INPUT} ),

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Terms' ),
				in_column: 2,
				field: 'term_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Severity' ),
				in_column: 2,
				field: 'severity_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'Due Date' ),
				in_column: 1,
				field: 'due_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER} ),

			new SearchField( {label: $.i18n._( 'KPI' ),
				in_column: 2,
				field: 'kpi_id',
				layout_name: ALayoutIDs.KPI,
				api_class: (APIFactory.getAPIClass( 'APIKPI' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

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
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_document_view_controller = null;
		this.editor = null;
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

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.initInsideEditorData();
	},

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'group_id':
				var filter = {};
				filter.filter_data = {};
				// why need [c_value, -1], -1 will return all, the filter won't work correct if send -1,remove for testting
				filter.filter_data.group_id = [c_value];
				this.kpi_api['get' + this.kpi_api.key_name]( filter, false, true, {onResult: function( res ) {
					$this.setInsideEditorData( res );
				}} );
				break;
			default:
				this.current_edit_record[key] = c_value;
				if ( !doNotValidate ) {
					this.validate();
				}
				break;
		}

	},

	onInsideFormItemChange: function( target, doNotValidate ) {
		target.clearErrorStyle();

		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'rating':
				var minimum_rate = parseInt( target.attr( 'minimum_rate' ) );
				var maximum_rate = parseInt( target.attr( 'maximum_rate' ) );
				if ( c_value !== '' ) {
					c_value = parseInt( c_value );
					if ( c_value >= minimum_rate && c_value <= maximum_rate ) {
						target.clearErrorStyle();
					} else {
						target.setErrorStyle( $.i18n._( 'Rating must between' ) + ' ' + minimum_rate + ' ' + $.i18n._( 'and' ) + ' ' + maximum_rate, true );
					}
				}
				break;
			default:
				break;
		}

	},

	initInsideEditorData: function() {

		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( this.current_edit_record.id ) {

			args.filter_data.user_review_control_id = this.current_edit_record['id'];

			$this.user_review_api['get' + $this.user_review_api.key_name]( args, true, {onResult: function( res ) {
				if ( !$this.edit_view ) {
					return;
				}
				$this.setInsideEditorData( res );
			}} );
		}

	},

	/* jshint ignore:start */

	setInsideEditorData: function( res ) {
		var data = res.getResult();
		var len = data.length;

		if ( len > 0 ) {

			if ( !this.editor ) {
				this.initInsideEditorUI();
			}

			var serial = 1;
			for ( var key in data ) {
				var row = data[key];
				var is_existed = false;
				if ( !row.kpi_id ) {
					row.kpi_id = row.id;
					row.id = false;
				}
				// the row.kpi_id if existed in this.editor.editor_data?
				if ( this.editor.editor_data ) {

					for ( var i = 0; i < this.editor.editor_data.length; i++ ) {
						var item = this.editor.editor_data[i];
						if ( row.kpi_id === item.kpi_id ) {
							is_existed = true; // the current row has already displayed.
							break;
						}
					}

					if ( !is_existed ) {
						serial = this.editor.editor_data.length + 1;
						row.serial = serial;
						this.editor.editor_data.push( row );
					}

//					serial++;

				} else {
					row.serial = serial;
					data[key] = row;
					serial++;
				}

				if ( !is_existed ) {
					this.editor.addRow( row );
				}

			}

			if ( !this.editor.editor_data ) {
				this.editor.editor_data = data;
			}

		}

//		$this.editor.setValue( data );
	},

	/* jshint ignore:end */

//	insideEditorSetValue: function( val ) {
//		var len = val.length;
//		this.removeAllRows();
//
//		if ( len > 0 ) {
//			var serial = 1;
//			for ( var i = 0; i < val.length; i++ ) {
//				if ( Global.isSet( val[i] ) ) {
//					var row = val[i];
//					row.serial = serial;
//					this.addRow( row );
//					serial++;
//				}
//			}
//		}
//
//	},

	insideEditorAddRow: function( data ) {
		var $this = this;
		if ( !data ) {
//			this.getDefaultData();
		} else {
			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = data; //Save each row's widgets

			//Build row widgets

			// #
			var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'serial', width: 50} );
			form_item_input.setValue( data.serial ? data.serial : null );
//			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );

			// Key Performance Indicator
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( {field: 'name', width: 600} );
			form_item_input.setValue( data.name ? data.name : null );
			form_item_input.attr( 'title', data.description ? data.description : '' );

			row.children().eq( 1 ).append( form_item_input );

			// Rating
			if ( parseInt( data.type_id ) === 10 ) {

				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( {field: 'rating', width: 40} );
				form_item_input.setValue( data.rating ? data.rating : null );
				form_item_input.attr( { 'minimum_rate': data.minimum_rate, 'maximum_rate': data.maximum_rate } );
				form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );
				widgets[form_item_input.getField()] = form_item_input;
				row.children().eq( 2 ).append( form_item_input );

				this.setWidgetEnableBaseOnParentController( form_item_input );

			} else if ( parseInt( data.type_id ) === 20 ) {

				form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
				form_item_input.TCheckbox( {field: 'rating'} );
				form_item_input.setValue( data.rating ? data.rating : null );
				widgets[form_item_input.getField()] = form_item_input;
				row.children().eq( 2 ).append( form_item_input );

				this.setWidgetEnableBaseOnParentController( form_item_input );
			}

			// Note
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
			form_item_input.TTextArea( {field: 'note', style: { width: '300px', height: '20px', 'min-height': '10px' }} );
			form_item_input.setValue( data.note ? data.note : null );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 3 ).css( 'text-align', 'right' ).append( form_item_input );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// end

			$( render ).append( row );

			if ( this.parent_controller.is_viewing ) {
				row.find( '.control-icon' ).hide();
			}

			this.rows_widgets_array.push( widgets );

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();
		}

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

			} else if ( result_data > 0 ) {
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

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;
		if ( $this.editor ) {
			$this.editor.remove();
			$this.editor = null;
		}

		$this._super( 'onEditClick', editId, noRefreshUI );
	},

	saveInsideEditorData: function( callBack ) {
		var $this = this;

		if ( !this.editor ) {
			callBack();
		} else {
			var data = this.editor.getValue( this.refresh_id );
			this.user_review_api.setUserReview( data, {onResult: function( res ) {
				callBack();
			}} );
		}

	},

	insideEditorGetValue: function( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;
		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			if ( row.rating ) {
				row.rating = row.rating.getValue();
			}
			row.note = row.note.getValue();

			row.user_review_control_id = current_edit_item_id;

			this.rows_widgets_array[i] = row;
		}

		return this.rows_widgets_array;
	},

	onCopyAsNewClick: function() {

		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			if ( this.editor ) {
				this.editor.remove();
				this.editor = null;
			}
			this.setEditMenu();
			this.setTabStatus();
			ProgressBar.closeOverlay();

		} else {
			this._super( 'onCopyAsNewClick' );
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

			$this.saveInsideEditorData( function() {
				$this.search( false );
				if ( $this.editor ) {
					$this.editor.remove();
					$this.editor = null;
				}
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
				if ( $this.editor ) {
					$this.editor.remove();
					$this.editor = null;
				}
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

	onRightArrowClick: function() {
		if ( this.editor ) {
			this.editor.remove();
			this.editor = null;
		}
		this._super( 'onRightArrowClick' );
	},

	onLeftArrowClick: function() {
		if ( this.editor ) {
			this.editor.remove();
			this.editor = null;
		}
		this._super( 'onLeftArrowClick' );
	}



} );

UserReviewControlViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserReviewControl', 'SubUserReviewControlView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_review_control_view_controller );
			}
		}
	} );
};