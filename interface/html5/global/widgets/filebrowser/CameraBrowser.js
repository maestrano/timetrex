(function( $ ) {

	$.fn.CameraBrowser = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.CameraBrowser.defaults, options );

		var $this = this;
		var field;

		var enabled = true;
		var video = null;
		var canvas = null;

		var local_stream = null;

		this.stopCamera = function() {

			if ( local_stream ) {
				local_stream.stop();
			}
		}

		this.showCamera = function() {

			// check for getUserMedia support
			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;
			if ( navigator.getUserMedia ) {
				// get webcam feed if available
				navigator.getUserMedia( {video: true}, function( stream ) {
					video.src = window.URL.createObjectURL( stream );
					video.play();
					local_stream = stream;
				}, errorBack );
			} else if ( navigator.webkitGetUserMedia ) { // WebKit-prefixed
				navigator.webkitGetUserMedia( {video: true}, function( stream ) {
					video.src = window.webkitURL.createObjectURL( stream );
					video.play();
					local_stream = stream;
				}, errorBack );
			} else if ( navigator.mozGetUserMedia ) { // Firefox-prefixed
				navigator.mozGetUserMedia( {video: true}, function( stream ) {
					video.src = window.URL.createObjectURL( stream );
					video.play();
					local_stream = stream;
				}, errorBack );
			} else {
				TAlertManager.showAlert( $.i18n._( 'Your browser don\'t support Camera, please use File tipe in step 1, or use Chrome or FireFox' ) );
			}

			function errorBack() {
				TAlertManager.showAlert( $.i18n._( 'No camera' ) );
			}
		}

		this.setEnable = function( val ) {
			enabled = val;

			var btn = this.children().eq( 1 );

			if ( !val ) {
				btn.attr( 'disabled', true );
				btn.removeClass( 'disable-element' ).addClass( 'disable-element' )
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
			return false;
		};

		this.getFileName = function() {
			return 'camera_stream.png'
		}

		this.getImageSrc = function() {
			return canvas[0].toDataURL();
		};

		this.setImage = function( val ) {
			var image = $this.children().eq( 0 );

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

//			var image_height = $( image ).height() > 0 ? $( image ).height() : image.naturalHeight;
//			var image_width = $( image ).width() > 0 ? $( image ).width() : image.naturalWidth;
//
//			if ( image_height > default_height ) {
//				$( image ).css( 'height', default_height );
//
//			}
//
//			if ( image_width > default_width ) {
//				$( image ).css( 'width', default_width );
//
//				$( image ).css( 'height', 'auto' );
//			}
//
//			$this.trigger( 'setSize' );

			$( image ).show();

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

			video = $( this ).children().eq( 0 ).children().eq( 0 )[0];
			canvas = $( this ).children().eq( 0 ).children().eq( 1 );

			var take_picture = $( this ).children().eq( 1 ).children().eq( 0 );
			var try_again = $( this ).children().eq( 1 ).children().eq( 1 );

			take_picture.bind( 'click', function() {
				var ctx = canvas[0].getContext( '2d' );
				ctx.drawImage( video, 0, 0, 400, 300 );
				canvas.css( 'z-index', 51 );

				$this.trigger( 'change', [$this] );

			} );

			try_again.bind( 'click', function() {
				canvas.css( 'z-index', -1 );

				$this.trigger( 'NoImageChange', [$this] );
			} );

		} );

		return this;

	};

	$.fn.CameraBrowser.defaults = {

	};

})( jQuery );