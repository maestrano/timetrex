RecurringHolidayViewController = BaseViewController.extend( {
	el: '#recurring_holiday_view_container',

	special_day_array: null,
	type_array: null,
	day_of_month_array: null,
	day_of_week_array: null,
	month_of_year_array: null,

	always_week_day_array: null,
	week_interval_array: null,
	pivot_day_direction_array: null,
	date_api: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'RecurringHolidayEditView.html';
		this.permission_id = 'holiday_policy';
		this.viewId = 'RecurringHoliday';
		this.script_name = 'RecurringHolidayView';
		this.table_name_key = 'recurring_holiday';
		this.context_menu_name = $.i18n._( 'Recurring Holiday' );
		this.navigation_label = $.i18n._( 'Recurring Holiday' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRecurringHoliday' ))();
		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RecurringHoliday' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'special_day', 'special_day' );
		this.initDropDownOption( 'week_interval', 'week_interval' );
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'pivot_day_direction' );
		this.initDropDownOption( 'always_week_day', 'always_week_day' );

		this.date_api.getDayOfMonthArray( {onResult: function( res ) {
			res = res.getResult();
			$this.day_of_month_array = res;
		}} );
		this.date_api.getMonthOfYearArray( {onResult: function( res ) {
			res = res.getResult();
			$this.month_of_year_array = res;
		}} );
		this.date_api.getDayOfWeekArray( {onResult: function( res ) {
			res = res.getResult();
			$this.day_of_week_array = res;
		}} );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_recurring_holiday': $.i18n._( 'Recurring Holiday' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIRecurringHoliday' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.RECURRING_HOLIDAY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();


		//Tab 0 start

		var tab_recurring_holiday = this.edit_view_tab.find( '#tab_recurring_holiday' );

		var tab_recurring_holiday_column1 = tab_recurring_holiday.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_holiday_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_holiday_column1, '' );

		form_item_input.parent().width( '45%' );

		// Special Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'special_day'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.special_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Special Day' ), form_item_input, tab_recurring_holiday_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Week Interval
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'week_interval'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.week_interval_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Week Interval' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Day of the week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'day_of_week'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $.extend( {}, $this.day_of_week_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of the week' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Pivot Day Direction
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'pivot_day_direction_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.pivot_day_direction_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pivot Day Direction' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Day of the Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'day_of_month'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of the Month' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'month_int'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_year_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Always On Week Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'always_week_day_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.always_week_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Always On Week Day' ), form_item_input, tab_recurring_holiday_column1, '' );

	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'special_day' ) {
			this.onSpecialDayChange();
		}
		if ( key === 'type_id' ) {
			this.onTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onSpecialDayChange();
		this.onTypeChange();
	},

	onSpecialDayChange: function() {

		this.edit_view_form_item_dic['type_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['week_interval'].css( 'display', 'none' );
		this.edit_view_form_item_dic['day_of_week'].css( 'display', 'none' );
		this.edit_view_form_item_dic['pivot_day_direction_id'].css( 'display', 'none' );
		this.edit_view_form_item_dic['day_of_month'].css( 'display', 'none' );
		this.edit_view_form_item_dic['month_int'].css( 'display', 'none' );

		if ( Global.isFalseOrNull( this.current_edit_record['special_day'] ) ) {
			this.current_edit_record['special_day'] = 0;
		}

		if ( parseInt( this.current_edit_record['special_day'] ) === 0 ) {

			this.edit_view_form_item_dic['type_id'].css( 'display', 'block' );
			this.edit_view_form_item_dic['week_interval'].css( 'display', 'none' );
			this.edit_view_form_item_dic['day_of_week'].css( 'display', 'none' );
			this.edit_view_form_item_dic['pivot_day_direction_id'].css( 'display', 'none' );
			this.edit_view_form_item_dic['day_of_month'].css( 'display', 'block' );
			this.edit_view_form_item_dic['month_int'].css( 'display', 'block' );


		}

		this.editFieldResize();

	},

	onTypeChange: function() {

		if ( parseInt( this.current_edit_record['special_day'] ) === 0 ) {

			if ( this.current_edit_record['type_id'] === 10 ) {
				this.edit_view_form_item_dic['week_interval'].css( 'display', 'none' );
				this.edit_view_form_item_dic['day_of_week'].css( 'display', 'none' );
				this.edit_view_form_item_dic['pivot_day_direction_id'].css( 'display', 'none' );
				this.edit_view_form_item_dic['day_of_month'].css( 'display', 'block' );
				this.edit_view_form_item_dic['month_int'].css( 'display', 'block' );

			} else if ( this.current_edit_record['type_id'] === 20 ) {
				this.edit_view_form_item_dic['week_interval'].css( 'display', 'block' );
				this.edit_view_form_item_dic['day_of_week'].css( 'display', 'block' );
				this.edit_view_form_item_dic['pivot_day_direction_id'].css( 'display', 'none' );
				this.edit_view_form_item_dic['day_of_month'].css( 'display', 'none' );
				this.edit_view_form_item_dic['month_int'].css( 'display', 'block' );
			} else if ( this.current_edit_record['type_id'] === 30 ) {
				this.edit_view_form_item_dic['week_interval'].css( 'display', 'none' );
				this.edit_view_form_item_dic['day_of_week'].css( 'display', 'block' );
				this.edit_view_form_item_dic['pivot_day_direction_id'].css( 'display', 'block' );
				this.edit_view_form_item_dic['day_of_month'].css( 'display', 'block' );
				this.edit_view_form_item_dic['month_int'].css( 'display', 'block' );
			}
		}


		this.editFieldResize();
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

RecurringHolidayViewController.loadView = function() {

	Global.loadViewSource( 'RecurringHoliday', 'RecurringHolidayView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};