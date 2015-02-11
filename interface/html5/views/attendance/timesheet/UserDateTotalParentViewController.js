UserDateTotalParentViewController = BaseViewController.extend( {
	el: '#user_date_total_parent_view_container',
	sub_user_date_total_view_controller: null,

	initialize: function() {

		if ( Global.isSet( this.options.sub_view_mode ) ) {

			this.sub_view_mode = this.options.sub_view_mode;
		}

		this._super( 'initialize' );
		this.edit_view_tpl = 'UserDateTotalParentEditView.html';
		this.permission_id = 'punch';
		this.script_name = 'UserDateTotalParentView';
		this.viewId = 'UserDateTotalParent';
		this.table_name_key = 'user_date_total_parent';
		this.context_menu_name = $.i18n._( 'Accumulated Time' );
		this.navigation_label = $.i18n._( 'Accumulated Time' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIUserDateTotal' ))();

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
		this.setSelectRibbonMenuIfNecessary();

	},

	removeEditView: function() {
		this._super( 'removeEditView' );

		if ( this.parent_view_controller && this.parent_view_controller.viewId === 'TimeSheet' ) {
			this.parent_view_controller.onSubViewRemoved();
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

		return [menu];

	},

	openEditView: function( date_str ) {

		var $this = this;

		if ( $this.edit_only_mode ) {

			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

			var date_stamp = Global.strToDate( date_str, 'YYYYMMDD' ).format();

			$this.current_edit_record = {date: date_str, user_id: LocalCacheData.all_url_args.user_id, date_stamp: date_stamp};
			$this.setEditViewWidgetsMode();
			$this.initEditView();

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

			this.setEditViewWidgetsMode();
		}

	},

	buildSearchFields: function() {
		this._super( 'buildSearchFields' );
		this.search_fields = [
		];
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_user_date_total_parent': $.i18n._( 'Accumulated Time' )
		} );

	},

	setCurrentEditRecordData: function() {

		this.edit_view_tab.find( '#tab_user_date_total_parent' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
		this.initSubUserDateTotalView( 'tab_user_date_total_parent' );
		this.setEditViewDataDone();

	},

	onTabShow: function( e, ui ) {
		return;

	},

	setTabStatus: function() {
		return;
	},

	initSubUserDateTotalView: function( tab_id ) {

		var $this = this;
		if ( this.sub_user_date_total_view_controller ) {
			this.sub_user_date_total_view_controller.buildContextMenu( true );
			this.sub_user_date_total_view_controller.setDefaultMenu();
			$this.sub_user_date_total_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_date_total_view_controller.getSubViewFilter = function( filter ) {

				return getFilter( filter, this );
			};

			$this.sub_user_date_total_view_controller.initData();
			return;
		}

		Global.loadScriptAsync( 'views/attendance/timesheet/UserDateTotalViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'UserDateTotal' + 'View' );
			UserDateTotalViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_date_total_view_controller = subViewController;
			$this.sub_user_date_total_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_date_total_view_controller.getSubViewFilter = function( filter ) {

				return getFilter( filter, this );
			};
			$this.sub_user_date_total_view_controller.parent_view_controller = $this;
			$this.sub_user_date_total_view_controller.initData();

		}

		function getFilter( filter, target ) {
			var date = Global.strToDate( target.parent_edit_record.date, 'YYYYMMDD' ).format();
			filter.date_stamp = date; //Should be an epoch value.
			filter.user_id = target.parent_edit_record.user_id; //Should be selected user_id
			filter.object_type_id = [20, 25, 30, 40, 100, 110];

			return filter;
		}
	}



} );

UserDateTotalParentViewController.loadView = function( container ) {
	Global.loadViewSource( 'UserDateTotalParent', 'UserDateTotalParentView.html', function( result ) {
		var args = { };
		var template = _.template( result, args );

		if ( Global.isSet( container ) ) {
			container.html( template );
		} else {
			Global.contentContainer().html( template );
		}

	} );
};

UserDateTotalParentViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'UserDateTotalParent', 'SubUserDateTotalParentView.html', function( result ) {

		var args = { };
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_date_total_parent_view_controller );
			}

		}

	} );

};