(function( $ ) {

	$.fn.TDatePicker = function( options ) {
		var opts = $.extend( {}, $.fn.TDatePicker.defaults, options );
		Global.addCss( 'global/widgets/datepicker/TDatePicker.css' );

		var $this = this;
		var field;
		var date_picker_input;
		var icon;
		var error_string = '';
		var error_tip_box;
		var mode = 'date';
		var multiple; // This is used to test Punches -> Edit view Date

		var mass_edit_mode = false;
		var check_box = null;

		var enabled = true;

		var is_open = false;

		var focus_out_timer;

		var can_open = false; //default when the calender can be open, we only open it when click on the icon

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
				date_picker_input.addClass( 't-date-picker-readonly' );
				icon.css( 'display', 'none' );
				date_picker_input.attr( 'readonly', 'readonly' )
			} else {
				$this.removeAttr( 'disabled' );
				date_picker_input.removeClass( 't-date-picker-readonly' );
				icon.css( 'display', 'inline' );
				date_picker_input.removeAttr( 'readonly' );
			}

		}

		this.setCheckBox = function( val ) {
			check_box.attr( 'checked', val )
		}

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.attr( 'checked' ) ) {
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
			// This is used to test Punches -> Edit view Date
			if ( multiple ) {
				return [date_picker_input.val()];
			}

			return date_picker_input.val();
		}

		this.setValue = function( val ) {

			//Error: Uncaught TypeError: Cannot read property 'val' of undefined in https://ondemand1.timetrex.com/interface/html5/global/widgets/datepicker/TDatePicker.js?v=8.0.0-20141230-130626 line 144 
			if ( !date_picker_input ) {
				return;
			}

			if ( !val ) {
				val = '';
			}

			date_picker_input.val( val );
		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			multiple = o.multiple; // This is used to test Punches -> Edit view Date

			if ( Global.isSet( o.mode ) ) {
				mode = o.mode
			}

			icon = $( this ).find( '.t-date-picker-icon' );
			date_picker_input = $( this ).find( '.t-date-picker' );
			icon.attr( 'src', Global.getRealImagePath( 'images/cal.gif' ) );

			icon.bind( 'mouseup', function() {

				if ( !enabled ) {
					return;
				}

				if ( !is_open ) {
					date_picker_input.datepicker( 'show' );
					is_open = true;
				} else {
					is_open = false;
					if ( focus_out_timer ) {
						clearTimeout( focus_out_timer );
						focus_out_timer = null;
					}

				}

			} );

			var format = LocalCacheData.getLoginUserPreference().date_format_1;

			//format null exception
			if ( !format ) {
				format = 'dd-mmm-yy';
			}

			if ( format.indexOf( 'yyyy' ) >= 0 ) {
				format = format.replace( 'yyyy', 'yy' );
			} else {
				format = format.replace( 'yy', 'y' );
			}

			if ( format.indexOf( 'dddd' ) >= 0 ) {
				format = format.replace( 'dddd', 'DD' );
			}

			if ( format.indexOf( 'ddd' ) >= 0 ) {
				format = format.replace( 'ddd', 'D' );
			}

			if ( format.indexOf( 'mmmm' ) >= 0 ) {
				format = format.replace( 'mmmm', 'MM' );
			} else {
				format = format.replace( 'mmm', 'M' );
			}

			var day_name_min = [$.i18n._( "Sun" ), $.i18n._( "Mon" ), $.i18n._( "Tue" ),
				$.i18n._( "Wed" ), $.i18n._( "Thu" ), $.i18n._( "Fri" ), $.i18n._( "Sat" )];
			var month_name_short = [$.i18n._( "Jan" ), $.i18n._( "Feb" ),
				$.i18n._( "Mar" ), $.i18n._( "Apr" ), $.i18n._( "May" ),
				$.i18n._( "Jun" ), $.i18n._( "Jul" ), $.i18n._( "Aug" ),
				$.i18n._( "Sep" ), $.i18n._( "Oct" ), $.i18n._( "Nov" ),
				$.i18n._( "Dec" )];

			var current_text = $.i18n._( 'Today' );

			var close_text = $.i18n._( 'Close' );

			if ( mode === 'date' ) {
				date_picker_input = date_picker_input.datepicker( {
					showTime: false,
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
					dayNamesMin: day_name_min,
					currentText: current_text,
					monthNamesShort: month_name_short,
					closeText: close_text,
					beforeShow: function() {
						if ( o.beforeShow ) {
							o.beforeShow();
						}
					},

					onClose: function() {
						focus_out_timer = setTimeout( function() {
							is_open = false;
							if ( o.onClose ) {
								o.onClose();
							}

						}, 100 );

					}

				} );

			} else {
				date_picker_input = date_picker_input.datetimepicker( {
					showTime: false,
					showHour: false,
					showMinute: false,
					changeMonth: true,
					changeYear: true,
					showButtonPanel: true,
					duration: '',
					showAnim: '',
					showOn: '',
					yearRange: '-100:+10',
					closeText: close_text,
					dayNamesMin: day_name_min,
					monthNamesShort: month_name_short,
					currentText: current_text,
					onClose: function() {
						focus_out_timer = setTimeout( function() {
							is_open = false;
						}, 100 )
					}
				} );
			}

			date_picker_input.change( function() {
				if ( check_box ) {
					check_box.attr( 'checked', 'true' )
				}

				$this.trigger( 'formItemChange', [$this] );
			} );

			date_picker_input.mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			date_picker_input.mouseout( function() {
				if ( !$( $this ).is( ':focus' ) ) {
					$this.hideErrorTip();
				}
			} );

//			date_picker_input.focus( function( e ) {
//
//				if ( !enabled || !can_open ) {
//					if ( mode === 'date' ) {
//						date_picker_input.datepicker( 'hide' );
//
//					} else {
//						date_picker_input.datetimepicker( 'hide' );
//
//					}
//				}
//
//				can_open = false;
//
//				is_open = true;
//
//			} );

			date_picker_input.focusin( function( e ) {
				if ( !enabled ) {
					if ( !check_box ) {
						if ( LocalCacheData.current_open_sub_controller &&
							LocalCacheData.current_open_sub_controller.edit_view &&
							LocalCacheData.current_open_sub_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						} else if ( LocalCacheData.current_open_primary_controller &&
							LocalCacheData.current_open_primary_controller.edit_view &&
							LocalCacheData.current_open_primary_controller.is_viewing ) {
							error_string = Global.view_mode_message;
							$this.showErrorTip( 10 );
						}
					}

				} else {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}
			} );

			date_picker_input.focusout( function() {
				$this.hideErrorTip();

			} );

			if ( o.width > 0 ) {
				date_picker_input.width( o.width );
			}

		} );

		return this;

	};

	$.fn.TDatePicker.defaults = {};

})( jQuery );