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
 * @package Modules\PayPeriod
 */
class PayPeriodScheduleFactory extends Factory {
	protected $table = 'pay_period_schedule';
	protected $pk_sequence_name = 'pay_period_schedule_id_seq'; //PK Sequence name

	protected $create_initial_pay_periods = FALSE;
	protected $enable_create_initial_pay_periods = TRUE;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				/*
				 * Set type to MANUAL to disable PP schedule. Rather then add a status_id field.
				 */
				$retval = array(
											5 => TTi18n::gettext('Manual'),
											10	=> TTi18n::gettext('Weekly (52/year)'),
											20	=> TTi18n::gettext('Bi-Weekly (26/year)'),
											30	=> TTi18n::gettext('Semi-Monthly (24/year)'),
											//40  => TTi18n::gettext('Monthly + Advance'), //Handled with monthly PP schedule and Tax / Deduction to automatically enter advance each month. Advances are paid manually.
											50	=> TTi18n::gettext('Monthly (12/year)'), //Must have this here, for ROEs

											100	=> TTi18n::gettext('Weekly (53/year)'),
											200	=> TTi18n::gettext('Bi-Weekly (27/year)'),
										);
				break;
			case 'start_week_day':
				$retval = array(
											0 => TTi18n::gettext('Sunday-Saturday'),
											1 => TTi18n::gettext('Monday-Sunday'),
											2 => TTi18n::gettext('Tuesday-Monday'),
											3 => TTi18n::gettext('Wednesday-Tuesday'),
											4 => TTi18n::gettext('Thursday-Wednesday'),
											5 => TTi18n::gettext('Friday-Thursday'),
											6 => TTi18n::gettext('Saturday-Friday'),
										);
				break;
			case 'shift_assigned_day':
				$retval = array(
											10 => TTi18n::gettext('Day They Start On'),
											20 => TTi18n::gettext('Day They End On'),
											30 => TTi18n::gettext('Day w/Most Time Worked'),
											40 => TTi18n::gettext('Each Day (Split at Midnight)'),
										);
				break;
			case 'transaction_date':
				for ($i = 1; $i <= 31; $i++) {
					$retval[$i] = $i;
				}
				break;
			case 'transaction_date_business_day':
				$retval = array(
											//Adjust Transaction Date To:
											0 => TTi18n::gettext('No'),
											1 => TTi18n::gettext('Yes - Previous Business Day'),
											2 => TTi18n::gettext('Yes - Next Business Day'),
											3 => TTi18n::gettext('Yes - Closest Business Day'),
										);
				break;
			case 'timesheet_verify_type':
				$retval = array(
											10 => TTi18n::gettext('Disabled'),
											20 => TTi18n::gettext('Employee Only'),
											30 => TTi18n::gettext('Superior Only'),
											40 => TTi18n::gettext('Employee & Superior'),
										);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1030-description' => TTi18n::gettext('Description'),
										'-1040-total_users' => TTi18n::gettext('Employees'),
										'-1050-start_week_day' => TTi18n::gettext('Overtime Week'),
										'-1060-shift_assigned_day' => TTi18n::gettext('Assign Shifts To'),
										'-1070-time_zone' => TTi18n::gettext('TimeZone'),
										'-1080-new_day_trigger_time' => TTi18n::gettext('Minimum Time Off Between Shifts'),
										'-1090-maximum_shift_time' => TTi18n::gettext('Maximum Shift Time'),

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
								'type',
								'name',
								'description',
								'total_users',
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
										'start_week_day_id' => 'StartWeekDay',
										'start_week_day' => FALSE,
										'shift_assigned_day_id' => 'ShiftAssignedDay',
										'shift_assigned_day' => FALSE,
										'name' => 'Name',
										'description' => 'Description',
										'start_day_of_week' => 'StartDayOfWeek',
										'transaction_date' => 'TransactionDate',

										'primary_day_of_month' => 'PrimaryDayOfMonth',
										'secondary_day_of_month' => 'SecondaryDayOfMonth',
										'primary_transaction_day_of_month' => 'PrimaryTransactionDayOfMonth',
										'secondary_transaction_day_of_month' => 'SecondaryTransactionDayOfMonth',

										'transaction_date_bd' => 'TransactionDateBusinessDay',
										'anchor_date' => 'AnchorDate',
										'day_start_time' => 'DayStartTime',
										'time_zone' => 'TimeZone',
										'day_continuous_time' => 'ContinuousTime',
										'new_day_trigger_time' => 'NewDayTriggerTime',
										'maximum_shift_time' => 'MaximumShiftTime',
										'annual_pay_periods' => 'AnnualPayPeriods',
										'timesheet_verify_type_id' => 'TimeSheetVerifyType',
										'timesheet_verify_before_end_date' => 'TimeSheetVerifyBeforeEndDate',
										'timesheet_verify_before_transaction_date' => 'TimeSheetVerifyBeforeTransactionDate',
										//TimeSheet verification email notices are no longer required, as its handled with exceptions now.
										//'timesheet_verify_notice_before_transaction_date' => 'TimeSheetVerifyNoticeBeforeTransactionDate',
										//'timesheet_verify_notice_email' => 'TimeSheetVerifyNoticeEmail',
										'total_users' => FALSE,
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}
		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

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
		//Have to return the KEY because it should always be a drop down box.
		//return Option::getByKey($this->data['status_id'], $this->getOptions('status') );
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getStartWeekDay() {
		if ( isset($this->data['start_week_day_id']) ) {
			return (int)$this->data['start_week_day_id'];
		}

		return FALSE;
	}
	function setStartWeekDay($val) {
		$val = trim($val);

		$key = Option::getByValue($val, $this->getOptions('start_week_day') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_week_day',
											$val,
											TTi18n::gettext('Incorrect Start Week Day'),
											$this->getOptions('start_week_day')) ) {

			$this->data['start_week_day_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getShiftAssignedDay() {
		if ( isset($this->data['shift_assigned_day_id']) ) {
			return (int)$this->data['shift_assigned_day_id'];
		}

		return FALSE;
	}
	function setShiftAssignedDay($val) {
		$val = trim($val);

		if ( $this->Validator->inArrayKey(	'shift_assigned_day_id',
											$val,
											TTi18n::gettext('Incorrect Shift Assigned Day'),
											$this->getOptions('shift_assigned_day')) ) {

			$this->data['shift_assigned_day_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND name = ? AND deleted=0';
		$pay_period_schedule_id = $this->db->GetOne($query, $ph);
		Debug::Arr($pay_period_schedule_id, 'Unique Pay Period Schedule ID: '. $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $pay_period_schedule_id === FALSE ) {
			return TRUE;
		} else {
			if ($pay_period_schedule_id == $this->getId() ) {
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
											TTi18n::gettext('Name is invalid'),
											2, 50)
				AND	$this->Validator->isTrue(	'name',
												$this->isUniqueName($name),
												TTi18n::gettext('Name is already in use')
												)
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
				OR
				$this->Validator->isLength(	'description',
											$description,
											TTi18n::gettext('Description is invalid'),
											2, 255) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getStartDayOfWeek( $raw = FALSE) {
		if ( isset($this->data['start_day_of_week']) ) {
			return $this->data['start_day_of_week'];
		}

		return FALSE;
	}
	function setStartDayOfWeek($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfWeekArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_day_of_week',
											$val,
											TTi18n::gettext('Incorrect start day of week'),
											TTDate::getDayOfWeekArray() ) ) {

			$this->data['start_day_of_week'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionDate() {
		if ( isset($this->data['transaction_date']) ) {
			return $this->data['transaction_date'];
		}

		return FALSE;
	}
	function setTransactionDate($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfWeekArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( $val == 0
				OR $this->Validator->inArrayKey(	'transaction_date',
											$val,
											TTi18n::gettext('Incorrect transaction date'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['transaction_date'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function convertLastDayOfMonth( $val ) {
		if ( $val == -1 ) {
			return 31;
		}

		return $val;
	}

	function getPrimaryDayOfMonth() {
		if ( isset($this->data['primary_day_of_month']) ) {
			return $this->data['primary_day_of_month'];
		}

		return FALSE;
	}
	function setPrimaryDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if (	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'primary_day_of_month',
											$val,
											TTi18n::getText('Incorrect primary day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['primary_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getSecondaryDayOfMonth() {
		if ( isset($this->data['secondary_day_of_month']) ) {
			return $this->data['secondary_day_of_month'];
		}

		return FALSE;
	}
	function setSecondaryDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if (	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'secondary_day_of_month',
											$val,
											TTi18n::gettext('Incorrect secondary day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['secondary_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getPrimaryTransactionDayOfMonth() {
		if ( isset($this->data['primary_transaction_day_of_month']) ) {
			return $this->data['primary_transaction_day_of_month'];
		}

		return FALSE;
	}
	function setPrimaryTransactionDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if (	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'primary_transaction_day_of_month',
											$val,
											TTi18n::gettext('Incorrect primary transaction day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['primary_transaction_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getSecondaryTransactionDayOfMonth() {
		if ( isset($this->data['secondary_transaction_day_of_month']) ) {
			return $this->data['secondary_transaction_day_of_month'];
		}

		return FALSE;
	}
	function setSecondaryTransactionDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if (	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'secondary_transaction_day_of_month',
											$val,
											TTi18n::gettext('Incorrect secondary transaction day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['secondary_transaction_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionDateBusinessDay() {
		if ( isset($this->data['transaction_date_bd']) ) {
			return (int)$this->data['transaction_date_bd'];
		}
		return FALSE;
	}
	function setTransactionDateBusinessDay($int) {
		$int = (int)$int;

		if ( $this->Validator->inArrayKey(	'transaction_date_bd',
											$int,
											TTi18n::gettext('Incorrect transaction date adjustment'),
											$this->getOptions('transaction_date_business_day') ) ) {

			$this->data['transaction_date_bd'] = $int;

			return TRUE;
		}

		return FALSE;
	}
/*
	function getTransactionDateBusinessDay() {
		if ( isset($this->data['transaction_date_bd']) ) {
			return $this->fromBool( $this->data['transaction_date_bd'] );
		}

		return FALSE;
	}
	function setTransactionDateBusinessDay($bool) {
		$this->data['transaction_date_bd'] = $this->toBool($bool);

		return TRUE;
	}
*/
	function getAnchorDate( $raw = FALSE ) {
		if ( isset($this->data['anchor_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['anchor_date'];
			} else {
				return TTDate::strtotime( $this->data['anchor_date'] ); //Need to use TTDate::strtotime so it can return a saved epoch properly when its set and returned without saving inbetween.
			}
		}

		return FALSE;
	}
	function setAnchorDate($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'anchor_date',
												$epoch,
												TTi18n::gettext('Incorrect start date')) ) {

			$this->data['anchor_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getDayStartTime() {
		if ( isset($this->data['day_start_time']) ) {
			return (int)$this->data['day_start_time'];
		}
		return FALSE;
	}
	function setDayStartTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'day_start_time',
													$int,
													TTi18n::gettext('Incorrect day start time')) ) {
			$this->data['day_start_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeZoneOptions() {
		$upf = TTnew( 'UserPreferenceFactory' );

		return $upf->getOptions('time_zone');
	}
	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		if ( $this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											TTi18n::gettext('Incorrect time zone'),
											Misc::trimSortPrefix( $this->getTimeZoneOptions() ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function setOriginalTimeZone() {
		if ( isset($this->original_time_zone) ) {
			return TTDate::setTimeZone( $this->original_time_zone );
		}

		return FALSE;
	}
	function setPayPeriodTimeZone() {
		$this->original_time_zone = TTDate::getTimeZone();

		return TTDate::setTimeZone( $this->getTimeZone() );
	}
/*
	//Continuous time from the first punch of the day to the last
	//So if continuous time is set to 18hrs, and someone punches in for the first time at
	//11pm. All punches from 11pm + 18hrs are considered for the same day.
	function getContinuousTime() {
		if ( isset($this->data['day_continuous_time']) ) {
			return (int)$this->data['day_continuous_time'];
		}
		return FALSE;
	}
	function setContinuousTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'continuous_time',
													$int,
													TTi18n::gettext('Incorrect continuous time')) ) {
			$this->data['day_continuous_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
*/
	//
	// Instead of daily continuous time, use minimum time-off between shifts that triggers a new day to start.
	//
	function getNewDayTriggerTime() {
		if ( isset($this->data['new_day_trigger_time']) ) {
			return (int)$this->data['new_day_trigger_time'];
		}
		return FALSE;
	}
	function setNewDayTriggerTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'new_day_trigger_time',
													$int,
													TTi18n::gettext('Incorrect Minimum Time-Off Between Shifts')) ) {
			$this->data['new_day_trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumShiftTime() {
		if ( isset($this->data['maximum_shift_time']) ) {
			return (int)$this->data['maximum_shift_time'];
		}
		return FALSE;
	}
	function setMaximumShiftTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'maximum_shift_time',
													$int,
													TTi18n::gettext('Incorrect Maximum Shift Time'))
				AND
				$this->Validator->isLessThan(		'maximum_shift_time',
													$int,
													TTi18n::gettext('Maximum Shift Time is too long'),
													691200 )
				AND
				$this->Validator->isGreaterThan(	'maximum_shift_time',
													$int,
													TTi18n::gettext('Maximum Shift Time is too short'),
													14400 )
			) {
			$this->data['maximum_shift_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAnnualPayPeriods() {
		if ( isset($this->data['annual_pay_periods']) ) {
			return (int)$this->data['annual_pay_periods'];
		}
		return FALSE;
	}
	function setAnnualPayPeriods($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'annual_pay_periods',
													$int,
													TTi18n::gettext('Incorrect Annual Pay Periods')) ) {
			$this->data['annual_pay_periods'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyType() {
		if ( isset($this->data['timesheet_verify_type_id']) ) {
			return (int)$this->data['timesheet_verify_type_id'];
		}

		return TRUE;
	}
	function setTimeSheetVerifyType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'timesheet_verify_type_id',
											$type,
											TTi18n::gettext('Incorrect TimeSheet Verification Type'),
											$this->getOptions('timesheet_verify_type')) ) {

			$this->data['timesheet_verify_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyBeforeEndDate() {
		if ( isset($this->data['timesheet_verify_before_end_date']) ) {
			return Misc::removeTrailingZeros( round( TTDate::getDays( (int)$this->data['timesheet_verify_before_end_date'] ), 3 ), 0 );
		}
		return FALSE;
	}
	function setTimeSheetVerifyBeforeEndDate($int) {
		$int = (float)$int; // Do not cast to INT, need to support partial days.

		if	(	$this->Validator->isNumeric(		'timesheet_verify_before_end_date',
													$int,
													TTi18n::gettext('Incorrect value for timesheet verification before/after end date')) ) {
			$this->data['timesheet_verify_before_end_date'] = ($int * 86400);

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyBeforeTransactionDate() {
		if ( isset($this->data['timesheet_verify_before_transaction_date']) ) {
			return Misc::removeTrailingZeros( round( TTDate::getDays( (int)$this->data['timesheet_verify_before_transaction_date'] ), 3 ), 0 );
		}
		return FALSE;
	}
	function setTimeSheetVerifyBeforeTransactionDate($int) {
		$int = (float)$int; // Do not cast to INT, need to support partial days.

		if	(	$this->Validator->isNumeric(		'timesheet_verify_before_transaction_date',
													$int,
													TTi18n::gettext('Incorrect value for timesheet verification before/after transaction date')) ) {
			$this->data['timesheet_verify_before_transaction_date'] = ($int * 86400); //Convert to seconds to support partial days. Do not cast to INT!

			return TRUE;
		}

		return FALSE;
	}

	//Notices are no longer required with TimeSheet not verified exception.
	function getTimeSheetVerifyNoticeBeforeTransactionDate() {
		if ( isset($this->data['timesheet_verify_notice_before_transaction_date']) ) {
			return (int)$this->data['timesheet_verify_notice_before_transaction_date'];
		}
		return FALSE;
	}
	function setTimeSheetVerifyNoticeBeforeTransactionDate($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'timesheet_verify_notice_before_transaction_date',
													$int,
													TTi18n::gettext('Incorrect value for timesheet verification notice before/after transaction date')) ) {
			$this->data['timesheet_verify_notice_before_transaction_date'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyNoticeEmail() {
		if ( isset($this->data['timesheet_verify_notice_email']) ) {
			return $this->fromBool( $this->data['timesheet_verify_notice_email'] );
		}

		return FALSE;
	}
	function setTimeSheetVerifyNoticeEmail($bool) {
		$this->data['timesheet_verify_notice_email'] = $this->toBool($bool);

		return TRUE;
	}

	function getUser() {
		$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
		$ppsulf->getByPayPeriodScheduleId( $this->getId() );
		foreach ($ppsulf as $pay_period_schedule) {
			$user_list[] = $pay_period_schedule->getUser();
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return FALSE;
	}
	function setUser($ids) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
				$ppsulf->getByPayPeriodScheduleId( $this->getId() );

				$user_ids = array();
				foreach ($ppsulf as $pay_period_schedule) {
					$user_id = $pay_period_schedule->getUser();
					Debug::text('Schedule ID: '. $pay_period_schedule->getPayPeriodSchedule() .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($user_id, $ids) ) {
						Debug::text('Deleting User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
						$pay_period_schedule->Delete();
					} else {
						//Save user ID's that need to be updated.
						Debug::text('NOT Deleting User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
						$user_ids[] = $user_id;
					}
				}
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' );

			foreach ($ids as $id) {
				if ( $id != '' AND isset($user_ids) AND !in_array($id, $user_ids) ) {
					$ppsuf = TTnew( 'PayPeriodScheduleUserFactory' );
					$ppsuf->setPayPeriodSchedule( $this->getId() );
					$ppsuf->setUser( $id );

					$user_obj = $ulf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'user',
														$ppsuf->Validator->isValid(),
														TTi18n::gettext('Selected Employee is already assigned to another Pay Period').' ('. $user_obj->getFullName() .')' )) {
						$ppsuf->save();
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionBusinessDay( $epoch ) {
		Debug::Text('Epoch: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		$holiday_epochs = array();

		$user_ids = $this->getUser();

		if ( count($user_ids) > 0 ) {
			$hlf = TTnew( 'HolidayListFactory' );
			$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $user_ids, ( $epoch - (86400 * 14) ), ( $epoch + (86400 * 2) ) );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach( $hlf as $h_obj ) {
					Debug::Text('Found Holiday Epoch: '. TTDate::getDate('DATE+TIME', $h_obj->getDateStamp() ) .' Name: '. $h_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
					$holiday_epochs[] = $h_obj->getDateStamp();
				}

				//Debug::Arr($holiday_epochs, 'Holiday Epochs: ', __FILE__, __LINE__, __METHOD__, 10);
			}

			$epoch = TTDate::getNearestWeekDay( $epoch, $this->getTransactionDateBusinessDay(), $holiday_epochs );
		}

		return $epoch;
	}

	function getNextPayPeriod($end_date = NULL) {
		if ( !$this->Validator->isValid() ) {
			return FALSE;
		}

		//Manual Pay Period Schedule, skip repeating...
		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		$pplf = TTnew( 'PayPeriodListFactory' );

		//Debug::text('PP Schedule ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('PP Schedule Name: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('PP Schedule Type ('.$this->getType().'): '. Option::getByKey($this->getType(), $this->getOptions('type') ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Anchor Date: '. $this->getAnchorDate() ." - ". TTDate::getDate('DATE+TIME', $this->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Primary Date: '. $this->getPrimaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Secondary Date: '. $this->getSecondaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);

		$last_pay_period_is_new = FALSE;
		if ( $end_date != '' AND $end_date != 0 ) {
			Debug::text('End Date is set: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
			$last_pay_period_end_date = $end_date;
		} else {
			Debug::text('Checking for Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10);
			//Get the last pay period schedule in the database.
			$pplf->getByPayPeriodScheduleId( $this->getId(), NULL, NULL, NULL, array('start_date' => 'desc') );
			$last_pay_period = $pplf->getCurrent();
			if ( $last_pay_period->isNew() ) {
				$last_pay_period_is_new = TRUE;

				Debug::text('No Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10);

				//Do this so a rollover doesn't happen while we're calculating.
				//$last_pay_period_end_date = TTDate::getTime();
				//This causes the pay period schedule to jump ahead one month. So set this to be beginning of the month.
				$last_pay_period_end_date = TTDate::getBeginMonthEpoch();
			} else {
				Debug::text('Previous pay periods found... ID: '. $last_pay_period->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$last_pay_period_end_date = $last_pay_period->getEndDate();
			}
			unset($last_pay_period, $pplf);
		}
		Debug::text('aLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

		//FIXME: This breaks having pay periods with different daily start times.
		//However, without it, I think DST breaks pay periods.
		//$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date + 1 ) - 86400;
		$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date - (86400 / 2) );
		Debug::text('bLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

		/*
		//This function isn't support currently, so skip it.
		if ( $this->getDayStartTime() != 0 ) {
			Debug::text('Daily Start Time is set, adjusting Last Pay Period End Date by: '. TTDate::getHours( $this->getDayStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
			//Next adjust last_pay_period_end_date (which becomes the start date) to DayStartTime because then there could be a gap if they
			//change this mid-schedule. The End Date will take care of it after the first pay period.
			$last_pay_period_end_date = TTDate::getTimeLockedDate( TTDate::getBeginDayEpoch($last_pay_period_end_date) + $this->getDayStartTime(), $last_pay_period_end_date);
			Debug::text('cLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		$insert_pay_period = 1; //deprecate primary pay periods.
		switch ( $this->getType() ) {
			case 10: //Weekly
			case 100: //Weekly (53)
			case 20: //Bi-Weekly
			case 200: //Bi-Weekly (27)
				$last_pay_period_end_day_of_week = TTDate::getDayOfWeek( $last_pay_period_end_date );
				Debug::text('Last Pay Period End Day Of Week: '. $last_pay_period_end_day_of_week .' Start Day Of Week: '. $this->getStartDayOfWeek(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $last_pay_period_end_day_of_week != $this->getStartDayOfWeek() ) {
					Debug::text('zTmp Pay Period End Date: '. 'next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), __FILE__, __LINE__, __METHOD__, 10);
					//$tmp_pay_period_end_date = strtotime('next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), $last_pay_period_end_date )-1;
					$tmp_pay_period_end_date = strtotime('next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek(), FALSE ), $last_pay_period_end_date );

					//strtotime doesn't keep time when using "next", it resets it to midnight on the day, so we need to adjust for that.
					$tmp_pay_period_end_date = ( TTDate::getTimeLockedDate( ( TTDate::getBeginDayEpoch($tmp_pay_period_end_date) + $this->getDayStartTime() ), $tmp_pay_period_end_date ) - 1 );
				} else {
					$tmp_pay_period_end_date = $last_pay_period_end_date;

					//This should fix a bug where if they are creating a new pay period schedule
					//starting on Monday with the anchor date of 01-Jul-08, it would start on 01-Jul-08 (Tue)
					//rather moving back to the Monday.
					if ( TTDate::getDayOfMonth( $tmp_pay_period_end_date ) != TTDate::getDayOfMonth( $tmp_pay_period_end_date + 1 ) ) {
						Debug::text('Right on day boundary, minus an additional second to account for difference...', __FILE__, __LINE__, __METHOD__, 10);
						$tmp_pay_period_end_date--;
					}
				}
				Debug::text('aTmp Pay Period End Date: '. TTDate::getDate('DATE+TIME', $tmp_pay_period_end_date) .' ('.$tmp_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

				$start_date = ( $tmp_pay_period_end_date + 1 );

				if ( $this->getType() == 10 OR $this->getType() == 100 ) { //Weekly
					$tmp_pay_period_end_date = ( TTDate::getMiddleDayEpoch($start_date) + (86400 * 7) ); //Add one week
				} elseif ( $this->getType() == 20 OR $this->getType() == 200 ) { //Bi-Weekly
					$tmp_pay_period_end_date = ( TTDate::getMiddleDayEpoch($start_date) + (86400 * 14) ); //Add two weeks
				}

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = ( TTDate::getBeginDayEpoch( $tmp_pay_period_end_date ) - 1 );
				$transaction_date = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch($end_date) + ($this->getTransactionDate() * 86400) ) );

				break;
			case 30: //Semi-monthly
				$tmp_last_pay_period_end_day_of_month = TTDate::getDayOfMonth( $last_pay_period_end_date + 1 );
				Debug::text('bLast Pay Period End Day Of Month: '. $tmp_last_pay_period_end_day_of_month, __FILE__, __LINE__, __METHOD__, 10);

				if ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) {
					$insert_pay_period = 1;
					$primary = TRUE;
				} elseif ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) {
					$insert_pay_period = 2;
					$primary = FALSE;
				} else {
					Debug::text('Finding if Primary or Secondary is closest...', __FILE__, __LINE__, __METHOD__, 10);

					$primary_date_offset = ( TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) - $last_pay_period_end_date );
					$secondary_date_offset = ( TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) - $last_pay_period_end_date );
					Debug::text('Primary Date Offset: '. TTDate::getDays( $primary_date_offset ) .' Secondary Date Offset: '. TTDate::getDays( $secondary_date_offset ), __FILE__, __LINE__, __METHOD__, 10);

					if ( $primary_date_offset <= $secondary_date_offset ) {
						$insert_pay_period = 1;
						$primary = TRUE;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
					} else {
						$insert_pay_period = 2;
						$primary = FALSE;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) );
					}
					$last_pay_period_end_date = ( TTDate::getBeginDayEpoch( $last_pay_period_end_date ) - 1 );
				}
				unset($tmp_last_pay_period_end_day_of_month);
				Debug::text('cLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .') Primary: '. (int)$primary, __FILE__, __LINE__, __METHOD__, 10);

				$start_date = ( $last_pay_period_end_date + 1 );

				if ( $primary == TRUE ) {
					$end_date = ( TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) ) - 1 );
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch($end_date), NULL, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );
				} else {
					$end_date = ( TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) ) - 1 );
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch($end_date), NULL, $this->convertLastDayOfMonth( $this->getSecondaryTransactionDayOfMonth() ) ) );
				}

				break;
			case 50: //Monthly
				$start_date = ( $last_pay_period_end_date + 1 );

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = TTDate::getDateOfNextDayOfMonth( TTDate::getBeginDayEpoch( $start_date + (86400 + 3600) ), NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
				$end_date = ( TTDate::getBeginDayEpoch( TTDate::getBeginMinuteEpoch($end_date) ) - 1 );

				$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( $end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );

				break;
		}

		if (  $this->getDayStartTime() != 0 ) {
			Debug::text('Daily Start Time is set, adjusting End Date by: '. TTDate::getHours( $this->getDayStartTime() ) .' Start Date: '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);

			//We already account for DayStartTime in weekly/bi-weekly start_date cases above, so skip applying it again here.
			if ( $this->getType() != 10 AND $this->getType() != 20 ) {
				$start_date = ( $start_date + $this->getDayStartTime() );
			}
			$end_date = ( $end_date + $this->getDayStartTime() );

			//Need to do this, otherwise transaction date could be earlier then end date.
			$transaction_date = ( $transaction_date + $this->getDayStartTime() );
		}

		Debug::text('aStart Date('. $start_date .'): '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('aEnd Date('. $end_date .'): '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('aPay Date('. $transaction_date .'): '. TTDate::getDate('DATE+TIME', $transaction_date), __FILE__, __LINE__, __METHOD__, 10);


		//Handle last day of the month flag for primary and secondary dates here
		if ( ( $this->getType() == 30
					AND (
						( $insert_pay_period == 1
						AND ( $this->getPrimaryDayOfMonth() == 31
							OR $this->getPrimaryDayOfMonth() == -1 )
						)
						OR ( $insert_pay_period == 2
							AND ( $this->getSecondaryDayOfMonth() == 31
								OR $this->getSecondaryDayOfMonth() == -1 )
							)
						)
			)
			OR
			(
				$this->getType() == 50 AND ( $this->getPrimaryDayOfMonth() == 31 OR $this->getPrimaryDayOfMonth() == -1 )
			) ) {

			Debug::text('Last day of the month set for start date: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getDayStartTime() > 0 ) {
				//Minus one day, THEN add daily start time, otherwise it will go past the month boundary
				$end_date = ( ( TTDate::getEndMonthEpoch($end_date) - 86400 ) + $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			} else {
				$end_date = ( TTDate::getEndMonthEpoch($end_date) + $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			}
		}

		//Handle "last day of the month" for transaction dates.
		if ( $this->getPrimaryDayOfMonth() == 31 OR $this->getPrimaryDayOfMonth() == -1 ) {
			//Debug::text('LDOM set for Primary: ', __FILE__, __LINE__, __METHOD__, 10);
			$transaction_date = TTDate::getEndMonthEpoch($transaction_date);
		}

		//Handle "always business day" flag for transaction dates here.
		if ( $this->getTransactionDateBusinessDay() == TRUE ) {
			$transaction_date = $this->getTransactionBusinessDay($transaction_date);
		}

		if ( $transaction_date < $end_date ) {
			$transaction_date = $end_date;
		}

		Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Pay Date: '. TTDate::getDate('DATE+TIME', $transaction_date), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text("<br><br>\n\n", __FILE__, __LINE__, __METHOD__, 10);

		$this->next_start_date = $start_date;
		$this->next_end_date = $end_date;
		$this->next_transaction_date = $transaction_date;

		//Its a primary pay period
		if ($insert_pay_period == 1) {
			$this->next_primary = TRUE;
		} else {
			$this->next_primary = FALSE;
		}

		return TRUE;
	}

	function createNextPayPeriod($end_date = NULL, $offset = NULL, $enable_import_data = TRUE ) {
		if ( $end_date == NULL OR $end_date == '' ) {
			$end_date = NULL;
		}

		if ( $offset == NULL OR $offset == '' ) {
			$offset = 86400; //24hrs
		}

		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		Debug::text('Current TimeZone: '. TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);
		//Handle timezones in this function rather then getNextPayPeriod()
		//Because if we set the timezone back to the original in that function, it
		//gets written to the database in the "original" timezone, not the proper timezone.
		$this->setPayPeriodTimeZone();
		Debug::text('Pay Period TimeZone: '. TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);

		Debug::text('End Date ('. $end_date.'): '. TTDate::getDate('DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10);

		$this->getNextPayPeriod($end_date);

		Debug::text('Next pay period starts: '. TTDate::getDate('DATE+TIME', $this->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//If the start date is within 24hrs of now, insert the next pay period.
		if ( $this->getNextStartDate() <= ( TTDate::getTime() + $offset ) ) {
			Debug::text('Insert new pay period. Start Date: '. $this->getNextStartDate() .' End Date: '. $this->getNextEndDate(), __FILE__, __LINE__, __METHOD__, 10);

			$ppf = TTnew( 'PayPeriodFactory' );
			$ppf->setCompany( $this->getCompany() );
			$ppf->setPayPeriodSchedule( $this->getId() );
			$ppf->setStatus(10);
			$ppf->setStartDate( $this->getNextStartDate() );
			$ppf->setEndDate( $this->getNextEndDate() );
			$ppf->setTransactionDate( $this->getNextTransactionDate() );

			$ppf->setPrimary( $this->getNextPrimary() );
			$ppf->setEnableImportData( $enable_import_data ); //Import punches when creating new pay periods.
			if ( $ppf->isValid() ) {
				$new_pay_period_id = $ppf->Save();
				Debug::text('New Pay Period ID: '. $new_pay_period_id, __FILE__, __LINE__, __METHOD__, 10);

				if ( $new_pay_period_id != '' ) {
					$this->setOriginalTimeZone();

					return TRUE;
				} else {
					Debug::text('aSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('bSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__, 10);
			}

		} else {
			Debug::text('***NOT inserting or changing status of new pay period yet, not within offset.', __FILE__, __LINE__, __METHOD__, 10);
		}

		$this->setOriginalTimeZone();

		return FALSE;
	}


	function getNextStartDate() {
		if ( isset($this->next_start_date) ) {
			return $this->next_start_date;
		}

		return FALSE;
	}

	function getNextEndDate() {
		if ( isset($this->next_end_date) ) {
			return $this->next_end_date;
		}

		return FALSE;
	}

	function getNextTransactionDate() {
		if ( isset($this->next_transaction_date) ) {
			return $this->next_transaction_date;
		}

		return FALSE;
	}

	function getNextAdvanceEndDate() {
		if ( isset($this->next_advance_end_date) ) {
			return $this->next_advance_end_date;
		}

		return FALSE;
	}

	function getNextAdvanceTransactionDate() {
		if ( isset($this->next_advance_transaction_date) ) {
			return $this->next_advance_transaction_date;
		}

		return FALSE;
	}

	function getNextPrimary() {
		if ( isset($this->next_primary) ) {
			return $this->next_primary;
		}

		return FALSE;
	}

	//Pay period number functionality is deprecated, it causes too many problems
	//for little or no benefit. Its also impossible to properly handle in custom situations where pay periods
	//may be adjusted.
	function getCurrentPayPeriodNumber($epoch = NULL, $end_date_epoch = NULL) {
		//EPOCH MUST BE TRANSACTION DATE!!!
		//End Date Epoch must be END DATE of pay period

		//Don't return pay period number if its a manual schedule.
		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		//FIXME: Turn this query in to a straight count(*) query for even more speed.
		if ($epoch == NULL OR $epoch == '') {
			$epoch = TTDate::getTime();
		}
		//Debug::text('Epoch: '. TTDate::getDate('DATE+TIME', $epoch) .' - End Date Epoch: '. TTDate::getDate('DATE+TIME', $end_date_epoch), __FILE__, __LINE__, __METHOD__, 10);

/*
		//FIXME: If a company starts with TimeTrex half way through the year, this will be incorrect.
		//Because it only counts pay periods that exist, not pay periods that WOULD have existed.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate( $this->getId(), TTDate::getBeginYearEpoch( $epoch ), $epoch );
		$retval = $pplf->getRecordCount();

		Debug::text('Current Pay Period: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
*/


		//Half Fixed method here. We cache the results so to speed it up, but there still might be a faster way to do this.
		//FIXME: Perhaps some type of hybrid system like the above unless they have less then a years worth of
		//pay periods, then use this method below?
		$id = $this->getId().$epoch.$end_date_epoch;

		$retval = $this->getCache($id);

		if ( $retval === FALSE ) {
			//FIXME: I'm sure there is a quicker way to do this.
			$next_transaction_date = 0;
			$next_end_date = $end_date_epoch;
			$end_year_epoch = TTDate::getEndYearEpoch( $epoch );
			$i = 0;

			while ( $next_transaction_date <= $end_year_epoch AND $i < 100 ) {
				//Debug::text('I: '. $i .' Looping: Transaction Date: '. TTDate::getDate('DATE+TIME', $next_transaction_date) .' - End Year Epoch: '. TTDate::getDate('DATE+TIME', $end_year_epoch), __FILE__, __LINE__, __METHOD__, 10);
				$this->getNextPayPeriod( $next_end_date );

				$next_transaction_date = $this->getNextTransactionDate();
				$next_end_date = $this->getNextEndDate();

				if ( $next_transaction_date <= $end_year_epoch ) {
					$i++;
				}
			}

			Debug::text('i: '. $i, __FILE__, __LINE__, __METHOD__, 10);

			$retval = ( $this->getAnnualPayPeriods() - $i );
			Debug::text('Current Pay Period: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

			//Cache results
			$this->saveCache($retval, $id);
		}

		return $retval;
	}

	function calcAnnualPayPeriods( $type_id = NULL ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		switch ( $type_id ) {
			case 5:
				//We need the annual number of pay periods calculated for manual pay period schedules if we
				//are to have any hope of calculating taxes correctly.
				//Get all the pay periods, take the first day, last day, and the total number to figure out an average
				//number of days per period.
				//Alternatively have them manually specify the number, but this required adding a field to the table.
				$retval = FALSE;

				if ( $this->getId() > 0 ) {
					$pplf = TTnew( 'PayPeriodListFactory' );
					$retarr = $pplf->getFirstStartDateAndLastEndDateByPayPeriodScheduleId( $this->getId() );
					if ( is_array($retarr) AND isset($retarr['first_start_date']) AND isset($retarr['last_end_date']) ) {
						$retarr['first_start_date'] = TTDate::strtotime( $retarr['first_start_date'] );
						$retarr['last_end_date'] = TTDate::strtotime( $retarr['last_end_date'] );

						$days_per_period = ( ( ( $retarr['last_end_date'] - $retarr['first_start_date'] ) / $retarr['total']) / 86400 );
						$retval = floor(365 / round( $days_per_period ) );
						Debug::text('First Start Date: '. TTDate::getDate('DATE+TIME', $retarr['first_start_date']) .' Last End Date: '. TTDate::getDate('DATE+TIME', $retarr['last_end_date']) .' Total PP: '. $retarr['total'] .' Average Days/Period: '. $days_per_period .'('. round($days_per_period).') Annual Pay Periods: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
					}
					unset($pplf, $retarr);
				}

				break;
			case 10:
				//Needs to take into account years, where 53 weeks may occur.
				//Will need to get the day weeks start on and the year for this to calculate properly.
				//I believe 2015 is the next time this will occur.
				//Not sure if we can automatically handle this as holidays could push the pay period transaction date forward/backwards
				//depending on what the employer may want to do. In addition to that it could be a last minute change, and this may affect
				//salary calculations which could catch them off guard too.
				$retval = 52;
				break;
			case 100:
				$retval = 53;
				break;
			case 20:
				$retval = 26;
				break;
			case 200:
				$retval = 27;
				break;
			case 30:
				$retval = 24; //Semi-monthly
				break;
			case 40:
				$retval = 12; //Monthly + advance, deductions only once per month
				break;
			case 50:
				$retval = 12;
				break;
			default:
				return FALSE;
				break;
		}

		return $retval;
	}

	//Given a single start date and the type of pay period schedule, try to determine all other dates.
	function detectPayPeriodScheduleDates( $type_id, $start_date ) {
		$retarr = FALSE;

		$max = 4;

		$start_date = TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $start_date ) ); //Handle DST by using middle day epoch.

		switch ( $type_id ) {
			case 5: //Manual
				break;
			case 10: //Weekly
				for( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + (86400 * 6) );
					$transaction_date = ( $end_date + (86400 * 5) );

					$retarr[] = array(
										'start_date' => TTDate::getDate('DATE', $start_date ),
										'end_date' => TTDate::getDate('DATE', $end_date ),
										'transaction_date' => TTDate::getDate('DATE', $transaction_date ),
									);

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 20: //BiWeekly
				for( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + (86400 * 13) );
					$transaction_date = ( $end_date + (86400 * 5) );

					$retarr[] = array(
										'start_date' => TTDate::getDate('DATE', $start_date ),
										'end_date' => TTDate::getDate('DATE', $end_date ),
										'transaction_date' => TTDate::getDate('DATE', $transaction_date ),
									);

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 30: //Semi-monthly
				for( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + (86400 * 14) );

					//If we're within 4 days of the last day of the month, use the last day.
					if ( abs( ( $end_date - TTDate::getEndMonthEpoch( $start_date ) ) ) < (86400 * 4) ) {
						$end_date = TTDate::getEndMonthEpoch( $start_date );
					}
					$transaction_date = $end_date;

					$retarr[] = array(
										'start_date' => TTDate::getDate('DATE', $start_date ),
										'end_date' => TTDate::getDate('DATE', $end_date ),
										'transaction_date' => TTDate::getDate('DATE', $transaction_date ),
									);

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 50: //Monthly
				for( $i = 0; $i < $max; $i++ ) {
					$end_date = TTDate::getEndMonthEpoch( $start_date );
					$transaction_date = ( $end_date + ( 86400 * 0) );

					$retarr[] = array(
										'start_date' => TTDate::getDate('DATE', $start_date ),
										'end_date' => TTDate::getDate('DATE', $end_date ),
										'transaction_date' => TTDate::getDate('DATE', $transaction_date ),
									);

					$start_date = ( $end_date + 86400 );
				}
				break;
			default:
				return FALSE;
				break;
		}

		return $retarr;
	}

	//This function given the pay period schedule type and example dates will attempt to determine the pay period schedule settings.
	//This will automatically configure the current object to be saved.
	//Base create initial pay period functionality on the first pay period date, otherwise we need additional data for bi-weekly pay periods.
	function detectPayPeriodScheduleSettings( $type_id, $example_dates ) {
		Debug::Arr($example_dates, 'Pay Period Type: '. $type_id .' Example Dates: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		if ( !is_array($example_dates) ) {
			$example_dates = array();
		}

		if ( !isset($example_dates[0]['start_date']) OR (isset($example_dates[0]['start_date']) AND $example_dates[0]['start_date'] == '' ) ) {
			Debug::Text('Example dates not specified properly, skipping...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$this->setType( $type_id );
		if ( isset($example_dates[0]['start_date']) ) {
			$this->setAnchorDate( ( TTDate::parseDateTime($example_dates[0]['start_date']) - 86400 ) ); //Anchor date one day before first start date.
		}

		$annual_pay_periods = $this->calcAnnualPayPeriods( $type_id );
		switch ( $type_id ) {
			case 5: //Manual
				break;
			case 10: //Weekly
			case 100: //Weekly (53)
			case 20: //BiWeekly
			case 200: //BiWeekly (27)
				//Need at least one example.
				foreach( $example_dates as $example_date ) {
					$start_dow[] = TTDate::getDayOfWeek( TTDate::parseDateTime( $example_date['start_date'] ) );
					$transaction_days[] = (int)round( TTDate::getDays( ( TTDate::parseDateTime( $example_date['transaction_date'] ) - TTDate::parseDateTime( $example_date['end_date'] ) ) ) );
				}
				Debug::Arr($start_dow, 'Start DOW: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Arr($transaction_days, 'Transaction Days: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get the most common values from arrays.
				$start_day_of_week = Misc::arrayCommonValue( $start_dow );
				Debug::Arr($start_dow, 'Start Day Of Week: '. $start_day_of_week .' Start DOW Count: ', __FILE__, __LINE__, __METHOD__, 10);

				$transaction_date = Misc::arrayCommonValue( $transaction_days );
				Debug::Arr($transaction_days, 'Transaction Date: '. $transaction_date .' Transaction Days Count: ', __FILE__, __LINE__, __METHOD__, 10);

				$this->setStartDayOfWeek( $start_day_of_week );
				$this->setTransactionDate( $transaction_date );

				break;
			case 30: //Semi-monthly
				//Need at least three examples?
				$i = 0;
				foreach( $example_dates as $example_date ) {
					if ( ($i % 2) == 0 ) {
						$primary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
						$primary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
					} else {
						$secondary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
						$secondary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
					}

					$i++;
				}
				Debug::Arr($primary_start_dom, 'Primary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Arr($primary_transaction_dom, 'Primary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Arr($secondary_start_dom, 'Secondary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Arr($secondary_transaction_dom, 'Secondary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10);

				$primary_dom = Misc::arrayCommonValue( $primary_start_dom );
				$primary_transaction_dom = Misc::arrayCommonValue( $primary_transaction_dom );
				$secondary_dom = Misc::arrayCommonValue( $secondary_start_dom );
				$secondary_transaction_dom = Misc::arrayCommonValue( $secondary_transaction_dom );
				Debug::Text('Primary: '. $primary_dom .' Trans: '. $primary_transaction_dom .' Secondary: '. $secondary_dom .' Trans: '. $secondary_transaction_dom, __FILE__, __LINE__, __METHOD__, 10);

				$this->setPrimaryDayOfMonth($primary_dom);
				$this->setSecondaryDayOfMonth($secondary_dom);
				$this->setPrimaryTransactionDayOfMonth($primary_transaction_dom);
				$this->setSecondaryTransactionDayOfMonth($secondary_transaction_dom);
				break;
			case 50: //Monthly
				//Need at least one example.
				foreach( $example_dates as $example_date ) {
					$primary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
					$primary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
				}
				Debug::Arr($primary_start_dom, 'Primary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::Arr($primary_transaction_dom, 'Primary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10);
				$primary_dom = Misc::arrayCommonValue( $primary_start_dom );
				$primary_transaction_dom = Misc::arrayCommonValue( $primary_transaction_dom );

				$this->setPrimaryDayOfMonth($primary_dom);
				$this->setPrimaryTransactionDayOfMonth($primary_transaction_dom);
				break;
			default:
				return FALSE;
				break;
		}

		Debug::Arr($this->data, 'PP Schedule Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	static function getShiftAssignedDate( $start_epoch, $end_epoch, $shift_assigned_day_id = FALSE ) {
		if ( $shift_assigned_day_id == '' AND isset($this) ) {
			$shift_assigned_day_id = $this->getShiftAssignedDay();
		}

		//
		//FIXME: During testing always force start_date which is existing behaivor.
		//		 Once this is tested more it can be changed.
		return $start_epoch;

		switch ( $shift_assigned_day_id ) {
			default:
			case 10: //Day they start on
			case 40: //Split at midnight
				//Debug::Text('Assign Shifts to the day they START on... Date: '. TTDate::getDate('DATE', $start_epoch ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $start_epoch;
				break;
			case 20: //Day they end on
				//Debug::Text('Assign Shifts to the day they END on... Date: '. TTDate::getDate('DATE', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $end_epoch;
				break;
			case 30: //Day with most time worked
				$day_with_most_time = TTDate::getDayWithMostTime( $start_epoch, $end_epoch );
				//Debug::Text('Assign Shifts to the day they WORK MOST on... Date: '. TTDate::getDate('DATE', $day_with_most_time ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $day_with_most_time;
				break;
		}

		return $retval;
	}

	//Returns shift data according to the pay period schedule criteria for use
	//in determining which day punches belong to.
	function getShiftData( $date_stamp = NULL, $user_id = NULL, $epoch = NULL, $filter = NULL, $tmp_punch_control_obj = NULL, $maximum_shift_time = NULL, $new_shift_trigger_time = NULL, $plf = NULL ) {
		global $profiler;
		$profiler->startTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		if ( is_numeric($date_stamp) AND $date_stamp > 0 ) {
			$epoch = NULL;
		}

		if ( $date_stamp == '' AND $user_id == '' AND $epoch == '' ) {
			return FALSE;
		}

		if ( $maximum_shift_time === NULL ) {
			$maximum_shift_time = $this->getMaximumShiftTime();
		}

		//Debug::text('User Date ID: '. $user_date_id .' User ID: '. $user_id .' TimeStamp: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $new_shift_trigger_time === NULL ) {
			$new_shift_trigger_time = $this->getNewDayTriggerTime();
		}

		if ( !is_object( $plf ) ) {
			$plf = TTnew( 'PunchListFactory' );
			if ( $date_stamp != '' ) {
				$plf->getByUserIdAndDateStamp( $user_id, $date_stamp );
			} else {
				//Get punches by time stamp.
				$punch_control_id = 0;
				if ( is_object( $tmp_punch_control_obj ) ) {
					$punch_control_id = $tmp_punch_control_obj->getId();
				}

				//We need to double the maximum shift time when searching for punches.
				//Assuming a maximum punch time of 14hrs:
				// In: 10:00AM Out: 2:00PM
				// In: 6:00PM Out: 6:00AM (next day)
				// The above scenario when adding the last 6:00AM punch on the next day will only look back 14hrs and not find the first
				// punch pair, therefore allowing more than 14hrs on the same day.
				// So we need to extend the maximum shift time just when searching for punches and let getShiftData() sort out the proper maximum shift time itself.
				$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch, $punch_control_id, ( $maximum_shift_time * 2 ) );
				unset($punch_control_id);
			}
		}

		Debug::text('Punch Rows: '. $plf->getRecordCount() .' UserID: '. $user_id .' Date: '. TTDate::getDate('DATE+TIME', $epoch) .'('.$epoch.') MaximumShiftTime: '. $maximum_shift_time .' NewShiftTrigger: '. $new_shift_trigger_time .' Filter: '. $filter, __FILE__, __LINE__, __METHOD__, 10);
		if ( $plf->getRecordCount() > 0 ) {
			$shift = 0;
			$i = 0;
			$x = 0;
			$nearest_shift_id = 0;
			$nearest_punch_difference = FALSE;
			$prev_punch_obj = FALSE;
			foreach( $plf as $p_obj ) {
				//Debug::text('Shift: '. $shift .' Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

				//If we're editing a punch, we need to use the object passed to this function instead of the one
				//from the database.

				if ( $epoch == NULL ) { //If user_date_id is passed without epoch, set epoch to the first punch we find.
					$epoch = $p_obj->getTimeStamp();
				}

				if ( isset($prev_punch_arr) AND $p_obj->getTimeStamp() > $prev_punch_arr['time_stamp'] ) {
					//Make sure $x resets itself after each shift.
					$shift_data[$shift]['previous_punch_key'] = ( $x - 1 );
					if ( $shift_data[$shift]['previous_punch_key'] < 0 ) {
						$shift_data[$shift]['previous_punch_key'] = NULL;
					}
				}

				//Determine if a non-saved PunchControl object was passed, and if so, match the IDs to use that instead.
				if ( is_object($tmp_punch_control_obj) AND $p_obj->getPunchControlID() == $tmp_punch_control_obj->getId() ) {
					Debug::text('Passed non-saved punch control object that matches, using that instead... Using ID: '. (int)$tmp_punch_control_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
					$punch_control_obj = $tmp_punch_control_obj;
				} else {
					$punch_control_obj = $p_obj->getPunchControlObject();
				}

				//Can't use PunchControl object total_time because the record may not be saved yet when editing
				//an already existing punch.
				//When editing, simply pass the existing PunchControl object to this function so we can
				//use it instead of the one in the database perhaps?
				$total_time = $punch_control_obj->getTotalTime();

				//We can't skip records with total_time == 0, because then when deleting one of the two
				//punches in a pair, the remaining punch is ignored and causing punches to jump around between days in some cases.
				if ( $i > 0 AND isset($shift_data[$shift]['last_out'])
						AND ( $p_obj->getStatus() == 10 OR $p_obj->getStatus() == $prev_punch_arr['status_id'] )) {
					Debug::text('Checking for new shift... This Control ID: '. $p_obj->getPunchControlID() .' Last Out Control ID: '. $shift_data[$shift]['last_out']['punch_control_id'] .' Last Out Time: '. TTDate::getDate('DATE+TIME', $shift_data[$shift]['last_out']['time_stamp']), __FILE__, __LINE__, __METHOD__, 10);
					//Assume that if two punches are assigned to the same punch_control_id are the same shift, even if the time between
					//them exceeds the new_shift_trigger_time. This helps fix the bug where you could add a In punch then add a Out
					//punch BEFORE the In punch as long as it was more than the Maximum Shift Time before the In Punch.
					//ie: Add: In Punch 10-Dec-09 @ 8:00AM, Add: Out Punch 09-Dec-09 @ 5:00PM.
					//Basically it just helps the validation checks to determine the error.
					//
					//It used to be that if shifts are split at midnight, new_shift_trigger_time must be 0, so the "split" punch can occur at midnight.
					//However we have since added a check to see if punches span midnight and trigger a new shift based on that, regardless of the new shift trigger time.
					//As the new_shift_trigger_time of 0 also affected lunch/break automatic detection by Punch Time, since an Out punch and a In punch of any time
					//would trigger a new shift, and it wouldn't be detected as lunch/break.
					//
					//What happens when the employee takes lunch/break over midnight? Lunch out at 11:30PM Lunch IN at 12:30AM
					//	We need to split those into two lunches, or two breaks? But then that can affect those policies if they are only allowed one break.
					//	Or do we not split the shift at all when this occurs? Currently we don't split at all.
					if ( $p_obj->getPunchControlID() != $shift_data[$shift]['last_out']['punch_control_id']
							AND (
									(
										$p_obj->getType() == 10
										AND $p_obj->getTimeStamp() != $shift_data[$shift]['last_out']['time_stamp'] //Don't allow transfer punches to cause a new shift to start.
										AND ( $p_obj->getTimeStamp() - $shift_data[$shift]['last_out']['time_stamp'] ) >= $new_shift_trigger_time
									)
									OR
									(
										$this->getShiftAssignedDay() == 40
										//Only split shifts on NORMAL punches.
										AND $p_obj->getType() == 10
										AND $shift_data[$shift]['last_out']['type_id'] == 10
										AND TTDate::doesRangeSpanMidnight( $shift_data[$shift]['last_out']['time_stamp'], $p_obj->getTimeStamp(), TRUE ) == TRUE
									)
								)
							) {
						Debug::Text('	 New shift because of normal punches... Punch Time: '. $p_obj->getTimeStamp() .' Last Out: '. $shift_data[$shift]['last_out']['time_stamp'] .' New Shift: '. $new_shift_trigger_time .' ShiftAssignedType: '. $this->getShiftAssignedDay(), __FILE__, __LINE__, __METHOD__, 10);
						$shift++;
						$x = 0;
					}
				} elseif ( $i > 0
							AND isset($prev_punch_arr['time_stamp'])
							AND $prev_punch_arr['punch_control_id'] != $p_obj->getPunchControlId()
							AND abs( ( $prev_punch_arr['time_stamp'] - $p_obj->getTimeStamp() ) ) > $maximum_shift_time ) {
					//Debug::text('	 New shift because two punch_control records exist and punch timestamp exceed maximum shift time.', __FILE__, __LINE__, __METHOD__, 10);
					$shift++;
					$x = 0;
				}

				if ( !isset($shift_data[$shift]['total_time']) ) {
					$shift_data[$shift]['total_time'] = 0;
				}

				$punch_day_epoch = TTDate::getBeginDayEpoch( $p_obj->getTimeStamp() );
				if ( !isset($shift_data[$shift]['total_time_per_day'][$punch_day_epoch]) ) {
					$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] = 0;
				}

				//Determine which shift is closest to the given epoch.
				$punch_difference_from_epoch = abs( ( $epoch - $p_obj->getTimeStamp() ) );
				if ( $nearest_punch_difference === FALSE OR $punch_difference_from_epoch <= $nearest_punch_difference ) {
					Debug::text('Nearest Shift Determined to be: '. $shift .' Nearest Punch Diff: '. (int)$nearest_punch_difference .' Punch Diff: '. $punch_difference_from_epoch .' Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' Current Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					//If two punches have the same timestamp, use the shift that matches the passed punch control object, which is usually the one we are currently editing...
					//This is for splitting shifts at exactly midnight.
					if ( $punch_difference_from_epoch != $nearest_punch_difference
							OR ( $punch_difference_from_epoch == $nearest_punch_difference AND ( is_object( $tmp_punch_control_obj ) AND $tmp_punch_control_obj->getId() == $p_obj->getPunchControlID() ) ) ) {
						//Debug::text('Found two punches with the same timestamp... Tmp Punch Control: '.$tmp_punch_control_obj->getId() .' Punch Control: '. $p_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
						$nearest_shift_id = $shift;
						$nearest_punch_difference = $punch_difference_from_epoch;
					}
				}

				$punch_arr = array(
									'id' => $p_obj->getId(),
									'punch_control_id' => $p_obj->getPunchControlId(),
									'user_id' => $punch_control_obj->getUser(),
									'date_stamp' => $punch_control_obj->getDateStamp(),
									'time_stamp' => $p_obj->getTimeStamp(),
									'status_id' => $p_obj->getStatus(),
									'type_id' => $p_obj->getType(),
									);

				$shift_data[$shift]['punches'][] = $punch_arr;
				$shift_data[$shift]['punch_control_ids'][] = $p_obj->getPunchControlId();
				if ( $punch_control_obj->getDateStamp() != FALSE ) {
					$shift_data[$shift]['date_stamps'][] = $punch_control_obj->getDateStamp();
				}

				if ( !isset($shift_data[$shift]['span_midnight']) ) {
					$shift_data[$shift]['span_midnight'] = FALSE;
				}
				if ( !isset($shift_data[$shift]['first_in']) AND $p_obj->getStatus() == 10 ) {
					//Debug::text('First In -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['first_in'] = $punch_arr;
				} elseif ( $p_obj->getStatus() == 20 ) {
					//Debug::text('Last Out -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['last_out'] = $punch_arr;

					//Debug::text('Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
					$shift_data[$shift]['total_time'] += $total_time;

					//Check to see if the previous punch was on a different day then the current punch.
					if ( isset($prev_punch_arr) AND is_array($prev_punch_arr)
							AND ( $p_obj->getStatus() == 20 AND $prev_punch_arr['status_id'] != 20 )
							AND TTDate::doesRangeSpanMidnight( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp() ) == TRUE ) {
						Debug::text('Punch PAIR DOES span midnight', __FILE__, __LINE__, __METHOD__, 10);
						$shift_data[$shift]['span_midnight'] = TRUE;

						$total_time_for_each_day_arr = TTDate::calculateTimeOnEachDayBetweenRange( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp() );
						if ( is_array( $total_time_for_each_day_arr ) ) {
							foreach( $total_time_for_each_day_arr as $begin_day_epoch => $day_total_time ) {
								if ( !isset($shift_data[$shift]['total_time_per_day'][$begin_day_epoch]) ) {
									$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] = 0;
								}
								$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] += $day_total_time;
							}
						}
						unset($total_time_for_each_day_arr, $begin_day_epoch, $day_total_time, $prev_day_total_time);
					} else {
						$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] += $total_time;
					}
				}

				//Keep instead of last punch for each shift so we can easily get to it.
				end( $shift_data[$shift]['punches'] );
				$shift_data[$shift]['last_punch_key'] = key( $shift_data[$shift]['punches'] );

				$prev_punch_arr = $punch_arr;
				$i++;
				$x++;
			}

			//Debug::Arr($shift_data, 'aShift Data:', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($shift_data) ) {
				//Loop through each shift to determine the day with the most time.
				foreach( $shift_data as $tmp_shift_key => $tmp_shift_data ) {
					krsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by day first
					arsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by total time per day.
					reset($shift_data[$tmp_shift_key]['total_time_per_day']);
					$shift_data[$tmp_shift_key]['day_with_most_time'] = key($shift_data[$tmp_shift_key]['total_time_per_day']);

					$shift_data[$tmp_shift_key]['punch_control_ids'] = array_unique( $shift_data[$tmp_shift_key]['punch_control_ids'] );
					if ( isset($shift_data[$tmp_shift_key]['date_stamps']) ) {
						$shift_data[$tmp_shift_key]['date_stamps'] = array_unique( $shift_data[$tmp_shift_key]['date_stamps'] );
					}
				}
				unset($tmp_shift_key, $tmp_shift_data);

				if ( $filter == 'first_shift' ) {
					//Only return first shift.
					$shift_data = $shift_data[0];
				} elseif( $filter == 'last_shift' ) {
					//Only return last shift.
					$shift_data = $shift_data[$shift];
				} elseif ( $filter == 'nearest_shift' ) {
					$shift_data = $shift_data[$nearest_shift_id];
					//Check to make sure the nearest shift is within the new shift trigger time of EPOCH.
					if ( isset($shift_data['first_in']['time_stamp']) ) {
						$first_in = $shift_data['first_in']['time_stamp'];
					} elseif ( isset($shift_data['last_out']['time_stamp']) ) {
						$first_in = $shift_data['last_out']['time_stamp'];
					}

					if ( isset($shift_data['last_out']['time_stamp']) ) {
						$last_out = $shift_data['last_out']['time_stamp'];
					} elseif ( isset($shift_data['first_in']['time_stamp']) ) {
						$last_out = $shift_data['first_in']['time_stamp'];
					}

					//The check below must occur so if the user attempts to add an In punch that occurs AFTER the Out punch, this function
					//still returns the shift data, so the validation checks can occur in PunchControl factory.
					if ( $first_in > $last_out ) {
						//It appears that the first in punch has occurred after the OUT punch, so swap first_in and last_out, so we don't return FALSE in this case.
						list( $first_in, $last_out ) = array( $last_out, $first_in );
					}

					//Only check overlap if the last punch in the shift is an OUT punch (shift has ended basically), and the first/last punches don't match.
					//Only check against NORMAL OUT punches though, that way if new_shift_trigger_time=0, if the employee goes for lunch it thinks they are starting a new shift.
					//However meal policies based on Punch Time, the last punch is always a Normal Out, so if new_shift_trigger_time=0 it will never detect lunches properly.
					//  Switch this back to checking if the shift spans midnight for new shifts to be triggered.
					if ( $first_in != $last_out
							AND ( isset($shift_data['punches'][$shift_data['last_punch_key']]) AND $shift_data['punches'][$shift_data['last_punch_key']]['status_id'] == 20 AND $shift_data['punches'][$shift_data['last_punch_key']]['type_id'] == 10 )
							AND TTDate::doesRangeSpanMidnight( $shift_data['punches'][$shift_data['last_punch_key']]['time_stamp'], $epoch ) == TRUE
							AND TTDate::isTimeOverLap($epoch, $epoch, ($first_in - $new_shift_trigger_time), ($last_out + $new_shift_trigger_time) ) == FALSE
							) {
						Debug::Text('Nearest shift is outside the new shift trigger time... Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' First In: '. TTDate::getDate('DATE+TIME', $first_in ) .' Last Out: '. TTDate::getDate('DATE+TIME', $last_out ) .' New Shift Trigger: '. $new_shift_trigger_time .' Prev Punch Key: '. $shift_data['previous_punch_key'], __FILE__, __LINE__, __METHOD__, 10);
						return FALSE;
					}
					unset($first_in, $last_out);
				} else {
					Debug::Text('ERROR: invalid filter used: '. $filter, __FILE__, __LINE__, __METHOD__, 10);
				}

				$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

				//Debug::Arr($shift_data, 'bShift Data:', __FILE__, __LINE__, __METHOD__, 10);
				return $shift_data;
			}
		}

		$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		Debug::Text('No Shift Data returned...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getEnableInitialPayPeriods() {
		if ( isset($this->enable_create_initial_pay_periods) ) {
			return $this->enable_create_initial_pay_periods;
		}

		return FALSE;
	}

	function setEnableInitialPayPeriods( $val ) {
		$this->enable_create_initial_pay_periods = (bool)$val;

		return TRUE;
	}

	function getCreateInitialPayPeriods() {
		if ( isset($this->create_initial_pay_periods) ) {
			return $this->create_initial_pay_periods;
		}

		return FALSE;
	}

	function setCreateInitialPayPeriods( $val ) {
		$this->create_initial_pay_periods = (bool)$val;

		return TRUE;
	}

	//Close pay periods that were left open.
	//Pay period schedule must be at least 45 days old so we don't close pay periods on new customers right away.
	//Only close OPEN pay periods that have passed the transaction date by 7 days.
	//Get OPEN pay periods with transaction dates at least 48hrs before the given date?
	//Or should we just prevent customers from generating pay stubs in a pay period that has a previous pay period that is still open? Both.
	function forceClosePreviousPayPeriods( $date = NULL ) {
		if ( $date == '' ) {
			$date = time();
		}

		//Start with 7 days initially, then cut back to 5 days eventually.
		$date = ( $date - (86400 * 5) ); //Give a 5 days grace period after the transaction date to start with.

		$pplf = TTNew('PayPeriodListFactory');
		$pplf->getByCompanyIDAndPayPeriodScheduleIdAndStatusAndStartTransactionDateAndEndTransactionDate( $this->getCompany(), $this->getID(), 10, 1, $date );
		Debug::text('Closing Pay Periods: '. $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				if ( is_object( $pp_obj->getPayPeriodScheduleObject() ) AND $pp_obj->getPayPeriodScheduleObject()->getCreatedDate() < ($date - (86400 * 45) ) ) {
					Debug::text('Closing Pay Period ID: '. $pp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$pp_obj->setStatus( 20 ); //Closed
					if ( $pp_obj->isValid() ) {
						//Make log entry as person who last updated the pay period schedule so they can see it in the audit log at least.
						TTLog::addEntry( $pp_obj->getId(), 500, TTi18n::getText('Force closing pay period').': '. TTDate::getDate('DATE', $pp_obj->getStartDate()) .' -> '. TTDate::getDate('DATE', $pp_obj->getEndDate()), $pp_obj->getPayPeriodScheduleObject()->getUpdatedBy(), $pp_obj->getTable() );

						$pp_obj->Save();
					}
				}
			}
		}

		return TRUE;
	}

	function preSave() {
		$this->StartTransaction();

		if ( $this->isNew() == TRUE ) {
			$this->setCreateInitialPayPeriods( TRUE );
		}

		$this->setDayStartTime( 0 ); //Since this isn't support, force DayStartTime to midnight always.

		if ( $this->getShiftAssignedDay() == FALSE ) {
			$this->setShiftAssignedDay( 10 ); //Day shifts start on
		} //elseif ( $this->getShiftAssignedDay() == 40 ) { //Split at midnight
			//We now support a minimum time-off setting when shifts are set to split at midnight.
			//$this->setNewDayTriggerTime( 0 ); //Minimum Time-off between shifts must be 0 in these cases.
		//}

		if ( $this->getType() != 5 ) { //If schedule is other then manual, automatically calculate annual pay periods
			$this->setAnnualPayPeriods( $this->calcAnnualPayPeriods() );
		}

		if ( $this->getDeleted() == TRUE ) {
			//Delete pay periods assigned to this schedule.
			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByPayPeriodScheduleId( $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				Debug::text('Delete Pay Periods: '. $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $pplf as $pp_obj ) {
					$pp_obj->setDeleted(TRUE);
					$pp_obj->Save();
				}
			}

		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			return TRUE;
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getEnableInitialPayPeriods() == TRUE AND $this->getCreateInitialPayPeriods() == TRUE ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			$pay_period_schedule_obj = $ppslf->getById( $this->getId() )->getCurrent();

			$pay_period_schedule_obj->createNextPayPeriod( $pay_period_schedule_obj->getAnchorDate() );
			Debug::text('New Pay Period Schdule, creating pay periods start from ('.$pay_period_schedule_obj->getAnchorDate().'): '. TTDate::getDate('DATE+TIME', $pay_period_schedule_obj->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10);

			//Create pay periods up until now, at most 260. (5yrs of weekly ones)
			for($i = 0; $i <= 260; $i++ ) {
				if ( $pay_period_schedule_obj->createNextPayPeriod() == FALSE ) {
					Debug::text('createNextPayPeriod returned false, stopping loop.', __FILE__, __LINE__, __METHOD__, 10);
					break;
				}
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			//Delete all pay periods related to this pay period schedule. This will not delete data within those pay periods though.
			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByPayPeriodScheduleId( $this->getID() );
			if ( $pplf->getRecordCount() > 0 ) {
				foreach( $pplf as $pp_obj ) {
					$pp_obj->setDeleted( TRUE );
					if ( $pp_obj->isValid() ) {
						$pp_obj->Save();
					}
				}
			}

			//Remove from User Defaults.
			$udlf = TTnew( 'UserDefaultListFactory' );
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				foreach( $udlf as $udf_obj ) {
					$udf_obj->setPayPeriodSchedule( 0 );
					if ( $udf_obj->isValid() ) {
						$udf_obj->Save();
					}
				}
			}
		}

		$this->CommitTransaction();

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						/*
						case 'new_day_trigger_time':
						case 'maximum_shift_time':
							Debug::text('Raw Time Unit: '. $data[$key] .' Parsing To: '. TTDate::parseTimeUnit( $data[$key] ), __FILE__, __LINE__, __METHOD__, 10);

							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseTimeUnit( $data[$key] ) );
							}
							break;
						*/
						case 'anchor_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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
						case 'total_users':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_week_day':
							$data[$variable] = Option::getByKey( $this->getStartWeekDay(), $this->getOptions( $variable ) );
							break;
						case 'shift_assigned_day':
							$data[$variable] = Option::getByKey( $this->getShiftAssignedDay(), $this->getOptions( $variable ) );
							break;
						case 'anchor_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Period Schedule'), NULL, $this->getTable(), $this );
	}

}
?>
