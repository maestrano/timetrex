var TAlertManager = (function() {

	var view = null;

	var showPreSessionAlert = function() {
		var result = $( Global.loadWidget( 'global/widgets/talert/SessionAlert.html' ) );

		Global.addCss( 'global/widgets/talert/TAlert.css' );

		$( 'body' ).append( result );

		result.find( '.content' ).html( $.i18n._( 'Previous Session' ) );

		var button = result.find( '.close-icon' );

		button.bind( 'click', function() {
			removePreSession();

		} );

		result.bind( 'click', function() {
			backToPreSession();
		} );

		function removePreSession() {
			result.remove();
			result = null;

			var host = Global.getHost();

			$.cookie( 'PreviousSessionID', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'PreviousSessionIDURL', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'PreviousSessionType', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'PreviousSessionIDHOST', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'NewSessionID', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
		}

		function backToPreSession() {

			var host = Global.getHost();

			var url = $.cookie( 'PreviousSessionIDURL' );

			$.cookie( 'NewSessionID', $.cookie( 'PreviousSessionID' ), {expires: 30, path: LocalCacheData.cookie_path, domain: host} );

			$.cookie( 'PreviousSessionID', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'PreviousSessionIDURL', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );
			$.cookie( 'PreviousSessionIDHOST', null, {expires: 30, path: LocalCacheData.cookie_path, domain: host} );

			window.location = url;
			Global.needReloadBrowser = true;

			result.remove();
			result = null;

		}

	};

	var closeBrowserBanner = function() {
		$( '.browser-banner' ).remove();
	}

	var showBrowserTopBanner = function( val ) {
		var div = $( '<div class="browser-banner"><a href="http://www.timetrex.com/supported_web_browsers.php" target="_blank"><span class="label"></span></a></div>' );

		div.children().find( 'span' ).text( $.i18n._( LocalCacheData.getLoginData().application_name + ' requires a modern HTML5 standards compatible web browser, click here for more information.' ) );

		$( 'body' ).append( div );
	};

	var showErrorAlert = function( result ) {
		var details = result.getDetails();

		if ( !details ) {
			details = result.getDescription(); // If the details is empty, try to get description to show.
		}

		var error_string = '';

		if ( Global.isArray( details ) || typeof details === 'object' ) {

			$.each( details, function( index, val ) {

				for ( var key in val ) {
					error_string = error_string + val[key] + "<br>";
				}
			} );
		} else {

			error_string = details;
		}

		showAlert( error_string, 'Error' );

	};

	var showAlert = function( content, title, callBack ) {


		if ( !title ) {
			title = $.i18n._( 'Message' );
		}

		if ( view !== null ) {

			var cContent = view.find( '.content' ).text();

			if ( cContent === content ) {
				return;
			}

			remove();

			if ( callBack ) {
				callBack();
			}
		}

		Global.addCss( 'global/widgets/talert/TAlert.css' );

		var result = $( Global.loadWidget( 'global/widgets/talert/TAlert.html' ) );

		view = result;

		$( 'body' ).append( result );

		result.find( '.title' ).text( title );

		result.find( '.content' ).html( content );

		var button = result.find( '.t-button' );

		button.bind( 'click', function() {
			remove();

			if ( callBack ) {
				callBack();
			}
		} );

		button.focus();

		button.bind( 'keydown', function( e ) {

			if ( e.keyCode === 13 ) {
				remove();
				if ( callBack ) {
					callBack();
				}
			}

		} );

	};

	var showConfirmAlert = function( content, title, callBackFunction ) {

		if ( !Global.isSet( title ) ) {
			title = $.i18n._( 'Message' );
		}

		if ( view !== null ) {

			var cContent = view.find( '.content' ).text();

			if ( cContent === content ) {
				return;
			}

			remove();
		}

		Global.addCss( 'global/widgets/talert/TAlert.css' );

		var result = $( Global.loadWidget( 'global/widgets/talert/ConfirmAlert.html' ) );

		view = result;
		$( 'body' ).append( result );

		result.find( '#yesBtn' ).text( $.i18n._( 'Yes' ) );
		result.find( '#noBtn' ).text( $.i18n._( 'No' ) );
		result.find( '.title' ).text( title );

		result.find( '.content' ).text( content );

		result.find( '#yesBtn' ).bind( 'click', function() {
			remove();
			callBackFunction( true );

		} );

		result.find( '#noBtn' ).bind( 'click', function() {
			remove();
			callBackFunction( false );

		} );

	}

	var remove = function() {

		if ( view ) {
			view.remove();
			view = null;
		}

	}

	return {
		showConfirmAlert: showConfirmAlert,
		showAlert: showAlert,
		showErrorAlert: showErrorAlert,
		showPreSessionAlert: showPreSessionAlert,
		showBrowserTopBanner: showBrowserTopBanner,
		closeBrowserBanner: closeBrowserBanner
	}

})();