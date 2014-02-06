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
 * $Revision: 5458 $
 * $Id: EditHolidayPolicy.php 5458 2011-11-04 21:07:59Z ipso $
 * $Date: 2011-11-04 14:07:59 -0700 (Fri, 04 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('holiday_policy','enabled')
		OR !( $permission->Check('holiday_policy','edit') OR $permission->Check('holiday_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Holiday Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data['minimum_time'] ) ) {
	$data['minimum_time'] = TTDate::parseTimeUnit($data['minimum_time']);
}
if ( isset($data['maximum_time'] ) ) {
	$data['maximum_time'] = TTDate::parseTimeUnit($data['maximum_time']);
}


$hpf = TTnew( 'HolidayPolicyFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$hpf->StartTransaction();

		$hpf->setId( $data['id'] );
		$hpf->setCompany( $current_company->getId() );
		$hpf->setName( $data['name'] );
		$hpf->setType( $data['type_id'] );

		$hpf->setDefaultScheduleStatus( $data['default_schedule_status_id'] );
		$hpf->setMinimumEmployedDays( $data['minimum_employed_days'] );

		$hpf->setMinimumWorkedPeriodDays( $data['minimum_worked_period_days'] );
		$hpf->setMinimumWorkedDays( $data['minimum_worked_days'] );
		$hpf->setWorkedScheduledDays( $data['worked_scheduled_days'] );

		$hpf->setMinimumWorkedAfterPeriodDays( $data['minimum_worked_after_period_days'] );
		$hpf->setMinimumWorkedAfterDays( $data['minimum_worked_after_days'] );
		$hpf->setWorkedAfterScheduledDays( $data['worked_after_scheduled_days'] );

		$hpf->setAverageTimeDays( $data['average_time_days'] );

		if ( isset($data['average_days']) ) {
			$hpf->setAverageDays( $data['average_days'] );
		} else {
			$hpf->setAverageDays( 0 );
		}

		if ( isset($data['average_time_worked_days']) ) {
			$hpf->setAverageTimeWorkedDays( TRUE );
		} else {
			$hpf->setAverageTimeWorkedDays( FALSE );
		}
		if ( isset($data['include_over_time']) ) {
			$hpf->setIncludeOverTime( TRUE );
		} else {
			$hpf->setIncludeOverTime( FALSE );
		}
		if ( isset($data['include_paid_absence_time']) ) {
			$hpf->setIncludePaidAbsenceTime( TRUE );
		} else {
			$hpf->setIncludePaidAbsenceTime( FALSE );
		}
		if ( isset($data['force_over_time_policy']) ) {
			$hpf->setForceOverTimePolicy( TRUE );
		} else {
			$hpf->setForceOverTimePolicy( FALSE );
		}

		$hpf->setMinimumTime( $data['minimum_time'] );
		$hpf->setMaximumTime( $data['maximum_time'] );
		$hpf->setAbsencePolicyID( $data['absence_policy_id'] );
		$hpf->setRoundIntervalPolicyID( $data['round_interval_policy_id'] );

		if ( $hpf->isValid() ) {
			$hpf->Save(FALSE);

			$hpf->setRecurringHoliday( $data['recurring_holiday_ids'] );

			if ( $hpf->isValid() ) {
				$hpf->Save();
				$hpf->CommitTransaction();

				Redirect::Page( URLBuilder::getURL( NULL, 'HolidayPolicyList.php') );

				break;
			}
		}

		$hpf->FailTransaction();

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$hplf = TTnew( 'HolidayPolicyListFactory' );
			$hplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($hplf as $hp_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $hp_obj->getId(),
									'name' => $hp_obj->getName(),
									'type_id' => $hp_obj->getType(),
									'default_schedule_status_id' => $hp_obj->getDefaultScheduleStatus(),
									'minimum_employed_days' => $hp_obj->getMinimumEmployedDays(),

									'minimum_worked_period_days' => $hp_obj->getMinimumWorkedPeriodDays(),
									'minimum_worked_days' => $hp_obj->getMinimumWorkedDays(),
									'worked_scheduled_days' => $hp_obj->getWorkedScheduledDays(),

									'minimum_worked_after_period_days' => $hp_obj->getMinimumWorkedAfterPeriodDays(),
									'minimum_worked_after_days' => $hp_obj->getMinimumWorkedAfterDays(),
									'worked_after_scheduled_days' => $hp_obj->getWorkedAfterScheduledDays(),

									'average_time_days' => $hp_obj->getAverageTimeDays(),
									'average_days' => $hp_obj->getAverageDays(),
									'average_time_worked_days' => $hp_obj->getAverageTimeWorkedDays(),
									'force_over_time_policy' => $hp_obj->getForceOverTimePolicy(),
									'include_over_time' => $hp_obj->getIncludeOverTime(),
									'include_paid_absence_time' => $hp_obj->getIncludePaidAbsenceTime(),
									'minimum_time' => $hp_obj->getMinimumTime(),
									'maximum_time' => $hp_obj->getMaximumTime(),
									//'time' => $hp_obj->getTime(),

									'round_interval_policy_id' => $hp_obj->getRoundIntervalPolicyID(),
									'absence_policy_id' => $hp_obj->getAbsencePolicyID(),

									'recurring_holiday_ids' => $hp_obj->getRecurringHoliday(),

									'created_date' => $hp_obj->getCreatedDate(),
									'created_by' => $hp_obj->getCreatedBy(),
									'updated_date' => $hp_obj->getUpdatedDate(),
									'updated_by' => $hp_obj->getUpdatedBy(),
									'deleted_date' => $hp_obj->getDeletedDate(),
									'deleted_by' => $hp_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			//Defaults
			$data = array(
						'default_schedule_status_id' => 20,
						'minimum_employed_days' => 30,
						'minimum_worked_period_days' => 30,
						'minimum_worked_days' => 15,
						'minimum_worked_after_period_days' => 0,
						'minimum_worked_after_days' => 0,
						'average_time_days' => 30,
						'average_days' => 30,
						'force_over_time_policy' => FALSE,
						'include_over_time' => FALSE,
						'include_paid_absence_time' => TRUE,
						'minimum_time' => 0,
						'maximum_time' => 0
						);
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$riplf = TTnew( 'RoundIntervalPolicyListFactory' );
		$round_interval_options = $riplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$rhlf = TTnew( 'RecurringHolidayListFactory' );
		$recurring_holiday_options = $rhlf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$sf = TTnew( 'ScheduleFactory' );

		//Select box options;
		$data['type_options'] = $hpf->getOptions('type');
		$data['schedule_status_options'] = $sf->getOptions('status');
		$data['scheduled_day_options'] = $hpf->getOptions('scheduled_day');
		$data['absence_options'] = $absence_options;
		$data['round_interval_options'] = $round_interval_options;
		$data['recurring_holiday_options'] = $recurring_holiday_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('hpf', $hpf);

$smarty->display('policy/EditHolidayPolicy.tpl');
?>