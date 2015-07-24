(function( $ ) {

	$.fn.TPasswordInput = function( options ) {
		var opts = $.extend( {}, $.fn.TPasswordInput.defaults, options );
		var $this = this;
		var field;
		var error_string = '';
		var error_tip_box;

		var mass_edit_mode = false;
		var check_box = null;

		var enabled = true;

		var hasKeyEvent = null;

		var validate_timer = null;

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'readonly', 'true' );
				$this.addClass( 't-text-input-readonly' );
			} else {
				$this.removeAttr( 'readonly' );
				$this.removeClass( 't-text-input-readonly' );
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
			check_box.attr( 'checked', val );
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
					$this.trigger( 'formItemChange', [$this] );
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {

			var val = $this.val();
			return val;

		};

		this.setValue = function( val ) {

			if ( !val && val !== 0 ) {
				val = '';
			}

			$this.val( val );

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
				sec = 2;
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox();
			}
			error_tip_box.cancelRemove();
			error_tip_box.show( this, error_string, sec );
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

			if ( o.width > 0 ) {
				$this.width( o.width );
			}

			$( this ).keydown( function( e ) {

				if ( hasKeyEvent ) {

					$this.trigger( 'formItemKeyDown', [$this] );
				}

				//don't clean event when click tab
				if ( e.keyCode !== 9 && validate_timer ) {
					clearTimeout( validate_timer );
					validate_timer = null;
				}

			} );

			$( this ).keyup( function() {

				if ( hasKeyEvent ) {
					$this.trigger( 'formItemKeyUp', [$this] );
				}

				validate_timer = setTimeout( function() {

					if ( check_box ) {
						check_box.attr( 'checked', 'true' );
					}

					$this.trigger( 'formItemChange', [$this] );

				}, 500 );

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

	$.fn.TPasswordInput.defaults = {

	};

})( jQuery );