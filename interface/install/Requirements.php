<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
 * $Revision: 9372 $
 * $Id: Requirements.php 9372 2013-03-22 21:51:39Z ipso $
 * $Date: 2013-03-22 14:51:39 -0700 (Fri, 22 Mar 2013) $
 */
@ini_set('display_errors', true);

$disable_database_connection=TRUE;
require_once('../../includes/global.inc.php');

$authenticate=FALSE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = '2. System Check Acceptance')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'data',
												'external_installer',
												) ) );

$install_obj = new Install();
if ( DEPLOYMENT_ON_DEMAND == FALSE ) {
	$install_obj->cleanCacheDirectory();
}
if ( $install_obj->isInstallMode() == FALSE ) {
	Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
}

$action = Misc::findSubmitButton();
switch ($action) {
	case 'phpinfo':
		phpinfo();
		exit;
		break;
	case 'back':
		Debug::Text('Back', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
		break;
	case 'next':
		Debug::Text('Next', __FILE__, __LINE__, __METHOD__,10);
		if ( $external_installer == 1 ) {
			Redirect::Page( URLBuilder::getURL( array('external_installer' => $external_installer ), 'DatabaseSchema.php') );
		} else {
			Redirect::Page( URLBuilder::getURL(NULL, 'DatabaseConfig.php') );
		}
		break;
	default:
		break;
}

$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array_merge( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'require'), $install_obj->getFailedRequirements( FALSE, array('clean_cache', 'file_permissions','file_checksums') ) ), 'pre_install.php'), "r");
@fclose($handle);

$check_all_requirements = $install_obj->checkAllRequirements();
if ( $external_installer == 1 AND $check_all_requirements == 0 AND $install_obj->checkTimeTrexVersion() == 0 ) {
	//Using external installer and there is no missing requirements, automatically send to next page.
	Redirect::Page( URLBuilder::getURL( array('external_installer' => $external_installer, 'action:next' => 'next' ), $_SERVER['SCRIPT_NAME']) );
}

$smarty->assign_by_ref('install_obj', $install_obj);
$smarty->assign_by_ref('external_installer', $external_installer);
$smarty->display('install/Requirements.tpl');
?>