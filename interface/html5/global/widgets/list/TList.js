(function( $ ) {

	$.fn.TList = function( options ) {
		var opts = $.extend( {}, $.fn.TList.defaults, options );
		var $this = this;
		var field;
		var source_data = null;

		var select_value = null;

		var set_empty = false;

		var set_any = false;

		var set_select_item_when_set_source_data = false;

		var error_string = '';
		var error_tip_box;

		var mass_edit_mode = false;

		var check_box = null;

		var enabled = true;

		this.getEnabled = function() {
			return enabled;
		}

		this.setEnabled = function( val ) {
			enabled = val;

			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
				$this.addClass( 't-select-readonly' );
			} else {
				$this.removeAttr( 'disabled' );
				$this.removeClass( 't-select-readonly' );
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

		this.getValue = function() {

			var result = [];
			var select_items = $( this ).children( 'option:selected' );
			var len = select_items.length;
			if ( len > 0 ) {
				for ( var i = 0; i < len; i++ ) {
					var item = select_items.eq( i )
					result.push( item.attr( 'value' ) )
				}
			}

			return	result;
		}

		this.setValue = function( val ) {

			select_value = val;

			if ( !source_data || (set_empty && source_data.length === 1) || (set_any && source_data.length === 1) ) {
				set_select_item_when_set_source_data = true;
				return;
			}

			if ( !val ) {
				if ( set_empty ) {
					val = '0';
				} else if ( set_any ) {
					val = '-1';
				}
			}

			$( $( this ).find( 'option' ) ).removeAttr( 'selected' );

			$( $( this ).find( 'option' ) ).filter(function() {

				if ( val === null || val === undefined ) {
					return false
				}

				var result = false;
				if ( $.type( val ) === 'array' ) {
					for ( var i = 0; i < val.length; i++ ) {
						var item = val[i];

						if ( item.toString() === $( this ).attr( 'value' ) ) {
							result = true;
							break;
						}
					}
				} else {
					result = !!($( this ).attr( 'value' ) === val.toString());
				}

				return	result;

			} ).prop( 'selected', true ).attr( 'selected', true );

		};

		this.setSourceData = function( val ) {

			$( this ).empty();

			if ( !Global.isSet( val ) || val.length < 1 ) {
				if ( set_empty ) {
					val = Global.addFirstItemToArray( val, 'empty' )
				} else if ( set_any ) {
					val = Global.addFirstItemToArray( val, 'any' );
				}
			} else {
				if ( set_empty ) {

					if ( val[0].value !== '0' ) {
						val = Global.addFirstItemToArray( val, 'empty' )
					}

				} else if ( set_any ) {
					if ( val[0].value !== '-1' ) {
						val = Global.addFirstItemToArray( val, 'any' );
					}

				}
			}

			source_data = val;

			var len = val.length;
			for ( var i = 0; i < len; i++ ) {
				var item = val[i]
				$( this ).append( '<option value="' + item.value + '">' + item.label + '</option>' )
			}

			if ( set_select_item_when_set_source_data ) {
				this.setValue( select_value );
			}

		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.set_empty ) {
				set_empty = o.set_empty;
			}

			if ( o.set_any ) {
				set_any = o.set_any;
			}

			if ( o.mass_edit_mode ) {
				mass_edit_mode = o.mass_edit_mode;
			}

			field = o.field;

			$( this ).change( function() {

				if ( !enabled ) {
					return;
				}

				if ( check_box ) {
					check_box.attr( 'checked', 'true' )
				}

				$this.trigger( 'formItemChange', [$this] );
			} )

			$( this ).click( function() {
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
				}
			} );

			$( this ).mouseover( function() {

				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 20 );
					}
				}

			} );

			$( this ).mouseout( function() {
				$this.hideErrorTip();
			} );

		} );

		return this;

	};

	$.fn.TList.defaults = {

	};

})( jQuery );