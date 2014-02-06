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
 * $Revision: 5428 $
 * $Id: User.php 5428 2011-10-31 18:13:21Z ipso $
 * $Date: 2011-10-31 11:13:21 -0700 (Mon, 31 Oct 2011) $
 */
require_once('../../includes/global.inc.php');

$authenticate=FALSE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = '6. Administrator User')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'company_id',
												'user_data',
												'external_installer',
												) ) );

$install_obj = new Install();
if ( $install_obj->isInstallMode() == FALSE ) {
	Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
}

$uf = TTnew( 'UserFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'back':
		Debug::Text('Back', __FILE__, __LINE__, __METHOD__,10);

		Redirect::Page( URLBuilder::getURL(NULL, 'Company.php') );
		break;

	case 'next':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$uf->StartTransaction();
		$uf->setCompany( $user_data['company_id'] );
		$uf->setStatus( 10 );
		$uf->setUserName($user_data['user_name']);
		if ( !empty($user_data['password']) AND $user_data['password'] == $user_data['password2'] ) {
			$uf->setPassword($user_data['password']);
		} else {
			$uf->Validator->isTrue(	$uf->password_validator_label,
									FALSE,
									$uf->password_validator_match_msg);
		}

		$uf->setEmployeeNumber(1);
		$uf->setFirstName($user_data['first_name']);
		$uf->setLastName($user_data['last_name']);
		$uf->setWorkEmail($user_data['work_email']);

		if ( is_object( $uf->getCompanyObject() ) ) {
			$uf->setCountry( $uf->getCompanyObject()->getCountry() );
			$uf->setProvince( $uf->getCompanyObject()->getProvince() );
			$uf->setAddress1( $uf->getCompanyObject()->getAddress1() );
			$uf->setAddress2( $uf->getCompanyObject()->getAddress2() );
			$uf->setCity( $uf->getCompanyObject()->getCity() );
			$uf->setPostalCode( $uf->getCompanyObject()->getPostalCode() );
			$uf->setWorkPhone( $uf->getCompanyObject()->getWorkPhone() );
			$uf->setHomePhone( $uf->getCompanyObject()->getWorkPhone() );

			if ( is_object( $uf->getCompanyObject()->getUserDefaultObject() ) ) {
				$uf->setCurrency( $uf->getCompanyObject()->getUserDefaultObject()->getCurrency() );
			}
		}

		//Get Permission Control with highest level, assume its for Administrators and use it.
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getByCompanyId( $user_data['company_id'], NULL, NULL, NULL, array('level' => 'desc' ) );
		if ( $pclf->getRecordCount() > 0 ) {
			$pc_obj = $pclf->getCurrent();
			if ( is_object($pc_obj) ) {
				Debug::Text('Adding User to Permission Control: '. $pc_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
				$uf->setPermissionControl( $pc_obj->getId() );
			}
		}

		if ( $uf->isValid() ) {
			$user_id = $uf->Save();

			//Assign this user as admin/support/billing contact for now.
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getById( $user_data['company_id'] );
			if ( $clf->getRecordCount() == 1 ) {
				$c_obj = $clf->getCurrent();
				$c_obj->setAdminContact( $user_id );
				$c_obj->setBillingContact( $user_id );
				$c_obj->setSupportContact( $user_id );
				if ( $c_obj->isValid() ) {
					$c_obj->Save();
				}
				unset($c_obj, $clf);
			}

			$uf->CommitTransaction();

			if ( $external_installer == 1 ) {
				Redirect::Page( URLBuilder::getURL( NULL, 'Done.php') );
			} else {
				Redirect::Page( URLBuilder::getURL( NULL, 'MaintenanceJobs.php') );
			}

			break;
		}
		$uf->FailTransaction();

	default:
		if ( isset($company_id) ) {
			$user_data['company_id'] = $company_id;
		}
		$smarty->assign_by_ref('user_data', $user_data);

		break;
}

$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'user'), 'pre_install.php'), "r");
@fclose($handle);

$smarty->assign_by_ref('uf', $uf);
$smarty->assign_by_ref('external_installer', $external_installer);

$smarty->display('install/User.tpl');
?>