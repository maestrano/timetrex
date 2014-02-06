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
 * $Revision: 9993 $
 * $Id: EditUserDateTotal.php 9993 2013-05-24 20:16:41Z ipso $
 * $Date: 2013-05-24 13:16:41 -0700 (Fri, 24 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','edit')
				OR $permission->Check('punch','edit_own')
				 ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Hour')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_date_id',
												'user_id',
												'date',
												'udt_data'
												) ) );


if ( isset($udt_data) ) {
	if ( $udt_data['total_time'] != '') {
		$udt_data['total_time'] = TTDate::parseTimeUnit( $udt_data['total_time'] ) ;
	}
}

$udtf = TTnew( 'UserDateTotalFactory' );

$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$udtf->setId($udt_data['id']);
		$udtf->setUserDateId($udt_data['user_date_id']);
		$udtf->setStatus($udt_data['status_id']);
		$udtf->setType( $udt_data['type_id'] );
		$udtf->setBranch($udt_data['branch_id']);
		$udtf->setDepartment($udt_data['department_id']);

		if ( isset($udt_data['job_id']) ) {
			$udtf->setJob($udt_data['job_id']);
		}
		if ( isset($udt_data['job_item_id']) ) {
			$udtf->setJobItem($udt_data['job_item_id']);
		}
		if ( isset($udt_data['quantity']) ) {
			$udtf->setQuantity($udt_data['quantity']);
		}
		if ( isset($udt_data['bad_quantity']) ) {
			$udtf->setBadQuantity($udt_data['bad_quantity']);
		}

		$udtf->setOverTimePolicyID($udt_data['over_time_policy_id']);
		$udtf->setPremiumPolicyID($udt_data['premium_policy_id']);
		$udtf->setAbsencePolicyID($udt_data['absence_policy_id']);
		$udtf->setMealPolicyID($udt_data['meal_policy_id']);

		$udtf->setTotalTime($udt_data['total_time']);
		$udtf->setPunchControlID( (int)$udt_data['punch_control_id']);
		if ( isset($udt_data['override']) AND $udt_data['override'] == 1 ) {
			Debug::Text('Setting override to TRUE!', __FILE__, __LINE__, __METHOD__,10);
			$udtf->setOverride(TRUE);
		} else {
			$udtf->setOverride(FALSE);
		}

		if ( $udtf->isValid() ) {
			$udtf->setEnableCalcSystemTotalTime( TRUE );
			$udtf->setEnableCalcWeeklySystemTotalTime( TRUE );
			$udtf->setEnableCalcException( TRUE );

			$udtf->Save();

			Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
			break;
		}
	default:
		if ( $id != '' ) {
			Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getById( $id );

			foreach ($udtlf as $udt_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$udt_data = array(
									'id' => $udt_obj->getId(),
									'user_date_id' => $udt_obj->getUserDateId(),
									'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
									'user_id' => $udt_obj->getUserDateObject()->getUser(),
									'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
									'status_id' => $udt_obj->getStatus(),
									'type_id' => $udt_obj->getType(),
									'total_time' => $udt_obj->getTotalTime(),
									'branch_id' => $udt_obj->getBranch(),
									'department_id' => $udt_obj->getDepartment(),
									'job_id' => $udt_obj->getJob(),
									'job_item_id' => $udt_obj->getJobItem(),
									'quantity' => $udt_obj->getQuantity(),
									'bad_quantity' => $udt_obj->getBadQuantity(),
									'punch_control_id' => $udt_obj->getPunchControlID(),
									'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
									'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
									'premium_policy_id' => $udt_obj->getPremiumPolicyID(),
									'meal_policy_id' => $udt_obj->getMealPolicyID(),
									'override' => $udt_obj->getOverride(),
									'created_date' => $udt_obj->getCreatedDate(),
									'created_by' => $udt_obj->getCreatedBy(),
									'updated_date' => $udt_obj->getUpdatedDate(),
									'updated_by' => $udt_obj->getUpdatedBy(),
									'deleted_date' => $udt_obj->getDeletedDate(),
									'deleted_by' => $udt_obj->getDeletedBy(),
									'override' => $udt_obj->getOverride(),
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//UserID has to be set at minimum

			if ( $user_date_id != '' ) {
				$udlf = TTnew( 'UserDateListFactory' );
				$udlf->getById( $user_date_id );
				if ( $udlf->getRecordCount() > 0 ) {
					$udt_obj = $udlf->getCurrent();

					$udt_data = array(
										'user_date_id' => $user_date_id,
										'date_stamp' => $udt_obj->getDateStamp(),
										'user_id' => $udt_obj->getUser(),
										'user_full_name' => $udt_obj->getUserObject()->getFullName(),
										'branch_id' => $udt_obj->getUserObject()->getDefaultBranch(),
										'department_id' => $udt_obj->getUserObject()->getDefaultDepartment(),
										'total_time' => 0,
										'status_id' => 20,
										'quantity' => 0,
										'bad_quantity' => 0,
										'punch_control_id' => 0,
										'override' => FALSE
								);
				}
			}
		}

		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		//Absence policies
		$otplf = TTnew( 'AbsencePolicyListFactory' );
		$absence_policy_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Overtime policies
		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$over_time_policy_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Premium policies
		$pplf = TTnew( 'PremiumPolicyListFactory' );
		$premium_policy_options = $pplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Meal policies
		$mplf = TTnew( 'MealPolicyListFactory' );
		$meal_policy_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		if ( $current_company->getProductEdition() >= 20 ) {
			$jlf = TTnew( 'JobListFactory' );
			$udt_data['job_options'] = $jlf->getByCompanyIdAndUserIdAndStatusArray( $current_company->getId(),  $udt_data['user_id'], array(10,20,30,40), TRUE );

			$jilf = TTnew( 'JobItemListFactory' );
			$udt_data['job_item_options'] = $jilf->getByCompanyIdArray( $current_company->getId(), TRUE );
		}

		//Select box options;
		$udt_data['status_options'] = $udtf->getOptions('status');
		$udt_data['type_options'] = $udtf->getOptions('type');
		$udt_data['branch_options'] = $branch_options;
		$udt_data['department_options'] = $department_options;
		$udt_data['absence_policy_options'] = $absence_policy_options;
		$udt_data['over_time_policy_options'] = $over_time_policy_options;
		$udt_data['premium_policy_options'] = $premium_policy_options;
		$udt_data['meal_policy_options'] = $meal_policy_options;

		//var_dump($pc_data);

		$smarty->assign_by_ref('udt_data', $udt_data);
		$smarty->assign_by_ref('user_date_id', $user_date_id);
		$smarty->assign_by_ref('user_id', $user_id);

		break;
}

$smarty->assign_by_ref('udtf', $udtf);

$smarty->display('punch/EditUserDateTotal.tpl');
?>