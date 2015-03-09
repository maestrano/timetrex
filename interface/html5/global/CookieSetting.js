function getCookie( cname ) {
	var name = cname + "=";
	var ca = document.cookie.split( ';' );
	for ( var i = 0; i < ca.length; i++ ) {
		var c = ca[i];
		while ( c.charAt( 0 ) == ' ' ) c = c.substring( 1 );
		if ( c.indexOf( name ) != -1 ) return c.substring( name.length, c.length );
	}
	return "";
}

function setCookie( cname, cvalue, exdays, path, domain ) {
	var d = new Date();
	d.setTime( d.getTime() + (exdays * 24 * 60 * 60 * 1000) );
	var expires = "expires=" + d.toGMTString();

	if ( domain ) {
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + path + "; domain=" + domain;
	} else {
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + path;
	}

}

var new_session = getCookie( 'NewSessionID' );
var host = window.location.hostname;
host = host.substring( (host.indexOf( '.' ) + 1) );
if ( new_session ) {
	setCookie( 'SessionID', new_session, 30, '/' );
	setCookie( 'NewSessionID', null, 0, '/', host );
}
