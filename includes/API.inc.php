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

define('TIMETREX_API', TRUE );
forceNoCacheHeaders(); //Send headers to disable caching.

//Returns valid classes when unauthenticated.
function getUnauthenticatedAPIClasses() {
	return array('APIAuthentication','APIClientStationUnAuthenticated', 'APIAuthenticationPlugin', 'APIClientStationUnAuthenticatedPlugin', 'APIProgressBar', 'APIInstall');
}

//Returns session ID from _COOKIE, _POST, then _GET.
function getSessionID() {
	if ( isset($_COOKIE['SessionID']) AND $_COOKIE['SessionID'] != '' ) {
		$session_id = $_COOKIE['SessionID'];
	} elseif ( isset($_POST['SessionID']) AND $_POST['SessionID'] != '' ) {
		$session_id = $_POST['SessionID'];
	} elseif ( isset($_GET['SessionID']) AND $_GET['SessionID'] != '' ) {
		$session_id = $_GET['SessionID'];
	} else {
		$session_id = FALSE;
	}

	return $session_id;
}

//Returns Station ID from _COOKIE, _POST, then _GET.
function getStationID() {
	if ( isset($_COOKIE['StationID']) AND $_COOKIE['StationID'] != '' ) {
		$station_id = $_COOKIE['StationID'];
	} elseif ( isset($_POST['StationID']) AND $_POST['StationID'] != '' ) {
		$station_id = $_POST['StationID'];
	} elseif ( isset($_GET['StationID']) AND $_GET['StationID'] != '' ) {
		$station_id = $_GET['StationID'];
	} else {
		$station_id = FALSE;
	}

	return $station_id;
}

//Make sure cron job information is always logged.
//Don't do this until log rotation is implemented.
/*
Debug::setEnable( TRUE );
Debug::setBufferOutput( TRUE );
Debug::setEnableLog( TRUE );
if ( Debug::getVerbosity() <= 1 ) {
	Debug::setVerbosity( 1 );
}
*/
?>