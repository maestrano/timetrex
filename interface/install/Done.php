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

require_once('../../includes/global.inc.php');

$authenticate = FALSE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$authentication->Logout(); //Logout during the install process.

//Debug::setVerbosity(11);

$smarty->assign('title', TTi18n::gettext($title = 'Done!')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'data',
												'upgrade',
												) ) );

$install_obj = new Install();
if ( $install_obj->isInstallMode() == FALSE ) {
	Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
	exit;
}

//Disable installer now that we're done.
$tmp_config_data['other']['installer_enabled'] = 'FALSE';
$tmp_config_data['other']['default_interface'] = 'html5';
$install_obj->writeConfigFile( $tmp_config_data );

//Reset new_version flag.
$sslf = TTnew( 'SystemSettingListFactory' );
$sslf->getByName('new_version');
if ( $sslf->getRecordCount() == 1 ) {
	$obj = $sslf->getCurrent();
} else {
	$obj = TTnew( 'SystemSettingListFactory' );
}
$obj->setName( 'new_version' );
$obj->setValue( 0 );
if ( $obj->isValid() ) {
	$obj->Save();
}

//Reset system requirement flag, as all requirements should have passed.
$sslf = new SystemSettingListFactory();
$sslf->getByName('valid_install_requirements');
if ( $sslf->getRecordCount() == 1 ) {
	$obj = $sslf->getCurrent();
} else {
	$obj = new SystemSettingListFactory();
}
$obj->setName( 'valid_install_requirements' );
$obj->setValue( 1 );
if ( $obj->isValid() ) {
	$obj->Save();
}

//Reset auto_upgrade_failed flag, as they likely just upgraded to the latest version.
$sslf = new SystemSettingListFactory();
$sslf->getByName('auto_upgrade_failed');
if ( $sslf->getRecordCount() == 1 ) {
	$obj = $sslf->getCurrent();
} else {
	$obj = new SystemSettingListFactory();
}
$obj->setName( 'auto_upgrade_failed' );
$obj->setValue( 0 );
if ( $obj->isValid() ) {
	$obj->Save();
}

$action = Misc::findSubmitButton();
switch ($action) {
	case 'back':
		Debug::Text('Back', __FILE__, __LINE__, __METHOD__, 10);

		Redirect::Page( URLBuilder::getURL(NULL, 'User.php') );
		break;

	case 'next':
		Debug::Text('Next', __FILE__, __LINE__, __METHOD__, 10);

		//Redirect::Page( URLBuilder::getURL(NULL, '../Login.php') );
		Redirect::Page( URLBuilder::getURL(NULL, '/') );
		break;
	default:
		break;
}

$cache->clean(); //Clear all cache.
$install_obj->cleanOrphanFiles();

$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'done'), 'pre_install.php'), "r");
@fclose($handle);

$smarty->assign_by_ref('upgrade', $upgrade);
$smarty->assign_by_ref('install_obj', $install_obj);
$smarty->display('install/Done.tpl');
?>