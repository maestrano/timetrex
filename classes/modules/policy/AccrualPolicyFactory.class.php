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
class AccrualPolicyFactory extends Factory {
	protected $table = 'accrual_policy';
	protected $pk_sequence_name = 'accrual_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $milestone_objs = NULL;
	protected $contributing_shift_policy_obj = NULL;
	protected $length_of_service_contributing_pay_code_policy_obj = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Standard'),
										20 => TTi18n::gettext('Calendar Based'),
										30 => TTi18n::gettext('Hour Based'),
									);
				break;
			case 'apply_frequency':
				$retval = array(
										10 => TTi18n::gettext('each Pay Period'),
										20 => TTi18n::gettext('Annually'),
										30 => TTi18n::gettext('Monthly'),
										40 => TTi18n::gettext('Weekly'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1030-name' => TTi18n::gettext('Name'),
										'-1035-description' => TTi18n::gettext('Description'),


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
								'type',
								'name',
								'description',
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
											'accrual_policy_account_id' => 'AccrualPolicyAccount',
											'accrual_policy_account' => FALSE,
											'contributing_shift_policy_id' => 'ContributingShiftPolicy',
											'contributing_shift_policy' => FALSE,
											'length_of_service_contributing_pay_code_policy_id' => 'LengthOfServiceContributingPayCodePolicy',
											'length_of_service_contributing_pay_code_policy' => FALSE,
											'name' => 'Name',
											'description' => 'Description',
											'enable_pay_stub_balance_display' => 'EnablePayStubBalanceDisplay',
											'minimum_time' => 'MinimumTime',
											'maximum_time' => 'MaximumTime',
											'apply_frequency' => 'ApplyFrequency',
											'apply_frequency_id' => 'ApplyFrequency', //Must go after apply_frequency, so its set last.
											'apply_frequency_month' => 'ApplyFrequencyMonth',
											'apply_frequency_day_of_month' => 'ApplyFrequencyDayOfMonth',
											'apply_frequency_day_of_week' => 'ApplyFrequencyDayOfWeek',
											'apply_frequency_hire_date' => 'ApplyFrequencyHireDate',
											'milestone_rollover_hire_date' => 'MilestoneRolloverHireDate',
											'milestone_rollover_month' => 'MilestoneRolloverMonth',
											'milestone_rollover_day_of_month' => 'MilestoneRolloverDayOfMonth',
											'minimum_employed_days' => 'MinimumEmployedDays',
											'in_use' => FALSE,
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	function getLengthOfServiceContributingPayCodePolicyObject() {
		return $this->getGenericObject( 'ContributingPayCodePolicyListFactory', $this->getLengthOfServiceContributingPayCodePolicy(), 'length_of_service_contributing_pay_code_policy_obj' );
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

			return TRUE;
		}

		return FALSE;
	}


	function getAccrualPolicyAccount() {
		if ( isset($this->data['accrual_policy_account_id']) ) {
			return (int)$this->data['accrual_policy_account_id'];
		}

		return FALSE;
	}
	function setAccrualPolicyAccount($id) {
		$id = trim($id);

		$apaplf = TTnew( 'AccrualPolicyAccountListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'accrual_policy_account_id',
													$apaplf->getByID($id),
													TTi18n::gettext('Accrual Account is invalid')
													) ) {

			$this->data['accrual_policy_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//This is the contributing shifts used for Hour Based accrual policies.
	function getContributingShiftPolicy() {
		if ( isset($this->data['contributing_shift_policy_id']) ) {
			return (int)$this->data['contributing_shift_policy_id'];
		}

		return FALSE;
	}
	function setContributingShiftPolicy($id) {
		$id = trim($id);

		$csplf = TTnew( 'ContributingShiftPolicyListFactory' );

		if (	$id == 0
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

	//This is strictly used to determine milestones with active after X hours.
	function getLengthOfServiceContributingPayCodePolicy() {
		if ( isset($this->data['length_of_service_contributing_pay_code_policy_id']) ) {
			return (int)$this->data['length_of_service_contributing_pay_code_policy_id'];
		}

		return FALSE;
	}
	function setLengthOfServiceContributingPayCodePolicy($id) {
		$id = trim($id);

		$csplf = TTnew( 'ContributingPayCodePolicyListFactory' );

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'length_of_service_contributing_pay_code_policy_id',
													$csplf->getByID($id),
													TTi18n::gettext('Contributing Pay Code Policy is invalid')
													) ) {

			$this->data['length_of_service_contributing_pay_code_policy_id'] = $id;

			return TRUE;
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

	function getEnablePayStubBalanceDisplay() {
		return $this->fromBool( $this->data['enable_pay_stub_balance_display'] );
	}
	function setEnablePayStubBalanceDisplay($bool) {
		$this->data['enable_pay_stub_balance_display'] = $this->toBool($bool);

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

	//
	// Calendar
	//
	function getApplyFrequency() {
		if ( isset($this->data['apply_frequency_id']) ) {
			return (int)$this->data['apply_frequency_id'];
		}

		return FALSE;
	}
	function setApplyFrequency($value) {
		$value = trim($value);

		if (	$value == 0
				OR
				$this->Validator->inArrayKey(	'apply_frequency_id',
												$value,
												TTi18n::gettext('Incorrect frequency'),
												$this->getOptions('apply_frequency')) ) {

			$this->data['apply_frequency_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getApplyFrequencyMonth() {
		if ( isset($this->data['apply_frequency_month']) ) {
			return $this->data['apply_frequency_month'];
		}

		return FALSE;
	}
	function setApplyFrequencyMonth($value) {
		$value = trim($value);

		if ( $value == 0
				OR
				$this->Validator->inArrayKey(	'apply_frequency_month',
											$value,
											TTi18n::gettext('Incorrect frequency month'),
											TTDate::getMonthOfYearArray() ) ) {

			$this->data['apply_frequency_month'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getApplyFrequencyDayOfMonth() {
		if ( isset($this->data['apply_frequency_day_of_month']) ) {
			return $this->data['apply_frequency_day_of_month'];
		}

		return FALSE;
	}
	function setApplyFrequencyDayOfMonth($value) {
		$value = trim($value);

		if ( $value == 0
				OR
				$this->Validator->inArrayKey(	'apply_frequency_day_of_month',
											$value,
											TTi18n::gettext('Incorrect frequency day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['apply_frequency_day_of_month'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getApplyFrequencyDayOfWeek() {
		if ( isset($this->data['apply_frequency_day_of_week']) ) {
			return $this->data['apply_frequency_day_of_week'];
		}

		return FALSE;
	}
	function setApplyFrequencyDayOfWeek($value) {
		$value = trim($value);

		if ( $value == 0
				OR
				$this->Validator->inArrayKey(	'apply_frequency_day_of_week',
											$value,
											TTi18n::gettext('Incorrect frequency day of week'),
											TTDate::getDayOfWeekArray() ) ) {

			$this->data['apply_frequency_day_of_week'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getApplyFrequencyHireDate() {
		return $this->fromBool( $this->data['apply_frequency_hire_date'] );
	}
	function setApplyFrequencyHireDate($bool) {
		$this->data['apply_frequency_hire_date'] = $this->toBool($bool);

		return TRUE;
	}

	function getMilestoneRolloverHireDate() {
		return $this->fromBool( $this->data['milestone_rollover_hire_date'] );
	}
	function setMilestoneRolloverHireDate($bool) {
		$this->data['milestone_rollover_hire_date'] = $this->toBool($bool);

		return TRUE;
	}

	function getMilestoneRolloverMonth() {
		if ( isset($this->data['milestone_rollover_month']) ) {
			return $this->data['milestone_rollover_month'];
		}

		return FALSE;
	}
	function setMilestoneRolloverMonth($value) {
		$value = trim($value);

		if ( $value == 0
				OR
				$this->Validator->inArrayKey(	'milestone_rollover_month',
											$value,
											TTi18n::gettext('Incorrect milestone rollover month'),
											TTDate::getMonthOfYearArray() ) ) {

			$this->data['milestone_rollover_month'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMilestoneRolloverDayOfMonth() {
		if ( isset($this->data['milestone_rollover_day_of_month']) ) {
			return $this->data['milestone_rollover_day_of_month'];
		}

		return FALSE;
	}
	function setMilestoneRolloverDayOfMonth($value) {
		$value = trim($value);

		if ( $value == 0
				OR
				$this->Validator->inArrayKey(	'milestone_rollover_day_of_month',
												$value,
												TTi18n::gettext('Incorrect milestone rollover day of month'),
												TTDate::getDayOfMonthArray() ) ) {

			$this->data['milestone_rollover_day_of_month'] = $value;

			return TRUE;
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

	function getMilestoneRolloverDate( $user_hire_date = NULL, $modifier_obj = NULL ) {
		if ( $user_hire_date == '' ) {
			return FALSE;
		}

		if ( is_object($modifier_obj) AND method_exists( $modifier_obj, 'getLengthOfServiceDate' ) AND $modifier_obj->getLengthOfServiceDate() != '' ) {
			Debug::Text('Using Modifier LengthOfService Date: '. TTDate::getDate('DATE+TIME', $modifier_obj->getLengthOfServiceDate() ) .' Hire Date: '. TTDate::getDate('DATE+TIME', $user_hire_date), __FILE__, __LINE__, __METHOD__, 10);
			$user_hire_date = $modifier_obj->getLengthOfServiceDate();
		}

		if ( $this->getMilestoneRolloverHireDate() == TRUE ) {
			$retval = $user_hire_date;
		} else {
			$user_hire_date_arr = getdate( $user_hire_date );
			$retval = mktime( $user_hire_date_arr['hours'], $user_hire_date_arr['minutes'], $user_hire_date_arr['seconds'], $this->getMilestoneRolloverMonth(), $this->getMilestoneRolloverDayOfMonth(), $user_hire_date_arr['year'] );
		}

		Debug::Text('Milestone Rollover Date: '. TTDate::getDate('DATE+TIME', $retval) .' Hire Date: '. TTDate::getDate('DATE+TIME', $user_hire_date), __FILE__, __LINE__, __METHOD__, 10);
		return TTDate::getBeginDayEpoch( $retval ); //Some hire dates might be at noon, so make sure they are all at midnight.
	}

	function getCurrentMilestoneRolloverDate( $epoch, $user_hire_date = NULL ) {
		$base_rollover_date = $this->getMilestoneRolloverDate( $user_hire_date );
		$rollover_date = mktime( 0, 0, 0, TTDate::getMonth( $base_rollover_date ), TTDate::getDayOfMonth( $base_rollover_date ), TTDate::getYear( $epoch ) );

		if ( $rollover_date < $user_hire_date ) {
			$rollover_date = $user_hire_date;
		}

		Debug::Text('Current Milestone Rollover Date: '. TTDate::getDate('DATE+TIME', $rollover_date) .' Hire Date: '. TTDate::getDate('DATE+TIME', $user_hire_date), __FILE__, __LINE__, __METHOD__, 10);
		return $rollover_date;
	}

	function getAccrualRatePerTimeFrequency( $accrual_rate, $annual_pay_periods = NULL ) {
		$retval = FALSE;
		switch( $this->getApplyFrequency() ) {
			case 10: //Pay Period
				if ( $annual_pay_periods == '' ) {
					return FALSE;
				}
				$retval = bcdiv( $accrual_rate, $annual_pay_periods, 0);
				break;
			case 20: //Year
				$retval = $accrual_rate;
				break;
			case 30: //Month
				$retval = bcdiv( $accrual_rate, 12, 0);
				break;
			case 40: //Week
				$retval = bcdiv( $accrual_rate, 52, 0);
				break;
		}

		//Round to nearest minute, or 15mins?
		//Well, if they accrue 99hrs/year on a weekly basis, rounding to the nearest minute means 98.8hrs/year...
		//Should round to the nearest second instead then.
		//$retval = TTDate::roundTime( $retval, 60, 20 );
		$retval = round($retval, 0);

		Debug::Text('Accrual Rate Per Frequency: '. $retval .' Accrual Rate: '. $accrual_rate .' Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function inRolloverFrequencyWindow( $current_epoch, $offset, $user_hire_date, $pay_period_start_date = NULL ) {
		//Use current_epoch mainly for Yearly cases where the rollover date is 01-Nov and the hire date is always right after it, 10-Nov in the next year.
		$rollover_date = $this->getCurrentMilestoneRolloverDate( $current_epoch, $user_hire_date );
		Debug::Text('Rollover Date: '. TTDate::getDate('DATE+TIME', $rollover_date ) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);

		if ( $rollover_date >= ($current_epoch - $offset) AND $rollover_date <= $current_epoch ) {
			Debug::Text('In rollover frequency window...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::Text('NOT in rollover frequency window...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_end_date = NULL, $hire_date = NULL ) {
		$retval = FALSE;
		switch( $this->getApplyFrequency() ) {
			case 10: //Pay Period
				if ( $pay_period_end_date == '' ) {
					return FALSE;
				}
				if ( $pay_period_end_date >= ($current_epoch - $offset)
						AND $pay_period_end_date <= $current_epoch ) {
					$retval = TRUE;
				}
				break;
			case 20: //Year
				if ( $this->getApplyFrequencyHireDate() == TRUE ) {
					Debug::Text('Hire Date: '. TTDate::getDate('DATE', $hire_date), __FILE__, __LINE__, __METHOD__, 10);
					$year_epoch = mktime( 0, 0, 0, TTDate::getMonth( $hire_date ), TTDate::getDayOfMonth( $hire_date ), TTDate::getYear( $current_epoch ) );
				} else {
					Debug::Text('Static Date', __FILE__, __LINE__, __METHOD__, 10);
					$year_epoch = mktime( 0, 0, 0, $this->getApplyFrequencyMonth(), $this->getApplyFrequencyDayOfMonth(), TTDate::getYear( $current_epoch ) );
				}
				Debug::Text('Year EPOCH: '. TTDate::getDate('DATE+TIME', $year_epoch), __FILE__, __LINE__, __METHOD__, 10);

				if ( $year_epoch >= ($current_epoch - $offset)
						AND $year_epoch <= $current_epoch ) {
					$retval = TRUE;
				}
				break;
			case 30: //Month
				$apply_frequency_day_of_month = $this->getApplyFrequencyDayOfMonth();

				//Make sure if they specify the day of month to be 31, that is still works for months with 30, or 28-29 days, assuming 31 basically means the last day of the month
				if ( $apply_frequency_day_of_month > TTDate::getDaysInMonth( $current_epoch ) ) {
					$apply_frequency_day_of_month = TTDate::getDaysInMonth( $current_epoch );
					Debug::Text('Apply frequency day of month exceeds days in this month, using last day of the month instead: '. $apply_frequency_day_of_month, __FILE__, __LINE__, __METHOD__, 10);
				}

				$month_epoch = mktime( 0, 0, 0, TTDate::getMonth( $current_epoch ), $apply_frequency_day_of_month, TTDate::getYear( $current_epoch ) );
				//Debug::Text('Day of Month: '. $this->getApplyFrequencyDayOfMonth() .' Month EPOCH: '. TTDate::getDate('DATE+TIME', $month_epoch) .' Current Month: '. TTDate::getMonth( $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Month EPOCH: '. TTDate::getDate('DATE+TIME', $month_epoch) .'('. $month_epoch .') Greater Than: '. TTDate::getDate('DATE+TIME', ($current_epoch - $offset)) .' Less Than: '.	 TTDate::getDate('DATE+TIME', $current_epoch) .'('. $current_epoch .')', __FILE__, __LINE__, __METHOD__, 10);

				if ( $month_epoch >= ($current_epoch - $offset)
						AND $month_epoch <= $current_epoch ) {
					$retval = TRUE;
				}
				break;
			case 40: //Week
				Debug::Text('Current Day Of Week: '. TTDate::getDayOfWeek($current_epoch - $offset), __FILE__, __LINE__, __METHOD__, 10);
				if ( $this->getApplyFrequencyDayOfWeek() == TTDate::getDayOfWeek( ($current_epoch - $offset) ) ) {
					$retval = TRUE;
				}
				break;
		}

		Debug::Text('RetVal: '. (int)$retval .' Offset: '. $offset, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getWorkedTimeByUserIdAndEndDate( $user_id, $start_date = NULL, $end_date = NULL ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			$start_date = 1; //Default to beginning of time if hire date is not specified.
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$retval = 0;

		$pay_code_policy_obj = $this->getLengthOfServiceContributingPayCodePolicyObject();
		if ( is_object( $pay_code_policy_obj ) ) {
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$retval = $udtlf->getTotalTimeSumByUserIDAndPayCodeIDAndStartDateAndEndDate( $user_id, $pay_code_policy_obj->getPayCode(), $start_date, $end_date );
		}

		Debug::Text('Worked Seconds: '. (int)$retval .' Before: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	//Determine if any milestones have an hour based length of service.
	function isHourBasedLengthOfService() {
		//Cache milestones to speed up getting projected balances.
		if ( !isset($this->milestone_objs[$this->getID()]) ) {
			$this->milestone_objs[$this->getID()] = TTnew( 'AccrualPolicyMilestoneListFactory' );
			$this->milestone_objs[$this->getID()]->getByAccrualPolicyId($this->getId(), NULL, array('length_of_service_days' => 'desc' ) );
		}
		Debug::Text('  Total Accrual Policy MileStones: '. (int)$this->milestone_objs[$this->getID()]->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->milestone_objs[$this->getID()]->getRecordCount() > 0 ) {
			foreach( $this->milestone_objs[$this->getID()] as $apm_obj ) {
				if ( $apm_obj->getLengthOfServiceUnit() == 50 AND $apm_obj->getLengthOfService() > 0 ) {
					Debug::Text('  MileStone is in Hours...', __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		Debug::Text('  No HourBased length of service MileStones...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getAccrualPolicyUserModifierObject( $u_obj ) {
		if ( !is_object( $u_obj ) ) {
			return FALSE;
		}

		if ( getTTProductEdition() > 10 ) {
			$apumlf = TTNew('AccrualPolicyUserModifierListFactory');
			$apumlf->getByUserIdAndAccrualPolicyId( $u_obj->getId(), $this->getId() );
			if ( $apumlf->getRecordCount() == 1 ) {
				$apum_obj = $apumlf->getCurrent();
				Debug::Text('  Found Accrual Policy User Modifier: Length of Service: '. $apum_obj->getLengthOfServiceDate() .' Accrual Rate: '. $apum_obj->getAccrualRateModifier(), __FILE__, __LINE__, __METHOD__, 10);

				return $apum_obj;
			}
		}

		return FALSE;
	}

	function getActiveMilestoneObject( $u_obj, $epoch = NULL, $worked_time = 0, $modifier_obj = FALSE ) {
		if ( !is_object( $u_obj ) ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		$milestone_obj = FALSE;

		if ( !is_object( $modifier_obj ) ) {
			$modifier_obj = $this->getAccrualPolicyUserModifierObject( $u_obj );
		}

		//Cache milestones to speed up getting projected balances.
		if ( !isset($this->milestone_objs[$this->getID()]) ) {
			$this->milestone_objs[$this->getID()] = TTnew( 'AccrualPolicyMilestoneListFactory' );
			$this->milestone_objs[$this->getID()]->getByAccrualPolicyId($this->getId(), NULL, array('length_of_service_days' => 'desc' ) );
		}
		Debug::Text('  Total Accrual Policy MileStones: '. (int)$this->milestone_objs[$this->getID()]->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->milestone_objs[$this->getID()]->getRecordCount() > 0 ) {
			$worked_time = NULL;
			$milestone_rollover_date = NULL;

			foreach( $this->milestone_objs[$this->getID()] as $apm_obj ) {
				if ( is_object($modifier_obj) ) {
					$apm_obj = $modifier_obj->getAccrualPolicyMilestoneObjectAfterModifier( $apm_obj );
				}
				
				if ( $apm_obj->getLengthOfServiceUnit() == 50 AND $apm_obj->getLengthOfService() > 0 ) {
					Debug::Text('  MileStone is in Hours...', __FILE__, __LINE__, __METHOD__, 10);
					//Hour based
					if ( $worked_time == NULL ) {
						//Get users worked time.
						$worked_time = TTDate::getHours( $this->getWorkedTimeByUserIdAndEndDate( $u_obj->getId(), $apm_obj->getLengthOfService(), $epoch ) );
						Debug::Text('  Worked Time: '. $worked_time .'hrs', __FILE__, __LINE__, __METHOD__, 10);
					}

					if ( $worked_time >= $apm_obj->getLengthOfService() ) {
						Debug::Text('  bLength Of Service: '. $apm_obj->getLengthOfService() .'hrs', __FILE__, __LINE__, __METHOD__, 10);
						$milestone_obj = $apm_obj;
						break;
					} else {
						Debug::Text('  Skipping Milestone...', __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::Text('  MileStone is in Days...', __FILE__, __LINE__, __METHOD__, 10);
					//Calendar based
					$milestone_rollover_date = $apm_obj->getLengthOfServiceDate( $this->getMilestoneRolloverDate( $u_obj->getHireDate(), $modifier_obj ) );

					//When a milestone first rolls-over, the Maximum rollover won't apply in many cases as it uses the new milestone rollover
					//at that time which often has a higher rollover amount. This only happens the first time the milestone rolls-over.
					//We could avoid this by using just ">" comparison below, but then that affects annual accruals as it will take two years
					//to see the milestone rollover after one year, so that won't work either.
					//if ( $length_of_service_days >= $apm_obj->getLengthOfServiceDays() ) {
					if ( $epoch >= $milestone_rollover_date ) {
						$milestone_obj = $apm_obj;
						Debug::Text('  Using MileStone due to Active After Days: '. $apm_obj->getLengthOfServiceDays() .' or Date: '. TTDate::getDate('DATE+TIME', $milestone_rollover_date ), __FILE__, __LINE__, __METHOD__, 10);
						break;
					} else {
						Debug::Text('  Skipping MileStone...', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
		}
		unset($apmlf, $apm_obj);

		return $milestone_obj;
	}

	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = NULL ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getAccrualPolicyAccount();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text('  Current Accrual Balance: '. $accrual_balance, __FILE__, __LINE__, __METHOD__, 10);

		return $accrual_balance;
	}

	function calcAccrualAmount( $milestone_obj, $total_time, $annual_pay_periods ) {
		if ( !is_object( $milestone_obj ) ) {
			return FALSE;
		}

		$accrual_amount = 0;
		if ( $this->getType() == 30 AND $total_time > 0 ) {
			//Calculate the fixed amount based off the rate.
			$accrual_amount = bcmul( $milestone_obj->getAccrualRate(), $total_time, 4);
		} elseif ( $this->getType() == 20 ) {
			$accrual_amount = $this->getAccrualRatePerTimeFrequency( $milestone_obj->getAccrualRate(), $annual_pay_periods );
		}
		Debug::Text('  Accrual Amount: '. $accrual_amount .' Total Time: '. $total_time .' Rate: '. $milestone_obj->getAccrualRate() .' Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

		return $accrual_amount;
	}

	//Returns an array of pay period start/end dates between a given start/end date.
	function getPayPeriodArray( $pps_obj, $u_obj, $start_epoch, $end_epoch ) {
		$retarr = array();

		$pp_end_date = $end_epoch;

		$pplf = TTNew('PayPeriodListFactory');
/*
		$pplf->getByUserIdAndEndDate( $u_obj->getID(), $start_epoch );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				$retarr[] = array('start_date' => $pp_obj->getStartDate(), 'end_date' => $pp_obj->getEndDate() );
				$pp_end_date = $pp_obj->getEndDate();
			}
		}
*/
		$pplf->getByUserIdAndOverlapStartDateAndEndDate( $u_obj->getId(), $start_epoch, $end_epoch);
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				$retarr[] = array('start_date' => $pp_obj->getStartDate(), 'end_date' => $pp_obj->getEndDate() );
				$pp_end_date = $pp_obj->getEndDate();
			}
		}

		Debug::Text('Last already created Pay Period End Date: '.  TTDate::getDate('DATE+TIME', $pp_end_date ), __FILE__, __LINE__, __METHOD__, 10);

		//$end_epoch is in the future, so continue to try and find pay period schedule dates.
		if ( $pp_end_date <= $end_epoch ) {
			//$pps_obj->setPayPeriodTimeZone();
			while ( $pp_end_date < $end_epoch ) {
				$pps_obj->getNextPayPeriod($pp_end_date);
				$retarr[] = array('start_date' => $pps_obj->getNextStartDate(), 'end_date' => $pps_obj->getNextEndDate() );
				$pp_end_date = $pps_obj->getNextEndDate();
			}
			//$pps_obj->setOriginalTimeZone();
		}

		//Debug::Arr($retarr, 'Pay Period array between Start: '.  TTDate::getDate('DATE+TIME', $start_epoch ) .' End: '.  TTDate::getDate('DATE+TIME', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}
	function getPayPeriodDatesFromArray( $pay_period_arr, $epoch ) {
		if ( is_array($pay_period_arr) ) {
			foreach( $pay_period_arr as $pp_dates ) {
				if ( $epoch >= $pp_dates['start_date'] AND $epoch <= $pp_dates['end_date']) {
					return $pp_dates;
				}
			}
		}

		return FALSE;
	}

	//$current_amount is the amount of time currently being entered.
	//$previous_amount is the old amount that is currently be edited.
	function getAccrualBalanceWithProjection( $u_obj, $epoch, $current_time, $previous_time = 0, $other_policy_balance_arr = FALSE ) {
		// Available Balance:			   10hrs
		// Current Time:					8hrs
		// Remaining Balance:				2hrs
		//
		// Projected Balance by 01-Jul-12: 15hrs
		// Projected Remaining Balance:		7hrs

		//Now that multiple Accrual Policies can deposit to the same account, we need to loop through all accrual policies that affect
		//any given account and add the projected balances together.
		$other_policy_projected_balance = 0;
		if ( is_array($other_policy_balance_arr) AND isset($other_policy_balance_arr['projected_balance']) ) {
			$other_policy_projected_balance = $other_policy_balance_arr['projected_balance'];
			Debug::Text('Other Policy Projected Balance: '. TTDate::getHours( $other_policy_projected_balance ), __FILE__, __LINE__, __METHOD__, 10);

		}

		//Previous time is time already taken into account in the balance, so add that back in here.
		$available_balance = ( $previous_time + $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() ) );
		$projected_accrual = ( ( $available_balance + $this->getProjectedAccrualAmount( $u_obj, time(), $epoch ) ) + $other_policy_projected_balance );

		$retarr = array(
						'available_balance' => $available_balance,
						'current_time' => $current_time,
						'remaining_balance' => $available_balance - $current_time,
						'projected_balance' => $projected_accrual,
						'projected_remaining_balance' => $projected_accrual - $current_time,
						);

		Debug::Arr($retarr, 'Projected Accrual Arr: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('Remaining Balance: '. TTDate::getHours( $retarr['remaining_balance']) .' Projected Remaining Balance: '. TTDate::getHours( $retarr['projected_remaining_balance']), __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;

	}

	function getProjectedAccrualAmount( $u_obj, $start_epoch, $end_epoch ) {
		$start_epoch = TTDate::getMiddleDayEpoch( $start_epoch );
		$end_epoch = TTDate::getMiddleDayEpoch( $end_epoch );

		$offset = 79200;

		$accrual_amount = 0;

		Debug::Text('Start Date '.	TTDate::getDate('DATE+TIME', $start_epoch ) .' End Date: '.	 TTDate::getDate('DATE+TIME', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10);

		$ppslf = TTNew('PayPeriodScheduleListFactory');
		$ppslf->getByCompanyIdAndUserId($u_obj->getCompany(), $u_obj->getId() );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$accrual_balance = $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() );

			$pay_period_arr = array();
			if ( $this->getApplyFrequency() == 10 ) {
				$pay_period_arr = $this->getPayPeriodArray( $pps_obj, $u_obj, $start_epoch, $end_epoch );
			}

			$accrual_amount = 0;
			for( $epoch = $start_epoch; $epoch <= $end_epoch; $epoch += 86400) {
				$epoch = ( TTDate::getBeginDayEpoch( $epoch ) + 7200) ; //This is required because the epoch has to be slightly AFTER the pay period end date, which is 11:59PM.
				$accrual_amount += $this->calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_balance, FALSE );
			}

			Debug::Text('Projected Accrual Amount: '. TTDate::getHours( $accrual_amount ), __FILE__, __LINE__, __METHOD__, 10);
		}

		return $accrual_amount;
	}

	//Calculate the accrual amount based on a given user/time.
	function calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_balance, $update_records = TRUE ) {
		$retval = 0;

		Debug::Text('User: '. $u_obj->getFullName() .' Status: '. $u_obj->getStatus() .' Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		//Make sure only active employees accrue time *after* their hire date.
		//Will this negatively affect Employees who may be on leave?
		if ( $u_obj->getStatus() == 10
				AND $epoch >= $u_obj->getHireDate()
				AND ( $this->getMinimumEmployedDays() == 0
					OR TTDate::getDays( ($epoch - $u_obj->getHireDate()) ) >= $this->getMinimumEmployedDays() ) ) {
			Debug::Text('  User is active and has been employed long enough.', __FILE__, __LINE__, __METHOD__, 10);

			$annual_pay_periods = $pps_obj->getAnnualPayPeriods();
			$in_apply_frequency_window = FALSE;
			$in_apply_rollover_window = FALSE;
			$pay_period_start_date = NULL;
			$accrual_amount = 0;
			if ( $this->getType() == 30 ) {
				Debug::Text('  Accrual policy is hour based, real-time window.', __FILE__, __LINE__, __METHOD__, 10);

				//Hour based, apply frequency is real-time.
				$in_apply_frequency_window = TRUE;
			} else {
				if ( $this->getApplyFrequency() == 10 ) {
					$pay_period_dates = $this->getPayPeriodDatesFromArray( $pay_period_arr, ($epoch - $offset) );
					if ( is_array( $pay_period_dates ) ) {
						Debug::Text('   Pay Period Start Date: '. TTDate::getDate('DATE+TIME', $pay_period_dates['start_date'] ) .' End Date: '. TTDate::getDate('DATE+TIME', $pay_period_dates['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);
						if ( $this->inApplyFrequencyWindow( $epoch, $offset, $pay_period_dates['end_date'] ) == TRUE ) {
							$in_apply_frequency_window = TRUE;

							$pay_period_start_date = $pay_period_dates['start_date']; //Used for inRolloverFrequencyWindow
						} else {
							Debug::Text('  User not in Apply Frequency Window: ', __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::Arr($pay_period_dates, '   No Pay Period Dates Found.', __FILE__, __LINE__, __METHOD__, 10);
					}
				} elseif ( $this->inApplyFrequencyWindow( $epoch, $offset, NULL, $u_obj->getHireDate() ) == TRUE ) {
					Debug::Text('  User IS in NON-PayPeriod Apply Frequency Window.', __FILE__, __LINE__, __METHOD__, 10);
					$in_apply_frequency_window = TRUE;
				} else {
					//Debug::Text('  User is not in Apply Frequency Window.', __FILE__, __LINE__, __METHOD__, 10);
					$in_apply_frequency_window = FALSE;
				}
			}

			if ( $this->inRolloverFrequencyWindow( $epoch, $offset, $u_obj->getHireDate(), $pay_period_start_date ) ) {
				Debug::Text('   In rollover window...', __FILE__, __LINE__, __METHOD__, 10);
				$in_apply_rollover_window = TRUE;
			}

			if ( $in_apply_frequency_window == TRUE OR $in_apply_rollover_window == TRUE ) {
				$milestone_obj = $this->getActiveMilestoneObject( $u_obj, $epoch );
			}

			if ( $in_apply_rollover_window == TRUE AND ( isset($milestone_obj) AND is_object( $milestone_obj ) ) ) {
				//Handle maximum rollover adjustments before continuing.
				if ( $accrual_balance > $milestone_obj->getRolloverTime() ) {
					$rollover_accrual_adjustment = bcsub( $milestone_obj->getRolloverTime(), $accrual_balance, 0);
					Debug::Text('   Adding rollover adjustment of: '. $rollover_accrual_adjustment, __FILE__, __LINE__, __METHOD__, 10);

					//Check to make sure there isn't an identical entry already made.
					//Ignore rollover adjustment is another adjustment of any amount has been made on the same day.
					$alf = TTnew( 'AccrualListFactory' );
					if ( $update_records == TRUE ) {
						$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccountAndTypeIDAndTimeStamp( $u_obj->getCompany(), $u_obj->getID(), $this->getAccrualPolicyAccount(), 60, TTDate::getMiddleDayEpoch( $epoch ) );
					}
					if ( $alf->getRecordCount() == 0 ) {
						//Get effective date, try to use the current milestone rollover date to make things more clear.
						$current_milestone_rollover_date = $this->getCurrentMilestoneRolloverDate( $epoch, $u_obj->getHireDate() );
						//If milestone rollover date comes after the current epoch, back date it by one year.
						if ( $current_milestone_rollover_date > $epoch ) {
							$current_milestone_rollover_date = mktime( 0, 0, 0, TTDate::getMonth($current_milestone_rollover_date), TTDate::getDayOfMonth($current_milestone_rollover_date), (TTDate::getYear($epoch) - 1) );
						}

						if ( $update_records == TRUE ) {
							//Don't round to the nearest minute, as that can cause too much error on weekly frequencies.
							$af = TTnew( 'AccrualFactory' );
							$af->setUser( $u_obj->getID() );
							$af->setType( 60 ); //Rollover Adjustment
							$af->setAccrualPolicyAccount( $this->getAccrualPolicyAccount() );
							$af->setAccrualPolicy( $this->getId() );
							$af->setAmount( $rollover_accrual_adjustment );
							$af->setTimeStamp( TTDate::getMiddleDayEpoch( $current_milestone_rollover_date ) );
							$af->setEnableCalcBalance( TRUE );

							if ( $af->isValid() ) {
								$af->Save();
							}
						} else {
							Debug::Text('   NOT UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10);
							$retval = $rollover_accrual_adjustment;
						}

						//Make sure we get updated balance after rollover adjustment was made.
						$accrual_balance += $rollover_accrual_adjustment;

						unset($current_milestone_rollover_date);
					} else {
						Debug::Text('   Found duplicate rollover accrual entry, skipping...', __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::Text('   Balance hasnt exceeded rollover adjustment...', __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($rollover_accrual_adjustment, $alf, $af);
			}

			if ( $in_apply_frequency_window === TRUE ) {
				if ( isset($milestone_obj) AND is_object( $milestone_obj ) ) {
					Debug::Text('  Found Matching Milestone, Accrual Rate: (ID: '. $milestone_obj->getId() .') '. $milestone_obj->getAccrualRate() .'/year', __FILE__, __LINE__, __METHOD__, 10);

					//Make sure we get updated balance after rollover adjustment was made.
					if ( $accrual_balance < $milestone_obj->getMaximumTime() ) {
						$accrual_amount = $this->calcAccrualAmount( $milestone_obj, 0, $annual_pay_periods);

						if ( $accrual_amount > 0 ) {
							$new_accrual_balance = bcadd( $accrual_balance, $accrual_amount);

							//If Maximum time is set to 0, make that unlimited.
							if ( $milestone_obj->getMaximumTime() > 0 AND $new_accrual_balance > $milestone_obj->getMaximumTime() ) {
								$accrual_amount = bcsub( $milestone_obj->getMaximumTime(), $accrual_balance, 0 );
							}
							Debug::Text('   Min/Max Adjusted Accrual Amount: '. $accrual_amount .' Limits: Min: '. $milestone_obj->getMinimumTime() .' Max: '. $milestone_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

							//Check to make sure there isn't an identical entry already made.
							$alf = TTnew( 'AccrualListFactory' );
							if ( $update_records == TRUE ) {
								$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccountAndTimeStampAndAmount( $u_obj->getCompany(), $u_obj->getID(), $this->getAccrualPolicyAccount(), TTDate::getMiddleDayEpoch( $epoch ), $accrual_amount );
							}
							if ( $alf->getRecordCount() == 0 ) {
								if ( $update_records == TRUE ) {
									Debug::Text('   UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10);
									//Round to nearest 1min
									$af = TTnew( 'AccrualFactory' );
									$af->setUser( $u_obj->getID() );
									$af->setType( 75 ); //Accrual Policy
									$af->setAccrualPolicyAccount( $this->getAccrualPolicyAccount() );
									$af->setAccrualPolicy( $this->getId() );
									$af->setAmount( $accrual_amount );
									$af->setTimeStamp( TTDate::getMiddleDayEpoch( $epoch ) );
									$af->setEnableCalcBalance( TRUE );

									if ( $af->isValid() ) {
										$af->Save();
									}
								} else {
									Debug::Text('   NOT UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10);
									$retval += $accrual_amount;
								}
							} else {
								Debug::Text('   Found duplicate accrual entry, skipping...', __FILE__, __LINE__, __METHOD__, 10);
							}
							unset($accrual_amount, $accrual_balance, $new_accrual_balance);
						} else {
							Debug::Text('   Accrual Amount is 0...', __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::Text('   Accrual Balance is outside Milestone Range. Skipping...', __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::Text('  DID NOT Find Matching Milestone.', __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($milestone_obj);
			}
		} else {
			Debug::Text('  User is not active (Status: '. $u_obj->getStatus() .') or has only been employed: '. TTDate::getDays( ($epoch - $u_obj->getHireDate()) ) .' Days, not enough. Hire Date: '. TTDate::getDATE( 'DATE+TIME', $u_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $update_records == TRUE ) {
			return TRUE;
		} else {
			Debug::Text('Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		}
	}

	function addAccrualPolicyTime( $epoch = NULL, $offset = 79200, $user_ids = FALSE ) { //22hr offset
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		Debug::Text('Accrual Policy ID: '. $this->getId() .' Current EPOCH: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		$pglf->StartTransaction();

		$pglf->getSearchByCompanyIdAndArrayCriteria( $this->getCompany(), array( 'accrual_policy_id' => array( $this->getId() ) ) );
		if ( $pglf->getRecordCount() > 0 ) {
			Debug::Text('Found Policy Group...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $pglf as $pg_obj ) {
				//Get all users assigned to this policy group.
				if ( is_array($user_ids) AND count($user_ids) > 0 AND !in_array( -1, $user_ids ) ) {
					Debug::Text('Using users passed in by filter...', __FILE__, __LINE__, __METHOD__, 10);
					$policy_group_users = array_intersect( (array)$pg_obj->getUser(), (array)$user_ids );
				} else {
					Debug::Text('Using users assigned to policy group...', __FILE__, __LINE__, __METHOD__, 10);
					$policy_group_users = $pg_obj->getUser();
				}
				if ( is_array($policy_group_users) AND count($policy_group_users) > 0 ) {
					Debug::Text('Found Policy Group Users: '. count($policy_group_users), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $policy_group_users as $user_id ) {
						Debug::Text('Policy Group User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

						//Get User Object
						$ulf = TTnew( 'UserListFactory' );
						$ulf->getByIDAndCompanyID( $user_id, $this->getCompany() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();

							//This is an optimization to detect inactive employees sooner.
							if ( $u_obj->getStatus() != 10 ) {
								Debug::Text('  Employee is not active, skipping...', __FILE__, __LINE__, __METHOD__, 10);
								continue;
							}

							//Switch to users timezone so rollover adjustments are handled on the proper date.
							$user_obj_prefs = $u_obj->getUserPreferenceObject();
							if ( is_object( $user_obj_prefs ) ) {
								$user_obj_prefs->setTimeZonePreferences();
							} else {
								//Use system timezone.
								TTDate::setTimeZone();
							}

							//Optmization to make sure we can quickly skip days outside the employment period.
							if ( $u_obj->getHireDate() != '' AND TTDate::getBeginDayEpoch( $epoch ) < TTDate::getBeginDayEpoch( $u_obj->getHireDate() ) ) {
								Debug::Text('  Before employees hire date, skipping...', __FILE__, __LINE__, __METHOD__, 10);
								continue;
							}
							if ( $u_obj->getTerminationDate() != '' AND TTDate::getBeginDayEpoch( $epoch ) > TTDate::getBeginDayEpoch( $u_obj->getTerminationDate() ) ) {
								Debug::Text('  After employees termination date, skipping...', __FILE__, __LINE__, __METHOD__, 10);
								continue;
							}

							$ppslf = TTNew('PayPeriodScheduleListFactory');
							$ppslf->getByCompanyIdAndUserId( $u_obj->getCompany(), $u_obj->getId() );
							if ( $ppslf->getRecordCount() > 0 ) {
								$pps_obj = $ppslf->getCurrent();

								$accrual_balance = $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() );

								$pay_period_arr = array();
								if ( $this->getApplyFrequency() == 10 ) {
									$pay_period_arr = $this->getPayPeriodArray( $pps_obj, $u_obj, ($epoch - $offset), ($epoch - $offset) );
								}

								$this->calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_balance, TRUE );
							}
						} else {
							Debug::Text('No User Found. Company ID: '. $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				}
			}
		}

		$pglf->CommitTransaction();

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this accrual policy.
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByAccrualPolicyAccount( $this->getId() );
			if ( $alf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This accrual policy is in use'));

			}
		}

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

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
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'apply_frequency':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Accrual Policy'), NULL, $this->getTable(), $this );
	}
}
?>
