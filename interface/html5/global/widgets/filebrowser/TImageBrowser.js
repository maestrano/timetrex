(function( $ ) {

	$.fn.TImageBrowser = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImageBrowser.defaults, options );

		var $this = this;
		var field;
		var name = 'filedata';
		var browser;

		var accept_filter = '';

		var default_width = 177;
		var default_height = 42;
		var enabled = true;

		this.setEnable = function( val ) {
			enabled = val;

			var btn = this.find( '.browser-form' );

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

		this.getFileName = function() {

			return browser.val();
		}

		this.getField = function() {
			return field;
		};

		this.getValue = function() {

			var form_data;

			if ( browser.val() ) {

				if ( typeof FormData == "undefined" ) {
					form_data = $this.find( '.browser-form' );
				} else {
					form_data = new FormData( $( $this.find( '.browser-form' ) )[0] );
				}

			} else {

				form_data = null;
			}

			return form_data;
		};

		this.getImageSrc = function() {
			var image = $this.find( '.image' );
			return image.attr( 'src' );
		};

		this.setImage = function( val ) {
			var image = $this.find( '.image' );

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

		onImageLoad = function( image ) {

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

			browser = $( this ).find( '.browser' );
			var image = $( this ).find( '.image' );
			image.load( function() {
				onImageLoad( this );

			} );

			if ( accept_filter ) {
				browser.attr( 'accept', accept_filter );
			} else {
				browser.attr( 'accept', 'image/*' );
			}

			browser.attr( 'name', name );

			if ( Global.isSet( o.changeHandler ) ) {

				$this.bind( 'imageChange', o.changeHandler );
			}

			browser.bind( 'change', function() {
				image.hide();

				if ( typeof FileReader != "undefined" ) {

					var files = !!this.files ? this.files : [];

					// If no files were selected, or no FileReader support, return
					if ( !files.length || !window.FileReader ) return;

					// Create a new instance of the FileReader
					var reader = new FileReader();

					// Read the local file as a DataURL
					reader.readAsDataURL( files[0] );

					// When loaded, set image data as background of div
					reader.onloadend = function() {
						var url = this.result;
						image.attr( 'src', url )

					}
				}

				$this.trigger( 'imageChange', [$this] );

			} )

		} );

		return this;

	};

	$.fn.TImageBrowser.defaults = {

	};

})( jQuery );