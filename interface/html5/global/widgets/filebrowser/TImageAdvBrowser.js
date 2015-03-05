(function( $ ) {

	$.fn.TImageAdvBrowser = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImageAdvBrowser.defaults, options );

		var $this = this;
		var field;
		var name = 'filedata';

		var accept_filter = '';

		var default_width = 177;
		var default_height = 42;

		var callBack = null;

		var enabled = true;

		var image;

		var result_form_data;

		this.setEnable = function( val ) {
			enabled = val;

			var btn = this.children().eq( 1 );

			if ( !val ) {
				btn.attr( 'disabled', true );
				btn.removeClass( 'disable-element' ).addClass( 'disable-element' );
			} else {
				btn.removeAttr( 'disabled' );
				btn.removeClass( 'disable-element' );
			}

		}

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {
			return result_form_data;
		};

		this.setImage = function( val ) {
			if ( !val ) {
				image.attr( 'src', '' );
				image.hide();
				return;
			}

			var d = new Date();
			image.hide();
			image.attr( 'src', val + '&t=' + d.getTime() );
			image.css( 'height', 'auto' );
			image.css( 'width', 'auto' );

		};

		this.onImageLoad = function( image ) {

			var image_height = $( image ).height() > 0 ? $( image ).height() : image.naturalHeight;
			var image_width = $( image ).width() > 0 ? $( image ).width() : image.naturalWidth;

			if ( image_height > default_height ) {
				$( image ).css( 'height', default_height );

			}

			if ( image_width > default_width ) {
				$( image ).css( 'width', default_width );
				$( image ).css( 'height', 'auto' );
			}

			$this.trigger( 'setSize' );

			if ( image_height < 5 ) {
				$( image ).hide();
			} else {
				$( image ).show();
			}

		};

		this.setValue = function( val ) {

			if ( !val ) {
				val = '';
			}

		};

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			var $$this = this;

			$( this ).find( '#upload_image' ).text( $.i18n._( 'Upload Image' ) );

			if ( o.callBack ) {
				callBack = o.callBack;
			}

			if ( o.show_browser === false ) {
				$( this ).children().eq( 1 ).hide();
			}

			if ( o.default_width > 0 ) {
				default_width = o.default_width;
			}

			if ( o.default_height > 0 ) {
				default_height = o.default_height;
			}

			if ( Global.isSet( o.name ) ) {
				name = o.name;
			}

			if ( Global.isSet( accept_filter ) ) {
				accept_filter = o.accept_filter;
			}

			var browser = $( this ).children().eq( 1 );

			browser.bind( 'click', function() {
				IndexViewController.openWizard( 'UserPhotoWizard', null, function( form_data ) {

					if ( callBack ) {
						callBack( form_data );
					}

					result_form_data = form_data;

				} )
			} );

			image = $( this ).children().eq( 0 );
			image.load( function() {
				$this.onImageLoad( this );

			} );

		} );

		return this;

	};

	$.fn.TImageAdvBrowser.defaults = {

	};

})( jQuery );