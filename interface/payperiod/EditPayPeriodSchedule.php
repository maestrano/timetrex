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
 * $Revision: 5178 $
 * $Id: EditPayPeriodSchedule.php 5178 2011-08-30 21:13:34Z ipso $
 * $Date: 2011-08-30 14:13:34 -0700 (Tue, 30 Aug 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_period_schedule','enabled')
		OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Pay Period Schedule')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'pay_period_schedule_data'
												) ) );
//var_dump($pay_period_schedule_data);

if ( isset($pay_period_schedule_data) ) {
	if ( isset($pay_period_schedule_data['anchor_date']) ) {
		$pay_period_schedule_data['anchor_date'] = TTDate::parseDateTime( $pay_period_schedule_data['anchor_date'] );
	}
	if ( isset($pay_period_schedule_data['day_start_time'] ) ) {
		$pay_period_schedule_data['day_start_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['day_start_time'] );
	}
	if ( isset($pay_period_schedule_data['new_day_trigger_time']) ) {
		$pay_period_schedule_data['new_day_trigger_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['new_day_trigger_time'] );
	}
	if ( isset($pay_period_schedule_data['maximum_shift_time']) ) {
		$pay_period_schedule_data['maximum_shift_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['maximum_shift_time'] );
	}
}

//var_dump($pay_period_schedule_data);
$ppsf = TTnew( 'PayPeriodScheduleFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ppsf->StartTransaction();

		$ppsf->setId($pay_period_schedule_data['id']);
		$ppsf->setCompany( $current_company->getId() );
		$ppsf->setName($pay_period_schedule_data['name']);
		$ppsf->setDescription($pay_period_schedule_data['description']);
		$ppsf->setType($pay_period_schedule_data['type']);
		$ppsf->setStartWeekDay($pay_period_schedule_data['start_week_day_id']);

		if ( $pay_period_schedule_data['type'] == 5 ) {
			$ppsf->setAnnualPayPeriods($pay_period_schedule_data['annual_pay_periods']);
		}

		if ( $pay_period_schedule_data['type'] == 10 OR $pay_period_schedule_data['type'] == 20 ) {
			$ppsf->setStartDayOfWeek($pay_period_schedule_data['start_day_of_week']);
			$ppsf->setTransactionDate($pay_period_schedule_data['transaction_date']);
		} elseif (  $pay_period_schedule_data['type'] == 30 ) {
			$ppsf->setPrimaryDayOfMonth($pay_period_schedule_data['primary_day_of_month']);
			$ppsf->setSecondaryDayOfMonth($pay_period_schedule_data['secondary_day_of_month']);
			$ppsf->setPrimaryTransactionDayOfMonth($pay_period_schedule_data['primary_transaction_day_of_month']);
			$ppsf->setSecondaryTransactionDayOfMonth($pay_period_schedule_data['secondary_transaction_day_of_month']);
		} elseif ( $pay_period_schedule_data['type'] == 50 ) {
			$ppsf->setPrimaryDayOfMonth($pay_period_schedule_data['primary_day_of_month']);
			$ppsf->setPrimaryTransactionDayOfMonth($pay_period_schedule_data['primary_transaction_day_of_month']);
		}

		if ( isset($pay_period_schedule_data['anchor_date']) ) {
			$ppsf->setAnchorDate( $pay_period_schedule_data['anchor_date'] );
		}

		$ppsf->setTransactionDateBusinessDay( $pay_period_schedule_data['transaction_date_bd'] );

		if ( isset($pay_period_schedule_data['day_start_time']) ) {
			$ppsf->setDayStartTime( $pay_period_schedule_data['day_start_time'] );
		} else {
			$ppsf->setDayStartTime(	0 );
		}

		$ppsf->setTimeZone( $pay_period_schedule_data['time_zone'] );
		$ppsf->setNewDayTriggerTime( $pay_period_schedule_data['new_day_trigger_time'] );
		$ppsf->setMaximumShiftTime( $pay_period_schedule_data['maximum_shift_time'] );
		$ppsf->setShiftAssignedDay( $pay_period_schedule_data['shift_assigned_day_id'] );

		$ppsf->setTimeSheetVerifyType( $pay_period_schedule_data['timesheet_verify_type_id'] );
		$ppsf->setTimeSheetVerifyBeforeEndDate( $pay_period_schedule_data['timesheet_verify_before_end_date'] );
		$ppsf->setTimeSheetVerifyBeforeTransactionDate( $pay_period_schedule_data['timesheet_verify_before_transaction_date'] );

		if ( isset($pay_period_schedule_data['user_ids']) ){
			$ppsf->setUser( $pay_period_schedule_data['user_ids'] );
		}

		if ( $ppsf->isValid() ) {
			//Pay Period schedule has to be saved before users can be assigned to it, so
			//do it this way.
			$ppsf->Save(FALSE);
			$ppsf->setEnableInitialPayPeriods(FALSE);

			if ( isset($pay_period_schedule_data['user_ids']) ){
				$ppsf->setUser( $pay_period_schedule_data['user_ids'] );
			} else {
				$ppsf->setUser( array() );
			}

			if ( $ppsf->isValid() ) {
				$ppsf->Save(TRUE);

				//$ppsf->FailTransaction();

				$ppsf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'PayPeriodScheduleList.php') );

				break;
			}
		}

		$ppsf->FailTransaction();

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

			$ppslf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($ppslf as $pay_period_schedule) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$pay_period_schedule_data = array(
													'id' => $pay_period_schedule->getId(),
													'company_id' => $pay_period_schedule->getCompany(),
													'name' => $pay_period_schedule->getName(),
													'description' => $pay_period_schedule->getDescription(),
													'type' => $pay_period_schedule->getType(),
													'start_week_day_id' => $pay_period_schedule->getStartWeekDay(),
													'start_day_of_week' => $pay_period_schedule->getStartDayOfWeek(),
													'transaction_date' => $pay_period_schedule->getTransactionDate(),

													'primary_day_of_month' => $pay_period_schedule->getPrimaryDayOfMonth(),
													'secondary_day_of_month' => $pay_period_schedule->getSecondaryDayOfMonth(),

													'primary_transaction_day_of_month' => $pay_period_schedule->getPrimaryTransactionDayOfMonth(),
													'secondary_transaction_day_of_month' => $pay_period_schedule->getSecondaryTransactionDayOfMonth(),

													'transaction_date_bd' => $pay_period_schedule->getTransactionDateBusinessDay(),

													'anchor_date' => $pay_period_schedule->getAnchorDate(),

													'annual_pay_periods' => $pay_period_schedule->getAnnualPayPeriods(),

													'day_start_time' => $pay_period_schedule->getDayStartTime(),
													'time_zone' => $pay_period_schedule->getTimeZone(),
													'new_day_trigger_time' => $pay_period_schedule->getNewDayTriggerTime(),
													'maximum_shift_time' => $pay_period_schedule->getMaximumShiftTime(),
													'shift_assigned_day_id' => $pay_period_schedule->getShiftAssignedDay(),

													'timesheet_verify_type_id' => $pay_period_schedule->getTimeSheetVerifyType(),
													'timesheet_verify_before_end_date' => $pay_period_schedule->getTimeSheetVerifyBeforeEndDate(),
													'timesheet_verify_before_transaction_date' => $pay_period_schedule->getTimeSheetVerifyBeforeTransactionDate(),
													'timesheet_verify_notice_before_transaction_date' => $pay_period_schedule->getTimeSheetVerifyNoticeBeforeTransactionDate(),
													'timesheet_verify_notice_email' => $pay_period_schedule->getTimeSheetVerifyNoticeEmail(),

													'user_ids' => $pay_period_schedule->getUser(),

													'deleted' => $pay_period_schedule->getDeleted(),
													'created_date' => $pay_period_schedule->getCreatedDate(),
													'created_by' => $pay_period_schedule->getCreatedBy(),
													'updated_date' => $pay_period_schedule->getUpdatedDate(),
													'updated_by' => $pay_period_schedule->getUpdatedBy(),
													'deleted_date' => $pay_period_schedule->getDeletedDate(),
													'deleted_by' => $pay_period_schedule->getDeletedBy()
												);
			}
		} elseif ( $action != 'submit' ) {

			$pay_period_schedule_data = array(
											'anchor_date' => TTDate::getBeginMonthEpoch( time() ),
											'day_start_time' => 0,
											'new_day_trigger_time' => (3600*4),
											'maximum_shift_time' => (3600*16),
											'time_zone' => $current_user_prefs->getTimeZone(),
											'type' => 20,
											'timesheet_verify_type_id' => 10, //Disabled
											'timesheet_verify_before_end_date' => 0,
											'timesheet_verify_before_transaction_date' => 0,
											'annual_pay_periods' => 0
											);
		}
		//Select box options;
		$pay_period_schedule_data['type_options'] = $ppsf->getOptions('type');
		$pay_period_schedule_data['start_week_day_options'] = $ppsf->getOptions('start_week_day');
		$pay_period_schedule_data['shift_assigned_day_options'] = $ppsf->getOptions('shift_assigned_day');
		$pay_period_schedule_data['timesheet_verify_type_options'] = $ppsf->getOptions('timesheet_verify_type');
		$pay_period_schedule_data['time_zone_options'] = $ppsf->getTimeZoneOptions();
		$pay_period_schedule_data['transaction_date_bd_options'] = $ppsf->getOptions('transaction_date_business_day');
		$pay_period_schedule_data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$pay_period_schedule_data['transaction_date_options'] = Misc::prependArray( array( 0 => '0' ), TTDate::getDayOfMonthArray() );
		$pay_period_schedule_data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$pay_period_schedule_data['day_of_month_options'][-1] = TTi18n::gettext('- Last Day Of Month -');

		$pay_period_schedule_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);

		if ( isset($pay_period_schedule_data['user_ids']) AND is_array($pay_period_schedule_data['user_ids']) ) {
			$tmp_user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
			foreach( $pay_period_schedule_data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('pay_period_schedule_data', $pay_period_schedule_data);

		break;
}

$smarty->assign_by_ref('ppsf', $ppsf);

$smarty->display('payperiod/EditPayPeriodSchedule.tpl');
?>