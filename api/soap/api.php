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

define('TIMETREX_SOAP_API', TRUE );

//Add timetrex.ini.php setting to enable/disable the API. Make an entire [API] section.
require_once('../../includes/global.inc.php');
require_once('../../includes/API.inc.php');
Header('Content-Type: application/xml; charset=utf-8');

$class_prefix = 'API';
$class_name = FALSE;

//Class name is case sensitive!
//Get proper class name early, as we need to allow
if ( isset($_GET['Class']) AND $_GET['Class'] != '' ) {
	$class_name = $_GET['Class'];

	//If API wasn't already put on the class, add it manually.
	if ( strtolower( substr( $class_name, 0, 3 ) ) != 'api' ) {
		$class_name = $class_prefix.$class_name;
	}

	$class_name = TTgetPluginClassName( $class_name );
} else {
	$class_name = TTgetPluginClassName( $class_prefix.'Authentication' );
}

//$class_factory = ( isset($_GET['Class']) AND $_GET['Class'] != '' ) ? $_GET['Class'] : 'Authentication'; //Default to APIAuthentication class if none is specified.
//$class_name = TTgetPluginClassName( $class_prefix.$class_factory );

$soap_server = new SoapServer( NULL, array('uri' => "urn:api") );
if ( isset($_GET['SessionID']) AND $_GET['SessionID'] != '' ) {
	$authentication = new Authentication();

	Debug::text('SOAP Session ID: '. $_GET['SessionID'] .' Source IP: '. Misc::getRemoteIPAddress(), __FILE__, __LINE__, __METHOD__, 10);
	if ( $authentication->Check( $_GET['SessionID'] ) === TRUE ) {
		Debug::text('SOAP Class Factory: '. $class_name, __FILE__, __LINE__, __METHOD__, 10);
		if ( $class_name != '' AND class_exists($class_name) ) {
			$current_user = $authentication->getObject();

			if ( is_object( $current_user ) ) {
				$current_user->getUserPreferenceObject()->setDateTimePreferences();
				$current_user_prefs = $current_user->getUserPreferenceObject();

				Debug::text('Locale Cookie: '. TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 10);
				if ( TTi18n::getLocaleCookie() != '' AND $current_user_prefs->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
					Debug::text('Changing User Preference Language to match cookie...', __FILE__, __LINE__, __METHOD__, 10);
					$current_user_prefs->setLanguage( TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) );
					if ( $current_user_prefs->isValid() ) {
						$current_user_prefs->Save(FALSE);
					}
				} else {
					Debug::text('User Preference Language matches cookie!', __FILE__, __LINE__, __METHOD__, 10);
				}
				if ( isset($_GET['language']) AND $_GET['language'] != '' ) {
					TTi18n::setLocale( $_GET['language'] ); //Sets master locale
				} else {
					TTi18n::setLanguage( $current_user_prefs->getLanguage() );
					TTi18n::setCountry( $current_user->getCountry() );
					TTi18n::setLocale(); //Sets master locale
				}

				$clf = new CompanyListFactory();
				$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();

				if ( is_object( $current_company ) ) {
					Debug::text('Handling SOAP Call To API Factory: '.  $class_name .' UserName: '. $current_user->getUserName(), __FILE__, __LINE__, __METHOD__, 10);
					$soap_server->setClass( $class_name );
					//$soap_server->setPersistence( SOAP_PERSISTENCE_SESSION );
					$soap_server->handle();
					//var_dump( $_SESSION );
				} else {
					Debug::text('Failed to get Company Object!', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('Failed to get User Object!', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('Class Factory does not exist!', __FILE__, __LINE__, __METHOD__, 10);
			$soap_server->fault( 9800, 'Class Factory ('. $class_name .') does not exist!');
		}
	} else {
		TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in
	
		Debug::text('User not authenticated! Session likely timed out.', __FILE__, __LINE__, __METHOD__, 10);
		//$soap_server->fault( 9900, 'Session timed out, please login again.');
		$soap_server->setClass('APIAuthentication'); //Allow checking isLoggedIn() and logging in again here.
		$soap_server->handle(); //PHP appears to exit in this function if there is an error.
	}
} else {
	TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in

	Debug::text('SOAP UnAuthenticated!', __FILE__, __LINE__, __METHOD__, 10);
	$valid_unauthenticated_classes = getUnauthenticatedAPIClasses();
	if ( $class_name != '' AND in_array( $class_name, $valid_unauthenticated_classes ) AND class_exists( $class_name ) ) {
		$soap_server->setClass( $class_name );
		$soap_server->handle(); //PHP appears to exit in this function if there is an error.
	} else {
		Debug::text('Class: '. $class_name .' does not exist! (unauth)', __FILE__, __LINE__, __METHOD__, 10);
	}
}

Debug::text('Server Response Time: '. ((float)microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT']), __FILE__, __LINE__, __METHOD__, 10);
//Debug::Display();
Debug::writeToLog();
?>