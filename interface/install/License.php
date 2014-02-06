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
 * $Revision: 9761 $
 * $Id: License.php 9761 2013-05-03 23:06:47Z ipso $
 * $Date: 2013-05-03 16:06:47 -0700 (Fri, 03 May 2013) $
 */
$disable_database_connection=TRUE;
require_once('../../includes/global.inc.php');

$authenticate=FALSE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = '1. License Acceptance')); // See index.php

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

$action = Misc::findSubmitButton();
switch ($action) {
	case 'start':
		Debug::Text('Start', __FILE__, __LINE__, __METHOD__,10);

		if ( $data['license_accept'] == 1 ) {
			Redirect::Page( URLBuilder::getURL( array('external_installer' => $external_installer ), 'Requirements.php') );
		}

		break;
	default:
		break;
}

$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'license' ), 'pre_install.php'), "r");
@fclose($handle);

$smarty->assign_by_ref('install_obj', $install_obj);
$smarty->assign_by_ref('external_installer', $external_installer);

$smarty->display('install/License.tpl');
?>