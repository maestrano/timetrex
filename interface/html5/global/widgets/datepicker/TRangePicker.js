(function( $ ) {

	$.fn.TRangePicker = function( options ) {
		var opts = $.extend( {}, $.fn.TRangePicker.defaults, options );
		Global.addCss( 'global/widgets/datepicker/TDatePicker.css' );

		var $this = this;
		var field;
		var date_picker_input;
		var icon;
		var error_string = '';
		var error_tip_box;
		var mass_edit_mode = false;
		var check_box = null;
		var enabled = true;
		var is_open = false;
		var focus_out_timer;
		var is_mouse_over = false;
		var ranger_picker;

		var range_start_picker;
		var range_end_picker;

		var result;

		var editor;

		var can_not_close = false;

		var tab_bars;

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
				date_picker_input.addClass( 't-date-picker-readonly' );
				icon.css( 'display', 'none' );
//				date_picker_input.attr( 'readonly', 'readonly' )
			} else {
				$this.removeAttr( 'disabled' );
				date_picker_input.removeClass( 't-date-picker-readonly' );
				icon.css( 'display', 'inline' );
//				date_picker_input.removeAttr( 'readonly' );
			}

		}

		this.setCheckBox = function( val ) {
			check_box.attr( 'checked', val )
		}

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.attr( 'checked' ) || check_box[0].checked === true ) {
					return true
				}
			}

			return false;
		}

		this.setMassEditMode = function( val ) {
			mass_edit_mode = val;

			if ( mass_edit_mode ) {
				check_box = $( " <input type='checkbox' class='mass-edit-checkbox' />" );
				check_box.insertBefore( $( this ) );

				check_box.change( function() {
					$this.trigger( 'formItemChange', [$this] );
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		}

		this.setErrorStyle = function( errStr, show ) {
			date_picker_input.addClass( 'error-tip' );

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
			date_picker_input.removeClass( 'error-tip' );
			error_string = '';
		}

		this.getField = function() {
			return field;
		}

		this.getDefaultFormatValue = function() {
			var val = date_picker_input.val();

			val = Global.strToDate( val ).format( 'YYYYMMDD' );

			return val;
		}

		this.getValue = function() {
			return result;
		}

		this.setValue = function( val ) {

			if ( !val ) {
				val = '';
			}

			if ( $.type( val ) !== 'array' ) {
				result = [val];
				date_picker_input.val( val );

			} else {
				result = val;

				if ( val.length == 1 ) {
					date_picker_input.val( val[0] );
				} else {
					date_picker_input.val( val.length + ' ' + $.i18n._( 'dates selected' ) );
				}

			}

		};

		this.close = function() {

			if ( can_not_close ) {
				return;
			}

			var tab_index = tab_bars.tabs( 'option', 'selected' );

			ranger_picker.remove();
			is_open = false;
			LocalCacheData.openRangerPicker = null;
			is_mouse_over = false; //When close from esc, this maybe true

			if ( tab_index == 0 ) {
				result = range_start_picker.val() + ' - ' + range_end_picker.val();
				date_picker_input.val( result );
			} else {
				result = editor.getValue();

				if ( result.length > 1 ) {
					date_picker_input.val( result.length + ' ' + $.i18n._( 'dates selected' ) );
				} else {
					date_picker_input.val( result[0] );
				}

			}

			setTimeout( function() {
				$this.trigger( 'formItemChange', [$this] );
			}, 100 );
//

		};

		this.getIsMouseOver = function() {
			return is_mouse_over;
		};

		var insideEditorSetValue = function( val ) {

			var len = val ? val.length : 0;
			this.removeAllRows();

			if ( len > 0 ) {
				for ( var i = 0; i < val.length; i++ ) {
					if ( Global.isSet( val[i] ) ) {
						var row = val[i];
						this.addRow( row );
					}
				}
			} else {
				this.addRow( '' );
			}

		};

		var setEditViewDataDone = function() {
			this._super( 'setEditViewDataDone' );
			this.initInsideEditorData();

		};

		var initInsideEditorData = function() {
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

		};

		var insideEditorRemoveRow = function( row ) {
			var index = row[0].rowIndex - 1;
			row.remove();
			this.rows_widgets_array.splice( index, 1 );
			this.removeLastRowLine();
		};

		var insideEditorAddRow = function( data ) {
			if ( !data ) {
				data = '';
			}

			if ( this.rows_widgets_array.length > 0 && !data ) {
				var current_data = this.rows_widgets_array[this.rows_widgets_array.length - 1].start_date_stamp.getValue()

				if ( !current_data ) {
					current_data = new Date();
				} else {
					current_data = Global.strToDate( current_data );
				}

				current_data = new Date( new Date( current_data.getTime() ).setDate( current_data.getDate() + 1 ) );
				data = current_data.format();

			} else if ( this.rows_widgets_array.length === 0 && !data ) {
				data = new Date().format();
			}

			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = {}; //Save each row's widgets

			//Build row widgets

			//Date
			var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
			form_item_input.TDatePicker( {field: 'start_date_stamp', width: 200,
				beforeShow: function() {
					can_not_close = true;
				},
				onClose: function() {
					can_not_close = false;
				}} );

			form_item_input.setValue( data );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );

			$( render ).append( row );

			this.rows_widgets_array.push( widgets );

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();
		};

		var insideEditorGetValue = function( current_edit_item_id ) {
			var len = this.rows_widgets_array.length;

			var result = [];

			for ( var i = 0; i < len; i++ ) {
				var row = this.rows_widgets_array[i];
				if ( row.start_date_stamp.getValue() ) {
					result.push( row.start_date_stamp.getValue() );
				}

			}

			return result;
		};

		var show = function() {
			ranger_picker = $( Global.loadWidget( 'global/widgets/datepicker/TRangePicker.html' ) );
			var tab_0_label = ranger_picker.find( 'a[ref=tab_range]' );
			var tab_1_label = ranger_picker.find( 'a[ref=tab_date]' );
			tab_0_label.text( $.i18n._( 'Range' ) );
			tab_1_label.text( $.i18n._( 'Dates' ) );
			range_start_picker = ranger_picker.find( '#tab_range_content_div' ).find( '.start-picker' );
			range_end_picker = ranger_picker.find( '#tab_range_content_div' ).find( '.end-picker' );
			var start_picker_label = ranger_picker.find( '#tab_range_content_div' ).find( '.start-picker-label' );
			var end_picker_label = ranger_picker.find( '#tab_range_content_div' ).find( '.end-picker-label' );
			start_picker_label.text( $.i18n._( 'Start' ) + ':' );
			end_picker_label.text( $.i18n._( 'End' ) + ':' );
			var format = LocalCacheData.getLoginUserPreference().date_format_1;
			range_start_picker.datepicker( {showTime: false,
				dateFormat: format,
				showHour: false,
				showMinute: false,
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				duration: '',
				showAnim: '',
				yearRange: '-100:+10',
				showOn: '',
				dayNamesMin: [$.i18n._( "Sun" ), $.i18n._( "Mon" ), $.i18n._( "Tue" ),
					$.i18n._( "Wed" ), $.i18n._( "Thu" ), $.i18n._( "Fri" ), $.i18n._( "Sat" )],
				currentText: $.i18n._( 'Today' ),
				monthNamesShort: [$.i18n._( "Jan" ), $.i18n._( "Feb" ),
					$.i18n._( "Mar" ), $.i18n._( "Apr" ), $.i18n._( "May" ),
					$.i18n._( "Jun" ), $.i18n._( "Jul" ), $.i18n._( "Aug" ),
					$.i18n._( "Sep" ), $.i18n._( "Oct" ), $.i18n._( "Nov" ),
					$.i18n._( "Dec" )]

			} );

			range_end_picker.datepicker( {showTime: false,
				dateFormat: format,
				showHour: false,
				showMinute: false,
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				duration: '',
				showAnim: '',
				yearRange: '-100:+10',
				showOn: '',
				dayNamesMin: [$.i18n._( "Sun" ), $.i18n._( "Mon" ), $.i18n._( "Tue" ),
					$.i18n._( "Wed" ), $.i18n._( "Thu" ), $.i18n._( "Fri" ), $.i18n._( "Sat" )],
				currentText: $.i18n._( 'Today' ),
				monthNamesShort: [$.i18n._( "Jan" ), $.i18n._( "Feb" ),
					$.i18n._( "Mar" ), $.i18n._( "Apr" ), $.i18n._( "May" ),
					$.i18n._( "Jun" ), $.i18n._( "Jul" ), $.i18n._( "Aug" ),
					$.i18n._( "Sep" ), $.i18n._( "Oct" ), $.i18n._( "Nov" ),
					$.i18n._( "Dec" )]

			} );

			var close_icon = ranger_picker.find( '.close-icon' );

			close_icon.click( function() {
				$this.close()
			} );

			//Add render in tab 1

			var tab_date = ranger_picker.find( '#tab_date' );

			var inside_editor_div = tab_date.find( '.inside-editor-div' );
			var args = { start_date_stamp: $.i18n._( 'Date' )
			};

			editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

			editor = editor.InsideEditor( {title: '',
				addRow: insideEditorAddRow,
				getValue: insideEditorGetValue,
				setValue: insideEditorSetValue,
				removeRow: insideEditorRemoveRow,
				parent_controller: this,
				render: 'global/widgets/datepicker/TRangeInsideEditorRender.html',
				render_args: args,
				api: null,
				row_render: 'global/widgets/datepicker/TRangeInsideEditorRow.html'
			} );

			inside_editor_div.append( editor );

			editor.setValue();

			$( 'body' ).append( ranger_picker );

			tab_bars = $( ranger_picker.find( '.edit-view-tab-bar' ) );

			tab_bars = tab_bars.tabs( {show: function( e, ui ) {

				if ( !tab_bars || !tab_bars.is( ':visible' ) ) {
					return;
				}

			}} );

			ranger_picker.mouseenter( function() {
				is_mouse_over = true;
			} );

			ranger_picker.mouseleave( function() {
				is_mouse_over = false;
			} );

			//Set Position

			var range_width = ranger_picker.width();

			if ( range_width + $( $this ).offset().left + 50 > Global.bodyWidth() ) {
				ranger_picker.css( 'left', Global.bodyWidth() - range_width - 50 );
			} else {

				ranger_picker.css( 'left', $( $this ).offset().left );
			}

			if ( $( $this ).offset().top + 25 + 300 < Global.bodyHeight() ) {
				ranger_picker.css( 'top', $( $this ).offset().top + 25 );
			} else {
				ranger_picker.css( 'top', Global.bodyHeight() - 300 );
			}

			LocalCacheData.openRangerPicker = $this;

			if ( result && (typeof result == 'string') ) {
				var result_array = result.split( ' - ' );
				range_start_picker.datepicker( 'setDate', result_array[0] );
				range_end_picker.datepicker( 'setDate', result_array[1] );
			} else if ( result && $.type( result ) === 'array' ) {
				tab_bars.tabs( 'select', 1 );

				editor.setValue( result );
			}

		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;
			icon = $( this ).find( '.t-date-picker-icon' );
			date_picker_input = $( this ).find( '.t-date-picker' );
			icon.attr( 'src', Global.getRealImagePath( 'images/cal.gif' ) );

			date_picker_input.attr( 'readonly', 'readonly' );

			icon.bind( 'mouseup', function() {

				if ( !enabled ) {
					return;
				}

				if ( !is_open ) {
					show();
					is_open = true;
				} else {
					is_open = false;
					if ( focus_out_timer ) {
						clearTimeout( focus_out_timer );
						focus_out_timer = null;
					}

				}

			} );

		} );

		return this;

	};

	$.fn.TRangePicker.defaults = {

	};

})( jQuery );