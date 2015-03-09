HolidayViewController = BaseViewController.extend( {
	el: '#holiday_view_container',

	initialize: function() {
		if ( Global.isSet( this.options.sub_view_mode ) ) {

			this.sub_view_mode = this.options.sub_view_mode;
		}

		this._super( 'initialize' );
		this.edit_view_tpl = 'HolidayEditView.html';
		this.permission_id = 'holiday_policy';
		this.viewId = 'Holiday';
		this.script_name = 'HolidayView';
		this.table_name_key = 'holidays';
		this.context_menu_name = $.i18n._( 'Holiday' );
		this.navigation_label = $.i18n._( 'Holiday' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIHoliday' ))();

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

		//this.setSelectRibbonMenuIfNecessary( 'UserContact' )

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_holiday': $.i18n._( 'Holiday' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIHoliday' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.HOLIDAY,
			navigation_mode: true,
			show_search_inputs: true} );

		this.setNavigation();

//		  this.edit_view_tab.css( 'width', '700' );

		//Tab 0 start

		var tab_holiday = this.edit_view_tab.find( '#tab_holiday' );

		var tab_holiday_column1 = tab_holiday.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_holiday_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_holiday_column1, '' );

		form_item_input.parent().width( '45%' );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'date_stamp'} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_holiday_column1, '' );

	}



} )

HolidayViewController.loadView = function() {

	Global.loadViewSource( 'Holiday', 'HolidayView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );

	} )

}

HolidayViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'Holiday', 'SubHolidayView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_holiday_view_controller );
			}
		}
	} )
}