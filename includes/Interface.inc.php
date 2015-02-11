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

//Help prevent XSS or frame clickjacking.
Header('X-XSS-Protection: 1; mode=block');
Header('X-Frame-Options: SAMEORIGIN');

if ( !isset($disable_cache_control) ) {
	//Turn caching off.
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
	//Can Break IE with downloading PDFs over SSL.
	// IE gets: "file could not be written to cache"
	// It works on some IE installs though.
	// Comment out No-Cache and Pragma: No-Cache to fix issue.
	header('Cache-Control: no-cache'); //Adding FALSE here breaks IE.
	header('Cache-Control: post-check=0,pre-check=0');
	header('Cache-Control: max-age=0');
	header('Pragma: public');
}

//Do not overwrite a previously sent content-type header, this breaks WAP.
if ( !isset($enable_wap) ) {
	header('Content-Type: text/html; charset=UTF-8');
}

//Skip this step if disable_database_connection is enabled or the user is going through the installer still
$clf = new CompanyListFactory();
if ( ( !isset($disable_database_connection) OR ( isset($disable_database_connection) AND $disable_database_connection != TRUE ) )
		AND ( !isset($config_vars['other']['installer_enabled']) OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] != TRUE ) )) {
	//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
	try {
		$sslf = new SystemSettingListFactory();
		$system_settings = $sslf->getAllArray();
		unset($sslf);

		//Get primary company data needs to be used when user isn't logged in as well.
		$clf->getByID( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() == 1 ) {
			$primary_company = $clf->getCurrent();
		}
	} catch (Exception $e) {
		//Database not initialized, or some error, redirect to Install page.
		throw new DBError($e, 'DBInitialize');
	}
}

$permission = new Permission();

$authentication = new Authentication();
if ( isset($authenticate) AND $authenticate === FALSE ) {
	Debug::text('Bypassing Authentication', __FILE__, __LINE__, __METHOD__, 10);
	TTi18n::chooseBestLocale();
} else {
	//Increase timeout on WAP devices, so they don't have to login as often.
	if ( isset($enable_wap) AND $enable_wap == TRUE ) {
		$authentication->setIdle( 32400 ); //9hrs
	} elseif ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
		$authentication->setIdle( (int)$config_vars['other']['web_session_timeout'] );
	}

	if ( $authentication->Check() === TRUE ) {
		$profiler->startTimer( 'Interface.inc - Post-Authentication' );

		/*
		 * Get default interface data here. Things like User info, Company info etc...
		 */

		$current_user = $authentication->getObject();
		Debug::text('User Authenticated: '. $current_user->getUserName() .' Created Date: '. $authentication->getCreatedDate(), __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($primary_company) AND PRIMARY_COMPANY_ID == $current_user->getCompany() ) {
			$current_company = $primary_company;
		} else {
			$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();
		}

		//Check to make sure the logged in user's information is all up to date.
		//Make sure they also have permissions to edit information, otherwise don't redirect them.
		if ( $current_user->isInformationComplete() == FALSE
				AND ( !stristr( $_SERVER['SCRIPT_NAME'], 'permissiondenied') AND !stristr( $_SERVER['SCRIPT_NAME'], 'logout') AND !stristr( $_SERVER['SCRIPT_NAME'], 'about') AND !stristr( $_SERVER['SCRIPT_NAME'], 'punch.php') AND !stristr( $_SERVER['SCRIPT_NAME'], 'ajax_server') AND !stristr( $_SERVER['SCRIPT_NAME'], 'global.js') AND !stristr( $_SERVER['SCRIPT_NAME'], 'menu.js') AND !stristr( $_SERVER['SCRIPT_NAME'], 'embeddeddocument') AND !stristr( $_SERVER['SCRIPT_NAME'], 'send_file') AND !stristr( $_SERVER['SCRIPT_NAME'], 'upload_file') )
				AND !isset($_GET['incomplete']) AND !isset($_POST['incomplete'])
				AND ($permission->Check('user', 'enabled') AND ( $permission->Check('user', 'edit') OR $permission->Check('user', 'edit_own') OR $permission->Check('user', 'edit_child')) ) ) {
			Redirect::Page( URLBuilder::getURL( array('id' => $current_user->getID(), 'incomplete' => 1 ), Environment::GetBaseURL().'users/EditUser.php') );
		}

		$db_time_zone_error = FALSE;
		$current_user_prefs = $current_user->getUserPreferenceObject();

		//If user doesnt have any preferences set, we need to bootstrap the preference object.
		if ( $current_user_prefs->getUser() == '' ) {
			$current_user_prefs->setUser( $current_user->getId() );
		}

		if ( $current_user_prefs->setDateTimePreferences() == FALSE ) {
			//Setting timezone failed, alert user to this fact.
			$db_time_zone_error = TRUE;
		}

		/*
		 *	Check locale cookie, if it varies from UserPreference Language,
		 *	change user preferences to match. This could cause some unexpected behavior
		 *  as the change is happening behind the scenes, but if we don't change
		 *  the user prefs then they could login for weeks/months as a different
		 *  language from their preferences, therefore making the user preference
		 *  setting almost useless. Causing issues when printing pay stubs and in each
		 *  users language.
		 */
		Debug::text('Locale Cookie: '. TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $current_user_prefs->isNew() == FALSE AND TTi18n::getLocaleCookie() != '' AND $current_user_prefs->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
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

		if ( $current_user->isInformationComplete() == TRUE
				AND $current_user_prefs->isPreferencesComplete() == FALSE
				AND ( !stristr( $_SERVER['SCRIPT_NAME'], 'permissiondenied') AND !stristr( $_SERVER['SCRIPT_NAME'], 'logout') AND !stristr( $_SERVER['SCRIPT_NAME'], 'about') AND !stristr( $_SERVER['SCRIPT_NAME'], 'punch.php') AND !stristr( $_SERVER['SCRIPT_NAME'], 'ajax_server') AND !stristr( $_SERVER['SCRIPT_NAME'], 'global.js') AND !stristr( $_SERVER['SCRIPT_NAME'], 'menu.js') )
				AND !isset($_GET['incomplete']) AND !isset($_POST['incomplete'])
				AND ($permission->Check('user_preference', 'enabled') AND ( $permission->Check('user_preference', 'edit') OR $permission->Check('user_preference', 'edit_child') OR $permission->Check('user_preference', 'edit_own') ) ) ) {
			Redirect::Page( URLBuilder::getURL( array('incomplete' => 1 ), Environment::GetBaseURL().'users/EditUserPreference.php') );
		}

		//Handle station functionality
		if ( isset( $_COOKIE['StationID'] ) ) {
			Debug::text('Station ID Cookie found! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);

			$slf = new StationListFactory();
			$slf->getByStationIdandCompanyId( $_COOKIE['StationID'], $current_company->getId() );
			$current_station = $slf->getCurrent();
			unset($slf);
			if ( $current_station->isNew() ) {
				Debug::text('Station ID is NOT IN DB!! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('No Station cookie defined... User ID: '. $current_user->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$current_station = NULL; //No station cookie defined, make sure we at least initialize the variable.
		}
		//Debug::Arr($current_station, 'Current Station Object: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Current Company: '. $current_company->getName(), __FILE__, __LINE__, __METHOD__, 10);

		//Make sure CronJobs are running correctly.
		$cjlf = new CronJobListFactory();
		$cjlf->getMostRecentlyRun();
		if ( $cjlf->getRecordCount() > 0 ) {
			//Is last run job more then 48hrs old?
			$cj_obj = $cjlf->getCurrent();

			if ( PRODUCTION == TRUE
					AND DEMO_MODE == FALSE
					AND $cj_obj->getLastRunDate() < ( time() - 172800 )
					AND $cj_obj->getCreatedDate() < ( time() - 172800 ) ) {
				$cron_out_of_date = 1;
			} else {
				$cron_out_of_date = 0;
			}
		}
		unset($cjlf, $cj_obj);

		$profiler->stopTimer( 'Interface.inc - Post-Authentication' );
	} else {
		Debug::text('User NOT Authenticated!', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($enable_wap) AND $enable_wap == TRUE ) {
			Redirect::Page( URLBuilder::getURL(NULL, Environment::GetBaseURL().'wap/wap_login.php') );
		} elseif ( isset($enable_iphone) AND $enable_iphone == TRUE ) {
			Redirect::Page( URLBuilder::getURL(NULL, Environment::GetBaseURL().'iphone/login/login.php') );
		} else {
			if ( isset($config_vars['other']['default_interface']) AND strtolower(trim($config_vars['other']['default_interface'])) == 'html' ) {
				Redirect::Page( URLBuilder::getURL(NULL, Environment::GetBaseURL().'Login_legacy.php') );
			} elseif ( isset($config_vars['other']['default_interface']) AND strtolower(trim($config_vars['other']['default_interface'])) == 'html5' ) {
				Redirect::Page( URLBuilder::getURL(NULL, Environment::GetBaseURL().'html5/') );
			} else {
				Redirect::Page( URLBuilder::getURL(NULL, Environment::GetBaseURL().'flex/') );
			}
		}

		//exit;
	}
}
unset($clf);

require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'smarty'. DIRECTORY_SEPARATOR .'libs'. DIRECTORY_SEPARATOR .'Smarty.class.php');

$smarty = new Smarty;
$smarty->compile_check = TRUE;
$smarty->template_dir = Environment::getTemplateDir();
$smarty->compile_dir = Environment::getTemplateCompileDir();

$smarty->assign('css_file', 'global.css.php' );
$smarty->assign('IMAGES_URL', Environment::getImagesURL() );
$smarty->assign('BASE_PATH', Environment::getBasePath() );

$smarty->assign('APPLICATION_NAME', APPLICATION_NAME );
$smarty->assign('ORGANIZATION_NAME', ORGANIZATION_NAME );
$smarty->assign('ORGANIZATION_URL', ORGANIZATION_URL );
$smarty->assign('APPLICATION_VERSION', APPLICATION_VERSION );
$smarty->assign('DEPLOYMENT_ON_DEMAND', DEPLOYMENT_ON_DEMAND );

if ( isset($cron_out_of_date) ) {
	$smarty->assign('CRON_OUT_OF_DATE', $cron_out_of_date );
}

if ( isset($db_time_zone_error) ) {
	$smarty->assign('DB_TIME_ZONE_ERROR', $db_time_zone_error );
}

if ( isset($config_vars['other']['installer_enabled']) ) {
	$smarty->assign('INSTALLER_ENABLED', $config_vars['other']['installer_enabled'] );
}

if ( isset($system_settings['valid_install_requirements']) AND DEPLOYMENT_ON_DEMAND == FALSE AND (int)$system_settings['valid_install_requirements'] == 0 ) {
	$smarty->assign('VALID_INSTALL_REQUIREMENTS', TRUE );
}

if ( isset($system_settings['system_version']) AND DEPLOYMENT_ON_DEMAND == FALSE AND APPLICATION_VERSION != $system_settings['system_version'] ) {
	$smarty->assign('VERSION_MISMATCH', TRUE );
}

if ( isset($system_settings['tax_data_version']) AND ( time() - strtotime($system_settings['tax_data_version']) ) > (86400 * 475) ) { //~1yr and 3mths
	$smarty->assign('VERSION_OUT_OF_DATE', TRUE );
}

if ( isset($system_settings) ) {
	$smarty->assign_by_ref('system_settings', $system_settings );
}

if ( isset($current_company) ) {
	$smarty->assign_by_ref('current_company', $current_company );
}
if ( isset($primary_company) ) {
	$smarty->assign_by_ref('primary_company', $primary_company );
}

if ( isset($config_vars) ) {
	$smarty->assign_by_ref('config_vars', $config_vars );
}

if ( TTi18n::getLanguage() != '' ) {
	$smarty->assign('CALENDAR_LANG', TTi18n::getLanguage() );
}else{
	$smarty->assign('CALENDAR_LANG', 'en');
}
$smarty->assign('MOBILE_BROWSER', Misc::detectMobileBrowser() );

if ( isset($current_user) )  {
	$smarty->assign_by_ref('current_user', $current_user );
	$smarty->assign_by_ref('current_user_prefs', $current_user_prefs );

	if ( !isset($skip_message_check) ) {
		$profiler->startTimer( 'Interface.inc - Check for UNREAD messages...');

		//CHeck for unread messages
		/*
		$mlf = new MessageListFactory();
		$unread_messages = $mlf->getNewMessagesByUserId( $current_user->getId() );
		*/
		$mclf = new MessageControlListFactory();
		$unread_messages = $mclf->getNewMessagesByCompanyIdAndUserId( $current_user->getCompany(), $current_user->getId() );
		Debug::text('UnRead Messages: '. $unread_messages, __FILE__, __LINE__, __METHOD__, 10);
		$smarty->assign_by_ref('unread_messages', $unread_messages );
		if ( isset($_COOKIE['newMailPopUp']) ) {
			$smarty->assign_by_ref('newMailPopUp', $_COOKIE['newMailPopUp'] );
		}
		unset($mclf);
		$profiler->stopTimer( 'Interface.inc - Check for UNREAD messages...');

		$profiler->startTimer( 'Interface.inc - Check for Exceptions');

		$elf = new ExceptionListFactory();
		$elf->getFlaggedExceptionsByUserIdAndPayPeriodStatus( $current_user->getId(), 10 );
		$display_exception_flag = FALSE;
		if ( $elf->getRecordCount() > 0 ) {
			foreach($elf as $e_obj) {
				if ( $e_obj->getColumn('severity_id') == 30 ) {
					$display_exception_flag = 'red';
				} elseif ( $e_obj->getColumn('severity_id') == 20 ) {
					$display_exception_flag = 'yellow';
				}
				break;
			}
		}
		unset($elf, $e_obj);

		if ( isset($display_exception_flag) ) {
			Debug::text('Exception Flag to Display: '. $display_exception_flag, __FILE__, __LINE__, __METHOD__, 10);
			$smarty->assign_by_ref('display_exception_flag', $display_exception_flag );
			//Make sure we leave this variable around for the menu.js.php.
		}

		$profiler->stopTimer( 'Interface.inc - Check for Exceptions');
	}
}
if ( isset($current_station) ) {
	$smarty->assign_by_ref('current_station', $current_station );
}

$smarty->assign('BASE_URL', Environment::getBaseURL() );
$smarty->assign('profiler', $profiler );
$smarty->assign_by_ref('permission', $permission );

$profiler->startTimer( 'Main' );
?>