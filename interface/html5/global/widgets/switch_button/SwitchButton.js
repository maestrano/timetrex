(function( $ ) {

	$.fn.SwitchButton = function( options ) {

		Global.addCss( 'global/widgets/switch_button/SwitchButton.css' );
		var opts = $.extend( {}, $.fn.SwitchButton.defaults, options );

		var $this = this;

		var btn = null;

		var enabled = true;

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnable = function( val ) {
			enabled = val;

			if ( !val ) {
				this.removeClass( 'disable-element' ).addClass( 'disable-element' )
			} else {
				this.removeClass( 'disable-element' );
			}

		}

		this.getValue = function() {
			return btn.hasClass( 'selected' ) ? true : false;
		}

		this.setValue = function( val ) {

			//Error: TypeError: btn is null in https://ondemand1.timetrex.com/interface/html5/global/widgets/switch_button/SwitchButton.js?v=8.0.0-20141230-130626 line 35 
			if ( !btn ) {
				return;
			}

			btn.removeClass( 'selected' );

			if ( val ) {
				btn.addClass( 'selected' );
			}
		};

		this.setIcon = function( val ) {
			btn.addClass( val )
		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			btn = $( "<div></div>" );

			$this.append( btn );

			if ( o.tooltip ) {
				btn.attr( 'title', o.tooltip );
			}

			$this.setIcon( o.icon );

			btn.click( function( e ) {

				if ( !enabled ) {
					e.stopImmediatePropagation();
					e.stopPropagation();
					return
				}

				$this.setValue( !$this.getValue() );
			} );

		} );

		return this;

	};

	$.fn.SwitchButton.defaults = {};

})( jQuery );

var SwitchButtonIcon = function() {

}

SwitchButtonIcon.daily_total = 'daily';
SwitchButtonIcon.weekly_total = 'weekly';
SwitchButtonIcon.all_employee = 'all-employee';
SwitchButtonIcon.strict_range = 'strict-range';