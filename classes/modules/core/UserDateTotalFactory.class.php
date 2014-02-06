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
 * $Revision: 11053 $
 * $Id: UserDateTotalFactory.class.php 11053 2013-09-27 23:08:52Z ipso $
 * $Date: 2013-09-27 16:08:52 -0700 (Fri, 27 Sep 2013) $
 */

/**
 * @package Core
 */
class UserDateTotalFactory extends Factory {
	protected $table = 'user_date_total';
	protected $pk_sequence_name = 'user_date_total_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $punch_control_obj = NULL;
	protected $overtime_policy_obj = NULL;
	protected $premium_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $meal_policy_obj = NULL;
	protected $break_policy_obj = NULL;
	protected $job_obj = NULL;
	protected $job_item_obj = NULL;
	protected $calc_system_total_time = FALSE;
	protected $timesheet_verification_check = FALSE;
	static $calc_future_week = FALSE; //Used for BiWeekly overtime policies to schedule future week recalculating.

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('System'),
										20 => TTi18n::gettext('Worked'),
										30 => TTi18n::gettext('Absence')
									);
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Total'),
										20 => TTi18n::gettext('Regular'),
										30 => TTi18n::gettext('Overtime'),
										40 => TTi18n::gettext('Premium'),
										100 => TTi18n::gettext('Lunch'),
										110 => TTi18n::gettext('Break')
									);
				break;
			case 'status_type':
				$retval = array(
										10 => array(10,20,30,40,100,110),
										20 => array(10),
										30 => array(10),
									);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1039-group' => TTi18n::gettext('Group'),
										'-1040-default_branch' => TTi18n::gettext('Default Branch'),
										'-1050-default_department' => TTi18n::gettext('Default Department'),
										'-1160-branch' => TTi18n::gettext('Branch'),
										'-1170-department' => TTi18n::gettext('Department'),

										'-1200-type' => TTi18n::gettext('Type'),
										'-1202-status' => TTi18n::gettext('Status'),
										'-1210-date_stamp' => TTi18n::gettext('Date'),
										'-1290-total_time' => TTi18n::gettext('Time'),

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
								'status',
								'time_stamp',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
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
						'user_id' => 'UserId',
						'user_date_id' => 'UserDateID',
						'over_time_policy_id' => 'OverTimePolicyID',
						'over_time_policy' => FALSE,
						'premium_policy_id' => 'PremiumPolicyID',
						'premium_policy' => FALSE,
						'absence_policy_id' => 'AbsencePolicyID',
						'absence_policy' => FALSE,
						'absence_policy_type_id' => FALSE,
						'meal_policy_id' => 'MealPolicyID',
						'meal_policy' => FALSE,
						'break_policy_id' => 'BreakPolicyID',
						'break_policy' => FALSE,
						'punch_control_id' => 'PunchControlID',
						'status_id' => 'Status',
						'status' => FALSE,
						'type_id' => 'Type',
						'type' => FALSE,
						'branch_id' => 'Branch',
						'branch' => FALSE,
						'department_id' => 'Department',
						'department' => FALSE,
						'job_id' => 'Job',
						'job' => FALSE,
						'job_item_id' => 'JobItem',
						'job_item' => FALSE,
						'quantity' => 'Quantity',
						'bad_quantity' => 'BadQuantity',
						'start_time_stamp' => 'StartTimeStamp',
						'end_time_stamp' => 'EndTimeStamp',
						'total_time' => 'TotalTime',
						'actual_total_time' => 'ActualTotalTime',
						'name' => FALSE,
						'override' => 'Override',

						'first_name' => FALSE,
						'last_name' => FALSE,
						'user_status_id' => FALSE,
						'user_status' => FALSE,
						'group_id' => FALSE,
						'group' => FALSE,
						'title_id' => FALSE,
						'title' => FALSE,
						'default_branch_id' => FALSE,
						'default_branch' => FALSE,
						'default_department_id' => FALSE,
						'default_department' => FALSE,

						'date_stamp' => FALSE,
						'pay_period_id' => FALSE,

						'deleted' => 'Deleted',
						);
		return $variable_function_map;
	}

    function getUserObject() {
        return $this->getGenericObject( 'UserListFactory', $this->getUserId(), 'user_obj' );
    }

	function getUserDateObject() {
		return $this->getGenericObject( 'UserDateListFactory', $this->getUserDateID(), 'user_date_obj' );
	}

	function getPunchControlObject() {
		return $this->getGenericObject( 'PunchControlListFactory', $this->getPunchControlID(), 'punch_control_obj' );
	}

	function getOverTimePolicyObject() {
		return $this->getGenericObject( 'OverTimePolicyListFactory', $this->getOverTimePolicyID(), 'overtime_policy_obj' );
	}

	function getPremiumPolicyObject() {
		return $this->getGenericObject( 'PremiumPolicyListFactory', $this->getPremiumPolicyID(), 'premium_policy_obj' );
	}

	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	function getMealPolicyObject() {
		return $this->getGenericObject( 'MealPolicyListFactory', $this->getMealPolicyID(), 'meal_policy_obj' );
	}

	function getBreakPolicyObject() {
		return $this->getGenericObject( 'BreakPolicyListFactory', $this->getBreakPolicyID(), 'break_policy_obj' );
	}

	function getJobObject() {
		return $this->getGenericObject( 'JobListFactory', $this->getJob(), 'job_obj' );
	}
	function getJobItemObject() {
		return $this->getGenericObject( 'JobItemListFactory', $this->getJobItem(), 'job_item_obj' );
	}

	function setUserDate($user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date);
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_date_id != '' ) {
			$this->setUserDateID( $user_date_id );
			return TRUE;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getUserDateID() {
		if ( isset($this->data['user_date_id']) ) {
			return $this->data['user_date_id'];
		}

		return FALSE;
	}
	function setUserDateID($id) {
		$id = trim($id);

		$udlf = TTnew( 'UserDateListFactory' );

		if (  $this->Validator->isResultSetWithRows(	'user_date',
														$udlf->getByID($id),
														TTi18n::gettext('Date/Time is incorrect, or pay period does not exist for this date')
														) ) {
			$this->data['user_date_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getOverTimePolicyID() {
		if ( isset($this->data['over_time_policy_id']) ) {
			return $this->data['over_time_policy_id'];
		}

		return FALSE;
	}
	function setOverTimePolicyID($id) {
		$id = trim($id);

		$otplf = TTnew( 'OverTimePolicyListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if (  	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'over_time_policy_id',
														$otplf->getByID($id),
														TTi18n::gettext('Invalid Overtime Policy')
														) ) {
			$this->data['over_time_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPremiumPolicyID() {
		if ( isset($this->data['premium_policy_id']) ) {
			return $this->data['premium_policy_id'];
		}

		return FALSE;
	}
	function setPremiumPolicyID($id) {
		$id = trim($id);

		$pplf = TTnew( 'PremiumPolicyListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'premium_policy_id',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Premium Policy ID')
														) ) {
			$this->data['premium_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return $this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy_id',
														$aplf->getByID($id),
														TTi18n::gettext('Invalid Absence Policy ID')
														) ) {
			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMealPolicyID() {
		if ( isset($this->data['meal_policy_id']) ) {
			return $this->data['meal_policy_id'];
		}

		return FALSE;
	}
	function setMealPolicyID($id) {
		$id = trim($id);

		$mplf = TTnew( 'MealPolicyListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'meal_policy_id',
														$mplf->getByID($id),
														TTi18n::gettext('Invalid Meal Policy ID')
														) ) {
			$this->data['meal_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBreakPolicyID() {
		if ( isset($this->data['break_policy_id']) ) {
			return $this->data['break_policy_id'];
		}

		return FALSE;
	}
	function setBreakPolicyID($id) {
		$id = trim($id);

		$bplf = TTnew( 'BreakPolicyListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'break_policy_id',
														$bplf->getByID($id),
														TTi18n::gettext('Invalid Break Policy ID')
														) ) {
			$this->data['break_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPunchControlID() {
		if ( isset($this->data['punch_control_id']) ) {
			return $this->data['punch_control_id'];
		}

		return FALSE;
	}
	function setPunchControlID($id) {
		$id = trim($id);

		$pclf = TTnew( 'PunchControlListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'punch_control_id',
														$pclf->getByID($id),
														TTi18n::gettext('Invalid Punch Control ID')
														) ) {
			$this->data['punch_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status_id',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type_id',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getTimeCategory() {
		if ( $this->getStatus() == 10 AND $this->getType() == 10 ) {
			$column = 'paid_time';
		} elseif ( $this->getStatus() == 10 AND $this->getType() == 20 ) {
			$column = 'regular_time';
		} elseif ( $this->getStatus() == 10 AND $this->getType() == 30 ) {
			$column = 'over_time_policy-'. $this->getColumn('over_time_policy_id');
		} elseif ( $this->getStatus() == 10 AND $this->getType() == 40 ) {
			$column = 'premium_policy-'. $this->getColumn('premium_policy_id');
		} elseif ( $this->getStatus() == 30 AND $this->getType() == 10 ) {
			$column = 'absence_policy-'. $this->getColumn('absence_policy_id');
		} elseif ( ( $this->getStatus() == 20 AND $this->getType() == 10 ) OR ( $this->getStatus() == 10 AND $this->getType() == 100 ) OR ( $this->getStatus() == 10 AND $this->getType() == 110 ) ) {
			$column = 'worked_time';
		} else {
			$column = NULL;
		}

		return $column;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return $this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$blf = TTnew( 'BranchListFactory' );

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'branch_id',
														$blf->getByID($id),
														TTi18n::gettext('Branch does not exist')
														) ) {
			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return $this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$dlf = TTnew( 'DepartmentListFactory' );

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'department_id',
														$dlf->getByID($id),
														TTi18n::gettext('Department does not exist')
														) ) {
			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		if ( isset($this->data['job_id']) ) {
			return $this->data['job_id'];
		}

		return FALSE;
	}
	function setJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_id',
														$jlf->getByID($id),
														TTi18n::gettext('Job does not exist')
														) ) {
			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return $this->data['job_item_id'];
		}

		return FALSE;
	}
	function setJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item_id',
														$jilf->getByID($id),
														TTi18n::gettext('Job Item does not exist')
														) ) {
			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getQuantity() {
		if ( isset($this->data['quantity']) ) {
			return (float)$this->data['quantity'];
		}

		return FALSE;
	}
	function setQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}

		if 	(	$val == 0
				OR
				$this->Validator->isFloat(			'quantity',
													$val,
													TTi18n::gettext('Incorrect quantity')) ) {
			$this->data['quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getBadQuantity() {
		if ( isset($this->data['bad_quantity']) ) {
			return (float)$this->data['bad_quantity'];
		}

		return FALSE;
	}
	function setBadQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}


		if 	(	$val == 0
				OR
				$this->Validator->isFloat(			'bad_quantity',
													$val,
													TTi18n::gettext('Incorrect bad quantity')) ) {
			$this->data['bad_quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getStartTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['start_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['start_time_stamp'];
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $this->data['start_time_stamp'] );
			}
		}

		return FALSE;
	}
	function setStartTimeStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'start_time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect start time stamp'))

			) {

			$this->data['start_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}


    function getUserId() {

		if ( isset($this->tmp_data['user_id']) ) {
			return $this->tmp_data['user_id'];
		}
		return FALSE;
	}
	function setUserId( $user_id ) {
		$user_id = trim($user_id);
		$this->tmp_data['user_id'] = $user_id;

		return TRUE;
	}

	function getEndTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['end_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['end_time_stamp'];
			} else {
				return TTDate::strtotime( $this->data['end_time_stamp'] );
			}
		}

		return FALSE;
	}
	function setEndTimeStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'end_time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect end time stamp'))

			) {

			$this->data['end_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTime() {
		if ( isset($this->data['total_time']) ) {
			return (int)$this->data['total_time'];
		}
		return FALSE;
	}
	function setTotalTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'total_time',
													$int,
													TTi18n::gettext('Incorrect total time')) ) {
			$this->data['total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getActualTotalTime() {
		if ( isset($this->data['actual_total_time']) ) {
			return (int)$this->data['actual_total_time'];
		}
		return FALSE;
	}
	function setActualTotalTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'actual_total_time',
													$int,
													TTi18n::gettext('Incorrect actual total time')) ) {
			$this->data['actual_total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getOverride() {
		if ( isset($this->data['override']) ) {
			return $this->fromBool( $this->data['override'] );
		}
		return FALSE;
	}
	function setOverride($bool) {
		$this->data['override'] = $this->toBool($bool);

		return TRUE;
	}

	function getName() {
		switch ( $this->getStatus().$this->getType() ) {
			case 1010:
				$name = TTi18n::gettext('Total Time');
				break;
			case 1020:
				$name = TTi18n::gettext('Regular Time');
				break;
			case 1030:
				if ( is_object($this->getOverTimePolicyObject()) ) {
					$name = $this->getOverTimePolicyObject()->getName();
				}
				break;
			case 1040:
				if ( is_object($this->getPremiumPolicyObject()) ) {
					$name = $this->getPremiumPolicyObject()->getName();
				}
				break;
			case 10100:
				if ( is_object($this->getMealPolicyObject()) ) {
					$name = $this->getMealPolicyObject()->getName();
				}
				break;
			case 10110:
				if ( is_object($this->getBreakPolicyObject()) ) {
					$name = $this->getBreakPolicyObject()->getName();
				}
				break;
			case 3010:
				if ( is_object($this->getAbsencePolicyObject()) ) {
					$name = $this->getAbsencePolicyObject()->getName();
				}
				break;
			default:
				$name = TTi18n::gettext('N/A');
				break;
		}

		if ( isset($name) ) {
			return $name;
		}

		return FALSE;
	}

	function getEnableCalcSystemTotalTime() {
		if ( isset($this->calc_system_total_time) ) {
			return $this->calc_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcSystemTotalTime($bool) {
		$this->calc_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcWeeklySystemTotalTime() {
		if ( isset($this->calc_weekly_system_total_time) ) {
			return $this->calc_weekly_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcWeeklySystemTotalTime($bool) {
		$this->calc_weekly_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcException() {
		if ( isset($this->calc_exception) ) {
			return $this->calc_exception;
		}

		return FALSE;
	}
	function setEnableCalcException($bool) {
		$this->calc_exception = $bool;

		return TRUE;
	}

	function getEnablePreMatureException() {
		if ( isset($this->premature_exception) ) {
			return $this->premature_exception;
		}

		return FALSE;
	}
	function setEnablePreMatureException($bool) {
		$this->premature_exception = $bool;

		return TRUE;
	}

	function getEnableCalcAccrualPolicy() {
		if ( isset($this->calc_accrual_policy) ) {
			return $this->calc_accrual_policy;
		}

		return FALSE;
	}
	function setEnableCalcAccrualPolicy($bool) {
		$this->calc_accrual_policy = $bool;

		return TRUE;
	}

	static function getEnableCalcFutureWeek() {
		if ( isset(self::$calc_future_week) ) {
			return self::$calc_future_week;
		}

		return FALSE;
	}
	static function setEnableCalcFutureWeek($bool) {
		self::$calc_future_week = $bool;

		return TRUE;
	}

	function getEnableTimeSheetVerificationCheck() {
		if ( isset($this->timesheet_verification_check) ) {
			return $this->timesheet_verification_check;
		}

		return FALSE;
	}
	function setEnableTimeSheetVerificationCheck($bool) {
		$this->timesheet_verification_check = $bool;

		return TRUE;
	}

	function getDailyTotalTime() {
		$udtlf = TTnew( 'UserDateTotalListFactory' );

		$daily_total_time = $udtlf->getTotalSumByUserDateID( $this->getUserDateID() );
		Debug::text('Daily Total Time for Day: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $daily_total_time;
	}

	function deleteSystemTotalTime() {
		//Delete everything that is not overrided.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$pcf = TTnew( 'PunchControlFactory' );

		//Optimize for a direct delete query.
		if ( $this->getUserDateID() > 0 ) {

			//Due to a MySQL gotcha: http://dev.mysql.com/doc/refman/5.0/en/subquery-errors.html
			//We need to wrap the subquery in a subquery of itself to hide it from MySQL
			//So it doesn't complain about updating a table and selecting from it at the same time.
			//MySQL v5.0.22 DOES NOT like this query, it takes 10+ seconds to run and seems to cause a deadlock.
			//Switch back to a select then a bulkDelete instead. Still fast enough I think.
			$udtlf->getByUserDateIdAndStatusAndOverrideAndMisMatchPunchControlUserDateId( $this->getUserDateID(), array(10,30), FALSE ); //System totals
			$this->bulkDelete( $this->getIDSByListFactory( $udtlf ) );
		} else {
			Debug::text('NO System Total Records to delete...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function processTriggerTimeArray( $trigger_time_arr, $weekly_total_time = 0 ) {
		if ( is_array($trigger_time_arr) == FALSE OR count($trigger_time_arr) == 0 ) {
			return FALSE;
		}

		//Debug::Arr($trigger_time_arr, 'Source Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		//Create a duplicate trigger_time_arr that we can sort so we know the
		//first trigger time is always the first in the array.
		//We don't want to use this array in the loop though, because it throws off other ordering.
		$tmp_trigger_time_arr = Sort::multiSort( $trigger_time_arr, 'trigger_time' );
		$first_trigger_time = $tmp_trigger_time_arr[0]['trigger_time']; //Get first trigger time.
		//Debug::Arr($tmp_trigger_time_arr, 'Trigger Time After Sort: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Weekly Total Time: '. (int)$weekly_total_time .' First Trigger Time: '. $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
		unset($tmp_trigger_time_arr);

		//Sort trigger_time array by calculation order before looping over it.
		//$trigger_time_arr = Sort::multiSort( $trigger_time_arr, 'calculation_order', 'trigger_time', 'asc', 'desc' );
		$trigger_time_arr = Sort::arrayMultiSort( $trigger_time_arr, array( 'calculation_order' => SORT_ASC, 'trigger_time' => SORT_DESC, 'combined_rate' => SORT_DESC )  );
		//Debug::Arr($trigger_time_arr, 'Source Trigger Arr After Calculation Order Sort: ', __FILE__, __LINE__, __METHOD__, 10);

		//We need to calculate regular time as early as possible so we can adjust the trigger time
		//of weekly overtime policies and re-sort the array.
		$tmp_trigger_time_arr = array();
		foreach( $trigger_time_arr as $key => $trigger_time_data ) {
			if ( $trigger_time_data['over_time_policy_type_id'] == 20 OR $trigger_time_data['over_time_policy_type_id'] == 30 OR $trigger_time_data['over_time_policy_type_id'] == 210 ) {
				if ( is_numeric($weekly_total_time)
						AND $weekly_total_time > 0
						AND $weekly_total_time >= $trigger_time_data['trigger_time'] ) {
					//Worked more then weekly trigger time already.
					Debug::Text('Worked more then weekly trigger time...', __FILE__, __LINE__, __METHOD__, 10);

					$tmp_trigger_time = 0;
				} else {
					//Haven't worked more then the weekly trigger time yet.
					$tmp_trigger_time = $trigger_time_data['trigger_time'] - $weekly_total_time;
					Debug::Text('NOT Worked more then weekly trigger time... TMP Trigger Time: '. $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

					if ( is_numeric($weekly_total_time)
						AND $weekly_total_time > 0
						AND $tmp_trigger_time > $first_trigger_time ) {
						Debug::Text('Using First Trigger Time: '. $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_trigger_time = $first_trigger_time;
					}
				}

				$trigger_time_arr[$key]['trigger_time'] = $tmp_trigger_time;
			} else {
				Debug::Text('NOT weekly overtime policy...', __FILE__, __LINE__, __METHOD__, 10);

				$tmp_trigger_time = $trigger_time_data['trigger_time'];
			}

			Debug::Text('Trigger Time: '. $tmp_trigger_time .' Overtime Policy Id: '. $trigger_time_data['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
			if ( !in_array( $tmp_trigger_time, $tmp_trigger_time_arr) ) {
				Debug::Text('Adding policy to final array... Trigger Time: '. $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
				$trigger_time_data['trigger_time'] = $tmp_trigger_time;
				$retval[] = $trigger_time_data;
			} else {
				Debug::Text('NOT Adding policy to final array...', __FILE__, __LINE__, __METHOD__, 10);
			}

			$tmp_trigger_time_arr[] = $trigger_time_arr[$key]['trigger_time'];
		}
		unset($trigger_time_arr, $tmp_trigger_time_arr, $trigger_time_data);

		$retval = Sort::multiSort( $retval, 'trigger_time' );
		//Debug::Arr($retval, 'Dest Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		//Loop through final array and remove policies with higher trigger times and lower rates.
		//The rate matters as we don't want one policy after 8hrs to have a lower rate than a policy after 0hrs. (ie: Holiday OT after 0hrs @ 2x and Daily OT after 8hrs @ 1.5x)
		//Are there any scenarios where an employee works more hours and gets a lesser rate?
		$prev_combined_rate = 0;
		foreach( $retval as $key => $policy_data ) {
			if ( $policy_data['combined_rate'] < $prev_combined_rate ) {
				Debug::Text('Removing policy with higher trigger time and lower combined rate... Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				unset($retval[$key]);
			} else {
				$prev_combined_rate = $policy_data['combined_rate'];
			}
		}
		unset($key,$policy_data);
		$retval = array_values($retval); //Rekey the array so there are no gaps.
		//Debug::Arr($retval, 'zDest Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function calcOverTimePolicyTotalTime( $udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr ) {
		global $profiler;

		$profiler->startTimer( 'UserDateTotal::calcOverTimePolicyTotalTime() - Part 1');

		//If this user is scheduled, get schedule overtime policy id.
		$schedule_total_time = 0;
		$schedule_over_time_policy_id = 0;
		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getByUserDateIdAndStatusId( $this->getUserDateID(), 10 ); //FIXME: Allow overtime policies to be specified on absence shifts too, like premium policies?
		if ( $slf->getRecordCount() > 0 ) {
			//Check for schedule policy
			foreach ( $slf as $s_obj ) {
				Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
				$schedule_total_time += $s_obj->getTotalTime();

				if ( is_object($s_obj->getSchedulePolicyObject()) AND $s_obj->getSchedulePolicyObject()->getOverTimePolicyID() != FALSE ) {
					$schedule_over_time_policy_id = $s_obj->getSchedulePolicyObject()->getOverTimePolicyID();
					Debug::text('Found New Schedule Overtime Policies to apply: '. $schedule_over_time_policy_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		} else {
			//If they are not scheduled, we use the PolicyGroup list to get a Over Schedule / No Schedule overtime policy.
			//We could check for an active recurring schedule, but there could be multiple, and which
			//one do we use?
		}

		//Apply policies for OverTime hours
		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$otp_calculation_order = $otplf->getOptions('calculation_order');
		$otplf->getByPolicyGroupUserIdOrId( $this->getUserDateObject()->getUser(), $schedule_over_time_policy_id );
		if ( $otplf->getRecordCount() > 0 ) {
			Debug::text('Found Overtime Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);

			//Get Pay Period Schedule info
			if ( is_object($this->getUserDateObject()->getPayPeriodObject())
					AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
				$start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
			} else {
				$start_week_day_id = 0;
			}
			Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

			//Convert all OT policies to daily before applying.
			//For instance, 40+hrs/week policy if they are currently at 35hrs is a 5hr daily policy.
			//For weekly OT policies, they MUST include regular time + other WEEKLY over time rules.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$weekly_total = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
			Debug::text('Weekly Total: '. (int)$weekly_total, __FILE__, __LINE__, __METHOD__, 10);

			//Daily policy always takes precedence, then Weekly, Bi-Weekly, Day Of Week etc...
			//So unless the next policy in the list has a lower trigger time then the previous policy
			//We ignore it.
			//ie: if Daily OT is after 8hrs, and Day Of Week is after 10. Day of week will be ignored.
			//	If Daily OT is after 8hrs, and Weekly is after 40, and they worked 35 up to yesterday,
			//	and 12 hrs today, from 5hrs to 8hrs will be weekly, then anything after that is daily.
			//FIXME: Take rate into account, so for example if we have a daily OT policy after 8hrs at 1.5x
			//  and a Holiday OT policy after 0hrs at 2.0x. If the employee works 10hrs on the holiday we want all 10hrs to be Holiday time.
			//  We shouldn't go back to a lesser rate of 1.5x for the Daily OT policy. However if we do this we also need to take into account accrual rates, as time could be banked.
			//  Combine Rate and Accrual rate to use for sorting, as some 2.0x rate overtime policies might accrual/bank it all (Rate: 0 Accrual Rate: 2.0), but it should still be considered a 2.0x rate.
			//  *The work around for this currently is to have multiple holiday policies that match the daily overtime policies so they take priority.
			$tmp_trigger_time_arr = array();
			foreach( $otplf as $otp_obj ) {
				Debug::text('  Checking Against Policy: '. $otp_obj->getName() .' Trigger Time: '. $otp_obj->getTriggerTime() , __FILE__, __LINE__, __METHOD__, 10);
				$trigger_time = NULL;

				switch( $otp_obj->getType() ) {
					case 10: //Daily
						$trigger_time = $otp_obj->getTriggerTime();
						Debug::text(' Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						break;
					case 20: //Weekly
						$trigger_time = $otp_obj->getTriggerTime();
						Debug::text(' Weekly Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						break;
					case 30: //Bi-Weekly
						//Convert biweekly into a weekly policy by taking the hours worked in the
						//first of the two week period and reducing the trigger time by that amount.
						//When does the bi-weekly cutoff start though? It must have a hard date that it can be based on so we don't count the same week twice.
						//Try to synchronize it with the week of the first pay period? Just figure out if we are odd or even weeks.
						//FIXME: Set flag that tells smartRecalculate to calculate the next week or not.
						$week_modifier = 0; //0=Even, 1=Odd
						if ( is_object( $this->getUserDateObject()->getPayPeriodObject() ) ) {
							$week_modifier = TTDate::getWeek($this->getUserDateObject()->getPayPeriodObject()->getStartDate(), $start_week_day_id) % 2;
						}
						$current_week_modifier = TTDate::getWeek( $this->getUserDateObject()->getDateStamp(), $start_week_day_id ) % 2;
						Debug::text(' Current Week: '. $current_week_modifier .' Week Modifier: '. $week_modifier, __FILE__, __LINE__, __METHOD__, 10);

						$first_week_total = 0;
						if ( $current_week_modifier != $week_modifier ) {
							//$udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch() uses "< $epoch" so the current day is ignored, but in this
							//case we want to include the last day of the week, so we need to add one day to this argument.
							$first_week_total = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch( (TTDate::getMiddleDayEpoch($this->getUserDateObject()->getDateStamp())-(86400*7) ), $start_week_day_id)+86400, TTDate::getBeginWeekEpoch( (TTDate::getMiddleDayEpoch($this->getUserDateObject()->getDateStamp())-(86400*7)), $start_week_day_id) );
							Debug::text(' Week modifiers differ, calculate total time for the first week: '. $first_week_total, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);
						}

						$trigger_time = ( $otp_obj->getTriggerTime() - $first_week_total );
						if ( $trigger_time < 0 ) {
							$trigger_time = 0;
						}
						Debug::text('Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);

						unset($first_week_total, $week_modifier, $current_week_modifier);
						break;
					case 40: //Sunday
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 0 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 50: //Monday
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 1 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 60: //Tuesday
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 2 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 70: //Wed
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 3 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 80: //Thu
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 4 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 90: //Fri
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 5 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 100: //Sat
						if ( date('w', $this->getUserDateObject()->getDateStamp() ) == 6 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 150: //2-day/week Consecutive
					case 151: //3-day/week Consecutive
					case 152: //4-day/week Consecutive
					case 153: //5-day/week Consecutive
					case 154: //6-day/week Consecutive
					case 155: //7-day/week Consecutive
						switch ( $otp_obj->getType() ) {
							case 150:
								$minimum_days_worked = 2;
								break;
							case 151:
								$minimum_days_worked = 3;
								break;
							case 152:
								$minimum_days_worked = 4;
								break;
							case 153:
								$minimum_days_worked = 5;
								break;
							case 154:
								$minimum_days_worked = 6;
								break;
							case 155:
								$minimum_days_worked = 7;
								break;
						}

						//Should these be reset on the week boundary or should any consecutive days worked apply? Or should we offer both options?
						//We should probably break this out to just a general "consecutive days worked" and add a field to specify any number of days
						//and a field to specify if its only per week, or any timeframe.
						//Will probably want to include a flag to consider scheduled days only too.
						$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id), $this->getUserDateObject()->getDateStamp() );

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						if ( $weekly_days_worked >= $minimum_days_worked AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT After Days Consecutive Worked...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						unset($days_worked_arr, $weekly_days_worked, $minimum_days_worked);
						break;
					case 300: //2-day Consecutive
					case 301: //3-day Consecutive
					case 302: //4-day Consecutive
					case 303: //5-day Consecutive
					case 304: //6-day Consecutive
					case 305: //7-day Consecutive
						switch ( $otp_obj->getType() ) {
							case 300:
								$minimum_days_worked = 2;
								break;
							case 301:
								$minimum_days_worked = 3;
								break;
							case 302:
								$minimum_days_worked = 4;
								break;
							case 303:
								$minimum_days_worked = 5;
								break;
							case 304:
								$minimum_days_worked = 6;
								break;
							case 305:
								$minimum_days_worked = 7;
								break;
						}

						//This does not reset on the week boundary.
						$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp()-(86400*$minimum_days_worked), $this->getUserDateObject()->getDateStamp() );

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						//Since these can span overtime weeks, we need to calculate the future week as well.
						UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);

						if ( $weekly_days_worked >= $minimum_days_worked AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT After Days Consecutive Worked...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						unset($days_worked_arr, $weekly_days_worked, $minimum_days_worked);
						break;
					case 350: //2nd Consecutive Day
					case 351: //3rd Consecutive Day
					case 352: //4th Consecutive Day
					case 353: //5th Consecutive Day
					case 354: //6th Consecutive Day
					case 355: //7th Consecutive Day
						switch ( $otp_obj->getType() ) {
							case 350:
								$minimum_days_worked = 2;
								break;
							case 351:
								$minimum_days_worked = 3;
								break;
							case 352:
								$minimum_days_worked = 4;
								break;
							case 353:
								$minimum_days_worked = 5;
								break;
							case 354:
								$minimum_days_worked = 6;
								break;
							case 355:
								$minimum_days_worked = 7;
								break;
						}

						$range_start_date = TTDate::getMiddleDayEpoch( $this->getUserDateObject()->getDateStamp() )-(86400*$minimum_days_worked);

						$previous_day_with_overtime_result = $udtlf->getPreviousDayByUserIdAndStartDateAndEndDateAndOverTimePolicyId( $this->getUserDateObject()->getUser(), $range_start_date, $this->getUserDateObject()->getDateStamp(), $otp_obj->getId() );
						if ( $previous_day_with_overtime_result !== FALSE ) {
							$previous_day_with_overtime = TTDate::getMiddleDayEpoch( TTDate::strtotime( $previous_day_with_overtime_result ) );
							Debug::text(' Previous Day with OT: '. TTDate::getDate('DATE', $previous_day_with_overtime ) .' Start Date: '. TTDate::getDate('DATE',  $range_start_date ) .' End Date: '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( isset( $previous_day_with_overtime ) AND $previous_day_with_overtime >= $range_start_date ) {
							$range_start_date = TTDate::getMiddleDayEpoch( $previous_day_with_overtime )+86400;
							Debug::text(' bPrevious Day with OT: '. TTDate::getDate('DATE', $previous_day_with_overtime ) .' Start Date: '. TTDate::getDate('DATE',  $range_start_date ) .' End Date: '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);
						}
						
						//This does not reset on the week boundary.
						$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), $range_start_date, $this->getUserDateObject()->getDateStamp() );
						sort($days_worked_arr);
						
						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						//Since these can span overtime weeks, we need to calculate the future week as well.
						UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);

						$days_worked_arr_key = $minimum_days_worked-1;
						if ( $weekly_days_worked >= $minimum_days_worked AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE AND isset($days_worked_arr[$days_worked_arr_key]) AND TTDate::getMiddleDayEpoch( TTDate::strtotime( $days_worked_arr[$days_worked_arr_key] ) ) == TTDate::getMiddleDayEpoch( $this->getUserDateObject()->getDateStamp() ) ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT After Days Consecutive Worked...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						unset($range_start_date, $previous_day_with_overtime, $previous_day_with_overtime, $days_worked_arr, $weekly_days_worked, $minimum_days_worked);
						break;
					case 180: //Holiday
						$hlf = TTnew( 'HolidayListFactory' );
						$hlf->getByPolicyGroupUserIdAndDate( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp() );
						if ( $hlf->getRecordCount() > 0 ) {
							$holiday_obj = $hlf->getCurrent();
							Debug::text(' Found Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__,10);

							if ( $holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
									OR $holiday_obj->isEligible( $this->getUserDateObject()->getUser() ) ) {
								$trigger_time = $otp_obj->getTriggerTime();
								Debug::text(' Is Eligible for Holiday: '. $holiday_obj->getName() .' Daily Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);

							} else {
								Debug::text(' Not Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
								continue 2; //Skip to next policy
							}
						} else {
							Debug::text(' Not Holiday...', __FILE__, __LINE__, __METHOD__, 10);
							continue 2; //Skip to next policy
						}
						unset($hlf, $holiday_obj);

						break;
					case 200: //Over schedule (Daily) / No Schedule. Have trigger time extend the schedule time.
						$trigger_time = $schedule_total_time + $otp_obj->getTriggerTime();
						Debug::text(' Over Schedule/No Schedule Trigger Time: '. $trigger_time , __FILE__, __LINE__, __METHOD__, 10);
						break;
					case 210: //Over Schedule (Weekly) / No Schedule
						//Get schedule time for the entire week, and add the Active After time to that.
						$schedule_weekly_total_time = $slf->getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
						Debug::text('Schedule Weekly Total Time: '. $schedule_weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);

						$trigger_time = $schedule_weekly_total_time + $otp_obj->getTriggerTime();
						unset($schedule_weekly_total_time);
						break;
				}

				if ( is_numeric($trigger_time) AND $trigger_time < 0 ) {
					$trigger_time = 0;
				}

				if ( is_numeric($trigger_time) ) {
					$trigger_time_arr[] = array('calculation_order' => $otp_calculation_order[$otp_obj->getType()],  'trigger_time' => $trigger_time, 'over_time_policy_id' => $otp_obj->getId(), 'over_time_policy_type_id' => $otp_obj->getType(), 'combined_rate' => ($otp_obj->getRate()+$otp_obj->getAccrualRate()) );
				}

				unset($trigger_time);
			}

			if ( isset($trigger_time_arr) ) {
				$trigger_time_arr = $this->processTriggerTimeArray( $trigger_time_arr, $weekly_total );
			}

			//Debug::Arr($trigger_time_arr, 'Trigger Time Array', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::text('    No OverTime Policies found for this user.', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset($otp_obj, $otplf);

		if ( isset($trigger_time_arr) ) {
			$total_daily_hours = 0;
			$total_daily_hours_used = 0;
			//get all worked total hours.

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text('Found Total Hours to attempt to apply policy: Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				if ( $trigger_time_arr[0]['trigger_time'] > 0 ) {
					//No trigger time set at 0.
					$enable_regular_hour_calculating = TRUE;
				} else {
					$enable_regular_hour_calculating = FALSE;
				}
				$tmp_policy_total_time = NULL;
				foreach( $udtlf as $udt_obj ) {
					//Ignore incomplete punches
					if ( $udt_obj->getTotalTime() == 0 ) {
						continue;
					}

					$udt_total_time = $udt_obj->getTotalTime();
					if ( isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] ) ) {
						$udt_total_time = bcadd( $udt_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
					}
					if ( isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] ) ) {
						$udt_total_time = bcadd( $udt_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
					}
					$total_daily_hours = bcadd( $total_daily_hours, $udt_total_time );

					//Loop through each trigger.
					$i=0;

					Debug::text('Total Hour: ID: '. $udt_obj->getId() .' Status: '. $udt_obj->getStatus() .' Total Time: '. $udt_obj->getTotalTime() .' Total Daily Hours: '. $total_daily_hours .' Used Total Time: '. $total_daily_hours_used .' Branch ID: '. $udt_obj->getBranch() .' Department ID: '. $udt_obj->getDepartment() .' Job ID: '. $udt_obj->getJob() .' Job Item ID: '. $udt_obj->getJobItem() .' Quantity: '. $udt_obj->getQuantity(), __FILE__, __LINE__, __METHOD__, 10);

					foreach( $trigger_time_arr as $trigger_time_data ) {

						if ( isset($trigger_time_arr[$i+1]['trigger_time']) AND $total_daily_hours_used >= $trigger_time_arr[$i+1]['trigger_time'] ) {
							Debug::text('     '. $i .': SKIPPING THIS TRIGGER TIME: '. $trigger_time_data['trigger_time'], __FILE__, __LINE__, __METHOD__, 10);
							$i++;
							continue;
						}

						Debug::text('     '. $i .': Trigger Time Data: Trigger Time: '. $trigger_time_data['trigger_time'] .' ID: '. $trigger_time_data['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
						Debug::text('     '. $i .': Used Total Time: '. $total_daily_hours_used, __FILE__, __LINE__, __METHOD__, 10);

						//Only consider Regular Time ONCE per user date total row.
						if ( $i == 0
								AND $trigger_time_arr[$i]['trigger_time'] > 0
								AND $total_daily_hours_used < $trigger_time_arr[$i]['trigger_time'] ) {
							Debug::text('     '. $i .': Trigger Time: '. $trigger_time_arr[$i]['trigger_time'] .' greater then 0, found Regular Time.', __FILE__, __LINE__, __METHOD__, 10);

							if ( $total_daily_hours > $trigger_time_arr[$i]['trigger_time'] ) {
								$regular_total_time = $trigger_time_arr[$i]['trigger_time'] - $total_daily_hours_used;

								$regular_quantity_percent = bcdiv($trigger_time_arr[$i]['trigger_time'], $udt_obj->getTotalTime() );
								$regular_quantity = round( bcmul($udt_obj->getQuantity(), $regular_quantity_percent) , 2);
								$regular_bad_quantity = round( bcmul( $udt_obj->getBadQuantity(), $regular_quantity_percent), 2);
							} else {
								//$regular_total_time = $udt_obj->getTotalTime();
								$regular_total_time = $udt_total_time;
								$regular_quantity = $udt_obj->getQuantity();
								$regular_bad_quantity = $udt_obj->getBadQuantity();
							}
							Debug::text('     '. $i .': Regular Total Time: '. $regular_total_time .' Regular Quantity: '. $regular_quantity, __FILE__, __LINE__, __METHOD__, 10);

							if ( isset($user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()] ) ) {
								Debug::text('     Adding to Compact Array: Branch: '. (int)$udt_obj->getBranch() .' Department: '. (int)$udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);
								$user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['total_time'] += $regular_total_time;
								$user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['quantity'] += $regular_quantity;
								$user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['bad_quantity'] += $regular_bad_quantity;
							} else {
								Debug::text('     Initiating Compact Sub-Array: Branch: '. (int)$udt_obj->getBranch() .' Department: '. (int)$udt_obj->getDepartment() , __FILE__, __LINE__, __METHOD__, 10);
								$user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()] = array( 'total_time' => $regular_total_time, 'quantity' => $regular_quantity, 'bad_quantity' => $regular_bad_quantity );
							}
							Debug::text('     Compact Array Regular Total: '. $user_data_total_compact_arr[20][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['total_time'] , __FILE__, __LINE__, __METHOD__, 10);

							$total_daily_hours_used += $regular_total_time;
						}

						Debug::text('     '. $i .': Daily Total Time: '. $total_daily_hours .' Trigger Time: '. $trigger_time_arr[$i]['trigger_time'] .' Used Total Time: '. $total_daily_hours_used .' Overtime Policy Type: '. $trigger_time_arr[$i]['over_time_policy_type_id'], __FILE__, __LINE__, __METHOD__, 10);

						if ( $total_daily_hours > $trigger_time_arr[$i]['trigger_time'] ) {
							Debug::text('     '. $i .': Trigger Time: '. $trigger_time_arr[$i]['trigger_time'] .' greater then 0, found Over Time.', __FILE__, __LINE__, __METHOD__, 10);

							if ( isset($trigger_time_arr[$i+1]['trigger_time'] ) ) {
								Debug::text('     '. $i .': Found trigger time after this one: '. $trigger_time_arr[$i+1]['trigger_time'] , __FILE__, __LINE__, __METHOD__, 10);
								$max_trigger_time = $trigger_time_arr[$i+1]['trigger_time'] - $trigger_time_arr[$i]['trigger_time'];
							} else {
								$max_trigger_time = $trigger_time_arr[$i]['trigger_time'];
							}
							Debug::text('     aMax Trigger Time '. $max_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

							if ( isset($trigger_time_arr[$i+1]['trigger_time']) AND $total_daily_hours_used > $trigger_time_arr[$i]['trigger_time'] ) {
								//$max_trigger_time = $max_trigger_time - ($total_daily_hours_used - $max_trigger_time);
								$max_trigger_time = $max_trigger_time - ($total_daily_hours_used - $trigger_time_arr[$i]['trigger_time']) ;
							}
							Debug::text('     bMax Trigger Time '. $max_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

							$over_time_total = $total_daily_hours - $total_daily_hours_used;
							if ( isset($trigger_time_arr[$i+1]['trigger_time'])
									AND $max_trigger_time > 0
									AND $over_time_total > $max_trigger_time ) {
								$over_time_total = $max_trigger_time;
							}

							if ( $over_time_total > 0 ) {
								$over_time_quantity_percent = bcdiv( $over_time_total, $udt_obj->getTotalTime() );
								$over_time_quantity = round( bcmul($udt_obj->getQuantity(), $over_time_quantity_percent), 2);
								$over_time_bad_quantity = round( bcmul($udt_obj->getBadQuantity(), $over_time_quantity_percent), 2);

								Debug::text('     Inserting Hours ('. $over_time_total .') for Policy ID: '. $trigger_time_arr[$i]['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);

								if ( isset($user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()] ) ) {
									Debug::text('     Adding to Compact Array: Policy ID: '.$trigger_time_arr[$i]['over_time_policy_id'] .' Branch: '. (int)$udt_obj->getBranch() .' Department: '. (int)$udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);
									$user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['total_time'] += $over_time_total;
									$user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['quantity'] += $over_time_quantity;
									$user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['bad_quantity'] += $over_time_bad_quantity;
								} else {
									Debug::text('     Initiating Compact Sub-Array: Policy ID: '.$trigger_time_arr[$i]['over_time_policy_id'] .' Branch: '. (int)$udt_obj->getBranch() .' Department: '. (int)$udt_obj->getDepartment() , __FILE__, __LINE__, __METHOD__, 10);
									$user_data_total_compact_arr[30][$trigger_time_arr[$i]['over_time_policy_id']][(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()] = array( 'total_time' => $over_time_total, 'quantity' => $over_time_quantity, 'bad_quantity' => $over_time_bad_quantity );
								}

								$total_daily_hours_used += $over_time_total;
							} else {
								Debug::text('     Over Time Total is 0: '. $over_time_total, __FILE__, __LINE__, __METHOD__, 10);
							}

							unset($over_time_total, $over_time_quantity_percent, $over_time_quantity, $over_time_bad_quantity);
						} else {
							break;
						}

						$i++;

					}
					unset($udt_total_time);
				}
				unset($tmp_policy_total_time, $trigger_time_data, $trigger_time_arr);
			}
		}

		$profiler->stopTimer( 'UserDateTotal::calcOverTimePolicyTotalTime() - Part 1');

		if ( isset($user_data_total_compact_arr) ) {
			return $user_data_total_compact_arr;
		}

		return FALSE;
	}


	//Take all punches for a given day, take into account the minimum time between shifts,
	//and return an array of shifts, with their start/end and total time calculated.
	function getShiftDataByUserDateID( $user_date_id = NULL ) {
		if ( $user_date_id == '' ) {
			$user_date_id = $this->getUserDateObject()->getId();
		}

		$new_shift_trigger_time = 3600*4; //Default to 8hrs
		if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
				AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
			$new_shift_trigger_time = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getNewDayTriggerTime();
		}

		$plf = TTnew( 'PunchListFactory' );
		$plf->getByUserDateId( $user_date_id );
		if ( $plf->getRecordCount() > 0 ) {
			$shift = 0;
			$i=0;
			foreach( $plf as $p_obj ) {
				$total_time = $p_obj->getPunchControlObject()->getTotalTime();

				if ( $total_time == 0 ) {
					continue;
				}

				if ( $i > 0 AND isset($shift_data[$shift]['last_out'])
						AND $p_obj->getStatus() == 10) {
					Debug::text('Checking for new shift...', __FILE__, __LINE__, __METHOD__, 10);
					if ( ($p_obj->getTimeStamp() - $shift_data[$shift]['last_out']) > $new_shift_trigger_time ) {
						$shift++;
					}
				}

				if ( !isset($shift_data[$shift]['total_time']) ) {
					$shift_data[$shift]['total_time'] = 0;
				}

				$shift_data[$shift]['punches'][] = $p_obj->getTimeStamp();
				if ( !isset($shift_data[$shift]['first_in']) AND $p_obj->getStatus() == 10 ) {
					$shift_data[$shift]['first_in'] = $p_obj->getTimeStamp();
				} elseif ( $p_obj->getStatus() == 20 ) {
					$shift_data[$shift]['last_out'] = $p_obj->getTimeStamp();
					$shift_data[$shift]['total_time'] += $total_time;
				}

				$i++;
			}

			if ( isset($shift_data)) {
				return $shift_data;
			}
		}

		return FALSE;
	}

	function calcPremiumPolicyTotalTime( $udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr, $daily_total_time = FALSE, $schedule_policy_ids = FALSE ) {
		global $profiler;

		$profiler->startTimer( 'UserDateTotal::calcPremiumPolicyTotalTime() - Part 1');

		if ( $daily_total_time === FALSE ) {
			$daily_total_time = $this->getDailyTotalTime();
		}

		$pplf = TTnew( 'PremiumPolicyListFactory' );
		//$pplf->getByPolicyGroupUserId( $this->getUserDateObject()->getUser() );
		$pplf->getByPolicyGroupUserIdOrSchedulePolicyId( $this->getUserDateObject()->getUser(), $schedule_policy_ids );
		if ( $pplf->getRecordCount() > 0 ) {
			Debug::text('Found Premium Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);

			foreach( $pplf as $pp_obj ) {
				Debug::text('Found Premium Policy: Name: '. $pp_obj->getName() .'('. $pp_obj->getId() .') Type: '. $pp_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

				//FIXME: Support manually setting a premium policy through the Edit Hours page?
				//In those cases, just skip auto-calculating it and accept it?
				switch( $pp_obj->getType() ) {
					case 10: //Date/Time
					case 100: //Advanced
					case 90: //Holiday (coverts to Date/Time policy automatically)
						if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
										AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
							$maximum_shift_time = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime();
						}
						if ( !isset($maximum_shift_time) OR $maximum_shift_time < 86400 ) {
							$maximum_shift_time = 86400;
						}

						if ( $pp_obj->getType() == 90 )	{
							Debug::text(' Holiday Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
							//Determine if the employee is eligible for holiday premium.
							$hlf = TTnew( 'HolidayListFactory' );
							$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), ($this->getUserDateObject()->getDateStamp()-$maximum_shift_time),($this->getUserDateObject()->getDateStamp()+$maximum_shift_time) );
							if ( $hlf->getRecordCount() > 0 ) {
								$holiday_obj = $hlf->getCurrent();
								Debug::text(' Found Holiday: '. $holiday_obj->getName() .' Date: '. TTDate::getDate('DATE', $holiday_obj->getDateStamp() ) .' Current Date: '.  TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) , __FILE__, __LINE__, __METHOD__,10);

								if ( $holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
										OR $holiday_obj->isEligible( $this->getUserDateObject()->getUser() ) ) {
									Debug::text(' User is Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__,10);

									//Modify the premium policy in memory to make it like a date/time policy
									$pp_obj->setStartDate( $holiday_obj->getDateStamp() );
									$pp_obj->setEndDate( $holiday_obj->getDateStamp() );
									$pp_obj->setStartTime( TTDate::getBeginDayEpoch( $holiday_obj->getDateStamp() ) );
									$pp_obj->setEndTime( TTDate::getEndDayEpoch( $holiday_obj->getDateStamp() ) );
									$pp_obj->setSun( TRUE );
									$pp_obj->setMon( TRUE );
									$pp_obj->setTue( TRUE );
									$pp_obj->setWed( TRUE );
									$pp_obj->setThu( TRUE );
									$pp_obj->setFri( TRUE );
									$pp_obj->setSat( TRUE );
									$pp_obj->setDailyTriggerTime( 0 );
									$pp_obj->setWeeklyTriggerTime( 0 );
								}
							} else {
								//If a Date/Time premium was created first, with all days activated, then switched to a holiday type,
								//its still calculated on all days, even when its not a holiday.
								$pp_obj->setSun( FALSE );
								$pp_obj->setMon( FALSE );
								$pp_obj->setTue( FALSE );
								$pp_obj->setWed( FALSE );
								$pp_obj->setThu( FALSE );
								$pp_obj->setFri( FALSE );
								$pp_obj->setSat( FALSE );
							}
							unset($hlf, $holiday_obj);
						} else {
							Debug::text(' Date/Time Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
						}

						//Make sure this is a valid day
						//Take into account shifts that span midnight though, where one half of the shift is eligilble for premium time.
						//ie: Premium Policy starts 7AM to 7PM on Sat/Sun. Punches in at 9PM Friday and out at 9AM Sat, we need to check if both days are valid.
						if ( $pp_obj->isActive( $this->getUserDateObject()->getDateStamp()-$maximum_shift_time, $this->getUserDateObject()->getDateStamp()+$maximum_shift_time, $this->getUserDateObject()->getUser() ) ) {
							Debug::text(' Premium Policy Is Active On OR Around This Day.', __FILE__, __LINE__, __METHOD__, 10);

							$total_daily_time_used = 0;
							$daily_trigger_time = 0;
							$maximum_daily_trigger_time = FALSE;

							$udtlf = TTnew( 'UserDateTotalListFactory' );

							if ( $pp_obj->isHourRestricted() == TRUE ) {
								if ( $pp_obj->getWeeklyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) {
									//Get Pay Period Schedule info
									if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
											AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
										$start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
									} else {
										$start_week_day_id = 0;
									}
									Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

									//$weekly_total_time = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
									$weekly_total_time = $udtlf->getWeekWorkedTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
									if ( $weekly_total_time > $pp_obj->getWeeklyTriggerTime() ) {
										$daily_trigger_time = 0;
									} else {
										$daily_trigger_time = $pp_obj->getWeeklyTriggerTime() - $weekly_total_time;
									}
									Debug::text(' Weekly Trigger Time: '. $daily_trigger_time .' Raw Weekly Time: '. $weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);
								}

								if ( $pp_obj->getDailyTriggerTime() > 0 AND $pp_obj->getDailyTriggerTime() > $daily_trigger_time) {
									$daily_trigger_time = $pp_obj->getDailyTriggerTime();
								}

								if ( $pp_obj->getMaximumDailyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0  ) {
									//$maximum_daily_trigger_time = ( $pp_obj->getMaximumDailyTriggerTime() > 0 ) ? ($pp_obj->getMaximumDailyTriggerTime()-$pp_obj->getDailyTriggerTime()) : FALSE;
									$maximum_daily_trigger_time = ( $pp_obj->getMaximumDailyTriggerTime() > 0 ) ? ($pp_obj->getMaximumDailyTriggerTime()) : FALSE;
									$maximum_weekly_trigger_time = ( isset($weekly_total_time) AND $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) ? ($pp_obj->getMaximumWeeklyTriggerTime()-$weekly_total_time) : FALSE;

									Debug::text(' Maximum Daily: '. $maximum_daily_trigger_time .' Weekly: '. $maximum_weekly_trigger_time .' Daily Total Time Used: '. $total_daily_time_used .' Daily Trigger Time: '. $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
									if ( $maximum_daily_trigger_time > 0 AND ( $maximum_weekly_trigger_time === FALSE OR $maximum_daily_trigger_time < $maximum_weekly_trigger_time ) ) {
										$pp_obj->setMaximumTime( $maximum_daily_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
										Debug::text(' Set Daily Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
									} else {
										if ( $maximum_weekly_trigger_time !== FALSE AND ( $maximum_weekly_trigger_time <= 0 OR ( $maximum_weekly_trigger_time < $daily_trigger_time ) ) ) {
											Debug::text(' Exceeded Weekly Maximum Time to: '. $pp_obj->getMaximumTime() .' Skipping...', __FILE__, __LINE__, __METHOD__, 10);
											continue;
										}
										$pp_obj->setMaximumTime( $maximum_weekly_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
										Debug::text(' Set Weekly Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
										$maximum_daily_trigger_time = $maximum_weekly_trigger_time;
									}
									unset($maximum_weekly_trigger_time);
								}
							}
							Debug::text(' Daily Trigger Time: '. $daily_trigger_time .' Max: '. $maximum_daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

							//Loop through all worked (status: 20) UserDateTotalRows
							$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
							$i = 1;
							if ( $udtlf->getRecordCount() > 0 ) {
								Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

								$valid_user_date_total_ids = array();
								foreach( $udtlf as $udt_obj ) {
									Debug::text('UserDateTotal ID: '. $udt_obj->getID() .' Total Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

									//Ignore incomplete punches
									if ( $udt_obj->getTotalTime() == 0 ) {
										continue;
									}

									//How do we handle actual shifts for premium time?
									//So if premium policy starts at 1PM for shifts, to not
									//include employees who return from lunch at 1:30PM.
									//Create a function that takes all punches for a day, and returns
									//the first in and last out time for a given shift when taking
									//into account minimum time between shifts, as well as the total time for that shift.
									//We can then use that time for ActiveTime on premium policies, and determine if a
									//punch falls within the active time, then we add it to the total.
									if ( ($pp_obj->getIncludePartialPunch() == TRUE OR $pp_obj->isTimeRestricted() == TRUE ) AND $udt_obj->getPunchControlID() != FALSE ) {
										Debug::text('Time Restricted Premium Policy, lookup punches to get times.', __FILE__, __LINE__, __METHOD__, 10);

										if ( $pp_obj->getIncludePartialPunch() == FALSE ) {
											$shift_data = $this->getShiftDataByUserDateID( $this->getUserDateID() );
										}

										$plf = TTnew( 'PunchListFactory' );
										$plf->getByPunchControlId( $udt_obj->getPunchControlID() );
										if ( $plf->getRecordCount() > 0 ) {
											Debug::text('Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
											foreach( $plf as $punch_obj ) {
												if ( $pp_obj->getIncludePartialPunch() == TRUE ) {
													//Debug::text('Including Partial Punches...', __FILE__, __LINE__, __METHOD__, 10);

													if ( $punch_obj->getStatus() == 10 ) {
														$punch_times['in'] = $punch_obj->getTimeStamp();
													} elseif ( $punch_obj->getStatus() == 20 ) {
														$punch_times['out'] = $punch_obj->getTimeStamp();
													}
												} else {
													if ( isset($shift_data) AND is_array($shift_data) ) {
														foreach( $shift_data as $shift ) {
															if ( $punch_obj->getTimeStamp() >= $shift['first_in']
																	AND $punch_obj->getTimeStamp() <= $shift['last_out'] ) {
																//Debug::Arr($shift,'Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
																Debug::text('Punch ('. TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp() ).') inside shift time...', __FILE__, __LINE__, __METHOD__, 10);
																$punch_times['in'] = $shift['first_in'];
																$punch_times['out'] = $shift['last_out'];
																break;
															} else {
																Debug::text('Punch ('. TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp() ).') outside shift time...', __FILE__, __LINE__, __METHOD__, 10);
															}
														}
													}
												}
											}

											if ( isset($punch_times) AND count($punch_times) == 2
													AND ( $pp_obj->isActiveDate( $punch_times['in'] ) == TRUE OR $pp_obj->isActiveDate( $punch_times['out'] ) )
													AND ( $pp_obj->isActive( $punch_times['in'], $punch_times['out'], $this->getUserDateObject()->getUser() ) )
													AND $pp_obj->isActiveTime( $punch_times['in'], $punch_times['out'], $this->getUserDateObject()->getUser() ) == TRUE ) {
												//Debug::Arr($punch_times, 'Punch Times: ', __FILE__, __LINE__, __METHOD__, 10);
												$punch_total_time = $pp_obj->getPartialPunchTotalTime( $punch_times['in'], $punch_times['out'], $udt_obj->getTotalTime(), $this->getUserDateObject()->getUser() );
												$valid_user_date_total_ids[] = $udt_obj->getID(); //Need to record punches that fall within the active time so we can properly handle break/meal adjustments.
												Debug::text('Valid Punch pair in active time, Partial Punch Total Time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
											} else {
												Debug::text('InValid Punch Pair or outside Active Time...', __FILE__, __LINE__, __METHOD__, 10);
												$punch_total_time = 0;
											}
										}
									} elseif ( $pp_obj->isActive( $udt_obj->getUserDateObject()->getDateStamp(), NULL, $this->getUserDateObject()->getUser() ) == TRUE )  {
										$punch_total_time = $udt_obj->getTotalTime();
										$valid_user_date_total_ids[] = $udt_obj->getID();
									} else {
										$punch_total_time = 0;
									}

									//Why is $tmp_punch_total_time not just $punch_total_time? Are the partial punches somehow separate from the meal/break calculation?
									//Yes, because tmp_punch_total_time is the DAILY total time used, whereas punch_total_time can be a partial shift. Without this the daily trigger time won't work.
									$tmp_punch_total_time = $udt_obj->getTotalTime();
									Debug::text('aPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									//When calculating meal/break policy adjustments, make sure they can be added to one another, in case there is a meal AND break
									//within the same shift, they both need to be included. Also make sure we double check the active date again.

									//Apply meal policy adjustment as early as possible.
									if ( $pp_obj->getIncludeMealPolicy() == TRUE
											AND $pp_obj->isActiveDate( $this->getUserDateObject()->getDateStamp() ) == TRUE
											AND $pp_obj->isActiveDayofWeek( $this->getUserDateObject()->getDateStamp() ) == TRUE
											AND isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] )
											AND in_array( $udt_obj->getID(), $valid_user_date_total_ids) ) {
										Debug::text(' Meal Policy Adjustment Found: '. $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
										$punch_total_time = bcadd( $punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
										$tmp_punch_total_time = bcadd( $tmp_punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
									}
									Debug::text('bPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									//Apply break policy adjustment as early as possible.
									if ( $pp_obj->getIncludeBreakPolicy() == TRUE
										AND $pp_obj->isActiveDate( $this->getUserDateObject()->getDateStamp() ) == TRUE
										AND $pp_obj->isActiveDayofWeek( $this->getUserDateObject()->getDateStamp() ) == TRUE
										AND isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] )
										AND in_array( $udt_obj->getID(), $valid_user_date_total_ids) ) {
										Debug::text(' Break Policy Adjustment Found: '. $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
										$punch_total_time = bcadd( $punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
										$tmp_punch_total_time = bcadd( $tmp_punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
									}
									Debug::text('cPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									$total_daily_time_used += $tmp_punch_total_time;
									Debug::text('Daily Total Time Used: '. $total_daily_time_used .' Maximum Trigger Time: '. $maximum_daily_trigger_time .' This Record: '. ($total_daily_time_used-$tmp_punch_total_time), __FILE__, __LINE__, __METHOD__, 10);

									//FIXME: Should the daily/weekly trigger time be >= instead of >.
									//That way if the policy is active after 7.5hrs, punch time of exactly 7.5hrs will still
									//activate the policy, rather then requiring 7.501hrs+
									if ( $punch_total_time > 0 AND $total_daily_time_used > $daily_trigger_time
											AND ( $maximum_daily_trigger_time === FALSE OR ( $maximum_daily_trigger_time !== FALSE AND ($total_daily_time_used-$tmp_punch_total_time) < $maximum_daily_trigger_time ) )
											) {
										Debug::text('Past Trigger Time!! '. ($total_daily_time_used-$tmp_punch_total_time), __FILE__, __LINE__, __METHOD__, 10);

										//Calculate how far past trigger time we are.
										$past_trigger_time = $total_daily_time_used - $daily_trigger_time;
										if ( $punch_total_time > $past_trigger_time ) {
											$punch_total_time = $past_trigger_time;
											Debug::text('Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										} else {
											Debug::text('NOT Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										}

										//If we are close to exceeding the maximum daily/weekly time, just use the remaining time.
										if ( $maximum_daily_trigger_time > 0 AND $total_daily_time_used > $maximum_daily_trigger_time ) {
											Debug::text('Using New Maximum Trigger Time as punch total time: '. $maximum_daily_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
											$punch_total_time = $punch_total_time - ($total_daily_time_used - $maximum_daily_trigger_time);
										} else {
											Debug::text('NOT Using New Maximum Trigger Time as punch total time: '. $maximum_daily_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
										}

										$total_time = $punch_total_time;
										if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
											$premium_policy_daily_total_time = (int)$udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $this->getUserDateID(), $pp_obj->getId() );
											Debug::text(' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

											if ( $pp_obj->getMinimumTime() > 0 ) {
												//FIXME: Split the minimum time up between all the punches somehow.
												//Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
												//and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
												//for the day. If its applied to the last it will be just 1hr.
												//Min & Max time is based on the shift time, rather then per punch pair time.
												//FIXME: If there is a minimum time set to say 9hrs, and the punches go like this:
												// In: 7:00AM Out: 3:00:PM, Out: 3:30PM (missing 2nd In Punch), the minimum time won't be calculated due to the invalid punch pair.
												if ( $i == $udtlf->getRecordCount() AND bcadd( $premium_policy_daily_total_time, $total_time ) < $pp_obj->getMinimumTime() ) {
													$total_time = bcsub( $pp_obj->getMinimumTime(), $premium_policy_daily_total_time );
												}
											}

											Debug::text(' Total Time After Minimum is applied: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
											if ( $pp_obj->getMaximumTime() > 0 ) {
												//Min & Max time is based on the shift time, rather then per punch pair time.
												if ( bcadd( $premium_policy_daily_total_time, $total_time ) > $pp_obj->getMaximumTime() ) {
													Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
													$total_time = bcsub( $total_time, bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() ) );
												}
											}
										}

										Debug::text(' Premium Punch Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
										if ( $total_time > 0 ) {
											Debug::text(' Applying  Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

											if ( $pp_obj->getType() == 100 ) {
												//Check Shift Differential criteria *AFTER* calculatating daily/weekly time, as the shift differential
												//applies to the resulting time calculation, not the daily/weekly time calculation. Daily/Weekly should always include all time.
												//This is fundamentally different than the Shift Differential premium policy type.
												if ( ( $pp_obj->getBranchSelectionType() == 10
															AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																	OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																			AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) ) )

														OR ( $pp_obj->getBranchSelectionType() == 20
																AND in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
																AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																		OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																				AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

														OR ( $pp_obj->getBranchSelectionType() == 30
																AND !in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
																AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																		OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																				AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

														) {
													Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserDateObject()->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

													if ( ( $pp_obj->getDepartmentSelectionType() == 10
																AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																		OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																				AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) ) )

															OR ( $pp_obj->getDepartmentSelectionType() == 20
																	AND in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
																	AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																			OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																					AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

															OR ( $pp_obj->getDepartmentSelectionType() == 30
																	AND !in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
																	AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																			OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																					AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

															) {
														Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserDateObject()->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);

														if ( $pp_obj->getJobGroupSelectionType() == 10
																OR ( $pp_obj->getJobGroupSelectionType() == 20
																		AND is_object( $udt_obj->getJobObject() )
																		AND in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) )
																OR ( $pp_obj->getJobGroupSelectionType() == 30
																		AND is_object( $udt_obj->getJobObject() )
																		AND !in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) )
																) {
															Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

															if ( $pp_obj->getJobSelectionType() == 10
																	OR ( $pp_obj->getJobSelectionType() == 20
																			AND in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
																	OR ( $pp_obj->getJobSelectionType() == 30
																			AND !in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
																	) {
																Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

																if ( $pp_obj->getJobItemGroupSelectionType() == 10
																		OR ( $pp_obj->getJobItemGroupSelectionType() == 20
																				AND is_object( $udt_obj->getJobItemObject() )
																				AND in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) )
																		OR ( $pp_obj->getJobItemGroupSelectionType() == 30
																				AND is_object( $udt_obj->getJobItemObject() )
																				AND !in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) )
																		) {
																	Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

																	if ( $pp_obj->getJobItemSelectionType() == 10
																			OR ( $pp_obj->getJobItemSelectionType() == 20
																					AND in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
																			OR ( $pp_obj->getJobItemSelectionType() == 30
																					AND !in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
																			) {
																		Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);


																		$udtf = TTnew( 'UserDateTotalFactory' );
																		$udtf->setUserDateID( $this->getUserDateID() );
																		$udtf->setStatus( 10 ); //System
																		$udtf->setType( 40 ); //Premium
																		$udtf->setPremiumPolicyId( $pp_obj->getId() );
																		$udtf->setBranch( $udt_obj->getBranch() );
																		$udtf->setDepartment( $udt_obj->getDepartment() );
																		$udtf->setJob( $udt_obj->getJob() );
																		$udtf->setJobItem( $udt_obj->getJobItem() );

																		$udtf->setQuantity( $udt_obj->getQuantity() );
																		$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

																		$udtf->setTotalTime( $total_time );
																		$udtf->setEnableCalcSystemTotalTime(FALSE);
																		if ( $udtf->isValid() == TRUE ) {
																			$udtf->Save();
																		}
																		unset($udtf);

																	} else {
																		Debug::text(' Shift Differential... DOES NOT Meet Task Criteria!', __FILE__, __LINE__, __METHOD__, 10);
																	}
																} else {
																	Debug::text(' Shift Differential... DOES NOT Meet Task Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
																}
															} else {
																Debug::text(' Shift Differential... DOES NOT Meet Job Criteria!', __FILE__, __LINE__, __METHOD__, 10);
															}
														} else {
															Debug::text(' Shift Differential... DOES NOT Meet Job Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
														}
													} else {
														Debug::text(' Shift Differential... DOES NOT Meet Department Criteria!', __FILE__, __LINE__, __METHOD__, 10);
													}
												} else {
													Debug::text(' Shift Differential... DOES NOT Meet Branch Criteria!', __FILE__, __LINE__, __METHOD__, 10);
												}
											} else {
												$udtf = TTnew( 'UserDateTotalFactory' );
												$udtf->setUserDateID( $this->getUserDateID() );
												$udtf->setStatus( 10 ); //System
												$udtf->setType( 40 ); //Premium
												$udtf->setPremiumPolicyId( $pp_obj->getId() );
												$udtf->setBranch( $udt_obj->getBranch() );
												$udtf->setDepartment( $udt_obj->getDepartment() );
												$udtf->setJob( $udt_obj->getJob() );
												$udtf->setJobItem( $udt_obj->getJobItem() );

												$udtf->setQuantity( $udt_obj->getQuantity() );
												$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

												$udtf->setTotalTime( $total_time );
												$udtf->setEnableCalcSystemTotalTime(FALSE);
												if ( $udtf->isValid() == TRUE ) {
													$udtf->Save();
												}
												unset($udtf);
											}
										} else {
											Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										Debug::text('Not Past Trigger Time Yet or Punch Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
									}

									$i++;
								}
								unset($valid_user_date_total_ids);
							}
						}
						unset($udtlf, $udt_obj);
						break;
					case 20: //Differential
						Debug::text(' Differential Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

						//Loop through all worked (status: 20) UserDateTotalRows
						$udtlf = TTnew( 'UserDateTotalListFactory' );
						$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
						$i = 1;
						if ( $udtlf->getRecordCount() > 0 ) {
							Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

							foreach( $udtlf as $udt_obj ) {
								//Ignore incomplete punches
								if ( $udt_obj->getTotalTime() == 0 ) {
									continue;
								}

								if ( ( $pp_obj->getBranchSelectionType() == 10
											AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
													OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
															AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) ) )

										OR ( $pp_obj->getBranchSelectionType() == 20
												AND in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
												AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
														OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

										OR ( $pp_obj->getBranchSelectionType() == 30
												AND !in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
												AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
														OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

										) {
									Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserDateObject()->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

									if ( ( $pp_obj->getDepartmentSelectionType() == 10
												AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
														OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) ) )

											OR ( $pp_obj->getDepartmentSelectionType() == 20
													AND in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
													AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
															OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																	AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

											OR ( $pp_obj->getDepartmentSelectionType() == 30
													AND !in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
													AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
															OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																	AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

											) {
										Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserDateObject()->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);


										if ( ( $pp_obj->getJobGroupSelectionType() == 0 OR $pp_obj->getJobGroupSelectionType() == 10 )
												OR ( $pp_obj->getJobGroupSelectionType() == 20
														AND ( is_object( $udt_obj->getJobObject() ) AND in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) ) )
												OR ( $pp_obj->getJobGroupSelectionType() == 30
														AND ( is_object( $udt_obj->getJobObject() ) AND !in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) ) )
												) {
											Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

											if ( ( $pp_obj->getJobSelectionType() == 0 OR $pp_obj->getJobSelectionType() == 10 )
													OR ( $pp_obj->getJobSelectionType() == 20
															AND in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
													OR ( $pp_obj->getJobSelectionType() == 30
															AND !in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
													) {
												Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

												if ( ( $pp_obj->getJobItemGroupSelectionType() == 0 OR $pp_obj->getJobItemGroupSelectionType() == 10 )
														OR ( $pp_obj->getJobItemGroupSelectionType() == 20
																AND ( is_object( $udt_obj->getJobItemObject() ) AND in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) ) )
														OR ( $pp_obj->getJobItemGroupSelectionType() == 30
																AND ( is_object( $udt_obj->getJobItemObject() ) AND !in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) ) )
														) {
													Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

													if ( ( $pp_obj->getJobItemSelectionType() == 0 OR $pp_obj->getJobItemSelectionType() == 10 )
															OR ( $pp_obj->getJobItemSelectionType() == 20
																	AND in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
															OR ( $pp_obj->getJobItemSelectionType() == 30
																	AND !in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
															) {
														Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

														$premium_policy_daily_total_time = 0;
														$punch_total_time = $udt_obj->getTotalTime();
														$total_time = 0;

														//Apply meal policy adjustment BEFORE min/max times
														if ( $pp_obj->getIncludeMealPolicy() == TRUE AND isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] ) ) {
															Debug::text(' Meal Policy Adjustment Found: '. $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
															$punch_total_time = bcadd( $punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
														}
														if ( $pp_obj->getIncludeBreakPolicy() == TRUE AND isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] ) ) {
															Debug::text(' Break Policy Adjustment Found: '. $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
															$punch_total_time = bcadd( $punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
														}

														$total_time = $punch_total_time;
														if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
															$premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $this->getUserDateID(), $pp_obj->getId() );
															Debug::text(' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

															if ( $pp_obj->getMinimumTime() > 0 ) {
																//FIXME: Split the minimum time up between all the punches somehow.
																if ( $i == $udtlf->getRecordCount() AND bcadd( $premium_policy_daily_total_time, $total_time ) < $pp_obj->getMinimumTime() ) {
																	$total_time = bcsub( $pp_obj->getMinimumTime(), $premium_policy_daily_total_time );
																}
															} else {
																$total_time = $punch_total_time;
															}

															Debug::text(' Total Time After Minimum is applied: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
															if ( $pp_obj->getMaximumTime() > 0 ) {
																//Min & Max time is based on the shift time, rather then per punch pair time.
																if ( bcadd( $premium_policy_daily_total_time, $total_time ) > $pp_obj->getMaximumTime() ) {
																	$total_time = bcsub( $total_time, bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() ) );
																	Debug::text(' bMore than Maximum Time... new Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
																}
															}
														} else {
															$total_time = $punch_total_time;
														}

														Debug::text(' Premium Punch Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
														if ( $total_time > 0 ) {
															Debug::text(' Applying  Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

															$udtf = TTnew( 'UserDateTotalFactory' );
															$udtf->setUserDateID( $this->getUserDateID() );
															$udtf->setStatus( 10 ); //System
															$udtf->setType( 40 ); //Premium
															$udtf->setPremiumPolicyId( $pp_obj->getId() );
															$udtf->setBranch( $udt_obj->getBranch() );
															$udtf->setDepartment( $udt_obj->getDepartment() );
															$udtf->setJob( $udt_obj->getJob() );
															$udtf->setJobItem( $udt_obj->getJobItem() );

															$udtf->setQuantity( $udt_obj->getQuantity() );
															$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

															$udtf->setTotalTime( $total_time );
															$udtf->setEnableCalcSystemTotalTime(FALSE);
															if ( $udtf->isValid() == TRUE ) {
																$udtf->Save();
															}
															unset($udtf);
														} else {
															Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
														}
													} else {
														Debug::text(' Shift Differential... DOES NOT Meet Task Criteria!', __FILE__, __LINE__, __METHOD__, 10);
													}
												} else {
													Debug::text(' Shift Differential... DOES NOT Meet Task Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
												}
											} else {
												Debug::text(' Shift Differential... DOES NOT Meet Job Criteria!', __FILE__, __LINE__, __METHOD__, 10);
											}
										} else {
											Debug::text(' Shift Differential... DOES NOT Meet Job Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										Debug::text(' Shift Differential... DOES NOT Meet Department Criteria!', __FILE__, __LINE__, __METHOD__, 10);
									}
								} else {
									Debug::text(' Shift Differential... DOES NOT Meet Branch Criteria!', __FILE__, __LINE__, __METHOD__, 10);
								}

								$i++;
							}

						}
						unset($udtlf, $udt_obj);
						break;
					case 30: //Meal/Break
						Debug::text(' Meal/Break Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

						if ( $pp_obj->getDailyTriggerTime() == 0
								OR ( $pp_obj->getDailyTriggerTime() > 0 AND $daily_total_time >= $pp_obj->getDailyTriggerTime() ) ) {
							//Find maximum worked without a break.
							$plf = TTnew( 'PunchListFactory' );
							$plf->getByUserDateId( $this->getUserDateID() ); //Get all punches for the day.
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text('Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
								foreach( $plf as $p_obj ) {
									Debug::text('TimeStamp: '. $p_obj->getTimeStamp() .' Status: '. $p_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
									$punch_pairs[$p_obj->getPunchControlID()][] = array(
																						'status_id' => $p_obj->getStatus(),
																						'punch_control_id' => $p_obj->getPunchControlID(),
																						'time_stamp' => $p_obj->getTimeStamp()
																					);
								}

								if ( isset($punch_pairs) ) {
									$prev_punch_timestamp = NULL;
									$maximum_time_worked_without_break = 0;

									foreach( $punch_pairs as $punch_pair ) {
										if ( count($punch_pair) > 1 ) {
											//Total Punch Time
											$total_punch_pair_time = $punch_pair[1]['time_stamp'] - $punch_pair[0]['time_stamp'];
											$maximum_time_worked_without_break += $total_punch_pair_time;
											Debug::text('Total Punch Pair Time: '. $total_punch_pair_time .' Maximum No Break Time: '. $maximum_time_worked_without_break, __FILE__, __LINE__, __METHOD__, 10);

											if ( $prev_punch_timestamp !== NULL ) {
												$break_time = $punch_pair[0]['time_stamp'] - $prev_punch_timestamp;
												if ( $break_time > $pp_obj->getMinimumBreakTime() ) {
													Debug::text('Exceeded Minimum Break Time: '. $break_time .' Minimum: '. $pp_obj->getMinimumBreakTime(), __FILE__, __LINE__, __METHOD__, 10);
													$maximum_time_worked_without_break = 0;
												}
											}

											if ( $maximum_time_worked_without_break > $pp_obj->getMaximumNoBreakTime() ) {
												Debug::text('Exceeded maximum no break time!', __FILE__, __LINE__, __METHOD__, 10);

												if ( $pp_obj->getMaximumTime() > $pp_obj->getMinimumTime() ) {
													$total_time = $pp_obj->getMaximumTime();
												} else {
													$total_time = $pp_obj->getMinimumTime();
												}

												if ( $total_time > 0 ) {
													Debug::text(' Applying Meal/Break Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

													//Get Punch Control obj.
													$pclf = TTnew( 'PunchControlListFactory' );
													$pclf->getById( $punch_pair[0]['punch_control_id'] );
													if ( $pclf->getRecordCount() > 0 ) {
														$pc_obj = $pclf->getCurrent();
													}

													$udtf = TTnew( 'UserDateTotalFactory' );
													$udtf->setUserDateID( $this->getUserDateID() );
													$udtf->setStatus( 10 ); //System
													$udtf->setType( 40 ); //Premium
													$udtf->setPremiumPolicyId( $pp_obj->getId() );

													if ( isset($pc_obj) AND is_object( $pc_obj ) ) {
														$udtf->setBranch( $pc_obj->getBranch() );
														$udtf->setDepartment( $pc_obj->getDepartment() );
														$udtf->setJob( $pc_obj->getJob() );
														$udtf->setJobItem( $pc_obj->getJobItem() );
													}

													$udtf->setTotalTime( $total_time );
													$udtf->setEnableCalcSystemTotalTime(FALSE);
													if ( $udtf->isValid() == TRUE ) {
														$udtf->Save();
													}
													unset($udtf);

													break; //Stop looping through punches.
												}
											} else {
												Debug::text('Did not exceed maximum no break time yet...', __FILE__, __LINE__, __METHOD__, 10);
											}

											$prev_punch_timestamp = $punch_pair[1]['time_stamp'];
										} else {
											Debug::text('Found UnPaired Punch, Ignorning...', __FILE__, __LINE__, __METHOD__, 10);
										}
									}
									unset($plf, $punch_pairs, $punch_pair, $prev_punch_timestamp, $maximum_time_worked_without_break, $total_time);
								}
							}
						} else {
							Debug::text(' Not within Daily Total Time: '. $daily_total_time .' Trigger Time: '. $pp_obj->getDailyTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
						}
						break;
					case 40: //Callback
						Debug::text(' Callback Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
						Debug::text(' Minimum Time Between Shifts: '. $pp_obj->getMinimumTimeBetweenShift() .' Minimum First Shift Time: '. $pp_obj->getMinimumFirstShiftTime(), __FILE__, __LINE__, __METHOD__, 10);

						$first_punch_epoch = FALSE;

						$plf = TTnew( 'PunchListFactory' );
						$plf->getByUserDateId( $this->getUserDateID() ); //Get all punches for the day.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text('Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
							$i=0;
							foreach( $plf as $p_obj ) {
								Debug::text('TimeStamp: '. $p_obj->getTimeStamp() .' Status: '. $p_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
								if (  $i == 0 ) {
									$first_punch_epoch = $p_obj->getTimeStamp();
								}
								$punch_pairs[$p_obj->getPunchControlID()][] = array(
																					'status_id' => $p_obj->getStatus(),
																					'punch_control_id' => $p_obj->getPunchControlID(),
																					'time_stamp' => $p_obj->getTimeStamp()
																				);
								$i++;
							}
						}
						//Debug::Arr($punch_pairs, ' Punch Pairs...', __FILE__, __LINE__, __METHOD__, 10);

						$shift_data = FALSE;
						if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
								AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
							//This should return all shifts within the minimum time between shifts setting.
							//We need to get all shifts within
							$shift_data = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getShiftData( NULL, $this->getUserDateObject()->getUser(), $first_punch_epoch, NULL, NULL, ( $pp_obj->getMinimumTimeBetweenShift()+$pp_obj->getMinimumFirstShiftTime() ) );
						} else {
							Debug::text(' No Pay Period...', __FILE__, __LINE__, __METHOD__, 10);
						}
						//Debug::Arr($shift_data, ' Shift Data...', __FILE__, __LINE__, __METHOD__, 10);

						//Only calculate if their are at least two shifts
						if ( count($shift_data) >= 2 ) {
							Debug::text(' Found at least two shifts...', __FILE__, __LINE__, __METHOD__, 10);

							//Loop through shifts backwards.
							krsort( $shift_data );

							$prev_key = FALSE;
							foreach( $shift_data as $key => $data ) {
								//Debug::Arr($data, ' Shift Data for Shift: '. $key, __FILE__, __LINE__, __METHOD__, 10);

								//Check if previous shift is greater than minimum first shift time.
								$prev_key = $key - 1;

								if ( isset($shift_data[$prev_key]) AND isset($shift_data[$prev_key]['total_time']) AND $shift_data[$prev_key]['total_time'] >= $pp_obj->getMinimumFirstShiftTime() ) {
									Debug::text(' Previous shift exceeds minimum first shift time... Shift Total Time: '. $shift_data[$prev_key]['total_time'] , __FILE__, __LINE__, __METHOD__, 10);

									//Get last out time of the previous shift.
									if ( isset($shift_data[$prev_key]['last_out']) ) {
										$previous_shift_last_out_epoch = $shift_data[$prev_key]['last_out']['time_stamp'];
										$current_shift_cutoff = $previous_shift_last_out_epoch + $pp_obj->getMinimumTimeBetweenShift();
										Debug::text(' Previous Shift Last Out: '. TTDate::getDate('DATE+TIME', $previous_shift_last_out_epoch ) .'('.$previous_shift_last_out_epoch.') Current Shift Cutoff: '. TTDate::getDate('DATE+TIME', $current_shift_cutoff ) .'('. $previous_shift_last_out_epoch .')', __FILE__, __LINE__, __METHOD__, 10);

										//Loop through all worked (status: 20) UserDateTotalRows
										$udtlf = TTnew( 'UserDateTotalListFactory' );
										$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
										if ( $udtlf->getRecordCount() > 0 ) {
											Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

											$x=1;
											foreach( $udtlf as $udt_obj ) {
												Debug::text('X: '. $x .'/'. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

												//Ignore incomplete punches
												if ( $udt_obj->getTotalTime() == 0 ) {
													continue;
												}

												if ( $udt_obj->getPunchControlID() > 0 AND isset($punch_pairs[$udt_obj->getPunchControlID()]) ) {
													Debug::text(' Found valid Punch Control ID: '. $udt_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
													Debug::text(' First Punch: '. TTDate::getDate('DATE+TIME', $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp'] ) .' Last Punch: '. TTDate::getDate('DATE+TIME', $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10);

													$punch_total_time = 0;
													$force_minimum_time_calculation = FALSE;
													//Make sure OUT punch is before current_shift_cutoff
													if ( isset($punch_pairs[$udt_obj->getPunchControlID()][1]) AND $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'] <= $current_shift_cutoff ) {
														Debug::text(' Both punches are BEFORE the cutoff time...', __FILE__, __LINE__, __METHOD__, 10);
														$punch_total_time = bcsub( $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'], $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']);
													} elseif ( isset($punch_pairs[$udt_obj->getPunchControlID()][0]) AND $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp'] <= $current_shift_cutoff ) {
														Debug::text(' Only IN punch is BEFORE the cutoff time...', __FILE__, __LINE__, __METHOD__, 10);
														$punch_total_time = bcsub($current_shift_cutoff, $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']);
														$force_minimum_time_calculation = TRUE;
													} else {
														Debug::text(' Both punches are AFTER the cutoff time... Skipping...', __FILE__, __LINE__, __METHOD__, 10);
														//continue;
														$punch_total_time = 0;
													}
													Debug::text(' Punch Total Time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

													//Apply meal policy adjustment BEFORE min/max times
													if ( $pp_obj->getIncludeMealPolicy() == TRUE AND isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] ) ) {
														Debug::text(' Meal Policy Adjustment Found: '. $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
														$punch_total_time = bcadd( $punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
													}
													if ( $pp_obj->getIncludeBreakPolicy() == TRUE AND isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] ) ) {
														Debug::text(' Break Policy Adjustment Found: '. $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
														$punch_total_time = bcadd( $punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
													}

													$premium_policy_daily_total_time = 0;
													if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
														$premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $this->getUserDateID(), $pp_obj->getId() );
														Debug::text('X: '. $x .'/'. $udtlf->getRecordCount() .' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

														if ( $pp_obj->getMinimumTime() > 0 ) {
															//FIXME: Split the minimum time up between all the punches somehow.
															//Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
															//and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
															//for the day. If its applied to the last it will be just 1hr.
															//Min & Max time is based on the shift time, rather then per punch pair time.
															if ( ( $force_minimum_time_calculation == TRUE OR $x == $udtlf->getRecordCount() ) AND bcadd( $premium_policy_daily_total_time, $punch_total_time ) < $pp_obj->getMinimumTime() ) {
																$total_time = bcsub( $pp_obj->getMinimumTime(), $premium_policy_daily_total_time );
															} else {
																$total_time = $punch_total_time;
															}
														} else {
															$total_time = $punch_total_time;
														}

														Debug::text(' Total Time After Minimum is applied: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

														if ( $pp_obj->getMaximumTime() > 0 ) {
															//Min & Max time is based on the shift time, rather then per punch pair time.
															if ( bcadd( $premium_policy_daily_total_time, $total_time ) > $pp_obj->getMaximumTime() ) {
																Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
																$total_time = bcsub( $total_time, bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() ) );
															}
														}
													} else {
														$total_time = $punch_total_time;
													}

													Debug::text(' Total Punch Control Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
													if ( $total_time > 0 ) {
														Debug::text(' Applying  Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

														$udtf = TTnew( 'UserDateTotalFactory' );
														$udtf->setUserDateID( $this->getUserDateID() );
														$udtf->setStatus( 10 ); //System
														$udtf->setType( 40 ); //Premium
														$udtf->setPremiumPolicyId( $pp_obj->getId() );
														$udtf->setBranch( $udt_obj->getBranch() );
														$udtf->setDepartment( $udt_obj->getDepartment() );
														$udtf->setJob( $udt_obj->getJob() );
														$udtf->setJobItem( $udt_obj->getJobItem() );

														$udtf->setQuantity( $udt_obj->getQuantity() );
														$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

														$udtf->setTotalTime( $total_time );
														$udtf->setEnableCalcSystemTotalTime(FALSE);
														if ( $udtf->isValid() == TRUE ) {
															$udtf->Save();
														}
														unset($udtf);
													}
												} else {
													Debug::text(' Skipping invalid Punch Control ID: '. $udt_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
												}

												$x++;
											}
										}
									}
									unset($previous_shift_last_out_epoch, $current_shift_cutoff, $udtlf );
								} else {
									Debug::text(' Previous shift does not exist or does NOT exceed minimum first shift time... Key: '. $prev_key, __FILE__, __LINE__, __METHOD__, 10);
								}
							}
						} else {
							Debug::text(' Didnt find two shifts, or the first shift wasnt long enough... Total Shifts: '. count($shift_data), __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($udtlf, $udt_obj, $plf, $punch_pairs, $first_punch_epoch, $shift_data, $data);
						break;
					case 50: //Minimum shift time
						Debug::text(' Minimum Shift Time Premium Policy... Minimum Shift Time: '. $pp_obj->getMinimumShiftTime() .' UserDateID: '. $this->getUserDateID() .'  Daily Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

						//Get all shifts on this day.
						//Minimum Shift time should be on a per shift level, not a per day level.
						//However the Min/Max time for the policy is per day. Only Minimum Shift Time is per shift.
						if ( $daily_total_time > 0
							AND is_object( $this->getUserDateObject() )
							AND is_object( $this->getUserDateObject()->getPayPeriodObject() )
							AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {

							$plf = TTnew('PunchListFactory');
							$plf->getShiftPunchesByUserIDAndEpochAndArrayCriteria( $this->getUserDateObject()->getUser(), TTDate::getMiddleDayEpoch( $this->getUserDateObject()->getDateStamp() ), array(
													'premium_policy_id' => $pp_obj->getId(),
													'branch_selection_type_id' => $pp_obj->getBranchSelectionType(),
													'exclude_default_branch' => $pp_obj->getExcludeDefaultBranch(),
													'default_branch_id' => $this->getUserDateObject()->getUserObject()->getDefaultBranch(),
													'department_selection_type_id' => $pp_obj->getDepartmentSelectionType(),
													'exclude_default_department' => $pp_obj->getExcludeDefaultDepartment(),
													'default_department_id' => $this->getUserDateObject()->getUserObject()->getDefaultDepartment(),
													'job_group_selection_type_id' => $pp_obj->getJobGroupSelectionType(),
													'job_selection_type_id' => $pp_obj->getJobSelectionType(),
													'job_item_group_selection_type_id' => $pp_obj->getJobItemGroupSelectionType(),
													'job_item_selection_type_id' => $pp_obj->getJobItemSelectionType(),
												 ) );
							$shift_data = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getShiftData( NULL, $this->getUserDateObject()->getUser(), TTDate::getMiddleDayEpoch( $this->getUserDateObject()->getDateStamp() ), 'nearest', NULL, NULL, $pp_obj->getMinimumTimeBetweenShift(), $plf );
							//$shift_data = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getShiftData( $this->getUserDateID() );
							//Debug::Arr($shift_data, ' Shift Data:', __FILE__, __LINE__, __METHOD__, 10);

							if ( is_array($shift_data) ) {
								$udtlf = TTnew( 'UserDateTotalListFactory' );

								$total_shifts = count($shift_data);
								$x=1;
								foreach( $shift_data as $shift_data_arr )  {
									$total_time = 0;
									$punch_total_time = $shift_data_arr['total_time'];
									if ( $punch_total_time == 0 ) { //Skip shift if its not complete.
										continue;
									}

									foreach( $shift_data_arr['user_date_ids'] as $user_date_id ) {
										//Apply meal policy adjustment BEFORE min/max times
										if ( $pp_obj->getIncludeMealPolicy() == TRUE AND isset( $udt_meal_policy_adjustment_arr[$user_date_id] ) ) {
											Debug::text(' Meal Policy Adjustment Found: '. $udt_meal_policy_adjustment_arr[$user_date_id], __FILE__, __LINE__, __METHOD__, 10);
											$punch_total_time = bcadd( $punch_total_time, $udt_meal_policy_adjustment_arr[$user_date_id] );
										}
										if ( $pp_obj->getIncludeBreakPolicy() == TRUE AND isset( $udt_break_policy_adjustment_arr[$user_date_id] ) ) {
											Debug::text(' Break Policy Adjustment Found: '. $udt_break_policy_adjustment_arr[$user_date_id], __FILE__, __LINE__, __METHOD__, 10);
											$punch_total_time = bcadd( $punch_total_time, $udt_break_policy_adjustment_arr[$user_date_id] );
										}
										Debug::text(' Found at least one shift, total time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
									}

									if ( $punch_total_time > $pp_obj->getMinimumShiftTime() ) {
										Debug::text(' Shift exceeds minimum shift time...', __FILE__, __LINE__, __METHOD__, 10);
										continue;
									} else {
										$punch_total_time = bcsub( $pp_obj->getMinimumShiftTime(), $punch_total_time );
									}

									$premium_policy_daily_total_time = 0;
									if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
										$premium_policy_daily_total_time = $udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $this->getUserDateID(), $pp_obj->getId() );
										Debug::text('X: '. $x .'/'. $total_shifts .' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

										if ( $pp_obj->getMinimumTime() > 0 ) {
											//FIXME: Split the minimum time up between all the punches somehow.
											//Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
											//and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
											//for the day. If its applied to the last it will be just 1hr.
											//Min & Max time is based on the shift time, rather then per punch pair time.
											if ( $x == $total_shifts AND bcadd( $premium_policy_daily_total_time, $punch_total_time ) < $pp_obj->getMinimumTime() ) {
												$total_time = bcsub( $pp_obj->getMinimumTime(), $premium_policy_daily_total_time );
											} else {
												$total_time = $punch_total_time;
											}
										} else {
											$total_time = $punch_total_time;
										}

										Debug::text(' Total Time After Minimum is applied: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( $pp_obj->getMaximumTime() > 0 ) {
											//Min & Max time is based on the shift time, rather then per punch pair time.
											if ( bcadd( $premium_policy_daily_total_time, $total_time ) > $pp_obj->getMaximumTime() ) {
												Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
												$total_time = bcsub( $total_time, bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() ) );
											}
										}
									} else {
										$total_time = $punch_total_time;
									}

									Debug::text(' Total Punch Control Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
									if ( $total_time > 0 ) {
										Debug::text(' Applying  Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

										//Find branch,department,job,task of last punch_control_id in shift.
										if ( isset($shift_data_arr['punch_control_ids']) ) {
											$punch_control_id = $shift_data_arr['punch_control_ids'][0];

											$udtlf->getByUserDateIdAndPunchControlId($this->getUserDateID(), $punch_control_id);
											if ( $udtlf->getRecordCount() > 0 ) {
												$udt_obj = $udtlf->getCurrent();

												$udtf = TTnew( 'UserDateTotalFactory' );
												$udtf->setUserDateID( $this->getUserDateID() );
												$udtf->setStatus( 10 ); //System
												$udtf->setType( 40 ); //Premium
												$udtf->setPremiumPolicyId( $pp_obj->getId() );
												$udtf->setBranch( $udt_obj->getBranch() );
												$udtf->setDepartment( $udt_obj->getDepartment() );
												$udtf->setJob( $udt_obj->getJob() );
												$udtf->setJobItem( $udt_obj->getJobItem() );

												$udtf->setQuantity( $udt_obj->getQuantity() );
												$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

												$udtf->setTotalTime( $total_time );
												$udtf->setEnableCalcSystemTotalTime(FALSE);
												if ( $udtf->isValid() == TRUE ) {
													$udtf->Save();
												}
												unset($udtf);
											}
										}
									}
									$x++;
								}
								unset($shift_data_arr, $total_shifts, $punch_total_time, $total_time);
							}
							unset($shift_data);
						} else {
							Debug::Text('No shift data to process...', __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($udtlf, $udt_obj);
						break;
/*
 *					//Merged with Date/Time criteria above to reduce code duplication.
					case 100: //Advanced
						Debug::text(' Advanced Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

						if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
										AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
							$maximum_shift_time = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime();
						}
						if ( !isset($maximum_shift_time) OR $maximum_shift_time < 86400 ) {
							$maximum_shift_time = 86400;
						}

						//Make sure this is a valid day
						if ( $pp_obj->isActive( $this->getUserDateObject()->getDateStamp()-$maximum_shift_time, $this->getUserDateObject()->getDateStamp()+$maximum_shift_time, $this->getUserDateObject()->getUser() ) ) {
							Debug::text(' Premium Policy Is Active On This Day.', __FILE__, __LINE__, __METHOD__, 10);

							$total_daily_time_used = 0;
							$daily_trigger_time = 0;
							$maximum_daily_trigger_time = FALSE;

							$udtlf = TTnew( 'UserDateTotalListFactory' );

							if ( $pp_obj->isHourRestricted() == TRUE ) {
								if ( $pp_obj->getWeeklyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) {
									//Get Pay Period Schedule info
									if ( is_object( $this->getUserDateObject()->getPayPeriodObject() )
											AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {
										$start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
									} else {
										$start_week_day_id = 0;
									}
									Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

									//$weekly_total_time = $udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
									$weekly_total_time = $udtlf->getWeekWorkedTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp(), TTDate::getBeginWeekEpoch($this->getUserDateObject()->getDateStamp(), $start_week_day_id) );
									if ( $weekly_total_time > $pp_obj->getWeeklyTriggerTime() ) {
										$daily_trigger_time = 0;
									} else {
										$daily_trigger_time = $pp_obj->getWeeklyTriggerTime() - $weekly_total_time;
									}
									Debug::text(' Weekly Trigger Time: '. $daily_trigger_time .' Weekly Total Time: '. $weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);
								}

								if ( $pp_obj->getDailyTriggerTime() > 0 AND $pp_obj->getDailyTriggerTime() > $daily_trigger_time ) {
									$daily_trigger_time = $pp_obj->getDailyTriggerTime();
								}

								if ( $pp_obj->getMaximumDailyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0  ) {
									$maximum_daily_trigger_time = ( $pp_obj->getMaximumDailyTriggerTime() > 0 ) ? ($pp_obj->getMaximumDailyTriggerTime()-$pp_obj->getDailyTriggerTime()) : FALSE;
									$maximum_weekly_trigger_time = ( isset($weekly_total_time) AND $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) ? ($pp_obj->getMaximumWeeklyTriggerTime()-$weekly_total_time) : FALSE;

									Debug::text(' Maximum Daily: '. $maximum_daily_trigger_time .' Weekly: '. $maximum_weekly_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
									if ( $maximum_daily_trigger_time > 0 AND ( $maximum_weekly_trigger_time === FALSE OR $maximum_daily_trigger_time < $maximum_weekly_trigger_time ) ) {
										$pp_obj->setMaximumTime( $maximum_daily_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
										Debug::text(' Set Daily Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
									} else {
										if ( $maximum_weekly_trigger_time !== FALSE AND ( $maximum_weekly_trigger_time <= 0 OR ( $maximum_weekly_trigger_time < $daily_trigger_time ) ) ) {
											Debug::text(' Exceeded Weekly Maximum Time to: '. $pp_obj->getMaximumTime() .' Skipping...', __FILE__, __LINE__, __METHOD__, 10);
											continue;
										}
										$pp_obj->setMaximumTime( $maximum_weekly_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
										Debug::text(' Set Weekly Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
										$maximum_daily_trigger_time = $maximum_weekly_trigger_time;
									}
									unset($maximum_weekly_trigger_time);
								}
							}
							Debug::text(' Daily Trigger Time: '. $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

							//Loop through all worked (status: 20) UserDateTotalRows
							$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
							$i = 1;
							if ( $udtlf->getRecordCount() > 0 ) {
								Debug::text('Found Total Hours to attempt to apply premium policy... Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

								foreach( $udtlf as $udt_obj ) {
									//Ignore incomplete punches
									if ( $udt_obj->getTotalTime() == 0 ) {
										continue;
									}

									//How do we handle actual shifts for premium time?
									//So if premium policy starts at 1PM for shifts, to not
									//include employees who return from lunch at 1:30PM.
									//Create a function that takes all punches for a day, and returns
									//the first in and last out time for a given shift when taking
									//into account minimum time between shifts, as well as the total time for that shift.
									//We can then use that time for ActiveTime on premium policies, and determine if a
									//punch falls within the active time, then we add it to the total.
									if ( ($pp_obj->getIncludePartialPunch() == TRUE OR $pp_obj->isTimeRestricted() == TRUE ) AND $udt_obj->getPunchControlID() != FALSE ) {
										Debug::text('Time Restricted Premium Policy, lookup punches to get times.', __FILE__, __LINE__, __METHOD__, 10);

										if ( $pp_obj->getIncludePartialPunch() == FALSE ) {
											$shift_data = $this->getShiftDataByUserDateID( $this->getUserDateID() );
										}

										$plf = TTnew( 'PunchListFactory' );
										$plf->getByPunchControlId( $udt_obj->getPunchControlID() );
										if ( $plf->getRecordCount() > 0 ) {
											Debug::text('Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
											foreach( $plf as $punch_obj ) {
												if ( $pp_obj->getIncludePartialPunch() == TRUE ) {
													//Debug::text('Including Partial Punches...', __FILE__, __LINE__, __METHOD__, 10);

													if ( $punch_obj->getStatus() == 10 ) {
														$punch_times['in'] = $punch_obj->getTimeStamp();
													} elseif ( $punch_obj->getStatus() == 20 ) {
														$punch_times['out'] = $punch_obj->getTimeStamp();
													}
												} else {
													if ( isset($shift_data) AND is_array($shift_data) ) {
														foreach( $shift_data as $shift ) {
															if ( $punch_obj->getTimeStamp() >= $shift['first_in']
																	AND $punch_obj->getTimeStamp() <= $shift['last_out'] ) {
																//Debug::Arr($shift,'Shift Data...', __FILE__, __LINE__, __METHOD__, 10);
																Debug::text('Punch ('. TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp() ).') inside shift time...', __FILE__, __LINE__, __METHOD__, 10);
																$punch_times['in'] = $shift['first_in'];
																$punch_times['out'] = $shift['last_out'];
																break;
															} else {
																Debug::text('Punch ('. TTDate::getDate('DATE+TIME', $punch_obj->getTimeStamp() ).') outside shift time...', __FILE__, __LINE__, __METHOD__, 10);
															}
														}
													}
												}
											}

											if ( isset($punch_times) AND count($punch_times) == 2
													AND ( $pp_obj->isActiveDate( $punch_times['in'] ) == TRUE OR $pp_obj->isActiveDate( $punch_times['out'] ) )
													AND ( $pp_obj->isActive( $punch_times['in'], $punch_times['out'], $this->getUserDateObject()->getUser() ) )
													AND $pp_obj->isActiveTime( $punch_times['in'], $punch_times['out'], $this->getUserDateObject()->getUser() ) == TRUE ) {
												//Debug::Arr($punch_times, 'Punch Times: ', __FILE__, __LINE__, __METHOD__, 10);
												$punch_total_time = $pp_obj->getPartialPunchTotalTime( $punch_times['in'], $punch_times['out'], $udt_obj->getTotalTime(), $this->getUserDateObject()->getUser() );
												$valid_user_date_total_ids[] = $udt_obj->getID(); //Need to record punches that fall within the active time so we can properly handle break/meal adjustments.
												Debug::text('Valid Punch pair in active time, Partial Punch Total Time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
											} else {
												Debug::text('InValid Punch Pair or outside Active Time...', __FILE__, __LINE__, __METHOD__, 10);
												$punch_total_time = 0;
											}
										}
									} elseif ( $pp_obj->isActive( $udt_obj->getUserDateObject()->getDateStamp(), NULL, $this->getUserDateObject()->getUser() ) == TRUE )  {
										$punch_total_time = $udt_obj->getTotalTime();
										$valid_user_date_total_ids[] = $udt_obj->getID();
									} else {
										$punch_total_time = 0;
									}

									$tmp_punch_total_time = $udt_obj->getTotalTime();
									Debug::text('aPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									//Apply meal policy adjustment as early as possible.
									if ( $pp_obj->getIncludeMealPolicy() == TRUE
											AND $pp_obj->isActiveDate( $this->getUserDateObject()->getDateStamp() ) == TRUE
											AND $pp_obj->isActiveDayofWeek( $this->getUserDateObject()->getDateStamp() ) == TRUE
											AND isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] )
											AND in_array( $udt_obj->getID(), $valid_user_date_total_ids) ) {
										Debug::text(' Meal Policy Adjustment Found: '. $udt_meal_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
										$punch_total_time = bcadd( $punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
										$tmp_punch_total_time = bcadd( $tmp_punch_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
									}
									Debug::text('bPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									//Apply break policy adjustment as early as possible.
									if ( $pp_obj->getIncludeBreakPolicy() == TRUE
										AND $pp_obj->isActiveDate( $this->getUserDateObject()->getDateStamp() ) == TRUE
										AND $pp_obj->isActiveDayofWeek( $this->getUserDateObject()->getDateStamp() ) == TRUE
										AND isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] )
										AND in_array( $udt_obj->getID(), $valid_user_date_total_ids) ) {
										Debug::text(' Break Policy Adjustment Found: '. $udt_break_policy_adjustment_arr[$udt_obj->getId()], __FILE__, __LINE__, __METHOD__, 10);
										$punch_total_time = bcadd( $punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
										$tmp_punch_total_time = bcadd( $tmp_punch_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
									}
									Debug::text('cPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									$total_daily_time_used += $tmp_punch_total_time;
									Debug::text('Daily Total Time Used: '. $total_daily_time_used, __FILE__, __LINE__, __METHOD__, 10);

									if ( $punch_total_time > 0 AND $total_daily_time_used > $daily_trigger_time
											AND ( $maximum_daily_trigger_time === FALSE OR ( $maximum_daily_trigger_time !== FALSE AND ($total_daily_time_used-$tmp_punch_total_time) <= $maximum_daily_trigger_time ) )
											) {
										Debug::text('Past Trigger Time!!', __FILE__, __LINE__, __METHOD__, 10);

										//Calculate how far past trigger time we are.
										$past_trigger_time = $total_daily_time_used - $daily_trigger_time;
										if ( $punch_total_time > $past_trigger_time ) {
											$punch_total_time = $past_trigger_time;
											Debug::text('Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										} else {
											Debug::text('NOT Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										}

										//If we are close to exceeding the maximum daily/weekly time, just use the remaining time.
										$maximum_trigger_time = $maximum_daily_trigger_time - ($total_daily_time_used-$tmp_punch_total_time);
										if ( $punch_total_time > $maximum_trigger_time AND $maximum_trigger_time > 0 ) {
											Debug::text('Using New Maximum Trigger Time as punch total time: '. $maximum_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
											$punch_total_time = $maximum_trigger_time;
										} else {
											Debug::text('NOT Using New Maximum Trigger Time as punch total time: '. $maximum_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
										}

										$total_time = $punch_total_time;
										if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
											$premium_policy_daily_total_time = (int)$udtlf->getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $this->getUserDateID(), $pp_obj->getId() );
											Debug::text(' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

											if ( $pp_obj->getMinimumTime() > 0 ) {
												//FIXME: Split the minimum time up between all the punches somehow.
												if ( $i == $udtlf->getRecordCount() AND bcadd( $premium_policy_daily_total_time, $total_time ) < $pp_obj->getMinimumTime() ) {
													$total_time = bcsub( $pp_obj->getMinimumTime(), $premium_policy_daily_total_time );
												}
											}

											Debug::text(' Total Time After Minimum is applied: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
											if ( $pp_obj->getMaximumTime() > 0 ) {
												//Make Min/Maximum time a per day setting rather than per user_date_total row setting.
												//This is ideal for fringe benefits or anything that applies on a "daily" basis.
												//if ( $total_time > $pp_obj->getMaximumTime() ) {
												//	Debug::text(' aMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
												//	$total_time = $pp_obj->getMaximumTime();
												//} else
												if ( bcadd( $premium_policy_daily_total_time, $total_time ) > $pp_obj->getMaximumTime() ) {
													Debug::text(' bMore than Maximum Time...', __FILE__, __LINE__, __METHOD__, 10);
													$total_time = bcsub( $total_time, bcsub( bcadd( $premium_policy_daily_total_time, $total_time ), $pp_obj->getMaximumTime() ) );
												}
											}
										}

										Debug::text(' Premium Punch Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
										if ( $total_time > 0 ) {
											Debug::text(' Applying Premium Time!: '. $total_time , __FILE__, __LINE__, __METHOD__, 10);

											//Check Shift Differential criteria *AFTER* calculatating daily/weekly time, as the shift differential
											//applies to the resulting time calculation, not the daily/weekly time calculation. Daily/Weekly should always include all time.
											//This is fundamentally different than the Shift Differential premium policy type.
											if ( ( $pp_obj->getBranchSelectionType() == 10
														AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																		AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) ) )

													OR ( $pp_obj->getBranchSelectionType() == 20
															AND in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
															AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																	OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																			AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

													OR ( $pp_obj->getBranchSelectionType() == 30
															AND !in_array( $udt_obj->getBranch(), (array)$pp_obj->getBranch() ) )
															AND ( $pp_obj->getExcludeDefaultBranch() == FALSE
																	OR ( $pp_obj->getExcludeDefaultBranch() == TRUE
																			AND $udt_obj->getBranch() != $this->getUserDateObject()->getUserObject()->getDefaultBranch() ) )

													) {
												Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserDateObject()->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

												if ( ( $pp_obj->getDepartmentSelectionType() == 10
															AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																	OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																			AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) ) )

														OR ( $pp_obj->getDepartmentSelectionType() == 20
																AND in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
																AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																		OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																				AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

														OR ( $pp_obj->getDepartmentSelectionType() == 30
																AND !in_array( $udt_obj->getDepartment(), (array)$pp_obj->getDepartment() ) )
																AND ( $pp_obj->getExcludeDefaultDepartment() == FALSE
																		OR ( $pp_obj->getExcludeDefaultDepartment() == TRUE
																				AND $udt_obj->getDepartment() != $this->getUserDateObject()->getUserObject()->getDefaultDepartment() ) )

														) {
													Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserDateObject()->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);

													if ( $pp_obj->getJobGroupSelectionType() == 10
															OR ( $pp_obj->getJobGroupSelectionType() == 20
																	AND is_object( $udt_obj->getJobObject() )
																	AND in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) )
															OR ( $pp_obj->getJobGroupSelectionType() == 30
																	AND is_object( $udt_obj->getJobObject() )
																	AND !in_array( $udt_obj->getJobObject()->getGroup(), (array)$pp_obj->getJobGroup() ) )
															) {
														Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

														if ( $pp_obj->getJobSelectionType() == 10
																OR ( $pp_obj->getJobSelectionType() == 20
																		AND in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
																OR ( $pp_obj->getJobSelectionType() == 30
																		AND !in_array( $udt_obj->getJob(), (array)$pp_obj->getJob() ) )
																) {
															Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

															if ( $pp_obj->getJobItemGroupSelectionType() == 10
																	OR ( $pp_obj->getJobItemGroupSelectionType() == 20
																			AND is_object( $udt_obj->getJobItemObject() )
																			AND in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) )
																	OR ( $pp_obj->getJobItemGroupSelectionType() == 30
																			AND is_object( $udt_obj->getJobItemObject() )
																			AND !in_array( $udt_obj->getJobItemObject()->getGroup(), (array)$pp_obj->getJobItemGroup() ) )
																	) {
																Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

																if ( $pp_obj->getJobItemSelectionType() == 10
																		OR ( $pp_obj->getJobItemSelectionType() == 20
																				AND in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
																		OR ( $pp_obj->getJobItemSelectionType() == 30
																				AND !in_array( $udt_obj->getJobItem(), (array)$pp_obj->getJobItem() ) )
																		) {
																	Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);


																	$udtf = TTnew( 'UserDateTotalFactory' );
																	$udtf->setUserDateID( $this->getUserDateID() );
																	$udtf->setStatus( 10 ); //System
																	$udtf->setType( 40 ); //Premium
																	$udtf->setPremiumPolicyId( $pp_obj->getId() );
																	$udtf->setBranch( $udt_obj->getBranch() );
																	$udtf->setDepartment( $udt_obj->getDepartment() );
																	$udtf->setJob( $udt_obj->getJob() );
																	$udtf->setJobItem( $udt_obj->getJobItem() );

																	$udtf->setQuantity( $udt_obj->getQuantity() );
																	$udtf->setBadQuantity( $udt_obj->getBadQuantity() );

																	$udtf->setTotalTime( $total_time );
																	$udtf->setEnableCalcSystemTotalTime(FALSE);
																	if ( $udtf->isValid() == TRUE ) {
																		$udtf->Save();
																	}
																	unset($udtf);

																} else {
																	Debug::text(' Shift Differential... DOES NOT Meet Task Criteria!', __FILE__, __LINE__, __METHOD__, 10);
																}
															} else {
																Debug::text(' Shift Differential... DOES NOT Meet Task Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
															}
														} else {
															Debug::text(' Shift Differential... DOES NOT Meet Job Criteria!', __FILE__, __LINE__, __METHOD__, 10);
														}
													} else {
														Debug::text(' Shift Differential... DOES NOT Meet Job Group Criteria!', __FILE__, __LINE__, __METHOD__, 10);
													}
												} else {
													Debug::text(' Shift Differential... DOES NOT Meet Department Criteria!', __FILE__, __LINE__, __METHOD__, 10);
												}
											} else {
												Debug::text(' Shift Differential... DOES NOT Meet Branch Criteria!', __FILE__, __LINE__, __METHOD__, 10);
											}

										} else {
											Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										Debug::text('Not Past Trigger Time Yet or Punch Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
									}

									$i++;
								}
							}
						}
						unset($udtlf, $udt_obj);
						break;
*/
				}
			}
		}

		$profiler->stopTimer( 'UserDateTotal::calcPremiumPolicyTotalTime() - Part 1');

		return TRUE;
	}

	function calcAbsencePolicyTotalTime() {
		//Don't do this, because it doubles up on paid time?
		//Only issue is if we want to add these hours to weekly OT hours or anything.
		//Does it double up on paid time, as it is paid time after all?

		/*
		Debug::text(' Adding Paid Absence Policy time to Regular Time: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$udtlf->getPaidAbsenceByUserDateID( $this->getUserDateID() );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ($udtlf as $udt_obj) {
				Debug::text(' Found some Paid Absence Policy time entries: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
				$udtf = TTnew( 'UserDateTotalFactory' );
				$udtf->setUserDateID( $this->getUserDateID() );
				$udtf->setStatus( 10 ); //System
				$udtf->setType( 20 ); //Regular
				$udtf->setBranch( $udt_obj->getBranch() );
				$udtf->setDepartment( $udt_obj->getDepartment() );
				$udtf->setTotalTime( $udt_obj->getTotalTime() );
				$udtf->Save();
			}

			return TRUE;
		} else {
			Debug::text(' Found zero Paid Absence Policy time entries: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
		*/

		return TRUE;
	}

	//Meal policy deduct/include time should be calculated on a percentage basis between all branches/departments/jobs/tasks
	//rounded to the nearest 60 seconds. This is the only way to keep things "fair"
	//as we can never know which individual branch/department/job/task to deduct/include the time for.
	//
	//Use the Worked Time UserTotal rows to calculate the adjustment for each worked time row.
	//Since we need this information BEFORE any compaction occurs.
	function calcUserTotalMealPolicyAdjustment( $meal_policy_time ) {
		if ( $meal_policy_time == '' OR $meal_policy_time == 0 ) {
			return array();
		}
		Debug::text('Meal Policy Time: '. $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);

		$day_total_time = 0;
		$retarr = array();

		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach( $udtlf as $udt_obj ) {
				$udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

				$day_total_time = bcadd($day_total_time, $udt_obj->getTotalTime() );
			}
			Debug::text('Day Total Time: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array($udt_arr) AND $day_total_time > 0 ) {
				$remainder = 0;
				foreach( $udt_arr as $udt_id => $total_time ) {
					$udt_raw_meal_policy_time = bcmul( bcdiv( $total_time, $day_total_time ), $meal_policy_time );
					if ( $meal_policy_time > 0 ) {
						$rounded_udt_raw_meal_policy_time = floor($udt_raw_meal_policy_time);
						$remainder = bcadd( $remainder, bcsub( $udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time ) );
					} else {
						$rounded_udt_raw_meal_policy_time = ceil($udt_raw_meal_policy_time);
						$remainder = bcadd( $remainder, bcsub( $udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time ) );
					}
					$retarr[$udt_id] = (int)$rounded_udt_raw_meal_policy_time;

					Debug::text('UserDateTotal Row ID: '. $udt_id .' Raw Meal Policy Time: '. $udt_raw_meal_policy_time .'('. $rounded_udt_raw_meal_policy_time .') Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
				}

				//Add remainder rounded to the nearest second to the last row.
				if ( $meal_policy_time > 0 ) {
					$remainder = ceil( $remainder );
				} else {
					$remainder = floor( $remainder );
				}
				$retarr[$udt_id] = (int)bcadd($retarr[$udt_id], $remainder);
			}
		} else {
			Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retarr;
	}

	function calcMealPolicyTotalTime( $meal_policy_ids = NULL ) {
		//Debug::arr($meal_policy_ids, 'MealPolicyObject param:', __FILE__, __LINE__, __METHOD__, 10);

		//Get total worked time for the day.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$daily_total_time = $udtlf->getWorkedTimeSumByUserDateID( $this->getUserDateID() );

		$mplf = TTnew( 'MealPolicyListFactory' );

		//-1 = NO meals, 0 = Policy Group.
		if ( $meal_policy_ids == 0 OR $meal_policy_ids == NULL OR $meal_policy_ids == '' ) { //Skip if -No Meal- is set in the schedule policy.
			//Lookup meal policy from policy group.
			$mplf->getByPolicyGroupUserIdAndDayTotalTime( $this->getUserDateObject()->getUser(), $daily_total_time );
		} elseif ( is_array($meal_policy_ids) OR $meal_policy_ids > 0 ) {
			$mplf->getByIdAndCompanyIdAndDayTotalTime( $meal_policy_ids, $this->getUserDateObject()->getUserObject()->getCompany(), $daily_total_time );
		}

		if ( $mplf->getRecordCount() > 0 ) {
			Debug::text('Found Meal Policy to apply.', __FILE__, __LINE__, __METHOD__, 10);
			$meal_policy_obj = $mplf->getCurrent();
		}

		$meal_policy_time = 0;

		if ( isset($meal_policy_obj) AND is_object( $meal_policy_obj ) AND $daily_total_time >= $meal_policy_obj->getTriggerTime() ) {
			Debug::text('Meal Policy ID: '. $meal_policy_obj->getId() .' Type ID: '. $meal_policy_obj->getType() .' Amount: '. $meal_policy_obj->getAmount() .' Daily Total TIme: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

			//Get lunch total time.
			$lunch_total_time = 0;

			$plf = TTnew( 'PunchListFactory' );
			$plf->getByUserDateIdAndTypeId( $this->getUserDateId(), 20 ); //Only Lunch punches
			if ( $plf->getRecordCount() > 0 ) {
				$pair = 0;
				$x = 0;
				$out_for_lunch = FALSE;
				foreach ( $plf as $p_obj ) {
					if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 20 ) {
						$lunch_out_timestamp = $p_obj->getTimeStamp();
						$out_for_lunch = TRUE;
					} elseif ( $out_for_lunch == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 20) {
						$lunch_punch_arr[$pair][20] = $lunch_out_timestamp;
						$lunch_punch_arr[$pair][10] = $p_obj->getTimeStamp();
						$out_for_lunch = FALSE;
						$pair++;
						unset($lunch_out_timestamp);
					} else {
						$out_for_lunch = FALSE;
					}

					$x++;
				}

				if ( isset($lunch_punch_arr) ) {
					foreach( $lunch_punch_arr as $punch_control_id => $time_stamp_arr ) {
						if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
							$lunch_total_time = bcadd($lunch_total_time, bcsub($time_stamp_arr[10], $time_stamp_arr[20] ) );
						} else {
							Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				} else {
					Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			Debug::text(' Lunch Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);
			switch ( $meal_policy_obj->getType() ) {
				case 10: //Auto-Deduct
					Debug::text(' Lunch AutoDeduct.', __FILE__, __LINE__, __METHOD__, 10);
					if ( $meal_policy_obj->getIncludeLunchPunchTime() == TRUE ) {
						$meal_policy_time = bcsub( $meal_policy_obj->getAmount(), $lunch_total_time )*-1;
						//If they take more then their alloted lunch, zero it out so time isn't added.
						if ( $meal_policy_time > 0 ) {
							$meal_policy_time = 0;
						}
					} else {
						$meal_policy_time = $meal_policy_obj->getAmount()*-1;
					}
					break;
				case 15: //Auto-Include
					Debug::text(' Lunch AutoInclude.', __FILE__, __LINE__, __METHOD__, 10);
					if ( $meal_policy_obj->getIncludeLunchPunchTime() == TRUE ) {
						if ( $lunch_total_time > $meal_policy_obj->getAmount() ) {
							$meal_policy_time = $meal_policy_obj->getAmount();
						} else {
							$meal_policy_time = $lunch_total_time;
						}
					} else {
						$meal_policy_time = $meal_policy_obj->getAmount();
					}
					break;
			}

			Debug::text(' Meal Policy Total Time: '. $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);

			if ( $meal_policy_time != 0 ) {
				$udtf = TTnew( 'UserDateTotalFactory' );
				$udtf->setUserDateID( $this->getUserDateID() );
				$udtf->setStatus( 10 ); //System
				$udtf->setType( 100 ); //Lunch
				$udtf->setMealPolicyId( $meal_policy_obj->getId() );
				$udtf->setBranch( $this->getUserDateObject()->getUserObject()->getDefaultBranch() );
				$udtf->setDepartment( $this->getUserDateObject()->getUserObject()->getDefaultDepartment() );

				$udtf->setTotalTime( $meal_policy_time );
				$udtf->setEnableCalcSystemTotalTime(FALSE);
				if ( $udtf->isValid() == TRUE ) {
					$udtf->Save();
				}
				unset($udtf);
			}
		} else {
			Debug::text(' No Meal Policy found, or not after meal policy trigger time yet...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $meal_policy_time;
	}


	//Break policy deduct/include time should be calculated on a percentage basis between all branches/departments/jobs/tasks
	//rounded to the nearest 60 seconds. This is the only way to keep things "fair"
	//as we can never know which individual branch/department/job/task to deduct/include the time for.
	//
	//Use the Worked Time UserTotal rows to calculate the adjustment for each worked time row.
	//Since we need this information BEFORE any compaction occurs.
	function calcUserTotalBreakPolicyAdjustment( $break_policy_time ) {
		if ( $break_policy_time == '' OR $break_policy_time == 0 ) {
			return array();
		}
		Debug::text('Break Policy Time: '. $break_policy_time, __FILE__, __LINE__, __METHOD__, 10);

		$day_total_time = 0;
		$retarr = array();

		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), 20 );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach( $udtlf as $udt_obj ) {
				$udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

				$day_total_time = bcadd($day_total_time, $udt_obj->getTotalTime() );
			}
			Debug::text('Day Total Time: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array($udt_arr) ) {
				$remainder = 0;
				$remainder_udt_id = FALSE;
				foreach( $udt_arr as $udt_id => $total_time ) {
					if ( $total_time > 0 AND $day_total_time > 0 ) { //This prevents a divide by 0 warning below.
						$udt_raw_break_policy_time = bcmul( bcdiv( $total_time, $day_total_time ), $break_policy_time );
						if ( $break_policy_time > 0 ) {
							$rounded_udt_raw_break_policy_time = floor($udt_raw_break_policy_time);
							$remainder = bcadd( $remainder, bcsub( $udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time ) );
						} else {
							$rounded_udt_raw_break_policy_time = ceil($udt_raw_break_policy_time);
							$remainder = bcadd( $remainder, bcsub( $udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time ) );
						}
						$retarr[$udt_id] = (int)$rounded_udt_raw_break_policy_time;
						$remainder_udt_id = $udt_id;
						Debug::text('UserDateTotal Row ID: '. $udt_id .' Raw Break Policy Time: '. $udt_raw_break_policy_time .'('. $rounded_udt_raw_break_policy_time .') Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				if ( isset($retarr[$remainder_udt_id]) ) {
					//Add remainder rounded to the nearest second to the last row.
					if ( $break_policy_time > 0 ) {
						$remainder = ceil( $remainder );
					} else {
						$remainder = floor( $remainder );
					}
					$retarr[$remainder_udt_id] = (int)bcadd($retarr[$remainder_udt_id], $remainder);
				}
			}
		} else {
			Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retarr;
	}

	function calcBreakPolicyTotalTime( $break_policy_ids = NULL ) {
		//Debug::Arr($break_policy_ids, 'Break Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		
		//Get total worked time for the day.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$daily_total_time = $udtlf->getWorkedTimeSumByUserDateID( $this->getUserDateID() );
		Debug::text('Daily Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

		$bplf = TTnew( 'BreakPolicyListFactory' );

		//-1 = NO breaks, 0 = Policy Group.
		if ( is_array($break_policy_ids) AND !in_array( -1, $break_policy_ids ) AND !in_array( 0, $break_policy_ids )  ) {
			$bplf->getByIdAndCompanyIdAndDayTotalTime( $break_policy_ids, $this->getUserDateObject()->getUserObject()->getCompany(), $daily_total_time );
		} elseif ( !is_array($break_policy_ids) OR ( is_array($break_policy_ids) AND !in_array( -1, $break_policy_ids ) ) ) {
			//Lookup break policy from policy group.
			$bplf->getByPolicyGroupUserIdAndDayTotalTime( $this->getUserDateObject()->getUser(), $daily_total_time );
		}

		$break_policy_total_time = 0;

		if ( $bplf->getRecordCount() > 0 ) {
			Debug::text('Found Break Policy(ies) to apply: '. $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			$break_total_time_arr = array();
			$break_overall_total_time = 0;

			$plf = TTnew( 'PunchListFactory' );
			$plf->getByUserDateIdAndTypeId( $this->getUserDateId(), 30 ); //Only Break punches
			if ( $plf->getRecordCount() > 0 ) {
				$pair = 0;
				$x = 0;
				$out_for_break = FALSE;
				foreach ( $plf as $p_obj ) {
					if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
						$break_out_timestamp = $p_obj->getTimeStamp();
						$out_for_break = TRUE;
					} elseif ( $out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
						$break_punch_arr[$pair][20] = $break_out_timestamp;
						$break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
						$out_for_break = FALSE;
						$pair++;
						unset($break_out_timestamp);
					} else {
						$out_for_break = FALSE;
					}

					$x++;
				}

				if ( isset($break_punch_arr) ) {
					foreach( $break_punch_arr as $punch_control_id => $time_stamp_arr ) {
						if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
							$break_overall_total_time = bcadd($break_overall_total_time, bcsub($time_stamp_arr[10], $time_stamp_arr[20] ) );
							$break_total_time_arr[] = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
						} else {
							Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				} else {
					Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			//Debug::Arr($break_punch_arr, ' Break Punch Arr: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($break_total_time_arr, ' Break Total Time Arr: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::text(' Break Overall Total Time: '. $break_overall_total_time, __FILE__, __LINE__, __METHOD__, 10);

			$remaining_break_time = $break_overall_total_time;

			$i = 0;
			foreach( $bplf as $break_policy_obj ) {
				$break_policy_time = 0;
				if ( !isset($break_total_time_arr[$i]) ) {
					$break_total_time_arr[$i] = 0; //Prevent PHP warnings.
				}

				//This is the time that can be considered for the break.
				if ( $break_policy_obj->getIncludeMultipleBreaks() == TRUE ) {
					//If only one break policy is defined (say 30min auto-add after 0hrs w/include punch time)
					//and the employee punches out for two breaks, one for 10mins and one for 15mins, only the first break will be added back in.
					//Because TimeTrex tries to match each break to a specific break policy.
					//getIncludeMultipleBreaks(): is the flag that ignores how many breaks there are in total,
					//and just combines any breaks together that fall within the active after time.
					//So it doesn't matter if the employee takes 1 break or 30, they are all combined into one after the active_after time.
					//FIXME: Handle cases where one break policy includes multiples and another one does not. Currently the break time may be doubled up in this case.
					$eligible_break_total_time = array_sum( $break_total_time_arr );
					Debug::text(' Including multiple breaks...', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					$eligible_break_total_time = $break_total_time_arr[$i];
				}

				Debug::text('Break Policy ID: '. $break_policy_obj->getId() .' Type ID: '. $break_policy_obj->getType() .' Break Total Time: '. $eligible_break_total_time .' Amount: '. $break_policy_obj->getAmount() .' Daily Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
				switch ( $break_policy_obj->getType() ) {
					case 10: //Auto-Deduct
						Debug::text(' Break AutoDeduct...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $break_policy_obj->getIncludeBreakPunchTime() == TRUE ) {
							$break_policy_time = bcsub( $break_policy_obj->getAmount(), $eligible_break_total_time )*-1;
							//If they take more then their alloted break, zero it out so time isn't added.
							if ( $break_policy_time > 0 ) {
								$break_policy_time = 0;
							}
						} else {
							$break_policy_time = $break_policy_obj->getAmount()*-1;
						}
						break;
					case 15: //Auto-Include
						Debug::text(' Break AutoAdd...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $break_policy_obj->getIncludeBreakPunchTime() == TRUE ) {
							if ( $eligible_break_total_time > $break_policy_obj->getAmount() ) {
								$break_policy_time = $break_policy_obj->getAmount();
							} else {
								$break_policy_time = $eligible_break_total_time;
							}
						} else {
							$break_policy_time = $break_policy_obj->getAmount();
						}
						break;
				}

				if ( $break_policy_obj->getIncludeBreakPunchTime() == TRUE AND $break_policy_time > $remaining_break_time ) {
					$break_policy_time = $remaining_break_time;
				}
				if ( $break_policy_obj->getIncludeBreakPunchTime() == TRUE  ) { //Handle cases where some break policies include punch time, and others don't.
					$remaining_break_time -= $break_policy_time;
				}

				Debug::text(' Break Policy Total Time: '. $break_policy_time .' Break Policy ID: '. $break_policy_obj->getId() .' Remaining Time: '. $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

				if ( $break_policy_time != 0 ) {
					$break_policy_total_time = bcadd( $break_policy_total_time, $break_policy_time );

					$udtf = TTnew( 'UserDateTotalFactory' );
					$udtf->setUserDateID( $this->getUserDateID() );
					$udtf->setStatus( 10 ); //System
					$udtf->setType( 110 ); //Break
					$udtf->setBreakPolicyId( $break_policy_obj->getId() );
					$udtf->setBranch( $this->getUserDateObject()->getUserObject()->getDefaultBranch() );
					$udtf->setDepartment( $this->getUserDateObject()->getUserObject()->getDefaultDepartment() );

					$udtf->setTotalTime( $break_policy_time );
					$udtf->setEnableCalcSystemTotalTime(FALSE);
					if ( $udtf->isValid() == TRUE ) {
						$udtf->Save();
					}
					unset($udtf);
				}

				Debug::text(' bBreak Policy Total Time: '. $break_policy_time .' Break Policy ID: '. $break_policy_obj->getId() .' Remaining Time: '. $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

				$i++;
			}
		} else {
			Debug::text(' No Break Policy found, or not after break policy trigger time yet...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text(' Final Break Policy Total Time: '. $break_policy_total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $break_policy_total_time;
	}

	function calcAccrualPolicy() {
		//FIXME: There is a minor bug for hour based accruals that if a milestone has a maximum limit,
		//  and an employee recalculates there timesheet, and the limit is reached midweek, if its recalculated
		//  again, the days that get the accrual time won't always be in order because the accrual balance is deleted
		//  only for the day currently being calculated, so on Monday it will delete 1hr of accrual, but the balance will
		//  still include Tue,Wed,Thu and the limit may already be reached.

		//We still need to calculate accruals even if the total time is 0, because we may want to override a
		//policy to 0hrs, and if we skip entries with TotalTime() == 0, the accruals won't be updated.
		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Calculating Accrual Policies... Total Time: '. $this->getTotalTime() .' Date: '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);

			//Calculate accrual policies assigned to other overtime/premium/absence policies
			//Debug::text('ID: '. $this->getId() .' Overtime Policy ID: '. (int)$this->getOverTimePolicyID()  .' Premium Policy ID: '. (int)$this->getPremiumPolicyID() .' Absence Policy ID: '. (int)$this->getAbsencePolicyID(), __FILE__, __LINE__, __METHOD__, 10);

			//If overtime, premium or absence policy is an accrual, handle that now.
			if ( $this->getOverTimePolicyID() != FALSE ) {
				$accrual_policy_id = $this->getOverTimePolicyObject()->getAccrualPolicyID();
				Debug::text('Over Time Accrual Policy ID: '. $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);

				if ( $accrual_policy_id > 0 ) {
					Debug::text('Over Time Accrual Rate: '. $this->getOverTimePolicyObject()->getAccrualRate() .' Policy ID: '. $this->getOverTimePolicyObject()->getAccrualPolicyID() , __FILE__, __LINE__, __METHOD__, 10);
					$af = TTnew( 'AccrualFactory' );
					$af->setUser( $this->getUserDateObject()->getUser() );
					$af->setAccrualPolicyID( $accrual_policy_id );
					$af->setTimeStamp( $this->getUserDateObject()->getDateStamp() );
					$af->setUserDateTotalID( $this->getID() );

					$accrual_amount = bcmul( $this->getTotalTime(), $this->getOverTimePolicyObject()->getAccrualRate() );
					if ( $accrual_amount > 0 ) {
						$af->setType(10); //Banked
					} else {
						$af->setType(20); //Used
					}
					$af->setAmount( $accrual_amount );
					$af->setEnableCalcBalance(TRUE);
					if ( $af->isValid() ) {
						$af->Save();
					}

					unset($accrual_amount);
				} else {
					Debug::text('Skipping Over Time Accrual Policy ID: '. $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			if ( $this->getPremiumPolicyID() != FALSE ) {
				$accrual_policy_id = $this->getPremiumPolicyObject()->getAccrualPolicyID();
				Debug::text('Premium Accrual Policy ID: '. $accrual_policy_id, __FILE__, __LINE__, __METHOD__, 10);

				if ( $accrual_policy_id > 0 ) {
					$af = TTnew( 'AccrualFactory' );
					$af->setUser( $this->getUserDateObject()->getUser() );
					$af->setAccrualPolicyID( $accrual_policy_id );
					$af->setTimeStamp( $this->getUserDateObject()->getDateStamp() );
					$af->setUserDateTotalID( $this->getID() );

					$accrual_amount = bcmul( $this->getTotalTime(), $this->getPremiumPolicyObject()->getAccrualRate() );
					if ( $accrual_amount > 0 ) {
						$af->setType(10); //Banked
					} else {
						$af->setType(20); //Used
					}
					$af->setAmount( $accrual_amount );
					$af->setEnableCalcBalance(TRUE);
					if ( $af->isValid() ) {
						$af->Save();
					}

					unset($accrual_amount);
				}
			}
			if ( $this->getAbsencePolicyID() != FALSE ) {
				$accrual_policy_id = $this->getAbsencePolicyObject()->getAccrualPolicyID();
				Debug::text('Absence Accrual Policy ID: '. $accrual_policy_id .' Absence Policy ID: '. $this->getAbsencePolicyObject()->getID() .' Absence Policy IDb: '. $this->getAbsencePolicyID(), __FILE__, __LINE__, __METHOD__, 10);

				//Absence entry was modified, delete previous accrual entry and re-create it.
				$alf = TTnew('AccrualListFactory');
				$alf->getByUserIdAndUserDateTotalID( $this->getUserDateObject()->getUser(), $this->getID() );
				if ( $alf->getRecordCount() > 0 ) {
					foreach( $alf as $af_obj ) {
						Debug::text('Found existing accrual!! ID: '. $af_obj->getID() .' UserDateTotalID: '.  $this->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$af_obj->setDeleted(TRUE);
						$af_obj->setEnableCalcBalance(TRUE);
						if ( $af_obj->isValid() ) {
							$af_obj->Save();
						}
					}
				}
				unset($alf, $af_obj);

				if ( $accrual_policy_id > 0 ) {
					$af = TTnew( 'AccrualFactory' );
					$af->setUser( $this->getUserDateObject()->getUser() );
					$af->setAccrualPolicyID( $accrual_policy_id );
					$af->setTimeStamp( $this->getUserDateObject()->getDateStamp() );
					$af->setUserDateTotalID( $this->getID() );

					//By default we withdraw from accrual policy, so if there is a negative rate, deposit instead.
					$accrual_amount = bcmul( $this->getTotalTime(), bcmul( $this->getAbsencePolicyObject()->getAccrualRate(), -1 ) );
					if ( $accrual_amount > 0 ) {
						$af->setType(10); //Banked
					} else {
						$af->setType(20); //Used
					}
					$af->setAmount( $accrual_amount );

					$af->setEnableCalcBalance(TRUE);
					if ( $af->isValid() ) {
						$af->Save();
					}
				}
			}
			unset($af, $accrual_policy_id);


			//Calculate any hour based accrual policies.
			//if ( $this->getType() == 10 AND $this->getStatus() == 10 ) {
			if ( $this->getStatus() == 10 AND in_array( $this->getType(), array(20,30) ) ) { //Calculate hour based accruals on regular/overtime only.
				$aplf = TTnew( 'AccrualPolicyListFactory' );
				$aplf->getByPolicyGroupUserIdAndType( $this->getUserDateObject()->getUser(), 30 );
				if ( $aplf->getRecordCount() > 0 ) {
					Debug::text('Found Hour Based Accrual Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);
					foreach( $aplf as $ap_obj  ) {
						if ( $ap_obj->getMinimumEmployedDays() == 0
								OR TTDate::getDays( ($this->getUserDateObject()->getDateStamp()-$this->getUserDateObject()->getUserObject()->getHireDate()) ) >= $ap_obj->getMinimumEmployedDays() ) {
							Debug::Text('  User has been employed long enough.', __FILE__, __LINE__, __METHOD__,10);

							$milestone_obj = $ap_obj->getActiveMilestoneObject( $this->getUserDateObject()->getUserObject(), $this->getUserDateObject()->getDateStamp() );
							$accrual_balance = $ap_obj->getCurrentAccrualBalance( $this->getUserDateObject()->getUserObject()->getId(), $ap_obj->getId() );

							//If Maximum time is set to 0, make that unlimited.
							if ( is_object($milestone_obj) AND ( $milestone_obj->getMaximumTime() == 0 OR $accrual_balance < $milestone_obj->getMaximumTime() ) ) {
								$accrual_amount = $ap_obj->calcAccrualAmount( $milestone_obj, $this->getTotalTime(), 0);

								if ( $accrual_amount > 0 ) {
									$new_accrual_balance = bcadd( $accrual_balance, $accrual_amount);

									//If Maximum time is set to 0, make that unlimited.
									if ( $milestone_obj->getMaximumTime() > 0 AND $new_accrual_balance > $milestone_obj->getMaximumTime() ) {
										$accrual_amount = bcsub( $milestone_obj->getMaximumTime(), $accrual_balance, 4 );
									}
									Debug::Text('   Min/Max Adjusted Accrual Amount: '. $accrual_amount .' Limits: Min: '. $milestone_obj->getMinimumTime() .' Max: '. $milestone_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__,10);

									$af = TTnew( 'AccrualFactory' );
									$af->setUser( $this->getUserDateObject()->getUserObject()->getId() );
									$af->setType( 75 ); //Accrual Policy
									$af->setAccrualPolicyID( $ap_obj->getId() );
									$af->setUserDateTotalID( $this->getID() );
									$af->setAmount( $accrual_amount );
									$af->setTimeStamp( $this->getUserDateObject()->getDateStamp() );
									$af->setEnableCalcBalance( TRUE );

									if ( $af->isValid() ) {
										$af->Save();
									}
									unset($accrual_amount, $accrual_balance, $new_accrual_balance);
								} else {
									Debug::Text('   Accrual Amount is 0...', __FILE__, __LINE__, __METHOD__,10);
								}
							} else {
								Debug::Text('   Accrual Balance is outside Milestone Range. Or no milestone found. Skipping...', __FILE__, __LINE__, __METHOD__,10);

							}
						} else {
							Debug::Text('  User has only been employed: '. TTDate::getDays( ($this->getUserDateObject()->getDateStamp()-$this->getUserDateObject()->getUserObject()->getHireDate()) ) .' Days, not enough.', __FILE__, __LINE__, __METHOD__,10);
						}
					}
				} else {
					Debug::text('No Hour Based Accrual Policies to apply.', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('No worked time on this day or not proper type/status, skipping hour based accrual policies...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return TRUE;
	}
	
	function calcSystemTotalTime() {
		global $profiler;

		$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 1');

		if ( !is_object( $this->getUserDateObject() ) ) {
			Debug::text(' UserDateObject not found!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( is_object( $this->getUserDateObject() )
				AND is_object( $this->getUserDateObject()->getPayPeriodObject() )
				AND $this->getUserDateObject()->getPayPeriodObject()->getStatus() == 20 ) {
			Debug::text(' Pay Period is closed!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		
		//IMPORTANT: Make sure the timezone is set to the users timezone, prior to calculating policies,
		//as that will affect when date/time premium policies apply
		//Its also important that the timezone gets set back after calculating multiple punches in a batch as this can prevent other employees
		//from using the wrong timezone.
		//FIXME: How do we handle the employee moving between stations that themselves are in different timezones from the users default timezone?
		//How do we apply time based premium policies in that case?
		if ( is_object( $this->getUserDateObject() ) AND is_object( $this->getUserDateObject()->getUserObject() ) AND is_object( $this->getUserDateObject()->getUserObject()->getUserPreferenceObject() ) ) {
			$original_time_zone = TTDate::getTimeZone();
			TTDate::setTimeZone( $this->getUserDateObject()->getUserObject()->getUserPreferenceObject()->getTimeZone() );
		}

		//Take the worked hours, and calculate Total,Regular,Overtime,Premium hours from that.
		//This is where many of the policies will be applied
		//Such as any meal/overtime/premium policies.
		$return_value = FALSE;

		$udtlf = TTnew( 'UserDateTotalListFactory' );

		$this->deleteSystemTotalTime();

		//We can't assign a dock absence to a given branch/dept automatically,
		//Because several punches with different branches could fall within a schedule punch pair.
		//Just total up entire day, and entire scheduled time to see if we're over/under
		//FIXME: Handle multiple schedules on a single day better.
		$schedule_total_time = 0;
		$meal_policy_ids = NULL;
		$break_policy_ids = NULL;
		$slf = TTnew( 'ScheduleListFactory' );

		$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Holiday');
		//Check for Holidays
		$holiday_time = 0;
		$hlf = TTnew( 'HolidayListFactory' );
		$hlf->getByPolicyGroupUserIdAndDate( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp() );
		if ( $hlf->getRecordCount() > 0 ) {
			$holiday_obj = $hlf->getCurrent();
			Debug::text(' Found Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__,10);

			if ( $holiday_obj->isEligible( $this->getUserDateObject()->getUser() ) ) {
				Debug::text(' User is Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__,10);

				$holiday_time = $holiday_obj->getHolidayTime( $this->getUserDateObject()->getUser() );
				Debug::text(' User average time for Holiday: '. TTDate::getHours($holiday_time), __FILE__, __LINE__, __METHOD__,10);

				if ( $holiday_time > 0 AND $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() != FALSE ) {
					Debug::text(' Adding Holiday hours: '. TTDate::getHours($holiday_time), __FILE__, __LINE__, __METHOD__,10);
					$udtf = TTnew( 'UserDateTotalFactory' );
					$udtf->setUserDateID( $this->getUserDateID() );
					$udtf->setStatus( 30 ); //Absence
					$udtf->setType( 10 ); //Total
					$udtf->setBranch( $this->getUserDateObject()->getUserObject()->getDefaultBranch() );
					$udtf->setDepartment( $this->getUserDateObject()->getUserObject()->getDefaultDepartment() );
					$udtf->setAbsencePolicyID( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() );
					$udtf->setTotalTime( $holiday_time );
					$udtf->setEnableCalcSystemTotalTime(FALSE);
					if ( $udtf->isValid() ) {
						$udtf->Save();
					}
				}
			}

			$slf->getByUserDateIdAndStatusId( $this->getUserDateID(), 20 );
			$schedule_absence_total_time = 0;
			if ( $slf->getRecordCount() > 0 ) {
				//Check for schedule policy
				foreach ( $slf as $s_obj ) {
					Debug::text(' Schedule Absence Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

					$schedule_absence_total_time += $s_obj->getTotalTime();
					if ( is_object($s_obj->getSchedulePolicyObject() ) AND $s_obj->getSchedulePolicyObject()->getAbsencePolicyID() > 0 ) {
						$holiday_absence_policy_id = $s_obj->getSchedulePolicyObject()->getAbsencePolicyID();
						Debug::text(' Found Absence Policy for docking: '. $holiday_absence_policy_id, __FILE__, __LINE__, __METHOD__,10);
					} else {
						Debug::text(' NO Absence Policy : ', __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}

			$holiday_total_under_time = $schedule_absence_total_time - $holiday_time;
			if ( isset($holiday_absence_policy_id) AND $holiday_total_under_time > 0 ) {
				Debug::text(' Schedule Under Time Case: '. $holiday_total_under_time, __FILE__, __LINE__, __METHOD__,10);
				$udtf = TTnew( 'UserDateTotalFactory' );
				$udtf->setUserDateID( $this->getUserDateID() );
				$udtf->setStatus( 30 ); //Absence
				$udtf->setType( 10 ); //Total
				$udtf->setBranch( $this->getUserDateObject()->getUserObject()->getDefaultBranch() );
				$udtf->setDepartment( $this->getUserDateObject()->getUserObject()->getDefaultDepartment() );
				$udtf->setAbsencePolicyID( $holiday_absence_policy_id );
				$udtf->setTotalTime( $holiday_total_under_time );
				$udtf->setEnableCalcSystemTotalTime(FALSE);
				if ( $udtf->isValid() ) {
					$udtf->Save();
				}
			}
			unset($holiday_total_under_time, $holiday_absence_policy_id, $schedule_absence_total_time);
		}
		$profiler->stopTimer( 'UserDateTotal::calcSystemTotalTime() - Holiday');

		//Do this after holiday policies have been applied, so if someone
		//schedules a holiday manually, we don't double up on the time.
		$slf->getByUserDateId( $this->getUserDateID() );
		if ( $slf->getRecordCount() > 0 ) {
			//Check for schedule policy
			foreach ( $slf as $s_obj ) {
				Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);
				if ( $s_obj->getStatus() == 20 AND $s_obj->getAbsencePolicyID() != '' ) {
					Debug::text(' Scheduled Absence Found of Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

					//If a holiday policy is applied on this day, ignore the schedule so we don't duplicate it.
					//We could take the difference, and use the greatest of the two,
					//But I think that will just open the door for errors.
					if ( !isset($holiday_obj) OR ( $holiday_time == 0 AND is_object($holiday_obj) AND $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() != $s_obj->getAbsencePolicyID() ) ) {
						$udtf = TTnew( 'UserDateTotalFactory' );
						$udtf->setUserDateID( $this->getUserDateID() );
						$udtf->setStatus( 30 ); //Absence
						$udtf->setType( 10 ); //Total
						$udtf->setBranch( $s_obj->getBranch() );
						$udtf->setDepartment( $s_obj->getDepartment() );
						$udtf->setJob( $s_obj->getJob() );
						$udtf->setJobItem( $s_obj->getJobItem() );
						$udtf->setAbsencePolicyID( $s_obj->getAbsencePolicyID() );
						$udtf->setTotalTime( $s_obj->getTotalTime() );
						$udtf->setEnableCalcSystemTotalTime(FALSE);
						if ( $udtf->isValid() ) {
							$udtf->Save();
						}
					} else {
						Debug::text(' Holiday Time Found, ignoring schedule!', __FILE__, __LINE__, __METHOD__,10);
					}
				}
				/*
				elseif ( $s_obj->getStatus() == 10 ) {

					$schedule_policy_ids[] = $s_obj->getSchedulePolicyID(); //Save schedule policies ID so we can passs them onto Premium Policies.

					$schedule_total_time += $s_obj->getTotalTime();
					if ( is_object($s_obj->getSchedulePolicyObject() ) ) {
						$schedule_absence_policy_id = $s_obj->getSchedulePolicyObject()->getAbsencePolicyID();
						$meal_policy_obj = $s_obj->getSchedulePolicyObject()->getMealPolicyObject();
						Debug::text(' Found Absence Policy for docking: '. $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__,10);
					} else {
						Debug::text(' NO Absence Policy : ', __FILE__, __LINE__, __METHOD__,10);
					}
				}
				*/
			}
		} else {
			Debug::text(' No Schedules found. ', __FILE__, __LINE__, __METHOD__,10);
		}
		unset($s_obj);
		unset($holiday_time, $holiday_obj);

		//Loop through punches on this day finding matching schedules.
		//Since schedules and punches may or may not fall on the same day, (there are always cases where they may not, due to employees coming in late) this is the only real way to match them properly.
		//This can happen even with assign shifts to the day they start on, if an employees shift starts at 11PM and they are 1.5hrs late, or if the shift starts at 12:30AM and they are 30 mins early.
		$schedule_policy_ids = array();
		$plf = TTnew( 'PunchListFactory' );
		$plf->getByUserDateId( $this->getUserDateID() );
		if ( $plf->getRecordCount() > 0 ) {
			foreach( $plf as $p_obj ) {
				$schedule_ids[] = $p_obj->findScheduleID( NULL, $this->getUserDateObject()->getUser() );
			}
		} else {
			//Debug::text(' No Punches found, grabbing all schedules on this day...', __FILE__, __LINE__, __METHOD__,10);
			//FIXME: If no punches are on a specific day, then its schedules are not even considered.
			//This breaks undertime absences, since they should have a full day of absence time but instead they won't have any.
			//It also doesn't work properly with split shifts, where the employee works the first part but not the 2nd part and should get undertime absence for the 2nd.
			//However if we handle it this way, the same scheduled shift can accounted for twice when the schedule and punches fall on different days.
			/*
			$slf->getByUserDateId( $this->getUserDateID() );
			if ( $slf->getRecordCount() > 0 ) {
				//Check for schedule policy
				foreach ( $slf as $s_obj ) {
					$schedule_ids[] = $s_obj->getId();
				}
			}
			*/
		}

		if ( isset($schedule_ids) ) {
			$slf->getByCompanyIDAndId( $this->getUserDateObject()->getUserObject()->getCompany(), array_unique( (array)$schedule_ids ) );
			if ( $slf->getRecordCount() > 0 ) {
				foreach( $slf as $s_obj ) {
					//Save schedule policies ID so we can pass them onto Premium Policies.
					//Do this for both working and absence schedules, for purposes of calculating premium when employees are not scheduled.
					$schedule_policy_ids[] = $s_obj->getSchedulePolicyID();
					if ( $s_obj->getStatus() == 10 ) {
						$schedule_total_time += $s_obj->getTotalTime();
						if ( is_object( $s_obj->getSchedulePolicyObject() ) ) {
							$schedule_absence_policy_id = $s_obj->getSchedulePolicyObject()->getAbsencePolicyID();
							$meal_policy_ids = $s_obj->getSchedulePolicyObject()->getMealPolicyID();
							$break_policy_ids = $s_obj->getSchedulePolicyObject()->getBreakPolicy();
							Debug::text(' Found Absence Policy for docking: '. $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__,10);
						} else {
							Debug::text(' NO Absence Policy : ', __FILE__, __LINE__, __METHOD__,10);
						}
					}
				}
			}
		}
		unset($plf, $p_obj, $s_obj, $slf, $schedule_ids );

		//Handle Meal Policy time.
		//Do this after schedule meal policies have been looked up, as those override any policy group meal policies.
		$meal_policy_time = $this->calcMealPolicyTotalTime( $meal_policy_ids );
		$udt_meal_policy_adjustment_arr = $this->calcUserTotalMealPolicyAdjustment( $meal_policy_time );
		//Debug::Arr($udt_meal_policy_adjustment_arr, 'UserDateTotal Meal Policy Adjustment: ', __FILE__, __LINE__, __METHOD__,10);

		$break_policy_time = $this->calcBreakPolicyTotalTime( $break_policy_ids );
		$udt_break_policy_adjustment_arr = $this->calcUserTotalBreakPolicyAdjustment( $break_policy_time );
		//Debug::Arr($udt_break_policy_adjustment_arr, 'UserDateTotal Break Policy Adjustment: ', __FILE__, __LINE__, __METHOD__,10);

		$daily_total_time = $this->getDailyTotalTime();
		Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__,10);

		//Check for overtime policies or undertime absence policies
		if ( $daily_total_time > $schedule_total_time ) {
			Debug::text(' Schedule Over Time Case: ', __FILE__, __LINE__, __METHOD__,10);
		} elseif ( isset($schedule_absence_policy_id) AND $schedule_absence_policy_id != '' AND $daily_total_time < $schedule_total_time ) {
			$total_under_time = bcsub($schedule_total_time, $daily_total_time);

			if ( $total_under_time > 0 ) {
				Debug::text(' Schedule Under Time Case: '. $total_under_time .' Absence Policy ID: '. $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__,10);
				$udtf = TTnew( 'UserDateTotalFactory' );
				$udtf->setUserDateID( $this->getUserDateID() );
				$udtf->setStatus( 30 ); //Absence
				$udtf->setType( 10 ); //Total
				$udtf->setBranch( $this->getUserDateObject()->getUserObject()->getDefaultBranch() );
				$udtf->setDepartment( $this->getUserDateObject()->getUserObject()->getDefaultDepartment() );
				$udtf->setAbsencePolicyID( $schedule_absence_policy_id );
				$udtf->setTotalTime( $total_under_time );
				$udtf->setEnableCalcSystemTotalTime(FALSE);
				if ( $udtf->isValid() ) {
					$udtf->Save();
				}
			} else {
				Debug::text(' Schedule Under Time is a negative value, skipping dock time: '. $total_under_time .' Absence Policy ID: '. $schedule_absence_policy_id, __FILE__, __LINE__, __METHOD__,10);
			}
		} else {
			Debug::text(' No Dock Absenses', __FILE__, __LINE__, __METHOD__,10);
		}
		unset($schedule_absence_policy_id);

		/*
		//This is no longer needed as calcAbsencePolicyTotalTime() is a NO-OP now.
		//Do this AFTER the UnderTime absence policy is submitted.
		$recalc_daily_total_time = $this->calcAbsencePolicyTotalTime();
		if ( $recalc_daily_total_time == TRUE ) {
			//Total up all "worked" hours for the day again, this time include
			//Paid Absences.
			$daily_total_time = $this->getDailyTotalTime();
			Debug::text('ReCalc Daily Total Time for Day: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		$profiler->stopTimer( 'UserDateTotal::calcSystemTotalTime() - Part 1');

		$user_data_total_compact_arr = $this->calcOverTimePolicyTotalTime( $udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr );
		//Debug::Arr($user_data_total_compact_arr, 'User Data Total Compact Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//Insert User Date Total rows for each compacted array entry.
		//The reason for compacting is to reduce the amount of rows as much as possible.
		if ( is_array($user_data_total_compact_arr) ) {
			$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 2');

			Debug::text('Compact Array Exists: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach($user_data_total_compact_arr as $type_id => $udt_arr ) {
				Debug::text('Compact Array Entry: Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);

				if ( $type_id == 20 ) {
					//Regular Time
					//Debug::text('Compact Array Entry: Branch ID: '. $udt_arr[' , __FILE__, __LINE__, __METHOD__, 10);
					foreach($udt_arr as $branch_id => $branch_arr ) {
						//foreach($branch_arr as $department_id => $total_time ) {
						foreach($branch_arr as $department_id => $department_arr ) {
							foreach($department_arr as $job_id => $job_arr ) {
								foreach($job_arr as $job_item_id => $data_arr ) {

									Debug::text('Compact Array Entry: Regular Time - Branch ID: '. $branch_id .' Department ID: '. $department_id .' Job ID: '. $job_id .' Job Item ID: '. $job_item_id .' Total Time: '. $data_arr['total_time'] , __FILE__, __LINE__, __METHOD__, 10);
									$user_data_total_expanded[] = array(
																		'type_id' => $type_id,
																		'over_time_policy_id' => NULL,
																		'branch_id' => $branch_id,
																		'department_id' => $department_id,
																		'job_id' => $job_id,
																		'job_item_id' => $job_item_id,
																		'total_time' => $data_arr['total_time'],
																		'quantity' => $data_arr['quantity'],
																		'bad_quantity' => $data_arr['bad_quantity']
																		);
								}
							}
						}
					}
				} else {
					//Overtime
					//Overtime array is completely different then regular time array!
					foreach($udt_arr as $over_time_policy_id => $policy_arr ) {
						foreach($policy_arr as $branch_id => $branch_arr ) {
							//foreach($branch_arr as $department_id => $total_time ) {
							foreach($branch_arr as $department_id => $department_arr ) {
								foreach($department_arr as $job_id => $job_arr ) {
									foreach($job_arr as $job_item_id => $data_arr ) {

										Debug::text('Compact Array Entry: Policy ID: '. $over_time_policy_id .' Branch ID: '. $branch_id .' Department ID: '. $department_id .' Job ID: '. $job_id .' Job Item ID: '. $job_item_id .' Total Time: '. $data_arr['total_time'] , __FILE__, __LINE__, __METHOD__, 10);
										$user_data_total_expanded[] = array(
																			'type_id' => $type_id,
																			'over_time_policy_id' => $over_time_policy_id,
																			'branch_id' => $branch_id,
																			'department_id' => $department_id,
																			'job_id' => $job_id,
																			'job_item_id' => $job_item_id,
																			'total_time' => $data_arr['total_time'],
																			'quantity' => $data_arr['quantity'],
																			'bad_quantity' => $data_arr['bad_quantity']
																			);
									}
								}
							}
						}
					}
				}

				unset($policy_arr, $branch_arr, $department_arr, $job_arr, $over_time_policy_id, $branch_id, $department_id, $job_id, $job_item_id, $data_arr);
			}
			$profiler->stopTimer( 'UserDateTotal::calcSystemTotalTime() - Part 2');

			//var_dump($user_data_total_expanded);
			//Do the actual inserts now.
			if ( isset($user_data_total_expanded) ) {
				foreach($user_data_total_expanded as $data_arr) {
					$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 2b');

					Debug::text('Inserting from expanded array, Type ID: '.  $data_arr['type_id'], __FILE__, __LINE__, __METHOD__, 10);
					$udtf = TTnew( 'UserDateTotalFactory' );
					$udtf->setUserDateID( $this->getUserDateID() );
					$udtf->setStatus( 10 ); //System
					$udtf->setType( $data_arr['type_id'] );
					if ( isset($data_arr['over_time_policy_id']) ) {
						$udtf->setOverTimePolicyId( $data_arr['over_time_policy_id'] );
					}

					$udtf->setBranch( $data_arr['branch_id'] );
					$udtf->setDepartment( $data_arr['department_id'] );
					$udtf->setJob( $data_arr['job_id'] );
					$udtf->setJobItem( $data_arr['job_item_id'] );

					$udtf->setQuantity( $data_arr['quantity'] );
					$udtf->setBadQuantity( $data_arr['bad_quantity'] );

					$udtf->setTotalTime( $data_arr['total_time'] );
					$udtf->setEnableCalcSystemTotalTime(FALSE);
					if ( $udtf->isValid() ) {
						$udtf->Save();
					} else {
						Debug::text('aINVALID UserDateTotal Entry!!: ', __FILE__, __LINE__, __METHOD__, 10);
					}

					$profiler->stopTimer( 'UserDateTotal::calcSystemTotalTime() - Part 2b');

				}
				unset($user_data_total_expanded);
			}

		} else {
			$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 3');

			//We need to break this out by branch, dept, job, task
			$udtlf = TTnew( 'UserDateTotalListFactory' );

			//FIXME: Should Absence time be included as "regular time". We do this on
			//the timesheet view manually as of 12-Jan-06. If we included it in the
			//regular time system totals, we wouldn't have to do it manually.
			//$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), array(20,30) );
			$udtlf->getByUserDateIdAndStatus( $this->getUserDateID(), array(20) );
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text('Found Total Hours for just regular time: Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				$user_date_regular_time_compact_arr = NULL;
				foreach( $udtlf as $udt_obj ) {
					//Create compact array, so we don't make as many system entries.
					//Check if this is a paid absence or not.
					if ( $udt_obj->getStatus() == 20 AND $udt_obj->getTotalTime() > 0 ) {

						$udt_total_time = $udt_obj->getTotalTime();
						if ( isset( $udt_meal_policy_adjustment_arr[$udt_obj->getId()] ) ) {
							$udt_total_time = bcadd( $udt_total_time, $udt_meal_policy_adjustment_arr[$udt_obj->getId()] );
						}
						if ( isset( $udt_break_policy_adjustment_arr[$udt_obj->getId()] ) ) {
							$udt_total_time = bcadd( $udt_total_time, $udt_break_policy_adjustment_arr[$udt_obj->getId()] );
						}

						if ( isset($user_date_regular_time_compact_arr[(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]) ) {
							Debug::text('     Adding to Compact Array: Regular Time -  Branch: '. (int)$udt_obj->getBranch() .' Department: '. (int)$udt_obj->getDepartment(), __FILE__, __LINE__, __METHOD__, 10);
							$user_date_regular_time_compact_arr[(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['total_time'] += $udt_total_time;
							$user_date_regular_time_compact_arr[(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['quantity'] += $udt_obj->getQuantity();
							$user_date_regular_time_compact_arr[(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()]['bad_quantity'] += $udt_obj->getBadQuantity();
						} else {
							$user_date_regular_time_compact_arr[(int)$udt_obj->getBranch()][(int)$udt_obj->getDepartment()][(int)$udt_obj->getJob()][(int)$udt_obj->getJobItem()] = array( 'total_time' => $udt_total_time, 'quantity' => $udt_obj->getQuantity(), 'bad_quantity' => $udt_obj->getBadQuantity() );
						}
						unset($udt_total_time);
					} else {
						Debug::text('Total Time is 0!!: '. $udt_obj->getTotalTime() .' Or its an UNPAID absence: '. $udt_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				if ( isset($user_date_regular_time_compact_arr) ) {
					foreach($user_date_regular_time_compact_arr as $branch_id => $branch_arr ) {
						//foreach($branch_arr as $department_id => $total_time ) {
						foreach($branch_arr as $department_id => $department_arr ) {
							foreach($department_arr as $job_id => $job_arr ) {
								foreach($job_arr as $job_item_id => $data_arr ) {

									Debug::text('Compact Array Entry: bRegular Time - Branch ID: '. $branch_id .' Department ID: '. $department_id .' Job ID: '. $job_id .' Job Item ID: '. $job_item_id .' Total Time: '. $data_arr['total_time'] , __FILE__, __LINE__, __METHOD__, 10);

									$udtf = TTnew( 'UserDateTotalFactory' );
									$udtf->setUserDateID( $this->getUserDateID() );
									$udtf->setStatus( 10 ); //System
									$udtf->setType( 20 ); //Regular

									$udtf->setBranch( $branch_id );
									$udtf->setDepartment( $department_id );

									$udtf->setJob( $job_id );
									$udtf->setJobItem( $job_item_id );

									$udtf->setQuantity( $data_arr['quantity']  );
									$udtf->setBadQuantity( $data_arr['bad_quantity'] );

									$udtf->setTotalTime( $data_arr['total_time'] );
									$udtf->setEnableCalcSystemTotalTime(FALSE);
									$udtf->Save();
								}
							}
						}
					}
				}
				unset($user_date_regular_time_compact_arr);
			}
		}

		//Handle Premium time.
		$this->calcPremiumPolicyTotalTime( $udt_meal_policy_adjustment_arr, $udt_break_policy_adjustment_arr, $daily_total_time, $schedule_policy_ids );

		//Total Hours
		$udtf = TTnew( 'UserDateTotalFactory' );
		$udtf->setUserDateID( $this->getUserDateID() );
		$udtf->setStatus( 10 ); //System
		$udtf->setType( 10 ); //Total
		$udtf->setTotalTime( $daily_total_time );
		$udtf->setEnableCalcSystemTotalTime(FALSE);
		if ( $udtf->isValid() ) {
			$return_value = $udtf->Save();
		} else {
			$return_value = FALSE;
		}

		$profiler->stopTimer( 'UserDateTotal::calcSystemTotalTime() - Part 3');

		if ( $this->getEnableCalcException() == TRUE ) {
			ExceptionPolicyFactory::calcExceptions( $this->getUserDateID(), $this->getEnablePreMatureException() );
		}
		
		if ( isset($original_time_zone) ) {
			TTDate::setTimeZone( $original_time_zone );
		}

		return $return_value;
	}

	function calcWeeklySystemTotalTime() {
		if ( $this->getEnableCalcWeeklySystemTotalTime() == TRUE ) {
			global $profiler;

			$profiler->startTimer( 'UserDateTotal::postSave() - reCalculateRange 1');

			//Get Pay Period Schedule info
			if ( is_object($this->getUserDateObject()->getPayPeriodObject())
					AND is_object($this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
				$start_week_day_id = $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
			} else {
				$start_week_day_id = 0;
			}
			Debug::text('Start Week Day ID: '. $start_week_day_id .' Date Stamp: '. TTDate::getDate('DATE+TIME', $this->getUserDateObject()->getDateStamp()), __FILE__, __LINE__, __METHOD__, 10);

			UserDateTotalFactory::reCalculateRange( $this->getUserDateObject()->getUser(), ($this->getUserDateObject()->getDateStamp()+86400), TTDate::getEndWeekEpoch( $this->getUserDateObject()->getDateStamp(), $start_week_day_id ) );
			unset($start_week_day_id);

			$profiler->stopTimer( 'UserDateTotal::postSave() - reCalculateRange 1');
			return TRUE;
		}

		return FALSE;
	}

	function getHolidayUserDateIDs() {
		Debug::text('reCalculating Holiday...', __FILE__, __LINE__, __METHOD__, 10);

		//Get Holiday policies and determine how many days we need to look ahead/behind in order
		//to recalculate the holiday eligilibility/time.
		$holiday_before_days = 0;
		$holiday_after_days = 0;

		if ( is_object( $this->getUserDateObject() ) AND is_object( $this->getUserDateObject()->getUserObject() ) ) {
			$hplf = TTnew( 'HolidayPolicyListFactory' );
			$hplf->getByCompanyId( $this->getUserDateObject()->getUserObject()->getCompany() );
			if ( $hplf->getRecordCount() > 0 ) {
				foreach( $hplf as $hp_obj ) {
					if ( $hp_obj->getMinimumWorkedPeriodDays() > $holiday_before_days ) {
						$holiday_before_days = $hp_obj->getMinimumWorkedPeriodDays();
					}
					if ( $hp_obj->getAverageTimeDays() > $holiday_before_days ) {
						$holiday_before_days = $hp_obj->getAverageTimeDays();
					}
					if ( $hp_obj->getMinimumWorkedAfterPeriodDays() > $holiday_after_days ) {
						$holiday_after_days = $hp_obj->getMinimumWorkedAfterPeriodDays();
					}
				}
			}
		}
		Debug::text('Holiday Before Days: '. $holiday_before_days .' Holiday After Days: '. $holiday_after_days, __FILE__, __LINE__, __METHOD__, 10);

		if ( $holiday_before_days > 0 OR $holiday_after_days > 0 ) {
			$retarr = array();

			$search_start_date = TTDate::getBeginWeekEpoch( ($this->getUserDateObject()->getDateStamp()-($holiday_after_days*86400)) );
			$search_end_date = TTDate::getEndWeekEpoch( TTDate::getEndDayEpoch($this->getUserDateObject()->getDateStamp())+($holiday_before_days*86400)+3601 );
			Debug::text('Holiday search start date: '. TTDate::getDate('DATE', $search_start_date ) .' End date: '. TTDate::getDate('DATE', $search_end_date ) .' Current Date: '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);

			$hlf = TTnew( 'HolidayListFactory' );
			//$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), TTDate::getEndWeekEpoch( $this->getUserDateObject()->getDateStamp() )+86400, TTDate::getEndDayEpoch()+($max_average_time_days*86400)+3601 );
			$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), $search_start_date, $search_end_date  );
			if ( $hlf->getRecordCount() > 0 ) {
				Debug::text('Found Holidays within range: '. $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				$udlf = TTnew( 'UserDateListFactory' );
				foreach( $hlf as $h_obj ) {
					Debug::text('ReCalculating Day due to Holiday: '. TTDate::getDate('DATE', $h_obj->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);
					$user_date_ids = $udlf->getArrayByListFactory( $udlf->getByUserIdAndDate( $this->getUserDateObject()->getUser(), $h_obj->getDateStamp() ) );

					if ( $user_date_ids == FALSE AND TTDate::getBeginDayEpoch( $h_obj->getDateStamp() ) <= TTDate::getBeginDayEpoch( time() ) ) {
						//This fixes a bug where if an employee was added after a holiday (ie: Sept 3rd after Labor day of Sept 2nd)
						//then had time added before the holiday, the holiday would not be calculated as no user_date record would exist.
						$user_date_ids = (array)UserDateFactory::findOrInsertUserDate( $this->getUserDateObject()->getUser(), TTDate::getBeginDayEpoch( $h_obj->getDateStamp() ) );
						Debug::Text( 'User Date ID for holiday doesnt exist, creating it now: '. $user_date_ids[0], __FILE__, __LINE__, __METHOD__, 10);
					}

					if ( is_array( $user_date_ids ) ) {
						$retarr = array_merge( $retarr, $user_date_ids );
					}
					unset($user_date_ids);
				}
			}
		}

		if ( isset($retarr) AND is_array( $retarr ) AND count($retarr) > 0 ) {
			//Debug::Arr($retarr, 'bHoliday UserDateIDs: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		Debug::text('No Holidays within range...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	static function reCalculateDay( $user_date_id, $enable_exception = FALSE, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE, $enable_holidays = FALSE ) {
		Debug::text('Re-calculating User Date ID: '. $user_date_id .' Enable Exception: '. (int)$enable_exception, __FILE__, __LINE__, __METHOD__, 10);
		$udtf = TTnew( 'UserDateTotalFactory' );
		//Make sure we wrap this in a transaction in case its called directly and an error occurs, the first thing that happens
		//is system entries are deleted, so we need to be able to recover from that.
		$udtf->StartTransaction();
		$udtf->setUserDateId( $user_date_id );
		$udtf->calcSystemTotalTime();

		if ( $enable_holidays == TRUE ) {
			$holiday_user_date_ids = $udtf->getHolidayUserDateIDs();
			if ( is_array($holiday_user_date_ids) ) {
				foreach( $holiday_user_date_ids as $holiday_user_date_id ) {
					Debug::Text('reCalculating Holiday...', __FILE__, __LINE__, __METHOD__, 10);
					if ( $user_date_id != $holiday_user_date_id ) { //Don't recalculate the same day twice.
						UserDateTotalFactory::reCalculateDay( $holiday_user_date_id, FALSE, FALSE, FALSE, FALSE );
					}
				}
			}
			unset($holiday_user_date_ids, $holiday_user_date_id);
		}

		if ( !isset(self::$calc_exception) AND $enable_exception == TRUE ) {
			ExceptionPolicyFactory::calcExceptions( $user_date_id, $enable_premature_exceptions, $enable_future_exceptions );
		}

		$udtf->CommitTransaction();

		return TRUE;
	}

	static function reCalculateRange( $user_id, $start_date, $end_date ) {
		Debug::text('Re-calculating Range for User: '. $user_id .' Start: '. $start_date .' End: '. $end_date , __FILE__, __LINE__, __METHOD__, 10);

		$udlf = TTnew( 'UserDateListFactory' );
		$udlf->getByUserIdAndStartDateAndEndDate( $user_id, $start_date, $end_date );
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::text('Found days to re-calculate: '.$udlf->getRecordCount() , __FILE__, __LINE__, __METHOD__, 10);

			$udlf->StartTransaction();
			$x = 0;
			$x_max = $udlf->getRecordCount();
			foreach($udlf as $ud_obj ) {

				if ( $x == $x_max ) {
					//At the end of each range, make sure we calculate holidays.
					UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), FALSE, FALSE, FALSE, TRUE );
				} else {
					UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), FALSE, FALSE, FALSE, FALSE );
				}

				$x++;
			}
			$udlf->CommitTransaction();

			return TRUE;
		}

		Debug::text('DID NOT find days to re-calculate: ', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	static function smartReCalculate( $user_id, $user_date_ids, $enable_exception = TRUE, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		//Debug::Arr($user_date_ids, 'aUser Date IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( !is_array($user_date_ids) AND is_numeric($user_date_ids) AND $user_date_ids > 0 ) {
			$user_date_ids = array($user_date_ids);
		}

		if ( !is_array($user_date_ids ) ) {
			Debug::Text('Returning FALSE... User Date IDs not an array...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$user_date_ids = array_unique( $user_date_ids );
		//Debug::Arr($user_date_ids, 'bUser Date IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		$start_week_day_id = 0;
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getByUserId( $user_id );
		if ( $ppslf->getRecordCount() == 1 ) {
			$pps_obj = $ppslf->getCurrent();
			$start_week_day_id = $pps_obj->getStartWeekDay();
		}
		Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

		//Get date stamps for all user_date_ids.
		$udlf = TTnew( 'UserDateListFactory' );
		$udlf->getByIds( $user_date_ids, NULL, array('date_stamp' => 'asc') ); //Order by date asc
		if ( $udlf->getRecordCount() > 0 ) {
			//Order them, and get the one or more sets of date ranges that need to be recalculated.
			//Need to consider re-calculating multiple weeks at once.

			$i=0;
			foreach( $udlf as $ud_obj ) {
				$start_week_epoch = TTDate::getBeginWeekEpoch( $ud_obj->getDateStamp(), $start_week_day_id );
				$end_week_epoch = TTDate::getEndWeekEpoch( $ud_obj->getDateStamp(), $start_week_day_id );

				Debug::text('Current Date: '. TTDate::getDate('DATE', $ud_obj->getDateStamp() )  .' Start Week: '. TTDate::getDate('DATE', $start_week_epoch) .' End Week: '. TTDate::getDate('DATE', $end_week_epoch) , __FILE__, __LINE__, __METHOD__, 10);

				if ( $i == 0 ) {
					$range_arr[$start_week_epoch] = array('start_date' => $ud_obj->getDateStamp(), 'end_date' => $end_week_epoch );
				} else {
					//Loop through each range extending it if needed.
					foreach( $range_arr as $tmp_start_week_epoch => $tmp_range ) {
						if ( $ud_obj->getDateStamp() >= $tmp_range['start_date'] AND $ud_obj->getDateStamp() <= $tmp_range['end_date'] ) {
							//Date falls within already existing range
							continue;
						} elseif ( $ud_obj->getDateStamp() < $tmp_range['start_date'] AND $ud_obj->getDateStamp() >= $tmp_start_week_epoch) {
							//Date falls within the same week, but before the current start date.
							$range_arr[$tmp_start_week_epoch]['start_date'] = $ud_obj->getDateStamp();
							Debug::text('Pushing Start Date back...', __FILE__, __LINE__, __METHOD__, 10);
						} else {
							//Outside current range. Check to make sure it isn't within another range.
							if ( isset($range_arr[$start_week_epoch]) ) {
								//Within another existing week, check to see if we need to extend it.
								if ( $ud_obj->getDateStamp() < $range_arr[$start_week_epoch]['start_date'] ) {
									Debug::text('bPushing Start Date back...', __FILE__, __LINE__, __METHOD__, 10);
									$range_arr[$start_week_epoch]['start_date'] = $ud_obj->getDateStamp();
								}
							} else {
								//Not within another existing week
								Debug::text('Adding new range...', __FILE__, __LINE__, __METHOD__, 10);
								$range_arr[$start_week_epoch] = array('start_date' => $ud_obj->getDateStamp(), 'end_date' => $end_week_epoch );
							}
						}
					}
					unset($tmp_range, $tmp_start_week_epoch);
				}

				$i++;
			}
			unset($start_week_epoch, $end_week_epoch,  $udlf, $ud_obj);

			if ( is_array( $range_arr ) ) {
				ksort($range_arr); //Sort range by start week, so recalculating goes in date order.
				//Debug::Arr($range_arr, 'Range Array: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $range_arr as $week_range ) {
					$udlf = TTnew( 'UserDateListFactory' );
					$udlf->getByUserIdAndStartDateAndEndDate( $user_id, $week_range['start_date'], $week_range['end_date'] );
					if ( $udlf->getRecordCount() > 0 ) {
						Debug::text('Found days to re-calculate: '. $udlf->getRecordCount() , __FILE__, __LINE__, __METHOD__, 10);

						$udlf->StartTransaction();

						$z = 1;
						$z_max = $udlf->getRecordCount();
						foreach($udlf as $ud_obj ) {
							//We only need to re-calculate exceptions on the exact days specified by user_date_ids.
							//This was the case before we Over Weekly Time/Over Scheduled Weekly Time exceptions,
							//Now we have to enable calculating exceptions for the entire week.
							Debug::text('Re-calculating day with exceptions: '. $ud_obj->getId() , __FILE__, __LINE__, __METHOD__, 10);
							if ( $z == $z_max ) {
								//Enable recalculating holidays at the end of each week.
								UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), $enable_exception, $enable_premature_exceptions, $enable_future_exceptions, TRUE );
							} else {
								UserDateTotalFactory::reCalculateDay( $ud_obj->getId(), $enable_exception, $enable_premature_exceptions, $enable_future_exceptions );
							}

							$z++;
						}
						$udlf->CommitTransaction();
					}
				}

				//Use the last date to base the future week calculation on. Make sure we don't unset $week_range['end_date']
				//When BiWeekly overtime policies are calculated, it sets getEnableCalcFutureWeek() to TRUE.
				if ( isset($week_range['end_date']) AND UserDateTotalFactory::getEnableCalcFutureWeek() == TRUE ) {
					$future_week_date = $week_range['end_date']+(86400*7);
					Debug::text('Found Biweekly overtime policy, calculate one week into the future: '. TTDate::getDate('DATE', $future_week_date ), __FILE__, __LINE__, __METHOD__, 10);
					UserDateTotalFactory::reCalculateRange( $user_id, TTDate::getBeginWeekEpoch( $future_week_date, $start_week_day_id ), TTDate::getEndWeekEpoch( $future_week_date, $start_week_day_id ) );
					UserDateTotalFactory::setEnableCalcFutureWeek(FALSE); //Return to FALSE so future weeks aren't calculate for other users.
					unset($future_week_date);
				}

				return TRUE;
			}

		}

		Debug::text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function Validate() {

		//Make sure status/type combinations are correct.
		if ( !in_array($this->getType(), $this->getOptions('status_type', $this->getStatus() ) ) ) {
				Debug::text('Type doesnt match status: Type: '. $this->getType() .' Status: '. $this->getStatus() , __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTRUE(	'type',
											FALSE,
											TTi18n::gettext('Incorrect Type'));
		}

		//Check to make sure if this is an absence row, the absence policy is actually set.
		if ( $this->getStatus() == 30 AND $this->getAbsencePolicyID() == FALSE ) {
				$this->Validator->isTRUE(	'absence_policy_id',
											FALSE,
											TTi18n::gettext('Please specify an absence type'));
		}



		//Check to make sure if this is an overtime row, the overtime policy is actually set.
		if ( $this->getStatus() == 10 AND $this->getType() == 30 AND $this->getOverTimePolicyID() == FALSE ) {
				$this->Validator->isTRUE(	'over_time_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Overtime Policy'));
		}

		//Check to make sure if this is an premium row, the premium policy is actually set.
		if ( $this->getStatus() == 10 AND $this->getType() == 40 AND $this->getPremiumPolicyID() == FALSE ) {
				$this->Validator->isTRUE(	'premium_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Premium Policy'));
		}

		//Check to make sure if this is an meal row, the meal policy is actually set.
		if ( $this->getStatus() == 10 AND $this->getType() == 100 AND $this->getMealPolicyID() == FALSE ) {
				$this->Validator->isTRUE(	'meal_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Meal Policy'));
		}

		//check that the user is allowed to be assigned to the absence policy
		if ( $this->getStatus() == 30 AND $this->getAbsencePolicyID() != FALSE AND $this->getUserId() != FALSE ) {
			$cgmlf = new CompanyGenericMapListFactory();
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getUserObject()->getCompany(), 170, $this->getAbsencePolicyID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach( $cgmlf as $cgm_obj ) {
					$policy_group_ids[] = $cgm_obj->getObjectID();
				}
			}
			if ( isset( $policy_group_ids ) ) {
				$pgulf = new PolicyGroupUserListFactory();
				foreach( $policy_group_ids as $policy_group_id ) {
					$pgulf->getByPolicyGroupId( $policy_group_id );
					if ( $pgulf->getRecordCount() > 0 ) {
						foreach( $pgulf as $pgu_obj ) {
							$user_ids[] = $pgu_obj->getUser();
						}
					}
				}
			}
			if ( isset( $user_ids ) AND in_array( $this->getUserId(), $user_ids ) == FALSE ) {
				$this->Validator->isTRUE(	'absence_policy_id',
								FALSE,
								TTi18n::gettext('This absence policy is not available for this employee'));
			}
		}

		//This is likely caused by employee not being assigned to a pay period schedule?
		//Make sure to allow entries in the future (ie: absences) where no pay period exists yet.
		if ( $this->getDeleted() == FALSE AND $this->getUserDateObject() == FALSE ) {
			$this->Validator->isTRUE(	'date_stamp',
										FALSE,
										TTi18n::gettext('Date is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already') );
		} elseif ( ( $this->getOverride() == TRUE OR ( $this->getOverride() == FALSE AND $this->getStatus() == 30 ) )
					AND is_object( $this->getUserDateObject() ) AND is_object( $this->getUserDateObject()->getPayPeriodObject() ) AND $this->getUserDateObject()->getPayPeriodObject()->getIsLocked() == TRUE ) {
			//Make sure we only check for pay period being locked if override is TRUE, otherwise it can prevent recalculations from occurring
			//after the pay period is locked (ie: recalculating exceptions each day from maintenance jobs?)
			//We need to be able to stop absences (non-overridden ones too) from being deleted in closed pay periods.
			$this->Validator->isTRUE(	'date_stamp',
										FALSE,
										TTi18n::gettext('Pay Period is Currently Locked') );
		}

		//Make sure that we aren't trying to overwrite an already overridden entry made by the user for some special purpose.
		if ( $this->getDeleted() == FALSE
				AND $this->isNew() == TRUE
				AND in_array( $this->getStatus(), array(10,20,30) ) ) {

			Debug::text('Checking over already existing overridden entries ... User Date ID: '. $this->getUserDateID() .' Status ID: '. $this->getStatus() .' Type ID: '. $this->getType(), __FILE__, __LINE__, __METHOD__, 10);

			$udtlf = TTnew( 'UserDateTotalListFactory' );

			if ( $this->getStatus() == 20 AND $this->getPunchControlID() > 0 ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndPunchControlIdAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPunchControlID(), TRUE );
			} elseif ( $this->getStatus() == 30 ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndAbsencePolicyIDAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getAbsencePolicyID(), TRUE );
			} elseif ( $this->getStatus() == 10 AND $this->getType() == 30 ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndOvertimePolicyIDAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getOverTimePolicyID(), TRUE );
			} elseif ( $this->getStatus() == 10 AND $this->getType() == 40 ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndPremiumPolicyIDAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPremiumPolicyID(), TRUE );
			} elseif ( $this->getStatus() == 10 AND $this->getType() == 100 ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndMealPolicyIDAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getMealPolicyID(), TRUE );
			} elseif ( $this->getStatus() == 10 AND ( $this->getType() == 10 OR ( $this->getType() == 20 AND $this->getPunchControlID() > 0 ) ) ) {
				$udtlf->getByUserDateIdAndStatusAndTypeAndPunchControlIdAndOverride( $this->getUserDateID(), $this->getStatus(), $this->getType(), $this->getPunchControlID(), TRUE );
			}

			Debug::text('Record Count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text('Found an overridden row... NOT SAVING: '. $udtlf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTRUE(	'absence_policy_id',
											FALSE,
											TTi18n::gettext('Similar entry already exists, not overriding'));
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getPunchControlID() === FALSE ) {
			$this->setPunchControlID(0);
		}

		if ( $this->getOverTimePolicyID() === FALSE ) {
			$this->setOverTimePolicyId(0);
		}

		if ( $this->getAbsencePolicyID() === FALSE ) {
			$this->setAbsencePolicyID(0);
		}

		if ( $this->getPremiumPolicyID() === FALSE ) {
			$this->setPremiumPolicyId(0);
		}

		if ( $this->getMealPolicyID() === FALSE ) {
			$this->setMealPolicyId(0);
		}

		if ( $this->getBranch() === FALSE ) {
			$this->setBranch(0);
		}

		if ( $this->getDepartment() === FALSE ) {
			$this->setDepartment(0);
		}

		if ( $this->getJob() === FALSE ) {
			$this->setJob(0);
		}

		if ( $this->getJobItem() === FALSE ) {
			$this->setJobItem(0);
		}

		if ( $this->getQuantity() === FALSE ) {
			$this->setQuantity(0);
		}

		if ( $this->getBadQuantity() === FALSE ) {
			$this->setBadQuantity(0);
		}

		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if timesheet is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( is_object( $this->getUserDateObject() )
					AND is_object( $this->getUserDateObject()->getPayPeriodObject() )
					AND is_object( $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject() )
					AND $this->getUserDateObject()->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getUserDateObject()->getPayPeriod(), $this->getUserDateObject()->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text('Pay Period is verified, deleting verification records: '. $pptsvlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
					foreach( $pptsvlf as $pptsv_obj ) {
						if ( is_object( $this->getAbsencePolicyObject() ) ) {
							TTLog::addEntry( $pptsv_obj->getId(), 500,  TTi18n::getText('TimeSheet Modified After Verification').': '. UserListFactory::getFullNameById( $this->getUserDateObject()->getUser() ) .' '. TTi18n::getText('Absence').': '. $this->getAbsencePolicyObject()->getName() .' - '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) , NULL, $pptsvlf->getTable() );
						}
						$pptsv_obj->setDeleted( TRUE );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		return TRUE;
	}

	function postSave() {
		if ( $this->getEnableCalcSystemTotalTime() == TRUE ) {
			Debug::text('Calc System Total Time Enabled: ', __FILE__, __LINE__, __METHOD__, 10);
			$this->calcSystemTotalTime();
		} else {
			Debug::text('Calc System Total Time Disabled: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $this->getDeleted() == FALSE ) {
			//Handle accruals here, instead of in calcSystemTime as that is too early in the process and user_date_total ID's don't exist yet.
			$this->calcAccrualPolicy();

			//AccrualFactory::deleteOrphans( $this->getUserDateObject()->getUser() );
			AccrualFactory::deleteOrphans( $this->getUserDateObject()->getUser(), $this->getUserDateObject()->getDateStamp() );
		}

		return TRUE;
	}

	//Takes UserDateTotal rows, and calculate the accumlated time sections
	static function calcAccumulatedTime( $data ) {
		if ( is_array($data) and count($data) > 0 ) {
			//Keep track of item ids for each section type so we can decide later on if we can eliminate unneeded data.
			$section_ids = array( 'branch' => array(), 'department' => array(), 'job' => array(), 'job_item' => array() );

			//Sort data by date_stamp at the top, so it works for multiple days at a time.
			//Keep a running total of all days, mainly for 'weekly total" purposes.
			foreach ( $data as $key => $row ) {
				//Skip rows with a 0 total_time.
				if ( $row['total_time'] == 0 ) {
					continue;
				}
				$combined_type_id_status_id = $row['type_id'].$row['status_id'];

				switch ( $combined_type_id_status_id ) {
					//Section: Accumulated Time:
					//  Includes: Total Time, Regular Time, Overtime, Meal Policy Time, Break Policy Time.
					case 1010: //Type_ID= 10, Status_ID= 10 - Total Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['total']) ) {
							$retval[$row['date_stamp']]['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['accumulated_time']['total']) ) {
							$retval['total']['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['accumulated_time']['total']['total_time'] += $row['total_time'];
						break;
					case 2010: //Type_ID= 20, Status_ID= 10 - Regular Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['regular']) ) {
							$retval[$row['date_stamp']]['accumulated_time']['regular'] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['regular']['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['accumulated_time']['regular']) ) {
							$retval['total']['accumulated_time']['regular'] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['accumulated_time']['regular']['total_time'] += $row['total_time'];
						break;
					case 3010: //Type_ID= 30, Status_ID= 10 - Over Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['over_time_policy_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['over_time_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['over_time_policy_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['accumulated_time']['over_time_'.$row['over_time_policy_id']]) ) {
							$retval['total']['accumulated_time']['over_time_'.$row['over_time_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['accumulated_time']['over_time_'.$row['over_time_policy_id']]['total_time'] += $row['total_time'];
						break;

					case 10010: //Type_ID= 100, Status_ID= 10 - Meal Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['meal_policy_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['meal_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['meal_policy_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['accumulated_time']['meal_time_'.$row['meal_policy_id']]) ) {
							$retval['total']['accumulated_time']['meal_time_'.$row['meal_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['accumulated_time']['meal_time_'.$row['meal_policy_id']]['total_time'] += $row['total_time'];
						break;
					case 11010: //Type_ID= 110, Status_ID= 10 - Break Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['break_policy_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['break_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['break_policy_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['accumulated_time']['break_time_'.$row['break_policy_id']]) ) {
							$retval['total']['accumulated_time']['break_time_'.$row['break_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['accumulated_time']['break_time_'.$row['break_policy_id']]['total_time'] += $row['total_time'];
						break;

					//Section: Premium Time:
					//  Includes: All Premium Time
					case 4010: //Type_ID= 40, Status_ID= 10 - Premium Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['premium_time']['premium_'.$row['premium_policy_id']]) ) {
							$retval[$row['date_stamp']]['premium_time']['premium_'.$row['premium_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['premium_time']['premium_'.$row['premium_policy_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['premium_time']['premium_'.$row['premium_policy_id']]) ) {
							$retval['total']['premium_time']['premium_'.$row['premium_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['premium_time']['premium_'.$row['premium_policy_id']]['total_time'] += $row['total_time'];
						break;

					//Section: Absence Time:
					//  Includes: All Absence Time
					case 1030: //Type_ID= 10, Status_ID= 30 - Absence Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['absence_time']['absence_'.$row['absence_policy_id']]) ) {
							$retval[$row['date_stamp']]['absence_time']['absence_'.$row['absence_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['absence_time']['absence_'.$row['absence_policy_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['absence_time']['absence_'.$row['absence_policy_id']]) ) {
							$retval['total']['absence_time']['absence_'.$row['absence_policy_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval['total']['absence_time']['absence_'.$row['absence_policy_id']]['total_time'] += $row['total_time'];
						break;
				}

				//Section: Accumulated Time by Branch,Department,Job,Task
				if ( in_array( $row['type_id'], array(20,30) ) AND in_array( $row['status_id'], array(10) ) ) {
					//Branch
					$branch_name = $row['branch'];
					if ( $branch_name == '' ) {
						$branch_name = TTi18n::gettext('No Branch');
					}
					if ( !isset($retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']]) ) {
						$retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']] = array('label' => $branch_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']]['total_time'] += $row['total_time'];
					$section_ids['branch'][] = (int)$row['branch_id'];

					//Department
					$department_name = $row['department'];
					if ( $department_name == '' ) {
						$department_name = TTi18n::gettext('No Department');
					}
					if ( !isset($retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']]) ) {
						$retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']] = array('label' => $department_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']]['total_time'] += $row['total_time'];
					$section_ids['department'][] = (int)$row['department_id'];

					//Job
					$job_name = $row['job'];
					if ( $job_name == '' ) {
						$job_name = TTi18n::gettext('No Job');
					}
					if ( !isset($retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']]) ) {
						$retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']] = array('label' => $job_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']]['total_time'] += $row['total_time'];
					$section_ids['job'][] = (int)$row['job_id'];

					//Job Item/Task
					$job_item_name = $row['job_item'];
					if ( $job_item_name == '' ) {
						$job_item_name = TTi18n::gettext('No Task');
					}
					if ( !isset($retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']]) ) {
						$retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']] = array('label' => $job_item_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']]['total_time'] += $row['total_time'];
					$section_ids['job_item'][] = (int)$row['job_item_id'];

					//Debug::text('ID: '. $row['id'] .' User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Branch: '. $branch_name .' Job: '. $job_name, __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( isset($retval) ) {
				//Remove any unneeded data, such as "No Branch" for all dates in the range
				foreach( $section_ids as $section => $ids ) {
					$ids = array_unique($ids);
					sort($ids);
					if ( isset($ids[0]) AND $ids[0] == 0 AND count($ids) == 1 ) {
						foreach( $retval as $date_stamp => $day_data ) {
							unset($retval[$date_stamp][$section.'_time']);
						}
					}
				}

				return $retval;
			}
		}

		return FALSE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			//We need to set the UserDate as soon as possible.
			if ( isset($data['user_id']) AND $data['user_id'] != ''
					AND isset($data['date_stamp']) AND $data['date_stamp'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__,10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__,10);
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'user_date_id': //Ignore user_date_id, as we already set it above.
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'pay_period_id':
						case 'branch':
						case 'department':
						case 'over_time_policy':
						case 'absence_policy':
						case 'absence_policy_type_id':
						case 'premium_policy':
						case 'meal_policy':
						case 'break_policy':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'date_stamp' ) ) );
							break;
						case 'start_time_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() ); //Include both date+time
							break;
						case 'end_time_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() ); //Include both date+time
							break;
						case 'name':
							$data[$variable] = $this->getName();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( $this->getOverride() == TRUE AND $this->getStatus() == 30 AND is_object( $this->getUserDateObject() ) ) { //Absence
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Absence') .' - '. TTi18n::getText('Date') .': '. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ). ' '. TTi18n::getText('Total Time') .': '. TTDate::getTimeUnit( $this->getTotalTime() ), NULL, $this->getTable(), $this );
		}
	}
}
?>
