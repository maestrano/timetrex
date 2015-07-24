(function( $ ) {

	$.fn.TCheckbox = function( options ) {
		var opts = $.extend( {}, $.fn.TCheckbox.defaults, options );

		var $this = this;
		var field;

		var error_string = '';
		var error_tip_box;

		var mass_edit_mode = false;
		var check_box = null;

		var enabled = true;

		this.clearErrorStyle = function() {

		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.attr( 'disabled', 'true' );
			} else {
				$this.removeAttr( 'disabled' );
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

		this.setErrorStyle = function( errStr, show ) {
			$( this ).addClass( 'ck-error-tip' );

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
			$( this ).removeClass( 'ck-error-tip' );
			error_string = '';
		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {

			if ( this.attr( 'checked' ) || this[0].checked === true ) {
				return true;
			}
			return false;
		};

		this.setValue = function( val ) {

			if ( val === true ) {
				this.attr( 'checked', 'checked' );
				this[0].checked = true;
			} else {
				this.removeAttr( 'checked' );
				this[0].checked = false;
			}

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			$( this ).change( function() {
				if ( check_box ) {
					check_box.attr( 'checked', 'true' );
				}

				if ( $this.attr( 'checked' ) === 'checked' ) {
					$this.removeAttr( 'checked' );
					$this[0].checked = false;
				} else {
					$this.attr( 'checked', 'checked' );
					$this[0].checked = true;
				}

				$this.trigger( 'formItemChange', [$this] );
			} );

		} );

		return this;

	};

	$.fn.TCheckbox.defaults = {

	};

})( jQuery );