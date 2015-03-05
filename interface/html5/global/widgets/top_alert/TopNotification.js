(function( $ ) {

	$.fn.TopNotification = function( options ) {
		var opts = $.extend( {}, $.fn.TopNotification.defaults, options );
		var data;
		var $this = this;
		var el;
		var row_box;
		var bottom_button;
		var collapse = false;

		var addRow = function() {

		};

		var setBarWidth = function() {
			var rows = el.children().eq( 0 ).children();
			var len = rows.length;
			var biggest_width = 0;

			for ( var i = 0; i < len; i++ ) {
				var row = rows.eq( i );
				var span = row.children().eq( 0 );
				var is_invisible = false;
				if ( !span.is( ':visible' ) ) {
					span.parent().show();
					is_invisible = true;
				}
				if ( span.width() > biggest_width ) {

					biggest_width = span.width();
				}

				if ( is_invisible ) {
					span.parent().hide();
				}

			}

			el.width( biggest_width + 25 );
		}

		var removeRowOnTime = function( row_tpl, delay ) {
			setTimeout( function() {
				removeRow( row_tpl );
				setExpendButton();
			}, (delay * 1000) );
		}

		var setCloseButton = function( row_tpl ) {

			row_tpl.children().eq( 1 ).unbind( 'click' ).bind( 'click', function( e ) {
				removeRow( row_tpl );
				setExpendButton();
			} );
		}

		var removeRow = function( row ) {

			row.remove();
			var rows = el.children().eq( 0 ).children();
			if ( collapse ) {

				if ( rows.length > 0 ) {
					rows.eq( 0 ).show();
				}

			}

			if ( rows.length < 1 ) {
				$this.remove();
			}

			setBarWidth();
		}

		var setDestination = function( row_tpl, item ) {

			row_tpl.css( 'cursor', 'pointer' );
			row_tpl.unbind( 'click' ).bind( 'click', function() {

				if ( item.destination ) {

					if ( (typeof item.destination["indexOf"]) !== 'undefined' && item.destination.indexOf( 'http://' ) > -1 ) {
						window.open( item.destination, '_blank' );

					} else if ( item.destination.hasOwnProperty( 'menu_name' ) ) {
						IndexViewController.goToViewByViewLabel( item.destination.menu_name );
					}
				} else {
					removeRow( row_tpl );
				}

				removeRow( row_tpl );
				IndexViewController.setNotificationBar( 'notification' );

			} );

		}

		var setExpendButton = function() {
			if ( row_box.children().length > 1 ) {
				bottom_button.show();
				bottom_button.parent().height( 15 );
			} else {
				bottom_button.hide();
				bottom_button.parent().height( 0 );
			}

			bottom_button.unbind( 'click' ).bind( 'click', function() {

				if ( bottom_button.parent().find( '.down-btn' ).length > 0 ) {
					bottom_button.attr( 'class', 'bottom-btn up-btn' );
					hideOrShowRows( false );

				} else {
					bottom_button.attr( 'class', 'bottom-btn down-btn' );
					hideOrShowRows( true );
				}
			} );

			function hideOrShowRows( hide ) {
				var len = row_box.children().length;
				collapse = hide;
				for ( var i = 1; i < len; i++ ) {
					if ( hide ) {
						row_box.children().eq( i ).hide();
					} else {
						row_box.children().eq( i ).show();
					}

				}

			}
		}

		this.show = function( arg ) {

			this.remove();

			data = arg;

			//Error: TypeError: undefined is not an object (evaluating 'el.width') in https://ondemand2001.timetrex.com/interface/html5/global/widgets/top_alert/TopNotification.js?v=7.4.6-20141027-085016 line 143
			if ( !el ) {
				return;
			}

			el.width( '100%' );
			bottom_button.attr( 'class', 'bottom-btn up-btn' );

			if ( data && data.length > 0 ) {
				for ( var i = 0; i < data.length; i++ ) {
					var item = data[i];
					var row_tpl = $( Global.loadWidget( 'global/widgets/top_alert/NotificationRow.html' ) );

					row_tpl.children().eq( 0 ).text( item.message );
					row_tpl.css( "backgroundColor", item.bg_color );

					setCloseButton( row_tpl );

					if ( item.delay === -1 ) {
						row_tpl.children().eq( 1 ).hide();
					} else if ( item.delay > 0 ) {
						removeRowOnTime( row_tpl, item.delay );
					}

					if ( item.destination || item.delay != -1 ) {
						setDestination( row_tpl, item );

					}

					el.children().eq( 0 ).append( row_tpl );
				}

				$( 'body' ).append( el );
				setExpendButton();

				setBarWidth();
			}

		};

		//Error: Unable to get property 'remove' of undefined or null reference in https://ondemand2001.timetrex.com/interface/html5/global/widgets/top_alert/TopNotification.js?v=7.4.6-20141027-132733 line 179
		this.remove = function() {
			if ( el ) {
				el.remove();
				el.children().eq( 0 ).empty();
			}

		}

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			el = $( this );

			row_box = el.children().eq( 0 );
			bottom_button = el.find( '.bottom-btn' );

		} );

		return this;

	};

	$.fn.TopNotification.defaults = {

	};

})( jQuery );