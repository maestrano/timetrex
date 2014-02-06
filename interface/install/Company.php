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
 * $Revision: 8678 $
 * $Id: Company.php 8678 2012-12-21 22:44:01Z ipso $
 * $Date: 2012-12-21 14:44:01 -0800 (Fri, 21 Dec 2012) $
 */
require_once('../../includes/global.inc.php');

//Debug::setVerbosity( 11 );

$authenticate=FALSE;
//Disable database connection for Interface so we don't attempt to get company information before its created causing the cache file to be created with no records.
$disable_database_connection=TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = '5. Company Information')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'company_data',
												'external_installer',
												) ) );

$install_obj = new Install();
if ( $install_obj->isInstallMode() == FALSE ) {
	Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
}

$cf = TTnew( 'CompanyFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'back':
		Debug::Text('Back', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL(NULL, 'SystemSettings.php') );
		break;

	case 'next':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);


		//$cf->setParent($company_data['parent']);
		$cf->setStatus( 10 );
		$cf->setProductEdition( (int)getTTProductEdition() );
		$cf->setName($company_data['name'], TRUE); //Force change.
		$cf->setShortName($company_data['short_name']);
		$cf->setIndustry($company_data['industry_id']);
		$cf->setAddress1($company_data['address1']);
		$cf->setAddress2($company_data['address2']);
		$cf->setCity($company_data['city']);
		$cf->setCountry($company_data['country']);
		$cf->setProvince($company_data['province']);
		$cf->setPostalCode($company_data['postal_code']);
		$cf->setWorkPhone($company_data['work_phone']);

		$cf->setEnableAddCurrency( TRUE );
		$cf->setEnableAddPermissionGroupPreset( TRUE );
		$cf->setEnableAddUserDefaultPreset( TRUE );
		$cf->setEnableAddStation( TRUE );
		$cf->setEnableAddPayStubEntryAccountPreset( TRUE );
		$cf->setEnableAddCompanyDeductionPreset( TRUE );
		$cf->setEnableAddRecurringHolidayPreset( TRUE );

		if ( $cf->isValid() ) {
			$company_id = $cf->Save();

			$install_obj->writeConfigFile( array('primary_company_id' => $company_id ) );

			Redirect::Page( URLBuilder::getURL( array('company_id' => $company_id, 'external_installer' => $external_installer), 'User.php') );

			break;
		}
	default:
		//Select box options;
		$company_data['status_options'] = $cf->getOptions('status');
		$company_data['country_options'] = $cf->getOptions('country');
		$company_data['industry_options'] = $cf->getOptions('industry');

		if (!isset($id) AND isset($company_data['id']) ) {
			$id = $company_data['id'];
		}
		$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id);

		$smarty->assign_by_ref('company_data', $company_data);

		break;
}

$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'company'), 'pre_install.php'), "r");
@fclose($handle);

$smarty->assign_by_ref('cf', $cf);
$smarty->assign_by_ref('external_installer', $external_installer);

$smarty->display('install/Company.tpl');
?>