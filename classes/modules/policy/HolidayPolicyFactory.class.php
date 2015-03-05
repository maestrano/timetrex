<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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


/**
 * @package Modules\Policy
 */
class HolidayPolicyFactory extends Factory {
	protected $table = 'holiday_policy';
	protected $pk_sequence_name = 'holiday_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $round_interval_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $contributing_shift_policy_obj = NULL;
	protected $eligible_contributing_shift_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'default_schedule_status':
				$sf = TTnew( 'ScheduleFactory' );
				$retval = $sf->getOptions('status');
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Standard'),
										20 => TTi18n::gettext('Advanced: Fixed'),
										30 => TTi18n::gettext('Advanced: Average'),
									);
				break;
			case 'scheduled_day':
				$retval = array(
										0 => TTi18n::gettext('Calendar Days'),
										1 => TTi18n::gettext('Scheduled Days'),
										2 => TTi18n::gettext('Holiday Week Days'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1025-description' => TTi18n::gettext('Description'),

										'-1030-default_schedule_status' => TTi18n::gettext('Default Schedule Status'),
										'-1040-minimum_employed_days' => TTi18n::gettext('Minimum Employed Days'),

										'-1900-in_use' => TTi18n::gettext('In Use'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'type',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
										'description' => 'Description',
										'default_schedule_status_id' => 'DefaultScheduleStatus',
										'default_schedule_status' => FALSE,
										'minimum_employed_days' => 'MinimumEmployedDays',
										'minimum_worked_period_days' => 'MinimumWorkedPeriodDays',
										'minimum_worked_days' => 'MinimumWorkedDays',
										'worked_scheduled_days' => 'WorkedScheduledDays',
										'minimum_worked_after_period_days' => 'MinimumWorkedAfterPeriodDays',
										'minimum_worked_after_days' => 'MinimumWorkedAfterDays',
										'worked_after_scheduled_days' => 'WorkedAfterScheduledDays',
										'average_time_days' => 'AverageTimeDays',
										'average_days' => 'AverageDays',
										'average_time_worked_days' => 'AverageTimeWorkedDays',
										'minimum_time' => 'MinimumTime',
										'maximum_time' => 'MaximumTime',
										'round_interval_policy_id' => 'RoundIntervalPolicyID',
										//'time' => 'Time',
										'paid_absence_as_worked' => 'PaidAbsenceAsWorked',
										'force_over_time_policy' => 'ForceOverTimePolicy',

										'contributing_shift_policy_id' => 'ContributingShiftPolicy',
										'contributing_shift_policy' => FALSE,
										'eligible_contributing_shift_policy_id' => 'EligibleContributingShiftPolicy',
										'eligible_contributing_shift_policy' => FALSE,

										'include_over_time' => 'IncludeOverTime',
										'include_paid_absence_time' => 'IncludePaidAbsenceTime',
										'absence_policy_id' => 'AbsencePolicyID',
										'recurring_holiday_id' => 'RecurringHoliday',
										'in_use' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getRoundIntervalPolicyObject() {
		return $this->getGenericObject( 'RoundIntervalPolicyListFactory', $this->getRoundIntervalPolicyID(), 'round_interval_policy_obj' );
	}

	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}
	
	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	function getEligibleContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getEligibleContributingShiftPolicy(), 'eligible_contributing_shift_policy_obj' );
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is too short or too long'),
											2, 50)
				AND
				$this->Validator->isTrue(	'name',
											$this->isUniqueName($name),
											TTi18n::gettext('Name is already in use') )
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$description == ''
				OR $this->Validator->isLength(	'description',
												$description,
												TTi18n::gettext('Description is invalid'),
												1, 250) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultScheduleStatus() {
		if ( isset($this->data['default_schedule_status_id']) ) {
			return (int)$this->data['default_schedule_status_id'];
		}

		return FALSE;
	}
	function setDefaultScheduleStatus($value) {
		$value = trim($value);

		$sf = TTnew( 'ScheduleFactory' );

		$key = Option::getByValue($value, $sf->getOptions('status') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'default_schedule_status',
											$value,
											TTi18n::gettext('Incorrect Default Schedule Status'),
											$sf->getOptions('status')) ) {

			$this->data['default_schedule_status_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getMinimumEmployedDays() {
		if ( isset($this->data['minimum_employed_days']) ) {
			return (int)$this->data['minimum_employed_days'];
		}

		return FALSE;
	}
	function setMinimumEmployedDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_employed_days',
													$int,
													TTi18n::gettext('Incorrect Minimum Employed days')) ) {
			$this->data['minimum_employed_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumWorkedPeriodDays() {
		if ( isset($this->data['minimum_worked_period_days']) ) {
			return (int)$this->data['minimum_worked_period_days'];
		}

		return FALSE;
	}
	function setMinimumWorkedPeriodDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_period_days',
													$int,
													TTi18n::gettext('Incorrect Minimum Worked Period days')) ) {
			$this->data['minimum_worked_period_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumWorkedDays() {
		if ( isset($this->data['minimum_worked_days']) ) {
			return (int)$this->data['minimum_worked_days'];
		}

		return FALSE;
	}
	function setMinimumWorkedDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_days',
													$int,
													TTi18n::gettext('Incorrect Minimum Worked days')) ) {
			$this->data['minimum_worked_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkedScheduledDays() {
		if ( isset($this->data['worked_scheduled_days']) ) {
			return (int)$this->data['worked_scheduled_days'];
		}

		return TRUE;
	}
	function setWorkedScheduledDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_period_days',
													$int,
													TTi18n::gettext('Incorrect Eligibility Type')) ) {
			$this->data['worked_scheduled_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumWorkedAfterPeriodDays() {
		if ( isset($this->data['minimum_worked_after_period_days']) ) {
			return (int)$this->data['minimum_worked_after_period_days'];
		}

		return FALSE;
	}
	function setMinimumWorkedAfterPeriodDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_after_period_days',
													$int,
													TTi18n::gettext('Incorrect Minimum Worked After Period days')) ) {
			$this->data['minimum_worked_after_period_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumWorkedAfterDays() {
		if ( isset($this->data['minimum_worked_after_days']) ) {
			return (int)$this->data['minimum_worked_after_days'];
		}

		return FALSE;
	}
	function setMinimumWorkedAfterDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_after_days',
													$int,
													TTi18n::gettext('Incorrect Minimum Worked After days')) ) {
			$this->data['minimum_worked_after_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkedAfterScheduledDays() {
		if ( isset($this->data['worked_after_scheduled_days']) ) {
			return (int)$this->data['worked_after_scheduled_days'];
		}

		return TRUE;
	}
	function setWorkedAfterScheduledDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_worked_after_period_days',
													$int,
													TTi18n::gettext('Incorrect Eligibility Type')) ) {
			$this->data['worked_after_scheduled_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAverageTimeDays() {
		if ( isset($this->data['average_time_days']) ) {
			return (int)$this->data['average_time_days'];
		}

		return FALSE;
	}
	function setAverageTimeDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'average_time_days',
													$int,
													TTi18n::gettext('Incorrect Days to Total Time over')) ) {
			$this->data['average_time_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	//This is the divisor in the time averaging formula, as some provinces total time over 30 days and divide by 20 days.
	function getAverageDays() {
		if ( isset($this->data['average_days']) ) {
			return (int)$this->data['average_days'];
		}

		return FALSE;
	}
	function setAverageDays($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'average_days',
													$int,
													TTi18n::gettext('Incorrect Days to Average Time over')) ) {
			$this->data['average_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	//If true, uses only worked days to average time over.
	//If false, always uses the above average days to average time over.
	function getAverageTimeWorkedDays() {
		return $this->fromBool( $this->data['average_time_worked_days'] );
	}
	function setAverageTimeWorkedDays($bool) {
		$this->data['average_time_worked_days'] = $this->toBool($bool);

		return TRUE;
	}

	function getMinimumTime() {
		if ( isset($this->data['minimum_time']) ) {
			return (int)$this->data['minimum_time'];
		}

		return FALSE;
	}
	function setMinimumTime($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'minimum_time',
													$int,
													TTi18n::gettext('Incorrect Minimum Time')) ) {
			$this->data['minimum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumTime() {
		if ( isset($this->data['maximum_time']) ) {
			return (int)$this->data['maximum_time'];
		}

		return FALSE;
	}
	function setMaximumTime($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'maximum_time',
													$int,
													TTi18n::gettext('Incorrect Maximum Time')) ) {
			$this->data['maximum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getRoundIntervalPolicyID() {
		if ( isset($this->data['round_interval_policy_id']) ) {
			return (int)$this->data['round_interval_policy_id'];
		}

		return FALSE;
	}
	function setRoundIntervalPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$riplf = TTnew( 'RoundIntervalPolicyListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'round_interval_policy',
													$riplf->getByID($id),
													TTi18n::gettext('Rounding Policy is invalid')
													) ) {

			$this->data['round_interval_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
/*
	function getTime() {
		if ( isset($this->data['time']) ) {
			return (int)$this->data['time'];
		}

		return FALSE;
	}
	function setTime($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'time',
													$int,
													TTi18n::gettext('Incorrect Time')) ) {
			$this->data['time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
*/

	function getEligibleContributingShiftPolicy() {
		if ( isset($this->data['eligible_contributing_shift_policy_id']) ) {
			return (int)$this->data['eligible_contributing_shift_policy_id'];
		}

		return FALSE;
	}
	function setEligibleContributingShiftPolicy($id) {
		$id = trim($id);

		if ( $id == 0 || $id == '' ) {

			$id = NULL;
		}

		$csplf = TTnew( 'ContributingShiftPolicyListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'eligible_contributing_shift_policy_id',
													$csplf->getByID($id),
													TTi18n::gettext('Eligible Contributing Shift Policy is invalid')
													) ) {

			$this->data['eligible_contributing_shift_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getContributingShiftPolicy() {
		if ( isset($this->data['contributing_shift_policy_id']) ) {
			return (int)$this->data['contributing_shift_policy_id'];
		}

		return FALSE;
	}
	function setContributingShiftPolicy($id) {
		$id = trim($id);

		if ( $id == 0 || $id == '' ) {

			$id = NULL;
		}

		$csplf = TTnew( 'ContributingShiftPolicyListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'contributing_shift_policy_id',
													$csplf->getByID($id),
													TTi18n::gettext('Contributing Shift Policy is invalid')
													) ) {

			$this->data['contributing_shift_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Count all paid absence time as worked time.
	function getPaidAbsenceAsWorked() {
		return $this->fromBool( $this->data['paid_absence_as_worked'] );
	}
	function setPaidAbsenceAsWorked($bool) {
		$this->data['paid_absence_as_worked'] = $this->toBool($bool);

		return TRUE;
	}

	//Always applies over time policy even if they are not eligible for the holiday.
	function getForceOverTimePolicy() {
		return $this->fromBool( $this->data['force_over_time_policy'] );
	}
	function setForceOverTimePolicy($bool) {
		$this->data['force_over_time_policy'] = $this->toBool($bool);

		return TRUE;
	}

	function getIncludeOverTime() {
		return $this->fromBool( $this->data['include_over_time'] );
	}
	function setIncludeOverTime($bool) {
		$this->data['include_over_time'] = $this->toBool($bool);

		return TRUE;
	}

	function getIncludePaidAbsenceTime() {
		return $this->fromBool( $this->data['include_paid_absence_time'] );
	}
	function setIncludePaidAbsenceTime($bool) {
		$this->data['include_paid_absence_time'] = $this->toBool($bool);

		return TRUE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return (int)$this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = 0;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy_id',
													$aplf->getByID($id),
													TTi18n::gettext('Absence Policy is invalid')
													) ) {

			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringHoliday() {
		$hprhlf = TTnew( 'HolidayPolicyRecurringHolidayListFactory' );
		$hprhlf->getByHolidayPolicyId( $this->getId() );
		Debug::text('Found Recurring Holidays Attached to this Policy: '. $hprhlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach ($hprhlf as $obj) {
			$list[] = $obj->getRecurringHoliday();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setRecurringHoliday($ids) {
		Debug::text('Setting Recurring Holiday IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if (is_array($ids) AND count($ids) > 0) {
			$tmp_ids = array();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$hprhlf = TTnew( 'HolidayPolicyRecurringHolidayListFactory' );
				$hprhlf->getByHolidayPolicyId( $this->getId() );

				foreach ($hprhlf as $obj) {
					$id = $obj->getRecurringHoliday();
					Debug::text('Policy ID: '. $obj->getHolidayPolicy() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$rhlf = TTnew( 'RecurringHolidayListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) AND $id > 0 ) {
					$hprhf = TTnew( 'HolidayPolicyRecurringHolidayFactory' );
					$hprhf->setHolidayPolicy( $this->getId() );
					$hprhf->setRecurringHoliday( $id );

					$obj = $rhlf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'recurring_holiday',
														$hprhf->Validator->isValid(),
														TTi18n::gettext('Selected Recurring Holiday is invalid').' ('. $obj->getName() .')' )) {
						$hprhf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}


	function Validate() {
		//If we always do this check, it breaks mass editing of holiday policies.
		/*
		if ( $this->isNew() == TRUE AND $this->isSave() == TRUE AND $this->getAbsencePolicyID() == FALSE ) {
			$this->Validator->isTrue(		'absence_policy_id',
											FALSE,
											TTi18n::gettext('Absence Policy is invalid') );
		}
		*/

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
						case 'default_schedule_status':
							$function = 'get'.str_replace('_', '', $variable);
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Holiday Policy'), NULL, $this->getTable(), $this );
	}
}
?>
