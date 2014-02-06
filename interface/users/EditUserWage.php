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
 * $Id: EditUserWage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Wage')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'saved_search_id',
												'wage_data'
												) ) );

if ( isset($wage_data) ) {
	if ( $wage_data['effective_date'] != '' ) {
		$wage_data['effective_date'] = TTDate::parseDateTime($wage_data['effective_date']);
	}
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

$uwf = TTnew( 'UserWageFactory' );

$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ulf->getByIdAndCompanyId($user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
			if ( $permission->Check('wage','edit')
					OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {
				$uwf->setId($wage_data['id']);
				$uwf->setUser($user_id);
				$uwf->setWageGroup($wage_data['wage_group_id']);
				$uwf->setType($wage_data['type']);
				$uwf->setWage($wage_data['wage']);
				$uwf->setHourlyRate($wage_data['hourly_rate']);
				$uwf->setWeeklyTime( TTDate::parseTimeUnit( $wage_data['weekly_time'] ) );
				$uwf->setEffectiveDate( $wage_data['effective_date'] );
				$uwf->setLaborBurdenPercent( $wage_data['labor_burden_percent'] );
				$uwf->setNote( $wage_data['note'] );

				if ( $uwf->isValid() ) {
					$uwf->Save();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id), 'UserWageList.php') );

					break;
				}
			} else {
				$permission->Redirect( FALSE ); //Redirect
				exit;
			}
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$uwlf = TTnew( 'UserWageListFactory' );
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($uwlf as $wage) {
				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();
				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$wage_data = array(
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
											'wage_group_id' => $wage->getWageGroup(),
											'type' => $wage->getType(),
											'wage' => Misc::removeTrailingZeros( $wage->getWage() ),
											'hourly_rate' => Misc::removeTrailingZeros( $wage->getHourlyRate() ),
											'weekly_time' => $wage->getWeeklyTime(),
											'effective_date' => $wage->getEffectiveDate(),
											'labor_burden_percent' => (float)$wage->getLaborBurdenPercent(),
											'note' => $wage->getNote(),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);

						$tmp_effective_date = TTDate::getDate('DATE', $wage->getEffectiveDate() );
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {
			if ( $action != 'submit' ) {
				$wage_data = array( 'effective_date' => TTDate::getTime(), 'labor_burden_percent' => 0 );
			}
		}
		//Select box options;
		$wage_data['type_options'] = $uwf->getOptions('type');

		$wglf = TTnew( 'WageGroupListFactory' );
		$wage_data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$crlf = TTnew( 'CurrencyListFactory' );
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();
		if ( is_object( $user_data->getCurrencyObject() ) ) {
			$wage_data['currency_symbol'] = $user_data->getCurrencyObject()->getSymbol();
			$wage_data['iso_code'] = $user_data->getCurrencyObject()->getISOCode();
		}

		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = TTi18n::gettext('(Hire Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => TTi18n::gettext('(Choose Date)')), $pay_period_boundary_dates);

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getByUserId( $user_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pay_period_schedule_id = $ppslf->getCurrent()->getId();
			$pay_period_schedule_name = $ppslf->getCurrent()->getName();
			Debug::Text('Pay Period Schedule ID: '. $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id, 10, NULL, NULL, array('transaction_date' => 'desc') );
			$pay_period_dates = NULL;
			foreach($pplf as $pay_period_obj) {
				//$pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getEndDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getEndDate() );
				if ( !isset($pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )])) {
					$pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getStartDate() );
				}
			}
		} else {
			$smarty->assign('pay_period_schedule', FALSE);

			$uwf->Validator->isTrue(		'employee',
											FALSE,
											TTi18n::getText('Employee is not currently assigned to a pay period schedule.').' <a href="'.URLBuilder::getURL( NULL, '../payperiod/PayPeriodScheduleList.php').'">'. TTi18n::getText('Click here</a> to assign') );
		}

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('wage_data', $wage_data);

		$smarty->assign_by_ref('tmp_effective_date', $tmp_effective_date);
		$smarty->assign_by_ref('pay_period_boundary_date_options', $pay_period_boundary_dates);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);


		break;
}

$smarty->assign_by_ref('uwf', $uwf);

$smarty->display('users/EditUserWage.tpl');
?>