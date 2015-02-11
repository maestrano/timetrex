<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
* $Revision: 12172 $
* $Id: Global.js.php 12172 2014-01-29 16:52:58Z mikeb $
* $Date: 2014-01-29 08:52:58 -0800 (Wed, 29 Jan 2014) $
*/
define('TIMETREX_JSON_API', TRUE );
require_once('../../../includes/global.inc.php');
require_once('../../../includes/API.inc.php');
forceNoCacheHeaders(); //Send headers to disable caching.
header('Content-Type: application/javascript');

TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in, this is needed for getPreLoginData as well.
$auth = TTNew('APIAuthentication');

?>


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

need_load_pre_login_data = false;
var new_session = getCookie( 'NewSessionID' );
var host = window.location.hostname;
host = host.substring( (host.indexOf( '.' ) + 1) );
if ( new_session ) {
	setCookie( 'SessionID', new_session, 30, '/' );
	setCookie( 'NewSessionID', null, 0, '/', host );
	need_load_pre_login_data = true; // need load it again since APIGlobal.pre_login_data.is_logged_in will be false when first load
}

//Convert getPreLoginData() array to JS.

var APIGlobal = function() {};
APIGlobal.pre_login_data = <?php echo json_encode( $auth->getPreLoginData() );?>
<?php
Debug::writeToLog();
?>
