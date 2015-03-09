UserTitleViewController = BaseViewController.extend( {
	el: '#user_title_view_container',
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'UserTitleEditView.html';
		this.permission_id = 'user';
		this.viewId = 'UserTitle';
		this.script_name = 'UserTitleView';
		this.table_name_key = 'user_title';
		this.context_menu_name = $.i18n._( 'Job Titles' );
		this.navigation_label = $.i18n._( 'Job Title' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserTitle' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true; //Hide some context menus

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'UserTitle' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_job_title': $.i18n._( 'Job Title' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER_TITLE,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_job_title = this.edit_view_tab.find( '#tab_job_title' );

		var tab_job_title_column1 = tab_job_title.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_job_title_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_job_title_column1, 'first_last' );

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
				form_item_type: FormItemType.AWESOME_BOX} )];
	}


} );

UserTitleViewController.loadView = function() {

	Global.loadViewSource( 'UserTitle', 'UserTitleView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} )

};