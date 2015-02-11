UserGenericStatusWindowController = BaseViewController.extend( {

	el: '.user-generic-data-status',

	batch_id: '',
	user_id: '',

	call_back: null,

	events: {
		'click .done-btn': 'onCloseClick'
	},

	onCloseClick: function() {
		UserGenericStatusWindowController.instance = null;
		$( this.el ).remove();

		if ( this.call_back ) {
			this.call_back();
		}

	},

	initialize: function() {
		this.content_div = $( this.el ).find( '.content' );
		this.batch_id = this.options.batch_id;
		this.user_id = this.options.user_id;
		this.api = new (APIFactory.getAPIClass( 'APIUserGenericStatus' ))();
		this.render();
		this.initData()

	},

	//Don't initOptions if edit_only_mode. Do it in sub views
	initData: function() {
		var $this = this;
		ProgressBar.showOverlay();
		this.getAllColumns( function() {
			$this.initLayout();
		} );
	},

	initLayout: function() {
		var $this = this;
		$this.getDefaultDisplayColumns( function() {
			$this.setSelectLayout();
			$this.search();
			//set right click menu to list view grid
			$this.initRightClickMenu();

		} );
	},

	render: function() {
		var title = $( this.el ).find( '.title' );
		title.text( $.i18n._( 'Status Report' ) );

	},

	getAllColumns: function( callBack ) {

		var $this = this;
		this.api.getOptions( 'columns', {onResult: function( columns_result ) {
			var columns_result_data = columns_result.getResult();
			$this.all_columns = Global.buildColumnArray( columns_result_data );

			if ( callBack ) {
				callBack();
			}

		}} );

	},

	search: function( set_default_menu ) {

		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.batch_id = this.batch_id;
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		this.api['getUserGenericStatus']( filter, {onResult: function( result ) {

			var result_data = result.getResult();
			result_data = Global.formatGridData( result_data, $this.api.key_name );

			$this.grid.clearGridData();
			$this.grid.setGridParam( {data: result_data} );
			$this.grid.trigger( 'reloadGrid' );

			$this.setGridSize();

			ProgressBar.closeOverlay(); //Add this in initData

			if ( set_default_menu ) {
				$this.setDefaultMenu( true );
			}

		}} );

		this.api['getUserGenericStatusCountArray']( this.user_id, this.batch_id, {onResult: function( result ) {

			var result_data = result.getResult();

			var failed = $( $this.el ).find( '.failed' );
			var warning = $( $this.el ).find( '.warning' );
			var success = $( $this.el ).find( '.success' );

			failed.text( result_data.status[10].total + '/' + result_data.total + '( ' + result_data.status[10].percent + '% )' );
			warning.text( result_data.status[20].total + '/' + result_data.total + '( ' + result_data.status[20].percent + '% )' );
			success.text( result_data.status[30].total + '/' + result_data.total + '( ' + result_data.status[30].percent + '% )' )

		}} );

	},

	setGridSize: function() {

	},

	setSelectLayout: function( column_start_from ) {

		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#user_generic_data__status_grid' );
		}

		var column_info_array = [];

		this.select_layout = {id: ''};
		this.select_layout.data = {filter_data: {}, filter_sort: {}};
		this.select_layout.data.display_columns = this.default_display_columns;
		var layout_data = this.select_layout.data;
		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		//Set Data Grid on List view
		var len = display_columns.length;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from
		}

		for ( var i = start_from; i < len; i++ ) {
			var view_column_data = display_columns[i];

			if ( view_column_data.value === 'description' ) {
				var column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 400, sortable: false, title: false};

			} else if ( view_column_data.value === 'status' ) {
				column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label,
					width: 100, sortable: false, title: false, formatter: function( cell_value, related_data, row ) {

						var span = $( "<span></span>" )

						if ( cell_value === 'Failed' ) {
							span.addClass( 'failed-label' );
						} else if ( cell_value === 'Warning' ) {
							span.addClass( 'warning-label' );
						} else if ( cell_value === 'Success' ) {
							span.addClass( 'success-label' );
						}

						span.text( cell_value )
						return span.get( 0 ).outerHTML;
					}};

			} else {
				column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 100, sortable: false, title: false};
			}

			column_info_array.push( column_info );
		}

		if ( !this.grid ) {
			this.grid = grid;

			this.grid = this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: 600,
				rowNum: 10000,
				colNames: [],
				colModel: column_info_array,
				viewrecords: true

			} );

		} else {

//			 var result_data =	$(grid).getGridParam( 'data' );
			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );

//			  this.grid.tableDnD({scrollAmount:0});
			this.grid = this.grid.jqGrid( {
				altRows: true,
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				data: [],
				rowNum: 10000,
				sortable: false,
				datatype: 'local',
				width: 600,
				colNames: [],
				viewrecords: true
			} );

		}

		var content_div = $( this.el ).find( '.content' );

		this.grid.setGridWidth( content_div.width() - 10 );
		this.grid.setGridHeight( content_div.height() - 20 );

		this.filter_data = this.select_layout.data.filter_data;
	}

} );

UserGenericStatusWindowController.instance = null;

UserGenericStatusWindowController.open = function( batch_id, user_id, call_back ) {

	Global.loadViewSource( 'UserGenericStatus', 'UserGenericStatusWindow.css' );

	Global.loadViewSource( 'UserGenericStatus', 'UserGenericStatusWindow.html', function( result ) {
		var args = {failed: $.i18n._( 'Failed' ),
			warning: $.i18n._( 'Warning' ),
			success: $.i18n._( 'Success' )
		};
		var template = _.template( result, args );

		$( 'body' ).append( template );

		//Make it global variable
		UserGenericStatusWindowController.instance = new UserGenericStatusWindowController( {batch_id: batch_id, user_id: user_id, can_cache_controller: false} );
		UserGenericStatusWindowController.instance.call_back = call_back;

	} );
}

UserGenericStatusWindowController.close = function() {
	if ( UserGenericStatusWindowController.instance ) {

	}
}