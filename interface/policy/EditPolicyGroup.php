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
 * $Id: EditPolicyGroup.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('policy_group','enabled')
		OR !( $permission->Check('policy_group','edit') OR $permission->Check('policy_group','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Policy Group')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$pgf = TTnew( 'PolicyGroupFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Debug::setVerbosity(11);

		$pgf->StartTransaction();

		$pgf->setId( $data['id'] );
		$pgf->setCompany( $current_company->getId() );
		$pgf->setName( $data['name'] );
		$pgf->setExceptionPolicyControlID( $data['exception_policy_control_id'] );

		if ( $pgf->isValid() ) {
			$pgf->Save(FALSE);

			if ( isset($data['user_ids'] ) ) {
				$pgf->setUser( $data['user_ids'] );
			} else {
				$pgf->setUser( array() );
			}

			if ( isset($data['over_time_policy_ids'] ) ) {
				$pgf->setOverTimePolicy( $data['over_time_policy_ids'] );
			} else {
				$pgf->setOverTimePolicy( array() );
			}

			if ( isset($data['premium_policy_ids'] ) ) {
				$pgf->setPremiumPolicy( $data['premium_policy_ids'] );
			} else {
				$pgf->setPremiumPolicy( array() );
			}

			if ( isset($data['round_interval_policy_ids']) ) {
				$pgf->setRoundIntervalPolicy( $data['round_interval_policy_ids'] );
			} else {
				$pgf->setRoundIntervalPolicy( array() );
			}

			if ( isset($data['accrual_policy_ids']) ) {
				$pgf->setAccrualPolicy( $data['accrual_policy_ids'] );
			} else {
				$pgf->setAccrualPolicy( array() );
			}

			if ( isset($data['meal_policy_ids']) ) {
				$pgf->setMealPolicy( $data['meal_policy_ids'] );
			} else {
				$pgf->setMealPolicy( array() );
			}

			if ( isset($data['break_policy_ids']) ) {
				$pgf->setBreakPolicy( $data['break_policy_ids'] );
			} else {
				$pgf->setBreakPolicy( array() );
			}

			if ( isset($data['holiday_policy_ids']) ) {
				$pgf->setHolidayPolicy( $data['holiday_policy_ids'] );
			} else {
				$pgf->setHolidayPolicy( array() );
			}

			if ( $pgf->isValid() ) {
				$pgf->Save();
				$pgf->CommitTransaction();

				Redirect::Page( URLBuilder::getURL( NULL, 'PolicyGroupList.php') );

				break;
			}


		}
		$pgf->FailTransaction();

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($pglf as $pg_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $pg_obj->getId(),
									'name' => $pg_obj->getName(),
									'meal_policy_ids' => $pg_obj->getMealPolicy(),
									'break_policy_ids' => $pg_obj->getBreakPolicy(),
									'holiday_policy_ids' => $pg_obj->getHolidayPolicy(),
									'exception_policy_control_id' => $pg_obj->getExceptionPolicyControlID(),
									'user_ids' => $pg_obj->getUser(),
									'over_time_policy_ids' => $pg_obj->getOverTimePolicy(),
									'premium_policy_ids' => $pg_obj->getPremiumPolicy(),
									'round_interval_policy_ids' => $pg_obj->getRoundIntervalPolicy(),
									'accrual_policy_ids' => $pg_obj->getAccrualPolicy(),
									'created_date' => $pg_obj->getCreatedDate(),
									'created_by' => $pg_obj->getCreatedBy(),
									'updated_date' => $pg_obj->getUpdatedDate(),
									'updated_by' => $pg_obj->getUpdatedBy(),
									'deleted_date' => $pg_obj->getDeletedDate(),
									'deleted_by' => $pg_obj->getDeletedBy()
								);
			}
		}

		$none_array_option = array('0' => TTi18n::gettext('-- None --') );

		$ulf = TTnew( 'UserListFactory' );
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), FALSE, TRUE );

		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$over_time_policy_options = Misc::prependArray( $none_array_option, $otplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$pplf = TTnew( 'PremiumPolicyListFactory' );
		$premium_policy_options = Misc::prependArray( $none_array_option, $pplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$riplf = TTnew( 'RoundIntervalPolicyListFactory' );
		$round_interval_policy_options = Misc::prependArray( $none_array_option, $riplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$mplf = TTnew( 'MealPolicyListFactory' );
		$meal_options = Misc::prependArray( $none_array_option, $mplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$bplf = TTnew( 'BreakPolicyListFactory' );
		$break_options = Misc::prependArray( $none_array_option, $bplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$epclf = TTnew( 'ExceptionPolicyControlListFactory' );
		$exception_options = Misc::prependArray( $none_array_option, $epclf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$hplf = TTnew( 'HolidayPolicyListFactory' );
		$holiday_policy_options = Misc::prependArray( $none_array_option, $hplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$aplf->getByCompanyIdAndTypeID( $current_company->getId(), array(20, 30) ); //Calendar and Hour based.
		$accrual_options = Misc::prependArray( $none_array_option, $aplf->getArrayByListFactory( $aplf, FALSE ) );

		//Select box options;
		$data['user_options'] = $user_options;
		$data['over_time_policy_options'] = $over_time_policy_options;
		$data['premium_policy_options'] = $premium_policy_options;
		$data['round_interval_policy_options'] = $round_interval_policy_options;
		$data['accrual_policy_options'] = $accrual_options;
		$data['meal_options'] = $meal_options;
		$data['break_options'] = $break_options;
		$data['exception_options'] = $exception_options;
		$data['holiday_policy_options'] = $holiday_policy_options;

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('pgf', $pgf);

$smarty->display('policy/EditPolicyGroup.tpl');
?>