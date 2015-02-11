(function( $ ) {

	$.fn.ColumnEditor = function( options ) {
		var opts = $.extend( {}, $.fn.ColumnEditor.defaults, options );

		var parent_awesome_box = null;

		var a_dropdown = null;

		var $this = this;

		var is_mouse_over = false;

		var layout_selector = null;

		var user_generic_data_api = null;

		var related_layout_Array = null;

		var all_columns;

		var original_columns;

		var numArray = [
			{ label: $.i18n._( 'Default' ), value: 0 },
			{ label: 5, value: 5 },
			{ label: 10, value: 10 },
			{ label: 15,
				value: 15 },
			{ label: 20, value: 20 },
			{ label: 25, value: 25 },
			{ label: 50, value: 50 },
			{ label: 100,
				value: 100 },
			{ label: 250, value: 250 },
			{ label: 500, value: 500 },
			{ label: 1000, value: 1000 }
		];

		this.getIsMouseOver = function() {
			return is_mouse_over;
		}

		this.getParentAwesomeBox = function() {
			return parent_awesome_box;
		}

		Global.addCss( 'global/widgets/column_editor/ColumnEditor.css' );

		this.show = function() {

			if ( LocalCacheData.openAwesomeBoxColumnEditor ) {

				if ( LocalCacheData.openAwesomeBoxColumnEditor.getParentAwesomeBox().getId() === parent_awesome_box.getId() ) {
					LocalCacheData.openAwesomeBoxColumnEditor.onClose();
					return;
				} else {
					LocalCacheData.openAwesomeBoxColumnEditor.onClose();
				}

			}

			var layout = parent_awesome_box.getLayout();
			var a_dropdown_div = $( this ).find( '.column-editor-drop-down-div' );

			var rows_per_page_div = $( this ).find( '.rows-per-page-div' );

			a_dropdown = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );
			a_dropdown = $( a_dropdown );
			//Create ADropDown
			a_dropdown = a_dropdown.ADropDown( {display_show_all: false, id: 'column_editor', key: 'value', display_close_btn: false} );

			a_dropdown_div.append( a_dropdown );

			//Add Self to UI
			$( 'body' ).append( $( this ) );

			a_dropdown.setColumns( [
				{name: 'label', index: 'label', label: 'Column Name', width: 100, sortable: false}
			] );

			a_dropdown.setUnselectedGridData( parent_awesome_box.getAllColumns() );

			original_columns = parent_awesome_box.getDisplayColumnsForEditor();
			a_dropdown.setSelectGridData( original_columns );

			//Set position
			if ( 958 + $( parent_awesome_box ).offset().left + 50 > Global.bodyWidth() ) {
				$( this ).css( 'left', Global.bodyWidth() - 958 - 50 );
			} else {

				$( this ).css( 'left', $( parent_awesome_box ).offset().left );
			}

			var $$this = this;
			setTimeout( function() {
				if ( ($( $$this ).height() + $( parent_awesome_box ).offset().top + 50 ) > Global.bodyHeight() ) {
					$( $$this ).css( 'top', (Global.bodyHeight() - $( $$this ).height() - 25 ) );
				} else {
					$( $$this ).css( 'top', $( parent_awesome_box ).offset().top + 25 );
				}
			}, 100 );

			$( this ).mouseenter( function() {
				is_mouse_over = true;
			} );

			$( this ).mouseleave( function() {
				is_mouse_over = false;
			} );

			LocalCacheData.openAwesomeBoxColumnEditor = $this;

			if ( layout && Global.isSet( layout.data.type ) && layout.data.type === ALayoutType.saved_layout ) {
				a_dropdown_div.css( 'display', 'none' );
				rows_per_page_div.css( 'display', 'none' );

			} else {
				a_dropdown_div.css( 'display', 'block' );
				rows_per_page_div.css( 'display', 'block' );
			}

			var script_name = parent_awesome_box.getScriptName();

			var api = parent_awesome_box.getAPI();

			api.getOptions( 'columns', {onResult: function( columns_result ) {

				var columns_result_data = columns_result.getResult();
				all_columns = Global.buildColumnArray( columns_result_data );

			}} );

			user_generic_data_api.getUserGenericData( {filter_data: {script: script_name, deleted: false}},
				{onResult: function( results ) {

					var result_data = results.getResult();

					//Save layout array
					related_layout_Array = result_data;

					if ( result_data && result_data.length > 0 ) {

						result_data.sort( function( a, b ) {

							return Global.compare( a, b, 'name' );

						} );

						$( layout_selector ).empty();

						var source_data = [];

						source_data.push( {label: $.i18n._( Global.customize_item ), value: -1} );

						var len = result_data.length;
						for ( var i = 0; i < len; i++ ) {
							var item = result_data[i];
							source_data.push( {label: item.name, value: item.id} );
						}

						layout_selector.setSourceData( source_data );

						if ( layout && Global.isSet( layout.data.type ) && layout.data.type === ALayoutType.saved_layout ) {
							$( $( layout_selector ).find( 'option' ) ).filter(function() {
								return parseInt( $( this ).attr( 'value' ) ) === layout.data.layout_id;
							} ).prop( 'selected', true ).attr( 'selected', true );

							var select_id = layout_selector.getValue();

							//If saved layout is deleted. Show first one and show columns setting
							if ( select_id === -1 ) {
								a_dropdown_div.css( 'display', 'block' );
								rows_per_page_div.css( 'display', 'block' );
							}
						}

					} else {

						source_data = [];

						source_data.push( {label: $.i18n._( Global.customize_item ), value: -1} );

//						$( layout_selector ).append( '<option value="' + -1 + '">' + Global.customize_item + '</option>' );

						layout_selector.setSourceData( source_data );
						//If saved layout is deleted. Show first one and show columns setting
						a_dropdown_div.css( 'display', 'block' );
						rows_per_page_div.css( 'display', 'block' );
					}

				} } );

		}

		this.onClose = function() {
			$( $this ).remove();
			LocalCacheData.openAwesomeBoxColumnEditor = null;
			is_mouse_over = false;
		}

		this.onSave = function() {
			$this.onClose();

			var select_id = layout_selector.getValue();

			if ( !related_layout_Array ) {
				return;
			}

			if ( select_id !== -1 ) {
				var len = related_layout_Array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = related_layout_Array[i];
					if ( item.id === select_id ) {
						item.data.filter_data = Global.convertLayoutFilterToAPIFilter( item );
						item.data.display_columns = $this.buildDisplayColumns( item.data.display_columns );
						parent_awesome_box.onColumnSettingSaveFromLayout( item );
						break;
					}

				}
			} else {
				var rowPerPageSelect = $( $this ).find( '#rows-per-page-selector' );

				rowPerPageSelect.find( 'option:selected' ).each( function() {

					var selectId = $( this ).attr( 'value' );

					var selection_items = a_dropdown.getSelectItems();

					if ( selection_items.length === 0 ) {
						selection_items = original_columns;
					}

					parent_awesome_box.onColumnSettingSave( selection_items, selectId, '-1' );

				} );
			}

		}

		this.buildDisplayColumns = function( api_display_columns ) {
			var len = all_columns.length;
			var len1 = api_display_columns.length;
			var display_columns = [];

			for ( var j = 0; j < len1; j++ ) {
				for ( var i = 0; i < len; i++ ) {
					if ( api_display_columns[j] === all_columns[i].value ) {
						display_columns.push( all_columns[i] );
					}
				}
			}
			return display_columns;
		}

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			var close_btn = $( this ).find( '#close_btn' );

			var save_btn = $( this ).find( '#save_btn' );

			user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();

			parent_awesome_box = o.parent_awesome_box;

			close_btn.bind( 'click', $this.onClose );

			save_btn.bind( 'click', $this.onSave );

			var rows_selector = $( this ).find( '#rows-per-page-selector' );

			var len = numArray.length;

			for ( var i = 0; i < len; i++ ) {
				var item = numArray[i];
				$( rows_selector ).append( '<option value="' + item.value + '">' + item.label + '</option>' );
			}

			$( $( rows_selector ).find( 'option' ) ).filter(function() {
				return $( this ).attr( 'value' ) === parent_awesome_box.getRowPerPage();
			} ).prop( 'selected', true ).attr( 'selected', true );

			layout_selector = $( this ).find( '#layout-selector' );
			layout_selector = layout_selector.TComboBox();

			var a_dropdown_div = $( this ).find( '.column-editor-drop-down-div' );
			var rows_per_page_div = $( this ).find( '.rows-per-page-div' );

			layout_selector.bind( 'formItemChange', function( e, widget ) {
				var select_id = widget.getValue();

				if ( select_id !== -1 ) {
					a_dropdown_div.css( 'display', 'none' );
					rows_per_page_div.css( 'display', 'none' );

				} else {
					a_dropdown_div.css( 'display', 'block' );
					rows_per_page_div.css( 'display', 'block' );
				}
			} );

		} );

		return this;

	};

	$.fn.ColumnEditor.defaults = {

	};

})( jQuery );