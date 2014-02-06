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
 * $Revision: 4104 $
 * $Id: EditUserAccrual.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual','enabled')
		OR !( $permission->Check('accrual','edit') OR $permission->Check('accrual','edit_own') OR $permission->Check('accrual','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Edit Accrual')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'filter_user_id',
												'accrual_policy_id',
												'data'
												) ) );

if ( isset($data) ) {
	$data['time_stamp'] = TTDate::parseDateTime($data['time_stamp']);
	$data['amount'] = TTDate::parseTimeUnit( $data['amount'] );
}

$af = TTnew( 'AccrualFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$af->setId( $data['id'] );
		$af->setUser( $data['user_id'] );
		$af->setType( $data['type_id'] );
		$af->setAccrualPolicyID( $data['accrual_policy_id'] );
		$af->setAmount( $data['amount'] );
		$af->setTimeStamp( $data['time_stamp'] );
		$af->setEnableCalcBalance( TRUE );

		if ( $af->isValid() ) {
			$af->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'UserAccrualBalanceList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$alf = TTnew( 'AccrualListFactory' );
			$alf->getById($id);

			foreach ($alf as $a_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $a_obj->getId(),
									'user_id' => $a_obj->getUser(),
									'accrual_policy_id' => $a_obj->getAccrualPolicyID(),
									'type_id' => $a_obj->getType(),
									'amount' => $a_obj->getAmount(),
									'time_stamp' => $a_obj->getTimeStamp(),
									'user_date_total_id' => $a_obj->getUserDateTotalID(),
									'created_date' => $a_obj->getCreatedDate(),
									'created_by' => $a_obj->getCreatedBy(),
									'updated_date' => $a_obj->getUpdatedDate(),
									'updated_by' => $a_obj->getUpdatedBy(),
									'deleted_date' => $a_obj->getDeletedDate(),
									'deleted_by' => $a_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			if ( $user_id == '' ) {
				$user_id = $filter_user_id;
			}
			$data = array(
						'user_id' => $user_id,
						'accrual_policy_id' => $accrual_policy_id,
						'amount' => 0,
						'time_stamp' => TTDate::getTime()
						);
		}

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$ulf = TTnew( 'UserListFactory' );
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Select box options;
		$data['type_options'] = $af->getOptions('user_type');
		$data['user_options'] = $user_options;
		$data['accrual_policy_options'] = $accrual_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('af', $af);

$smarty->display('accrual/EditUserAccrual.tpl');
?>