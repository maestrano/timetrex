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
 * $Revision: 7108 $
 * $Id: EditBankAccount.php 7108 2012-06-21 17:51:21Z ipso $
 * $Date: 2012-06-21 10:51:21 -0700 (Thu, 21 Jun 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = 'Bank Account')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'user_id',
												'company_id',
												'bank_data',
												'data_saved',
												) ) );

if ( isset($company_id) AND $company_id != '' ) {
	if ( !$permission->Check('company','enabled')
			OR !( $permission->Check('company','edit_own_bank') ) ) {
		$permission->Redirect( FALSE ); //Redirect
	}
} else {
	if ( !$permission->Check('user','enabled')
			OR !( $permission->Check('user','edit_bank') OR $permission->Check('user','edit_own_bank') ) ) {
		$permission->Redirect( FALSE ); //Redirect
	}
}

$baf = TTnew( 'BankAccountFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);

		$balf = TTnew( 'BankAccountListFactory' );

		if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_own_bank') ) {
			Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
			$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			$redirect_arr = array('data_saved' => TRUE );
		} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_bank') ) {
			Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
			$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );
			$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
		} elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' AND $permission->Check('company','edit_own_bank') ) {
			Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
			//Company bank.
			$balf->GetCompanyAccountByCompanyId( $current_company->getId() );
			$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
		} else {
			$permission->Redirect( FALSE );
		}

		Debug::Text('Found Records: '. $balf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $balf->getRecordCount() > 0 ) {
			$b_obj = $balf->getCurrent();
			$b_obj->setDeleted(TRUE);
			$b_obj->Save();

			Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/bank_account/EditBankAccount.php') );
		}

		break;
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($bank_data, 'Bank Data: ', __FILE__, __LINE__, __METHOD__,10);

		if ( !empty($bank_data['id']) ) {
			$balf = TTnew('BankAccountListFactory');
			$balf->getByIdAndCompanyId( $bank_data['id'], $current_company->getId() );
			if ( $balf->getRecordCount() == 1 ) {
				$baf = $balf->getCurrent();
			}
		}

		if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_own_bank') ) {
			Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
			//Current user
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $current_user->getId() );
			$redirect_arr = array('data_saved' => TRUE );
		} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_bank') ) {
			Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
			//Specified User
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $bank_data['user_id'] );
			$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
		} elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' AND $permission->Check('company','edit_own_bank') ) {
			Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
			//Company bank.
			$baf->setCompany( $bank_data['company_id'] );
			$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
		} else {
			$permission->Redirect( FALSE );
		}

		if ( isset($bank_data['institution']) ) {
			$baf->setInstitution( $bank_data['institution'] );
		}
		$baf->setTransit( $bank_data['transit'] );
		$baf->setAccount( $bank_data['account'] );

		if ( $baf->isValid() ) {
			$baf->Save();

			Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/bank_account/EditBankAccount.php') );

			break;
		} else {
			Debug::Text('Invalid bank data...', __FILE__, __LINE__, __METHOD__,10);
		}
	default:
		$balf = TTnew( 'BankAccountListFactory' );
		$ulf = TTnew( 'UserListFactory' );

		$country = NULL;
		if ( ( $user_id == '' OR $user_id == $current_user->getId() ) AND $company_id == '' AND $permission->Check('user','edit_own_bank') ) {
			//Current user
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			$user_id = $current_user->getId();

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
		} elseif ( $user_id != '' AND $permission->Check('user','edit_bank') ) {
			//Specified User
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
		} elseif ( $company_id != '' AND $permission->Check('company','edit_own_bank') ) {
			//Company bank.
			$balf->getCompanyAccountByCompanyId( $current_company->getId() );

			$country = $current_company->getCountry();
		} else {
			$permission->Redirect( FALSE );
		}

		if ( !isset($action) ) {
			BreadCrumb::setCrumb($title);

			foreach ($balf as $bank_account) {
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$bank_data = array(
									'id' => $bank_account->getId(),
									'country' => strtolower($country),
									'institution' => $bank_account->getInstitution(),
									'transit' => $bank_account->getTransit(),
									'account' => $bank_account->getSecureAccount(),
									'created_date' => $bank_account->getCreatedDate(),
									'created_by' => $bank_account->getCreatedBy(),
									'updated_date' => $bank_account->getUpdatedDate(),
									'updated_by' => $bank_account->getUpdatedBy(),
									'deleted_date' => $bank_account->getDeletedDate(),
									'deleted_by' => $bank_account->getDeletedBy()
								);
			}
		}

		if ( isset($user_id) AND $company_id == '' ) {
			//$user_id = $current_user->getId();
			$ulf = TTnew( 'UserListFactory' );
			$full_name = $ulf->getById( $user_id )->getCurrent()->getFullName();
		} elseif ( $company_id != '' ) {
			$clf = TTnew( 'CompanyListFactory' );
			$full_name = $clf->getById( $company_id )->getCurrent()->getName();
		}

		//Select box options;
		$bank_data['ach_transaction_type_options'] = $baf->getOptions('ach_transaction_type');

		$bank_data['full_name'] = $full_name;
		$bank_data['country'] = strtolower($country);

		$bank_data['user_id'] = $user_id;
		$bank_data['company_id'] = $company_id;

		//var_dump($bank_data);
		$smarty->assign_by_ref('bank_data', $bank_data);
		$smarty->assign_by_ref('data_saved', $data_saved);

		break;
}

$smarty->assign_by_ref('baf', $baf);
//$smarty->assign_by_ref('current_time', TTDate::getDate('TIME') );

$smarty->display('bank_account/EditBankAccount.tpl');
?>
