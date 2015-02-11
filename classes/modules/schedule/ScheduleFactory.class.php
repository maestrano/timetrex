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
 * @package Modules\Schedule
 */
class ScheduleFactory extends Factory {
	protected $table = 'schedule';
	protected $pk_sequence_name = 'schedule_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $schedule_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $branch_obj = NULL;
	protected $department_obj = NULL;
	protected $pay_period_schedule_obj = NULL;

	function _getFactoryOptions( $name ) {

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(

										//10  => TTi18n::gettext('OPEN'), //Available to be covered/overridden.
										//20 => TTi18n::gettext('Manual'),
										//30 => TTi18n::gettext('Recurring')
										//90  => TTi18n::gettext('Replaced'), //Replaced by another shift. Set replaced_id

										//Not displayed on schedules, used to overwrite recurring schedule if we want to change a 8AM-5PM recurring schedule
										//with a 6PM-11PM schedule? Although this can be done with an absence shift as well...
										//100 => TTi18n::gettext('Hidden'),
									);
				break;
			case 'status':
				$retval = array(
										//If user_id = 0 then the schedule is assumed to be open. That way its easy to assign recurring schedules
										//to user_id=0 for open shifts too.
										10 => TTi18n::gettext('Working'),
										20 => TTi18n::gettext('Absent'),
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
										'-1200-status' => TTi18n::gettext('Status'),
										'-1210-schedule_policy' => TTi18n::gettext('Schedule Policy'),
										'-1212-absence_policy' => TTi18n::gettext('Absence Policy'),
										'-1215-date_stamp' => TTi18n::gettext('Date'),
										'-1220-start_time' => TTi18n::gettext('Start Time'),
										'-1230-end_time' => TTi18n::gettext('End Time'),
										'-1240-total_time' => TTi18n::gettext('Total Time'),
										'-1250-note' => TTi18n::gettext('Note'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval['-1180-job'] = TTi18n::gettext('Job');
					$retval['-1190-job_item'] = TTi18n::gettext('Task');
				}
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'status',
								'date_stamp',
								'start_time',
								'end_time',
								'total_time',
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
			case 'group_columns': //Columns available for grouping on the schedule.
				$retval = array(
								'title',
								'group',
								'default_branch',
								'default_department',
								'branch',
								'department',
								);

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval[] = 'job';
					$retval[] = 'job_item';

				}
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'user_id' => 'User',
										'date_stamp' => 'DateStamp',
										'pay_period_id' => 'PayPeriod',

										//'user_id' => FALSE,
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

										//'date_stamp' => FALSE,
										'start_date_stamp' => FALSE,
										'pay_period_id' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'start_date' => FALSE,
										'end_date' => FALSE,
										'start_time_stamp' => FALSE,
										'end_time_stamp' => FALSE,
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'schedule_policy_id' => 'SchedulePolicyID',
										'schedule_policy' => FALSE,
										'absence_policy_id' => 'AbsencePolicyID',
										'absence_policy' => FALSE,
										'branch_id' => 'Branch',
										'branch' => FALSE,
										'department_id' => 'Department',
										'department' => FALSE,
										'job_id' => 'Job',
										'job' => FALSE,
										'job_item_id' => 'JobItem',
										'job_item' => FALSE,
										'total_time' => 'TotalTime',

										'note' => 'Note',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	function getSchedulePolicyObject() {
		return $this->getGenericObject( 'SchedulePolicyListFactory', $this->getSchedulePolicyID(), 'schedule_policy_obj' );
	}

	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	function getBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getBranch(), 'branch_obj' );
	}

	function getDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDepartment(), 'department_obj' );
	}

	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if ( $this->getUser() > 0 ) {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			} elseif ( $this->getUser() == 0 AND $this->getCompany() > 0 ) {
				//OPEN SHIFT, try to find pay period schedule for the company
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$ppslf->getByCompanyId( $this->getCompany() );
				if ( $ppslf->getRecordCount() == 1 ) {
					Debug::Text('Using Company ID: '. $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10);
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			}

			return FALSE;
		}
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

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriod() {
		if ( isset($this->data['pay_period_id']) ) {
			return (int)$this->data['pay_period_id'];
		}

		return FALSE;
	}
	function setPayPeriod($id = NULL) {
		$id = trim($id);

		if ( $id == NULL AND $this->getUser() > 0 ) { //Don't attempt to find pay period if user_id is not specified.
			$id = (int)PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}
		
		$pplf = TTnew( 'PayPeriodListFactory' );

		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Pay Period')
														) ) {
			$this->data['pay_period_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = (int)$epoch;
		if ( $epoch > 0 ) {
			$epoch = TTDate::getMiddleDayEpoch( $epoch );
		}

		if	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date').'(a)')
			) {

			if	( $epoch > 0 ) {
				if ( $this->getDateStamp() !== $epoch AND $this->getOldDateStamp() != $this->getDateStamp() ) {
					Debug::Text(' Setting Old DateStamp... Current Old DateStamp: '. (int)$this->getOldDateStamp() .' Current DateStamp: '. (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
					$this->setOldDateStamp( $this->getDateStamp() );
				}

				//Debug::Text(' Setting DateStamp to: '. (int)$epoch, __FILE__, __LINE__, __METHOD__, 10);
				$this->data['date_stamp'] = $epoch;

				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date').'(b)');
			}
		}

		return FALSE;
	}

	//
	//FIXME: The problem with assigning schedules to other dates than what they start on, is that employees can get confused
	//		 as to what day their shift actually starts on, especially when looking at iCal schedules, or printed schedules.
	//		 It can even be different for some employees if they are assigned to other pay period schedules.
	//		 However its likely they may already know this anyways, due to internal termination, if they call a Monday shift one that starts Sunday night for example.
	function findUserDate() {
		//Must allow user_id=0 for open shifts.

		/*
		This needs to be able to run before Validate is called, so we can validate the pay period schedule.
		*/
		if ( $this->getDateStamp() == FALSE ) {
			$this->setDateStamp( $this->getStartTime() );
		}

		//Debug::Text(' Finding User Date ID: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' User: '. $this->getUser(), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			$user_date_epoch = $this->getPayPeriodScheduleObject()->getShiftAssignedDate( $this->getStartTime(), $this->getEndTime(), $this->getPayPeriodScheduleObject()->getShiftAssignedDay() );
		} else {
			$user_date_epoch = $this->getStartTime();
		}

		if ( isset($user_date_epoch) AND $user_date_epoch > 0 ) {
			//Debug::Text('Found DateStamp: '. $user_date_epoch .' Based On: '. TTDate::getDate('DATE+TIME', $user_date_epoch ), __FILE__, __LINE__, __METHOD__, 10);

			return $this->setDateStamp( $user_date_epoch );
		}

		Debug::Text('Not using timestamp only: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	function getOldDateStamp() {
		if ( isset($this->tmp_data['old_date_stamp']) ) {
			return $this->tmp_data['old_date_stamp'];
		}

		return FALSE;
	}
	function setOldDateStamp($date_stamp) {
		Debug::Text(' Setting Old DateStamp: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
		$this->tmp_data['old_date_stamp'] = TTDate::getMiddleDayEpoch( $date_stamp );

		return TRUE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = (int)$status;

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getStartTime( $raw = FALSE ) {
		if ( isset($this->data['start_time']) ) {
			return TTDate::strtotime( $this->data['start_time'] );
		}

		return FALSE;
	}
	function setStartTime($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'start_time',
												$epoch,
												TTi18n::gettext('Incorrect start time'))
			) {

			$this->data['start_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndTime( $raw = FALSE ) {
		if ( isset($this->data['end_time']) ) {
			return TTDate::strtotime( $this->data['end_time'] );
		}

		return FALSE;
	}
	function setEndTime($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'end_time',
												$epoch,
												TTi18n::gettext('Incorrect end time'))
			) {

			$this->data['end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getMealPolicyDeductTime( $day_total_time, $filter_type_id = FALSE ) {
		$total_time = 0;

		$mplf = TTnew( 'MealPolicyListFactory' );
		if ( is_object( $this->getSchedulePolicyObject() ) AND $this->getSchedulePolicyObject()->isUsePolicyGroupMealPolicy() == FALSE ) {
			$policy_group_meal_policy_ids = $this->getSchedulePolicyObject()->getMealPolicy();
			$mplf->getByIdAndCompanyId( $policy_group_meal_policy_ids, $this->getCompany() );
		} else {
			$mplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Meal Policy Record Count: '. $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $mplf->getRecordCount() > 0 ) {
			foreach( $mplf as $meal_policy_obj ) {
				if ( 		( $filter_type_id == FALSE AND ( $meal_policy_obj->getType() == 10 OR $meal_policy_obj->getType() == 20 ) )
							OR
							( $filter_type_id == $meal_policy_obj->getType() )
					) {
					if ( $day_total_time > $meal_policy_obj->getTriggerTime() ) {
						$total_time = $meal_policy_obj->getAmount(); //Only consider a single meal policy per shift, so don't add here.
					}
				}

			}
		}

		$total_time = ($total_time * -1);
		Debug::Text('Meal Policy Deduct Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $total_time;
	}
	function getBreakPolicyDeductTime( $day_total_time, $filter_type_id = FALSE ) {
		$total_time = 0;

		$bplf = TTnew( 'BreakPolicyListFactory' );
		if ( is_object( $this->getSchedulePolicyObject() ) AND $this->getSchedulePolicyObject()->isUsePolicyGroupBreakPolicy() == FALSE ) {
			$policy_group_break_policy_ids = $this->getSchedulePolicyObject()->getBreakPolicy();
			$bplf->getByIdAndCompanyId( $policy_group_break_policy_ids, $this->getCompany() );
		} else {
			$bplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Break Policy Record Count: '. $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $bplf->getRecordCount() > 0 ) {
			foreach( $bplf as $break_policy_obj ) {
				if ( 	( $filter_type_id == FALSE AND ( $break_policy_obj->getType() == 10 OR $break_policy_obj->getType() == 20 ) )
						OR
						( $filter_type_id == $break_policy_obj->getType() )
					) {
					if ( $day_total_time > $break_policy_obj->getTriggerTime() ) {
						$total_time += $break_policy_obj->getAmount();
					}
				}
			}
		}

		$total_time = ($total_time * -1);
		Debug::Text('Break Policy Deduct Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $total_time;
	}
	
	function calcRawTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			$total_time = ( $this->getEndTime() - $this->getStartTime() ); // + TTDate::getDSTOffset( $this->getStartTime(), $this->getEndTime() );
			//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().') Total Time: '. TTDate::getHours( $total_time ), __FILE__, __LINE__, __METHOD__, 10);

			return $total_time;
		}

		return FALSE;
	}
	function calcTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			$total_time = $this->calcRawTotalTime();
			
			$total_time += $this->getMealPolicyDeductTime( $total_time );
			$total_time += $this->getBreakPolicyDeductTime( $total_time );

			return $total_time;
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

		if	(	$this->Validator->isNumeric(		'total_time',
													$int,
													TTi18n::gettext('Incorrect total time')) ) {
			$this->data['total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}


	function getSchedulePolicyID() {
		if ( isset($this->data['schedule_policy_id']) ) {
			return (int)$this->data['schedule_policy_id'];
		}

		return FALSE;
	}
	function setSchedulePolicyID($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$splf = TTnew( 'SchedulePolicyListFactory' );

		if ( 	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'schedule_policy',
														$splf->getByID($id),
														TTi18n::gettext('Schedule Policy is invalid')
													) ) {

			$this->data['schedule_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return (int)$this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy',
														$aplf->getByID($id),
														TTi18n::gettext('Invalid Absence Policy')
														) ) {
			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return (int)$this->data['branch_id'];
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
				$this->Validator->isResultSetWithRows(	'branch',
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
			return (int)$this->data['department_id'];
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
				$this->Validator->isResultSetWithRows(	'department',
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
			return (int)$this->data['job_id'];
		}

		return FALSE;
	}
	function setJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == -1 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job',
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
			return (int)$this->data['job_item_id'];
		}

		return FALSE;
	}
	function setJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == -1 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item',
														$jilf->getByID($id),
														TTi18n::gettext('Task does not exist')
														) ) {
			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
	function setNote($val) {
		$val = trim($val);

		if	(	$val == ''
				OR
				$this->Validator->isLength(		'note',
												$val,
												TTi18n::gettext('Note is too short or too long'),
												0,
												1024) ) {

			$this->data['note'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	//Find the difference between $epoch and the schedule time, so we can determine the best schedule that fits.
	//**This returns FALSE when it doesn't match, so make sure you do an exact comparison using ===
	function inScheduleDifference( $epoch, $status_id = FALSE ) {
		$retval = FALSE;
		if ( $epoch >= $this->getStartTime() AND $epoch <= $this->getEndTime() ) {
			Debug::text('aWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			$retval = 0; //Within schedule start/end time, no difference.
		} else	{
			if ( ( $status_id == FALSE OR $status_id == 10 ) AND $epoch < $this->getStartTime() AND $this->inStartWindow( $epoch ) ) {
				$retval = ($this->getStartTime() - $epoch);
			} elseif ( ( $status_id == FALSE OR $status_id == 20 ) AND $epoch > $this->getEndTime() AND $this->inStopWindow( $epoch ) ) {
				$retval = ($epoch - $this->getEndTime());
			} else {
				$retval = FALSE; //Not within start/stop window at all, return FALSE.
			}
		}

		Debug::text('Difference from schedule: "'. $retval .'" Epoch: '. $epoch .' Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function inSchedule( $epoch ) {
		if ( $epoch >= $this->getStartTime() AND $epoch <= $this->getEndTime() ) {
			Debug::text('aWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		} elseif ( $this->inStartWindow( $epoch ) OR $this->inStopWindow( $epoch ) )  {
			Debug::text('bWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		return FALSE;
	}

	function inStartWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr to help avoid In Late exceptions when they come in too early.
		}

		if ( $epoch >= ( $this->getStartTime() - $start_stop_window ) AND $epoch <= ( $this->getStartTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Start window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function inStopWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ) ; //Default to 2hr
		}

		if ( $epoch >= ( $this->getEndTime() - $start_stop_window ) AND $epoch <= ( $this->getEndTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Stop window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function mergeScheduleArray($schedule_shifts, $recurring_schedule_shifts) {
		//Debug::text('Merging Schedule, and Recurring Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		$ret_arr = $schedule_shifts;

		//Debug::Arr($schedule_shifts, '(c) Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($recurring_schedule_shifts) AND count($recurring_schedule_shifts) > 0 ) {
			foreach( $recurring_schedule_shifts as $date_stamp => $day_shifts_arr ) {
				//Debug::text('----------------------------------', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp). ' Epoch: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($schedule_shifts[$date_stamp], 'Date Arr: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $day_shifts_arr as $key => $shift_arr ) {

					if ( isset($ret_arr[$date_stamp]) ) {
						//Debug::text('Already Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);

						//Loop through each shift on this day, and check for overlaps
						//Only include the recurring shift if ALL times DO NOT overlap
						$overlap = 0;
						foreach( $ret_arr[$date_stamp] as $tmp_shift_arr ) {
							if ( TTDate::isTimeOverLap( $shift_arr['start_time'], $shift_arr['end_time'], $tmp_shift_arr['start_time'], $tmp_shift_arr['end_time']) ) {
								//Debug::text('Times OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
								$overlap++;
							} //else { //Debug::text('Times DO NOT OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( $overlap == 0 ) {
							//Debug::text('NO Times OverLap, using recurring schedule: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
							$ret_arr[$date_stamp][] = $shift_arr;
						}
					} else {
						//Debug::text('No Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
						$ret_arr[$date_stamp][] = $shift_arr;
					}
				}
			}
		}

		return $ret_arr;
	}

	function getScheduleArray( $filter_data, $permission_children_ids = NULL ) {
		global $current_user, $current_user_prefs;

		//Get all schedule data by general filter criteria.
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !isset($filter_data['start_date']) OR $filter_data['start_date'] == '' ) {
			return FALSE;
		}

		if ( !isset($filter_data['end_date']) OR $filter_data['end_date'] == '' ) {
			return FALSE;
		}

		$filter_data['start_date'] = TTDate::getBeginDayEpoch( $filter_data['start_date'] );
		$filter_data['end_date'] = TTDate::getEndDayEpoch( $filter_data['end_date'] );

		$schedule_shifts_index = array();
		$branch_options = array(); //No longer needed, use SQL instead.
		$department_options = array(); //No longer needed, use SQL instead.

		$pcf = TTnew( 'PayCodeFactory' );
		$absence_policy_paid_type_options = $pcf->getOptions('paid_type');

		$max_i = 0;

		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
		Debug::text('Found Scheduled Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($absence_policy_paid_type_options, 'Paid Absences: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $slf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $slf->getRecordCount(), NULL, TTi18n::getText('Processing Committed Shifts...') );

			$i = 0;
			foreach( $slf as $s_obj ) {
				if ( (int)$s_obj->getUser() == 0 AND ( getTTProductEdition() == TT_PRODUCT_COMMUNITY OR $current_user->getCompanyObject()->getProductEdition() == 10 ) ) { continue; }

				//Debug::text('Schedule ID: '. $s_obj->getId() .' User ID: '. $s_obj->getUser() .' Start Time: '. $s_obj->getStartTime(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $s_obj->getAbsencePolicyID() > 0 ) {
					$absence_policy_name = $s_obj->getColumn('absence_policy');
				} else {
					$absence_policy_name = NULL; //Must be NULL for it to appear as "N/A" in legacy interface.
				}

				$hourly_rate = Misc::MoneyFormat( $s_obj->getColumn('user_wage_hourly_rate'), FALSE );

				if ( $s_obj->getAbsencePolicyID() > 0
						AND is_object( $s_obj->getAbsencePolicyObject() )
						AND is_object( $s_obj->getAbsencePolicyObject()->getPayCodeObject() )
						AND in_array( $s_obj->getAbsencePolicyObject()->getPayCodeObject()->getType(), $absence_policy_paid_type_options ) == FALSE ) {
					//UnPaid Absence.
					$total_time_wage = Misc::MoneyFormat(0);
				} else {
					$total_time_wage = Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), $hourly_rate ), FALSE );
				}

				//$iso_date_stamp = TTDate::getISODateStamp($s_obj->getStartTime());
				$iso_date_stamp = TTDate::getISODateStamp( $s_obj->getDateStamp() );

				//$schedule_shifts[$iso_date_stamp][$s_obj->getUser().$s_obj->getStartTime()] = array(
				$schedule_shifts[$iso_date_stamp][$i] = array(
													'id' => (int)$s_obj->getID(),
													'pay_period_id' => (int)$s_obj->getColumn('pay_period_id'),
													'user_id' => (int)$s_obj->getUser(),
													'user_created_by' => (int)$s_obj->getColumn('user_created_by'),
													'user_full_name' => ( $s_obj->getUser() > 0 ) ? Misc::getFullName( $s_obj->getColumn('first_name'), NULL, $s_obj->getColumn('last_name'), FALSE, FALSE ) : TTi18n::getText('OPEN'),
													'first_name' => ( $s_obj->getUser() > 0 ) ? $s_obj->getColumn('first_name') : TTi18n::getText('OPEN'),
													'last_name' => $s_obj->getColumn('last_name'),
													'title_id' => (int)$s_obj->getColumn('title_id'),
													'title' => $s_obj->getColumn('title'),
													'group_id' => (int)$s_obj->getColumn('group_id'),
													'group' => $s_obj->getColumn('group'),
													'default_branch_id' => (int)$s_obj->getColumn('default_branch_id'),
													'default_branch' => $s_obj->getColumn('default_branch'),
													'default_department_id' => (int)$s_obj->getColumn('default_department_id'),
													'default_department' => $s_obj->getColumn('default_department'),

													'job_id' => (int)$s_obj->getColumn('job_id'),
													'job' => $s_obj->getColumn('job'),
													'job_status_id' => (int)$s_obj->getColumn('job_status_id'),
													'job_manual_id' => (int)$s_obj->getColumn('job_manual_id'),
													'job_branch_id' => (int)$s_obj->getColumn('job_branch_id'),
													'job_department_id' => (int)$s_obj->getColumn('job_department_id'),
													'job_group_id' => (int)$s_obj->getColumn('job_group_id'),
													'job_item_id' => (int)$s_obj->getColumn('job_item_id'),
													'job_item' => $s_obj->getColumn('job_item'),

													'type_id' => 10, //Committed
													'status_id' => (int)$s_obj->getStatus(),

													'date_stamp' => TTDate::getAPIDate( 'DATE', $s_obj->getDateStamp() ), //Date the schedule is displayed on
													'start_date_stamp' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE', $s_obj->getStartTime() ) : $s_obj->getStartTime(), //Date the schedule starts on.
													'start_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $s_obj->getStartTime() ) : $s_obj->getStartTime(),
													'end_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $s_obj->getEndTime() ) : $s_obj->getEndTime(),
													'start_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $s_obj->getStartTime() ) : $s_obj->getStartTime(),
													'end_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $s_obj->getEndTime() ) : $s_obj->getEndTime(),
													
													'start_time_stamp' => $s_obj->getStartTime(),
													'end_time_stamp' => $s_obj->getEndTime(),

													'total_time' => $s_obj->getTotalTime(),

													'hourly_rate' => $hourly_rate,
													'total_time_wage' => $total_time_wage,

													'note' => $s_obj->getColumn('note'),

													'schedule_policy_id' => (int)$s_obj->getSchedulePolicyID(),
													'absence_policy_id' => (int)$s_obj->getAbsencePolicyID(),
													'absence_policy' => $absence_policy_name,
													'branch_id' => (int)$s_obj->getBranch(),
													'branch' => $s_obj->getColumn('branch'),
													'department_id' => (int)$s_obj->getDepartment(),
													'department' => $s_obj->getColumn('department'),

													'created_by_id' => (int)$s_obj->getCreatedBy(),
													'created_date' => $s_obj->getCreatedDate(),
													'updated_date' => $s_obj->getUpdatedDate(),
												);

				//Make sure we add in permission columns.
				$this->getPermissionColumns( $schedule_shifts[$iso_date_stamp][$i], (int)$s_obj->getUser(), $s_obj->getCreatedBy(), $permission_children_ids );

				//$schedule_shifts_index[$iso_date_stamp][$s_obj->getUser()][] = $s_obj->getUser().$s_obj->getStartTime();
				$schedule_shifts_index[$iso_date_stamp][$s_obj->getUser()][] = $i;
				unset($absence_policy_name);

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $slf->getCurrentRow() );

				$i++;
			}
			$max_i = $i;
			unset($i);

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//Debug::Arr($schedule_shifts, 'Committed Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($schedule_shifts_index, 'Committed Schedule Shifts Index: ', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$schedule_shifts = array();
		}
		unset($slf);

		//Get holidays
		//Make sure holiday policies are segragated by policy_group_id, otherwise all policies apply to all employees.
		$holiday_data = array();
		$hlf = TTnew( 'HolidayListFactory' );
		$hlf->getByCompanyIdAndStartDateAndEndDate( $current_user->getCompany(), $filter_data['start_date'], $filter_data['end_date'] );
		Debug::text('Found Holiday Rows: '. $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach( $hlf as $h_obj ) {
			//If there are conflicting holidays, one being absent and another being working, don't override the working one.
			//That way we default to working just in case. 
			if ( !isset($holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())])
				AND is_object( $h_obj->getHolidayPolicyObject() )
				AND is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject() )
				AND is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject() ) ) {
				$holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus(), 'absence_policy_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyID(), 'type_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject()->getType(), 'absence_policy' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getName() );
			} else {
				$holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => 10 ); //Working
			}
		}
		unset($hlf);

		$recurring_schedule_shifts = array();
		$open_shift_conflict_index = array();

		$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' );
		//Order for this is critcal to working with OPEN shifts. OPEN shifts (user_id=0) must come last, so it can find all conflicting shifts that will override it.
		//Also order by start_time so earlier shifts come first and therefore are the first to be overridden.
		$rstlf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data, NULL, NULL, NULL, array( 'c.start_date' => 'asc', 'cb.user_id' => 'desc', 'a.week' => 'asc', 'a.start_time' => 'asc' ) );
		Debug::text('Found Recurring Schedule Template Rows: '. $rstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $rstlf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $rstlf->getRecordCount(), NULL, TTi18n::getText('Processing Recurring Shifts...') );

			foreach( $rstlf as $rst_obj ) {
				//Debug::text('Recurring Schedule Template ID: '. $rst_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$rst_obj->getShifts( $filter_data['start_date'], $filter_data['end_date'], $holiday_data, $branch_options, $department_options, $max_i, $schedule_shifts, $schedule_shifts_index, $open_shift_conflict_index, $permission_children_ids );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $rstlf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
		} else {
			Debug::text('DID NOT find Recurring Schedule for this time period: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset($rstlf, $rst_obj, $open_shift_conflict_index);
		//Debug::Arr($schedule_shifts, 'Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		//Include employees without scheduled shifts.
		if ( isset($filter_data['include_all_users']) AND $filter_data['include_all_users'] == TRUE ) {
			if ( !isset($filter_data['exclude_id']) ) {
				$filter_data['exclude_id'] = array();
			}

			//If the user is searching for scheduled branch/departments, convert that to default branch/departments when Show All Employees is enabled.
			if ( isset($filter_data['branch_ids']) AND !isset($filter_data['default_branch_ids']) ) {
				$filter_data['default_branch_ids'] = $filter_data['branch_ids'];
			}
			if ( isset($filter_data['department_ids']) AND !isset($filter_data['default_department_ids']) ) {
				$filter_data['default_department_ids'] = $filter_data['department_ids'];
			}

			//Loop through schedule_shifts_index getting user_ids.
			foreach( $schedule_shifts_index as $date_stamp => $date_shifts ) {
				$filter_data['exclude_id'] = array_unique( array_merge( $filter_data['exclude_id'], array_keys( $date_shifts ) ) );
			}
			unset($date_stamp, $date_shifts);

			if ( isset($filter_data['exclude_id']) ) {
				//Debug::Arr($filter_data['exclude_id'], 'Including all employees. Excluded User Ids: ', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($filter_data, 'All Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

				//Only include active employees without any scheduled shifts.
				$filter_data['status_id'] = 10;

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getAPISearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
				Debug::text('Found blank employees: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $ulf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Processing Employees...') );

					$i = $max_i;
					foreach( $ulf as $u_obj ) {
						//Create dummy shift arrays with no start/end time.
						//$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$u_obj->getID().TTDate::getBeginDayEpoch($filter_data['start_date'])] = array(
						$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i] = array(
															//'id' => (int)$u_obj->getID(),
															'pay_period_id' => FALSE,
															'user_id' => (int)$u_obj->getID(),
															'user_created_by' => (int)$u_obj->getCreatedBy(),
															'user_full_name' => Misc::getFullName( $u_obj->getFirstName(), NULL, $u_obj->getLastName(), FALSE, FALSE ),
															'first_name' => $u_obj->getFirstName(),
															'last_name' => $u_obj->getLastName(),
															'title_id' => $u_obj->getTitle(),
															'title' => $u_obj->getColumn('title'),
															'group_id' => $u_obj->getColumn('group_id'),
															'group' => $u_obj->getColumn('group'),
															'default_branch_id' => $u_obj->getColumn('default_branch_id'),
															'default_branch' => $u_obj->getColumn('default_branch'),
															'default_department_id' => $u_obj->getColumn('default_department_id'),
															'default_department' => $u_obj->getColumn('default_department'),

															'branch_id' => (int)$u_obj->getDefaultBranch(),
															'branch' => $u_obj->getColumn('default_branch'),
															'department_id' => (int)$u_obj->getDefaultDepartment(),
															'department' => $u_obj->getColumn('default_department'),

															'created_by_id' => $u_obj->getCreatedBy(),
															'created_date' => $u_obj->getCreatedDate(),
															'updated_date' => $u_obj->getUpdatedDate(),
														);

						//Make sure we add in permission columns.
						$this->getPermissionColumns( $schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i], (int)$u_obj->getID(), $u_obj->getCreatedBy(), $permission_children_ids );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $ulf->getCurrentRow() );

						$i++;
					}

					$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
				}
			}
			//Debug::Arr($schedule_shifts, 'Final Scheduled Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset($schedule_shifts_index);

		if ( isset($schedule_shifts) ) {
			return $schedule_shifts;
		}

		return FALSE;
	}

	function getEnableReCalculateDay() {
		if ( isset($this->recalc_day) ) {
			return $this->recalc_day;
		}

		return FALSE;
	}
	function setEnableReCalculateDay($bool) {
		$this->recalc_day = $bool;

		return TRUE;
	}

	function getEnableOverwrite() {
		if ( isset($this->overwrite) ) {
			return $this->overwrite;
		}

		return FALSE;
	}
	function setEnableOverwrite($bool) {
		$this->overwrite = $bool;

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

	function handleDayBoundary() {
		//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);

		//This used to be done in Validate, but needs to be done in preSave too.
		//Allow 12:00AM to 12:00AM schedules for a total of 24hrs.
		if ( $this->getStartTime() != '' AND $this->getEndTime() != '' AND $this->getEndTime() <= $this->getStartTime() ) {
			//Since the initial end time is the same date as the start time, we need to see if DST affects between that end time and one day later. NOT the start time.
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			//$this->setEndTime( $this->getEndTime() + ( 86400 + (TTDate::getDSTOffset( $this->getEndTime(), ($this->getEndTime() + 86400) ) ) ) ); //End time spans midnight, add 24hrs.
			$this->setEndTime( strtotime('+1 day', $this->getEndTime() ) ); //Using strtotime handles DST properly, whereas adding 86400 causes strange behavior.
			Debug::Text('EndTime spans midnight boundary! Bump to next day... New End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	//Write all the schedules shifts for a given week.
	function writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day = 0, $bottom_border = FALSE) {
		$week_of_year = TTDate::getWeek( strtotime($week_date_stamps[0]), $start_week_day);
		//Debug::Text('Max Week Shifts: '. (int)$max_week_data[$week_of_year]['shift'], __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text('Max Week Branches: '. count($max_week_data[$week_of_year]['branch']), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text('Max Week Departments: '. count($max_week_data[$week_of_year]['department']), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('Week Of Year: '. $week_of_year, __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($max_week_data, 'max_week_data: ', __FILE__, __LINE__, __METHOD__, 10);

		$week_data_array = NULL;

		if ( !isset($max_week_data[$week_of_year]['labels']) ) {
			$max_week_data[$week_of_year]['labels'] = 0;
		}

		if ( $group_schedule == TRUE ) {
			$min_rows_multiplier = 2;
		} else {
			$min_rows_multiplier = 1;
		}

		if ( isset($max_week_data[$week_of_year]['shift']) ) {
			$min_rows_per_day = ( ($max_week_data[$week_of_year]['shift'] * $min_rows_multiplier) + $max_week_data[$week_of_year]['labels'] );
			Debug::Text('Shift Total: '. $max_week_data[$week_of_year]['shift'], __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$min_rows_per_day = ( $min_rows_multiplier + $max_week_data[$week_of_year]['labels'] );
		}
		Debug::Text('aMin Rows Per Day: '. $min_rows_per_day .' Labels: '. $max_week_data[$week_of_year]['labels'], __FILE__, __LINE__, __METHOD__, 10);
		//print_r($this->schedule_shifts);

		//Prepare data so we can write it out line by line, left to right.
		$shift_counter = 0;
		foreach( $week_date_stamps as $week_date_stamp ) {
			Debug::Text('Week Date Stamp: ('.$week_date_stamp.')'. TTDate::getDate('DATE+TIME', strtotime($week_date_stamp)), __FILE__, __LINE__, __METHOD__, 10);

			$rows_per_day = 0;
			if ( isset($this->schedule_shifts[$week_date_stamp]) ) {
				foreach( $this->schedule_shifts[$week_date_stamp] as $branch => $department_schedule_shifts ) {
					if ( $branch != '--' ) {
						$tmp_week_data_array[$week_date_stamp][] = array('type' => 'branch', 'date_stamp' => $week_date_stamp, 'label' => $branch );
						$rows_per_day++;
					}

					foreach( $department_schedule_shifts as $department => $tmp_schedule_shifts ) {
						if ( $department != '--' ) {
							$tmp_week_data_array[$week_date_stamp][] = array('type' => 'department', 'label' => $department );
							$rows_per_day++;
						}

						foreach( $tmp_schedule_shifts as $schedule_shift ) {
							if ( $group_schedule == TRUE ) {
								$tmp_week_data_array[$week_date_stamp][] = array('type' => 'user_name', 'label' => $schedule_shift['user_full_name'], 'shift' => $shift_counter );
								if ( $schedule_shift['status_id'] == 10 ) {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'shift', 'label' => TTDate::getDate('TIME', $schedule_shift['start_time'] ) .' - '. TTDate::getDate('TIME', $schedule_shift['end_time'] ), 'shift' => $shift_counter );
								} else {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'absence', 'label' => $schedule_shift['absence_policy'], 'shift' => $shift_counter );
								}
								$rows_per_day += 2;
							} else {
								if ( $schedule_shift['status_id'] == 10 ) {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'shift', 'label' => TTDate::getDate('TIME', $schedule_shift['start_time'] ) .' - '. TTDate::getDate('TIME', $schedule_shift['end_time'] ), 'shift' => $shift_counter );
								} else {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'absence', 'label' => $schedule_shift['absence_policy'], 'shift' => $shift_counter );
								}
								$rows_per_day++;
							}
							$shift_counter++;
						}
					}
				}
			}

			if ( $rows_per_day < $min_rows_per_day ) {
				for( $z = $rows_per_day; $z < $min_rows_per_day; $z++) {
					$tmp_week_data_array[$week_date_stamp][] = array('type' => 'blank', 'label' => NULL );
				}
			}
		}
		//print_r($tmp_week_data_array);

		for( $x = 0; $x < $min_rows_per_day; $x++ ) {
			foreach( $week_date_stamps as $week_date_stamp ) {
				if ( isset($tmp_week_data_array[$week_date_stamp][0]) ) {
					$week_data_array[] = $tmp_week_data_array[$week_date_stamp][0];
					array_shift($tmp_week_data_array[$week_date_stamp]);
				}
			}
		}
		unset($tmp_week_data_array);
		//print_r($week_data_array);

		//Render PDF here
		$border = 'LR';
		$i = 0;
		$total_cells = count($week_data_array);

		foreach( $week_data_array as $key => $data ) {
			if ( ($i % 7) == 0 ) {
				$pdf->Ln();
			}

			$pdf->setTextColor(0, 0, 0); //Black
			switch( $data['type'] ) {
				case 'branch':
					$pdf->setFillColor(200, 200, 200);
					$pdf->SetFont('freesans', 'B', 8);
					break;
				case 'department':
					$pdf->setFillColor(220, 220, 220);
					$pdf->SetFont('freesans', 'B', 8);
					break;
				case 'user_name':
					if ( ($data['shift'] % 2) == 0 ) {
						$pdf->setFillColor(240, 240, 240);
					} else {
						$pdf->setFillColor(255, 255, 255);
					}
					$pdf->SetFont('freesans', 'B', 8);
					break;
				case 'shift':
					if ( ($data['shift'] % 2) == 0 ) {
						$pdf->setFillColor(240, 240, 240);
					} else {
						$pdf->setFillColor(255, 255, 255);
					}
					$pdf->SetFont('freesans', '', 8);
					break;
				case 'absence':
					$pdf->setTextColor(255, 0, 0);
					if ( ($data['shift'] % 2) == 0 ) {
						$pdf->setFillColor(240, 240, 240);
					} else {
						$pdf->setFillColor(255, 255, 255);
					}
					$pdf->SetFont('freesans', 'I', 8);
					break;
				case 'blank':
					$pdf->setFillColor(255, 255, 255);
					$pdf->SetFont('freesans', '', 8);
					break;
			}

			if ( $bottom_border == TRUE AND $i >= ($total_cells - 7) ) {
				$border = 'LRB';
			}

			$pdf->Cell($cell_width, 15, $data['label'], $border, 0, 'C', 1);
			$pdf->setTextColor(0, 0, 0); //Black

			$i++;
		}

		$pdf->Ln();

		return TRUE;
	}

	//function getSchedule( $company_id, $user_ids, $start_date, $end_date, $start_week_day = 0, $group_schedule = FALSE ) {
	function getSchedule( $filter_data, $start_week_day = 0, $group_schedule = FALSE ) {
		global $current_user, $current_user_prefs;

		//Individual is one schedule per employee, or all on one schedule.
		if (!is_array($filter_data) ) {
			return FALSE;
		}

		$current_epoch = time();

		//Debug::Text('Start Date: '. TTDate::getDate('DATE', $start_date) .' End Date: '. TTDate::getDate('DATE', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $filter_data['start_date']) .' End Date: '. TTDate::getDate('DATE+TIME', $filter_data['end_date']) .' Start Week Day: '. $start_week_day, __FILE__, __LINE__, __METHOD__, 10);

		$pdf = new TTPDF('L', 'pt', 'Letter');

		$left_margin = 20;
		$top_margin = 20;
		$pdf->setMargins($left_margin, $top_margin);
		$pdf->SetAutoPageBreak(TRUE, 30);
		//$pdf->SetAutoPageBreak(FALSE);
		$pdf->SetFont('freesans', '', 10);

		$border = 0;
		$adjust_x = 0;
		$adjust_y = 0;

		if ( $group_schedule == FALSE ) {
			$valid_schedules = 0;

			$sf = TTnew( 'ScheduleFactory' );
			$tmp_schedule_shifts = $sf->getScheduleArray( $filter_data );
			//Re-arrange array by user_id->date
			if ( is_array($tmp_schedule_shifts) ) {
				foreach( $tmp_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
					foreach ( $day_schedule_shifts as $day_schedule_shift ) {
						$raw_schedule_shifts[$day_schedule_shift['user_id']][$day_epoch][] = $day_schedule_shift;
					}
				}
			}
			unset($tmp_schedule_shifts);
			//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($raw_schedule_shifts) AND is_array($raw_schedule_shifts) ) {
				foreach( $raw_schedule_shifts as $user_id => $day_schedule_shifts ) {

					foreach( $day_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
						foreach ( $day_schedule_shifts as $day_schedule_shift ) {
							//Debug::Arr($day_schedule_shift, 'aDay Schedule Shift: ', __FILE__, __LINE__, __METHOD__, 10);
							$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;

							if ( isset($schedule_shift_totals[$day_epoch]['total_shifts']) ) {
								$schedule_shift_totals[$day_epoch]['total_shifts']++;
							} else {
								$schedule_shift_totals[$day_epoch]['total_shifts'] = 1;
							}

							//$week_of_year = TTDate::getWeek( strtotime($day_epoch) );
							$week_of_year = TTDate::getWeek( strtotime($day_epoch), $start_week_day );
							if ( !isset($schedule_shift_totals[$day_epoch]['labels']) ) {
								$schedule_shift_totals[$day_epoch]['labels'] = 0;
							}
							if ( $day_schedule_shift['branch'] != '--'
									AND !isset($schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']]) ) {
								$schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']] = TRUE;
								$schedule_shift_totals[$day_epoch]['labels']++;
							}
							if ( $day_schedule_shift['department'] != '--'
									AND !isset($schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']]) ) {
								$schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']] = TRUE;
								$schedule_shift_totals[$day_epoch]['labels']++;
							}

							if ( !isset($max_week_data[$week_of_year]['shift']) ) {
								Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to 0', __FILE__, __LINE__, __METHOD__, 10);
								$max_week_data[$week_of_year]['shift'] = 1;
								$max_week_data[$week_of_year]['labels'] = 0;
							}

							if ( isset($max_week_data[$week_of_year]['shift'])
									AND ($schedule_shift_totals[$day_epoch]['total_shifts'] + $schedule_shift_totals[$day_epoch]['labels']) > ($max_week_data[$week_of_year]['shift'] + $max_week_data[$week_of_year]['labels']) ) {
								Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to: '.	 $schedule_shift_totals[$day_epoch]['total_shifts'] .' Labels: '. $schedule_shift_totals[$day_epoch]['labels'], __FILE__, __LINE__, __METHOD__, 10);
								$max_week_data[$week_of_year]['shift'] = $schedule_shift_totals[$day_epoch]['total_shifts'];
								$max_week_data[$week_of_year]['labels'] = $schedule_shift_totals[$day_epoch]['labels'];
							}

							//Debug::Arr($schedule_shift_totals, ' Schedule Shift Totals: ', __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($max_week_data, ' zMaxWeekData: ', __FILE__, __LINE__, __METHOD__, 10);
						}
					}

					if ( isset($tmp_schedule_shifts) ) {
						//Sort Branches/Departments first
						foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
							ksort($day_tmp_schedule_shift);
							$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

							foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
								ksort($tmp_schedule_shifts[$day_epoch][$branch]);
							}
						}

						//Sort each department by start time.
						foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
							foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
								foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
									$department_schedule_shift = Sort::multiSort( $department_schedule_shift, 'start_time' );

									$this->schedule_shifts[$day_epoch][$branch][$department] = $department_schedule_shift;
								}
							}
						}
					}
					unset($day_tmp_schedule_shift, $department_schedule_shifts, $department_schedule_shift, $tmp_schedule_shifts, $branch, $department);

					$calendar_array = TTDate::getCalendarArray($filter_data['start_date'], $filter_data['end_date'], $start_week_day );
					//var_dump($calendar_array);

					if ( !is_array($calendar_array) OR !isset($this->schedule_shifts) OR !is_array($this->schedule_shifts) ) {
						continue; //Skip to next user.
					}

					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByIdAndCompanyId( $user_id, $current_user->getCompany() );
					if ( $ulf->getRecordCount() != 1 ) {
						continue;
					} else {
						$user_obj = $ulf->getCurrent();

						$pdf->AddPage();

						$pdf->setXY( 670, $top_margin);
						$pdf->SetFont('freesans', '', 10);
						$pdf->Cell(100, 15, TTDate::getDate('DATE+TIME', $current_epoch ), $border, 0, 'R');

						$pdf->setXY( $left_margin, $top_margin);
						$pdf->SetFont('freesans', 'B', 25);
						$pdf->Cell(0, 25, $user_obj->getFullName(). ' - '. TTi18n::getText('Schedule'), $border, 0, 'C');
						$pdf->Ln();
					}

					$pdf->SetFont('freesans', 'B', 16);
					$pdf->Cell(0, 15, TTDate::getDate('DATE', $filter_data['start_date']) .' - '. TTDate::getDate('DATE', $filter_data['end_date']), $border, 0, 'C');
					//$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();

					$pdf->SetFont('freesans', '', 8);

					$cell_width = floor(($pdf->GetPageWidth() - ($left_margin * 2)) / 7);
					$cell_height = 100;

					$i = 0;
					$total_days = (count($calendar_array) - 1);
					$boader = 1;
					foreach( $calendar_array as $calendar ) {
						if ( $i == 0 ) {
							//Calendar Header
							$pdf->SetFont('freesans', 'B', 8);
							$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );

							foreach( $calendar_header as $header_name ) {
								$pdf->Cell($cell_width, 15, $header_name, 1, 0, 'C');
							}

							$pdf->Ln();
							unset($calendar_header, $header_name);
						}

						$month_name = NULL;
						if ( $i == 0 OR $calendar['isNewMonth'] == TRUE ) {
							$month_name = $calendar['month_name'];
						}

						if ( ($i > 0 AND $i % 7 == 0) ) {
							$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day);
							unset($week_date_stamps);
						}

						$pdf->SetFont('freesans', 'B', 8);
						$pdf->Cell( ($cell_width / 2), 15, $month_name, 'LT', 0, 'L');
						$pdf->Cell( ($cell_width / 2), 15, $calendar['day_of_month'], 'RT', 0, 'R');

						$week_date_stamps[] = $calendar['date_stamp'];

						$i++;
					}

					$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day, TRUE);

					$valid_schedules++;

					unset($this->schedule_shifts, $calendar_array, $week_date_stamps, $max_week_data, $day_epoch, $day_schedule_shifts, $day_schedule_shift, $schedule_shift_totals);
				}
			}
			unset($raw_schedule_shifts);
		} else {
			$valid_schedules = 1;

			$sf = TTnew( 'ScheduleFactory' );
			$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
			if ( is_array($raw_schedule_shifts) ) {
				foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
					foreach ( $day_schedule_shifts as $day_schedule_shift ) {
						//Debug::Arr($day_schedule_shift, 'bDay Schedule Shift: ', __FILE__, __LINE__, __METHOD__, 10);
						$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;

						if ( isset($schedule_shift_totals[$day_epoch]['total_shifts']) ) {
							$schedule_shift_totals[$day_epoch]['total_shifts']++;
						} else {
							$schedule_shift_totals[$day_epoch]['total_shifts'] = 1;
						}

						//$week_of_year = TTDate::getWeek( strtotime($day_epoch) );
						$week_of_year = TTDate::getWeek( strtotime($day_epoch), $start_week_day );
						Debug::text(' Date: '. TTDate::getDate('DATE', strtotime($day_epoch)) .' Week: '. $week_of_year .' TMP: '. TTDate::getWeek( strtotime('20070721'), $start_week_day ), __FILE__, __LINE__, __METHOD__, 10);
						if ( !isset($schedule_shift_totals[$day_epoch]['labels']) ) {
							$schedule_shift_totals[$day_epoch]['labels'] = 0;
						}
						if ( $day_schedule_shift['branch'] != '--'
								AND !isset($schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']]) ) {
							$schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']] = TRUE;
							$schedule_shift_totals[$day_epoch]['labels']++;
						}
						if ( $day_schedule_shift['department'] != '--'
								AND !isset($schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']]) ) {
							$schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']] = TRUE;
							$schedule_shift_totals[$day_epoch]['labels']++;
						}

						if ( !isset($max_week_data[$week_of_year]['shift']) ) {
							Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to 0', __FILE__, __LINE__, __METHOD__, 10);
							$max_week_data[$week_of_year]['shift'] = 1;
							$max_week_data[$week_of_year]['labels'] = 0;
						}

						if ( isset($max_week_data[$week_of_year]['shift'])
								AND ($schedule_shift_totals[$day_epoch]['total_shifts'] + $schedule_shift_totals[$day_epoch]['labels']) > ($max_week_data[$week_of_year]['shift'] + $max_week_data[$week_of_year]['labels']) ) {
							Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to: '.	 $schedule_shift_totals[$day_epoch]['total_shifts'] .' Labels: '. $schedule_shift_totals[$day_epoch]['labels'], __FILE__, __LINE__, __METHOD__, 10);
							$max_week_data[$week_of_year]['shift'] = $schedule_shift_totals[$day_epoch]['total_shifts'];
							$max_week_data[$week_of_year]['labels'] = $schedule_shift_totals[$day_epoch]['labels'];
						}
					}
				}
			}
			//print_r($tmp_schedule_shifts);
			//print_r($max_week_data);

			if ( isset($tmp_schedule_shifts) ) {
				//Sort Branches/Departments first
				foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
					ksort($day_tmp_schedule_shift);
					$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

					foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
						ksort($tmp_schedule_shifts[$day_epoch][$branch]);
					}
				}

				//Sort each department by start time.
				foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
					foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
						foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
							$department_schedule_shift = Sort::multiSort( $department_schedule_shift, 'last_name' );
							$this->schedule_shifts[$day_epoch][$branch][$department] = $department_schedule_shift;
						}
					}
				}
			}
			//Debug::Arr($this->schedule_shifts, 'Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

			$calendar_array = TTDate::getCalendarArray($filter_data['start_date'], $filter_data['end_date'], $start_week_day );
			//var_dump($calendar_array);

			if ( !is_array($calendar_array) OR !isset($this->schedule_shifts) OR !is_array($this->schedule_shifts) ) {
				return FALSE;
			}

			$pdf->AddPage();

			$pdf->setXY( 670, $top_margin);
			$pdf->SetFont('freesans', '', 10);
			$pdf->Cell(100, 15, TTDate::getDate('DATE+TIME', $current_epoch ), $border, 0, 'R');

			$pdf->setXY( $left_margin, $top_margin);

			$pdf->SetFont('freesans', 'B', 25);
			$pdf->Cell(0, 25, 'Employee Schedule', $border, 0, 'C');
			$pdf->Ln();

			$pdf->SetFont('freesans', 'B', 10);
			$pdf->Cell(0, 15, TTDate::getDate('DATE', $filter_data['start_date']) .' - '. TTDate::getDate('DATE', $filter_data['end_date']), $border, 0, 'C');
			$pdf->Ln();
			$pdf->Ln();

			$pdf->SetFont('freesans', '', 8);

			$cell_width = floor(($pdf->GetPageWidth() - ($left_margin * 2)) / 7);
			$cell_height = 100;

			$i = 0;
			$total_days = ( count($calendar_array) - 1 );
			$boader = 1;
			foreach( $calendar_array as $calendar ) {
				if ( $i == 0 ) {
					//Calendar Header
					$pdf->SetFont('freesans', 'B', 8);
					$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );

					foreach( $calendar_header as $header_name ) {
						$pdf->Cell($cell_width, 15, $header_name, 1, 0, 'C');
					}

					$pdf->Ln();
					unset($calendar_header, $header_name);
				}

				$month_name = NULL;
				if ( $i == 0 OR $calendar['isNewMonth'] == TRUE ) {
					$month_name = $calendar['month_name'];
				}

				if ( ($i > 0 AND $i % 7 == 0) ) {
					$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day);
					unset($week_date_stamps);
				}

				$pdf->SetFont('freesans', 'B', 8);
				$pdf->Cell( ($cell_width / 2), 15, $month_name, 'LT', 0, 'L');
				$pdf->Cell( ($cell_width / 2), 15, $calendar['day_of_month'], 'RT', 0, 'R');

				$week_date_stamps[] = $calendar['date_stamp'];

				$i++;
			}

			$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day, TRUE);
		}

		if ( $valid_schedules > 0 ) {
			$output = $pdf->Output('', 'S');
			return $output;
		}

		return FALSE;
	}

	function isConflicting() {
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		//Make sure we're not conflicting with any other schedule shifts.
		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getConflictingByUserIdAndStartDateAndEndDate( $this->getUser(), $this->getStartTime(), $this->getEndTime(), (int)$this->getID() );
		if ( $slf->getRecordCount() > 0 ) {
			foreach( $slf as $conflicting_schedule_shift_obj ) {
				if ( $conflicting_schedule_shift_obj->isNew() === FALSE
						AND $conflicting_schedule_shift_obj->getId() != $this->getId() ) {
					Debug::text('Conflicting Schedule Shift ID:'. $conflicting_schedule_shift_obj->getId() .' Schedule Shift ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	function Validate() {
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);

		$this->handleDayBoundary();

		$this->findUserDate();
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getUser() === FALSE ) { //Use === so we still allow OPEN shifts (user_id=0)
			$this->Validator->isTRUE(	'user_id',
										FALSE,
										TTi18n::gettext('Employee is not specified') );
		}

		//Check to make sure EnableOverwrite isn't enabled when editing an existing record.
		if ( $this->isNew() == FALSE AND $this->getEnableOverwrite() == TRUE ) {
			Debug::Text('Overwrite enabled when editing existing record, disabling overwrite.', __FILE__, __LINE__, __METHOD__, 10);
			$this->setEnableOverwrite( FALSE );
		}

		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company_id',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		if ( $this->getDateStamp() == FALSE ) {
			Debug::Text('DateStamp is INVALID! ID: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'date_stamp',
											FALSE,
											TTi18n::gettext('Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already') );
		}

		if ( $this->getDateStamp() != FALSE AND $this->getStartTime() == '' ) {
			$this->Validator->isTrue(		'start_time',
											FALSE,
											TTi18n::gettext('In Time not specified'));
		}
		if ( $this->getDateStamp() != FALSE AND $this->getEndTime() == '' ) {
			$this->Validator->isTrue(		'end_time',
											FALSE,
											TTi18n::gettext('Out Time not specified'));
		}

		if ( $this->getDeleted() == FALSE AND $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getHireDate() != '' AND TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Shift is before employees hire date') );
			}

			if ( $this->getUserObject()->getTerminationDate() != '' AND TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Shift is after employees termination date') );
			}
		}

		if ( $this->getStatus() == 20 AND $this->getAbsencePolicyID() != FALSE AND ( $this->getDateStamp() != FALSE AND $this->getUser() > 0 ) ) {
			$pglf = TTNew('PolicyGroupListFactory');
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array('user_id' => array($this->getUser()), 'absence_policy' => array($this->getAbsencePolicyID()) ) );
			if ( $pglf->getRecordCount() == 0 ) {
				$this->Validator->isTRUE(	'absence_policy_id',
								FALSE,
								TTi18n::gettext('This absence policy is not available for this employee'));
			}
		}

		//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
		//Also ignore when setting OPEN shifts to allow for multiple.
		if ( $this->getEnableOverwrite() == FALSE AND $this->getDeleted() == FALSE AND ( $this->getDateStamp() != FALSE AND $this->getUser() > 0 )) {
			$this->Validator->isTrue(		'start_time',
											!$this->isConflicting(), //Reverse the boolean.
											TTi18n::gettext('Conflicting start/end time, schedule already exists for this employee'));
		} else {
			Debug::text('Not checking for conflicts... DateStamp: '. (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		}
																																												if ( $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}

	function preSave() {
		if ( $this->getSchedulePolicyID() === FALSE ) {
			$this->setSchedulePolicyID(0);
		}

		if ( $this->getAbsencePolicyID() === FALSE ) {
			$this->setAbsencePolicyID(0);
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

		$this->handleDayBoundary();
		$this->findUserDate();

		if ( $this->getPayPeriod() == FALSE ) {
			$this->setPayPeriod();
		}

		if ( $this->getTotalTime() == FALSE ) {
			$this->setTotalTime( $this->calcTotalTime() );
		}

		if ( $this->getStatus() == 10 ) {
			$this->setAbsencePolicyID( NULL );
		} elseif ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Default to working.
		}

		if ( $this->getEnableOverwrite() == TRUE AND $this->isNew() == TRUE ) {
			//Delete any conflicting schedule shift before saving.
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getConflictingByUserIdAndStartDateAndEndDate( $this->getUser(), $this->getStartTime(), $this->getEndTime() );
			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text('Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__, 10);
				//Delete shifts.
				foreach( $slf as $s_obj ) {
					Debug::Text('Deleting Schedule Shift ID: '. $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
					$s_obj->setDeleted(TRUE);
					if ( $s_obj->isValid() ) {
						$s_obj->Save();
					}
				}
			} else {
				Debug::Text('NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return TRUE;
	}

	function postSave() {
		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if schedule is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != FALSE
					AND is_object( $this->getPayPeriodObject() )
					AND is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					AND $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId(  $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text('Pay Period is verified, deleting verification records: '. $pptsvlf->getRecordCount() .' User ID: '. $this->getUser() .' Pay Period ID: '. $this->getPayPeriod(), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $pptsvlf as $pptsv_obj ) {
						TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText('Schedule Modified After Verification').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Schedule').': '. TTDate::getDate('DATE', $this->getStartTime() ), NULL, $pptsvlf->getTable() );
						$pptsv_obj->setDeleted( TRUE );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		if ( $this->getEnableReCalculateDay() == TRUE ) {
			//Calculate total time. Mainly for docked.
			//Calculate entire week as Over Schedule (Weekly) OT policy needs to be reapplied if the schedule changes.
			if ( $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) {
				//When shifts are assigned to different days, we need to calculate both days the schedule touches, as the shift could be assigned to either of them.
				UserDateTotalFactory::reCalculateDay( $this->getUserObject(), array( $this->getDateStamp(), $this->getOldDateStamp(), $this->getStartTime(), $this->getEndTime() ), TRUE, FALSE );
			}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
/*
 *			//Use date_stamp is determined from StartTime and EndTime now automatically, due to schedules honoring the "assign shifts to" setting
			//We need to set the UserDate as soon as possible.
			//Consider mass editing shifts, where user_id is not sent but user_date_id is. We need to prevent the shifts from being assigned to the OPEN user.
			if ( isset($data['user_id']) AND ( $data['user_id'] !== '' AND $data['user_id'] !== FALSE )
					AND isset($data['date_stamp']) AND $data['date_stamp'] != ''
					AND isset($data['start_time']) AND $data['start_time'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] .' Start Time: '. $data['start_time'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'].' '.$data['start_time'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] >= 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
			}
*/
			if ( isset($data['overwrite']) ) {
				$this->setEnableOverwrite( TRUE );
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$this->setUser( $data[$key] );
							break;
						case 'user_date_id': //Ignore explicitly set user_date_id here as its set above.
						case 'total_time': //If they try to specify total time, just skip it, as it gets calculated later anyways.
							break;
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'start_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..Setting start time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__, 10);

								if ( isset($data['start_date_stamp']) AND $data['start_date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting start time... "'. $data['start_date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto start_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									//When start_time is provided as a full timestamp. Happens with audit log detail.
									Debug::text(' aaSetting start time...: '. $data[$key], __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' aaaSetting start time...: '. $this->getUserDateObject()->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting start time...', __FILE__, __LINE__, __METHOD__, 10);
								}
							}
							break;
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..xSetting end time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__, 10);

								if ( isset($data['start_date_stamp']) AND $data['start_date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting end time... "'. $data['start_date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto end_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aaSetting end time...: '. $data[$key], __FILE__, __LINE__, __METHOD__, 10);
									//When end_time is provided as a full timestamp. Happens with audit log detail.
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' bbbSetting end time... "'. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key]	 .'"', __FILE__, __LINE__, __METHOD__, 10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting end time...', __FILE__, __LINE__, __METHOD__, 10);
								}
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

			$this->handleDayBoundary(); //Make sure we handle day boundary before calculating total time.
			$this->setTotalTime( $this->calcTotalTime() ); //Calculate total time immediately after. This is required for proper audit logging too.
			$this->setEnableReCalculateDay(TRUE); //This is needed for Absence schedules to carry over to the timesheet.
			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							if ( $this->getColumn('user_id') > 0 ) {
								$data[$variable] = $this->getColumn( $variable );
							} else {
								$data[$variable] = TTi18n::getText('OPEN');
							}
							break;
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$data[$variable] = $this->tmp_data['user_id'] = (int)$this->getColumn( $variable );
							break;
						case 'user_status_id':
						case 'group_id':
						case 'title_id':
						case 'default_branch_id':
						case 'default_department_id':
						case 'pay_period_id':
							$data[$variable] = (int)$this->getColumn( $variable );
							break;
						case 'group':
						case 'title':
						case 'default_branch':
						case 'default_department':
						case 'schedule_policy':
						case 'absence_policy':
						case 'branch':
						case 'department':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartTime() ); //Include both date+time
							break;
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getStartTime() ); //Include both date+time
							break;
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getEndTime() ); //Include both date+time
							break;
						case 'start_time_stamp':
							$data[$variable] = $this->getStartTime(); //Include start date/time in epoch format for sorting...
							break;
						case 'end_time_stamp':
							$data[$variable] = $this->getEndTime(); //Include end date/time in epoch format for sorting...
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() ); //Just include time, so Mass Edit sees similar times without dates
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
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Schedule - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Start Time').': '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' '. TTi18n::getText('End Time').': '. TTDate::getDate('DATE+TIME', $this->getEndTime() ), NULL, $this->getTable(), $this );
	}
}
?>