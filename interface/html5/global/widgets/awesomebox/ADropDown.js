(function( $ ) {

	$.fn.ADropDown = function( options ) {
		var opts = $.extend( {}, $.fn.ADropDown.defaults, options );

		var unselect_grid = null;

		var select_grid = null;

		var total_display_span = null;

		var static_source_data = null; //Always use this to help to set Select data

		var id = null;

		var key = 'id';

		var parent_a_combo_box = null;

		var a_dropdown_this = this;

		var unselect_grid_header_array = [];

		var select_grid_header_array = [];

		var show_search_inputs = false;

		var unselect_grid_search_map = null;

		var select_grid_search_map = null;

		var unselect_grid_sort_map = null;

		var select_grid_sort_map = null;

		var select_item = null;

		var tree_mode = false;

		var unselect_grid_last_row = '';

		var select_grid_last_row = '';

		var allow_multiple_selection = true;

		var allow_drag_to_order = false;

		var pager_data = null;

		var paging_widget = null;

		var real_selected_items = null; //Set this after search in select grid;

		var start;

		var last;

		var next;

		var end;

		var paging_selector;

		var left_buttons_div;

		var right_buttons_div;

		var left_buttons_enable;

		var right_buttons_enable;

		var field;

		var error_tip_box;

		var error_string = '';

		var default_height = 150;

		var unselect_grid_no_result_box = null;

		var select_grid_no_result_box = null;

		var box_width;

		var focus_in_select_grid = false;

		//Select all records in target grid
		var selectAllInGrid = function( target, deSelect ) {
			target.resetSelection();
			if ( !deSelect ) {
				var source_data = target.getGridParam( 'data' );
				var len = source_data.length;
				for ( var i = 0; i < len; i++ ) {
					var item = source_data[i];
					if ( Global.isSet( item.id ) ) {
						target.jqGrid( 'setSelection', item.id, false );
					} else {
						target.jqGrid( 'setSelection', i + 1, false );
					}

				}

				target.parent().parent().parent().find( '.cbox-header' ).attr( 'checked', true );
			}
		}

		Global.addCss( 'global/widgets/awesomebox/ADropDown.css' );

		this.unSelectAll = function( target ) {
			selectAllInGrid( target, true );
		}

		this.getFocusInSeletGrid = function() {
			return focus_in_select_grid;
		}

		this.selectAll = function() {

			if ( focus_in_select_grid ) {
				selectAllInGrid( select_grid );
			} else {
				selectAllInGrid( unselect_grid );
			}

		}

		this.gridScrollTop = function() {

			unselect_grid.parent().parent().scrollTop( 0 );
		};

		this.gridScrollDown = function() {

			unselect_grid.parent().parent().scrollTop( 10000 );
		};

		this.getBoxWidth = function() {
			return box_width;
		}

		this.setErrorStyle = function( errStr, show ) {
			$( this ).addClass( 'error-tip' );

			error_string = errStr;

			if ( show ) {
				this.showErrorTip();
			}
		};

		this.showErrorTip = function( sec ) {

			if ( !Global.isSet( sec ) ) {
				sec = 2
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox()
			}
			error_tip_box.show( this, error_string, sec )
		};

		this.hideErrorTip = function() {

			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}

		}

		this.clearErrorStyle = function() {
			$( this ).removeClass( 'error-tip' );
			error_string = '';
		}

		this.getField = function() {
			return field;
		}

		// Must call after setUnSelectGridData
		this.setValue = function( val ) {
			this.setSelectGridData( val )
		}

		this.getValue = function() {
			return this.getSelectItems()
		}

		this.getSelectGridSortMap = function() {
			return select_grid_sort_map;
		}

		this.getUnSelectGridSortMap = function() {
			return unselect_grid_sort_map;
		}

		this.getUnSelectGridMap = function() {

			if ( !unselect_grid_search_map ) {
				unselect_grid_search_map = {};
			}

			//			  var ids = [];
			//			  if ( allow_multiple_selection ) {
			//				  var select_items = a_dropdown_this.getSelectItems();
			//
			//				  if ( select_items ) {
			//					  for ( var i = 0; i < select_items.length; i++ ) {
			//						  ids.push( select_items[i][key] );
			//					  }
			//				  }
			//			  }

			//			  unselect_grid_search_map.exclude_id = ids;

			return unselect_grid_search_map;
		}

		this.getUnSelectGrid = function() {
			return unselect_grid;
		}

		this.getSelectGrid = function() {
			return select_grid;
		}

		this.getSelectGridMap = function() {

			if ( !select_grid_search_map ) {
				select_grid_search_map = {};
			}

			var ids = [];
			if ( allow_multiple_selection ) {
				var select_items = a_dropdown_this.getSelectItems();
				for ( var i = 0; i < select_items.length; i++ ) {
					ids.push( select_items[i][key] );
				}

			}

			if ( ids.length > 0 ) {
				select_grid_search_map.id = ids;
			} else {
				select_grid_search_map.id = ['-1'];
			}

			return select_grid_search_map;
		}

		this.collectUnselectGridColumns = function() {
			var columns = unselect_grid.getGridParam( 'colModel' );

			var len = columns.length;

			unselect_grid_header_array = [];

			for ( var i = 0; i < len; i++ ) {
				var column_info = columns[i];
				var column_header = $( this ).find( 'div #jqgh_unselect_grid_' + id + '_' + column_info.name );

				unselect_grid_header_array.push( column_header.TGridHeader( {column_model: column_info} ) );

				column_header.bind( 'headerClick', onUnSelectColumnHeaderClick );
			}

			a_dropdown_this.setGridHeaderStyle( 'unselect_grid' );

			function onUnSelectColumnHeaderClick( e, headerE, column_model ) {

				//Error: Uncaught TypeError: Cannot read property 'setCachedSortFilter' of null in https://ondemand3.timetrex.com/interface/html5/global/widgets/awesomebox/ADropDown.js?v=7.4.6-20141027-072624 line 286
				if ( !parent_a_combo_box || !parent_a_combo_box.getAPI() ) {
					return;
				}

				var field = column_model.name;

				if ( field === 'cb' ) { //first column, check box column.
					return;
				}

				if ( headerE.metaKey || headerE.ctrlKey ) {
					a_dropdown_this.buildSortCondition( false, field, 'unselect_grid' );
				} else {
					a_dropdown_this.buildSortCondition( true, field, 'unselect_grid' );

				}

				parent_a_combo_box.setCachedSortFilter( unselect_grid_sort_map );
				a_dropdown_this.setGridHeaderStyle( 'unselect_grid' );
				parent_a_combo_box.onADropDownSearch( 'unselect_grid' );

			}
		}

		this.buildSortCondition = function( reset, field, targetName ) {

			var sort_map = null;
			var nextSort = 'desc';

			if ( targetName === 'unselect_grid' ) {
				sort_map = unselect_grid_sort_map
			} else {
				sort_map = select_grid_sort_map;
			}

			if ( reset ) {

				if ( sort_map && sort_map.length > 0 ) {
					var len = sort_map.length;
					var found = false;
					for ( i = 0; i < len; i++ ) {
						var sortItem = sort_map[i];
						for ( var key in sortItem ) {
							if ( key === field ) {
								if ( sortItem[key] === 'asc' ) {
									nextSort = 'desc'
								} else {
									nextSort = 'asc'
								}

								found = true;
							}
						}

						if ( found ) {
							break;
						}

					}

				}

				sort_map = [
					{}
				];
				sort_map[0][field] = nextSort;

			} else {
				if ( !sort_map ) {
					sort_map = [
						{}
					];
					sort_map[0][field] = 'desc'
				} else {
					len = sort_map.length;
					found = false;
					for ( var i = 0; i < len; i++ ) {
						sortItem = sort_map[i];
						for ( var key in sortItem ) {
							if ( key === field ) {
								if ( sortItem[key] === 'asc' ) {
									sortItem[key] = 'desc'
								} else {
									sortItem[key] = 'asc'
								}

								found = true;
							}
						}

						if ( found ) {
							break;
						}

					}

					if ( !found ) {
						sort_map.push( {} );
						sort_map[len][field] = 'desc';
					}
				}

			}

			if ( targetName === 'unselect_grid' ) {
				unselect_grid_sort_map = sort_map;
			} else {
				select_grid_sort_map = sort_map;
			}

		},

			this.setGridHeaderStyle = function( targetName ) {

				var headerArray = [];
				var sort_map = [];

				if ( targetName === 'unselect_grid' ) {
					headerArray = unselect_grid_header_array;
					sort_map = unselect_grid_sort_map;
				} else {
					headerArray = select_grid_header_array;
					sort_map = select_grid_sort_map;
				}

				var len = headerArray.length;

				for ( var i = 0; i < len; i++ ) {
					var tGridHeader = headerArray[i];
					var field = tGridHeader.getColumnModel().name;

					tGridHeader.cleanSortStyle();

					if ( sort_map ) {
						var sortArrayLen = sort_map.length;

						for ( var j = 0; j < sortArrayLen; j++ ) {
							var sortItem = sort_map[j];
							var sortField = Global.getFirstKeyFromObject( sortItem );
							if ( sortField === field ) {

								if ( sortArrayLen > 1 ) {
									tGridHeader.setSortStyle( sortItem[sortField], j + 1 );
								} else {
									tGridHeader.setSortStyle( sortItem[sortField], 0 );
								}

							}

						}
					}

				}

			},

			this.collectSelectGridColumns = function() {
				var columns = select_grid.getGridParam( 'colModel' );

				var len = columns.length;

				select_grid_header_array = [];

				for ( var i = 0; i < len; i++ ) {
					var column_info = columns[i];
					var column_header = $( this ).find( 'div #jqgh_select_grid_' + id + '_' + column_info.name );

					select_grid_header_array.push( column_header.TGridHeader( {column_model: column_info} ) );

					column_header.bind( 'headerClick', onSelectColumnHeaderClick );

				}
				a_dropdown_this.setGridHeaderStyle( 'select_grid' );

				function onSelectColumnHeaderClick( e, headerE, column_model ) {

					if ( !parent_a_combo_box || !parent_a_combo_box.getAPI() ) {
						return;
					}

					var field = column_model.name;

					if ( field === 'cb' ) { //first column, check box column.
						return;
					}

					if ( headerE.metaKey || headerE.ctrlKey ) {
						a_dropdown_this.buildSortCondition( false, field, 'select_grid' );
					} else {
						a_dropdown_this.buildSortCondition( true, field, 'select_grid' );

					}
					parent_a_combo_box.setCachedSelectedGridSortFilter( select_grid_sort_map );
					a_dropdown_this.setGridHeaderStyle( 'select_grid' );
					parent_a_combo_box.onADropDownSearch( 'select_grid' );

				}

			}

		//HightLight select item in UnSelect grid when !allow_multiple_selection
		this.setSelectItem = function( val, target_grid ) {

			if ( !val ) {
				return;
			}

			if ( !target_grid ) {
				target_grid = unselect_grid;
			}

			var source_data = target_grid.getGridParam( 'data' );

			select_item = val;

			if ( source_data ) {
				$.each( source_data, function( index, content ) {
					if ( content[key] == val[key] ) {  //Some times 0, sometimes '0'

						//Always use id to set select row, all record array should have id
						target_grid.jqGrid( 'setSelection', content['id'], false );

						target_grid.find( 'tr[id="' + content['id'] + '"]' ).focus();
						select_item = content;
						return false;
					}
				} );

				this.setTotalDisplaySpan();
			}

		};

		this.getUnSelectGridData = function() {
			return unselect_grid.getGridParam( 'data' );
		}

		this.getSelectItem = function() {
			return select_item;
		}

		this.getSelectItems = function() {

			//Save last edit row if there is editable row in grid cell;
			if ( unselect_grid_last_row.length > 0 ) {
				unselect_grid.jqGrid( 'saveRow', unselect_grid_last_row );
				unselect_grid_last_row = '';
			}

			if ( select_grid_last_row.length > 0 ) {
				select_grid.jqGrid( 'saveRow', select_grid_last_row );
				select_grid_last_row = '';

			}

			if ( real_selected_items ) {
				return real_selected_items;
			} else {
				return select_grid.getGridParam( 'data' ); //Set this when setSelectGridItems
			}

		};

		this.getAllowMultipleSelection = function() {
			return allow_multiple_selection;
		}

		this.getTreeMode = function() {
			return tree_mode;
		}

		//Must Set this after set Columns
		this.setUnselectedGridData = function( val ) {
			static_source_data = val;

			if ( !tree_mode ) {

				unselect_grid.clearGridData();
				unselect_grid.setGridParam( {data: val} );
				unselect_grid.trigger( 'reloadGrid' );
				this.setTotalDisplaySpan();

			} else {
				this.reSetUnSelectGridTreeData( val );

			}

			this.setUnSelectGridDragAble();

			this.setGridsHeight();

			this.setGridColumnsWidth( unselect_grid );
			this.resizeUnSelectSearchInputs();

		};

		this.setGridColumnsWidth = function( target ) {
			var col_model = target.getGridParam( 'colModel' );
			var grid_data = target.getGridParam( 'data' );
			this.grid_total_width = 0;

			//Possible exception
			//Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand1.timetrex.com/interface/html5/#!m=TimeSheet&date=20141102&user_id=53130 line 4288
			if ( !col_model ) {
				return;
			}

			if ( allow_multiple_selection && col_model.length == 2 || (!allow_multiple_selection && col_model.length === 1) ) {
				return;
			}

			if ( allow_multiple_selection ) {
				this.grid_total_width = 20;
			}

			for ( var i = 0; i < col_model.length; i++ ) {
				var col = col_model[i];
				var field = col.name;
				var longest_words = '';

				for ( var j = 0; j < grid_data.length; j++ ) {
					var row_data = grid_data[j];
					if ( !row_data.hasOwnProperty( field ) ) {
						break;
					}

					var current_words = row_data[field];

					if ( !current_words ) {
						current_words = '';
					}

					if ( !longest_words ) {
						longest_words = current_words.toString();
					} else {
						if ( current_words && current_words.toString().length > longest_words.length ) {
							longest_words = current_words.toString();
						}
					}

				}

				if ( longest_words ) {
					var width_test = $( '<span id="width_test" />' );
					width_test.css( 'font-size', '11' );
					width_test.css( 'font-weight', 'normal' );
					$( 'body' ).append( width_test );
					width_test.text( longest_words );

					var width = width_test.width();
					width_test.text( col.label );
					var header_width = width_test.width();

					if ( header_width > width ) {
						width = header_width + 20;
					}

					this.grid_total_width += width + 10;

					target.setColProp( field, {widthOrg: width + 10} );
					width_test.remove();

				}
			}
			var gw = target.getGridParam( 'width' );

			if ( this.grid_total_width > gw ) {
				gw = this.grid_total_width;

			}

			target.setGridWidth( gw );

		};

		this.getPagerData = function() {
			return pager_data;
		}

		//Alwasy setPager data no matter static options or api.
		this.setPagerData = function( value ) {

			pager_data = value;

			if ( LocalCacheData.paging_type === 0 ) {
				if ( paging_widget.parent().length > 0 ) {
					paging_widget.remove();
				}

				paging_widget.css( 'width', unselect_grid.width() )
				unselect_grid.append( paging_widget );
				paging_widget.click( $.proxy( this.onPaging, this ) );

				if ( !pager_data || pager_data.is_last_page || pager_data.last_page_number < 0 ) {
					paging_widget.css( 'display', 'none' );
				} else {
					paging_widget.css( 'display', 'block' );
				}

			} else {

				if ( !pager_data || pager_data.last_page_number < 0 ) {
					left_buttons_div.css( 'display', 'none' );
					right_buttons_div.css( 'display', 'none' );

				} else {
					left_buttons_div.css( 'display', 'block' );
					right_buttons_div.css( 'display', 'block' );

					if ( pager_data.is_last_page === true ) {
						right_buttons_div.addClass( 'disabled' );
						right_buttons_div.addClass( 'disabled-image' );
						right_buttons_enable = false;
					} else {
						right_buttons_div.removeClass( 'disabled' );
						right_buttons_div.removeClass( 'disabled-image' );
						right_buttons_enable = true;
					}

					if ( pager_data.is_first_page ) {
						left_buttons_div.addClass( 'disabled' );
						left_buttons_div.addClass( 'disabled-image' );
						left_buttons_enable = false;

					} else {
						left_buttons_div.removeClass( 'disabled' );
						left_buttons_div.removeClass( 'disabled-image' );
						left_buttons_enable = true;
					}
				}

			}

			a_dropdown_this.setTotalDisplaySpan();
		}

		this.onPaging = function() {
			parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'next' );
		};

		this.reSetUnSelectGridTreeData = function( val ) {
			var col_model = unselect_grid.getGridParam( 'colModel' );

			if ( !unselect_grid.is( ':visible' ) ) {
				return;
			}

			unselect_grid.jqGrid( 'GridUnload' );
			unselect_grid = $( this ).find( '.unselect-grid' );
			unselect_grid = unselect_grid.jqGrid( {
				altRows: true,
				datastr: val,
				datatype: 'jsonstring',
				sortable: false,
				width: 440,
				height: default_height,
				colNames: [],
				rowNum: 10000,
				colModel: col_model,
				ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
				gridview: true,
				treeGrid: true,
				treeGridModel: 'adjacency',
				treedatatype: 'local',
				ExpandColumn: 'name',
				onSelectRow: function( id ) {

					id = Global.convertToNumberIfPossible( id );

					if ( id >= -1 || $.type( id ) === 'string' ) {
						if ( !allow_multiple_selection ) {
							var source_data = unselect_grid.getGridParam( 'data' );
							$.each( source_data, function( index, content ) {
								if ( content[key] === id ) {
									select_item = content;
									a_dropdown_this.trigger( 'close', [a_dropdown_this] );
									return;
								}
							} );

						}
					}
				},
				jsonReader: {
					repeatitems: false,
					root: function( obj ) {
						return obj;
					},
					page: function( obj ) {
						return 1;
					},
					total: function( obj ) {
						return 1;
					},
					records: function( obj ) {
						return obj.length;
					}
				}
			} );
		}

		this.setGridsHeight = function() {
			//Calculate the max possible size of awesomebox.

			if ( !parent_a_combo_box ) {
				return;
			}

			var top_offset = parent_a_combo_box.offset().top;
			var bottom_offset = Global.bodyHeight() - top_offset - 30;
			var new_height = top_offset > bottom_offset ? top_offset : bottom_offset;

			new_height = new_height - 130;

			var source_data = parent_a_combo_box.getStaticSourceData();

			if ( !source_data ) {
				new_height = default_height;
			} else {
				var source_height = source_data.length * 23;

				if ( source_height < default_height ) {
					new_height = default_height;
				} else if ( source_height > default_height && source_height < new_height ) {
					new_height = source_height;
				}
			}

			unselect_grid.setGridHeight( new_height );
			if ( allow_multiple_selection ) {
				select_grid.setGridHeight( new_height );
			}

			this.setPosition( top_offset, new_height );

		}

		this.setPosition = function( top_offset, new_height ) {

			if ( top_offset + new_height + 130 < Global.bodyHeight() ) {
				a_dropdown_this.parent().css( 'top', top_offset + 25 );
			} else {

				if ( new_height != default_height ) {
					a_dropdown_this.parent().css( 'top', (top_offset - new_height - 125) );
				} else {
					a_dropdown_this.parent().css( 'top', (top_offset - new_height - 125) );
				}

			}
		}

		//Do this before set data
		this.setColumns = function( val ) {

			unselect_grid.jqGrid( 'GridUnload' );
			unselect_grid = $( this ).find( '.unselect-grid' );
			unselect_grid.getGridParam();
			var unselect_grid_search_div = $( this ).find( '.unselect-grid-search-div' );
			var select_grid_search_div = $( this ).find( '.select-grid-search-div' );

			var gridWidth = 440;
			if ( val.length > 3 ) {
				gridWidth = val.length * 110;
				box_width = gridWidth;
				if ( allow_multiple_selection && gridWidth > (Global.bodyWidth() / 2 - 30 - 15) ) {
					box_width = (Global.bodyWidth() / 2 - 30 - 15);
				} else if ( !allow_multiple_selection && gridWidth > (Global.bodyWidth() - 30 - 15) ) {
					box_width = (Global.bodyWidth() - 30 - 15);
				}

				this.find( '.unselect-grid-div' ).width( box_width + 22 );
				this.find( '.unselect-grid-border-div' ).width( box_width + 2 );

				this.find( '.select-grid-div' ).width( box_width + 22 );
				this.find( '.select-grid-border-div' ).width( box_width + 2 );

				if ( show_search_inputs ) {
					unselect_grid_search_div.css( 'width', gridWidth );
					select_grid_search_div.css( 'width', gridWidth );
				}
			} else {
				box_width = gridWidth;
			}

			if ( !tree_mode ) {

				unselect_grid = unselect_grid.jqGrid( {
					altRows: true,
					data: [],
					datatype: 'local',
					sortable: false,
					width: gridWidth,
					height: default_height,
					colNames: [],
					rowNum: 10000,
					keep_scroll_place: true,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					colModel: val,
					multiselect: allow_multiple_selection,
					multiboxonly: allow_multiple_selection,
					viewrecords: true,
					editurl: 'clientArray',
					resizeStop: function() {
						a_dropdown_this.resizeUnSelectSearchInputs();
					},
					onSelectRow: function( id ) {

						id = Global.convertToNumberIfPossible( id );

						if ( id >= -1 || $.type( id ) === 'string' ) {

							if ( !allow_multiple_selection ) {

								var source_data = unselect_grid.getGridParam( 'data' );

								$.each( source_data, function( index, content ) {

									if ( key !== 'id' ) {
										if ( content['id'] === id ) {
											select_item = content;
											a_dropdown_this.trigger( 'close', [a_dropdown_this] );
											return false;
										}
									} else {
										if ( content[key] === id ) {
											select_item = content;
											a_dropdown_this.trigger( 'close', [a_dropdown_this] );
											return false;
										}
									}

								} );

							}

							if ( unselect_grid_last_row ) {
								unselect_grid.jqGrid( 'saveRow', unselect_grid_last_row );
							}
							unselect_grid.jqGrid( 'editRow', id, true );
							unselect_grid_last_row = id;

							a_dropdown_this.setTotalDisplaySpan();

							function getSelectValue() {
								var len = source_data.length;

							}
						}
					}
				} );
			} else {
				unselect_grid = unselect_grid.jqGrid( {
					altRows: true,
					datastr: [],
					datatype: 'jsonstring',
					sortable: false,
					width: gridWidth,
					height: default_height,
					colNames: [],
					rowNum: 10000,
					colModel: val,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					onSelectRow: a_dropdown_this.onUnSelectGridSelectRow,
					gridview: true,
					treeGrid: true,
					treeGridModel: 'adjacency',
					treedatatype: 'local',
					ExpandColumn: 'name',

					jsonReader: {
						repeatitems: false,
						root: function( obj ) {
							return obj;
						},
						page: function( obj ) {
							return 1;
						},
						total: function( obj ) {
							return 1;
						},
						records: function( obj ) {
							return obj.length;
						}
					}
				} );
			}

			this.collectUnselectGridColumns(); //Make each column as THeader plugin and save them

			if ( show_search_inputs && parent_a_combo_box.getAPI() ) {
				this.buildUnSelectSearchInputs(); //Build search input above columns
			}

			select_grid.jqGrid( 'GridUnload' );
			select_grid = $( this ).find( '.select-grid' );
			select_grid = select_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: gridWidth,
				height: default_height,
				colNames: [],
				rowNum: 10000,
				ondblClickRow: this.onSelectGridDoubleClick,
				colModel: val,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true,
				keep_scroll_place: true,
				editurl: 'clientArray',
				resizeStop: function() {
					a_dropdown_this.resizeSelectSearchInputs();
				},
				onSelectRow: function( id ) {
					if ( id ) {

						if ( select_grid_last_row ) {
							select_grid.jqGrid( 'saveRow', select_grid_last_row );
						}
						select_grid.jqGrid( 'editRow', id, true );
						select_grid_last_row = id;
					}

				}
			} );

			this.collectSelectGridColumns(); //Make each column as THeader plugin and save them
			if ( show_search_inputs && parent_a_combo_box.getAPI() ) {
				this.buildSelectSearchInputs(); //Build search input above columns
			}

		}

		this.buildSelectSearchInputs = function() {
			var len = select_grid_header_array.length;

			var search_div = $( this ).find( '.select-grid-search-div' );
			var first_column_width = 0;
			var search_input_array = [];
			for ( var i = 0; i < len; i++ ) {
				var header = select_grid_header_array[i];

				if ( i === 0 ) {
					first_column_width = header.getWidth();
					continue;
				} else if ( allow_multiple_selection && i === 1 ) {
					var search_input = $( "<input type='text' class='search-input'>" );
					search_input.css( 'width', header.getWidth() + first_column_width );
				} else {
					search_input = $( "<input type='text' class='search-input'>" );
					search_input.css( 'width', header.getWidth() );
				}

				search_input.ASearchInput( {column_model: header.getColumnModel()} ); //Make it as ASearchInout Widget;

				search_div.append( search_input );
				search_input_array.push( search_input );
				//Set cached seach_input data back, usually in navigation_mode
				if ( select_grid_search_map ) {
					search_input.setFilter( select_grid_search_map );

				}

				search_input.bind( 'searchEnter', function( e, searchVal, field ) {

					if ( a_dropdown_this.getValue().length < 1 ) {
						return;
					}

					if ( !select_grid_search_map ) {
						select_grid_search_map = {};
					}

					delete select_grid_search_map.id;

					if ( !searchVal ) {
						delete select_grid_search_map[field]
					} else {
						select_grid_search_map[field] = searchVal;
					}

					parent_a_combo_box.setCachedSelectGridSearchInputsFilter( Global.clone( select_grid_search_map ) );

					parent_a_combo_box.onADropDownSearch();
				} )

			}

			var close_btn = $( '<button class="close-btn"></button>' );
			search_div.append( close_btn );
			close_btn.click( function() {

				select_grid_search_map = {};
				parent_a_combo_box.setCachedSelectGridSearchInputsFilter( select_grid_search_map );
				parent_a_combo_box.onADropDownSearch();

				for ( var i = 0; i < search_input_array.length; i++ ) {

					var s_i = search_input_array[i];
					s_i.clearValue();
				}

			} );
		}

		this.resizeUnSelectSearchInputs = function() {
			var search_div = $( this ).find( '.unselect-grid-search-div' );

			var search_inputs = search_div.find( '.search-input' );
			var first_column_width;
			var unselect_grid_search_div = $( this ).find( '.unselect-grid-search-div' );

			var len = search_inputs.length;
			var header;
			var search_input;
			if ( allow_multiple_selection ) {
				first_column_width = unselect_grid_header_array[0].width();

				for ( var i = 0; i < len; i++ ) {
					header = unselect_grid_header_array[i + 1];
					search_input = $( search_inputs[i] );

					if ( i === 0 ) {
						search_input.css( 'width', header.getWidth() + first_column_width );
					} else {
						search_input.css( 'width', header.getWidth() );
					}

				}
			} else {
				for ( i = 0; i < len; i++ ) {
					header = unselect_grid_header_array[i];
					search_input = $( search_inputs[i] );

					search_input.css( 'width', header.getWidth() );

				}
			}

//			//grid header row
			unselect_grid.parent().parent().parent().find( '.ui-jqgrid-hdiv' ).css( 'width', unselect_grid.width() + 18 );
			unselect_grid.parent().parent().parent().parent().css( 'width', unselect_grid.width() + 18 );
			unselect_grid.parent().parent().parent().css( 'width', unselect_grid.width() + 18 );
			unselect_grid.parent().parent().css( 'width', unselect_grid.width() + 18 );
			unselect_grid_search_div.css( 'width', unselect_grid.width() + 15 );

		}

		this.resizeSelectSearchInputs = function() {
			var search_div = $( this ).find( '.select-grid-search-div' );

			var search_inputs = search_div.find( '.search-input' );
			var first_column_width;
			var select_grid_search_div = $( this ).find( '.select-grid-search-div' );

			var len = search_inputs.length;

			first_column_width = select_grid_header_array[0].width();
			for ( var i = 0; i < len; i++ ) {
				var header = select_grid_header_array[i + 1];
				var search_input = $( search_inputs[i] );

				if ( allow_multiple_selection && i === 0 ) {
					search_input.css( 'width', header.getWidth() + first_column_width );
				} else {
					search_input.css( 'width', header.getWidth() );
				}

			}

			//grid header row
			select_grid.parent().parent().parent().find( '.ui-jqgrid-hdiv' ).css( 'width', select_grid.width() + 18 );
			select_grid.parent().parent().parent().parent().css( 'width', select_grid.width() + 18 );
			select_grid.parent().parent().parent().css( 'width', select_grid.width() + 18 );
			select_grid.parent().parent().css( 'width', select_grid.width() + 18 );
			select_grid_search_div.css( 'width', select_grid.width() + 15 );

		}

		this.buildUnSelectSearchInputs = function() {
			var len = unselect_grid_header_array.length;

			var search_div = $( this ).find( '.unselect-grid-search-div' );
			var first_column_width = 0;
			var search_input_array = [];
			for ( var i = 0; i < len; i++ ) {
				var header = unselect_grid_header_array[i];

				if ( allow_multiple_selection && i === 0 ) {
					first_column_width = header.getWidth();
					continue;
				} else if ( allow_multiple_selection && i === 1 ) {
					var search_input = $( "<input type='text' class='search-input unselect-grid-search-input'>" );
					search_input.css( 'width', header.getWidth() + first_column_width );
				} else {
					search_input = $( "<input type='text' class='search-input'>" );
					search_input.css( 'width', header.getWidth() );
				}

				search_input.ASearchInput( {column_model: header.getColumnModel()} ); //Make it as ASearchInout Widget;

				search_div.append( search_input );
				search_input_array.push( search_input );

				//Set cached seach_input data back, unsualy in navigation_mode
				if ( unselect_grid_search_map ) {
					search_input.setFilter( unselect_grid_search_map );

				}

				//Do Column Search
				search_input.bind( 'searchEnter', function( e, searchVal, field ) {

					if ( !unselect_grid_search_map ) {
						unselect_grid_search_map = {};
					}

					if ( !searchVal ) {
						delete unselect_grid_search_map[field]
					} else {
						unselect_grid_search_map[field] = searchVal;
					}

					parent_a_combo_box.setCachedSearchInputsFilter( unselect_grid_search_map );

					parent_a_combo_box.onADropDownSearch( 'unselect_grid' );
				} )

			}

			var close_btn = $( '<button class="close-btn"></button>' );
			search_div.append( close_btn );
			close_btn.click( function() {

				unselect_grid_search_map = {};
				parent_a_combo_box.setCachedSearchInputsFilter( unselect_grid_search_map );
				parent_a_combo_box.onADropDownSearch( 'unselect_grid' );

				for ( var i = 0; i < search_input_array.length; i++ ) {

					var s_i = search_input_array[i];
					s_i.clearValue();
				}

			} );

		}

		//Set select item when not allow multiple selection
		setSelectItem = function( val ) {
			select_item = val;
		}

		//Search Reesult in select grid. it's not effect the selectitems when getSelectItems
		this.setSelectGridSearchResult = function( val ) {

			if ( !real_selected_items ) {
				real_selected_items = this.getSelectItems();
			}
			select_grid.clearGridData();
			select_grid.setGridParam( {data: val} );
			select_grid.trigger( 'reloadGrid' );
		}

		//Must Set this after setUnselectedGridData for now
		//Remove select items form allColumn array
		this.setSelectGridData = function( val, searchResult ) {

			if ( parent_a_combo_box && parent_a_combo_box.getAPI() ) {
				val = Global.formatGridData( val, parent_a_combo_box.getAPI().key_name );
			}

			if ( Object.prototype.toString.call( static_source_data ) !== '[object Array]' || static_source_data.length < 1 ) {
				static_source_data = [];
			}

			//Uncaught TypeError: Cannot read property 'length' of undefined
			if ( !val ) {
				val = [];
			}

			var all_columns = static_source_data.slice(); //Copy from Static data
			if ( all_columns && all_columns.length > 0 ) {
				var selectItemLen = val.length;
				for ( var i = 0; i < selectItemLen; i++ ) {
					var select_item = val[i];
					if ( !Global.isSet( select_item[key] ) ) {
						select_item = [];
						select_item[key] = val[i];
						if ( !Global.isSet( tmp_select_items ) ) {
							var tmp_select_items = [];
						}
					}
					for ( var j = 0; j < all_columns.length; j++ ) {
						var fromAllColumn = all_columns[j];
						// we have both string case and number case. sometimes number will be 'xx'. So use == make sure all match
						if ( fromAllColumn[key] == select_item[key] ) {

							//saved search select items may don't have ids if it's saved from flex, so set it back
							if ( !select_item.hasOwnProperty( 'id' ) && fromAllColumn.hasOwnProperty( 'id' ) ) {
								select_item.id = fromAllColumn.id;
							}

							if ( Global.isSet( tmp_select_items ) ) {
								tmp_select_items.push( fromAllColumn );
							}
							if ( !tree_mode ) {
								all_columns.splice( j, 1 );
							}
							break;
						}
					}
				}
			}
			// for all static options, that don't need get reald data, the length should always be match, use temp array because val don't
			//contains full info.
			if ( tmp_select_items && tmp_select_items.length === val.length ||
				(val.length > 0 && !val[0].hasOwnProperty( key )) ) {
				val = tmp_select_items;
			}
//			val = ( Global.isSet( tmp_select_items ) ) ? tmp_select_items : val;

			//don't refresh select grid if it's calling from onDropDownsearch whcih doing search in search input
			if ( !searchResult ) {
				select_grid.clearGridData();
				select_grid.setGridParam( {data: val} );
				select_grid.trigger( 'reloadGrid' );
			}

			if ( !tree_mode ) {
				unselect_grid.clearGridData();
				unselect_grid.setGridParam( {data: all_columns} );
				unselect_grid.trigger( 'reloadGrid' );
				this.setTotalDisplaySpan();
			} else {
				a_dropdown_this.reSetUnSelectGridTreeData( all_columns );
			}

			a_dropdown_this.setSelectGridDragAble();
			a_dropdown_this.setUnSelectGridDragAble();
			a_dropdown_this.setGridColumnsWidth( select_grid );
			a_dropdown_this.resizeSelectSearchInputs();

		};

		this.setUnSelectGridHighlight = function( array ) {
			unselect_grid.resetSelection();
			$.each( array, function( index, content ) {
				unselect_grid.jqGrid( 'setSelection', content, false );
			} );

		};

		this.showNoResultCover = function( target_grid ) {

			this.removeNoResultCover( target_grid );

			var no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
			no_result_box.NoResultBox( {related_view_controller: this} );
			var grid_div;

			if ( target_grid === 'unselect_grid' ) {
				no_result_box.attr( 'id', id + target_grid + '_no_result_box' );

				grid_div = $( this ).find( '#gbox_unselect_grid_' + id );

				unselect_grid_no_result_box = no_result_box;

			} else {
				no_result_box.attr( 'id', id + target_grid + '_no_result_box' );

				grid_div = $( this ).find( '#gbox_select_grid_' + id );

				select_grid_no_result_box = no_result_box;

			}

			grid_div.append( no_result_box );

		};

		this.removeNoResultCover = function( target_grid ) {
			if ( target_grid === 'unselect_grid' ) {

				if ( unselect_grid_no_result_box ) {
					unselect_grid_no_result_box.remove();
					unselect_grid_no_result_box = null;
				}

			} else {

				if ( select_grid_no_result_box ) {
					select_grid_no_result_box.remove();
					select_grid_no_result_box = null;
				}

			}
		}

		this.setSelectGridHighlight = function( array ) {
			select_grid.resetSelection();
			$.each( array, function( index, content ) {
				select_grid.jqGrid( 'setSelection', content, false );
			} );

		}

		this.setUnSelectGridDragAble = function() {

			var highlight_Rows = null;

			var trs = unselect_grid.find( 'tr.ui-widget-content' ).attr( 'draggable', 'true' );

			if ( ie <= 9 ) {
				trs.bind( 'selectstart', function( event ) {
					this.dragDrop();
					return false;
				} );
			}

			trs.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
				var target = $( event.target );

				var container = $( "<table class='drag-holder-table'></table>" );
				highlight_Rows = unselect_grid.find( 'tr.ui-state-highlight' );
				var cloneRows = [];
				var len = highlight_Rows.length;

				if ( len === 0 ) {
					len = 1;
					unselect_grid.jqGrid( 'setSelection', target.attr( 'id' ), false );

				}

				if ( len === 1 ) {
					highlight_Rows = unselect_grid.find( 'tr.ui-state-highlight' );
					var clone_row = $( highlight_Rows[0] ).clone();

					clone_row.children().eq( 0 ).remove();
					clone_row.find( 'td' ).css( 'padding-right', 10 );
					clone_row.find( 'td' ).css( 'padding-left', 10 );

					container.append( clone_row );
				} else {
					container.append( len + ' item(s) selected' );
				}

				$( 'body' ).find( '.drag-holder-table' ).remove();

				$( 'body' ).append( container );

				event.originalEvent.dataTransfer.setData( 'Text', 'un_select_grid' );//JUST ELEMENT references is ok here NO ID

				if ( event.originalEvent.dataTransfer.setDragImage ) {
					event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
				}

				return true;
			} );

			unselect_grid.parent().parent().unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
			} );

			unselect_grid.parent().parent().unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				//drag from left to right
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {
					var grid_selected_id_array = select_grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( false, grid_selected_id_array );
					}
				}
			} );

		}

		//Start Drag
		this.setSelectGridDragAble = function() {

			var highlight_Rows = null;
			var $$this = this;

			var trs = select_grid.find( 'tr.ui-widget-content' ).attr( 'draggable', 'true' );

			trs.attr( 'draggable', true );

			if ( ie <= 9 ) {
				trs.bind( 'selectstart', function( event ) {
					this.dragDrop();
					return false;
				} );
			}

			trs.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
				var target = $( event.target );
				var container = $( "<table class='drag-holder-table' from='select_grid'></table>" );
				highlight_Rows = select_grid.find( 'tr.ui-state-highlight' );
				var cloneRows = [];
				var len = highlight_Rows.length;

				if ( len === 0 ) {
					len = 1;
					select_grid.jqGrid( 'setSelection', target.attr( 'id' ), false );

				}

				if ( len === 1 ) {
					highlight_Rows = select_grid.find( 'tr.ui-state-highlight' );

					var clone_row = $( highlight_Rows[0] ).clone();

					clone_row.children().eq( 0 ).remove();
					clone_row.find( 'td' ).css( 'padding-right', 10 );
					clone_row.find( 'td' ).css( 'padding-left', 10 );

					container.append( clone_row );

					//						  container.append( $( highlight_Rows[0] ).clone() );
				} else {
					container.append( len + ' item(s) selected' );
				}

				$( 'body' ).find( '.drag-holder-table' ).remove();

				$( 'body' ).append( container );

				event.originalEvent.dataTransfer.setData( 'Text', 'select_grid' );//JUST ELEMENT references is ok here NO ID

				if ( event.originalEvent.dataTransfer.setDragImage ) {
					event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
				}

				return true;

			} );

			select_grid.parent().parent().unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
			} );

			select_grid.parent().parent().unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				//drag from left to right
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {
					if ( !tree_mode ) {
						var grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
						var grid_selected_length = grid_selected_id_array.length;
						if ( grid_selected_length > 0 ) {
							a_dropdown_this.moveItems( true, grid_selected_id_array );
						}
					} else {
						var selectRow = unselect_grid.jqGrid( 'getGridParam', 'selrow' );
						a_dropdown_this.moveItems( true, [selectRow] );
					}

					return;
				}

			} );

			//when drag item to the header row, put them as first row
			var parent_grid_container = select_grid.parent().parent().parent();
			parent_grid_container = parent_grid_container.find( '.ui-jqgrid-labels' );

			parent_grid_container.unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
				$( trs[0] ).addClass( 'drag-over-top' );
			} );

			parent_grid_container.unbind( 'dragleave' ).bind( 'dragleave', function( event ) {

				$( trs[0] ).removeClass( 'drag-over-top' );

			} );

			parent_grid_container.unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				$( trs[0] ).removeClass( 'drag-over-top' );
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {

					var firstTr = select_grid.find( 'tr.ui-widget-content' )[0];
					var rows = select_grid.find( 'tr.ui-state-highlight' );

					var len = rows.length;

					for ( var i = len - 1; i >= 0; i-- ) {

						var value = rows[i];
						var row = $( value );

						var target_row_index = 0;
						var select_items = a_dropdown_this.getSelectItems();
						var drag_item_index = value.rowIndex - 1;

						select_items.splice( target_row_index, 0, select_items.splice( drag_item_index, 1 )[0] );

						$( row ).insertBefore( firstTr );
					}

					var scroll_position = select_grid.closest( '.ui-jqgrid-bdiv' ).scrollTop();

					select_grid.trigger( 'reloadGrid' );

					$$this.setSelectGridDragAble();

					rows = select_grid.find( 'tr.ui-widget-content' );

					$.each( highlight_Rows, function( index, value ) {

						var item = value;
						var itemLabel = $( item ).find( 'td' )[1].innerHTML;

						$.each( rows, function( index1, value1 ) {
							var row = value1;

							var rowLabel = $( row ).find( 'td' )[1].innerHTML;
							if ( itemLabel === rowLabel ) {
								select_grid.jqGrid( 'setSelection', $( row ).attr( 'id' ), false );
								return false;
							}
						} );

					} );

					select_grid.closest( '.ui-jqgrid-bdiv' ).scrollTop( scroll_position );
				} else if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {
					target_row_index = -1;
					var grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;
					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( true, grid_selected_id_array, target_row_index );
					}
				}

			} );

			trs.unbind( 'dragover' ).bind( 'dragover', function( event ) {

				$( this ).addClass( 'drag-over-bottom' );

			} );

			trs.unbind( 'dragleave' ).bind( 'dragleave', function( event ) {

				$( this ).removeClass( 'drag-over-bottom' );

			} );

			trs.unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				$( this ).removeClass( 'drag-over-bottom' );

				var $this = this;
				// Dont do drag to order
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {
					var rows = select_grid.find( 'tr.ui-state-highlight' );

					var len = rows.length;

					for ( var i = len - 1; i >= 0; i-- ) {

						var value = rows[i];
						var row = $( value );

						if ( value === this ) {
							continue;
						}

						var target_row_index = $this.rowIndex - 1;
						var select_items = a_dropdown_this.getSelectItems();
						var drag_item_index = value.rowIndex - 1;

						if ( target_row_index >= drag_item_index ) {
							select_items.splice( target_row_index, 0, select_items.splice( drag_item_index, 1 )[0] );
						} else {
							select_items.splice( target_row_index + 1, 0, select_items.splice( drag_item_index, 1 )[0] );
						}

						$( row ).insertAfter( $this );
					}

					var scroll_position = select_grid.closest( '.ui-jqgrid-bdiv' ).scrollTop();

					select_grid.trigger( 'reloadGrid' );

					$$this.setSelectGridDragAble();

					rows = select_grid.find( 'tr.ui-widget-content' );

					$.each( highlight_Rows, function( index, value ) {

						var item = value;
						var itemLabel = $( item ).find( 'td' )[1].innerHTML;

						$.each( rows, function( index1, value1 ) {
							row = value1;

							var rowLabel = $( row ).find( 'td' )[1].innerHTML;
							if ( itemLabel === rowLabel ) {
								select_grid.jqGrid( 'setSelection', $( row ).attr( 'id' ), false );
								return false;
							}
						} );

					} );

					select_grid.closest( '.ui-jqgrid-bdiv' ).scrollTop( scroll_position );
				} else if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {

					if ( !tree_mode ) {
						target_row_index = $this.rowIndex - 1;
						var grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
						var grid_selected_length = grid_selected_id_array.length;
						if ( grid_selected_length > 0 ) {
							a_dropdown_this.moveItems( true, grid_selected_id_array, target_row_index, $( $this ).attr( 'id' ) );
						}
					} else {
						var selectRow = unselect_grid.jqGrid( 'getGridParam', 'selrow' );
						a_dropdown_this.moveItems( true, [selectRow] );
					}

				}

			} );
		}

		this.setTotalDisplaySpan = function() {

			var grid_selected_id_array;

			if ( allow_multiple_selection ) {
				grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
			} else {
				grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selrow' ) ? [unselect_grid.jqGrid( 'getGridParam', 'selrow' )] : [];
			}

			var grid_selected_length = 0;
			//Uncaught TypeError: Cannot read property 'length' of undefined
			if ( grid_selected_id_array ) {
				grid_selected_length = grid_selected_id_array.length;
			}

			// CLICK TO SHOW MORE MODE OR SHOW ALL
			if ( LocalCacheData.paging_type === 0 || !pager_data || (pager_data && pager_data.last_page_number < 0) ) {
				if ( pager_data ) {
					var totalRows = pager_data.total_rows;
					var start = 1;
					var end = unselect_grid.getGridParam( 'data' ).length;
				} else {
					totalRows = unselect_grid.getGridParam( 'data' ).length;
					start = 1;
					end = unselect_grid.getGridParam( 'data' ).length;
				}
			} else {
				if ( pager_data ) {
					totalRows = pager_data.total_rows;
					start = 0;
					end = 0;

					if ( pager_data.last_page_number > 1 ) {
						if ( !pager_data.is_last_page ) {
							start = (pager_data.current_page - 1) * pager_data.rows_per_page + 1;
							end = start + pager_data.rows_per_page - 1;
						} else {
							start = totalRows - pager_data.rows_per_page + 1;
							end = totalRows;
						}

					} else {
						start = 1;
						end = totalRows;
					}

				} else {
					totalRows = 0;
					start = 0;
					end = 0;
				}
			}

			var totalInfo;
			if ( allow_multiple_selection ) {

				var selected_count = select_grid.getGridParam( 'data' ).length;

				var remain_count = unselect_grid.getGridParam( 'data' ).length;

				if ( remain_count === 0 ) {
					end = 0;
					start = 0;
				} else {
					end = (remain_count + start);
					if ( start === 1 ) {
						end = end - 1;

					}
				}

				if ( totalRows ) {

					//If there is manually added item
					if ( end > totalRows ) {
						totalRows = end;
					}

					totalInfo = start + ' - ' + end + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '. ';

				} else {
					totalInfo = start + ' - ' + end + '.';
				}

				total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo + ' ' + $.i18n._( 'Selected' ) + ': ' + selected_count );

			}
			else {

				if ( end === 0 ) start = 0;
				if ( totalRows ) {

					//If there is manually added item
					if ( end > totalRows ) {
						totalRows = end;
					}

					totalInfo = start + ' - ' + end + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '. ';
				} else {
					totalInfo = start + ' - ' + end + '.';
				}

				total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo );
			}

		}

		this.onUnSelectGridSelectRow = function() {

			this.setTotalDisplaySpan();
		}

		this.onUnSelectGridDoubleClick = function() {

			if ( !tree_mode ) {
				var grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
				var grid_selected_length = grid_selected_id_array.length;
				if ( grid_selected_length > 0 ) {
					a_dropdown_this.moveItems( true, grid_selected_id_array );
				}
			} else {
				var selectRow = unselect_grid.jqGrid( 'getGridParam', 'selrow' );
				a_dropdown_this.moveItems( true, [selectRow] );
			}

		}

		this.onSelectGridDoubleClick = function() {

			var grid_selected_id_array = select_grid.jqGrid( 'getGridParam', 'selarrrow' );
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				a_dropdown_this.moveItems( false, grid_selected_id_array );
			}

		}

		//Move items between 2 grids
		this.moveItems = function( left_to_right, array, index, target_row_id ) {
			var added_items = [];
			var removed_items = [];

			var moved_items_array = array.slice();

			if ( left_to_right ) {
				var source_grid = unselect_grid;
				var target_grid = select_grid;
				var source_data = unselect_grid.getGridParam( 'data' );
				var target_data = select_grid.getGridParam( 'data' );
			} else {
				source_grid = select_grid;
				target_grid = unselect_grid;
				source_data = select_grid.getGridParam( 'data' );
				target_data = unselect_grid.getGridParam( 'data' );
			}

			if ( !Global.isArray( target_data ) ) {
				target_data = [];
			}

			if ( !Global.isSet( source_data[0].id ) ) {

			} else {
				var last_item_index = null; //for drag order fixing when drag to empty space

				if ( !Global.isSet( index ) ) {
					array = array.reverse();
				}

				for ( var i = array.length - 1; i >= 0; i-- ) {
					var selected_item_id = array[i];

					for ( var j = 0; j < source_data.length; j++ ) {
						var from_all_columns_item = source_data[j];
						if ( from_all_columns_item.id == selected_item_id ) {  //html number is string, compare string numbers with number number
							var select_item = from_all_columns_item;

							if ( !tree_mode || !left_to_right ) { //Don't remove item from list if tree mode
								source_grid.jqGrid( 'delRowData', selected_item_id );
								i = i + 1;
							}

							if ( !tree_mode || left_to_right ) {
								if ( tree_mode ) {
									//Make sure only one item can be add to right when tree mode
									var target_data_len = target_data.length;
									var find = false;
									for ( var y = 0; y < target_data_len; y++ ) {
										var existed_item = target_data[y];
										if ( existed_item[key] === select_item[key] ) {
											find = true;
											break
										}
									}

									if ( !find ) {

										if ( index >= 0 ) {
											target_grid.addRowData( selected_item_id, select_item, 'after', target_row_id );
											target_data.splice( target_data.length - 1, 1 );
											target_data.splice( index + 1, 0, select_item );

										} else if ( index === -1 ) { // add to first row
											target_grid.addRowData( selected_item_id, select_item, 'first' );
											target_data.splice( target_data.length - 1, 1 );
											target_data.unshift( select_item );
										} else {
											target_grid.addRowData( selected_item_id, select_item );
											target_data[target_data.length - 1] = select_item; //need this since we need full data, addRowData only keep data which shown on UI
										}

									}
								} else {

									if ( index >= 0 ) {
										target_grid.addRowData( selected_item_id, select_item, 'after', target_row_id );
										target_data.splice( target_data.length - 1, 1 );
										target_data.splice( index + 1, 0, select_item );

									} else if ( index === -1 ) { // add to first row
										target_grid.addRowData( selected_item_id, select_item, 'first' );
										target_data.splice( target_data.length - 1, 1 );
										target_data.unshift( select_item );
									} else {

										target_grid.addRowData( selected_item_id, select_item );
										target_data[target_data.length - 1] = select_item; //

									}

								}

							}
							break;
						}
					}
				}
			}

			if ( !tree_mode ) {
				if ( left_to_right ) {
					target_grid.trigger( 'reloadGrid' );
					a_dropdown_this.setGridColumnsWidth( select_grid );
					a_dropdown_this.resizeSelectSearchInputs();
				}
			} else {
				if ( left_to_right ) {
					a_dropdown_this.reSetUnSelectGridTreeData( source_data );
				} else {

				}
			}

			a_dropdown_this.setSelectGridDragAble();
			a_dropdown_this.setUnSelectGridDragAble();

			a_dropdown_this.updateRealSelectItemsIfNecessary( left_to_right, moved_items_array );

			a_dropdown_this.setTotalDisplaySpan();

		}

		this.updateRealSelectItemsIfNecessary = function( left_to_right, moved_items ) {
			if ( !real_selected_items ) {
				return;
			}

			if ( left_to_right ) {
				var current_items_in_selected_grid = select_grid.getGridParam( 'data' );
				$.each( moved_items, function( index, value ) {
					$.each( current_items_in_selected_grid, function( index1, value1 ) {
						if ( value1[key] == value ) { //use == to match '' or number of id
							real_selected_items.push( value1 );
							return false;
						}
					} );
				} )
			} else {
				$.each( moved_items, function( index, value ) {
					$.each( real_selected_items, function( index1, value1 ) {
						if ( value1[key] == value ) { //use == to match '' or number of id
							real_selected_items.splice( index1, 1 );
							return false;
						}
					} );
				} )
			}

		}

		this.setHeight = function( height ) {
			unselect_grid.setGridHeight( height );
			if ( allow_multiple_selection ) {
				select_grid.setGridHeight( height );
			}
		}

		var setLabels = function() {
			var unselected_items_label = a_dropdown_this.find( '#unSelectedItemsLabel' );
			var un_deselect_all_btn = a_dropdown_this.find( '#unDeselectAllBtn' );
			var unselect_all_btn = a_dropdown_this.find( '#unselect_all_btn' );
			var un_clear_btn = a_dropdown_this.find( '#un_clear_btn' );
			var show_all_check_box_label = a_dropdown_this.find( '#show_all_check_box_label' );

			unselected_items_label.text( $.i18n._( 'UNSELECTED ITEMS' ) );
			un_deselect_all_btn.text( $.i18n._( 'Deselect All' ) );
			unselect_all_btn.text( $.i18n._( 'Select All' ) );
			un_clear_btn.text( $.i18n._( 'Clear' ) );
			show_all_check_box_label.text( $.i18n._( 'Show All' ) );

			var selectedItemsLabel = a_dropdown_this.find( '#selectedItemsLabel' );
			var close_btn = a_dropdown_this.find( '#close_btn' );
			var delete_all_btn = a_dropdown_this.find( '#delete_all_btn' );
			var select_all_btn = a_dropdown_this.find( '#select_all_btn' );
			var clear_btn = a_dropdown_this.find( '#clear_btn' );

			selectedItemsLabel.text( $.i18n._( 'SELECTED ITEMS' ) );
			delete_all_btn.text( $.i18n._( 'Deselect All' ) );
			select_all_btn.text( $.i18n._( 'Select All' ) );
			clear_btn.text( $.i18n._( 'Clear' ) );

		}

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {

			setLabels();

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.default_height > 150 ) {
				default_height = o.default_height;
			}

			field = o.field;

			if ( o.search_input_filter ) {
				unselect_grid_search_map = o.search_input_filter;
			}

			if ( o.select_grid_search_input_filter ) {
				select_grid_search_map = o.select_grid_search_input_filter;
			}

			if ( o.default_sort_filter ) {
				unselect_grid_sort_map = o.default_sort_filter;
			}

			if ( o.default_select_grid_sort_filter ) {
				select_grid_sort_map = o.default_select_grid_sort_filter;
			}

			//Init paging widget

			left_buttons_div = $( this ).find( '.left-buttons-div' );
			right_buttons_div = $( this ).find( '.right-buttons-div' );

			start = $( this ).find( '.start' );
			last = $( this ).find( '.last' );
			next = $( this ).find( '.next' );
			end = $( this ).find( '.end' );

			start.text( 'Start' );
			last.text( 'Previous' );

			next.text( 'Next' );
			end.text( 'End' );

			start.click( function() {
				if ( left_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'start' );
				}
			} );

			last.click( function() {
				if ( left_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'last' );
				}
			} );

			next.click( function() {
				if ( right_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'next' );
				}
			} );

			end.click( function() {
				if ( right_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'end' );
				}
			} );

			left_buttons_div.css( 'display', 'none' );
			right_buttons_div.css( 'display', 'none' );

			if ( LocalCacheData.paging_type !== 10 ) {
				//Click to show more button below the last row
				paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING );
			}

			//Display 'Displaying XX of xx, Selected: xxx'
			total_display_span = $( this ).find( '.total-number-span' );

			if ( allow_multiple_selection ) {
				total_display_span.text( $.i18n._( 'Displaying' ) + ' 0 ' + $.i18n._( 'of' ) + ' 0 ' + $.i18n._( 'total' ) );
			} else {
				total_display_span.text( $.i18n._( 'Displaying' ) + ' 0 - 0 ' + $.i18n._( 'of' ) + ' 0 ' + $.i18n._( 'total' ) + '. ' + $.i18n._( 'Selected' ) + ': 0' );
			}

			if ( Global.isSet( o.allow_multiple_selection ) ) {
				allow_multiple_selection = o.allow_multiple_selection
			}

			if ( !allow_multiple_selection ) {
				var unselect_grd_border_div = $( this ).find( '.unselect-grid-border-div' );
				unselect_grd_border_div.addClass( 'single-mode-border' );

			}

			var unselect_grd_div = $( this ).find( '.unselect-grid-div' );

			unselect_grd_div.bind( 'click', function() {
				focus_in_select_grid = false;

			} );

			var select_grid_div = $( this ).find( '.select-grid-div' );
			var left_and_right_div = $( this ).find( '.left-and-right-div' );
			var unselected_items_label = $( this ).find( '#unselected_items_label' );
			var selected_items_label = $( this ).find( '#selected_items_label' );

			select_grid_div.bind( 'click', function() {
				focus_in_select_grid = true

			} );

			if ( !allow_multiple_selection ) {
				select_grid_div.css( 'display', 'none' );
				left_and_right_div.css( 'display', 'none' );
				unselected_items_label.text( $.i18n._( 'SELECT AN ITEM' ) )
			} else {
				select_grid_div.css( 'display', 'block' );
				left_and_right_div.css( 'display', 'block' );
				unselected_items_label.text( $.i18n._( 'UNSELECTED ITEMS' ) )
				selected_items_label.text( $.i18n._( 'SELECTED ITEMS' ) )
			}

			if ( Global.isSet( o.allow_drag_to_order ) ) {
				allow_drag_to_order = o.allow_drag_to_order;
			}

			//Set UI visibility
			if ( o.display_show_all === true ) {

				var show_all_check_box = $( this ).find( '#show_all_check_box' );

				show_all_check_box.css( 'display', 'normal' );
				$( this ).find( '#show_all_check_box_label' ).css( 'display', 'normal' );

				if ( o.show_all === true ) {
					show_all_check_box.attr( 'checked', 'true' )
				} else {
					show_all_check_box.attr( 'checked', undefined )
				}

				$( this ).find( '#show_all_check_box' ).click( function() {
					var show_all_checked = false;
					if ( show_all_check_box.attr( 'checked' ) === 'checked' || show_all_check_box[0].checked === true ) {
						show_all_checked = true;
					}

					parent_a_combo_box.onShowAll( show_all_checked );

				} );

			} else {
				$( this ).find( '#show_all_check_box' ).css( 'display', 'none' );
				$( this ).find( '#show_all_check_box_label' ).css( 'display', 'none' );

			}

			if ( o.comboBox ) {
				parent_a_combo_box = o.comboBox;

				if ( parent_a_combo_box.allow_multiple_selection ) {
					$( this ).css( 'min-Width', 958 );
				} else {

				}
			} else {
				$( this ).css( 'min-Width', 958 );
			}

			id = o.id;

			$( this ).attr( 'id', o.id + 'ADropDown' );

			if ( o.key ) {
				key = o.key;
			}

			if ( o.show_search_inputs ) {
				show_search_inputs = o.show_search_inputs
			}

			if ( o.tree_mode ) {
				tree_mode = o.tree_mode;
			}

			unselect_grid = $( this ).find( '.unselect-grid' ); //Must add id for them

			unselect_grid.attr( 'id', 'unselect_grid' + '_' + id );

			if ( !tree_mode ) {
				unselect_grid = unselect_grid.jqGrid( {
					altRows: true,
					data: [],
					datatype: 'local',
					sortable: false,
					width: 440,
					height: default_height,
					colNames: [],
					rowNum: 10000,
					colModel: [],
					onSelectRow: a_dropdown_this.onUnSelectGridSelectRow,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					multiselect: allow_multiple_selection,
					multiboxonly: allow_multiple_selection,
					viewrecords: true
				} );
			} else {
				unselect_grid = unselect_grid.jqGrid( {
					altRows: true,
					datastr: [],
					datatype: 'jsonstring',
					sortable: false,
					width: 440,
					height: default_height,
					colNames: [],
					rowNum: 10000,
					sortname: 'id',
					colModel: [],
					onSelectRow: a_dropdown_this.onUnSelectGridSelectRow,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					gridview: true,
					treeGrid: true,
					treeGridModel: 'adjacency',
					treedatatype: 'local',
					ExpandColumn: 'name',
					jsonReader: {
						repeatitems: false,
						root: function( obj ) {
							return obj;
						},
						page: function( obj ) {
							return 1;
						},
						total: function( obj ) {
							return 1;
						},
						records: function( obj ) {
							return obj.length;
						}
					}
				} );
			}

			select_grid = $( this ).find( '.select-grid' );

			select_grid.attr( 'id', 'select_grid' + '_' + id );

			select_grid = select_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: 440,
				height: default_height,
				rowNum: 10000,
				colNames: [],
				colModel: [],
				ondblClickRow: a_dropdown_this.onSelectGridDoubleClick,
				multiselect: true,
				multiboxonly: true,
				viewrecords: true
			} );

			var right_arrow = $( this ).find( '.a-grid-right-arrow' );
			var left_arrow = $( this ).find( '.a-grid-left-arrow' );

			right_arrow.attr( 'src', Global.getRealImagePath( 'css/global/widgets/awesomebox/images/nav_right.png' ) );
			left_arrow.attr( 'src', Global.getRealImagePath( 'css/global/widgets/awesomebox/images/nav_left.png' ) );

			right_arrow.click( function() {

				if ( !tree_mode ) {
					var grid_selected_id_array = unselect_grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;
					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( true, grid_selected_id_array );
					}
				} else {
					var selectRow = unselect_grid.jqGrid( 'getGridParam', 'selrow' );
					a_dropdown_this.moveItems( true, [selectRow] );
				}

			} );

			left_arrow.click( function() {
				var grid_selected_id_array = select_grid.jqGrid( 'getGridParam', 'selarrrow' );
				var grid_selected_length = grid_selected_id_array.length;

				if ( grid_selected_length > 0 ) {
					a_dropdown_this.moveItems( false, grid_selected_id_array );
				}

			} );

			//Set Action  Buttons

			//UnSelect grid
			var unselect_all_btn = $( this ).find( '#unselect_all_btn' );

			unselect_all_btn.click( function() {
				selectAllInGrid( unselect_grid );
			} );

			var un_deselect_all_Btn = $( this ).find( '#unDeselectAllBtn' );

			un_deselect_all_Btn.click( function() {
				selectAllInGrid( unselect_grid, true );

			} );

			var un_clear_btn = $( this ).find( '#un_clear_btn' );

			un_clear_btn.click( function() {
				cleanAllInGrid( unselect_grid, true );

			} );

			if ( tree_mode || !allow_multiple_selection ) {
				unselect_all_btn.css( 'display', 'none' );
				un_deselect_all_Btn.css( 'display', 'none' );
				un_clear_btn.css( 'display', 'none' );
			} else {
				unselect_all_btn.css( 'display', 'block' );
				un_deselect_all_Btn.css( 'display', 'block' );
				un_clear_btn.css( 'display', 'block' );
			}

			//Select Grid
			var select_all_btn = $( this ).find( '#select_all_btn' );

			select_all_btn.click( function() {
				selectAllInGrid( select_grid );

			} );

			var delete_all_btn = $( this ).find( '#delete_all_btn' );

			delete_all_btn.click( function() {
				selectAllInGrid( select_grid, true );

			} );

			var clear_btn = $( this ).find( '#clear_btn' );

			clear_btn.click( function() {
				cleanAllInGrid( select_grid );
			} );

			var close_btn = $( this ).find( '#close_btn' );

			if ( Global.isSet( o.display_close_btn ) && !o.display_close_btn ) {
				close_btn.css( 'display', 'none' )
			} else {
				close_btn.css( 'display', 'block' );

				close_btn.click( function() {
					a_dropdown_this.trigger( 'close', [a_dropdown_this] );

				} );
			}

			//Move all records from target grid to another
			function cleanAllInGrid( target, left_to_right ) {

				var finalArray = [];
				if ( left_to_right ) {
					var source_grid = unselect_grid;
					var target_grid = select_grid;
					var source_data = unselect_grid.getGridParam( 'data' );
					var target_data = select_grid.getGridParam( 'data' );
				} else {
					source_grid = select_grid;
					target_grid = unselect_grid;
					source_data = select_grid.getGridParam( 'data' );
					target_data = unselect_grid.getGridParam( 'data' );
				}

				finalArray = target_data.concat( source_data );

				target_grid.clearGridData();
				target_grid.setGridParam( {data: finalArray} );
				target_grid.trigger( 'reloadGrid' );

				source_grid.clearGridData();
				source_grid.trigger( 'reloadGrid' );

				a_dropdown_this.setTotalDisplaySpan();

			}

		} );

		return this;

	};

	$.fn.ADropDown.defaults = {};

})
( jQuery );