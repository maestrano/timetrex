(function( $ ) {

	$.fn.TTextInput = function( options ) {
		var opts = $.extend( {}, $.fn.TTextInput.defaults, options );
		var $this = this;
		var field;
		var error_string = '';
		var error_tip_box;

		var mass_edit_mode = false;
		var check_box = null;

		var enabled = true;

		var hasKeyEvent = null;

		//DONT USE THIS ANY MORE
		var need_parser_date = false;

		var need_parser_sec = false;

		var parsed_value = false; //work with need_parser_date

		var api_date = null;

		var validate_timer = null;

		var no_validate_timer = null;

		var password_style = false;

		var disable_keyup_event = false; //set to not send change event when mouseup

		var parseDateAsync = function( callBack ) {

			parsed_value = -1;

			ProgressBar.showOverlay();
			api_date.parseTimeUnit( $this.val(), {onResult: function( result ) {
				parsed_value = result.getResult();

				if ( callBack ) {
					callBack();
				}

				ProgressBar.closeOverlay();

			}} );
		};

		this.setNeedParsDate = function( val ) {
			need_parser_date = val;
		};

		this.setNeedParseSec = function( val ) {
			if ( val ) {
				parsed_value = parseDateAsync();
			}
			need_parser_sec = val;

		}

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'readonly', 'true' );
				$this.addClass( 't-text-input-readonly' );
				if ( check_box ) {
					check_box.hide();
				}
			} else {
				$this.removeAttr( 'readonly' );
				$this.removeClass( 't-text-input-readonly' );
				if ( check_box ) {
					check_box.show();
				}
			}

		};

		this.setReadOnly = function( val ) {
			if ( val ) {
				$this.attr( 'disabled', 'true' );
				$this.addClass( 't-text-input-readonly-bg' );
			} else {
				$this.removeAttr( 'disabled' );
				$this.removeClass( 't-text-input-readonly-bg' );
			}
		};

		this.setCheckBox = function( val ) {
			check_box.attr( 'checked', val )
		};

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.attr( 'checked' ) || check_box[0].checked === true ) {
					return true;
				}
			}

			return false;
		};

		this.setMassEditMode = function( val ) {

			mass_edit_mode = val;

			if ( mass_edit_mode ) {
				check_box = $( " <input type='checkbox' class='mass-edit-checkbox' />" );
				check_box.insertBefore( $( this ) );
				check_box.change( function() {

					if ( need_parser_date || need_parser_sec ) {
						parseDateAsync( function() {
							$this.trigger( 'formItemChange', [$this] );
						} );
					} else {
						$this.trigger( 'formItemChange', [$this] );
					}
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.setField = function( val ) {
			field = val;
		};

		this.getField = function() {
			return field;
		};

		this.getInputValue = function() {

			var val = $this.val();
			return val;

		};
		this.getValue = function() {

			var val = $this.val();
			if ( need_parser_date ) {

				if ( parsed_value === -1 ) {
					parsed_value = api_date.parseTimeUnit( val, {async: false} ).getResult();
				}
				return parsed_value;
			} else if ( need_parser_sec ) {
				return parsed_value;
			}
			else {
				return val;
			}

		};

		this.setValue = function( val ) {

			if ( !val && val !== 0 ) {
				val = '';
			}

			$this.val( val );

			if ( need_parser_date ) {
				parseDateAsync();
			} else if ( need_parser_sec ) {
				parsed_value = val;
				$this.val( Global.secondToHHMMSS( val ) );
			}

		};

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
			error_tip_box.cancelRemove();
			error_tip_box.show( this, error_string, sec )
		};

		this.hideErrorTip = function() {

			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}

		};

		this.clearErrorStyle = function() {
			$( this ).removeClass( 'error-tip' );
			error_string = '';
		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;
			hasKeyEvent = o.hasKeyEvent;

			need_parser_date = o.need_parser_date;
			need_parser_sec = o.need_parser_sec;

			if ( need_parser_date || need_parser_sec ) {
				api_date = new (APIFactory.getAPIClass( 'APIDate' ))();
			}

			if ( o.width && ( o.width > 0 || o.width.indexOf( '%' ) > 0) ) {
				$this.width( o.width );
			}

			if ( o.disable_keyup_event ) {
				disable_keyup_event = o.disable_keyup_event;
			}

			$( this ).keydown( function( e ) {

				if ( hasKeyEvent ) {

					$this.trigger( 'formItemKeyDown', [$this] );
				}

			} );

			$( this ).keyup( function( e ) {

				//don't clean event when click tab
				if ( e.keyCode !== 9 && validate_timer ) {
					clearTimeout( validate_timer );
					validate_timer = null;
				}

				if ( e.keyCode !== 9 && no_validate_timer ) {
					clearTimeout( no_validate_timer );
					no_validate_timer = null;
				}

				if ( hasKeyEvent ) {
					$this.trigger( 'formItemKeyUp', [$this] );
				}

				var validate_sec = 1000;

				if ( error_string && error_string.length > 0 ) {
					validate_sec = 500;
				}

				validate_timer = setTimeout( function() {

					if ( check_box ) {
						check_box.attr( 'checked', 'true' )
					}

					if ( need_parser_date || need_parser_sec ) {
						parseDateAsync( function() {

							if ( !disable_keyup_event ) {
								$this.trigger( 'formItemChange', [$this] );
							}

						} );
					} else {
						if ( !disable_keyup_event ) {
							$this.trigger( 'formItemChange', [$this] );
						}
					}

				}, validate_sec );

				//TO make sure the value is set to currentEditRecord when user typing it, but not trigger validate
				no_validate_timer = setTimeout( function() {
					if ( check_box ) {
						check_box.attr( 'checked', 'true' )
					}

					if ( need_parser_date || need_parser_sec ) {
						parseDateAsync( function() {

							if ( !disable_keyup_event ) {
								$this.trigger( 'formItemChange', [$this, true] );
							}

						} );
					} else {
						if ( !disable_keyup_event ) {
							$this.trigger( 'formItemChange', [$this, true] );
						}
					}

				}, 300 )

			} );

			$( this ).mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			$( this ).mouseout( function() {
				if ( !$( $this ).is( ':focus' ) ) {
					$this.hideErrorTip();
				}
			} );

			$( this ).change( function() {
				if ( disable_keyup_event ) {
					$this.trigger( 'formItemChange', [$this] );
				}
			} );

			$( this ).focusin( function() {

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

			$( this ).focusout( function() {
				$this.hideErrorTip();
			} );

		} );

		return this;

	};

	$.fn.TTextInput.defaults = {

	};

})( jQuery );