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
 * @package Core
 */
class CalculatePolicy {

	//Default option flags.
	private $flags = array(
							'meal' => TRUE,
							'undertime_absence' => TRUE,
							'break' => TRUE,
							'holiday' => TRUE,
							'schedule_absence' => TRUE,
							'absence' => TRUE,
							'regular' => TRUE,
							'overtime' => TRUE,
							'premium' => TRUE,
							'accrual' => TRUE,
							'exception' => TRUE,

							//Exception options
							'exception_premature' => FALSE, //Calculates premature exceptions
							'exception_future' => TRUE, //Calculates exceptions in the future.

							//Calculate policies for future dates.
							'future_dates' => TRUE, //Calculates dates in the future.
							'past_dates' => FALSE, //Calculates dates in the past. This is only needed when Pay Formulas that use averaging are enabled?
						);
	
	private $user_obj = NULL;
	private $original_time_zone = NULL;

	private $pay_periods = NULL;
	private $pay_period_schedules = NULL;
	private $pay_period_obj = NULL;
	private $pay_period_schedule_obj = NULL;
	private $start_week_day_id = 0; //Cache the pay period schedule start_week_day_id.

	//Array of dates that data has been obtained, pending calculation or already calculated.
	private $dates = array( 'data' => array(), 'pending_calculation' => array(), 'calculated' => array() );

	private $currency_rates = NULL;
	private $user_wages = NULL;
	public $user_date_total_insert_id = -1;
	private $new_user_date_total_ids = NULL;
	private $new_system_user_date_total_id = array(); //Used to assign hour based accruals to.
	public $user_date_total = NULL;
	private $schedule = NULL;
	private $exception = NULL;
	private $punch = NULL;

	private $schedule_policy_rs = NULL;
	private $schedule_policy_max_start_stop_window = 0; //0 Seconds.
	private $meal_time_policy = NULL;
	private $schedule_policy_meal_time_policy = NULL;
	private $break_time_policy = NULL;
	private $schedule_policy_break_time_policy = NULL;
	private $undertime_absence_policy = NULL;

	private $exception_policy = NULL;

	private $regular_time_policy = NULL;
	private $regular_time_exclusivity_map = NULL;
	private $over_time_policy = NULL;
	private $over_time_trigger_time_exclusivity_map = NULL;
	private $over_time_recurse_map = NULL;
	private $schedule_over_time_policy_ids = NULL;

	private $premium_time_policy = NULL;
	private $schedule_premium_time_policy_ids = NULL;

	private $accrual_policy = NULL;

	public $holiday_policy = NULL; //Needs to be public so ContributingShiftPolicyFactory can read it.
	private $holiday = NULL;
	private $policy_group_holiday_policy_ids = NULL; //Holiday Policies associated with contributing shifts only.

	private $contributing_shift_policy = NULL;
	private $contributing_pay_code_policy = NULL;
	private $contributing_pay_codes_by_policy_id = NULL; //PolicyID -> Pay Code map.
	
	private $pay_codes = NULL;
	private $pay_formula_policy = NULL;


	//Determine pay period based on the date that is being calculated.
	function __construct( $user_obj = NULL ) {
		if ( is_object( $user_obj ) ) {
			$this->setUserObject( $user_obj );
		}

		return TRUE;
	}

	function setFlag( $key, $value = TRUE ) {
		if ( is_array($key) ) {
			foreach( $key as $k => $v ) {
				$this->flags[$k] = $v;
			}
		} else {
			$this->flags[$key] = $value;
		}
		return TRUE;
	}
	function getFlag( $key ) {
		if( isset($this->flags[$key]) ) {
			return $this->flags[$key];
		}

		return FALSE;
	}

	function getUserObject() {
		return $this->user_obj;
	}
	function setUserObject( $obj ) {
		if ( is_object($obj) ) {
			$this->user_obj = $obj;

			//Need to set the timezone as soon as the user object is specified, so when addPendingDates() is called they are in the proper timezone too.
			$this->setTimeZone();

			$this->setFlag( 'past_dates', $this->isPastDateCalculationRequired() );

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriodObject( $id ) {
		if ( $id > 0 ) {
			if ( isset($this->pay_periods[$id]) AND is_object($this->pay_periods[$id]) AND $id == $this->pay_periods[$id]->getID() ) {
				return $this->pay_periods[$id];
			} else {
				$lf = TTnew( 'PayPeriodListFactory' );
				$lf->getById( $id );
				if ( $lf->getRecordCount() == 1 ) {
					$this->pay_periods[$id] = $lf->getCurrent();
					return $this->pay_periods[$id];
				}

				return FALSE;
			}
		}

		return FALSE;
	}

	function getPayPeriodScheduleObject( $id ) {
		if ( $id > 0 ) {
			if ( isset($this->pay_period_schedules[$id]) AND is_object($this->pay_period_schedules[$id]) AND $id == $this->pay_period_schedules[$id]->getID() ) {
				return $this->pay_period_schedules[$id];
			} else {
				$lf = TTnew( 'PayPeriodListFactory' );
				$lf->getById( $id );
				if ( $lf->getRecordCount() == 1 ) {
					$this->pay_period_schedules[$id] = $lf->getCurrent();
					return $this->pay_period_schedules[$id];
				}

				return FALSE;
			}
		}

		return FALSE;
	}

	function setTimeZone() {
		//IMPORTANT: Make sure the timezone is set to the users timezone, prior to calculating policies,
		//as that will affect when date/time premium policies apply
		//Its also important that the timezone gets set back after calculating multiple punches in a batch as this can prevent other employees
		//from using the wrong timezone.
		//FIXME: How do we handle the employee moving between stations that themselves are in different timezones from the users default timezone?
		//How do we apply time based premium policies in that case?
		if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getUserPreferenceObject() ) ) {
			$this->original_time_zone = TTDate::getTimeZone();
			return TTDate::setTimeZone( $this->getUserObject()->getUserPreferenceObject()->getTimeZone() );
		}

		return FALSE;
	}
	function revertTimeZone() {
		if ( isset($this->original_time_zone) AND $this->original_time_zone != '' ) {
			return TTDate::setTimeZone( $this->original_time_zone );
		}

		return FALSE;
	}

	//Check if past date calculation is required.
	//This is based on PayFormulas that use average calculations.
	function isPastDateCalculationRequired() {
		$pfplf = TTnew( 'PayFormulaPolicyListFactory' );
		$pfplf->getByCompanyIdAndPayTypeId( $this->getUserObject()->getCompany(), 30 );
		if ( $pfplf->getRecordCount() > 0 ) {
			Debug::Text('Past date calculation is required...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::Text('Past date calculation is NOT required...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Since handling auto-deduct meal policies (negative total time) is virtually impossible to handle by itself
	//when it comes to overtime/premium policies that adjust the total time themselves,
	//this function will roll the meal/break policy time into the source record before being calculated for Reg/OT/Prem.
	//This way Reg/OT/Prem. calculation functions don't need to worry about negative total times at all. 
	function compactMealAndBreakUserDateTotalObjects( $user_date_total_rows ) {
		if ( is_array( $user_date_total_rows ) ) {
			$tmp_user_date_total_rows = $user_date_total_rows;
			Debug::Text('Total Records: '. count($user_date_total_rows), __FILE__, __LINE__, __METHOD__, 10);

			$processed_keys = array();
			
			//Check for Meal/Break object_types (100, 110)
			//Each record should correspond directly with another different object type, find that record and adjust it accordingly.
			foreach( $user_date_total_rows as $key => $udt_obj ) {
				if ( $udt_obj->getObjectType() == 100 OR $udt_obj->getObjectType() == 110 ) {
					Debug::Text('Found Meal/Break record... Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					foreach( $tmp_user_date_total_rows as $tmp_key => $tmp_udt_obj ) {
						if (	!isset($processed_keys[$tmp_key])
								AND !in_array( $tmp_udt_obj->getObjectType(), array(100, 101, 110, 111) )
								AND $udt_obj->getBranch() == $tmp_udt_obj->getBranch()
								AND $udt_obj->getDepartment() == $tmp_udt_obj->getDepartment()
								AND $udt_obj->getJob() == $tmp_udt_obj->getJob()
								AND $udt_obj->getJobItem() == $tmp_udt_obj->getJobItem()
								AND ( $udt_obj->getTotalTime() < 0 AND $tmp_udt_obj->getTotalTime() >= abs( $udt_obj->getTotalTime() ) )
							) {
							Debug::Text('  Found Corresponding record: '. $tmp_key .' Object Type: '. $tmp_udt_obj->getObjectType() .' Pay Code: '. $tmp_udt_obj->getPayCode() .' Total Time: '. $tmp_udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

							//Adjust corresponding record
							$user_date_total_rows[$tmp_key] = clone $tmp_udt_obj; //Clone the object so we don't modify the original one.
							$user_date_total_rows[$tmp_key]->setTotalTime( ( $user_date_total_rows[$tmp_key]->getTotalTime() + $udt_obj->getTotalTime() ) );
							if ( $user_date_total_rows[$tmp_key]->getEndTimeStamp() != '' ) {
								$user_date_total_rows[$tmp_key]->setEndTimeStamp( ( $user_date_total_rows[$tmp_key]->getEndTimeStamp() + $udt_obj->getTotalTime() ) );
							}
							
							Debug::Text('  New Total Time: '. $user_date_total_rows[$tmp_key]->getTotalTime() .' New End Stamp: '. TTDate::getDate('DATE+TIME', $user_date_total_rows[$tmp_key]->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

							//Remove original meal/break row.
							unset($user_date_total_rows[$key]);

							//Mark record as processed so we don't do it again.
							$processed_keys[$tmp_key] = TRUE;
							break;
						}
					}
				}
			}

			//Debug::Text('Done compacting... Total Records: '. count($user_date_total_rows), __FILE__, __LINE__, __METHOD__, 10);
			unset($tmp_user_date_total_rows, $udt_obj, $tmp_udt_obj, $processed_keys);
			return $user_date_total_rows;
		}

		Debug::Text('No data to compact...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Find existing UDT records that have override=TRUE and other fields matching, so we don't try to insert new UDT records.
	function isOverriddenUserDateTotalObject( $udt_obj ) {
		if ( is_array($this->user_date_total) ) {
			//Debug::Text('Search based on UDT: Object Type: '. $udt_obj->getObjectType() .' Pay Code: '. $udt_obj->getPayCode() .' Date: '. TTDate::getDate('DATE', $udt_obj->getDateStamp() ) .' Branch: '. $udt_obj->getBranch() .' Department: '. $udt_obj->getDepartment() .' Job: '. $udt_obj->getJob() .' Task: '. $udt_obj->getJobItem(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $this->user_date_total as $key => $tmp_udt_obj ) {
				if ( $key > 0 //Found positive time record, only positive ones can be overridden anyways.
						AND $tmp_udt_obj->getOverride() == TRUE
						AND $udt_obj->getObjectType() == $tmp_udt_obj->getObjectType()
						AND $udt_obj->getPayCode() == $tmp_udt_obj->getPayCode()
						AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $tmp_udt_obj->getDateStamp() )
						AND $udt_obj->getBranch() == $tmp_udt_obj->getBranch()
						AND $udt_obj->getDepartment() == $tmp_udt_obj->getDepartment()
						AND $udt_obj->getJob() == $tmp_udt_obj->getJob()
						AND $udt_obj->getJobItem() == $tmp_udt_obj->getJobItem()
					) {
					Debug::Text('Found override UDT object key: '. $key .' Object Type: '. $tmp_udt_obj->getObjectType() .' Pay Code: '. $tmp_udt_obj->getPayCode() .' Date: '. TTDate::getDate('DATE', $tmp_udt_obj->getDateStamp() ) .' Branch: '. $tmp_udt_obj->getBranch() .' Department: '. $tmp_udt_obj->getDepartment() .' Job: '. $tmp_udt_obj->getJob() .' Task: '. $tmp_udt_obj->getJobItem(), __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
				//else {
				//	Debug::Text('Skipping UDT object key: '. $key .' Object Type: '. $tmp_udt_obj->getObjectType() .' Pay Code: '. $tmp_udt_obj->getPayCode() .' Date: '. TTDate::getDate('DATE', $tmp_udt_obj->getDateStamp() ) .' Branch: '. $tmp_udt_obj->getBranch() .' Department: '. $tmp_udt_obj->getDepartment() .' Job: '. $tmp_udt_obj->getJob() .' Task: '. $tmp_udt_obj->getJobItem() .' Override: '. $tmp_udt_obj->getOverride(), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}
		}

		//Debug::Text('No override UDT records...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	
	//Remove UserDateTotalObjects that cancel each other out, such as a +1800 total time and -1800 total time for the same pay_code_id.
	//This is required for some premium policies with auto-deduct lunches and such.
	function removeRedundantUserDateTotalObjects() {
		if ( is_array($this->user_date_total) ) {
			foreach( $this->user_date_total as $key => $udt_obj ) {
				if ( $key < 0 AND $udt_obj->getTotalTime() < 0 ) { //Found negative time record.
					foreach( $this->user_date_total as $tmp_key => $tmp_udt_obj ) {
						if ( ( $udt_obj->getTotalTime() + $tmp_udt_obj->getTotalTime() ) == 0
								AND $udt_obj->getDateStamp() == $tmp_udt_obj->getDateStamp()
								AND $udt_obj->getObjectType() == $tmp_udt_obj->getObjectType()
								AND $udt_obj->getPayCode() == $tmp_udt_obj->getPayCode()
								AND ( $udt_obj->getTotalTimeAmount() + $tmp_udt_obj->getTotalTimeAmount() ) == 0
							) {
							Debug::Text('Removing redundant UDT object keys: 1: '. $key .' 2: '. $tmp_key .' Object Type: '. $udt_obj->getObjectType() .' Pay Code: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
							unset( $this->user_date_total[$key], $this->user_date_total[$tmp_key] );
							continue 2;
						}
					}
				}
			}

			return TRUE;
		}

		Debug::Text('No data to process...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function insertUserDateTotal( $user_date_total_records ) {
		if ( is_array($user_date_total_records) AND count($user_date_total_records) > 0 ) {
			//Debug::Arr($user_date_total_records, 'Inserting UserDateTotal entries...', __FILE__, __LINE__, __METHOD__, 10);
			
			$this->getUserObject()->StartTransaction();
			$inserted_records = 0;
			foreach( $user_date_total_records as $key => $udt_obj ) {
				//Insert new rows as long as total_time != 0.
				//  We want to have total time rows even if they are zero.
				//  However rows with total_time=0 account for about 40% of all rows, so removing them will save a lot of space.
				//  We also need to re-save UDT rows that have override=TRUE, so we can handle overtime exclusivity and such.
				//     This allows the user to override Regular Time to 10hrs, and have 2hrs still go into OT.
				//  Don't resave override absence entries, this caused a bug where UDT rows would switch to different dates when calcExceptions was run.
				if ( ( $key < 0 AND ( $udt_obj->getTotalTime() != 0 OR $udt_obj->getTotalTimeAmount() != 0 ) )
						OR ( $key > 0 AND $udt_obj->getObjectType() != 50 AND $udt_obj->getOverride() == TRUE ) ) {
					//Debug::text('    Currency ID: '. $this->getUserObject()->getCurrency() .' Rate: '. $this->filterCurrencyRate( $udt_obj->getDateStamp() )->getReverseConversionRate(), __FILE__, __LINE__, __METHOD__, 10);
					//Handle currency rates here, just before the record is saved.
					$udt_obj->setCurrency( (int)$this->getUserObject()->getCurrency(), TRUE ); //Disable automatic rate lookup.
					$udt_obj->setCurrencyRate( $this->filterCurrencyRate( $udt_obj->getDateStamp() )->getReverseConversionRate() );

					//Debug::Arr($udt_obj->data, 'Inserting UserDateTotal entry... Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					if ( $udt_obj->isValid() ) {
						$udt_obj->Save(FALSE);
						$inserted_records++;

						//Remove pre-saved object and replace it with saved object and proper ID.
						unset($this->user_date_total[$key]);
						$this->user_date_total[$udt_obj->getID()] = $udt_obj; //Keep the ID negative so know which records were newly inserted.
						$this->new_user_date_total_ids[] = $udt_obj->getID();

						if ( $udt_obj->getObjectType() == 5 ) {
							$this->new_system_user_date_total_id[TTDate::getMiddleDayEpoch($udt_obj->getDateStamp())] = $udt_obj->getId();
						}
					} else {
						Debug::text('ERROR: Invalid UserDateTotal Entry!', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				//else {
				//	Debug::text('Skipping UserDateTotal entry... Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				//}
			}
			Debug::text('UserDateTotal records inserted: '. $inserted_records, __FILE__, __LINE__, __METHOD__, 10);

			//$this->getUserObject()->FailTransaction();
			$this->getUserObject()->CommitTransaction();

			return TRUE;
		}

		Debug::text('No UserDateTotal entries to insert...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isConflictingUserDateTotal( $date_stamp, $object_type_id, $pay_code_id = NULL, $branch_id = NULL, $department_id = NULL, $job_id = NULL, $job_item_id = NULL ) {
		if ( is_array($this->user_date_total) ) {
			foreach( $this->user_date_total as $udt_key => $udt_obj ) {
				if ( TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp )
						AND in_array( $udt_obj->getObjectType(), (array)$object_type_id) ) {
					if (
							( $pay_code_id === NULL OR $udt_obj->getPayCode() == $pay_code_id )
							AND
							( $branch_id === NULL OR $udt_obj->getBranch() == $branch_id )
							AND
							( $department_id === NULL OR $udt_obj->getDepartment() == $department_id )
							AND
							( $job_id === NULL OR $udt_obj->getJob() == $job_id )
							AND
							( $job_item_id === NULL OR $udt_obj->getJobItem() == $job_item_id )

						) {
						Debug::text('Found conflicting UserDateTotal row: '. $udt_key, __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					}
				}
			}
		}

		Debug::text('No conflicting UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Calculates schedule absence time (exclusive to holiday absence time, or manually entered time on the timesheet)
	function calculateScheduleAbsence( $date_stamp ) {
		$slf = $this->filterScheduleDataByStatus( $date_stamp, $date_stamp, 20 );
		if ( is_array( $slf ) AND count( $slf ) > 0 ) {
			foreach( $slf as $key => $s_obj ) {
				if ( $s_obj->getStatus() == 20 AND $s_obj->getAbsencePolicyID() != '' ) {
					//Check for conflicting/overridden records, so we don't double up on the time.
					//This is to allow users to enter a schedule shift for absence time, then override it to smaller number of hours.
					//Only consider records using the same pay code though, so a user could have different absences on the same day
					//like a "No Show/No Call" on a Stat holiday and still receive stat holiday time and the absence time.
					if ( is_object( $s_obj->getAbsencePolicyObject() ) AND $this->isConflictingUserDateTotal( $date_stamp, array(25, 50), (int)$s_obj->getAbsencePolicyObject()->getPayCode() ) ) {
						continue;
					}

					if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
						$udtf = TTnew( 'UserDateTotalFactory' );
						$udtf->setUser( $this->getUserObject()->getId() );
						$udtf->setDateStamp( $date_stamp );
						$udtf->setObjectType( 50 ); //Absence
						$udtf->setSourceObject( $s_obj->getAbsencePolicyID() );
						$udtf->setPayCode( (int)$s_obj->getAbsencePolicyObject()->getPayCode() );

						$udtf->setBranch( (int)$s_obj->getBranch() );
						$udtf->setDepartment( (int)$s_obj->getDepartment() );
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
							$udtf->setJob( (int)$s_obj->getJob() );
							$udtf->setJobItem( (int)$s_obj->getJobItem() );
						}

						$udtf->setTotalTime( $s_obj->getTotalTime() );

						$udtf->setStartType( 10 ); //Normal
						$udtf->setStartTimeStamp( $s_obj->getStartTime() );
						$udtf->setEndType( 10 ); //Normal
						$udtf->setEndTimeStamp( $s_obj->getEndTime() );

						$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $s_obj->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp ) );
						$udtf->setHourlyRate( $this->getHourlyRate( $s_obj->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
						$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $s_obj->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

						$udtf->setEnableCalcSystemTotalTime(FALSE);
						$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

						if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
							//Don't save the record, just add it to the existing array, so it can be included in other calculations.
							//We will save these records at the end.
							$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
							$this->user_date_total_insert_id--;
						}
						Debug::text('Found scheduled absence... Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('No absence policy specified in schedule.', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			return TRUE;
		}

		Debug::text('No scheduled absences to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getScheduleDates( $schedule_arr ) {
		$retarr = array();
		if ( is_array($schedule_arr) ) {
			foreach( $schedule_arr as $s_obj ) {
				$retarr[] = $s_obj->getDateStamp();
			}
		}
		Debug::text('Schedule Dates: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
		return $retarr;
	}
	function getSumScheduledDays( $schedule_arr ) {
		$sum = 0;
		if ( is_array($schedule_arr) ) {
			foreach( $schedule_arr as $s_obj ) {
				if ( $s_obj->getStatus() == 10 AND $s_obj->getTotalTime() > 0 ) {
					$sum++;
				}
			}
		}
		Debug::text('Scheduled Days Total: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
		return $sum;
	}

	function getSumScheduleTime( $schedule_arr ) {
		$sum = 0;
		if ( is_array($schedule_arr) ) {
			foreach( $schedule_arr as $s_obj ) {
				$sum += $s_obj->getTotalTime();
			}

		}
		Debug::text('Sum Total: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
		return $sum;
	}

	function sortScheduleByDateASC( $a, $b ) {
		if ( $a->getDateStamp() == $b->getDateStamp() ) {
			return 0;
		}

		return ( $a->getDateStamp() < $b->getDateStamp() ) ? (-1) : 1;
	}
	function sortScheduleByDateDESC( $a, $b ) {
		if ( $a->getDateStamp() == $b->getDateStamp() ) {
			return 0;
		}

		return ( $a->getDateStamp() > $b->getDateStamp() ) ? (-1) : 1;
	}

	function filterScheduleDataByDateAndDirection( $pivot_date = NULL, $status_ids = NULL, $direction = NULL, $limit = NULL ) {
		$slf = $this->schedule;
		Debug::text('Pivot Date: '. TTDate::getDate('DATE', $pivot_date ) .' Direction: '.  $direction .' Limit: '. $limit, __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($slf) AND count($slf) > 0 ) {
			$direction = strtolower($direction);
			
			if ( $direction == 'desc' ) {
				uasort( $slf, array( $this, 'sortScheduleByDateDESC' ) );
			} else {
				uasort( $slf, array( $this, 'sortScheduleByDateASC' ) );
			}

			foreach( $slf as $s_obj ) {
				if ( ( $direction == 'desc' AND TTDate::getMiddleDayEpoch( $s_obj->getDateStamp() ) < TTDate::getMiddleDayEpoch( $pivot_date ) )
						OR $direction == 'asc' AND TTDate::getMiddleDayEpoch( $s_obj->getDateStamp() ) > TTDate::getMiddleDayEpoch( $pivot_date )  ) {
					$retarr[$s_obj->getId()] = $s_obj;
				} else {
					Debug::text('Scheduled shift does not match filter: '. $s_obj->getID() .' DateStamp: '. TTDate::getDate('DATE', $s_obj->getDateStamp() ) .' Status: '. $s_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found schedule rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No schedule rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterScheduleDataByStatus( $start_date, $end_date, $status_ids = NULL ) {
		$slf = $this->schedule;
		Debug::text('Start Date: '. TTDate::getDate('DATE', $start_date ) .' End Date: '.  TTDate::getDate('DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($slf) AND count($slf) > 0 ) {
			foreach( $slf as $s_obj ) {
				if ( TTDate::getMiddleDayEpoch( $s_obj->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $start_date )
						AND TTDate::getMiddleDayEpoch( $s_obj->getDateStamp() ) <= TTDate::getMiddleDayEpoch( $end_date )
						AND in_array( $s_obj->getStatus(), (array)$status_ids ) ) {
						$retarr[$s_obj->getId()] = $s_obj;
				}
				//else {
				//	Debug::text('Scheduled shift does not match filter: '. $s_obj->getID() .' DateStamp: '. TTDate::getDate('DATE', $s_obj->getDateStamp() ) .' Status: '. $s_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}

			if ( isset($retarr) ) {
				Debug::text('Found schedule rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No schedule rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Filter scheduled shifts based on worked shift times.
	function filterScheduleDataByShiftStartAndEnd( $start_time, $end_time ) {
		$slf = $this->schedule;
		Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_time ) .' End Date: '.  TTDate::getDate('DATE+TIME', $end_time ), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($slf) AND count($slf) > 0 ) {
			foreach( $slf as $s_obj ) {
				$start_stop_window = 0;
				if ( isset( $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()] ) ) {
					$start_stop_window = $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()]->getStartStopWindow();
				}

				if ( $s_obj->inSchedule( $start_time ) OR $s_obj->inSchedule( $end_time ) ) {
					$retarr[$s_obj->getId()] = $s_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found schedule rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No schedule rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getScheduleData( $start_date = NULL, $end_date = NULL, $limit = NULL, $order = NULL ) {
		$slf = TTNew('ScheduleListFactory');
		$filter_data = array(
								'user_id' => $this->getUserObject()->getId(),
								'start_date' => ( $start_date - $this->schedule_policy_max_start_stop_window ),
								'end_date' => ( $end_date + $this->schedule_policy_max_start_stop_window ),
								'exclude_id' => array_keys( (array)$this->schedule ),
							);
		Debug::text('Getting Schedule Data for Start Date: '. TTDate::getDate('DATE+TIME', $filter_data['start_date'] ) .' End: '. TTDate::getDate('DATE+TIME', $filter_data['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);
		$slf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $limit, NULL, NULL, $order );
		if ( $slf->getRecordCount() > 0 ) {
			Debug::text('Found schedule rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $slf as $s_obj ) {
				$this->schedule[$s_obj->getID()] = $s_obj;
			}

			return TRUE;
		}
		
		Debug::text('No schedule rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Filter schedule policies to only those that affect a specific shift.
	function filterSchedulePolicyByDate( $date_stamp ) {
		$shift_udt_objs = $this->getShiftStartAndEndUserDateTotal( $date_stamp, $date_stamp );
		if ( is_array($shift_udt_objs) AND isset($shift_udt_objs['start']) AND isset($shift_udt_objs['end']) ) {
			$slf = $this->filterScheduleDataByShiftStartAndEnd( $shift_udt_objs['start']->getStartTimeStamp(), $shift_udt_objs['end']->getEndTimeStamp() );
			if ( is_array($slf) AND count($slf) > 0 ) {
				foreach( $slf as $s_obj ) {
					if ( $s_obj->getSchedulePolicyID() > 0 ) {
						if ( isset( $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()] ) ) {
							$retarr[$s_obj->getSchedulePolicyID()] = $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()];
						} else {
							Debug::text('ERROR: Schedule policy that should exist does not: '. $s_obj->getSchedulePolicyID(), __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				}

				if ( isset($retarr) ) {
					Debug::text('Found scheduled shifts for this date: '. TTDate::getDATE('DATE', $date_stamp ) .' Total: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
					return $retarr;
				}
			}
		}
		/*
		$slf = $this->schedule;
		if ( is_array($slf) AND count($slf) > 0 ) {

			foreach( $slf as $s_obj ) {
				Debug::text('Schedule Date: '. TTDate::getDATE('DATE', $s_obj->getDateStamp() ) .' Start Time: '. TTDate::getDATE('DATE+TIME', $s_obj->getStartTime() ) .' End Time: '. TTDate::getDATE('DATE+TIME', $s_obj->getEndTime() ) .' Filter Date: '. TTDate::getDATE('DATE+TIME', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);

				//Make sure we find the nearest schedule if it fits within start/stop window.
				if ( $s_obj->getSchedulePolicyID() > 0
						AND (
								TTDate::getMiddleDayEpoch( $s_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp )
								//OR $s_obj->inSchedule( $date_stamp )
								//Make sure we handle cases where the schedule starts at 12:30AM on Dec 1st, but the shift starts at 11:00PM on Nov 30th.
								OR
								(
									isset( $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()] )
									AND
									TTDate::getMiddleDayEpoch( $date_stamp ) == TTDate::getMiddleDayEpoch( ( $s_obj->getStartTime() - $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()]->getStartStopWindow() ) )
								)
								OR
								(
									isset( $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()] )
									AND
									TTDate::getMiddleDayEpoch( $date_stamp ) == TTDate::getMiddleDayEpoch( ( $s_obj->getEndTime() + $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()]->getStartStopWindow() ) )
								)
							)
						) {
					if ( isset( $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()] ) ) {
						$retarr[$s_obj->getSchedulePolicyID()] = $this->schedule_policy_rs[$s_obj->getSchedulePolicyID()];
					} else {
						Debug::text('ERROR: Schedule policy that should exist does not: '. $s_obj->getSchedulePolicyID(), __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found scheduled shifts for this date: '. TTDate::getDATE('DATE', $date_stamp ) .' Total: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}
		*/

		Debug::text('No scheduled shifts for this date: '. TTDate::getDATE('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all possible schedule policies.
	function getSchedulePolicy() {
		//Get all schedule policies so we can figure out the maximum start/stop window
		//which we then use to get schedules. So this has to be called before getSchedule().
		$splf = TTnew( 'SchedulePolicyListFactory' );
		$splf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $splf->getRecordCount() > 0 ) {
			foreach( $splf as $sp_obj ) {
				$this->schedule_policy_rs[$sp_obj->getId()] = $sp_obj;
				if ( $sp_obj->getStartStopWindow() > $this->schedule_policy_max_start_stop_window ) {
					$this->schedule_policy_max_start_stop_window = $sp_obj->getStartStopWindow();
				}
			}

			Debug::text('Maximum Schedule Policy Start/Stop Window: '. $this->schedule_policy_max_start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			Debug::text('aNo schedule policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		}

		/*
		//Requires getSchedule() is called first.
		$slf = $this->schedule;
		if ( is_array($slf) AND count($slf) > 0 ) {
			Debug::text('Found schedule rows: '. count($slf), __FILE__, __LINE__, __METHOD__, 10);
			$schedule_policy_ids = array();
			foreach( $slf as $s_obj ) {
				$schedule_policy_ids[] = $s_obj->getSchedulePolicyID();
			}
			unset($s_obj);

			if ( count($schedule_policy_ids) > 0 ) {
				$splf = TTnew( 'SchedulePolicyListFactory' );
				$splf->getByIdAndCompanyId( $schedule_policy_ids, $this->getUserObject()->getCompany() );
				if ( $splf->getRecordCount() > 0 ) {
					foreach( $splf as $sp_obj ) {
						$this->schedule_policy_rs[$sp_obj->getId()] = $sp_obj;
						if ( $sp_obj->getStartStopWindow() > $this->schedule_policy_max_start_stop_window ) {
							$this->schedule_policy_max_start_stop_window = $sp_obj->getStartStopWindow();
						}
					}
					
					Debug::text('Maximum Schedule Policy Start/Stop Window: '. $this->schedule_policy_max_start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				} else {
					Debug::text('aNo schedule policy rows...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}
		*/

		Debug::text('bNo schedule policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function deleteSystemTotalTime( $date_stamp ) {
		//Delete everything that is not overrided.
		$udtlf = TTnew( 'UserDateTotalListFactory' );

		//Optimize for a direct delete query.
		//Due to a MySQL gotcha: http://dev.mysql.com/doc/refman/5.0/en/subquery-errors.html
		//We need to wrap the subquery in a subquery of itself to hide it from MySQL
		//So it doesn't complain about updating a table and selecting from it at the same time.
		//MySQL v5.0.22 DOES NOT like this query, it takes 10+ seconds to run and seems to cause a deadlock.
		//Switch back to a select then a bulkDelete instead. Still fast enough I think.

		//Only delete rows for policies we are actually recalculating.
		//This prevents calcQuickExceptions maintenance job from deleting/recalcuting UDT rows when it doesn't need to.
		//However if we add any more Flags we need to set them to FALSE in calcQuickExceptions.
		$object_type_ids = array();
		if ( $this->getFlag('meal') == TRUE ) {
			$object_type_ids = array_merge( $object_type_ids, array( 100, 101 ) );
		}

		if ( $this->getFlag('break') == TRUE ) {
			$object_type_ids = array_merge( $object_type_ids, array( 110, 111 ) );
		}

		if ( $this->getFlag('regular') == TRUE ) {
			$object_type_ids = array_merge( $object_type_ids, array( 20 ) );
		}

		if ( $this->getFlag('undertime_absence') == TRUE
				OR $this->getFlag('absence') == TRUE
				OR $this->getFlag('schedule_absence') == TRUE
				OR $this->getFlag('holiday') == TRUE
				) {
			$object_type_ids = array_merge( $object_type_ids, array( 25, 50 ) );
		}

		if ( $this->getFlag('overtime') == TRUE ) {
			$object_type_ids = array_merge( $object_type_ids, array( 30 ) );
		}

		if ( $this->getFlag('premium') == TRUE ) {
			$object_type_ids = array_merge( $object_type_ids, array( 40 ) );
		}

		if ( count($object_type_ids) > 0 ) {
			$object_type_ids = array_merge( $object_type_ids, array( 5 ) ); //System
		}

		if ( is_array($object_type_ids) AND count( $object_type_ids ) > 0 ) {
			//Debug::Arr( $object_type_ids, 'Deleting UDT rows based on total object_type_ids: '. count($object_type_ids), __FILE__, __LINE__, __METHOD__, 10);
			$udtlf->getByUserIdAndDateStampAndObjectTypeAndOverrideAndMisMatchPunchControlDateStamp( $this->getUserObject()->getId(), $date_stamp, $object_type_ids, FALSE ); //System totals
			$udtlf->bulkDelete( $udtlf->getIDSByListFactory( $udtlf ) );

			unset($this->new_system_user_date_total_id[TTDate::getMiddleDayEpoch($date_stamp)]); //Reset this when deleting records, so it can be set again when we insert them later on.
		} else {
			Debug::text('NOT Deleting UDT rows based on total object_type_ids: '. count($object_type_ids), __FILE__, __LINE__, __METHOD__, 10);
		}

		//Regardless if there are any accrual policies to calculate, we need to delete orphan records
		//in cases where we are deleting a manually added absence entry (override=1)
		//Do this immediately after we delete the UDT rows, as thats when orphans are created.
		AccrualFactory::deleteOrphans( $this->getUserObject()->getId(), $date_stamp );

		//Also delete records in memory for this date so they can be recalculated.
		$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, $object_type_ids );
		if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
			foreach( $udtlf as $key => $udt_obj ) {
				if ( $udt_obj->getOverride() == FALSE ) { //Ensure we don't delete overridden rows.
					Debug::text('Removing UDT row from memory: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					unset($this->user_date_total[$key]);
				}
			}
		}

		return TRUE;
	}
	
	function calculateSystemTotalTime( $date_stamp, $system_total_time ) {
		Debug::text('System Total Time: '. $system_total_time, __FILE__, __LINE__, __METHOD__, 10);

		$shift_udt_objs = $this->getShiftStartAndEndUserDateTotal( $date_stamp, $date_stamp );

		$this->user_date_total_insert_id--;
		if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
			$udtf = TTnew( 'UserDateTotalFactory' );
			$udtf->setUser( $this->getUserObject()->getId()  );
			$udtf->setDateStamp( $date_stamp );
			$udtf->setObjectType( 5 ); //System Total
			$udtf->setTotalTime( $system_total_time );

			if ( is_array( $shift_udt_objs ) AND isset( $shift_udt_objs['start'] ) AND isset( $shift_udt_objs['end'] ) ) {
				$udtf->setStartType( 10 ); //Normal
				$udtf->setEndType( 10 ); //Normal
				$udtf->setStartTimeStamp( $shift_udt_objs['start']->getStartTimeStamp());
				$udtf->setEndTimeStamp( $shift_udt_objs['end']->getEndTimeStamp() );
			}

			$udtf->setEnableCalcSystemTotalTime(FALSE);
			$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

			//Don't save the record, just add it to the existing array, so it can be included in other calculations.
			//We will save these records at the end.
			$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
			$this->user_date_total_insert_id--;
			return TRUE;
		} else {
			Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function calculateBreakTimePolicy( $date_stamp ) {
		//Calculate break time taken even if no break policies exist.
		$break_policy_total_time = 0;
		$break_overall_total_time = 0;

		$last_punch_in_timestamp = FALSE;

		$plf = $this->filterUserDateTotalDataByPunchTypeIDs( $date_stamp, $date_stamp, array(30) ); //Break rows only.
		if ( is_array( $plf ) AND count( $plf ) > 0 ) {
			$break_total_time_arr = array();

			$pair = 0;
			$x = 0;
			$out_for_break = FALSE;
			foreach ( $plf as $p_obj ) {
				if ( $out_for_break == FALSE AND $p_obj->getEndType() == 30 ) {
					$break_out_timestamp = $p_obj->getEndTimeStamp();
					$out_for_break = TRUE;
				} elseif ( $out_for_break == TRUE AND $p_obj->getStartType() == 30 ) {
					$break_punch_arr[$pair][20] = $break_out_timestamp;
					$break_punch_arr[$pair][10] = $p_obj->getStartTimeStamp();

					$out_for_break = FALSE;
					$pair++;
					unset($break_out_timestamp);

					if ( $p_obj->getStartType() == 30 AND $p_obj->getEndType() == 30 ) {
						$break_out_timestamp = $p_obj->getEndTimeStamp();
						$out_for_break = TRUE;
					}
				} else {
					$out_for_break = FALSE;
				}
				$x++;
			}

			if ( isset($break_punch_arr) ) {
				//Debug::Arr($break_punch_arr, 'Break Array: ', __FILE__, __LINE__, __METHOD__, 10);

				foreach( $break_punch_arr as $punch_control_id => $time_stamp_arr ) {
					if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
						if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
							$udtf = TTnew( 'UserDateTotalFactory' );
							$udtf->setUser( $this->getUserObject()->getId() );
							$udtf->setDateStamp( $date_stamp );
							$udtf->setObjectType( 111 ); //Break (Taken)
							$udtf->setPayCode( 0 );

							$udtf->setBranch( (int)$this->getUserObject()->getDefaultBranch() );
							$udtf->setDepartment( (int)$this->getUserObject()->getDefaultDepartment() );
							$udtf->setJob( (int)$this->getUserObject()->getDefaultJob() );
							$udtf->setJobItem( (int)$this->getUserObject()->getDefaultJobItem() );

							$udtf->setStartType( 30 ); //Break
							$udtf->setStartTimeStamp( $time_stamp_arr[20] );
							$udtf->setEndType( 30 ); //Break
							$udtf->setEndTimeStamp( $time_stamp_arr[10] );

							$udtf->setQuantity( count( $break_punch_arr ) ); //Use this to count total lunches taken?
							$udtf->setBadQuantity( 0 );
							$udtf->setTotalTime( bcsub($time_stamp_arr[10], $time_stamp_arr[20] ) );

							$udtf->setEnableCalcSystemTotalTime(FALSE);
							$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

							//Don't save the record, just add it to the existing array, so it can be included in other calculations.
							//We will save these records at the end.
							$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

							Debug::text('   Adding UDT row for Break (Taken) Total Time: '. $udtf->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
							$this->user_date_total_insert_id--;
						} else {
							Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
						}

						$break_overall_total_time = bcadd($break_overall_total_time, bcsub($time_stamp_arr[10], $time_stamp_arr[20] ) );
						$break_total_time_arr[] = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
					} else {
						Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				//Get the last punch in timestamp so we start auto-add/auto-deduct timestamps from this.
				$last_punch_in_timestamp = $time_stamp_arr[10];
			} else {
				Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
			}
		}
		unset( $plf, $pair, $x, $out_for_break, $break_out_timestamp, $break_punch_arr, $break_pair_total_time, $time_stamp_arr );

		$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10 ) );
		if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
			$day_total_time = 0;
			foreach( $udtlf as $udt_obj ) {
				$udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

				$day_total_time += $udt_obj->getTotalTime();
			}
			Debug::text('Day Total Time: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

			//Loop over all regular time policies calculating the pay codes, up until $maximum_time is reached.
			$break_time_policies = $this->filterBreakTimePolicy( $date_stamp, $day_total_time );
			if ( $day_total_time > 0 AND is_array( $break_time_policies ) AND count($break_time_policies) > 0 ) {
				$remaining_break_time = $break_overall_total_time;

				$i = 0;
				foreach( $break_time_policies as $bp_obj ) {
					if ( $last_punch_in_timestamp == FALSE ) {
						if ( !isset( $shift_udt_objs ) ) {
							$shift_udt_objs = $this->getShiftStartAndEndUserDateTotal( $date_stamp, $date_stamp );
						}
						if ( is_array($shift_udt_objs) AND isset($shift_udt_objs['start']) ) {
							$last_punch_in_timestamp = ( $shift_udt_objs['start']->getStartTimeStamp() + $bp_obj->getTriggerTime() );
						}
					}

					$break_policy_time = 0;
					if ( !isset($break_total_time_arr[$i]) ) {
						$break_total_time_arr[$i] = 0; //Prevent PHP warnings.
					}

					//This is the time that can be considered for the break.
					if ( $bp_obj->getIncludeMultipleBreaks() == TRUE ) {
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

					Debug::text('Break Policy ID: '. $bp_obj->getId() .' Type ID: '. $bp_obj->getType() .' Break Total Time: '. $eligible_break_total_time .' Amount: '. $bp_obj->getAmount() .' Day Total Time: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);
					switch ( $bp_obj->getType() ) {
						case 10: //Auto-Deduct
							Debug::text(' Break AutoDeduct...', __FILE__, __LINE__, __METHOD__, 10);
							if ( $bp_obj->getIncludeBreakPunchTime() == TRUE ) {
								$break_policy_time = ( bcsub( $bp_obj->getAmount(), $eligible_break_total_time ) * -1 );
								//If they take more then their alloted break, zero it out so time isn't added.
								if ( $break_policy_time > 0 ) {
									$break_policy_time = 0;
								}
							} else {
								$break_policy_time = ( $bp_obj->getAmount() * -1 );
							}
							break;
						case 15: //Auto-Add
							Debug::text(' Break AutoAdd...', __FILE__, __LINE__, __METHOD__, 10);
							if ( $bp_obj->getIncludeBreakPunchTime() == TRUE ) {
								if ( $eligible_break_total_time > $bp_obj->getAmount() ) {
									$break_policy_time = $bp_obj->getAmount();
								} else {
									$break_policy_time = $eligible_break_total_time;
								}
							} else {
								$break_policy_time = $bp_obj->getAmount();
							}
							break;
					}

					if ( $bp_obj->getIncludeBreakPunchTime() == TRUE AND $break_policy_time > $remaining_break_time ) {
						$break_policy_time = $remaining_break_time;
					}
					if ( $bp_obj->getIncludeBreakPunchTime() == TRUE	) { //Handle cases where some break policies include punch time, and others don't.
						$remaining_break_time -= $break_policy_time;
					}

					Debug::text('  bBreak Policy Total Time: '. $break_policy_time .' Break Policy ID: '. $bp_obj->getId() .' Remaining Time: '. $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

					if ( $break_policy_time != 0 ) {
						$break_policy_total_time = bcadd( $break_policy_total_time, $break_policy_time );

						if ( is_array($udt_arr) AND $day_total_time > 0 ) {
							$remainder = 0;
							foreach( $udt_arr as $udt_id => $total_time ) {
								//Make sure we use bcmath() functions here to avoid floating point imprecision issues.
								$udt_raw_break_policy_time = bcmul( bcdiv( $total_time, $day_total_time ), $break_policy_time );
								if ( $break_policy_time > 0 ) {
									$rounded_udt_raw_break_policy_time = floor($udt_raw_break_policy_time);
									$remainder = bcadd( $remainder, bcsub( $udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time ) );
								} else {
									$rounded_udt_raw_break_policy_time = ceil($udt_raw_break_policy_time);
									$remainder = bcadd( $remainder, bcsub( $udt_raw_break_policy_time, $rounded_udt_raw_break_policy_time ) );
								}

								$worked_time_break_policy_adjustments[$udt_id] = (int)$rounded_udt_raw_break_policy_time;
								Debug::text('UserDateTotal Row ID: '. $udt_id .' UDT Total Time: '. $total_time .' Raw Break Policy Time: '. $udt_raw_break_policy_time .'('. $rounded_udt_raw_break_policy_time .') Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
							}

							//Add remainder rounded to the nearest second to the last row.
							if ( $break_policy_time > 0 ) {
								$remainder = ceil( $remainder );
							} else {
								$remainder = floor( $remainder );
							}
							$worked_time_break_policy_adjustments[$udt_id] = (int)( $worked_time_break_policy_adjustments[$udt_id] + $remainder );

							Debug::Arr($worked_time_break_policy_adjustments, 'UserDateTotal Adjustments: Final Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
						}

						//Create a UDT row for each break policy adjustment element, so other policies can include/exclude the break/break time on its own.
						foreach( $worked_time_break_policy_adjustments as $udt_id => $worked_time_break_policy_adjustment ) {
							if ( isset($this->user_date_total[$udt_id]) ) {
								if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
									$udtf = TTnew( 'UserDateTotalFactory' );
									$udtf->setUser( $this->getUserObject()->getId() );
									$udtf->setDateStamp( $date_stamp );
									$udtf->setObjectType( 110 ); //Break
									$udtf->setSourceObject( (int)$bp_obj->getId() );
									$udtf->setPayCode( (int)$bp_obj->getPayCode() );

									$udtf->setBranch( (int)$this->user_date_total[$udt_id]->getBranch() );
									$udtf->setDepartment( (int)$this->user_date_total[$udt_id]->getDepartment() );
									$udtf->setJob( (int)$this->user_date_total[$udt_id]->getJob() );
									$udtf->setJobItem( (int)$this->user_date_total[$udt_id]->getJobItem() );

									$udtf->setStartType( 30 ); //Break
									$udtf->setEndType( 30 ); //Break
									if ( $bp_obj->getType() == 15 ) { //Auto-Add
										if ( $last_punch_in_timestamp != '' ) { //If the first punch is a Break IN and only a Normal OUT, $last_punch_in_timestamp will be NULL.
											$udtf->setStartTimeStamp( $last_punch_in_timestamp - abs( $worked_time_break_policy_adjustment ) );
											$udtf->setEndTimeStamp( $last_punch_in_timestamp );
											$last_punch_in_timestamp = $udtf->getStartTimeStamp();
										}
									} else { //Auto-Deduct
										if ( $last_punch_in_timestamp != '' ) { //If the first punch is a Break IN and only a Normal OUT, $last_punch_in_timestamp will be NULL.
											$udtf->setStartTimeStamp( $last_punch_in_timestamp );
											$udtf->setEndTimeStamp( $last_punch_in_timestamp + abs( $worked_time_break_policy_adjustment ) );
											$last_punch_in_timestamp = $udtf->getEndTimeStamp();
										}
									}

									$udtf->setQuantity( 0 );
									$udtf->setBadQuantity( 0 );
									$udtf->setTotalTime( $worked_time_break_policy_adjustment );

									//Base hourly rate on the regular wage
									$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $bp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp ) );
									$udtf->setHourlyRate( $this->getHourlyRate( $bp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
									$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $bp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

									$udtf->setEnableCalcSystemTotalTime(FALSE);
									$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

									if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
										//Don't save the record, just add it to the existing array, so it can be included in other calculations.
										//We will save these records at the end.
										$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
										Debug::text(' Adding UDT row for Break Policy Total Time: '. $worked_time_break_policy_adjustment, __FILE__, __LINE__, __METHOD__, 10);
										$this->user_date_total_insert_id--;
									}
								} else {
									Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::text(' ERROR: UDT ID does not exist: '. $udt_id, __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}

					Debug::text('  cBreak Policy Total Time: '. $break_policy_time .' Break Policy ID: '. $bp_obj->getId() .' Remaining Time: '. $remaining_break_time, __FILE__, __LINE__, __METHOD__, 10);

					$i++;
				}

				Debug::text('Total Break Policy Time: '. $break_policy_time, __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		} else {
			Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('No break policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function BreakTimePolicySortByTriggerTimeAsc( $a, $b ) {
		if ( $a->getTriggerTime() == $b->getTriggerTime() ) {
			return 0;
		}

		return ( $a->getTriggerTime() < $b->getTriggerTime() ) ? (-1) : 1;
	}

	function filterBreakTimePolicy( $date_stamp, $daily_total_time = NULL, $type_id = NULL, $always_return_at_least_one = FALSE ) {
		if ( ( $daily_total_time > 0 OR $always_return_at_least_one == TRUE )
					AND (
						( is_array( $this->break_time_policy ) AND count( $this->break_time_policy ) > 0 )
						OR
						( is_array( $this->schedule_policy_break_time_policy ) AND count( $this->schedule_policy_break_time_policy ) > 0 )
					)
			) {
			$schedule_policy_break_time_policy_ids = array();
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( is_array( $sp_obj->getBreakPolicy() ) ) {
						$schedule_policy_break_time_policy_ids = array_merge( $schedule_policy_break_time_policy_ids, (array)$sp_obj->getBreakPolicy() );
					}
				}
				Debug::Arr($schedule_policy_break_time_policy_ids, 'Break Policies that apply to: '. TTDate::getDate('DATE', $date_stamp) .' from schedule policies: ', __FILE__, __LINE__, __METHOD__, 10);
			}
			unset($schedule_policy_arr);

			//When break policies are defined in a schedule policy, they completely override the policy group break policies.
			//Break Policy ID: -1 == No Break
			//Break Policy ID: 0 == Defined By Policy Group
			if ( count($schedule_policy_break_time_policy_ids) > 0 AND !in_array( 0, $schedule_policy_break_time_policy_ids ) ) {
				//Only use break policies from schedule policy
				if ( in_array( -1, $schedule_policy_break_time_policy_ids ) ) {
					Debug::text('Using NO break policies...', __FILE__, __LINE__, __METHOD__, 10);
					$bplf = array(); //No break policies.
				} else {
					Debug::text('Using Schedule Policy break policies...', __FILE__, __LINE__, __METHOD__, 10);
					$bplf = Misc::arrayIntersectByKey( $schedule_policy_break_time_policy_ids, $this->schedule_policy_break_time_policy );
				}
			} else {
				//Only use break policies from policy group
				Debug::text('Using Policy Group break policies...', __FILE__, __LINE__, __METHOD__, 10);
				$bplf = $this->break_time_policy;
			}

			if ( is_array($bplf) AND count( $bplf ) > 0 ) {
				foreach( $bplf as $bp_obj ) {
					if ( $daily_total_time >= $bp_obj->getTriggerTime() AND ( $type_id == NULL OR in_array( $bp_obj->getType(), (array)$type_id ) ) ) {
						$retarr[$bp_obj->getId()] = $bp_obj;
						Debug::text('  Found Break policy matching trigger time: '. $bp_obj->getTriggerTime() .' Name: '. $bp_obj->getName() .' ID: '. $bp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::text('  Break policy does not match trigger time: '. $bp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				if ( isset($retarr) ) {
					Debug::text('Found break policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);

					//Since we have included/excluded additional policies, we need to resort them again so they are in the proper order.
					uasort( $retarr, array( $this, 'BreakTimePolicySortByTriggerTimeAsc' ) );

					return $retarr;
				} elseif( $always_return_at_least_one == TRUE AND isset( $bp_obj ) ) {
					Debug::text('Forced to always return at least one...', __FILE__, __LINE__, __METHOD__, 10);
					return array( $bp_obj ); //This is used by calculateExceptionPolicy() so we can *not* trigger No Lunch exception when the user has worked less than trigger time.
				}
			} elseif ( is_array($bplf) AND count( $bplf ) == 0 ) {
				return array(); //Return a blank array so we know no meal policies apply to this day, but there were some, or -1 (No Meal) was used instead.
			}

		}

		Debug::text('No break policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all overtime policies that could possibly apply, including from schedule policies.
	function getBreakTimePolicy() {
		$this->schedule_break_time_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			foreach( $splf as $sp_obj ) {
				$this->schedule_break_time_policy_ids = array_merge( $this->schedule_break_time_policy_ids, (array)$sp_obj->getBreakPolicy() );
			}
			unset($sp_obj);
		}

		$bplf = TTnew( 'BreakPolicyListFactory' );
		$bplf->getByPolicyGroupUserIdOrId( $this->getUserObject()->getId(), $this->schedule_break_time_policy_ids );
		if ( $bplf->getRecordCount() > 0 ) {
			Debug::text('Found break policy rows: '. $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $bplf as $bp_obj ) {
				if ( $bp_obj->getColumn('from_policy_group') == 1 ) {
					$this->break_time_policy[$bp_obj->getId()] = $bp_obj;
				} else {
					$this->schedule_policy_break_time_policy[$bp_obj->getId()] = $bp_obj;
				}
			}

			return TRUE;
		}

		Debug::text('No break policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculateMealTimePolicy( $date_stamp ) {
		//Calculate meal time taken even if no meal policies exist.
		$lunch_total_time = 0;

		$last_punch_in_timestamp = FALSE;
		
		$plf = $this->filterUserDateTotalDataByPunchTypeIDs( $date_stamp, $date_stamp, array(20) ); //Lunch rows only.
		if ( is_array( $plf ) AND count( $plf ) > 0 ) {
			$pair = 0;
			$x = 0;
			$out_for_lunch = FALSE;
			foreach ( $plf as $p_obj ) {
				if ( $out_for_lunch == FALSE AND $p_obj->getEndType() == 20 ) {
					$lunch_out_timestamp = $p_obj->getEndTimeStamp();
					$out_for_lunch = TRUE;
				} elseif ( $out_for_lunch == TRUE AND $p_obj->getStartType() == 20 ) {
					$lunch_punch_arr[$pair][20] = $lunch_out_timestamp;
					$lunch_punch_arr[$pair][10] = $p_obj->getStartTimeStamp();
					$out_for_lunch = FALSE;
					$pair++;
					unset($lunch_out_timestamp);

					if ( $p_obj->getStartType() == 20 AND $p_obj->getEndType() == 20 ) {
						$lunch_out_timestamp = $p_obj->getEndTimeStamp();
						$out_for_lunch = TRUE;
					}
				} else {
					$out_for_lunch = FALSE;
				}
				$x++;
			}

			if ( isset($lunch_punch_arr) ) {
				foreach( $lunch_punch_arr as $punch_control_id => $time_stamp_arr ) {
					if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
						//Insert UDT row for each lunch taken, with the start/end times.
						if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
							$udtf = TTnew( 'UserDateTotalFactory' );
							$udtf->setUser( $this->getUserObject()->getId() );
							$udtf->setDateStamp( $date_stamp );
							$udtf->setObjectType( 101 ); //Lunch (Taken)
							$udtf->setPayCode( 0 );

							$udtf->setBranch( (int)$this->getUserObject()->getDefaultBranch() );
							$udtf->setDepartment( (int)$this->getUserObject()->getDefaultDepartment() );
							$udtf->setJob( (int)$this->getUserObject()->getDefaultJob() );
							$udtf->setJobItem( (int)$this->getUserObject()->getDefaultJobItem() );

							$udtf->setStartType( 20 ); //Lunch
							$udtf->setStartTimeStamp( $time_stamp_arr[20] );
							$udtf->setEndType( 20 ); //Lunch
							$udtf->setEndTimeStamp( $time_stamp_arr[10] );

							$udtf->setQuantity( 0 ); //Use this to count total lunches taken?
							$udtf->setBadQuantity( 0 );
							$udtf->setTotalTime( ( $time_stamp_arr[10] - $time_stamp_arr[20] ));

							$udtf->setEnableCalcSystemTotalTime(FALSE);
							$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

							//Don't save the record, just add it to the existing array, so it can be included in other calculations.
							//We will save these records at the end.
							$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

							Debug::text('   Adding UDT row for Meal (Taken) Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);
							$this->user_date_total_insert_id--;

							$lunch_total_time = ( $lunch_total_time + $udtf->getTotalTime() );
						} else {
							Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				//Get the last punch in timestamp so we start auto-add/auto-deduct timestamps from this.
				$last_punch_in_timestamp = $time_stamp_arr[10];
			} else {
				Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
			}
		}
		unset( $plf, $udtf, $pair, $x, $out_for_lunch, $lunch_out_timestamp, $lunch_punch_arr, $lunch_pair_total_time, $time_stamp_arr );

		$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10 ) );
		if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
			$day_total_time = 0;
			foreach( $udtlf as $udt_obj ) {
				$udt_arr[$udt_obj->getId()] = $udt_obj->getTotalTime();

				$day_total_time += $udt_obj->getTotalTime();
			}
			Debug::text('Day Total Time: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

			//Loop over all regular time policies calculating the pay codes, up until $maximum_time is reached.
			$meal_time_policies = $this->filterMealTimePolicy( $date_stamp, $day_total_time );
			if ( $day_total_time > 0 AND is_array( $meal_time_policies ) AND count($meal_time_policies) > 0 ) {
				$meal_policy_time = 0;

				foreach( $meal_time_policies as $mp_obj ) {
					Debug::text('Meal Policy: '. $mp_obj->getName() .'('. $mp_obj->getId().') Type ID: '. $mp_obj->getType() .' Amount: '. $mp_obj->getAmount() .' Trigger Time: '. $mp_obj->getTriggerTime() .' Day Total TIme: '. $day_total_time, __FILE__, __LINE__, __METHOD__, 10);

					if ( $last_punch_in_timestamp == FALSE ) {
						if ( !isset( $shift_udt_objs ) ) {
							$shift_udt_objs = $this->getShiftStartAndEndUserDateTotal( $date_stamp, $date_stamp );
						}
						if ( is_array($shift_udt_objs) AND isset($shift_udt_objs['start']) ) {
							$last_punch_in_timestamp = ( $shift_udt_objs['start']->getStartTimeStamp() + $mp_obj->getTriggerTime() );
						}
					}

					Debug::text(' Lunch Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);
					switch ( $mp_obj->getType() ) {
						case 10: //Auto-Deduct
							Debug::text(' Lunch AutoDeduct.', __FILE__, __LINE__, __METHOD__, 10);
							if ( $mp_obj->getIncludeLunchPunchTime() == TRUE ) {
								$meal_policy_time = ( bcsub( $mp_obj->getAmount(), $lunch_total_time ) * -1 );
								//If they take more then their alloted lunch, zero it out so time isn't added.
								if ( $meal_policy_time > 0 ) {
									$meal_policy_time = 0;
								}
							} else {
								$meal_policy_time = ( $mp_obj->getAmount() * -1 );
							}
							break;
						case 15: //Auto-Add
							Debug::text(' Lunch AutoAdd.', __FILE__, __LINE__, __METHOD__, 10);
							if ( $mp_obj->getIncludeLunchPunchTime() == TRUE ) {
								if ( $lunch_total_time > $mp_obj->getAmount() ) {
									$meal_policy_time = $mp_obj->getAmount();
								} else {
									$meal_policy_time = $lunch_total_time;
								}
							} else {
								$meal_policy_time = $mp_obj->getAmount();
							}
							break;
					}

					Debug::text(' Meal Policy Total Time: '. $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);
					if ( $meal_policy_time != 0 ) {
						if ( is_array($udt_arr) AND $day_total_time > 0 ) {
							$remainder = 0;
							foreach( $udt_arr as $udt_id => $total_time ) {
								//Make sure we use bcmath() functions here to avoid floating point imprecision issues.
								$udt_raw_meal_policy_time = bcmul( bcdiv( $total_time, $day_total_time ), $meal_policy_time );
								if ( $meal_policy_time > 0 ) {
									$rounded_udt_raw_meal_policy_time = floor($udt_raw_meal_policy_time);
									$remainder = bcadd( $remainder, bcsub( $udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time ) );
								} else {
									$rounded_udt_raw_meal_policy_time = ceil($udt_raw_meal_policy_time);
									$remainder = bcadd( $remainder, bcsub( $udt_raw_meal_policy_time, $rounded_udt_raw_meal_policy_time ) );
								}

								$worked_time_meal_policy_adjustments[$udt_id] = (int)$rounded_udt_raw_meal_policy_time;
								Debug::text('UserDateTotal Row ID: '. $udt_id .' UDT Total Time: '. $total_time .' Raw Meal Policy Time: '. $udt_raw_meal_policy_time .'('. $rounded_udt_raw_meal_policy_time .') Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
							}

							//Add remainder rounded to the nearest second to the last row.
							if ( $meal_policy_time > 0 ) {
								$remainder = ceil( $remainder );
							} else {
								$remainder = floor( $remainder );
							}
							$worked_time_meal_policy_adjustments[$udt_id] = (int)( $worked_time_meal_policy_adjustments[$udt_id] + $remainder );

							Debug::Arr($worked_time_meal_policy_adjustments, 'UserDateTotal Adjustments: Final Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);
						}

						//Create a UDT row for each meal policy adjustment element, so other policies can include/exclude the meal/break time on its own.
						foreach( $worked_time_meal_policy_adjustments as $udt_id => $worked_time_meal_policy_adjustment ) {
							if ( isset($this->user_date_total[$udt_id]) ) {
								if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
									$udtf = TTnew( 'UserDateTotalFactory' );
									$udtf->setUser( $this->getUserObject()->getId() );
									$udtf->setDateStamp( $date_stamp );
									$udtf->setObjectType( 100 ); //Lunch
									$udtf->setSourceObject( (int)$mp_obj->getId() );
									$udtf->setPayCode( (int)$mp_obj->getPayCode() );

									$udtf->setBranch( (int)$this->user_date_total[$udt_id]->getBranch() );
									$udtf->setDepartment( (int)$this->user_date_total[$udt_id]->getDepartment() );
									$udtf->setJob( (int)$this->user_date_total[$udt_id]->getJob() );
									$udtf->setJobItem( (int)$this->user_date_total[$udt_id]->getJobItem() );

									$udtf->setStartType( 20 ); //Lunch
									$udtf->setEndType( 20 ); //Lunch
									if ( $mp_obj->getType() == 15 ) { //Auto_Include
										if ( $last_punch_in_timestamp != '' ) { //If the first punch is a Lunch IN and only a Normal OUT, $last_punch_in_timestamp will be NULL.
											$udtf->setStartTimeStamp( $last_punch_in_timestamp - abs( $worked_time_meal_policy_adjustment ) );
											$udtf->setEndTimeStamp( $last_punch_in_timestamp );
											$last_punch_in_timestamp = $udtf->getStartTimeStamp();
										}
									} else { //Auto-Deduct
										if ( $last_punch_in_timestamp != '' ) { //If the first punch is a Lunch IN and only a Normal OUT, $last_punch_in_timestamp will be NULL.
											$udtf->setStartTimeStamp( $last_punch_in_timestamp );
											$udtf->setEndTimeStamp( $last_punch_in_timestamp + abs( $worked_time_meal_policy_adjustment ) );
											$last_punch_in_timestamp = $udtf->getEndTimeStamp();
										}
									}

									$udtf->setQuantity( 0 );
									$udtf->setBadQuantity( 0 );
									$udtf->setTotalTime( $worked_time_meal_policy_adjustment );

									//Base hourly rate on the regular wage
									$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $mp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp ) );
									$udtf->setHourlyRate( $this->getHourlyRate( $mp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
									$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $mp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

									$udtf->setEnableCalcSystemTotalTime(FALSE);
									$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

									if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
										//Don't save the record, just add it to the existing array, so it can be included in other calculations.
										//We will save these records at the end.
										$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

										Debug::text(' Adding UDT row for Meal Policy Total Time: '. $worked_time_meal_policy_adjustment, __FILE__, __LINE__, __METHOD__, 10);
										$this->user_date_total_insert_id--;
									}
								} else {
									Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
								}

							} else {
								Debug::text(' ERROR: UDT ID does not exist: '. $udt_id, __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}
				}

				Debug::text('Total Meal Policy Time: '. $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		} else {
			Debug::text('No UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('No meal policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterMealTimePolicy( $date_stamp, $daily_total_time = NULL, $type_id = NULL, $always_return_at_least_one = FALSE ) {
		if ( ( $daily_total_time > 0 OR $always_return_at_least_one == TRUE )
				AND (
						( is_array( $this->meal_time_policy ) AND count( $this->meal_time_policy ) > 0 )
						OR
						( is_array( $this->schedule_policy_meal_time_policy ) AND count( $this->schedule_policy_meal_time_policy ) > 0 )
					)
				) {
			$schedule_policy_meal_time_policy_ids = array();
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( is_array( $sp_obj->getMealPolicy() ) AND count( $sp_obj->getMealPolicy() ) > 0 ) {
						$schedule_policy_meal_time_policy_ids = array_merge( $schedule_policy_meal_time_policy_ids, (array)$sp_obj->getMealPolicy() );
					}
				}
				Debug::Arr($schedule_policy_meal_time_policy_ids, 'Meal Policies that apply to: '. TTDate::getDate('DATE', $date_stamp) .' from schedule policies: ', __FILE__, __LINE__, __METHOD__, 10);
			}
			unset($schedule_policy_arr);

			//When meal policies are defined in a schedule policy, they completely override the policy group meal policies.
			//Meal Policy ID: -1 == No Meal
			//Meal Policy ID: 0 == Defined By Policy Group
			if ( count($schedule_policy_meal_time_policy_ids) > 0 AND !in_array( 0, $schedule_policy_meal_time_policy_ids ) ) {
				//Only use meal policies from schedule policy
				if ( in_array( -1, $schedule_policy_meal_time_policy_ids ) ) {
					Debug::text('Using NO meal policies...', __FILE__, __LINE__, __METHOD__, 10);
					$mplf = array(); //No meal policies.
				} else {
					Debug::text('Using Schedule Policy meal policy: '. $schedule_policy_meal_time_policy_ids[0], __FILE__, __LINE__, __METHOD__, 10);
					$mplf = Misc::arrayIntersectByKey( $schedule_policy_meal_time_policy_ids, $this->schedule_policy_meal_time_policy );
				}
			} else {
				//Only use meal policies from policy group
				Debug::text('Using Policy Group meal policies...', __FILE__, __LINE__, __METHOD__, 10);
				$mplf = $this->meal_time_policy;
			}

			if ( is_array($mplf) AND count( $mplf ) > 0 ) {
				foreach( $mplf as $mp_obj ) {
					if ( $daily_total_time >= $mp_obj->getTriggerTime() AND ( $type_id == NULL OR in_array( $mp_obj->getType(), (array)$type_id ) ) ) {
						$retarr[$mp_obj->getId()] = $mp_obj;
						Debug::text('  Found Meal policy matching trigger time: '. $mp_obj->getTriggerTime() .' ID: '. $mp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						break; //Only return one meal policy.
					} else {
						Debug::text('  Meal policy does not match type or trigger time: '. $mp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				if ( isset($retarr) ) {
					Debug::text('Found meal policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
					return $retarr;
				} elseif( $always_return_at_least_one == TRUE AND isset($mp_obj) ) {
					Debug::text('Forced to always return at least one...', __FILE__, __LINE__, __METHOD__, 10);
					return array( $mp_obj ); //This is used by calculateExceptionPolicy() so we can *not* trigger No Lunch exception when the user has worked less than trigger time.
				}

			} elseif ( is_array($mplf) AND count( $mplf ) == 0 ) {
				return array(); //Return a blank array so we know no meal policies apply to this day, but there were some, or -1 (No Meal) was used instead.
			}
		}

		Debug::text('No meal policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all meal policies that could possibly apply, including from schedule policies.
	function getMealTimePolicy() {
		$this->schedule_meal_time_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			Debug::text('Found schedule policy rows: '. count($splf), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $splf as $sp_obj ) {
				if ( is_array( $sp_obj->getMealPolicy() ) AND count( $sp_obj->getMealPolicy() ) > 0 ) {
					$this->schedule_meal_time_policy_ids = array_merge( $this->schedule_meal_time_policy_ids, (array)$sp_obj->getMealPolicy() );
				}
			}
			unset($sp_obj);
		}

		$mplf = TTnew( 'MealPolicyListFactory' );
		$mplf->getByPolicyGroupUserIdOrId( $this->getUserObject()->getId(), $this->schedule_meal_time_policy_ids );
		if ( $mplf->getRecordCount() > 0 ) {
			Debug::text('Found meal policy rows: '. $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $mplf as $mp_obj ) {
				if ( $mp_obj->getColumn('from_policy_group') == 1 ) {
					$this->meal_time_policy[$mp_obj->getId()] = $mp_obj;
				} else {
					$this->schedule_policy_meal_time_policy[$mp_obj->getId()] = $mp_obj;
				}
			}

			Debug::text('Found schedule policy meal policy rows: '. count($this->schedule_policy_meal_time_policy) .' Policy Group: '. count($this->meal_time_policy), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No meal policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculateAbsenceTimePolicy( $date_stamp ) {
		$user_date_total_rows = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 50 ) );
		if ( is_array($user_date_total_rows) ) {
			foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
				$ap_obj = $udt_obj->getSourceObjectObject();
				
				Debug::text('Generating UserDateTotal object from Absence Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 25 .' Pay Code ID: '. (int)$udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .' UDT Key: '. $udt_key, __FILE__, __LINE__, __METHOD__, 10);
				if ( is_object($ap_obj) AND !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
					$udtf = TTnew( 'UserDateTotalFactory' );
					$udtf->setUser( $this->getUserObject()->getId() );
					$udtf->setDateStamp( $date_stamp );
					$udtf->setObjectType( 25 ); //Absence Time
					$udtf->setSourceObject( (int)$udt_obj->getSourceObject() );
					$udtf->setPayCode( $udt_obj->getPayCode() );

					$udtf->setBranch( (int)$udt_obj->getBranch() );
					$udtf->setDepartment( (int)$udt_obj->getDepartment() );
					$udtf->setJob( (int)$udt_obj->getJob() );
					$udtf->setJobItem( (int)$udt_obj->getJobItem() );

					$udtf->setQuantity( 0 );
					$udtf->setBadQuantity( 0 );
					$udtf->setTotalTime( $udt_obj->getTotalTime() );

					//Base hourly rate on the regular wage
					$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $ap_obj->getPayFormulaPolicy(), $udt_obj->getPayCode(), $date_stamp ) );
					$udtf->setHourlyRate( $this->getHourlyRate( $ap_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
					$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $ap_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

					$udtf->setEnableCalcSystemTotalTime(FALSE);
					$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

					if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
						//Don't save the record, just add it to the existing array, so it can be included in other calculations.
						//We will save these records at the end.
						$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

						$udt_used_keys[] = $udt_key;
						$this->user_date_total_insert_id--;
					}
				} else {
					Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			Debug::text('Done with absence time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No absence time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}


	function calculateUnderTimeAbsencePolicy( $date_stamp ) {
		$ap_obj = $this->filterUnderTimeAbsencePolicy( $date_stamp );
		if ( is_object($ap_obj) ) {
			$schedule_daily_total_time = $this->getSumScheduleTime( $this->filterScheduleDataByStatus( $date_stamp, $date_stamp, array( 10 ) ) );
			$worked_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ) ); //Make sure we include paid/unpaid lunches/breaks.

			$total_under_time = ( $schedule_daily_total_time - $worked_daily_total_time );
			Debug::text('Schedule Daily Total Time: '. $schedule_daily_total_time .' Worked Time: '. $worked_daily_total_time .' Total Under Time: '. $total_under_time .' Date: '. TTDate::getDATE('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

			//Make sure they have at least some worked time, as without any punch we can't match to what schedules should be applied.
			if ( $worked_daily_total_time > 0 AND $total_under_time > 0 ) {
				//Check for conflicting/overridden records, so the user can override undertime absences and zero them out.
				if ( $this->isConflictingUserDateTotal( $date_stamp, array(50), (int)$ap_obj->getPayCode() ) == FALSE ) {

					if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
						$udtf = TTnew( 'UserDateTotalFactory' );
						$udtf->setUser( $this->getUserObject()->getId() );
						$udtf->setDateStamp( $date_stamp );

						//This has to be recorded as type 25 absence rather than type 50 (taken),
						//since Taken entries shouldn't be automatically modified/created.
						//In cases of a undertime absence being created and the employer wanting to manually override
						//it to a paid absence for example, they would need to switch the Schedule Policy or
						//wait until we have Hour List modification functionality added.
						$udtf->setObjectType( 25 );
						$udtf->setSourceObject( (int)$ap_obj->getId() );
						$udtf->setPayCode( (int)$ap_obj->getPayCode() );

						$udtf->setBranch( $this->getUserObject()->getDefaultBranch() );
						$udtf->setDepartment( $this->getUserObject()->getDefaultDepartment() );
						$udtf->setJob( $this->getUserObject()->getDefaultJob() );
						$udtf->setJobItem( $this->getUserObject()->getDefaultJobItem() );

						$udtf->setQuantity( 0 );
						$udtf->setBadQuantity( 0 );
						$udtf->setTotalTime( $total_under_time );

						//Base hourly rate on the regular wage
						$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $ap_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp ) );
						$udtf->setHourlyRate( $this->getHourlyRate( $ap_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
						$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $ap_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

						$udtf->setEnableCalcSystemTotalTime(FALSE);
						$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

						if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
							//Don't save the record, just add it to the existing array, so it can be included in other calculations.
							//We will save these records at the end.
							$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

							Debug::text(' Adding UDT row for UnderTime Absence Policy Time: '. $total_under_time, __FILE__, __LINE__, __METHOD__, 10);
							$this->user_date_total_insert_id--;
						}

						return TRUE;
					} else {
						Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('Found absence taken that conflicts with undertime policy, skipping...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::text('No undertime absence policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterUnderTimeAbsencePolicy( $date_stamp ) {
		if ( ( is_array( $this->undertime_absence_policy ) AND count( $this->undertime_absence_policy ) > 0 ) ) {
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( isset($this->undertime_absence_policy[$sp_obj->getAbsencePolicyID()]) ) {
						Debug::text('  Found undertime absence policy...', __FILE__, __LINE__, __METHOD__, 10);
						return $this->undertime_absence_policy[$sp_obj->getAbsencePolicyID()];
					}
				}
			}
		}

		Debug::text('No undertime absence policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all absence policies that could possibly apply, including from schedule policies.
	function getUnderTimeAbsenceTimePolicy() {
		$this->schedule_undertime_absence_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			Debug::text('Found schedule policy rows: '. count($splf), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $splf as $sp_obj ) {
				$this->schedule_undertime_absence_policy_ids = array_merge( $this->schedule_undertime_absence_policy_ids, (array)$sp_obj->getAbsencePolicyID() );
			}
			unset($sp_obj);
		}

		if ( count( $this->schedule_undertime_absence_policy_ids ) > 0 ) {
			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$aplf->getByIdAndCompanyId( $this->schedule_undertime_absence_policy_ids, $this->getUserObject()->getCompany() );
			if ( $aplf->getRecordCount() > 0 ) {
				Debug::text('Found undertime absence policy rows: '. $aplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $aplf as $ap_obj ) {
					$this->undertime_absence_policy[$ap_obj->getId()] = $ap_obj;
				}

				return TRUE;
			}
		}

		Debug::text('No undertime absence policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculateRegularTimeExclusivity() {
		//Loop through the exclusivity map and reduce regular time records by the amount of the linked absence record.
		if ( is_array( $this->regular_time_exclusivity_map ) AND count( $this->regular_time_exclusivity_map ) > 0 ) {
			foreach( $this->regular_time_exclusivity_map as $exclusivity_data ) {
				foreach( $exclusivity_data as $regular_udt_key => $reg_udt_key ) {
					//Debug::text('Regular UDT Key '. $regular_udt_key .' Absence Key: '. $reg_udt_key, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $this->user_date_total[$regular_udt_key] ) AND isset($this->user_date_total[$reg_udt_key]) ) {
						$udt_obj = $this->user_date_total[$regular_udt_key];
						$reg_udt_obj = $this->user_date_total[$reg_udt_key];

						Debug::text('Absence UDT Total Time: '. $udt_obj->getTotalTime() .'('.$regular_udt_key.') Regular Total Time: '. $reg_udt_obj->getTotalTime() .'('.$reg_udt_key.')', __FILE__, __LINE__, __METHOD__, 10);
						if ( $udt_obj->getObjectType() == 25 ) { //Regular Time or Absence
							$udt_obj->setTotalTime( ( $udt_obj->getTotalTime() - $reg_udt_obj->getTotalTime() ) );
							$udt_obj->setQuantity( ( $udt_obj->getQuantity() - $reg_udt_obj->getQuantity() ) );
							$udt_obj->setBadQuantity( ( $udt_obj->getBadQuantity() - $reg_udt_obj->getBadQuantity() ) );

							if ( $udt_obj->getEndTimeStamp() != '' ) {
								$udt_obj->setEndTimeStamp( ( $udt_obj->getEndTimeStamp() - $reg_udt_obj->getTotalTime() ) );
							}

							$udt_obj->preSave(); //Calculate TotalTimeAmount.
							Debug::text('  Reducing Absence Time to: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text('WARNING: UDT Records isnt absence time, unable to adjust for exclusivity. Object Type: '. $udt_obj->getObjectType() .' Pay Code: '. $udt_obj->getPayCode(), __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::text('ERROR: UDT Records dont exist, unable to adjust for exclusivity.', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
			unset($udt_obj, $reg_udt_obj);

			$this->regular_time_exclusivity_map = NULL; //Make sure this reset each time this is run.

			return TRUE;
		}

		Debug::text('No exclusivity records to calculate!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculateRegularTimePolicy( $date_stamp, $maximum_daily_total_time = NULL ) {
		//Since other policies such as OT need to be able to calculate hourly rates based on regular time.
		//We need to assign *all* worked time+meal/break+absence to regular time policies.
		//  Then when OT policies are calculated, they don't need to use worked time at all, and can reduce the regular time policy time by whatever is converted to OT.

		//Loop over all regular time policies calculating the pay codes, up until $maximum_time is reached.
		$regular_time_policies = $this->filterRegularTimePolicy( $date_stamp );
		$total_regular_time_policies = count($regular_time_policies);
		if ( is_array( $regular_time_policies ) AND $total_regular_time_policies > 0 ) {
			//Don't set an upper limit on the regular time, as we have to account for worked, absence time, but not always in every situation
			//So we can't reliably calculate the upper limit. As long as we don't calculate policies on source UDT rows multiple times we should be fine.
			//$maximum_time = $maximum_daily_total_time;
			$maximum_time = 0;
			Debug::text('Maximum Possible Regular Time: '. $maximum_time, __FILE__, __LINE__, __METHOD__, 10);

			$udt_used_keys = array();
			
			$covered_time = 0;
			$break_loop = FALSE;
			for( $i = 0; $i <= $total_regular_time_policies; $i++ ) {

				if ( $i == $total_regular_time_policies ) {
					//Don't set an upper limit on the regular time, as we have to account for worked, absence time, but not always in every situation
					//So we can't reliably calculate the upper limit. As long as we don't calculate policies on source UDT rows multiple times we should be fine.
					//continue;

					Debug::text('Reached last row, apply catch all: '. $maximum_time .' I: '. $i .' Total Reg Policies: '. $total_regular_time_policies, __FILE__, __LINE__, __METHOD__, 10);
					$rtp_obj = end($regular_time_policies);

					if ( !isset($this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]) ) {
						Debug::text(' ERROR: Contributing Shift Policy for RegularTime Policy: '. $rtp_obj->getName() .' does not exist...', __FILE__, __LINE__, __METHOD__, 10);
						continue;
					}

					//Haven't used all worked time, so use all worked time as the input for the last policy, regardless of what the contributing shift policy says.
					//Should this be the regular time policy with the highest calculation order assigned to this employee/day, or highest for the entire company?
					//Should the time go to pay_code_id = 0 using a special name like "Regular Time (Catch All)" with 0 rate of pay? That way its easy to figure out the issue
					//  and correct by simply making a regular time policy that includes all worked time? (which would be there by default anyways)
					// Don't include Absence Time (25) in here, otherwise it will always override absence time as its exclusive. 
					//$user_date_total_rows = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) );

					//Still need to include meal/break policy time, in case they want to include it in regular time or not.
					$user_date_total_rows = $this->compactMealAndBreakUserDateTotalObjects( $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()], array( 10, 100, 110 ) ) );
				} else {
					$rtp_obj = current($regular_time_policies);

					if ( !isset($this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]) ) {
						Debug::text(' ERROR: Contributing Shift Policy for RegularTime Policy: '. $rtp_obj->getName() .' does not exist...', __FILE__, __LINE__, __METHOD__, 10);
						continue;
					}

					//Do we include just Worked Time in the calculation, or do we somehow handle Contributing Shift Policies?
					//Regular Time should be exclusive to itself, so we can't calculate regular time on top of regular time.
					//Only include worked time in regular time calculation, then overtime and premium will not include regular time.
					$user_date_total_rows = $this->compactMealAndBreakUserDateTotalObjects( $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()], array( 10, 25, 100, 110 ) ) );
				}
				Debug::text('I: '. $i .' Regular Time Policy: '. $rtp_obj->getName() .' Pay Code: '. $rtp_obj->getPayCode(), __FILE__, __LINE__, __METHOD__, 10);
				
				if ( is_array($user_date_total_rows) ) {
					foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
						Debug::text('Regular Time Policy: '. $rtp_obj->getName() .' ID: '. $udt_obj->getID() .' Time: '. $udt_obj->getTotalTime() .' Pay Code: '. $rtp_obj->getPayCode() .' Quantity: '. $udt_obj->getQuantity() .' Bad Quantity: '. $udt_obj->getBadQuantity() .' Used Regular Time: '. $covered_time .' Maximum Time: '. $maximum_time, __FILE__, __LINE__, __METHOD__, 10);
						if ( in_array( $udt_key, $udt_used_keys ) ) {
							Debug::text('UDT row already used in another regular time policy! ID: '. $udt_key, __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}

						if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
							$create_udt_record = FALSE;
							if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getBranchSelectionType(), $rtp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $rtp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
								//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $rtp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$rtp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
								if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getDepartmentSelectionType(), $rtp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $rtp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
									//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $rtp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$rtp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
									$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
									if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getJobGroupSelectionType(), NULL, $job_group, $rtp_obj->getJobGroup() ) ) {
										//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $rtp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
										if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getJobSelectionType(), $rtp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $rtp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
											//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $rtp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
											$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
											if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $rtp_obj->getJobItemGroup() ) ) {
												//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $rtp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
												if ( $this->contributing_shift_policy[$rtp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $rtp_obj->getJobItemSelectionType(), $rtp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $rtp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
													//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $rtp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
													$create_udt_record = TRUE;
												}
											}
										}
									}
								}
							} else {
								Debug::text('Branch Selection is disabled! Branch Selection Type: '. $rtp_obj->getBranchSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							$create_udt_record = TRUE;
						}

						if ( $create_udt_record == TRUE ) {
							//No need to pro-rate regular time, as calculating regular/overtime exclusivity will handle this.
							if ( $maximum_time > 0 AND ( $covered_time + $udt_obj->getTotalTime() ) > $maximum_time ) {
								$total_time = ( $maximum_time - $covered_time );

								$total_time_percent = ( $total_time / $udt_obj->getTotalTime() );
								$quantity = round( ( $udt_obj->getQuantity() * $total_time_percent ), 2);
								$bad_quantity = round( ( $udt_obj->getBadQuantity() * $total_time_percent ), 2);
								$break_loop = TRUE;
								Debug::text('  Reached maximum time, calculate percent of quantities... Used Regular Time: '. $covered_time .' Percent: '. $total_time_percent, __FILE__, __LINE__, __METHOD__, 10);
								Debug::text('  Percent Calculated: ID: '. $udt_obj->getID() .' Time: '. $total_time .' Pay Code: '. $rtp_obj->getPayCode() .' Quantity: '. $quantity .' Bad Quantity: '. $bad_quantity, __FILE__, __LINE__, __METHOD__, 10);

								unset($total_time_percent);
							} else {
								$total_time = $udt_obj->getTotalTime();
								$quantity = $udt_obj->getQuantity();
								$bad_quantity = $udt_obj->getBadQuantity();
							}

							//Can't compact the data here, as that won't allow us to reference (pyramid) the time as each policy total time is calculated.
							//We will need to create the UserDateTotal objects, then compact them just before inserting...
							Debug::text('Generating UserDateTotal object from Regular Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 20 .' Pay Code ID: '. (int)$rtp_obj->getPayCode() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
							if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
								$udtf = TTnew( 'UserDateTotalFactory' );
								$udtf->setUser( $this->getUserObject()->getId() );
								$udtf->setDateStamp( $date_stamp );
								$udtf->setObjectType( 20 ); //Regular Time
								$udtf->setSourceObject( (int)$rtp_obj->getId() );
								if ( $i == $total_regular_time_policies ) {
									$udtf->setPayCode( 0 );
								} else {
									$udtf->setPayCode( (int)$rtp_obj->getPayCode() );
								}

								$udtf->setBranch( (int)$udt_obj->getBranch() );
								$udtf->setDepartment( (int)$udt_obj->getDepartment() );
								$udtf->setJob( (int)$udt_obj->getJob() );
								$udtf->setJobItem( (int)$udt_obj->getJobItem() );

								if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
									$udtf->setStartType( $udt_obj->getStartType() );
									$udtf->setStartTimeStamp( $udt_obj->getStartTimeStamp() );
									$udtf->setEndType( $udt_obj->getEndType() );
									$udtf->setEndTimeStamp( $udt_obj->getEndTimeStamp() );
								}

								$udtf->setQuantity( $quantity );
								$udtf->setBadQuantity( $bad_quantity );
								$udtf->setTotalTime( $total_time );

								$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $rtp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
								$udtf->setHourlyRate( $this->getHourlyRate( $rtp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
								$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $rtp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

								$udtf->setEnableCalcSystemTotalTime(FALSE);
								$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

								if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
									//Don't save the record, just add it to the existing array, so it can be included in other calculations.
									//We will save these records at the end.
									$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

									//Track the regular/absence exclusivity adjustments by linking the two records together.
									//Then once all regular time is calculated the absence time can be reduced accordingly.
									$this->regular_time_exclusivity_map[] = array( $udt_key => $this->user_date_total_insert_id );
									Debug::text('        Queuing reduction of Absence UDT Key: '. $udt_key .'('.$this->user_date_total_insert_id.') from: '. $udt_obj->getTotalTime()  .' to: '. ( $udt_obj->getTotalTime() - $total_time ) .' Total: '. $udtf->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

									$this->user_date_total_insert_id--;
								}
								$udt_used_keys[] = $udt_key; //Always run this to prevent getting to the catch-all when override records exist.
							} else {
								Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
							}

							$covered_time += $total_time;

							if ( $break_loop === TRUE ) {
								break 2;
							}
						}
					}
				}

				next($regular_time_policies);
			}

			return TRUE;
		}

		Debug::text('No regular time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function RegularTimePolicySortByCalculationOrderAsc( $a, $b ) {
		if ( $a->getCalculationOrder() == $b->getCalculationOrder() ) {
			return 0;
		}

		return ( $a->getCalculationOrder() < $b->getCalculationOrder() ) ? (-1) : 1;
	}

	function filterRegularTimePolicy( $date_stamp ) {
		$rtplf = $this->regular_time_policy;
		if ( is_array( $rtplf ) AND count( $rtplf ) > 0 ) {
			$schedule_policy_regular_time_policy_ids = array();
			$schedule_policy_exclude_regular_time_policy_ids = array();
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( is_array( $sp_obj->getIncludeRegularTimePolicy() ) AND count( $sp_obj->getIncludeRegularTimePolicy() ) > 0 ) {
						$schedule_policy_regular_time_policy_ids = array_merge( $schedule_policy_regular_time_policy_ids, (array)$sp_obj->getIncludeRegularTimePolicy() );
					}
					if ( is_array( $sp_obj->getExcludeRegularTimePolicy() ) AND count( $sp_obj->getExcludeRegularTimePolicy() ) > 0 ) {
						$schedule_policy_exclude_regular_time_policy_ids = array_merge( $schedule_policy_exclude_regular_time_policy_ids, (array)$sp_obj->getExcludeRegularTimePolicy() );
					}
				}
				Debug::Arr($schedule_policy_regular_time_policy_ids, 'Regular Time Policies that apply to: '. TTDate::getDate('DATE', $date_stamp) .' from schedule policies: ', __FILE__, __LINE__, __METHOD__, 10);
			}

			foreach( $rtplf as $rtp_obj ) {
				//FIXME: Check contributing shift start/end date so we can quickly filter out regular time policies that may never apply. Similar to what we do with premium policies.
				if (
						(
							( (int)$rtp_obj->getColumn('is_policy_group') == 1 AND !in_array( $rtp_obj->getId(), $schedule_policy_exclude_regular_time_policy_ids ) )
							OR
							( (int)$rtp_obj->getColumn('is_policy_group') == 0 AND in_array( $rtp_obj->getId(), $schedule_policy_regular_time_policy_ids ) )
						)
					) {
					$retarr[$rtp_obj->getId()] = $rtp_obj;
				}
			}

			//Since we have included/excluded additional policies, we need to resort them again so they are in the proper order.
			uasort( $retarr, array( $this, 'RegularTimePolicySortByCalculationOrderAsc' ) );

			if ( isset($retarr) ) {
				Debug::text('Found regular time policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No regular time policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all regulartime policies that could possibly apply, including from schedule policies.
	function getRegularTimePolicy() {
		$this->schedule_regular_time_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			foreach( $splf as $sp_obj ) {
				//Don't handle excludeRegularTimePolicy() here, as we need to get all possible policy IDs that could come into play, then just ignore them in filterRegularTimePolicy()
				if ( is_array( $sp_obj->getIncludeRegularTimePolicy() ) AND count( $sp_obj->getIncludeRegularTimePolicy() ) > 0 ) {
					$schedule_regular_time_policy_ids = array_merge( $this->schedule_regular_time_policy_ids, (array)$sp_obj->getIncludeRegularTimePolicy() );
				}
			}
			unset($sp_obj);
		}

		$rtplf = TTnew( 'RegularTimePolicyListFactory' );
		$rtplf->getByPolicyGroupUserIdOrId( $this->getUserObject()->getId(), $this->schedule_regular_time_policy_ids );
		if ( $rtplf->getRecordCount() > 0 ) {
			Debug::text('Found regular time policy rows: '. $rtplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $rtplf as $rtp_obj ) {
				$this->regular_time_policy[$rtp_obj->getId()] = $rtp_obj;
			}

			return TRUE;
		}

		Debug::text('No regular time policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//This must be done after all other policies are calculated so any average calculations can include premium time wages.
	function calculateOverTimeHourlyRates( $user_date_total_records ) {
		if ( is_array($user_date_total_records) AND count($user_date_total_records) > 0 ) {
			Debug::text('Calculating Overtime Hourly Rates...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $user_date_total_records as $key => $udt_obj ) {
				if ( $key < 0 AND $udt_obj->getTotalTime() > 0 AND $udt_obj->getObjectType() == 30 ) {
					//Debug::text('  Calculating UserDateTotal Entry!', __FILE__, __LINE__, __METHOD__, 10);

					//Only recalculate rates if we are actually using averaging. Otherwise we calculate them in calculateOverTime() instead.
					if ( $this->isPayFormulaPolicyAveraging( $this->over_time_policy[$udt_obj->getSourceObject()]->getPayFormulaPolicy(), $udt_obj->getPayCode() ) ) {
						$udt_obj->setHourlyRate( $this->getHourlyRate( $this->over_time_policy[$udt_obj->getSourceObject()]->getPayFormulaPolicy(), $udt_obj->getPayCode(), $this->getBaseHourlyRate( $this->over_time_policy[$udt_obj->getSourceObject()]->getPayFormulaPolicy(), $udt_obj->getPayCode(), $udt_obj->getDateStamp(), $udt_obj->getBaseHourlyRate(), $this->contributing_shift_policy[$this->over_time_policy[$udt_obj->getSourceObject()]->getContributingShiftPolicy()], array( 20, 25, 100, 110 ) ) ) );
						$udt_obj->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $this->over_time_policy[$udt_obj->getSourceObject()]->getPayFormulaPolicy(), $udt_obj->getPayCode(), $udt_obj->getDateStamp(), $udt_obj->getBaseHourlyRate() ) );

						$udt_obj->setEnableCalcSystemTotalTime(FALSE);
						$udt_obj->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.
					}
				}
				//else {
					//Debug::text('  Skipping... UserDateTotal Entry! Key: '. $key .' ObjectType: '. $udt_obj->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}

			return TRUE;
		}

		Debug::text('No UserDateTotal entries to calculate wages for...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	
	//Overtime policies need to be nested...
	//Since they are basically like saying: Any time after Xhrs/day goes to this OT policy. If some time is filtered out, it simply applies to the next OT policy.
	//So the first OT policy should have almost all time applied to it, then the next policy simply moves time from the prior OT policy into itself, rinse and repeat...
	function calculateOverTimePolicyForTriggerTime( $date_stamp, $current_trigger_time_arr ) {
		if ( isset($this->over_time_policy[$current_trigger_time_arr['over_time_policy_id']]) ) {
			$current_trigger_time_arr_trigger_time = $current_trigger_time_arr['trigger_time'];

			$otp_obj = $this->over_time_policy[$current_trigger_time_arr['over_time_policy_id']];
			Debug::text('OverTime Policy: '. $otp_obj->getName() .' Pay Code: '. $otp_obj->getPayCode() .' Trigger Time: '. $current_trigger_time_arr_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

			if ( !isset($this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]) ) {
				Debug::text('  ERROR: Contributing Shift Policy for OverTime Policy: '. $otp_obj->getName() .' does not exist...', __FILE__, __LINE__, __METHOD__, 10);
				continue;
			}

			$user_date_total_rows = $this->compactMealAndBreakUserDateTotalObjects( $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], array( 20, 25, 100, 110 ) ) );

			//Because some overtime policies may or may not include absence time, we need to recalculate the "maximum" time
			//As the maximum time passed into this function always includes absence time.
			$maximum_daily_total_time = $this->getSumUserDateTotalData( $user_date_total_rows );
			Debug::text('  bMaximum Possible Over Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array($user_date_total_rows) ) {
				//Set the start/end timestamps for each OT policy. This needs to be done on every OT policy/trigger_time_arr element.
				//As the contributing shifts can differ for each.
				//So these can change with each loop, which may seem confusing, but the trigger_time itself won't change.
				$current_trigger_time_arr['start_time_stamp'] = ( $user_date_total_rows[key($user_date_total_rows)]->getStartTimeStamp() + $current_trigger_time_arr['trigger_time'] );
				Debug::text('  Current Trigger TimeStamp: '. TTDate::getDate('DATE+TIME', $current_trigger_time_arr['start_time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10);

				Debug::text('  Total UDT Rows: '. count( $user_date_total_rows ), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
					//Debug::text('  UDT Row KEY: '. $udt_key .' ID: '. $udt_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

					//Detect gap between UDT end -> start timestamps so we can adjust accordingly.
					if ( isset($prev_udt_obj)
							AND $prev_udt_obj->getStartTimeStamp() != $udt_obj->getStartTimeStamp() //Make sure its not the same record.
							AND $prev_udt_obj->getEndTimeStamp() != $udt_obj->getStartTimeStamp() ) {
						$current_trigger_time_arr['start_time_stamp'] += ( $udt_obj->getStartTimeStamp() - $prev_udt_obj->getEndTimeStamp() );
						Debug::text('    Found gap between UDT records, either a split shift, lunch or break, adjusting next start time... Prev End: '. TTDate::getDate('DATE+TIME', $prev_udt_obj->getEndTimeStamp() ) .' Current Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
					}

					//This must be below the gap detection/adjustment above.
					if	( isset($this->over_time_recurse_map[$udt_key]) ) {
						Debug::text('  Found recursive key, swapping UDT record... UDT Row KEY: '. $udt_key .' ID: '. $udt_obj->getId() .' New ID: '. $this->over_time_recurse_map[$udt_key], __FILE__, __LINE__, __METHOD__, 10);
						$udt_obj = $this->user_date_total[$this->over_time_recurse_map[$udt_key]];
					}
					
					$udt_overlap_time = TTDate::getTimeOverLapDifference( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $current_trigger_time_arr['start_time_stamp'], $udt_obj->getEndTimeStamp() );
					Debug::text('    aID: '. (int)$udt_obj->getID() .' Total Time: '. $udt_obj->getTotalTime() .' Quantity: '. $udt_obj->getQuantity() .' Bad Quantity: '. $udt_obj->getBadQuantity() .' Overlap Time: '. $udt_overlap_time .' Start Time: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End Time: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					if ( $udt_overlap_time > 0 ) {
						Debug::text('      UDT Overlaps with Trigger Time...', __FILE__, __LINE__, __METHOD__, 10);

						if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
							$create_udt_record = FALSE;
							if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getBranchSelectionType(), $otp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $otp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
								//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $otp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$otp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
								if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getDepartmentSelectionType(), $otp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $otp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
									//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $otp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$otp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
									$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
									if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobGroupSelectionType(), NULL, $job_group, $otp_obj->getJobGroup() ) ) {
										//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $otp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
										if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobSelectionType(), $otp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $otp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
											//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $otp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
											$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
											if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $otp_obj->getJobItemGroup() ) ) {
												//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $otp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
												if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobItemSelectionType(), $otp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $otp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
													//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $otp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
													$create_udt_record = TRUE;
												}
											}
										}
									}
								}
							} else {
								Debug::text('      Branch Selection is disabled! Branch Selection Type: '. $otp_obj->getBranchSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							$create_udt_record = TRUE;
						}

						if ( $create_udt_record == TRUE ) {
							if ( !isset($this->over_time_trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time]) OR ( isset($this->over_time_trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time]) AND in_array($udt_key, $this->over_time_trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time] ) == FALSE ) ) {
								//Pro-Rate quantities based on overlap time.
								//Calculate percent that applies to overtime initially.
								$udt_total_time_percent = ( $udt_obj->getTotalTime() > 0 ) ? ( $udt_overlap_time / $udt_obj->getTotalTime() ) : 1; //Make sure we avoid a division by 0 here, it may happen during drag&drop.
								$udt_quantity = round( ( $udt_obj->getQuantity() * $udt_total_time_percent ), 2);
								$udt_bad_quantity = round( ( $udt_obj->getBadQuantity() * $udt_total_time_percent ), 2);
								Debug::text('        Split user_date_total when overlapping overtime: Time: '. $udt_overlap_time .' Percent: '. $udt_total_time_percent .' Quantity: '. $udt_quantity .' Bad Quantity: '. $udt_bad_quantity, __FILE__, __LINE__, __METHOD__, 10);
								unset($udt_total_time_percent);

								//Can't compact the data here, as that won't allow us to reference (pyramid) the time as each policy total time is calculated.
								//We will need to create the UserDateTotal objects, then compact them just before inserting...
								Debug::text('          Generating UserDateTotal object from OverTime Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 30 .' Pay Code ID: '. (int)$otp_obj->getPayCode() .' Total Time: '. $udt_overlap_time .' Original Hourly Rate: '. $udt_obj->getHourlyRate(), __FILE__, __LINE__, __METHOD__, 10);
								if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
									$udtf = TTnew( 'UserDateTotalFactory' );
									$udtf->setUser( $this->getUserObject()->getId() );
									$udtf->setDateStamp( $date_stamp );
									$udtf->setObjectType( 30 ); //Overtime
									$udtf->setSourceObject( $otp_obj->getId() );
									$udtf->setPayCode( (int)$otp_obj->getPayCode() );

									$udtf->setBranch( (int)$udt_obj->getBranch() );
									$udtf->setDepartment( (int)$udt_obj->getDepartment() );
									$udtf->setJob( (int)$udt_obj->getJob() );
									$udtf->setJobItem( (int)$udt_obj->getJobItem() );

									//Make sure we set start/end timestamps for overtime, so its easier to diagnose problems.
									//However when including absences in OT it makes that difficult.
									//This is required to properly handle Premium Policy that is based on Date/Times (ie: Evening shifts, when multiple overtime policies are applied like on a holiday.)
									if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
										$udtf->setStartType( $udt_obj->getStartType() );
										$udtf->setEndType( $udt_obj->getEndType() );

										$udt_overlap_arr = TTDate::getTimeOverLap( $current_trigger_time_arr['start_time_stamp'], $udt_obj->getEndTimeStamp(), $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp() );
										$udtf->setStartTimeStamp( $udt_overlap_arr['start_date'] );
										$udtf->setEndTimeStamp( $udt_overlap_arr['end_date'] );
										//Debug::text('        Current Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ) .' Covered Time: '. $covered_time .' Adjust: '. $adjust_covered_time .' Overlap: '. $udt_overlap_time, __FILE__, __LINE__, __METHOD__, 10);
										//Debug::text('        OT Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udtf->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udtf->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
										unset($udt_overlap_arr);
									}

									$udtf->setQuantity( $udt_quantity );
									$udtf->setBadQuantity( $udt_bad_quantity );
									$udtf->setTotalTime( $udt_overlap_time );

									//If the BaseRate is the average regular rate, each time its calculated it will continue to increase.
									//So we need to make an exception for overtime policies where the base rate is the base regular time rate instead.
									$udtf->setBaseHourlyRate( $udt_obj->getHourlyRate() );

									//Calculate HourlyRate/HourlyRateWithBurden so Premium Policies can be based off these amounts.
									//If they happen to be averaging rates, we will recalculate those later in calculateOverTimeHourlyRates().
									$udtf->setHourlyRate( $this->getHourlyRate( $otp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
									$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $otp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

									$udtf->setEnableCalcSystemTotalTime(FALSE);
									$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

									if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
										//Don't save the record, just add it to the existing array, so it can be included in other calculations.
										//We will save these records at the end.
										$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

										if ( (int)$udt_obj->getID() == 0 AND ( $udt_obj->getObjectType() == 20 OR $udt_obj->getObjectType() == 25 OR $udt_obj->getObjectType() == 30 ) ) {
											//Since we reduce the source UDT record immediately here, we need a pointer to it rather than a copy.
											//If we didn't get a pointer to it near the top of this loop, get it here.
											if ( !isset($this->over_time_recurse_map[$udt_key]) ) {
												$udt_obj = $this->user_date_total[$udt_key];
											}

											$this->over_time_recurse_map[$udt_key] = $this->user_date_total_insert_id;
											Debug::text('        Reducing source recursive UDT row... ID: '. (int)$udt_obj->getId() .' KEY: '. $udt_key .' row EndTimeStamp: '. $udt_obj->getEndTimeStamp() .'/'. ( $udt_obj->getEndTimeStamp() - $udt_overlap_time ) .' TotalTime: '. $udt_obj->getTotalTime() .'/'. ( $udt_obj->getTotalTime() - $udt_overlap_time ) .' Object Type: '. $udt_obj->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);
											
											$udt_obj->setEndTimeStamp( ( $udt_obj->getEndTimeStamp() - $udt_overlap_time ) );
											$udt_obj->setQuantity( ( $udt_obj->getQuantity() - $udt_quantity ) );
											$udt_obj->setBadQuantity( ( $udt_obj->getBadQuantity() - $udt_bad_quantity ) );
											$udt_obj->setTotalTime( ( $udt_obj->getTotalTime() - $udt_overlap_time ) );
											$udt_obj->setEnableCalcSystemTotalTime(FALSE);
											$udt_obj->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.
										}

										$this->over_time_trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time][] = $udt_key;
										$this->user_date_total_insert_id--;
									}
								} else {
									Debug::text('      ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::text('      Skipping UDT row due to trigger time exclusivity...', __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							Debug::text('      Skipping UDT row due to Differential Criteria...', __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::text('      UDT does NOT Overlap with Trigger Time, not on last policy of same trigger time...', __FILE__, __LINE__, __METHOD__, 10);
					}

					$prev_udt_obj = $udt_obj;
				}
			}
		} else {
			Debug::text('ERROR: Unable to find over time policy ID: '. $current_trigger_time_arr['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}
	
	function calculateOverTimePolicy( $date_stamp, $trigger_time_arr, $maximum_daily_total_time = NULL ) {
		//1. Loop through each trigger_time_arr, as that will contain all the overtime policies that should apply to this date.
		$total_over_time_policies = count($trigger_time_arr);
		if ( $total_over_time_policies > 0 AND is_array($trigger_time_arr) AND count($trigger_time_arr) ) {
			Debug::text('Maximum Possible Over Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

			$this->over_time_trigger_time_exclusivity_map = array();
			$this->over_time_recurse_map = array();
			$trigger_time_arr_keys = array_keys( $trigger_time_arr );
			foreach( $trigger_time_arr_keys as $key => $trigger_time_arr_trigger_time ) {
				$current_trigger_time_arr_trigger_time = $trigger_time_arr_keys[$key];
				foreach( $trigger_time_arr[$current_trigger_time_arr_trigger_time] as $key_b => $current_trigger_time_arr ) {
					$this->calculateOverTimePolicyForTriggerTime( $date_stamp, $current_trigger_time_arr );
				}
			}

			return TRUE;
		}

		Debug::text('No over time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

/*
 	function calculateOverTimeExclusivity() {
		//Loop through the exclusivity map and reduce regular time records by the amount of the linked overtime record.
		if ( is_array( $this->over_time_exclusivity_map ) AND count( $this->over_time_exclusivity_map ) > 0 ) {
			foreach( $this->over_time_exclusivity_map as $exclusivity_data ) {
				foreach( $exclusivity_data as $regular_udt_key => $ot_udt_key ) {
					//Debug::text('Regular UDT Key '. $regular_udt_key .' OT Key: '. $ot_udt_key, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $this->user_date_total[$regular_udt_key] ) AND isset( $this->user_date_total[$ot_udt_key] ) ) {
						$udt_obj = $this->user_date_total[$regular_udt_key];
						$ot_udt_obj = $this->user_date_total[$ot_udt_key];

						Debug::text('Regular UDT Total Time: '. $udt_obj->getTotalTime() .' OT Total Time: '. $ot_udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
						if ( $udt_obj->getObjectType() == 20 OR $udt_obj->getObjectType() == 25 ) { //Regular Time or Absence
							$udt_obj->setTotalTime( ( $udt_obj->getTotalTime() - $ot_udt_obj->getTotalTime() ) );
							$udt_obj->setQuantity( ( $udt_obj->getQuantity() - $ot_udt_obj->getQuantity() ) );
							$udt_obj->setBadQuantity( ( $udt_obj->getBadQuantity() - $ot_udt_obj->getBadQuantity() ) );

							if ( $udt_obj->getEndTimeStamp() != '' ) {
								$udt_obj->setEndTimeStamp( ( $udt_obj->getEndTimeStamp() - $ot_udt_obj->getTotalTime() ) );
							}

							$udt_obj->preSave(); //Calculate TotalTimeAmount.
							Debug::text('  Reducing Regular Time to: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

							//Don't leave records with total_time=0 around, as it can affect premium policies
							//specifically minimum time premium policies as they only take effect on the last record, and a 0 record is ignored.
							if ( $udt_obj->getTotalTime() == 0 ) {
								Debug::text('    Regular Time is 0, removing record completely: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
								unset($this->user_date_total[$regular_udt_key]);
							}
						} else {
							Debug::text('ERROR: UDT Records isnt regular time, unable to adjust for exclusivity. Object Type: '. $udt_obj->getObjectType() .' Pay Code: '. $udt_obj->getPayCode(), __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::text('ERROR: UDT Records dont exist, unable to adjust for exclusivity. Reg UDT Key: '. $regular_udt_key .' OT UDT Key: '. $ot_udt_key, __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
			unset($udt_obj, $ot_udt_obj);

			$this->over_time_exclusivity_map = NULL; //Make sure this reset each time this is run.

			return TRUE;
		}

		Debug::text('No exclusivity records to calculate!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//FIXME: Need to figure out how to handle:
	//   - Cases where multiple OT policies with the same trigger time exist, but they are filtered by Differential Criteria.
	//		Currently only one can ever be calculated, because processTriggerTime() only returns one. (PEO, GreenA could both use this so employees can choose when/what overtime applies.)
	//   - Cases where Contributing Shift Policy includes only partial shifts, and make overtime cover 12:00AM - 11:59PM only on a specific day (ie: Holiday) [active after 0] (Rev. could use this)
	//		Combine this with above to get different OT policies to apply on different holidays (Xmas/New Years), one being 1.5x and another 2.0x for example.
	function calculateOverTimePolicy( $date_stamp, $trigger_time_arr, $maximum_daily_total_time = NULL ) {
		//1. Loop through each trigger_time_arr, as that will contain all the overtime policies that should apply to this date.
		//2. Determine the start/end time that the overtime policy applies for (current trigger time and next trigger time)
		//3. Filter user_date_total data based on that overtime policy, and only apply up to the total amount of time available. (with pro-rating)
		$total_over_time_policies = count($trigger_time_arr);
		if ( $total_over_time_policies > 0 AND is_array($trigger_time_arr) AND count($trigger_time_arr) ) {
			Debug::text('aMaximum Possible Over Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
			
			$trigger_time_exclusivity_map = array();
			$trigger_time_arr_keys = array_keys( $trigger_time_arr );
			$covered_time = 0;
			$i = 0;
			foreach( $trigger_time_arr_keys as $key => $trigger_time_arr_trigger_time ) {
				$break_out_of_trigger_time = FALSE;

				$first_trigger_time_arr_trigger_time = $trigger_time_arr_keys[0];
				$current_trigger_time_arr_trigger_time = $trigger_time_arr_keys[$key];
				if ( isset($trigger_time_arr_keys[($key + 1)]) ) {
					$next_trigger_time_arr_trigger_time = $trigger_time_arr_keys[($key + 1)];
				} else {
					$next_trigger_time_arr_trigger_time = FALSE;
				}

				$x = 0;
				$max_x = ( count($trigger_time_arr[$current_trigger_time_arr_trigger_time]) - 1 );
				$used_time_per_policy = 0; //This is actually used time at each trigger time, not per policy. Required to handle duplicate policies at the same trigger time properly.
				$start_covered_time = $covered_time; //Store the covered_time before each trigger time starts, so if a policy isn't calculated at all we can revert back to it for the next one.
				foreach( $trigger_time_arr[$current_trigger_time_arr_trigger_time] as $key_b => $current_trigger_time_arr ) {
					if ( isset($this->over_time_policy[$current_trigger_time_arr['over_time_policy_id']]) ) {
						//$used_time_per_policy = $start_trigger_time_used_time_per_policy; //When processing each policy, reset user_time_per_policy for that trigger time.
						
						$otp_obj = $this->over_time_policy[$current_trigger_time_arr['over_time_policy_id']];
						Debug::text('OverTime Policy: '. $otp_obj->getName() .' Pay Code: '. $otp_obj->getPayCode() .' Trigger Time: '. $trigger_time_arr_trigger_time .' KeyB: '. $key_b, __FILE__, __LINE__, __METHOD__, 10);

						if ( !isset($this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]) ) {
							Debug::text('  ERROR: Contributing Shift Policy for OverTime Policy: '. $otp_obj->getName() .' does not exist...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}

						$user_date_total_rows = $this->compactMealAndBreakUserDateTotalObjects( $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], array( 20, 25, 100, 110 ) ) );

						//Because some overtime policies may or may not include absence time, we need to recalculate the "maximum" time
						//As the maximum time passed into this function always includes absence time.
						$maximum_daily_total_time = $this->getSumUserDateTotalData( $user_date_total_rows );
						Debug::text('  bMaximum Possible Over Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

						if ( $next_trigger_time_arr_trigger_time !== FALSE AND isset($trigger_time_arr[$next_trigger_time_arr_trigger_time]) ) {
							$next_trigger_time_arr = $trigger_time_arr[$next_trigger_time_arr_trigger_time][0];
							$maximum_time = ( $next_trigger_time_arr['trigger_time'] - $current_trigger_time_arr['trigger_time'] );
							Debug::text('  Current Trigger Time: '. $current_trigger_time_arr['trigger_time'] .' Next Trigger Time: '. $next_trigger_time_arr['trigger_time'] .' Maximum Time: '. $maximum_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							$next_trigger_time_arr = array(
															'calculation_order' => 99999999, //Don't change this as its used later on to determine if we're on the last OT policy
															'trigger_time' => $maximum_daily_total_time,
															'over_time_policy_id' => FALSE,
															'over_time_policy_type_id' => FALSE,
															'contributing_shift_policy_id' => FALSE,
															'pay_code_id' => FALSE,
															'combined_rate' => 99999999,
															);
							$maximum_time = ( $maximum_daily_total_time - $covered_time );
							Debug::text('  Current Trigger Time: '. $current_trigger_time_arr['trigger_time'] .' Next Trigger Time: NONE Maximum Time: '. $maximum_time .' Covered Time: '. $covered_time, __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($maximum_time); //It isn't needed again other than for debugging purposes.

						if ( is_array($user_date_total_rows) ) {
							//Set the start/end timestamps for each OT policy. This needs to be done on every OT policy/trigger_time_arr element.
							//As the contributing shifts can differ for each.
							//So these can change with each loop, which may seem confusing, but the trigger_time itself won't change.
							$current_trigger_time_arr['start_time_stamp'] = ( $user_date_total_rows[key($user_date_total_rows)]->getStartTimeStamp() + $current_trigger_time_arr['trigger_time'] );
							$next_trigger_time_arr['start_time_stamp'] = ( $user_date_total_rows[key($user_date_total_rows)]->getStartTimeStamp() + $next_trigger_time_arr['trigger_time'] );
							Debug::text('  Current Trigger TimeStamp: '. TTDate::getDate('DATE+TIME', $current_trigger_time_arr['start_time_stamp'] ) .' Next Trigger TimeStamp: '. TTDate::getDate('DATE+TIME', $next_trigger_time_arr['start_time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10);

							$inserted_user_date_total_row = FALSE;
							$n = 0;
							$max_n = ( count( $user_date_total_rows ) - 1 );
							Debug::text('  Total UDT Rows: '. count( $user_date_total_rows ) , __FILE__, __LINE__, __METHOD__, 10);
							foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
								//Debug::text('  UDT Row KEY: '. $udt_key .' ID: '. $udt_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

								//Detect gap between UDT end -> start timestamps so we can adjust accordingly.
								if ( isset($prev_udt_obj)
										AND $prev_udt_obj->getStartTimeStamp() != $udt_obj->getStartTimeStamp() //Make sure its not the same record.
										AND $prev_udt_obj->getEndTimeStamp() != $udt_obj->getStartTimeStamp() ) {
									$current_trigger_time_arr['start_time_stamp'] += ( $udt_obj->getStartTimeStamp() - $prev_udt_obj->getEndTimeStamp() );
									Debug::text('    Found gap between UDT records, either a split shift, lunch or break, adjusting next start time... Prev End: '. TTDate::getDate('DATE+TIME', $prev_udt_obj->getEndTimeStamp() ) .' Current Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
								}

								//If we are on the last OT policy, make the StartTimeStamp always match the end timestamp of the UDT record.
								//Because using just trigger_time doesn't account for gaps between shifts or lunch/break time.
								if ( $next_trigger_time_arr['calculation_order'] == 99999999 ) {
									$next_trigger_time_arr['start_time_stamp'] = $udt_obj->getEndTimeStamp();
								}

								if ( $x == $max_x AND ( $used_time_per_policy + $udt_obj->getTotalTime() ) < $covered_time ) {
									$used_time_per_policy += $udt_obj->getTotalTime();
									Debug::text('    Used time for this policy hasnt exceeded covered time yet... Covered Time: '. $covered_time .' Used Time Per Policy: '. $used_time_per_policy .' UDT ID: '. $udt_obj->getID() .' Total Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
									$n++;
									continue;
								}

								if ( $used_time_per_policy > $covered_time ) {
									$udt_start_time = $covered_time;
								} else {
									$udt_start_time = $used_time_per_policy;
								}
								$udt_end_time = ( $udt_start_time + $udt_obj->getTotalTime() );
								$udt_overlap_time = TTDate::getTimeOverLapDifference( $udt_start_time, $udt_end_time, $current_trigger_time_arr['trigger_time'], $next_trigger_time_arr['trigger_time'] );
								Debug::text('    aID: '. (int)$udt_obj->getID() .' Time: '. $udt_obj->getTotalTime() .' Quantity: '. $udt_obj->getQuantity() .' Bad Quantity: '. $udt_obj->getBadQuantity() .' Covered Time: '. $covered_time .' UDT Start: '. $udt_start_time .'('. TTDate::getHours( $udt_start_time ) .') End: '. $udt_end_time .'('. TTDate::getHours( $udt_end_time ) .') Overlap Time: '. $udt_overlap_time, __FILE__, __LINE__, __METHOD__, 10);

								$adjust_covered_time = 0;
								if ( $udt_overlap_time > 0 ) {
									Debug::text('      UDT Overlaps with Trigger Time...', __FILE__, __LINE__, __METHOD__, 10);

									if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
										$create_udt_record = FALSE;
										if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getBranchSelectionType(), $otp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $otp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
											//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $otp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$otp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
											if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getDepartmentSelectionType(), $otp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $otp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
												//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $otp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$otp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
												$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
												if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobGroupSelectionType(), NULL, $job_group, $otp_obj->getJobGroup() ) ) {
													//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $otp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
													if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobSelectionType(), $otp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $otp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
														//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $otp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
														$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
														if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $otp_obj->getJobItemGroup() ) ) {
															//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $otp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
															if ( $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $otp_obj->getJobItemSelectionType(), $otp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $otp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
																//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $otp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
																$create_udt_record = TRUE;
															}
														}
													}
												}
											}
										} else {
											Debug::text('      Branch Selection is disabled! Branch Selection Type: '. $otp_obj->getBranchSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										$create_udt_record = TRUE;
									}

									if ( $create_udt_record == TRUE ) {
										if ( !isset($trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time]) OR ( isset($trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time]) AND in_array($udt_key, $trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time] ) == FALSE ) ) {
											//Pro-Rate quantities based on overlap time.
											//Calculate percent that applies to overtime initially.
											$udt_total_time_percent = ( $udt_overlap_time / $udt_obj->getTotalTime() );
											$udt_quantity = round( ( $udt_obj->getQuantity() * $udt_total_time_percent ), 2);
											$udt_bad_quantity = round( ( $udt_obj->getBadQuantity() * $udt_total_time_percent ), 2);
											Debug::text('        Split user_date_total when overlapping overtime: Time: '. $udt_overlap_time .' Percent: '. $udt_total_time_percent .' Quantity: '. $udt_quantity .' Bad Quantity: '. $udt_bad_quantity, __FILE__, __LINE__, __METHOD__, 10);
											unset($udt_total_time_percent);

											//Can't compact the data here, as that won't allow us to reference (pyramid) the time as each policy total time is calculated.
											//We will need to create the UserDateTotal objects, then compact them just before inserting...
											Debug::text('          Generating UserDateTotal object from OverTime Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 30 .' Pay Code ID: '. (int)$otp_obj->getPayCode() .' Total Time: '. $udt_overlap_time .' Original Hourly Rate: '. $udt_obj->getHourlyRate(), __FILE__, __LINE__, __METHOD__, 10);
											if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
												$udtf = TTnew( 'UserDateTotalFactory' );
												$udtf->setUser( $this->getUserObject()->getId() );
												$udtf->setDateStamp( $date_stamp );
												$udtf->setObjectType( 30 ); //Overtime
												$udtf->setSourceObject( $otp_obj->getId() );
												$udtf->setPayCode( (int)$otp_obj->getPayCode() );

												$udtf->setBranch( (int)$udt_obj->getBranch() );
												$udtf->setDepartment( (int)$udt_obj->getDepartment() );
												$udtf->setJob( (int)$udt_obj->getJob() );
												$udtf->setJobItem( (int)$udt_obj->getJobItem() );

												//Make sure we set start/end timestamps for overtime, so its easier to diagnose problems.
												//However when including absences in OT it makes that difficult.
												//This is required to properly handle Premium Policy that is based on Date/Times (ie: Evening shifts, when multiple overtime policies are applied like on a holiday.)
												if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
													$udtf->setStartType( $udt_obj->getStartType() );
													$udtf->setEndType( $udt_obj->getEndType() );

													$udt_overlap_arr = TTDate::getTimeOverLap( $current_trigger_time_arr['start_time_stamp'], $next_trigger_time_arr['start_time_stamp'], $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp() );
													$udtf->setStartTimeStamp( $udt_overlap_arr['start_date'] );
													$udtf->setEndTimeStamp( $udt_overlap_arr['end_date'] );
													//Debug::text('        Current Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ) .' Covered Time: '. $covered_time .' Adjust: '. $adjust_covered_time .' Overlap: '. $udt_overlap_time, __FILE__, __LINE__, __METHOD__, 10);
													//Debug::text('        OT Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udtf->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udtf->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
													unset($udt_overlap_arr);
												}

												$udtf->setQuantity( $udt_quantity );
												$udtf->setBadQuantity( $udt_bad_quantity );
												$udtf->setTotalTime( $udt_overlap_time );

												//If the BaseRate is the average regular rate, each time its calculated it will continue to increase.
												//So we need to make an exception for overtime policies where the base rate is the base regular time rate instead.
												$udtf->setBaseHourlyRate( $udt_obj->getHourlyRate() );

												//Calculate HourlyRate/HourlyRateWithBurden so Premium Policies can be based off these amounts.
												//If they happen to be averaging rates, we will recalculate those later in calculateOverTimeHourlyRates().
												$udtf->setHourlyRate( $this->getHourlyRate( $otp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
												$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $otp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

												$udtf->setEnableCalcSystemTotalTime(FALSE);
												$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

												if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
													//Don't save the record, just add it to the existing array, so it can be included in other calculations.
													//We will save these records at the end.
													$this->user_date_total[$this->user_date_total_insert_id] = $udtf;

													//Track the overtime exclusivity adjustments by linking the two records together.
													//Then once all overtime is calculated the regular time can be reduced accordingly.
													$this->over_time_exclusivity_map[] = array( $udt_key => $this->user_date_total_insert_id );
													$trigger_time_exclusivity_map[$current_trigger_time_arr_trigger_time][] = $udt_key;
													Debug::text('            Queuing reduction of Regular UDT Key: '. $udt_key .'('.$this->user_date_total_insert_id.') from: '. $udt_obj->getTotalTime()  .' to: '. ( $udt_obj->getTotalTime() - $udt_overlap_time ) .' Total: '. $udtf->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
													$this->user_date_total_insert_id--;

													$inserted_user_date_total_row = TRUE;
												}
											} else {
												Debug::text('      ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
											}
										} else {
											Debug::text('      Skipping UDT row due to trigger time exclusivity...'. $i, __FILE__, __LINE__, __METHOD__, 10);

											$n++;
											continue;
										}
									} else {
										Debug::text('      Skipping UDT row due to Differential Criteria... I: '. $i .' N: '. $n .'/'. $max_n .' X: '. $x .'/'. $max_x .' Used Time Per Trigger Time: '. $used_time_per_policy, __FILE__, __LINE__, __METHOD__, 10);

										if ( $i > 0 AND $x == $max_x AND $n == $max_n AND $used_time_per_policy < $current_trigger_time_arr['trigger_time'] ) {
											Debug::text('        Reached end of trigger time array without using any time, but a previous trigger time exists...', __FILE__, __LINE__, __METHOD__, 10);
										} elseif ( $x != $max_x AND $n == $max_n AND $inserted_user_date_total_row == FALSE ) {
											Debug::text('        zzz1...', __FILE__, __LINE__, __METHOD__, 10);
											$used_time_per_policy = 0;
											$covered_time = $start_covered_time;
										} else {
											Debug::text('        zzz2...', __FILE__, __LINE__, __METHOD__, 10);
										}

										$n++;
										continue;
									}

									if ( $udt_start_time < $current_trigger_time_arr['trigger_time'] AND $udt_end_time > $current_trigger_time_arr['trigger_time']
											AND $udt_end_time < $next_trigger_time_arr['trigger_time'] ) {
										Debug::text('UDT Row crosses into overtime policy and does not cross out...', __FILE__, __LINE__, __METHOD__, 10);
										if ( $covered_time <= $trigger_time_arr[$first_trigger_time_arr_trigger_time][0]['trigger_time'] ) {
											//Only until we have crossed into the first OT policy do we use the full getTotalTime() when crossing into an overtime policy
											//as the otherside is accounted for as regular time, not another OT policy.
											//On the 2+ OT policy the otherside is accounted for another OT policy, so we can't double up on it.
											//Especially imported for Daily+Weekly+Auto-Add break/meal policies all on the same day.
											$adjust_covered_time = $udt_obj->getTotalTime();
										} else {
											$adjust_covered_time = $udt_overlap_time;
										}
									} elseif ( $udt_start_time < $current_trigger_time_arr['trigger_time'] AND $udt_end_time > $next_trigger_time_arr['trigger_time']
												AND $covered_time <= $trigger_time_arr[$first_trigger_time_arr_trigger_time][0]['trigger_time'] ) {
										Debug::text('      UDT Row crosses into overtime policy and crosses out on the first OT policy... Trigger: '. $current_trigger_time_arr['trigger_time'], __FILE__, __LINE__, __METHOD__, 10);
										$adjust_covered_time = ( ( $current_trigger_time_arr['trigger_time'] + $udt_overlap_time ) - $covered_time );
									} else {
										$adjust_covered_time = $udt_overlap_time;
									}
								} elseif ( $x == $max_x ) {
									Debug::text('      UDT does NOT Overlap with Trigger Time...', __FILE__, __LINE__, __METHOD__, 10);

									//Don't increase covered time when there is no overlap and we have already crossed into the first overtime policy.
									if ( $covered_time <= $trigger_time_arr[$first_trigger_time_arr_trigger_time][0]['trigger_time'] ) {
										if ( $max_x > 0 ) {
											$adjust_covered_time = ( ( $current_trigger_time_arr['trigger_time'] + $udt_overlap_time ) - $covered_time );
										} else {
											$adjust_covered_time = $udt_obj->getTotalTime();
										}
									} else {
										$adjust_covered_time = 0; //This is required where Daily+Weekly+Auto-Add Meal/Break policies all happen on the same day.
									}
								} else {
									Debug::text('      UDT does NOT Overlap with Trigger Time, not on last policy of same trigger time...', __FILE__, __LINE__, __METHOD__, 10);
									$adjust_covered_time = ( ( $current_trigger_time_arr['trigger_time'] + $udt_overlap_time ) - $covered_time );
									//If there is no overlap, we still need to increase covered_time.
									//UnitTest: testDailyAndWeeklyOverTimePolicyC(), tests this case when a shift is split and the first half doesn't overlap any OT trigger time.
									if ( $adjust_covered_time > $udt_obj->getTotalTime() ) {
										$adjust_covered_time = $udt_obj->getTotalTime();
									}
								}

								$covered_time += $adjust_covered_time;
								$used_time_per_policy += $covered_time;
								Debug::text('      Covered Time: '. $covered_time .' Adjusted By: '. $adjust_covered_time .' Used Time Per Policy: '. $used_time_per_policy, __FILE__, __LINE__, __METHOD__, 10);

								$prev_udt_obj = $udt_obj;

								$n++;

								if ( $covered_time >= $maximum_daily_total_time ) {
									Debug::text('      bReached maximum daily time, done processing overtime policies...', __FILE__, __LINE__, __METHOD__, 10);
									break 2;
								}
							}
						}
					} else {
						Debug::text('ERROR: Unable to find over time policy ID: '. $current_trigger_time_arr['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
					}

					$i++;
					$x++;
					unset($next_trigger_time_arr);
				}
			}

			Debug::text('Covered Time: '. $covered_time .' Maximum Daily Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('No over time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
*/
	function filterOverTimePolicy( $date_stamp ) {
		$otplf = $this->over_time_policy;
		if ( is_array($otplf) AND count($otplf) > 0 ) {
			$schedule_policy_over_time_policy_ids = array();
			$schedule_policy_exclude_over_time_policy_ids = array();
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( is_array( $sp_obj->getIncludeOverTimePolicy() ) AND count( $sp_obj->getIncludeOverTimePolicy() ) > 0 ) {
						$schedule_policy_over_time_policy_ids = array_merge( $schedule_policy_over_time_policy_ids, (array)$sp_obj->getIncludeOverTimePolicy() );
					}
					if ( is_array( $sp_obj->getExcludeOverTimePolicy() ) AND count( $sp_obj->getExcludeOverTimePolicy() ) > 0 ) {
						$schedule_policy_exclude_over_time_policy_ids = array_merge( $schedule_policy_exclude_over_time_policy_ids, (array)$sp_obj->getExcludeOverTimePolicy() );
					}
				}
				Debug::Arr($schedule_policy_over_time_policy_ids, 'OverTime Policies that apply to: '. TTDate::getDate('DATE', $date_stamp) .' from schedule policies: ', __FILE__, __LINE__, __METHOD__, 10);
			}

			foreach( $otplf as $otp_obj ) {
				if (
						(
							( (int)$otp_obj->getColumn('is_policy_group') == 1 AND !in_array( $otp_obj->getId(), $schedule_policy_exclude_over_time_policy_ids ) )
							OR
							( (int)$otp_obj->getColumn('is_policy_group') == 0 AND in_array( $otp_obj->getId(), $schedule_policy_over_time_policy_ids ) )
						)
					) {
					$retarr[$otp_obj->getId()] = $otp_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found overtime policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No overtime policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getWeeklyOverTimePolicyPayCodes() {
		$weekly_over_time_pay_code_ids = array();
		$weekly_over_time_policies = $this->filterWeeklyOverTimePolicy();
		if ( is_array($weekly_over_time_policies) AND count($weekly_over_time_policies) > 0 ) {
			foreach( $weekly_over_time_policies as $otp_obj ) {
				$weekly_over_time_pay_code_ids[] = $otp_obj->getPayCode();
			}
		}
		unset($weekly_over_time_policies, $otp_obj);

		return $weekly_over_time_pay_code_ids;
	}
	
	//Get list of all weekly overtime policies so they can be included when calculating weekly time.
	function filterWeeklyOverTimePolicy() {
		$otplf = $this->over_time_policy;
		if ( is_array($otplf) AND count($otplf) > 0 ) {
			foreach( $otplf as $otp_obj ) {
				if ( in_array( $otp_obj->getType(), array(20, 30, 210) ) ) {
					$retarr[$otp_obj->getId()] = $otp_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found overtime policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No overtime policies apply on date...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Get all overtime policies that could possibly apply, including from schedule policies.
	function getOverTimePolicy() {
		$this->schedule_over_time_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			foreach( $splf as $sp_obj ) {
				if ( is_array( $sp_obj->getIncludeOverTimePolicy() ) AND count( $sp_obj->getIncludeOverTimePolicy() ) > 0 ) {
					$this->schedule_over_time_policy_ids = array_merge( $this->schedule_over_time_policy_ids, (array)$sp_obj->getIncludeOverTimePolicy() );
				}
			}
			unset($sp_obj);
		}

		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$otplf->getByPolicyGroupUserIdOrId( $this->getUserObject()->getId(), $this->schedule_over_time_policy_ids );
		if ( $otplf->getRecordCount() > 0 ) {
			Debug::text('Found overtime policy rows: '. $otplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			//$this->over_time_policy_rs = $otplf;
			foreach( $otplf as $otp_obj ) {
				$this->over_time_policy[$otp_obj->getId()] = $otp_obj;
			}

			return TRUE;
		}

		Debug::text('No overtime policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getOverTimeTriggerArray( $date_stamp ) {
		//Loop over each overtime policy that applies to this day.
		$over_time_policies = $this->filterOverTimePolicy( $date_stamp );
		if ( is_array( $over_time_policies ) ) {
			$weekly_over_time_pay_code_ids = $this->getWeeklyOverTimePolicyPayCodes();

			$tmp_trigger_time_arr = array();
			foreach( $over_time_policies as $otp_obj ) {
				if ( !isset( $otp_calculation_order ) ) {
					$otp_calculation_order = $otp_obj->getOptions('calculation_order');
				}

				Debug::text('  Checking Against Policy: '. $otp_obj->getName() .'('. $otp_obj->getID() .') Trigger Time: '. $otp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
				$trigger_time = NULL;
				switch( $otp_obj->getType() ) {
					case 10: //Daily
						$trigger_time = $otp_obj->getTriggerTime();
						Debug::text(' Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						break;
					case 20: //Weekly
						$trigger_time = $otp_obj->getTriggerTime();
						Debug::text(' Weekly Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						break;
					case 30: //Bi-Weekly
						//Convert biweekly into a weekly policy by taking the hours worked in the
						//first of the two week period and reducing the trigger time by that amount.
						//When does the bi-weekly cutoff start though? It must have a hard date that it can be based on so we don't count the same week twice.
						//Try to synchronize it with the week of the first pay period? Just figure out if we are odd or even weeks.
						$week_modifier = 0; //0=Even, 1=Odd
						if ( is_object( $this->pay_period_obj ) ) {
							$week_modifier = ( TTDate::getWeek( TTDate::getMiddleDayEpoch( $this->pay_period_obj->getStartDate() ), $this->start_week_day_id ) % 2 ); //Due to DST, use getMiddleDayEpoch()
							//Debug::text(' Pay Period Start Date: '. TTDate::getDate('DATE+TIME', $this->pay_period_obj->getStartDate() ).' Start Week Day: '. $this->start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);
						}
						$current_week_modifier = ( TTDate::getWeek( $date_stamp, $this->start_week_day_id ) % 2 );
						Debug::text(' Current Week: '. $current_week_modifier .' Week Modifier: '. $week_modifier, __FILE__, __LINE__, __METHOD__, 10);

						$first_week_total = 0;
						if ( $current_week_modifier != $week_modifier ) {
							//$udtlf->getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch() uses "< $epoch" so the current day is ignored, but in this
							//case we want to include the last day of the week, so we need to add one day to this argument.
							//The above caused problems around March 9th due to DST, so just use the beginning of the current week and the beginning of the last week instead.

							$first_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( 86400 * 7 ) ), $this->start_week_day_id );
							$first_week_end_date = TTDate::getEndWeekEpoch( $first_week_start_date, $this->start_week_day_id );

							//Get data for first week if we haven't already.
							Debug::text(' Getting data for first week: Start: '. TTDate::getDate('DATE', $first_week_start_date ) .' End: '. TTDate::getDate('DATE', $first_week_end_date ), __FILE__, __LINE__, __METHOD__, 10);
							$this->getRequiredData( $first_week_end_date );

							$first_week_total = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( $first_week_start_date, $first_week_end_date, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], array( 20, 25, 30, 100, 110 ), $weekly_over_time_pay_code_ids ) ); //Don't include object_type_id=50 as that often is duplicated with ID: 25.
							Debug::text(' Week modifiers differ, calculate total time for the first week: '. $first_week_total, __FILE__, __LINE__, __METHOD__, 10);
							unset($first_week_start_date, $first_week_end_date);
						} else {
							$second_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $date_stamp ) + ( 86400 * 7 ) ), $this->start_week_day_id );
							$second_week_end_date = ( TTDate::getEndWeekEpoch( $second_week_start_date, $this->start_week_day_id ) );
							Debug::text(' Calculating OT for second week: Date: '. TTDate::getDate('DATE+TIME', $date_stamp ) .' Start: '. TTDate::getDate('DATE', $second_week_start_date ) .' End: '. TTDate::getDate('DATE', $second_week_end_date ), __FILE__, __LINE__, __METHOD__, 10);

							$this->addPendingCalculationDate( $second_week_start_date, $second_week_end_date );
						}

						$trigger_time = ( $otp_obj->getTriggerTime() - $first_week_total );
						if ( $trigger_time < 0 ) {
							$trigger_time = 0;
						}
						Debug::text('Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						unset($first_week_total, $week_modifier, $current_week_modifier);
						break;
					case 40: //Sunday
						if ( date('w', $date_stamp ) == 0 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 50: //Monday
						if ( date('w', $date_stamp ) == 1 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 60: //Tuesday
						if ( date('w', $date_stamp ) == 2 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 70: //Wed
						if ( date('w', $date_stamp ) == 3 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 80: //Thu
						if ( date('w', $date_stamp ) == 4 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 90: //Fri
						if ( date('w', $date_stamp ) == 5 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT DayOfWeek OT for Sat...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						break;
					case 100: //Sat
						if ( date('w', $date_stamp ) == 6 ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' DayOfWeek OT for Sat ... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
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

						//This always resets on the week boundary.
						//$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserObject()->getId(), TTDate::getBeginWeekEpoch($date_stamp, $start_week_day_id), $date_stamp );
						$days_worked_arr = (array)$this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id ), $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], NULL, $weekly_over_time_pay_code_ids ) );

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						if ( $weekly_days_worked >= $minimum_days_worked AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
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
						//$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserObject()->getId(), ( $date_stamp - (86400 * $minimum_days_worked) ), $date_stamp );
						$days_worked_arr = (array)$this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( ( $date_stamp - (86400 * $minimum_days_worked) ), $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], NULL, $weekly_over_time_pay_code_ids ) );

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						//Since these can span overtime weeks, we need to calculate the future week as well.
						//UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);

						if ( $weekly_days_worked >= $minimum_days_worked AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
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

						//Why is this checking for previous day with overtime worked? Because thats how it knows when to restart the consec. day count
						//Based on when the last overtime was calculated?
						$range_start_date = ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( 86400 * $minimum_days_worked) );

						//FIXME: This checks for any other time assigned to the pay code, but if they assigned multiple overtime policies to the same pay code
						//       they may not get the expected results. In order to this fix this we need to track src_object_id for all UDT records and not compact it out.
						//$previous_day_with_overtime_result = $udtlf->getPreviousDayByUserIdAndStartDateAndEndDateAndObjectTypeAndObjectId( $this->getUserObject()->getId(), $range_start_date, $date_stamp, 30, $otp_obj->getId() );
						$previous_day_with_overtime_result = $this->getPreviousDayByUserTotalData( $this->filterUserDateTotalDataByPayCodeIDs( $range_start_date, $date_stamp, $otp_obj->getPayCode() ), $date_stamp );
						if ( $previous_day_with_overtime_result !== FALSE ) {
							$previous_day_with_overtime = TTDate::getMiddleDayEpoch( $previous_day_with_overtime_result );
							Debug::text(' Previous Day with OT: '. TTDate::getDate('DATE', $previous_day_with_overtime ) .' Start Date: '. TTDate::getDate('DATE', $range_start_date ) .' End Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( isset( $previous_day_with_overtime ) AND $previous_day_with_overtime >= $range_start_date ) {
							$range_start_date = ( TTDate::getMiddleDayEpoch( $previous_day_with_overtime ) + 86400 );
							Debug::text(' bPrevious Day with OT: '. TTDate::getDate('DATE', $previous_day_with_overtime ) .' Start Date: '. TTDate::getDate('DATE', $range_start_date ) .' End Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
						}

						//This does not reset on the week boundary.
						//$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserObject()->getId(), $range_start_date, $date_stamp );
						$days_worked_arr = (array)$this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( $range_start_date, $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], NULL, $weekly_over_time_pay_code_ids ) );
						sort($days_worked_arr);

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						//Since these can span overtime weeks, we need to calculate the future week as well.
						//UserDateTotalFactory::setEnableCalcFutureWeek(TRUE);

						$days_worked_arr_key = ( $minimum_days_worked - 1 );
						if ( $weekly_days_worked >= $minimum_days_worked
								AND TTDate::isConsecutiveDays( $days_worked_arr ) == TRUE
								AND isset($days_worked_arr[$days_worked_arr_key])
								AND TTDate::getMiddleDayEpoch( $days_worked_arr[$days_worked_arr_key] ) == TTDate::getMiddleDayEpoch( $date_stamp ) ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days Consecutive... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT After Days Consecutive Worked...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						unset($range_start_date, $previous_day_with_overtime, $previous_day_with_overtime, $days_worked_arr, $weekly_days_worked, $minimum_days_worked);
						break;
					case 400: //2-day/week Consecutive
					case 401: //3-day/week Consecutive
					case 402: //4-day/week Consecutive
					case 403: //5-day/week Consecutive
					case 404: //6-day/week Consecutive
					case 405: //7-day/week Consecutive
						switch ( $otp_obj->getType() ) {
							case 400:
								$minimum_days_worked = 2;
								break;
							case 401:
								$minimum_days_worked = 3;
								break;
							case 402:
								$minimum_days_worked = 4;
								break;
							case 403:
								$minimum_days_worked = 5;
								break;
							case 404:
								$minimum_days_worked = 6;
								break;
							case 405:
								$minimum_days_worked = 7;
								break;
						}

						//This always resets on the week boundary.
						//$days_worked_arr = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUserObject()->getId(), TTDate::getBeginWeekEpoch($date_stamp, $start_week_day_id), $date_stamp );
						$days_worked_arr = (array)$this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id ), $date_stamp, $this->contributing_shift_policy[$otp_obj->getContributingShiftPolicy()], NULL, $weekly_over_time_pay_code_ids ) );

						$weekly_days_worked = count($days_worked_arr);
						Debug::text(' Weekly Days Worked: '. $weekly_days_worked .' Minimum Required: '. $minimum_days_worked, __FILE__, __LINE__, __METHOD__, 10);

						if ( $weekly_days_worked >= $minimum_days_worked ) {
							$trigger_time = $otp_obj->getTriggerTime();
							Debug::text(' After Days... Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::text(' NOT After Days Worked...', __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}
						unset($days_worked_arr, $weekly_days_worked, $minimum_days_worked);
						break;
					case 180: //Holiday
						$holiday_obj = $this->filterHoliday( $date_stamp );
						if ( is_object( $holiday_obj ) AND isset($this->holiday_policy[$holiday_obj->getHolidayPolicyID()]) ) {
							Debug::text(' Found Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
							if ( $this->holiday_policy[$holiday_obj->getHolidayPolicyID()]->getForceOverTimePolicy() == TRUE
									OR $this->isEligibleForHoliday( $date_stamp, $this->holiday_policy[$holiday_obj->getHolidayPolicyID()] ) ) {
								$trigger_time = $otp_obj->getTriggerTime();
								Debug::text(' Is Eligible for Holiday: '. $holiday_obj->getName() .' Daily Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
							} else {
								Debug::text(' Not Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
								continue 2; //Skip to next policy
							}
						} else {
							Debug::text(' Not Holiday...', __FILE__, __LINE__, __METHOD__, 10);
							continue 2; //Skip to next policy
						}
						unset($holiday_obj);
						break;
					case 200: //Over schedule (Daily) / No Schedule. Have trigger time extend the schedule time.
						$schedule_daily_total_time = $this->getSumScheduleTime( $this->filterScheduleDataByStatus( $date_stamp, $date_stamp, array( 10 ) ) );
						Debug::text('Schedule Daily Total Time: '. $schedule_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

						$trigger_time = ( $schedule_daily_total_time + $otp_obj->getTriggerTime() );
						Debug::text(' Over Schedule/No Schedule Trigger Time: '. $trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						unset($schedule_daily_total_time);
						break;
					case 210: //Over Schedule (Weekly) / No Schedule
						//Get schedule time for the entire week, and add the Active After time to that.
						//$schedule_weekly_total_time = $slf->getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch( $this->getUserObject()->getId(), TTDate::getEndWeekEpoch($date_stamp, $start_week_day_id), TTDate::getBeginWeekEpoch($date_stamp, $start_week_day_id) );
						$schedule_weekly_total_time = $this->getSumScheduleTime( $this->filterScheduleDataByStatus( TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id ), TTDate::getEndWeekEpoch($date_stamp, $this->start_week_day_id), array( 10 ) ) );
						Debug::text('Schedule Weekly Total Time: '. $schedule_weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);

						$trigger_time = ( $schedule_weekly_total_time + $otp_obj->getTriggerTime() );
						unset($schedule_weekly_total_time);
						break;
				}

				if ( is_numeric($trigger_time) AND $trigger_time < 0 ) {
					$trigger_time = 0;
				}

				if ( is_numeric($trigger_time) ) {
					$pay_formula_obj = $this->getPayFormulaPolicyObject( $otp_obj );
					if ( is_object( $pay_formula_obj ) ) {
						$trigger_time_arr[] = array('calculation_order' => $otp_calculation_order[$otp_obj->getType()], 'trigger_time' => $trigger_time, 'is_differential_criteria' => $otp_obj->isDifferentialCriteriaDefined(), 'over_time_policy_id' => $otp_obj->getId(), 'over_time_policy_type_id' => $otp_obj->getType(), 'contributing_shift_policy_id' => $otp_obj->getContributingShiftPolicy(), 'pay_code_id' => $otp_obj->getPayCode(), 'combined_rate' => ($pay_formula_obj->getRate() + $pay_formula_obj->getAccrualRate()) );
						//Debug::Arr($trigger_time_arr, 'Trigger Time Array: ', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::Arr( array_keys( (array)$this->pay_codes ), 'Pay Formula Policy not found! OT Policy ID: '. $otp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($trigger_time);
			}

			if ( isset($trigger_time_arr) ) {
				return $trigger_time_arr;
			}

			return TRUE;
		}

		Debug::text('No over time policies to build trigger array from...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function processTriggerTimeArray( $date_stamp, $trigger_time_arr ) {
		if ( is_array($trigger_time_arr) == FALSE OR count($trigger_time_arr) == 0 ) {
			return FALSE;
		}

		//Debug::Arr($trigger_time_arr, 'Source Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		//Convert all OT policies to daily before applying.
		//For instance, 40+hrs/week policy if they are currently at 35hrs is a 5hr daily policy.
		//For weekly OT policies, they MUST include regular time + other WEEKLY over time rules.
		//FIXME: If they use the same pay code for both Daily and Weekly OT it will break this. So we need to base it on the src_object_id instead I think.
		$weekly_over_time_pay_code_ids = $this->getWeeklyOverTimePolicyPayCodes();
		
		//Create a duplicate trigger_time_arr that we can sort so we know the
		//first trigger time is always the first in the array.
		//We don't want to use this array in the loop though, because it throws off other ordering.
		$tmp_trigger_time_arr = Sort::multiSort( $trigger_time_arr, 'trigger_time' );
		$first_trigger_time = $tmp_trigger_time_arr[0]['trigger_time']; //Get first trigger time.
		//Debug::Arr($tmp_trigger_time_arr, 'Trigger Time After Sort: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('First Trigger Time: '. $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
		unset($tmp_trigger_time_arr);

		//Sort trigger_time array by calculation order before looping over it.
		//$trigger_time_arr = Sort::multiSort( $trigger_time_arr, 'calculation_order', 'trigger_time', 'asc', 'desc' );
		$trigger_time_arr = Sort::arrayMultiSort( $trigger_time_arr, array( 'calculation_order' => SORT_ASC, 'trigger_time' => SORT_DESC, 'is_differential_criteria' => SORT_DESC, 'combined_rate' => SORT_DESC )  );
		//Debug::Arr($trigger_time_arr, 'Source Trigger Arr After Calculation Order Sort: ', __FILE__, __LINE__, __METHOD__, 10);

		//We need to calculate regular time as early as possible so we can adjust the trigger time
		//of weekly overtime policies and re-sort the array.
		$tmp_trigger_time_arr = array();
		foreach( $trigger_time_arr as $key => $trigger_time_data ) {
			if ( in_array($trigger_time_data['over_time_policy_type_id'], array(20, 30, 210 ) ) ) {
				//Get weekly total time for this contributing shift id.
				$weekly_total_time = 0;
				if ( isset( $this->contributing_shift_policy[$trigger_time_data['contributing_shift_policy_id']] ) ) {
					if ( TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id ) == TTDate::getBeginDayEpoch( $date_stamp ) ) {
						Debug::Text('Current day is start of the week, no need to collect weekly total time...', __FILE__, __LINE__, __METHOD__, 10);
					} else {
						$weekly_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id ), ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ), $this->contributing_shift_policy[$trigger_time_data['contributing_shift_policy_id']], array( 20, 25, 30, 100, 110 ), $weekly_over_time_pay_code_ids ) ); //Don't include object_type_id=50 as that often is duplicated with ID: 25.
					}
				} else {
					Debug::Text('Unable to find Contributing Shift Policy ID: '. $trigger_time_data['contributing_shift_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
				}
				Debug::Text('Weekly Total Time: '. $weekly_total_time .' as of: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

				if ( is_numeric($weekly_total_time)
						AND $weekly_total_time > 0
						AND $weekly_total_time >= $trigger_time_data['trigger_time'] ) {
					//Worked more then weekly trigger time already.
					Debug::Text('Worked more then weekly trigger time...', __FILE__, __LINE__, __METHOD__, 10);

					$tmp_trigger_time = 0;
				} else {
					//Haven't worked more then the weekly trigger time yet.
					$tmp_trigger_time = ( $trigger_time_data['trigger_time'] - $weekly_total_time );
					Debug::Text('NOT Worked more then weekly trigger time... TMP Trigger Time: '. $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

					/*
					//Don't recall why were doing this, as it breaks the new OT policy setup as it dumps all weekly OT policies into the same trigger time
					//array, which throws off the combined_rate removal logic.
					if ( is_numeric($weekly_total_time)
						AND $weekly_total_time > 0
						AND $tmp_trigger_time > $first_trigger_time ) {
						Debug::Text('Using First Trigger Time: '. $first_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_trigger_time = $first_trigger_time;
					}
					*/
				}

				$trigger_time_arr[$key]['trigger_time'] = $tmp_trigger_time;
			} else {
				Debug::Text('NOT special (weekly/biweekly) overtime policy...', __FILE__, __LINE__, __METHOD__, 10);
				$tmp_trigger_time = $trigger_time_data['trigger_time'];
			}

			Debug::Text('Trigger Time: '. $tmp_trigger_time .' OverTime Policy Id: '. $trigger_time_data['over_time_policy_id'], __FILE__, __LINE__, __METHOD__, 10);
			//Only include policies with the same trigger time if some differential criteria is defined.
			//Such a limit won't work properly when one policy is active for all branches, and two others have differential criteria.
			//The differential criteria ones may cause the non-differential crtieria to never be included.
			//if ( !isset($retval[$tmp_trigger_time]) OR ( isset($retval[$tmp_trigger_time]) AND $trigger_time_data['is_differential_criteria'] == TRUE ) ) {
				Debug::Text('Adding policy to final array... Trigger Time: '. $tmp_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
				$trigger_time_data['trigger_time'] = $tmp_trigger_time;
				$retval[$tmp_trigger_time][] = $trigger_time_data;
			//} else {
			//	Debug::Text('NOT Adding policy to final array...', __FILE__, __LINE__, __METHOD__, 10);
			//}
			
			$tmp_trigger_time_arr[] = $trigger_time_arr[$key]['trigger_time'];
		}
		unset($trigger_time_arr, $tmp_trigger_time_arr, $trigger_time_data);

		ksort($retval);
		//Debug::Arr($retval, 'Dest Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		//Loop through final array and remove policies with higher trigger times and lower rates.
		//The rate matters as we don't want one policy after 8hrs to have a lower rate than a policy after 0hrs. (ie: Holiday OT after 0hrs @ 2x and Daily OT after 8hrs @ 1.5x)
		//Are there any scenarios where an employee works more hours and gets a lesser rate?
		$prev_combined_rate = 0;
		foreach( $retval as $tmp_trigger_time => $overtime_policies ) {
			//Get highest combined rate for each OT policy with the same trigger time.
			//We always need to keep OT policies with the same triger time due to differential criteria. 
			$tmp_combined_rate = 0;
			foreach( $overtime_policies as $key => $tmp_policy_data ) {
				if ( $tmp_policy_data['combined_rate'] > $tmp_combined_rate ) {
					$tmp_combined_rate = $tmp_policy_data['combined_rate'];
					$policy_data = $tmp_policy_data;
				}
			}
			unset($tmp_policy_data, $tmp_combined_rate);

			if ( isset($policy_data) ) {
				if ( $policy_data['combined_rate'] < $prev_combined_rate ) {
					Debug::Text('Removing policy with higher trigger time and lower combined rate... Trigger Time: '. $tmp_trigger_time .' Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					unset($retval[$tmp_trigger_time][$key]);

					if ( count($retval[$tmp_trigger_time]) == 0 ) {
						unset($retval[$tmp_trigger_time]);
					}
				} else {
					$prev_combined_rate = $policy_data['combined_rate'];
				}
			}

		}
		unset($key, $tmp_trigger_time, $overtime_policies, $policy_data);
		Debug::Arr($retval, 'Final OverTime Trigger Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}


	function calculateExceptionPolicy( $date_stamp ) {
		if ( is_array($this->exception_policy) ) {
			//Make sure passed date_stamp is middleDayEpoch() to match the existing exceptions.
			$date_stamp = TTDate::getMiddleDayEpoch( $date_stamp );

			$enable_premature_exceptions = $this->getFlag('exception_premature');
			$enable_future_exceptions = $this->getFlag('exception_future');

			Debug::text(' DateStamp: '. TTDate::getDATE('DATE+TIME', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
			$current_epoch = TTDate::getTime();

			if ( $enable_future_exceptions == FALSE
					AND $date_stamp > TTDate::getEndDayEpoch( $current_epoch ) ) {
				return FALSE;
			}

			if ( is_object( $this->pay_period_schedule_obj ) ) {
				$premature_delay = $this->pay_period_schedule_obj->getMaximumShiftTime();
				$start_week_day_id = $this->pay_period_schedule_obj->getStartWeekDay();
			} else {
				$premature_delay = 57600;
				$start_week_day_id = 0;
			}
			Debug::text(' Setting preMature Exception delay to maximum shift time: '. $premature_delay .' Enable PreMature Exceptions: '. (int)$enable_premature_exceptions, __FILE__, __LINE__, __METHOD__, 10);

			$user_id = $this->getUserObject()->getId();

			$existing_exceptions = array();
			$elf = $this->exception;
			if ( is_array($elf) AND count($elf) > 0 ) {
				foreach( $elf as $e_obj ) {
					//Because the exception diff. function compares on what exists vs whats new, we can only pass exceptions from the current date to it.
					if ( TTDate::getMiddleDayEpoch( $e_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp ) ) {
						$existing_exceptions[] = array(
														'id' => $e_obj->getId(),
														'user_id' => $e_obj->getUser(),
														'date_stamp' => TTDate::getMiddleDayEpoch( $e_obj->getDateStamp() ),
														'exception_policy_id' => $e_obj->getExceptionPolicyID(),
														'type_id' => $e_obj->getType(),
														'punch_id' => $e_obj->getPunchID(),
														'punch_control_id' => $e_obj->getPunchControlID(),
													);
					}
				}
			}
			unset($elf, $e_obj);

			$current_exceptions = array(); //Array holding current exception data.

			$slf = $this->filterScheduleDataByStatus( $date_stamp, $date_stamp, array(10) );
			$plf = $this->filterPunchDataByDateAndTypeAndStatus( $date_stamp );

			foreach( $this->exception_policy as $ep_obj ) {
				//Only allow pre-mature exceptions to be enabled if we are calculating no further back than 2 days from the current time.
				if ( $enable_premature_exceptions == TRUE AND $ep_obj->isPreMature( $ep_obj->getType() ) == TRUE
						AND TTDate::getMiddleDayEpoch( $date_stamp ) >= ( TTDate::getMiddleDayEpoch( $current_epoch ) - $premature_delay ) ) {
					//Debug::text(' Premature Exception: '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);
					$type_id = 5; //Pre-Mature
				} else {
					//Debug::text(' NOT Premature Exception: '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);
					$type_id = 50; //Active
				}
				
				Debug::text('---Calculating Exception: '. $ep_obj->getType() .' Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);
				switch ( strtolower( $ep_obj->getType() ) ) {
					case 's1':	//Unscheduled Absence... Anytime they are scheduled and have not punched in.
								//Ignore these exceptions if the schedule is after today (not including today),
								//so if a supervisors schedules an employee two days in advance they don't get a unscheduled
								//absence appearing right away.
								//Since we now trigger In Late/Out Late exceptions immediately after schedule time, only trigger this exception after
								//the schedule end time has passed.
								//**We also need to handle shifts that start at 11:00PM on one day, end at 8:00AM the next day, and they are assigned to the day where
								//the most time is worked (ie: the next day).
								//Handle split shifts too...
								//- This has a side affect that if the schedule policy start/stop time is set to 0, it will trigger both a UnScheduled Absence
								//	and a Not Scheduled exception for the same schedule/punch.

						//Loop through all schedules, then find punches to match.
						if ( is_array($slf) AND count($slf) > 0 ) {
							foreach( $slf as $s_obj ) {
								if ( $s_obj->getStatus() == 10 AND ( $current_epoch >= $s_obj->getEndTime() ) ) {
									$add_exception = TRUE;
									//Debug::text(' Found Schedule: Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
									//Find punches that fall within this schedule time including start/stop window.
									if ( TTDate::doesRangeSpanMidnight( $s_obj->getStartTime(), $s_obj->getEndTime() ) ) {
										//Get punches from both days.
										$plf_tmp = TTnew( 'PunchListFactory' );
										//$plf_tmp->getShiftPunchesByUserIDAndEpoch( $user_date_obj->getUser(), $s_obj->getStartTime(), 0, $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime() );
										$plf_tmp->getShiftPunchesByUserIDAndEpoch( $user_id, $s_obj->getStartTime(), 0, $premature_delay );
										Debug::text(' Schedule spans midnight... Found rows from expanded search: '. $plf_tmp->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
										if ( $plf_tmp->getRecordCount() > 0 ) {
											foreach( $plf_tmp as $p_obj_tmp ) {
												if ( $s_obj->inSchedule( $p_obj_tmp->getTimeStamp() ) ) {
													Debug::text(' aFound punch for schedule...', __FILE__, __LINE__, __METHOD__, 10);
													$add_exception = FALSE;
													break;
												}
											}
										}
										unset( $plf_tmp, $p_obj_tmp );
									} else {
										if ( is_array($plf) AND count($plf) > 0 ) {
											//Get punches from just this day.
											foreach( $plf as $p_obj ) {
												if ( $s_obj->inSchedule( $p_obj->getTimeStamp() ) ) {
													//Debug::text(' bFound punch for schedule...', __FILE__, __LINE__, __METHOD__, 10);
													$add_exception = FALSE;
													break;
												}
											}
										}
									}

									if ( $add_exception == TRUE ) {
										//Debug::text(' Adding S1 exception...', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																		'schedule_obj' => $s_obj,
																	);
									}
								}
							}
						}
						unset($s_obj, $add_exception);
						break;
					case 's2': //Not Scheduled
						//**We also need to handle shifts that start at 11:00PM on one day, end at 8:00AM the next day, and they are assigned to the day where
						//the most time is worked (ie: the next day).
						//Handle split shifts too...
						if ( is_array($plf) AND count($plf) > 0 ) { //Make sure at least two punche exist.
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}

									//Check if no schedule exists, or an absent schedule exists. If they work when not scheduled (no schedule) or schedule absent, both should trigger this.
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == FALSE
											OR ( is_object( $p_obj->getScheduleObject() ) AND $p_obj->getScheduleObject()->getStatus() == 20 ) ) {
										//Debug::text(' Worked when wasnt scheduled', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getID(),
																		'punch_control_id' => FALSE,
																	);

									} else {
										Debug::text('	 Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						unset($scheduled_id_cache, $prev_punch_time_stamp, $p_obj);
						break;
					case 's3': //In Early
						if ( is_array($plf) AND count($plf) > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('	 NO Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						break;
					case 's4': //In Late
						if ( is_array($plf) AND count($plf) > 0 ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								//Debug::text('	 In Late. Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
											} elseif (	TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('	 NO Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						unset($scheduled_id_cache);

						//Late Starting their shift, with no punch yet, trigger exception if:
						//	- Schedule is found
						//	- Current time is after schedule start time and before schedule end time.
						//	- Current time is after exception grace time
						//Make sure we take into account split shifts.
						Debug::text('	 Checking Late Starting Shift exception... Current time: '. TTDate::getDate('DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10);
						if ( is_array($slf) AND count($slf) > 0 ) {
							foreach ( $slf as $s_obj ) {
								if ( $s_obj->getStatus() == 10 AND ( $current_epoch >= $s_obj->getStartTime() AND $current_epoch <= $s_obj->getEndTime() ) ) {
									if ( TTDate::inWindow( $current_epoch, $s_obj->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
										Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
									} else {
										//See if we can find a punch within the schedule time, if so assume we already created the exception above.
										//Make sure we take into account the schedule policy start/stop window.
										//However in the case where a single schedule shift and just one punch exists, if an employee comes in really
										//early (1AM) before the schedule start/stop window it will trigger an In Late exception.
										//This could still be correct though if they only come in for an hour, then come in late for their shift later.
										//Schedule start/stop time needs to be correct.
										//Also need to take into account shifts that span midnight, ie: 10:30PM to 6:00AM, as its important the schedules/punches match up properly.

										$add_exception = TRUE;
										Debug::text(' Found Schedule: Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
										//Find punches that fall within this schedule time including start/stop window.
										if ( TTDate::doesRangeSpanMidnight( $s_obj->getStartTime(), $s_obj->getEndTime() ) ) {
											//Get punches from both days.
											$plf_tmp = TTnew( 'PunchListFactory' );
											$plf_tmp->getShiftPunchesByUserIDAndEpoch( $user_id, $s_obj->getStartTime(), 0, $premature_delay );
											Debug::text(' Schedule spans midnight... Found rows from expanded search: '. $plf_tmp->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
											if ( $plf_tmp->getRecordCount() > 0 ) {
												foreach( $plf_tmp as $p_obj_tmp ) {
													if ( $s_obj->inSchedule( $p_obj_tmp->getTimeStamp() ) ) {
														Debug::text('	 Found punch for this schedule, skipping schedule...', __FILE__, __LINE__, __METHOD__, 10);
														$add_exception = FALSE;
														continue 2; //Skip to next schedule without creating exception.
													}
												}
											}
											unset( $plf_tmp, $p_obj_tmp );
										} else {
											//Get punches from just this day.
											if ( is_array($plf) AND count($plf) > 0 ) {
												foreach( $plf as $p_obj ) {
													if ( $s_obj->inSchedule( $p_obj->getTimeStamp() ) ) {
														Debug::text(' bFound punch for schedule...', __FILE__, __LINE__, __METHOD__, 10);
														$add_exception = FALSE;
														break;
													}
												}
											}
										}

										if ( $add_exception == TRUE ) {
											Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																			'schedule_obj' => $s_obj,
																		);
										}
									}
								}
							}
						} else {
							Debug::text('	 NO Schedules Found', __FILE__, __LINE__, __METHOD__, 10);
						}
						break;
					case 's5': //Out Early
						if ( is_array($plf) AND count($plf) > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							$total_punches = count($plf);
							$x = 1;
							foreach ( $plf as $p_obj ) {
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								//For Out Early, we have to wait until we are at the last punch, or there is a subsequent punch
								// to see if it matches the exact same time (transfer)
								//Therefore we need a two step confirmation before this exception can be triggered. Current punch, then next punch if it exists.
								if ( $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);

												$tmp_exception = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);

												if ( $x	== $total_punches ) { //Trigger exception if we're the last punch.
													$current_exceptions[] = $tmp_exception;
												} //else { //Save exception to be triggered if the next punch doesn't match the same time.
											}
										}
									} else {
										Debug::text('	 NO Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								} elseif ( $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									//This comes after an OUT punch, so we need to check if there are two punches
									//in a row with the same timestamp, if so ignore the exception.
									if ( isset($tmp_exception ) AND $p_obj->getTimeStamp() == $prev_punch_time_stamp ) {
										unset($tmp_exception);
									} elseif ( isset($tmp_exception) ) {
										$current_exceptions[] = $tmp_exception; //Set exception.
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();

								$x++;
							}
						}
						unset($tmp_exception, $x, $prev_punch_time_stamp);
						break;
					case 's6': //Out Late
						if ( is_array($plf) AND count($plf) > 0 ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );

								//Ignore transfer punches to optimize cases where many punches exist.
								if ( $p_obj->getTransfer() == FALSE AND $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																				'punch_obj' => $p_obj,
																				'schedule_obj' => $p_obj->getScheduleObject(),
																			);
											}
										}
									} else {
										Debug::text('	 NO Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}

							//Trigger exception if no out punch and we have passed schedule out time.
							//	- Schedule is found
							//	- Make sure the user is missing an OUT punch.
							//	- Current time is after schedule end time
							//	- Current time is after exception grace time
							//	- Current time is before schedule end time + maximum shift time.
							if ( isset($punch_pairs) AND is_array($slf) AND count($slf) > 0 ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('aFound Missing Punch: ', __FILE__, __LINE__, __METHOD__, 10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('bFound Missing Out Punch: ', __FILE__, __LINE__, __METHOD__, 10);

											foreach ( $slf as $s_obj ) {
												Debug::text('Punch: '. TTDate::getDate('DATE+TIME', $punch_pair[0]['time_stamp'] ) .' Schedule Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
												//Because this is just an IN punch, make sure the IN punch is before the schedule end time
												//So we can eliminate split shift schedules.
												if ( $punch_pair[0]['time_stamp'] <= $s_obj->getEndTime()
														AND $current_epoch >= $s_obj->getEndTime() AND $current_epoch <= ($s_obj->getEndTime() + $premature_delay) ) {
													if ( TTDate::inWindow( $current_epoch, $s_obj->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
														Debug::text('	 Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
													} else {
														Debug::text('	 NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__, 10);
														$current_exceptions[] = array(
																						'user_id' => $user_id,
																						'date_stamp' => $date_stamp,
																						'exception_policy_id' => $ep_obj->getId(),
																						'type_id' => $type_id,
																						'punch_id' => FALSE,
																						'punch_control_id' => $punch_pair[0]['punch_control_id'],
																						'schedule_obj' => $s_obj,
																					);
													}
												}
											}
										}
									}
									//else {
									//	Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__, 10);
									//}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'sb': //Not Scheduled Branch/Department
					case 'sc': //Not Scheduled Job/Task
						if ( is_array($plf) AND count($plf) > 0 ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf as $p_obj ) {
								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );

								//How do we handle transfer punches? Should we just check Normal IN punches that aren't transfers punches?
								//For now consider all IN punches, even transfer punches.
								//if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) {
								if ( $p_obj->getStatus() == 10 ) {
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( is_object( $p_obj->getPunchControlObject() )
												AND (
														( strtolower( $ep_obj->getType() ) == 'sb' AND $p_obj->getScheduleObject()->getBranch() > 0 AND $p_obj->getPunchControlObject()->getBranch() != $p_obj->getScheduleObject()->getBranch() )
														OR
														( strtolower( $ep_obj->getType() ) == 'sb' AND $p_obj->getScheduleObject()->getDepartment() > 0 AND $p_obj->getPunchControlObject()->getDepartment() != $p_obj->getScheduleObject()->getDepartment() )
														OR
														( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND strtolower( $ep_obj->getType() ) == 'sc' AND $p_obj->getScheduleObject()->getJob() > 0 AND $p_obj->getPunchControlObject()->getJob() != $p_obj->getScheduleObject()->getJob() )
														OR
														( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND strtolower( $ep_obj->getType() ) == 'sc' AND $p_obj->getScheduleObject()->getJobItem() > 0 AND $p_obj->getPunchControlObject()->getJobItem() != $p_obj->getScheduleObject()->getJobItem() )
													)
											) {
											Debug::text('	 Punch Branch/Department does not match scheduled branch/department: ', __FILE__, __LINE__, __METHOD__, 10);
											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => $p_obj->getID(),
																			'punch_control_id' => FALSE,
																			'punch_obj' => $p_obj,
																			'schedule_obj' => $p_obj->getScheduleObject(),
																		);
										}
									} else {
										Debug::text('	 NO Schedule Found', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
							unset($punch_pairs, $punch_pair, $prev_punch_time_stamp);
						}
						break;
					case 'm1': //Missing In Punch
						if ( is_array($plf) AND count($plf) > 0 ) {
							foreach ( $plf as $p_obj ) {
								//Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10);
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
									$type_id = 50;
								}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'punch_id' => $p_obj->getId() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									//Debug::Arr($punch_pair, 'Punch Pair for Control ID:'. $punch_control_id, __FILE__, __LINE__, __METHOD__, 10);

									if ( count($punch_pair) != 2 ) {
										Debug::text('a1Found Missing Punch: ', __FILE__, __LINE__, __METHOD__, 10);

										if ( $punch_pair[0]['status_id'] == 20 ) { //Missing In Punch
											Debug::text('b1Found Missing In Punch: ', __FILE__, __LINE__, __METHOD__, 10);
											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									}
									//else {
									//	Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__, 10);
									//}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm2': //Missing Out Punch
						if ( is_array($plf) AND count($plf) > 0 ) {
							foreach( $plf as $p_obj ) {
								//Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. TTDate::getDATE('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

								//This causes the exception to trigger if the first punch pair is more than the Maximum Shift time away from the current punch,
								//ie: In: 1:00AM, Out: 2:00AM, In 3:00PM (Maximum Shift Time less than 12hrs). The missing punch exception will be triggered immediately upon the 3:00PM punch.
								//if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
								//	$type_id = 50;
								//}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('a2Found Missing Punch: ', __FILE__, __LINE__, __METHOD__, 10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('b2Found Missing Out Punch: ', __FILE__, __LINE__, __METHOD__, 10);

											//Make sure we are at least MaximumShift Time from the matching In punch before trigging this exception.
											//Even when an supervisor is entering punches for today, make missing out punch pre-mature if the maximum shift time isn't exceeded.
											//This will prevent timesheet recalculations from having missing punches for everyone today.
											//if ( $type_id == 5 AND $punch_pair[0]['time_stamp'] < ($current_epoch - $premature_delay) ) {
											if ( $punch_pair[0]['time_stamp'] < ($current_epoch - $premature_delay) ) {
												$type_id = 50;
											} else {
												$type_id = 5;
											}

											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									}
									//else {
									//	Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__, 10);
									//}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm3': //Missing Lunch In/Out punch
						if ( is_array($plf) AND count($plf) > 0 ) {
							//We need to account for cases where they may punch IN from lunch first, then Out.
							//As well as just a Lunch In punch and nothing else.
							foreach ( $plf as $p_obj ) {
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 20 ) { //Lunch
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Lunch/Out
											if ( !isset($punches[($key - 1)])
													OR ( isset($punches[($key - 1)]) AND is_object($punches[($key - 1)])
															AND ( $punches[($key - 1)]->getType() != 20
																OR $punches[($key - 1)]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Lunch/In
											if ( !isset($punches[($key + 1)]) OR ( isset($punches[($key + 1)]) AND is_object($punches[($key + 1)]) AND ( $punches[($key + 1)]->getType() != 20 OR $punches[($key + 1)]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Lunch In/Out Punch: ', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Lunch Punches match up.', __FILE__, __LINE__, __METHOD__, 10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 'm4': //Missing Break In/Out punch
						if ( is_array($plf) AND count($plf) > 0 ) {
							//We need to account for cases where they may punch IN from break first, then Out.
							//As well as just a break In punch and nothing else.
							foreach ( $plf as $p_obj ) {
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 30 ) { //Break
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Type: '. $p_obj->getType() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Break/Out
											if ( !isset($punches[($key - 1)])
													OR ( isset($punches[($key - 1)]) AND is_object($punches[($key - 1)])
															AND ( $punches[($key - 1)]->getType() != 30
																OR $punches[($key - 1)]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Break/In
											if ( !isset($punches[($key + 1)]) OR ( isset($punches[($key + 1)]) AND is_object($punches[($key + 1)]) AND ( $punches[($key + 1)]->getType() != 30 OR $punches[($key + 1)]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Break In/Out Punch: ', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Break Punches match up.', __FILE__, __LINE__, __METHOD__, 10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 'c1': //Missed Check-in
						//Use grace period and make sure the employee punches within that period of time (usually a transfer punch, but break/lunch should work too)
						if ( is_array($plf) AND count($plf) > 0 AND $ep_obj->getGrace() > 0 ) {
							$prev_punch_time_stamp = FALSE;
							$prev_punch_obj = FALSE;

							$x = 1;
							foreach ( $plf as $p_obj ) {
								Debug::text('	Missed Check-In Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ) .' Delay: '. $premature_delay .' Current Epoch: '. $current_epoch, __FILE__, __LINE__, __METHOD__, 10);

								//Handle punch pairs below. Only trigger on OUT punches.
								if ( is_object($prev_punch_obj) AND $prev_punch_obj->getStatus() == 10
									AND $p_obj->getStatus() == 20 AND ( $p_obj->getTimeStamp() - $prev_punch_time_stamp ) > $ep_obj->getGrace() ) { //Only check OUT punches when paired.
									Debug::text('	Triggering excepetion as employee missed check-in within: '. ( $p_obj->getTimeStamp() - $prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
									$current_exceptions[] = array(
																	'user_id' => $user_id,
																	'date_stamp' => $date_stamp,
																	'exception_policy_id' => $ep_obj->getId(),
																	'type_id' => $type_id,
																	'punch_id' => $p_obj->getID(), //When paired, only attach to the out punch.
																	'punch_control_id' => FALSE,
																	'punch_obj' => $p_obj,
																	'schedule_obj' => $p_obj->getScheduleObject(),
																);
								} elseif ( $prev_punch_time_stamp !== FALSE ) {
									Debug::text('	Employee Checked-In within: '. ( $p_obj->getTimeStamp() - $prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
								}

								//Handle cases where there is a IN punch but no OUT punch yet.
								//However ignore cases where there is a OUT punch but no IN punch.
								if ( $x == count($plf)
										AND $p_obj->getStatus() == 10
										AND ( $current_epoch - $p_obj->getTimeStamp() ) > $ep_obj->getGrace()
										AND $p_obj->getTimeStamp() > ($current_epoch - $premature_delay)
										) {
									Debug::text('	Triggering excepetion as employee hasnt checked in yet, within: '. ( $current_epoch - $prev_punch_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
									$current_exceptions[] = array(
																	'user_id' => $user_id,
																	'date_stamp' => $date_stamp,
																	'exception_policy_id' => $ep_obj->getId(),
																	'type_id' => $type_id,
																	'punch_id' => FALSE,
																	'punch_control_id' => $p_obj->getPunchControlID(), //When not paired, attach to the punch control.
																	'punch_obj' => $p_obj,
																	'schedule_obj' => $p_obj->getScheduleObject(),
																);
								}

								$prev_punch_time_stamp = $p_obj->getTimeStamp();
								$prev_punch_obj = $p_obj;
								$x++;
							}
						}
						unset($prev_punch_obj, $prev_punch_time_stamp, $x);
						break;
					case 'd1': //No Branch or Department
						if ( is_array($plf) AND count($plf) > 0 ) {
							foreach ( $plf as $p_obj ) {
								$add_exception = FALSE;

								//In punches only
								if ( $p_obj->getStatus() == 10 AND is_object( $p_obj->getPunchControlObject() ) ) {
									//If no Branches are setup, ignore checking them.
									if ( $p_obj->getPunchControlObject()->getBranch() == ''
											OR $p_obj->getPunchControlObject()->getBranch() == 0
											OR $p_obj->getPunchControlObject()->getBranch() == FALSE  ) {
										//Make sure at least one task exists before triggering exception.
										$blf = TTNew('BranchListFactory');
										$blf->getByCompanyID( $this->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $blf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									//If no Departments are setup, ignore checking them.
									if ( $p_obj->getPunchControlObject()->getDepartment() == ''
											OR $p_obj->getPunchControlObject()->getDepartment() == 0
											OR $p_obj->getPunchControlObject()->getDepartment() == FALSE ) {
										//Make sure at least one task exists before triggering exception.
										$dlf = TTNew('DepartmentListFactory');
										$dlf->getByCompanyID( $this->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $dlf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									if ( $add_exception === TRUE ) {
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getId(),
																		'punch_control_id' => $p_obj->getPunchControlId(),
																	);
									}
								}
							}
						}
						break;
					case 's7': //Over Scheduled Hours
						if ( is_array($plf) AND count($plf) > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							//FIXME: Do we want to trigger this before their last out punch?
							$schedule_total_time = 0;

							if ( is_array($slf) AND count($slf) > 0 ) {
								//Check for schedule policy
								foreach ( $slf as $s_obj ) {
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									//Take into account auto-deduct/add meal policies, but not paid absences.
									$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ); //Worked time only.
									if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
										foreach( $udtlf as $udt_obj ) {
											$daily_total_time += $udt_obj->getTotalTime();
										}
									}
									unset($udtlf, $udt_obj);
									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__, 10);

									if ( $daily_total_time > 0 AND $daily_total_time > ( $schedule_total_time + $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Over Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);

										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 's8': //Under Scheduled Hours
						if ( is_array($plf) AND count($plf) > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$schedule_total_time = 0;

							if ( is_array($slf) AND count($slf) > 0 ) {
								//Check for schedule policy
								foreach ( $slf as $s_obj ) {
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									//Take into account auto-deduct/add meal policies
									$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ); //Worked time only.
									if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
										foreach( $udtlf as $udt_obj ) {
											$daily_total_time += $udt_obj->getTotalTime();
										}
									}
									unset($udtlf, $udt_obj);
									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__, 10);

									if ( $daily_total_time < ( $schedule_total_time - $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Under Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);

										if ( $type_id == 5 AND $date_stamp < TTDate::getBeginDayEpoch( ($current_epoch - $premature_delay) ) ) {
											$type_id = 50;
										}

										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Under Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'o1': //Over Daily Time.
						if ( is_array($plf) AND count($plf) > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							//FIXME: Do we want to trigger this before their last out punch?
							$daily_total_time = 0;

							//Get daily total time.
							//Take into account auto-deduct/add meal policies
							$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ); //Worked time only.
							if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}
							unset($udtlf, $udt_obj);
							Debug::text(' Daily Total Time: '. $daily_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

							if ( $daily_total_time > 0 AND $daily_total_time > $ep_obj->getWatchWindow() ) {
								Debug::text(' Worked Over Daily Hours', __FILE__, __LINE__, __METHOD__, 10);

								$current_exceptions[] = array(
																'user_id' => $user_id,
																'date_stamp' => $date_stamp,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'o2': //Over Weekly Time.
					case 's9': //Over Weekly Scheduled Time.
						if ( is_array($plf) AND count($plf) > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//Get Pay Period Schedule info
							//FIXME: Do we want to trigger this before their last out punch?
							Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

							$weekly_scheduled_total_time = 0;

							//Currently we only consider committed scheduled shifts. We may need to change this to take into account
							//recurring scheduled shifts that haven't been committed yet as well.
							//In either case though we should take into account the entires week worth of scheduled time even if we are only partially through
							//the week, that way we won't be triggering s9 exceptions on a Wed and a Fri or something, it will only occur on the last days of the week.
							if ( strtolower( $ep_obj->getType() ) == 's9' ) {
								$tmp_slf = TTnew( 'ScheduleListFactory' );
								$tmp_slf->getByUserIdAndStartDateAndEndDate( $user_id, TTDate::getBeginWeekEpoch($date_stamp, $start_week_day_id), TTDate::getEndWeekEpoch($date_stamp, $start_week_day_id) );
								if ( $tmp_slf->getRecordCount() > 0 ) {
									foreach( $tmp_slf as $s_obj ) {
										if ( $s_obj->getStatus() == 10 ) { //Only working shifts.
											$weekly_scheduled_total_time += $s_obj->getTotalTime();
										}
									}
								}
								unset($tmp_slf, $s_obj);
							}

							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$weekly_total_time = 0;

							//Get daily total time.
							$udtlf = TTnew( 'UserDateTotalListFactory' );
							$weekly_total_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_id, TTDate::getBeginWeekEpoch($date_stamp, $start_week_day_id), $date_stamp );

							Debug::text(' Weekly Total Time: '. $weekly_total_time .' Weekly Scheduled Total Time: '. $weekly_scheduled_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' Grace: '. $ep_obj->getGrace() .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
							//Don't trigger either of these exceptions unless both the worked and scheduled time is greater than 0. If they aren't scheduled at all
							//it should trigger a Unscheduled Absence exception instead of a over weekly scheduled time exception.
							if ( ( strtolower( $ep_obj->getType() ) == 'o2' AND $weekly_total_time > 0 AND $weekly_total_time > $ep_obj->getWatchWindow() )
									OR ( strtolower( $ep_obj->getType() ) == 's9' AND $weekly_scheduled_total_time > 0 AND $weekly_total_time > 0 AND $weekly_total_time > ( $weekly_scheduled_total_time + $ep_obj->getGrace() ) ) ) {
								Debug::text(' Worked Over Weekly Hours', __FILE__, __LINE__, __METHOD__, 10);
								$current_exceptions[] = array(
																'user_id' => $user_id,
																'date_stamp' => $date_stamp,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'l1': //Long Lunch
					case 'l2': //Short Lunch
						if ( is_array($plf) AND count($plf) > 0 ) {
							//Get all lunch punches.
							$pair = 0;
							$x = 0;
							$out_for_lunch = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 20 ) {
									$lunch_out_timestamp = $p_obj->getTimeStamp();
									$lunch_punch_arr[$pair]['punch_id'] = $p_obj->getId();
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
							}

							if ( isset($lunch_punch_arr) ) {
								//Debug::Arr($lunch_punch_arr, 'Lunch Punch Array: ', __FILE__, __LINE__, __METHOD__, 10);

								$daily_total_time = 0;
								$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10 ) ); //Worked time only.
								if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
									foreach( $udtlf as $udt_obj ) {
										$daily_total_time += $udt_obj->getTotalTime();
									}
								}
								unset($udtlf, $udt_obj);
								Debug::text(' Daily Total Time: '. $daily_total_time .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

								foreach( $lunch_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$lunch_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Lunch Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);

										$meal_time_policies = $this->filterMealTimePolicy( $date_stamp, $daily_total_time );
										if ( is_array($meal_time_policies) AND count($meal_time_policies) > 0 ) {
											reset($meal_time_policies);
											$mp_obj = $meal_time_policies[key( $meal_time_policies )];
										}

										if ( isset($mp_obj) AND is_object($mp_obj) ) {
											$meal_policy_lunch_time = $mp_obj->getAmount();
											Debug::text('Meal Policy Time: '. $meal_policy_lunch_time, __FILE__, __LINE__, __METHOD__, 10);

											$add_exception = FALSE;
											if ( strtolower( $ep_obj->getType() ) == 'l1'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time > ($meal_policy_lunch_time + $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											} elseif ( strtolower( $ep_obj->getType() ) == 'l2'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time < ( $meal_policy_lunch_time - $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											}

											if ( $add_exception == TRUE ) {
												Debug::text('Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);

												if ( isset($time_stamp_arr['punch_id']) ) {
													$punch_id = $time_stamp_arr['punch_id'];
												} else {
													$punch_id = FALSE;
												}

												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $punch_id,
																				'punch_control_id' => FALSE,
																			);
												unset($punch_id);
											} else {
												Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
											}
										}
									} else {
										Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'l3': //No Lunch
						if ( is_array($plf) AND count($plf) > 0 ) {
							//If they are scheduled or not, we can check for a meal policy and base our
							//decision off that. We don't want a No Lunch exception on a 3hr short shift though.
							//Also ignore this exception if the lunch is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							$daily_total_time = 0;
							$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10 ) ); //Worked time only.
							if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
									$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
								}
							}
							unset($udtlf, $udt_obj);
							Debug::text(' Daily Total Time: '. $daily_total_time .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
							//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__, 10);

							//Find meal policy
							//Use scheduled meal policy first.
							$meal_policy_obj = NULL;

							//Enable $always_return_at_least_one=TRUE so no matter what at least one meal policy is returned if it exists.
							//This allows us to *not* trigger this exception when the user works less than the meal policy trigger time.
							$meal_time_policies = $this->filterMealTimePolicy( $date_stamp, $daily_total_time, array( 15, 20 ), TRUE ); //Exclude auto-deduct meal policies.
							if ( is_array($meal_time_policies) AND count($meal_time_policies) > 0 ) {
								reset($meal_time_policies);
								$meal_policy_obj = $meal_time_policies[key( $meal_time_policies )]; //Get first
							} elseif ( is_array($meal_time_policies) AND count($meal_time_policies) == 0 ) {
								$meal_policy_obj = NULL; //Schedule defined, but no meal policy applies.
							} else {
								//There is no  meal policy or schedule policy with a meal policy assigned to it
								//With out this we could still apply No meal exceptions, but they will happen even on
								//a 2minute shift.
								Debug::text('No Lunch policy, applying No meal exception.', __FILE__, __LINE__, __METHOD__, 10);
								$meal_policy_obj = TRUE;
							}

							if ( is_object($meal_policy_obj) OR $meal_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								//Check meal policy type again here, as any meal policy type can be returned given the above $always_return_at_least_one=TRUE
								if ( $daily_total_time > 0 AND ( $meal_policy_obj === TRUE OR ( $daily_total_time > $meal_policy_obj->getTriggerTime() AND in_array( $meal_policy_obj->getType(), array( 15, 20 ) ) ) ) ) {
									//Check for meal punch.
									$meal_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf as $p_obj ) {
										if ( $p_obj->getType() == 20 ) { //20 = Lunch
											Debug::text('Found meal Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10);
											$meal_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $meal_policy_obj === TRUE OR $tmp_punch_total_time > $meal_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID() .' Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't meal the loop here, as we have to continue on and check for other meals.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									//If the last punch is before the premature delay, make this a mature exception instead.
									if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
										$type_id = 50;
									}

									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $meal_punch == FALSE ) {
										Debug::text('Triggering No Lunch exception!', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
							unset($meal_time_policies, $meal_policy_obj, $tmp_punch_control_ids, $punch_control_total_time );
						}
						break;
					case 'b1': //Long Break
					case 'b2': //Short Break
						if ( is_array($plf) AND count($plf) > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
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
							}
							unset($pair);

							if ( isset($break_punch_arr) ) {
								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__, 10);

								//Get daily total time.
								$daily_total_time = 0;
								$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ); //Worked time only.
								if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
									foreach( $udtlf as $udt_obj ) {
										$daily_total_time += $udt_obj->getTotalTime();
									}
								}
								unset($udtlf, $udt_obj);
								Debug::text(' Daily Total Time: '. $daily_total_time .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
										}

										//Check to see if they have a schedule policy
										$bplf = $this->filterBreakTimePolicy( $date_stamp, $daily_total_time );
										if ( is_array($bplf) AND count( $bplf ) > 0 ) {
											Debug::text('Found Break Policy(ies) to apply: '. count( $bplf ) .' Pair: '. $pair, __FILE__, __LINE__, __METHOD__, 10);

											foreach( $bplf as $bp_obj ) {
												$bp_objs[] = $bp_obj;
											}
											unset($bplf, $bp_obj);

											if ( isset($bp_objs[$pair]) AND is_object($bp_objs[$pair]) ) {
												$bp_obj = $bp_objs[$pair];

												$break_policy_break_time = $bp_obj->getAmount();
												Debug::text('Break Policy Time: '. $break_policy_break_time .' ID: '. $bp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

												$add_exception = FALSE;
												if ( strtolower( $ep_obj->getType() ) == 'b1'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time > ($break_policy_break_time + $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												} elseif ( strtolower( $ep_obj->getType() ) == 'b2'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time < ( $break_policy_break_time - $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												}

												if ( $add_exception == TRUE ) {
													Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

													if ( isset($time_stamp_arr['punch_id']) ) {
														$punch_id = $time_stamp_arr['punch_id'];
													} else {
														$punch_id = FALSE;
													}

													$current_exceptions[] = array(
																					'user_id' => $user_id,
																					'date_stamp' => $date_stamp,
																					'exception_policy_id' => $ep_obj->getId(),
																					'type_id' => $type_id,
																					'punch_id' => $punch_id,
																					'punch_control_id' => FALSE,
																				);
													unset($punch_id);
												} else {
													Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
												}

												unset($bp_obj);
											}
											unset( $bp_objs );
										}
									} else {
										Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'b3': //Too Many Breaks
					case 'b4': //Too Few Breaks
						if ( is_array($plf) AND count($plf) > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
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
							}
							//If the last punch is before the premature delay, make this a mature exception instead.
							if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
								$type_id = 50;
							}

							unset($pair);

							//Get daily total time.
							$daily_total_time = 0;
							$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 100, 110 ) ); //Worked time only.
							if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}
							unset($udtlf, $udt_obj);
							Debug::text(' Daily Total Time: '. $daily_total_time .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

							//Make sure we take into account how long they have currently worked, so we don't
							//say too few breaks for 3hr shift that they employee took one break on.
							//Trigger this exception if the employee doesn't take a break at all?
							if ( isset($break_punch_arr) ) {
								$total_breaks = count($break_punch_arr);

								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__, 10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_id );
										}

										//Check to see if they have a schedule policy
										$bplf = $this->filterBreakTimePolicy( $date_stamp, $daily_total_time );
										$allowed_breaks = count($bplf);

										$add_exception = FALSE;
										if ( strtolower( $ep_obj->getType() ) == 'b3' AND $total_breaks > $allowed_breaks ) {
											Debug::text(' Too many breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} elseif ( strtolower( $ep_obj->getType() ) == 'b4' AND $total_breaks < $allowed_breaks )  {
											Debug::text(' Too few breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} else {
											Debug::text(' Proper number of breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
										}

										if ( $add_exception == TRUE
												AND ( strtolower( $ep_obj->getType() ) == 'b4'
													OR ( strtolower( $ep_obj->getType() ) == 'b3' AND $pair > ($allowed_breaks - 1) )  ) ) {
											Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

											if ( isset($time_stamp_arr['punch_id']) AND strtolower( $ep_obj->getType() ) == 'b3' ) {
												$punch_id = $time_stamp_arr['punch_id'];
											} else {
												$punch_id = FALSE;
											}

											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => $punch_id,
																			'punch_control_id' => FALSE,
																		);
											unset($punch_id);
										} else {
											Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
										}

									}
								}
							}
						}
						break;
					case 'b5': //No Break
						if ( is_array($plf) AND count($plf) > 0 ) {
							//If they are scheduled or not, we can check for a break policy and base our
							//decision off that. We don't want a No Break exception on a 3hr short shift though.
							//Also ignore this exception if the break is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							$daily_total_time = 0;
							$udtlf = $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10 ) ); //Worked time only.
							if ( is_array( $udtlf ) AND count( $udtlf ) > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
									$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
								}
							}
							unset($udtlf, $udt_obj);
							Debug::text(' Daily Total Time: '. $daily_total_time .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

							//Find break policy
							//Use scheduled break policy first.
							$break_policy_obj = NULL;

							//Enable $always_return_at_least_one=TRUE so no matter what at least one break policy is returned if it exists.
							//This allows us to *not* trigger this exception when the user works less than the break policy trigger time.
							$break_time_policies = $this->filterBreakTimePolicy( $date_stamp, $daily_total_time, array( 15, 20 ), TRUE ); //Exclude auto-deduct break policies.
							if ( is_array($break_time_policies) AND count($break_time_policies) > 0 ) {
								reset($break_time_policies);
								$break_policy_obj = $break_time_policies[key( $break_time_policies )]; //Get first
							} elseif ( is_array($break_time_policies) AND count($break_time_policies) == 0 ) {
								$break_policy_obj = NULL; //Schedule defined, but no break policy applies.
							} else {
								//There is no  break policy or schedule policy with a break policy assigned to it
								//With out this we could still apply No break exceptions, but they will happen even on
								//a 2minute shift.
								Debug::text('No Break policy, applying No break exception.', __FILE__, __LINE__, __METHOD__, 10);
								$break_policy_obj = TRUE;
							}
							unset($break_time_policies);

							if ( is_object($break_policy_obj) OR $break_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
								//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__, 10);

								if ( $daily_total_time > 0 AND ( $break_policy_obj === TRUE OR $daily_total_time > $break_policy_obj->getTriggerTime() ) ) {
									//Check for break punch.
									$break_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf as $p_obj ) {
										if ( $p_obj->getType() == 30 ) { //30 = Break
											Debug::text('Found break Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10);
											$break_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $break_policy_obj === TRUE OR $tmp_punch_total_time > $break_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't break the loop here, as we have to continue on and check for other breaks.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									//If the last punch is before the premature delay, make this a mature exception instead.
									if ( $type_id == 5 AND $p_obj->getTimeStamp() < ($current_epoch - $premature_delay) ) {
										$type_id = 50;
									}
									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $break_punch == FALSE ) {
										Debug::text('Triggering No Break exception!', __FILE__, __LINE__, __METHOD__, 10);
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
							unset($break_time_policies, $break_policy_obj, $tmp_punch_control_ids, $udtlf);
						}
						break;
					case 'v1': //TimeSheet Not Verified
						//Get pay period schedule data, determine if timesheet verification is even enabled.
						if ( is_object( $this->pay_period_obj )
								AND is_object( $this->pay_period_schedule_obj )
								AND $this->pay_period_schedule_obj->getTimeSheetVerifyType() > 10 ) {
							Debug::text('Verification enabled... Window Start: '. TTDate::getDate('DATE+TIME', $this->pay_period_obj->getTimeSheetVerifyWindowStartDate() ) .' Grace Time: '. $ep_obj->getGrace(), __FILE__, __LINE__, __METHOD__, 10);

							//*Only* trigger this exception on the last day of the pay period, because when the pay period is verified it has to force the last day to be recalculated.
							//Ignore timesheets without any time, (worked and absence). Or we could use the Watch Window to specify the minimum time required on
							//a timesheet to trigger this instead?
							//Make sure we are after the timesheet window start date + the grace period.
							if (	$this->pay_period_obj->getStatus() != 50
									AND $current_epoch >= ($this->pay_period_obj->getTimeSheetVerifyWindowStartDate() + $ep_obj->getGrace())
									AND TTDate::getBeginDayEpoch( $date_stamp ) == TTDate::getBeginDayEpoch( $this->pay_period_obj->getEndDate() )
									) {

									//Get pay period total time, include worked and paid absence time.
									$udtlf = TTnew( 'UserDateTotalListFactory' );
									$total_time = $udtlf->getTimeSumByUserIDAndPayPeriodId( $user_id, $this->pay_period_obj->getID() );
									if ( $total_time > 0 ) {
										//Check to see if pay period has been verified or not yet.
										$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
										$pptsvlf->getByPayPeriodIdAndUserId( $this->pay_period_obj->getId(), $user_id );

										$pay_period_verified = FALSE;
										if ( $pptsvlf->getRecordCount() > 0 ) {
											$pay_period_verified = $pptsvlf->getCurrent()->getAuthorized();
										}

										if ( $pay_period_verified == FALSE ) {
											//Always allow for emailing this exception because it can be triggered after a punch is modified and
											//any supervisor would need to be notified to verify the timesheet again.
											$current_exceptions[] = array(
																			'user_id' => $user_id,
																			'date_stamp' => $date_stamp,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																			'enable_email_notification' => TRUE,
																		);
										} else {
											Debug::text('TimeSheet has already been authorized!', __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										Debug::text('Timesheet does not have any worked or paid absence time...', __FILE__, __LINE__, __METHOD__, 10);
									}
									unset($udtlf, $total_time);
							} else {
								Debug::text('Not within timesheet verification window, or not after grace time.', __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							Debug::text('No Pay Period Schedule or TimeSheet Verificiation disabled...', __FILE__, __LINE__, __METHOD__, 10);
						}
						break;
					case 'j1': //Not Allowed on Job
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND is_array($plf) AND count($plf) > 0 ) {
							$jlf = TTnew( 'JobListFactory' );
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.

										//If the job is all the same across many punches, don't look it up every time.
										if ( !isset($j_obj) OR ( $j_obj->getId() != $p_obj->getPunchControlObject()->getJob() ) ) {
											$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										}
										
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedUser( $user_id ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											}
											//else {
											//	Debug::text('	 User allowed on Job!', __FILE__, __LINE__, __METHOD__, 10);
											//}
										} else {
											Debug::text('	 Job not found!', __FILE__, __LINE__, __METHOD__, 10);
										}
									} //else { //Debug::text('	   Not a Job Punch...', __FILE__, __LINE__, __METHOD__, 10);
								}
							}
							unset($j_obj);
						}
						break;
					case 'j2': //Not Allowed on Task
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND is_array($plf) AND count($plf) > 0 ) {
							$jlf = TTnew( 'JobListFactory' );
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 AND $p_obj->getPunchControlObject()->getJobItem() > 0 ) {
										//Found job punch, check job settings.

										//If the job is all the same across many punches, don't look it up every time.
										if ( !isset($j_obj) OR ( $j_obj->getId() != $p_obj->getPunchControlObject()->getJob() ) ) {
											$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										}

										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedItem( $p_obj->getPunchControlObject()->getJobItem() ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											}
											//else {
											//	Debug::text('	 Job item allowed on job: '. $p_obj->getPunchControlObject()->getJob(), __FILE__, __LINE__, __METHOD__, 10);
											//}
										} else {
											Debug::text('	 Job not found!', __FILE__, __LINE__, __METHOD__, 10);
										}
									} //else { //Debug::text('	   Not a Job Punch...', __FILE__, __LINE__, __METHOD__, 10);
								}
							}
							unset($j_obj);
						}
						break;
					case 'j3': //Job already completed
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND is_array($plf) AND count($plf) > 0 ) {
							$jlf = TTnew( 'JobListFactory' );
							foreach ( $plf as $p_obj ) {
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.

										//If the job is all the same across many punches, don't look it up every time.
										if ( !isset($j_obj) OR ( $j_obj->getId() != $p_obj->getPunchControlObject()->getJob() ) ) {
											$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										}

										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											//Status is completed and the User Date Stamp is greater then the job end date.
											//If no end date is set, ignore this.
											if ( $j_obj->getStatus() == 30 AND $j_obj->getEndDate() != FALSE AND $date_stamp > $j_obj->getEndDate() ) {
												$current_exceptions[] = array(
																				'user_id' => $user_id,
																				'date_stamp' => $date_stamp,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											}
											//else {
											//	Debug::text('	 Job Not Completed!', __FILE__, __LINE__, __METHOD__, 10);
											//}
										} else {
											Debug::text('	 Job not found!', __FILE__, __LINE__, __METHOD__, 10);
										}
									}
									//else {
									//	Debug::text('	 Not a Job Punch...', __FILE__, __LINE__, __METHOD__, 10);
									//}
								}
							}
							unset($j_obj);
						}
						break;
					case 'j4': //No Job or Task
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE AND is_array($plf) AND count($plf) > 0 ) {
							foreach ( $plf as $p_obj ) {
								$add_exception = FALSE;

								//In punches only
								if ( $p_obj->getStatus() == 10 AND is_object( $p_obj->getPunchControlObject() ) ) {
									//If no Tasks are setup, ignore checking them.
									if ( $p_obj->getPunchControlObject()->getJob() == ''
											OR $p_obj->getPunchControlObject()->getJob() == 0
											OR $p_obj->getPunchControlObject()->getJob() == FALSE  ) {
										//Make sure at least one task exists before triggering exception.
										$jlf = TTNew('JobListFactory');
										$jlf->getByCompanyID( $this->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $jlf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									if ( $p_obj->getPunchControlObject()->getJobItem() == ''
											OR $p_obj->getPunchControlObject()->getJobItem() == 0
											OR $p_obj->getPunchControlObject()->getJobItem() == FALSE ) {

										//Make sure at least one task exists before triggering exception.
										$jilf = TTNew('JobItemListFactory');
										$jilf->getByCompanyID( $this->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $jilf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									if ( $add_exception === TRUE ) {
										$current_exceptions[] = array(
																		'user_id' => $user_id,
																		'date_stamp' => $date_stamp,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getId(),
																		'punch_control_id' => $p_obj->getPunchControlId(),
																	);
									}
								}
							}
						}
						break;
					default:
						Debug::text('BAD, should never get here: '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);
						break;
				}
			}

			$exceptions = $ep_obj->diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions );
			if ( is_array($exceptions) ) {
				if ( isset($exceptions['create_exceptions']) AND is_array($exceptions['create_exceptions']) AND count($exceptions['create_exceptions']) > 0 ) {
					Debug::text('Creating new exceptions... Total: '. count($exceptions['create_exceptions']), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $exceptions['create_exceptions'] as $tmp_exception ) {
						$ef = TTnew( 'ExceptionFactory' );
						$ef->setUser( $tmp_exception['user_id'] );
						$ef->setDateStamp( $tmp_exception['date_stamp'] );
						$ef->setExceptionPolicyID( $tmp_exception['exception_policy_id'] );
						$ef->setType( $tmp_exception['type_id'] );
						if ( isset($tmp_exception['punch_control_id']) AND $tmp_exception['punch_control_id'] != '' ) {
							$ef->setPunchControlId( $tmp_exception['punch_control_id'] );
						}
						if ( isset($tmp_exception['punch_id']) AND $tmp_exception['punch_id'] != '' ) {
							$ef->setPunchId( $tmp_exception['punch_id'] );
						}
						$ef->setEnableDemerits( TRUE );
						if ( $ef->isValid() ) {
							$ef->Save( FALSE ); //Save exception prior to emailing it, otherwise we can't save audit logs.
							if ( $enable_premature_exceptions == TRUE OR ( isset($tmp_exception['enable_email_notification']) AND $tmp_exception['enable_email_notification'] == TRUE ) ) {
								$eplf = TTnew( 'ExceptionPolicyListFactory' );
								$eplf->getById( $tmp_exception['exception_policy_id'] );
								if ( $eplf->getRecordCount() == 1 ) {
									$ep_obj = $eplf->getCurrent();
									$ef->emailException( $this->getUserObject(), $date_stamp, ( isset($tmp_exception['punch_obj']) ) ? $tmp_exception['punch_obj'] : NULL, ( isset($tmp_exception['schedule_obj']) ) ? $tmp_exception['schedule_obj'] : NULL, $ep_obj );
								}
							} else {
								Debug::text('Not emailing new exception: User ID: '. $tmp_exception['user_id'] .' Date Stamp: '. $tmp_exception['date_stamp'] .' Type ID: '. $tmp_exception['type_id'] .' Enable PreMature: '. (int)$enable_premature_exceptions, __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						unset($ef);
					}
				}

				if ( isset($exceptions['delete_exceptions']) AND is_array($exceptions['delete_exceptions']) AND count($exceptions['delete_exceptions']) > 0 ) {
					$ef = TTnew( 'ExceptionFactory' );
					$ef->bulkDelete( $exceptions['delete_exceptions'] );
				}
			}
		}

		Debug::text('No exception policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getExceptionPolicy() {
		$eplf = TTnew( 'ExceptionPolicyListFactory' );
		$eplf->getByPolicyGroupUserIdAndActive( $this->getUserObject()->getId(), TRUE );
		if ( $eplf->getRecordCount() > 0 ) {
			Debug::text(' Found Active Exceptions: '.  $eplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $eplf as $ep_obj ) {
				$this->exception_policy[$ep_obj->getId()] = $ep_obj;
			}

			return TRUE;
		}

		Debug::text('No exception policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getExceptionData( $start_date = NULL, $end_date = NULL ) {
		$elf = TTNew('ExceptionListFactory');
		$elf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUserObject()->getId(), $start_date, $end_date );
		if ( $elf->getRecordCount() > 0 ) {
			Debug::text('Found existing exception rows: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $elf as $e_obj ) {
				$this->exception[$e_obj->getID()] = $e_obj;
			}

			return TRUE;
		}

		Debug::text('No exception rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterPunchDataByDateAndTypeAndStatus( $date_stamp, $type_ids = NULL, $status_ids = NULL ) {
		$plf = $this->punch;
		Debug::text('Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($plf) AND count($plf) > 0 ) {
			foreach( $plf as $p_obj ) {
				//TTDate::getMiddleDayEpoch( $p_obj->getTimeStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp )
				if ( TTDate::getMiddleDayEpoch( TTDate::strtotime( $p_obj->getColumn('date_stamp') ) ) == TTDate::getMiddleDayEpoch( $date_stamp )
						AND ( $type_ids == NULL OR in_array( $p_obj->getType(), (array)$type_ids ) )
						AND ( $status_ids == NULL OR in_array( $p_obj->getStatus(), (array)$status_ids ) ) ) {
						$retarr[$p_obj->getId()] = $p_obj;
				}
				//else {
					//Debug::text('Punch does not match filter: '. $p_obj->getID() .' DateStamp: '. TTDate::getDate('DATE', $p_obj->getTimeStamp() ) .' Status: '. $p_obj->getStatus() .' Type: '. $p_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}

			if ( isset($retarr) ) {
				Debug::text('Found punch rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No punch rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPunchData( $start_date, $end_date ) {
		if ( is_object( $this->pay_period_schedule_obj ) ) {
			$maximum_shift_time = $this->pay_period_schedule_obj->getMaximumShiftTime();
		} else {
			$maximum_shift_time = (16 * 3600);
		}

		$plf = TTnew( 'PunchListFactory' );
		//We need to double the maximum shift time when searching for punches.
		//Assuming a maximum punch time of 14hrs:
		// In: 10:00AM Out: 2:00PM
		// In: 6:00PM Out: 6:00AM (next day)
		// The above scenario when adding the last 6:00AM punch on the next day will only look back 14hrs and not find the first
		// punch pair, therefore allowing more than 14hrs on the same day.
		// So we need to extend the maximum shift time just when searching for punches and let getShiftData() sort out the proper maximum shift time itself.
		//$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch, $punch_control_id, ( $maximum_shift_time * 2 ) );
		$plf->getShiftPunchesByUserIDAndStartDateAndEndDate( $this->getUserObject()->getId(), $start_date, $end_date, 0, ( $maximum_shift_time * 2 ) );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::text('Found punch rows: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $plf as $p_obj ) {
				$this->punch[$p_obj->getID()] = $p_obj;
			}

			return TRUE;
		}

		Debug::text('No punch rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getContributingShiftPolicy() {
		$this->contributing_shift_policy_ids = array();

		$rtplf = $this->regular_time_policy;
		if ( is_array($rtplf) AND count($rtplf) > 0 ) {
			foreach( $rtplf as $rtp_obj ) {
				$this->contributing_shift_policy_ids[] = $rtp_obj->getContributingShiftPolicy();
			}
		}
		unset($rtplf, $rtp_obj);

		$otplf = $this->over_time_policy;
		if ( is_array($otplf) AND count($otplf) > 0 ) {
			foreach( $otplf as $otp_obj ) {
				$this->contributing_shift_policy_ids[] = $otp_obj->getContributingShiftPolicy();
			}
		}
		unset($otplf, $otp_obj);

		$hplf = $this->holiday_policy;
		if ( is_array($hplf) AND count($hplf) > 0 ) {
			foreach( $hplf as $hp_obj ) {
				$this->contributing_shift_policy_ids[] = $hp_obj->getContributingShiftPolicy();
				$this->contributing_shift_policy_ids[] = $hp_obj->getEligibleContributingShiftPolicy();
			}
		}
		unset($hplf, $hp_obj);

		$pplf = $this->premium_time_policy;
		if ( is_array($pplf) AND count($pplf) > 0 ) {
			foreach( $pplf as $pp_obj ) {
				$this->contributing_shift_policy_ids[] = $pp_obj->getContributingShiftPolicy();
			}
		}
		unset($pplf, $pp_obj);

		$pfplf = $this->pay_formula_policy;
		if ( is_array($pfplf) AND count($pfplf) > 0 ) {
			foreach( $pfplf as $pfp_obj ) {
				if ( $pfp_obj->getWageSourceContributingShiftPolicy() > 0 ) {
					$this->contributing_shift_policy_ids[] = $pfp_obj->getWageSourceContributingShiftPolicy();
					$this->contributing_shift_policy_ids[] = $pfp_obj->getTimeSourceContributingShiftPolicy();
				}
			}
		}
		unset($pfplf, $pfp_obj);

		$aplf = $this->accrual_policy;
		if ( is_array($aplf) AND count($aplf) > 0 ) {
			foreach( $aplf as $ap_obj ) {
				$this->contributing_shift_policy_ids[] = $ap_obj->getContributingShiftPolicy();
			}
		}
		unset($aplf, $ap_obj);

		$this->contributing_shift_policy_ids = array_unique( $this->contributing_shift_policy_ids );
		if ( count($this->contributing_shift_policy_ids) > 0 ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' );
			$csplf->getByIdAndCompanyId( $this->contributing_shift_policy_ids, $this->getUserObject()->getCompany() );
			if ( $csplf->getRecordCount() > 0 ) {
				Debug::text('Found contributing shift policy rows: '. $csplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				//$this->contributing_shift_policy_rs = $csplf;
				foreach( $csplf as $csp_obj ) {
					$this->contributing_shift_policy[$csp_obj->getId()] = $csp_obj;
				}

				//Debug::Arr($this->contributing_shift_policy, 'Contributing shift policy rows...', __FILE__, __LINE__, __METHOD__, 10);

				return TRUE;
			}
		}

		Debug::text('No contributing shift policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getContributingPayCodePolicy() {
		$csplf = $this->contributing_shift_policy;
		if ( is_array( $csplf ) AND count( $csplf ) > 0 ) {
			foreach( $csplf as $csp_obj ) {
				$this->contributing_pay_code_policy_ids[] = $csp_obj->getContributingPayCodePolicy();
			}
			unset($csp_obj);

			if ( count($this->contributing_pay_code_policy_ids) > 0 ) {
				$cpcplf = TTnew( 'ContributingPayCodePolicyListFactory' );
				$cpcplf->getByIdAndCompanyId( $this->contributing_pay_code_policy_ids, $this->getUserObject()->getCompany() );
				if ( $cpcplf->getRecordCount() > 0 ) {
					Debug::text('Found contributing pay code policy rows: '. $cpcplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					//$this->contributing_pay_code_policy_rs = $cpcplf;

					foreach( $cpcplf as $cpcp_obj ) {
						$this->contributing_pay_code_policy[$cpcp_obj->getId()] = $cpcp_obj;
						$this->contributing_pay_codes_by_policy_id[$cpcp_obj->getId()] = $cpcp_obj->getPayCode();
					}

					//Debug::Arr($this->contributing_pay_codes_by_policy_id, 'Contributing pay code policy rows...', __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		Debug::text('No contributing pay code policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Need to get all pay codes referenced by policies and all pay codes used by contributing shift policies too.
	//So we may as well just get them all.
	function getPayCode() {
		$pclf = TTnew( 'PayCodeListFactory' );
		$pclf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pclf->getRecordCount() > 0 ) {
			foreach( $pclf as $pc_obj ) {
				$this->pay_codes[$pc_obj->getId()] = $pc_obj;
			}

			Debug::Text('Pay code rows: '. count( $this->pay_codes ), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No pay code rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPayFormulaPolicyObject( $obj ) {
		if ( $obj->getPayFormulaPolicy() > 0 AND isset($this->pay_formula_policy[$obj->getPayFormulaPolicy()]) ) {
			return $this->pay_formula_policy[$obj->getPayFormulaPolicy()];
		} elseif ( $obj->getPayCode() > 0 AND isset($this->pay_codes[$obj->getPayCode()]) AND isset($this->pay_formula_policy[$this->pay_codes[$obj->getPayCode()]->getPayFormulaPolicy()]) ) {
			return $this->pay_formula_policy[$this->pay_codes[$obj->getPayCode()]->getPayFormulaPolicy()];
		}

		Debug::text('No pay formula policy assigned...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isPayFormulaPolicyAveraging( $pay_formula_policy_id, $pay_code_id ) {
		if ( isset( $this->pay_formula_policy[$pay_formula_policy_id] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$pay_formula_policy_id];
		} elseif ( isset( $this->pay_codes[$pay_code_id] ) AND $this->pay_codes[$pay_code_id]->getPayFormulaPolicy() > 0 AND isset( $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()];
		} else {
			Debug::text('  No Pay Formula Policy to use...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $pay_formula_policy_obj->getWageSourceType() == 30 ) {
			Debug::text('  Pay Formula is averaging...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		return FALSE;
	}

	//Need to get all pay formula policies referenced by policies and all pay codes too.
	//So we may as well just get them all.
	function getPayFormulaPolicy() {
		$pfplf = TTnew( 'PayFormulaPolicyListFactory' );
		$pfplf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pfplf->getRecordCount() > 0 ) {
			foreach( $pfplf as $pfp_obj ) {
				$this->pay_formula_policy[$pfp_obj->getId()] = $pfp_obj;
			}

			Debug::Text('Pay Formula Policy rows: '. count( $this->pay_formula_policy ), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No pay formula policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getAverageHourlyRate( $date_stamp, $hour_contributing_shift_policy_obj, $hour_object_type_ids, $wage_contributing_shift_policy_obj ) {
		//To determine average rate we need to seperate what hours are included and what dollars are included.

		$total_time = 0;
		$total_wages = 0;

		$start_date = TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id );
		$end_date = TTDate::getEndWeekEpoch( $date_stamp, $this->start_week_day_id );

		$this->getRequiredData( $end_date ); //Use the end of the week date stamp.
		$this->addPendingCalculationDate( $start_date, $end_date );
		
		//Get total hours.
		//Don't include Meal/Break time though, as its already calculated in the Regular Time.
		$hour_udt_rows = $this->filterUserDateTotalDataByContributingShiftPolicy( $start_date, $end_date, $hour_contributing_shift_policy_obj, array(20, 25, 30, 40) );
		if ( is_array($hour_udt_rows) AND count($hour_udt_rows) > 0 ) {
			foreach( $hour_udt_rows as $udt_obj ) {
				$total_time += $udt_obj->getTotalTime();
			}
		}
		//Debug::text('Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
		unset($hour_udt_rows, $udt_obj);

		if ( $total_time != 0 ) { //Handle average wages for negative values too.
			//Get total wages. Normally this will include almost all object types.
			//Don't include Meal/Break time though, as its already calculated in the Regular Time.
			$wage_udt_rows = $this->filterUserDateTotalDataByContributingShiftPolicy( $start_date, $end_date, $wage_contributing_shift_policy_obj, array(20, 25, 30, 40) );
			if ( is_array($wage_udt_rows) AND count($wage_udt_rows) > 0 ) {
				foreach( $wage_udt_rows as $udt_obj ) {
					if ( $udt_obj->getObjectType() == 30 ) { //Overtime.
						Debug::text('Overtime, using base hourly rate: '. $udt_obj->getBaseHourlyRate() .' Total Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
						$tmp_wages = ( $udt_obj->getBaseHourlyRate() * TTDate::getHours( $udt_obj->getTotalTime() ) );
					} else {
						$tmp_wages = $udt_obj->getTotalTimeAmount();
					}

					//Debug::text('Adding wages: '. $tmp_wages, __FILE__, __LINE__, __METHOD__, 10);
					$total_wages += $tmp_wages;
				}
			}
			//Debug::text('Total Wages: '. $total_wages, __FILE__, __LINE__, __METHOD__, 10);
			unset($wage_udt_rows, $udt_obj );

			$average_hourly_rate = ( $total_wages / TTDate::getHours( $total_time ) );
			Debug::text('Total Time: '. $total_time .' Wages: '. $total_wages .' Average Hourly Rate: '. $average_hourly_rate .' DateStamp: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
			return $average_hourly_rate;
		}

		return FALSE;
	}

	function getBaseHourlyRate( $pay_formula_policy_id, $pay_code_id, $date_stamp, $contributing_pay_code_hourly_rate = FALSE, $contributing_shift_policy_obj = NULL, $object_type_ids = NULL ) {
		$pay_code_id = (int)$pay_code_id;
		$pay_formula_policy_id = (int)$pay_formula_policy_id;

		Debug::text('Pay Code ID: '. $pay_code_id .' DateStamp: '. $date_stamp .' Contributing Pay Code Hourly Rate: '. $contributing_pay_code_hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		$hourly_rate = 0;
		$tmp_hourly_rate = 0;

		if ( isset( $this->pay_formula_policy[$pay_formula_policy_id] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$pay_formula_policy_id];
		} elseif ( isset( $this->pay_codes[$pay_code_id] ) AND $this->pay_codes[$pay_code_id]->getPayFormulaPolicy() > 0 AND isset( $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()];
		} else {
			Debug::text('  No Pay Formula Policy to use...', __FILE__, __LINE__, __METHOD__, 10);
			return 0;
		}

		switch ( $pay_formula_policy_obj->getWageSourceType()  ) {
			case 10: //Wage Group
				$uw_obj = $this->filterUserWage( $pay_formula_policy_obj->getWageGroup(), $date_stamp );
				if ( is_object( $uw_obj) ) {
					$tmp_hourly_rate = $uw_obj->getHourlyRate();
				}
				break;
			case 20: //Contributing Pay Code
				$tmp_hourly_rate = $contributing_pay_code_hourly_rate;
				break;
			case 30: //Average Contributing Pay Codes
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					Debug::text('  Average Contributing Pay Codes... Determine Average Hourly Rate...', __FILE__, __LINE__, __METHOD__, 10);
					$tmp_hourly_rate = $this->getAverageHourlyRate( $date_stamp, $this->contributing_shift_policy[$pay_formula_policy_obj->getTimeSourceContributingShiftPolicy()], $object_type_ids, $this->contributing_shift_policy[$pay_formula_policy_obj->getWageSourceContributingShiftPolicy()] );
				}
				break;
		}
		$hourly_rate = $tmp_hourly_rate;

		Debug::text('  Base Hourly Rate: '. $hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		return $hourly_rate;
	}

	function getHourlyRate( $pay_formula_policy_id, $pay_code_id, $base_hourly_rate ) {
		$pay_code_id = (int)$pay_code_id;
		$pay_formula_policy_id = (int)$pay_formula_policy_id;

		Debug::text('Pay Formula ID: '. $pay_formula_policy_id .' Pay Code ID: '. $pay_code_id .' Base Hourly Rate: '. $base_hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		$hourly_rate = 0;
		if ( isset( $this->pay_formula_policy[$pay_formula_policy_id] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$pay_formula_policy_id];
		} elseif ( isset( $this->pay_codes[$pay_code_id] ) AND $this->pay_codes[$pay_code_id]->getPayFormulaPolicy() > 0 AND isset( $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()];
		} else {
			Debug::text('  No Pay Formula Policy to use...', __FILE__, __LINE__, __METHOD__, 10);
			return 0;
		}

		if ( is_object( $pay_formula_policy_obj ) ) {
			$hourly_rate = $pay_formula_policy_obj->getHourlyRate( $base_hourly_rate );
		}

		if ( $this->pay_codes[$pay_code_id]->getType() == 30 AND $hourly_rate > 0 ) { //Dock Pay
			$hourly_rate = ( $hourly_rate * -1 );
		}

		Debug::text('  Hourly Rate: '. $hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		return $hourly_rate;
	}

	function getHourlyRateWithBurden( $pay_formula_policy_id, $pay_code_id, $date_stamp, $base_hourly_rate = 0 ) {
		$pay_code_id = (int)$pay_code_id;
		$pay_formula_policy_id = (int)$pay_formula_policy_id;

		$hourly_rate = 0;
		if ( isset( $this->pay_formula_policy[$pay_formula_policy_id] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$pay_formula_policy_id];
		} elseif ( isset( $this->pay_codes[$pay_code_id] ) AND $this->pay_codes[$pay_code_id]->getPayFormulaPolicy() > 0 AND isset( $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()] ) ) {
			$pay_formula_policy_obj = $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()];
		} else {
			Debug::text('  No Pay Formula Policy to use...', __FILE__, __LINE__, __METHOD__, 10);
			return 0;
		}

		$uw_obj = $this->filterUserWage( (int)$pay_formula_policy_obj->getWageGroup(), $date_stamp );
		if ( is_object( $uw_obj) ) {
			$hourly_rate = ( $pay_formula_policy_obj->getHourlyRate( $base_hourly_rate ) * ( ( $uw_obj->getLaborBurdenPercent() / 100 ) + 1 ) );
		}

		if ( $this->pay_codes[$pay_code_id]->getType() == 30 AND $hourly_rate > 0 ) { //Dock Pay
			$hourly_rate = ( $hourly_rate * -1 );
		}

		Debug::text('  Hourly Rate w/Burden: '. $hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		return $hourly_rate;
	}

	function filterUserWage( $wage_group_id, $date_stamp ) {
		$uwlf = $this->user_wages;
		if ( is_array( $uwlf ) AND count( $uwlf ) > 0 ) {
			foreach( $uwlf as $uw_obj ) {
				if ( $uw_obj->getWageGroup() == $wage_group_id AND TTDate::getMiddleDayEpoch( $uw_obj->getEffectiveDate() ) <= TTDate::getMiddleDayEpoch( $date_stamp ) ) {
					Debug::text('User wage DOES match filter... ID: '. $uw_obj->getID() .' Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
					return $uw_obj;
				}
				//else {
				//	Debug::text('User wage does not match filter... ID: '. $uw_obj->getID() .' Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}
		}

		Debug::text('No user wage rows match filter... Date: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function UserWageSortByEffectiveDateDesc( $a, $b ) {
		if ( $a->getEffectiveDate() == $b->getEffectiveDate() ) {
			//Compare updated dates instead, so hopefully in cases where two wage entries on the same date exist we will pick the newest one.
			return ( $a->getUpdatedDate() < $b->getUpdatedDate() ) ? 1 : (-1);
		}
		return ( $a->getEffectiveDate() < $b->getEffectiveDate() ) ? 1 : (-1);
	}
	function getUserWageData( $start_date, $end_date ) {
		$uwlf = TTnew('UserWageListFactory');
		$uwlf->getByUserIdAndStartDateAndEndDate( $this->getUserObject()->getId(), $start_date, $end_date );
		if ( $uwlf->getRecordCount() > 0 ) {
			foreach( $uwlf as $uw_obj ) {
				$this->user_wages[$uw_obj->getId()] = $uw_obj;
			}

			//Because wage entries can be added as different dates are calculated, the order isn't guaranteed.
			//Therefore manually sort the entries again each time new data is retrieved.
			uasort( $this->user_wages, array( $this, 'UserWageSortByEffectiveDateDesc' ) );

			Debug::Text('User wage rows: '. count( $this->user_wages ), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No user wage rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}


	function filterCurrencyRate( $date_stamp ) {
		$crlf = $this->currency_rates;
		if ( is_array( $crlf ) AND count( $crlf ) > 0 ) {
			foreach( $crlf as $cr_obj ) {
				if ( TTDate::getMiddleDayEpoch( $cr_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp ) ) {
					//Debug::text('User wage DOES match filter... ID: '. $uw_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					return $cr_obj;
				}
				//else {
					//Debug::text('Currency rate does not match filter... ID: '. $cr_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}
		}

		Debug::text('No currency rate rows match filter...', __FILE__, __LINE__, __METHOD__, 10);

		$crf = TTnew('CurrencyRateFactory');
		$crf->setCurrency( $this->getUserObject()->getCurrency() );
		$crf->setDateStamp( $date_stamp );
		$crf->setConversionRate( 1 );

		return $crf;
	}

	function getCurrencyRateData( $start_date, $end_date ) {
		$crlf = TTnew('CurrencyRateListFactory');
		$crlf->getByCurrencyIdAndStartDateAndEndDate( $this->getUserObject()->getCurrency(), $start_date, $end_date );
		if ( $crlf->getRecordCount() > 0 ) {
			foreach( $crlf as $cr_obj ) {
				$this->currency_rates[$cr_obj->getDateStamp()] = $cr_obj;
			}
			Debug::Text('Currency Rates rows before gaps filled: '. count( $this->currency_rates ), __FILE__, __LINE__, __METHOD__, 10);

			//Loop through all days and fill in any currency gaps.
			for( $x = TTDate::getBeginDayEpoch( $start_date ); $x <= TTDate::getBeginDayEpoch( $end_date ); $x += 86400 ) {
				if ( !isset($this->currency_rates[$x]) ) {
					Debug::Text(' Filling in gap: Date: '. TTDate::getDate('DATE', $x ) .' with Rate: 1', __FILE__, __LINE__, __METHOD__, 10);

					$crf = TTnew('CurrencyRateFactory');
					$crf->setCurrency( $this->getUserObject()->getCurrency() );
					$crf->setDateStamp( $x );
					$crf->setConversionRate( 1 );
					$this->currency_rates[$crf->getDateStamp()] = $crf;
				}
			}
			
			Debug::Text('Currency Rates rows after gaps filled: '. count( $this->currency_rates ), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('No currency rate rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculatePremiumTimePolicy( $date_stamp, $maximum_daily_total_time = NULL ) {
		//Loop over all premium time policies calculating the pay codes
		$premium_time_policies = $this->filterPremiumTimePolicy( $date_stamp );
		if ( is_array( $premium_time_policies ) AND count($premium_time_policies) > 0 ) {
			foreach( $premium_time_policies as $pp_obj ) {
				Debug::text('Found Premium Policy: Name: '. $pp_obj->getName() .'('. $pp_obj->getId() .') Type: '. $pp_obj->getType() .' DateStamp: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

				if ( !isset($this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]) ) {
					Debug::text(' ERROR: Contributing Shift Policy for Premium Policy: '. $pp_obj->getName() .' does not exist...', __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				$user_date_total_rows = $this->compactMealAndBreakUserDateTotalObjects( $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()], array( 20, 25, 30, 100, 110 ) ) );
				$user_date_total_rows_count = count($user_date_total_rows);
				if ( is_array($user_date_total_rows) AND $user_date_total_rows_count > 0 ) {
					switch( $pp_obj->getType() ) {
						case 10: //Date/Time
						case 100: //Advanced
						case 90: //Holiday (coverts to Date/Time policy automatically)
							if ( is_object( $this->pay_period_schedule_obj ) ) {
								$maximum_shift_time = $this->pay_period_schedule_obj->getMaximumShiftTime();
							}
							if ( !isset($maximum_shift_time) OR $maximum_shift_time < 86400 ) {
								$maximum_shift_time = 86400;
							}

							if ( $pp_obj->getType() == 90 )	{
								Debug::text(' Holiday Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
								//Determine if the employee is eligible for holiday premium.
								$holiday_obj = $this->filterHoliday( $date_stamp );
								if ( !is_object($holiday_obj) ) {
									$holiday_obj = $this->filterHoliday( ($date_stamp - $maximum_shift_time) );
									if ( !is_object($holiday_obj) ) {
										$holiday_obj = $this->filterHoliday( ($date_stamp + $maximum_shift_time) );
									}
								}

								if ( is_object( $holiday_obj ) ) {
									Debug::text(' Found Holiday: '. $holiday_obj->getName() .' Date: '. TTDate::getDate('DATE', $holiday_obj->getDateStamp() ) .' Current Date: '.	TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
									if ( $this->holiday_policy[$holiday_obj->getHolidayPolicyID()]->getForceOverTimePolicy() == TRUE
											OR $this->isEligibleForHoliday( $date_stamp, $this->holiday_policy[$holiday_obj->getHolidayPolicyID()] ) ) {
										Debug::text(' User is Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

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
								unset($holiday_obj);
							}

							//Make sure this is a valid day
							//Take into account shifts that span midnight though, where one half of the shift is eligilble for premium time.
							//ie: Premium Policy starts 7AM to 7PM on Sat/Sun. Punches in at 9PM Friday and out at 9AM Sat, we need to check if both days are valid.
							if ( $pp_obj->isActive( ( $date_stamp - $maximum_shift_time ), ( $date_stamp + $maximum_shift_time ), $this ) ) {
								Debug::text(' Premium Policy Is Active On OR Around This Day.', __FILE__, __LINE__, __METHOD__, 10);

								$total_daily_time_used = 0;
								$daily_trigger_time = 0;
								$maximum_daily_trigger_time = FALSE;

								if ( $pp_obj->isHourRestricted() == TRUE ) {
									if ( $pp_obj->getWeeklyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) {
										//Get Pay Period Schedule info
										if ( is_object( $this->pay_period_schedule_obj ) ) {
											$start_week_day_id = $this->pay_period_schedule_obj->getStartWeekDay();
										} else {
											$start_week_day_id = 0;
										}
										Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

										$weekly_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getBeginWeekEpoch($date_stamp, $this->start_week_day_id), ( $date_stamp - 86400 ), $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()], array( 20, 25, 30, 100, 110 ) ) );
										if ( $weekly_total_time > $pp_obj->getWeeklyTriggerTime() ) {
											$daily_trigger_time = 0;
										} else {
											$daily_trigger_time = ( $pp_obj->getWeeklyTriggerTime() - $weekly_total_time );
										}
										Debug::text(' Weekly Trigger Time: '. $daily_trigger_time .' Raw Weekly Time: '. $weekly_total_time, __FILE__, __LINE__, __METHOD__, 10);
									}

									if ( $pp_obj->getDailyTriggerTime() > 0 AND $pp_obj->getDailyTriggerTime() > $daily_trigger_time) {
										$daily_trigger_time = $pp_obj->getDailyTriggerTime();
									}

									if ( $pp_obj->getMaximumDailyTriggerTime() > 0 OR $pp_obj->getMaximumWeeklyTriggerTime() > 0  ) {
										$maximum_daily_trigger_time = ( $pp_obj->getMaximumDailyTriggerTime() > 0 ) ? ($pp_obj->getMaximumDailyTriggerTime()) : FALSE;
										$maximum_weekly_trigger_time = ( isset($weekly_total_time) AND $pp_obj->getMaximumWeeklyTriggerTime() > 0 ) ? ($pp_obj->getMaximumWeeklyTriggerTime() - $weekly_total_time) : FALSE;

										Debug::text(' Maximum Daily: '. $maximum_daily_trigger_time .' Weekly: '. $maximum_weekly_trigger_time .' Daily Total Time Used: '. $total_daily_time_used .' Daily Trigger Time: '. $daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										if ( $maximum_daily_trigger_time > 0 AND ( $maximum_weekly_trigger_time === FALSE OR $maximum_daily_trigger_time < $maximum_weekly_trigger_time ) ) {
											$pp_obj->setMaximumTime( $maximum_daily_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
											Debug::text(' Set Daily Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
										} else {
											if ( $maximum_weekly_trigger_time !== FALSE AND ( $maximum_weekly_trigger_time <= 0 OR ( $maximum_weekly_trigger_time < $daily_trigger_time ) ) ) {
												Debug::text(' Exceeded Weekly Maximum Time to: '. $pp_obj->getMaximumTime() .' Skipping...', __FILE__, __LINE__, __METHOD__, 10);
												continue;
											}

											if ( $maximum_weekly_trigger_time < $pp_obj->getMaximumTime() ) {
												$pp_obj->setMaximumTime( $maximum_weekly_trigger_time ); //Temporarily set the maximum time in memory so it doesn't exceed the maximum daily trigger time.
											}
											Debug::text(' Set Weekly Maximum Time to: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);
											$maximum_daily_trigger_time = $maximum_weekly_trigger_time;
										}
										unset($maximum_weekly_trigger_time);
									}
								}
								Debug::text(' Daily Trigger Time: '. $daily_trigger_time .' Max: '. $maximum_daily_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

								$i = 1;
								foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
									Debug::text('UserDateTotal ID: '. $udt_obj->getID() .' Total Time: '. $udt_obj->getTotalTime() .' I: '. $i, __FILE__, __LINE__, __METHOD__, 10);

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
									if ( ($pp_obj->getIncludePartialPunch() == TRUE OR $pp_obj->isTimeRestricted() == TRUE ) ) {
										Debug::text('Time Restricted Premium Policy... Using Start/End timestamps...', __FILE__, __LINE__, __METHOD__, 10);

										//Do this outside the user_date_total_rows loop
										if ( $pp_obj->getIncludePartialPunch() == FALSE ) {
											$shift_data = $this->getShiftData( $user_date_total_rows, $udt_obj->getStartTimeStamp(), 'nearest_shift', NULL, $pp_obj->getMinimumTimeBetweenShift() );
										}

										if ( $pp_obj->getIncludePartialPunch() == TRUE ) {
											$punch_times['in'] = $udt_obj->getStartTimeStamp();
											$punch_times['out'] = $udt_obj->getEndTimeStamp();
										} elseif ( isset($shift_data) AND is_array( $shift_data ) AND isset($shift_data['first_in']) AND isset($user_date_total_rows[$shift_data['first_in']]) ) {
											$punch_times['in'] = $user_date_total_rows[$shift_data['first_in']]->getStartTimeStamp();
											$punch_times['out'] = $user_date_total_rows[$shift_data['last_out']]->getEndTimeStamp();
										} else {
											Debug::text('ERROR: No punch times...', __FILE__, __LINE__, __METHOD__, 10);
										}

										//How do we handle "shifts" when we can include absence pay codes?
										//When its time restricted we ignore absences or any record without in/out times.
										$punch_total_time = 0;
										if ( isset($punch_times) AND count($punch_times) == 2
												AND $punch_times['in'] != '' AND $punch_times['out'] != '' ) {
											if (  ( $pp_obj->isActiveDate( $punch_times['in'] ) == TRUE OR $pp_obj->isActiveDate( $punch_times['out'] ) == TRUE )
													AND ( $pp_obj->isActive( $punch_times['in'], $punch_times['out'], $this ) == TRUE )
													AND $pp_obj->isActiveTime( $punch_times['in'], $punch_times['out'], $this ) == TRUE ) {
												//Debug::Arr($punch_times, 'Punch Times: ', __FILE__, __LINE__, __METHOD__, 10);
												$punch_total_time = $pp_obj->getPartialPunchTotalTime( $punch_times['in'], $punch_times['out'], $udt_obj->getTotalTime(), $this );
												Debug::text('Valid Punch pair in active time, Partial Punch Total Time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);
											} else {
												Debug::text('InValid Punch Pair or outside Active Time...', __FILE__, __LINE__, __METHOD__, 10);
											}
										} else {
											Debug::text('No timestamps...', __FILE__, __LINE__, __METHOD__, 10);
										}
										unset($punch_times);
									} elseif ( $pp_obj->isActive( $udt_obj->getDateStamp(), NULL, $this->getUserObject()->getId() ) == TRUE )  {
										$punch_total_time = $udt_obj->getTotalTime();
									} else {
										$punch_total_time = 0;
									}

									//Why is $tmp_punch_total_time not just $punch_total_time? Are the partial punches somehow separate from the meal/break calculation?
									//Yes, because tmp_punch_total_time is the DAILY total time used, whereas punch_total_time can be a partial shift. Without this the daily trigger time won't work.
									$tmp_punch_total_time = $udt_obj->getTotalTime();
									Debug::text('aPunch Total Time: '. $punch_total_time .' TMP Punch Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									$total_daily_time_used += $tmp_punch_total_time;
									Debug::text('Daily Total Time Used: '. $total_daily_time_used .' Maximum Trigger Time: '. $maximum_daily_trigger_time .' This Record: '. ($total_daily_time_used - $tmp_punch_total_time), __FILE__, __LINE__, __METHOD__, 10);
									Debug::text('Daily Trigger Time: '. $daily_trigger_time .' TMP: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

									//That way if the policy is active after 7.5hrs, punch time of exactly 7.5hrs will still
									//activate the policy, rather then requiring 7.501hrs+
									//Make sure we allow for UDT records that are just negative values for lunch/break audo-deduct.
									//  How to handle auto-deduct/auto-add meal polcies for this and Shift Differential/Callback premiums?
									if (
											(
												(
													( $punch_total_time > 0 AND $total_daily_time_used > $daily_trigger_time )
													OR
													( $punch_total_time < 0 AND ( $total_daily_time_used - abs($tmp_punch_total_time) ) > $daily_trigger_time )
												)
												AND ( $maximum_daily_trigger_time === FALSE OR ( $maximum_daily_trigger_time !== FALSE AND ($total_daily_time_used - abs($tmp_punch_total_time) ) < $maximum_daily_trigger_time ) )
											)
										) {
									/*
									//The below helped deal with negative time entries, before compactin meal/break policy function was added.
									if (
											(
												$punch_total_time > 0
												AND $total_daily_time_used > $daily_trigger_time
												AND ( $maximum_daily_trigger_time === FALSE OR ( $maximum_daily_trigger_time !== FALSE AND ($total_daily_time_used - abs($tmp_punch_total_time) ) < $maximum_daily_trigger_time ) )
											)
											OR
											(
												(
													$punch_total_time < 0 AND $daily_trigger_time == 0
													AND $total_daily_time_used > $daily_trigger_time
													AND ( $maximum_daily_trigger_time === FALSE OR ( $maximum_daily_trigger_time !== FALSE AND ($total_daily_time_used ) < $maximum_daily_trigger_time ) )
												)
												OR
												(
													$punch_total_time < 0 AND $daily_trigger_time > 0
													AND ( $total_daily_time_used - $tmp_punch_total_time ) > $daily_trigger_time
												)
											)
										) {
										*/
										Debug::text('Past Trigger Time!! '. ($total_daily_time_used - $tmp_punch_total_time), __FILE__, __LINE__, __METHOD__, 10);

										//Calculate how far past trigger time we are.
										$past_trigger_time = ( $total_daily_time_used - $daily_trigger_time );
										if ( $punch_total_time > $past_trigger_time ) {
										//if ( $past_trigger_time > 0 AND $daily_trigger_time > 0 AND $punch_total_time > $past_trigger_time ) { //This helped deal with negative time entries, before compactin meal/break policy function was added.
											$punch_total_time = $past_trigger_time;
											Debug::text('Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										} else {
											Debug::text('NOT Using Past Trigger Time as punch total time: '. $past_trigger_time, __FILE__, __LINE__, __METHOD__, 10);
										}

										//If we are close to exceeding the maximum daily/weekly time, just use the remaining time.
										if ( $maximum_daily_trigger_time > 0 AND $total_daily_time_used > $maximum_daily_trigger_time ) {
											Debug::text('Using New Maximum Trigger Time as punch total time: '. $maximum_daily_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
											$punch_total_time = ( $punch_total_time - ($total_daily_time_used - $maximum_daily_trigger_time) );
										} else {
											Debug::text('NOT Using New Maximum Trigger Time as punch total time: '. $maximum_daily_trigger_time .'('. $total_daily_time_used.')', __FILE__, __LINE__, __METHOD__, 10);
										}

										$total_time = $punch_total_time;
										if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {

											//$premium_policy_daily_total_time = (int)$udtlf->getTotalSumByUserIdAndDateStampAndObjectTypeAndObjectID( $this->getUserObject()->getId(), $date_stamp, 40, $pp_obj->getId() );
											$premium_policy_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByPayCodeIDs( $date_stamp, $date_stamp, $pp_obj->getPayCode() ) );
											Debug::text(' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

											if ( $pp_obj->getMinimumTime() > 0 ) {
												//FIXME: Split the minimum time up between all the punches somehow.
												//Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
												//and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
												//for the day. If its applied to the last it will be just 1hr.
												//Min & Max time is based on the shift time, rather then per punch pair time.
												//FIXME: If there is a minimum time set to say 9hrs, and the punches go like this:
												// In: 7:00AM Out: 3:00:PM, Out: 3:30PM (missing 2nd In Punch), the minimum time won't be calculated due to the invalid punch pair.
												if ( $i == $user_date_total_rows_count AND bcadd( $premium_policy_daily_total_time, $total_time ) < $pp_obj->getMinimumTime() ) {
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
										if ( $total_time != 0 ) { //Need to handle negative values too for things like lunch auto-deduct.
											Debug::text(' Applying Premium Time!: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

											$create_udt_record = FALSE;
											if ( $pp_obj->getType() == 100 ) {
												//Check Shift Differential criteria *AFTER* calculatating daily/weekly time, as the shift differential
												//applies to the resulting time calculation, not the daily/weekly time calculation. Daily/Weekly should always include all time.
												//This is fundamentally different than the Shift Differential premium policy type.
												if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getBranchSelectionType(), $pp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $pp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
													//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
													if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getDepartmentSelectionType(), $pp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $pp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
														//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
														$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
														if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobGroupSelectionType(), NULL, $job_group, $pp_obj->getJobGroup() ) ) {
															//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
															if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobSelectionType(), $pp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $pp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
																//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
																$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
																if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $pp_obj->getJobItemGroup() ) ) {
																	//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
																	if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemSelectionType(), $pp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $pp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
																		//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
																		$create_udt_record = TRUE;
																	}
																}
															}
														}
													}
												}
											} else {
												$create_udt_record = TRUE;
											}

											if ( $create_udt_record == TRUE ) {
												Debug::text('Generating UserDateTotal object from Premium Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 40 .' Pay Code ID: '. (int)$pp_obj->getPayCode() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
												if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
													$udtf = TTnew( 'UserDateTotalFactory' );
													$udtf->setUser( $this->getUserObject()->getId() );
													$udtf->setDateStamp( $date_stamp );
													$udtf->setObjectType( 40 ); //Premium Time
													$udtf->setSourceObject( (int)$pp_obj->getId() );
													$udtf->setPayCode( (int)$pp_obj->getPayCode() );

													$udtf->setBranch( (int)$udt_obj->getBranch() );
													$udtf->setDepartment( (int)$udt_obj->getDepartment() );
													$udtf->setJob( (int)$udt_obj->getJob() );
													$udtf->setJobItem( (int)$udt_obj->getJobItem() );

													if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
														$udtf->setStartType( $udt_obj->getStartType() );
														$udtf->setEndType( $udt_obj->getEndType() );
														$udtf->setStartTimeStamp( ( $udt_obj->getEndTimeStamp() - $total_time ) );
														$udtf->setEndTimeStamp( ( $udtf->getStartTimeStamp() + $total_time ) );
														//Debug::text('        Current Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ) .' Covered Time: '. $covered_time .' Adjust: '. $adjust_covered_time, __FILE__, __LINE__, __METHOD__, 10);
														//Debug::text('        Premium Start Time Stamp: '. TTDate::getDate('DATE+TIME', $udtf->getStartTimeStamp() ) .' End: '.  TTDate::getDate('DATE+TIME', $udtf->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
													}

													$udtf->setQuantity( $udt_obj->getQuantity() );
													$udtf->setBadQuantity( $udt_obj->getBadQuantity() );
													$udtf->setTotalTime( $total_time );

													$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
													$udtf->setHourlyRate( $this->getHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
													$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

													$udtf->setEnableCalcSystemTotalTime(FALSE);
													$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

													if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
														//Don't save the record, just add it to the existing array, so it can be included in other calculations.
														//We will save these records at the end.
														$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
														$this->user_date_total_insert_id--;
													}
												} else {
													Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
												}
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
							unset($udtlf, $udt_obj);
							break;
						case 20: //Differential
							Debug::text(' Differential Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);

							$i = 1;
							foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
								//Ignore incomplete punches
								if ( $udt_obj->getTotalTime() == 0 ) {
									continue;
								}

								if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getBranchSelectionType(), $pp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $pp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
									//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
									if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getDepartmentSelectionType(), $pp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $pp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
										//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
										$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
										if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobGroupSelectionType(), NULL, $job_group, $pp_obj->getJobGroup() ) ) {
											//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
											if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobSelectionType(), $pp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $pp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
												//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
												$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
												if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $pp_obj->getJobItemGroup() ) ) {
													//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
													if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemSelectionType(), $pp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $pp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
														//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

														$premium_policy_daily_total_time = 0;
														$punch_total_time = $udt_obj->getTotalTime();
														$total_time = 0;

														$total_time = $punch_total_time;
														if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
															//$premium_policy_daily_total_time = (int)$udtlf->getTotalSumByUserIdAndDateStampAndObjectTypeAndObjectID( $this->getUserObject()->getId(), $date_stamp, 40, $pp_obj->getId() );
															$premium_policy_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByPayCodeIDs( $date_stamp, $date_stamp, $pp_obj->getPayCode() ) );
															Debug::text(' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

															if ( $pp_obj->getMinimumTime() > 0 ) {
																//FIXME: Split the minimum time up between all the punches somehow.
																if ( $i == $user_date_total_rows_count AND bcadd( $premium_policy_daily_total_time, $total_time ) < $pp_obj->getMinimumTime() ) {
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
														if ( $total_time != 0 ) { //Need to handle negative values too for things like lunch auto-deduct.
															Debug::text('Generating UserDateTotal object from Premium Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 40 .' Pay Code ID: '. (int)$pp_obj->getPayCode() .' Total Time: '. $total_time .' UDT ObjectType: '. $udt_obj->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);
															if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
																$udtf = TTnew( 'UserDateTotalFactory' );
																$udtf->setUser( $this->getUserObject()->getId() );
																$udtf->setDateStamp( $date_stamp );
																$udtf->setObjectType( 40 ); //Premium Time
																$udtf->setSourceObject( (int)$pp_obj->getId() );
																$udtf->setPayCode( (int)$pp_obj->getPayCode() );

																$udtf->setBranch( (int)$udt_obj->getBranch() );
																$udtf->setDepartment( (int)$udt_obj->getDepartment() );
																$udtf->setJob( (int)$udt_obj->getJob() );
																$udtf->setJobItem( (int)$udt_obj->getJobItem() );

																if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
																	$udtf->setStartType( $udt_obj->getStartType() );
																	$udtf->setEndType( $udt_obj->getEndType() );
																	$udtf->setStartTimeStamp( $udt_obj->getStartTimeStamp() );
																	$udtf->setEndTimeStamp( ( $udtf->getStartTimeStamp() + $total_time ) );
																}

																$udtf->setQuantity( $udt_obj->getQuantity() );
																$udtf->setBadQuantity( $udt_obj->getBadQuantity() );
																$udtf->setTotalTime( $total_time );

																$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
																$udtf->setHourlyRate( $this->getHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
																$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

																$udtf->setEnableCalcSystemTotalTime(FALSE);
																$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

																if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
																	//Don't save the record, just add it to the existing array, so it can be included in other calculations.
																	//We will save these records at the end.
																	$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
																	$this->user_date_total_insert_id--;
																}
															} else {
																Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
															}
														} else {
															Debug::text(' Premium Punch Total Time is 0...', __FILE__, __LINE__, __METHOD__, 10);
														}
													}
												}
											}
										}
									}

									$i++;
								}
							}
							break;
						case 30: //Meal/Break
							Debug::text(' Meal/Break Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
							if ( $pp_obj->getDailyTriggerTime() == 0
									OR ( $pp_obj->getDailyTriggerTime() > 0 AND $daily_total_time >= $pp_obj->getDailyTriggerTime() ) ) {

								$prev_punch_timestamp = NULL;
								$maximum_time_worked_without_break = 0;

								foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
									//Ignore incomplete punches
									if ( $udt_obj->getTotalTime() == 0 ) {
										continue;
									}

									//Debug::text(' UDT Start Time: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End Time: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

									//Total Punch Time
									$total_punch_pair_time = ( $udt_obj->getEndTimeStamp() - $udt_obj->getStartTimeStamp() );
									$maximum_time_worked_without_break += $total_punch_pair_time;
									Debug::text('Total Punch Pair Time: '. $total_punch_pair_time .' Maximum No Break Time: '. $maximum_time_worked_without_break, __FILE__, __LINE__, __METHOD__, 10);

									if ( $prev_punch_timestamp !== NULL ) {
										$break_time = ( $udt_obj->getStartTimeStamp() - $prev_punch_timestamp );
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

										if ( $total_time != 0 ) { //Need to handle negative values too for things like lunch auto-deduct.
											Debug::text('Generating UserDateTotal object from Premium Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 40 .' Pay Code ID: '. (int)$pp_obj->getPayCode() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
											if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
												$udtf = TTnew( 'UserDateTotalFactory' );
												$udtf->setUser( $this->getUserObject()->getId() );
												$udtf->setDateStamp( $date_stamp );
												$udtf->setObjectType( 40 ); //Premium Time
												$udtf->setSourceObject( (int)$pp_obj->getId() );
												$udtf->setPayCode( (int)$pp_obj->getPayCode() );

												$udtf->setBranch( (int)$udt_obj->getBranch() );
												$udtf->setDepartment( (int)$udt_obj->getDepartment() );
												$udtf->setJob( (int)$udt_obj->getJob() );
												$udtf->setJobItem( (int)$udt_obj->getJobItem() );

												if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
													$udtf->setStartType( $udt_obj->getStartType() );
													$udtf->setEndType( $udt_obj->getEndType() );
													$udtf->setStartTimeStamp( $udt_obj->getStartTimeStamp() );
													$udtf->setEndTimeStamp( ( $udtf->getStartTimeStamp() + $total_time ) );
												}

												$udtf->setQuantity( $udt_obj->getQuantity() );
												$udtf->setBadQuantity( $udt_obj->getBadQuantity() );
												$udtf->setTotalTime( $total_time );

												$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
												$udtf->setHourlyRate( $this->getHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
												$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

												$udtf->setEnableCalcSystemTotalTime(FALSE);
												$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

												if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
													//Don't save the record, just add it to the existing array, so it can be included in other calculations.
													//We will save these records at the end.
													$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
													$this->user_date_total_insert_id--;
												}
											} else {
												Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
											}

											break; //Stop looping through punches.
										}
									} else {
										Debug::text('Did not exceed maximum no break time yet... Time: '. $maximum_time_worked_without_break, __FILE__, __LINE__, __METHOD__, 10);
									}

									$prev_punch_timestamp = $udt_obj->getEndTimeStamp();
								}
							} else {
								Debug::text(' Not within Daily Total Time: '. $daily_total_time .' Trigger Time: '. $pp_obj->getDailyTriggerTime(), __FILE__, __LINE__, __METHOD__, 10);
							}
							break;
						case 40: //Callback
							Debug::text(' Callback Premium Policy...', __FILE__, __LINE__, __METHOD__, 10);
							Debug::text(' Minimum Time Between Shifts: '. $pp_obj->getMinimumTimeBetweenShift() .' Minimum First Shift Time: '. $pp_obj->getMinimumFirstShiftTime(), __FILE__, __LINE__, __METHOD__, 10);

							$shift_data = $this->getShiftData( $user_date_total_rows, TTDate::getMiddleDayEpoch( $date_stamp ), NULL, NULL, $pp_obj->getMinimumTimeBetweenShift() );
							Debug::Arr($shift_data, ' Shift Data...', __FILE__, __LINE__, __METHOD__, 10);

							//Only calculate if their are at least two shifts
							if ( count($shift_data) >= 2 ) {
								Debug::text(' Found at least two shifts...', __FILE__, __LINE__, __METHOD__, 10);

								$prev_key = FALSE;
								foreach( $shift_data as $key => $data ) {
									Debug::Arr($data, ' Shift Data for Shift: '. $key, __FILE__, __LINE__, __METHOD__, 10);

									//Check if previous shift is greater than minimum first shift time.
									$prev_key = ( $key - 1 );

									if ( isset($shift_data[$prev_key]) AND isset($shift_data[$prev_key]['total_time']) AND $shift_data[$prev_key]['total_time'] >= $pp_obj->getMinimumFirstShiftTime() ) {
										Debug::text(' Previous shift exceeds minimum first shift time... Shift Total Time: '. $shift_data[$prev_key]['total_time'], __FILE__, __LINE__, __METHOD__, 10);

										//Get last out time of the previous shift.
										if ( isset($shift_data[$prev_key]['last_out']) ) {

											//$previous_shift_last_out_epoch = $shift_data[$prev_key]['last_out']['time_stamp'];
											$previous_shift_last_out_epoch = $user_date_total_rows[$shift_data[$prev_key]['last_out']]->getEndTimeStamp();
											$current_shift_cutoff = ( $previous_shift_last_out_epoch + $pp_obj->getMinimumTimeBetweenShift() );
											Debug::text(' Previous Shift Last Out: '. TTDate::getDate('DATE+TIME', $previous_shift_last_out_epoch ) .'('.$previous_shift_last_out_epoch.') Current Shift Cutoff: '. TTDate::getDate('DATE+TIME', $current_shift_cutoff ) .'('. $previous_shift_last_out_epoch .')', __FILE__, __LINE__, __METHOD__, 10);

											$x = 1;
											foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
												//Debug::text('X: '. $x .'/'. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

												//Ignore incomplete punches
												if ( $udt_obj->getTotalTime() == 0 ) {
													continue;
												}

												if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
													Debug::text(' Found valid UDT KEY: '. $udt_key, __FILE__, __LINE__, __METHOD__, 10);
													Debug::text(' First Punch: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' Last Punch: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

													//The upper limit has to be the new shift trigger time, as once we start a new shift its no longer considered a callback.
													if ( $x == 1 AND ( $udt_obj->getStartTimeStamp() - $previous_shift_last_out_epoch ) > $this->pay_period_schedule_obj->getNewDayTriggerTime() ) {
														Debug::text(' Greater than NewDayTrigger time, skipping...', __FILE__, __LINE__, __METHOD__, 10);
														continue;
													}

													$punch_total_time = 0;
													$force_minimum_time_calculation = FALSE;

													//Make sure all punches are after the cutoff time, so we only include time considered to be "callback"/
													if ( $udt_obj->getStartTimeStamp() >= $current_shift_cutoff ) {
														Debug::text(' Both punches are AFTER the cutoff time...', __FILE__, __LINE__, __METHOD__, 10);
														//$punch_total_time = bcsub( $punch_pairs[$udt_obj->getPunchControlID()][1]['time_stamp'], $punch_pairs[$udt_obj->getPunchControlID()][0]['time_stamp']);
														$punch_total_time = $udt_obj->getTotalTime();
													} else {
														Debug::text(' Both punches are BEFORE the cutoff time... Skipping...', __FILE__, __LINE__, __METHOD__, 10);
														$punch_total_time = 0;
													}
													Debug::text(' Punch Total Time: '. $punch_total_time, __FILE__, __LINE__, __METHOD__, 10);

													$premium_policy_daily_total_time = 0;
													if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
														//$premium_policy_daily_total_time = (int)$udtlf->getTotalSumByUserIdAndDateStampAndObjectTypeAndObjectID( $this->getUserObject()->getId(), $date_stamp, 40, $pp_obj->getId() );
														$premium_policy_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByPayCodeIDs( $date_stamp, $date_stamp, $pp_obj->getPayCode() ) );
														Debug::text('X: '. $x .'/'. $user_date_total_rows_count .' Premium Policy Daily Total Time: '. $premium_policy_daily_total_time .' Minimum Time: '. $pp_obj->getMinimumTime() .' Maximum Time: '. $pp_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

														if ( $pp_obj->getMinimumTime() > 0 ) {
															//FIXME: Split the minimum time up between all the punches somehow.
															//Apply the minimum time on the last punch, otherwise if there are two punch pairs of 15min each
															//and a 1hr minimum time, if the minimum time is applied to the first, it will be 1hr and 15min
															//for the day. If its applied to the last it will be just 1hr.
															//Min & Max time is based on the shift time, rather then per punch pair time.
															if ( ( $force_minimum_time_calculation == TRUE OR $x == $user_date_total_rows_count ) AND bcadd( $premium_policy_daily_total_time, $punch_total_time ) < $pp_obj->getMinimumTime() ) {
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
													if ( $total_time != 0 ) { //Need to handle negative values too for things like lunch auto-deduct.
														//Debug::text(' Applying	Premium Time!: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

														Debug::text('Generating UserDateTotal object from Premium Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 40 .' Pay Code ID: '. (int)$pp_obj->getPayCode() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
														if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
															$udtf = TTnew( 'UserDateTotalFactory' );
															$udtf->setUser( $this->getUserObject()->getId() );
															$udtf->setDateStamp( $date_stamp );
															$udtf->setObjectType( 40 ); //Premium Time
															$udtf->setSourceObject( (int)$pp_obj->getId() );
															$udtf->setPayCode( (int)$pp_obj->getPayCode() );

															$udtf->setBranch( (int)$udt_obj->getBranch() );
															$udtf->setDepartment( (int)$udt_obj->getDepartment() );
															$udtf->setJob( (int)$udt_obj->getJob() );
															$udtf->setJobItem( (int)$udt_obj->getJobItem() );

															if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
																$udtf->setStartType( $udt_obj->getStartType() );
																$udtf->setEndType( $udt_obj->getEndType() );
																$udtf->setStartTimeStamp( $udt_obj->getStartTimeStamp() );
																$udtf->setEndTimeStamp( ( $udtf->getStartTimeStamp() + $total_time ) );
															}

															$udtf->setQuantity( $udt_obj->getQuantity() );
															$udtf->setBadQuantity( $udt_obj->getBadQuantity() );
															$udtf->setTotalTime( $total_time );

															$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
															$udtf->setHourlyRate( $this->getHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
															$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

															$udtf->setEnableCalcSystemTotalTime(FALSE);
															$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

															if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
																//Don't save the record, just add it to the existing array, so it can be included in other calculations.
																//We will save these records at the end.
																$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
																$this->user_date_total_insert_id--;
															}
														} else {
															Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
														}
													}
												} else {
													Debug::text(' Skipping invalid Punch Control ID: '. $udt_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
												}

												$x++;
											}
										}
									}
								}
							}
							unset( $shift_data, $x, $udtf, $udt_obj );
							break;
						case 50: //Minimum shift time
							Debug::text(' Minimum Shift Time Premium Policy... Minimum Shift Time: '. $pp_obj->getMinimumShiftTime() .' User ID: '. $this->getUserObject()->getId() .' DateStamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);

							foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
								//Ignore incomplete punches
								if ( $udt_obj->getTotalTime() == 0 ) {
									continue;
								}

								if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getBranchSelectionType(), $pp_obj->getExcludeDefaultBranch(), $udt_obj->getBranch(), $pp_obj->getBranch(), $this->getUserObject()->getDefaultBranch() ) ) {
									//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $pp_obj->getBranchSelectionType() .' Exclude Default Branch: '. (int)$pp_obj->getExcludeDefaultBranch() .' Default Branch: '.  $this->getUserObject()->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);
									if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getDepartmentSelectionType(), $pp_obj->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $pp_obj->getDepartment(), $this->getUserObject()->getDefaultDepartment() ) ) {
										//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $pp_obj->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$pp_obj->getExcludeDefaultDepartment() .' Default Department: '.  $this->getUserObject()->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);
										$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
										if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobGroupSelectionType(), NULL, $job_group, $pp_obj->getJobGroup() ) ) {
											//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $pp_obj->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
											if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobSelectionType(), $pp_obj->getExcludeDefaultJob(), $udt_obj->getJob(), $pp_obj->getJob(), $this->getUserObject()->getDefaultJob() ) ) {
												//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
												$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
												if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemGroupSelectionType(), NULL, $job_item_group, $pp_obj->getJobItemGroup() ) ) {
													//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $pp_obj->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
													if ( $this->contributing_shift_policy[$pp_obj->getContributingShiftPolicy()]->checkIndividualDifferentialCriteria( $pp_obj->getJobItemSelectionType(), $pp_obj->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $pp_obj->getJobItem(), $this->getUserObject()->getDefaultJobItem() ) ) {
														//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $pp_obj->getJobSelectionType(), __FILE__, __LINE__, __METHOD__, 10);
														$tmp_user_date_total_rows[] = $udt_obj;
													}
												}
											}
										}
									}
								}
							}

							if ( isset($tmp_user_date_total_rows) ) {
								//This used to have differential criteria that could be specified as well, but now that contributing shifts exist, use those instead.
								$shift_data = $this->getShiftData( $tmp_user_date_total_rows, TTDate::getMiddleDayEpoch( $date_stamp ), NULL, NULL, $pp_obj->getMinimumTimeBetweenShift() );
								Debug::Arr($shift_data, ' Shift Data...', __FILE__, __LINE__, __METHOD__, 10);

								if ( is_array($shift_data) ) {
									$total_shifts = count($shift_data);
									$x = 1;
									foreach( $shift_data as $shift_data_arr )  {
										$total_time = 0;
										$punch_total_time = $shift_data_arr['total_time'];
										if ( $punch_total_time == 0 ) { //Skip shift if its not complete.
											continue;
										}

										if ( $punch_total_time > $pp_obj->getMinimumShiftTime() ) {
											Debug::text(' Shift exceeds minimum shift time...', __FILE__, __LINE__, __METHOD__, 10);
											continue;
										} else {
											$punch_total_time = bcsub( $pp_obj->getMinimumShiftTime(), $punch_total_time );
										}

										$premium_policy_daily_total_time = 0;
										if ( $pp_obj->getMinimumTime() > 0 OR $pp_obj->getMaximumTime() > 0 ) {
											$premium_policy_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByPayCodeIDs( $date_stamp, $date_stamp, $pp_obj->getPayCode() ) );
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
										if ( $total_time != 0 ) { //Need to handle negative values too for things like lunch auto-deduct.
											Debug::text(' Applying	Premium Time!: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

											//Find branch, department, job, task of last punch_control_id in shift.
											if ( isset($shift_data_arr['last_out']) AND isset($tmp_user_date_total_rows[$shift_data_arr['last_out']]) ) {
												$udt_obj = $tmp_user_date_total_rows[$shift_data_arr['last_out']];

												Debug::text('Generating UserDateTotal object from Premium Time Policy, ID: '. $this->user_date_total_insert_id .' Object Type ID: '. 40 .' Pay Code ID: '. (int)$pp_obj->getPayCode() .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
												if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
													$udtf = TTnew( 'UserDateTotalFactory' );
													$udtf->setUser( $this->getUserObject()->getId() );
													$udtf->setDateStamp( $date_stamp );
													$udtf->setObjectType( 40 ); //Premium Time
													$udtf->setSourceObject( (int)$pp_obj->getId() );
													$udtf->setPayCode( (int)$pp_obj->getPayCode() );

													$udtf->setBranch( (int)$udt_obj->getBranch() );
													$udtf->setDepartment( (int)$udt_obj->getDepartment() );
													$udtf->setJob( (int)$udt_obj->getJob() );
													$udtf->setJobItem( (int)$udt_obj->getJobItem() );

													if ( $udt_obj->getStartTimeStamp() != '' AND $udt_obj->getEndTimeStamp() != '' ) {
														$udtf->setStartType( $udt_obj->getStartType() );
														$udtf->setEndType( $udt_obj->getEndType() );
														$udtf->setStartTimeStamp( $udt_obj->getEndTimeStamp() );
														$udtf->setEndTimeStamp( ( $udtf->getStartTimeStamp() + $total_time ) );
													}

													$udtf->setQuantity( $udt_obj->getQuantity() );
													$udtf->setBadQuantity( $udt_obj->getBadQuantity() );
													$udtf->setTotalTime( $total_time );

													$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udt_obj->getHourlyRate() ) );
													$udtf->setHourlyRate( $this->getHourlyRate( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
													$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $pp_obj->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

													$udtf->setEnableCalcSystemTotalTime(FALSE);
													$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

													//Don't save the record, just add it to the existing array, so it can be included in other calculations.
													//We will save these records at the end.
													$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
													$this->user_date_total_insert_id--;
												} else {
													Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
												}
											}
										}
										$x++;
									}
								}
							} else {
								Debug::text('  Differential Criteria filtered out all UDT rows...', __FILE__, __LINE__, __METHOD__, 10);
							}

							unset($tmp_user_date_total_rows, $shift_data, $total_shifts, $udtf, $udt_obj);
							break;
					}
				} else {
					Debug::text('No matching UserDateTotal rows...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			return TRUE;
		}

		Debug::text('No premium time policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterPremiumTimePolicy( $date_stamp ) {
		$pplf = $this->premium_time_policy;
		if ( is_array( $pplf ) AND count( $pplf ) > 0 ) {
			$schedule_policy_premium_time_policy_ids = array();
			$schedule_policy_exclude_premium_time_policy_ids = array();
			$schedule_policy_arr = $this->filterSchedulePolicyByDate( $date_stamp );
			if ( is_array( $schedule_policy_arr ) ) {
				foreach( $schedule_policy_arr as $sp_obj ) {
					if ( is_array( $sp_obj->getIncludePremiumPolicy() ) AND count( $sp_obj->getIncludePremiumPolicy() ) > 0 ) {
						$schedule_policy_premium_time_policy_ids = array_merge( $schedule_policy_premium_time_policy_ids, (array)$sp_obj->getIncludePremiumPolicy() );
					}
					if ( is_array( $sp_obj->getExcludePremiumPolicy() ) AND count( $sp_obj->getExcludePremiumPolicy() ) > 0 ) {
						$schedule_policy_exclude_premium_time_policy_ids = array_merge( $schedule_policy_exclude_premium_time_policy_ids, (array)$sp_obj->getExcludePremiumPolicy() );
					}
				}
				Debug::Arr($schedule_policy_premium_time_policy_ids, 'Premium Policies that apply to: '. TTDate::getDate('DATE', $date_stamp) .' from schedule policies: ', __FILE__, __LINE__, __METHOD__, 10);
			}

			foreach( $pplf as $pp_obj ) {
				//Filter out premium policies that aren't within the active start/end dates.
				//This can help significantly when many premium policies exist.
				if (
						(
							( (int)$pp_obj->getColumn('is_policy_group') == 1 AND !in_array( $pp_obj->getId(), $schedule_policy_exclude_premium_time_policy_ids ) )
							OR
							( (int)$pp_obj->getColumn('is_policy_group') == 0 AND in_array( $pp_obj->getId(), $schedule_policy_premium_time_policy_ids ) )
						)
						AND
						(
							$pp_obj->getType() == 90 //If its a Holiday Premium policy we always need to include it due to different shift times.
							OR
							$pp_obj->isActiveDate( TTDate::getMiddleDayEpoch( $date_stamp ), 86400 ) == TRUE //Need to handle shifts that span midnight, so filter policies active on the day before, current date, and day after as well.
						)
					) {
					$retarr[$pp_obj->getId()] = $pp_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found premium time policies that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No premium time policies apply on date: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPremiumTimePolicy() {
		$this->schedule_premium_time_policy_ids = array();

		$splf = $this->schedule_policy_rs;
		if ( is_array($splf) AND count($splf) > 0 ) {
			foreach( $splf as $sp_obj ) {
				if ( is_array( $sp_obj->getIncludePremiumPolicy() ) AND count( $sp_obj->getIncludePremiumPolicy() ) > 0 ) {
					$this->schedule_premium_time_policy_ids = array_merge( $this->schedule_premium_time_policy_ids, (array)$sp_obj->getIncludePremiumPolicy() );
				}
			}
			unset($sp_obj);
		}

		$pplf = TTnew( 'PremiumPolicyListFactory' );
		$pplf->getByPolicyGroupUserIdOrId( $this->getUserObject()->getId(), $this->schedule_premium_time_policy_ids );
		if ( $pplf->getRecordCount() > 0 ) {
			Debug::text('Found premium policy rows: '. $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $pplf as $pp_obj ) {
				$this->premium_time_policy[$pp_obj->getId()] = $pp_obj;
			}

			return TRUE;
		}

		Debug::text('No premium policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function calculateAccrualPolicy() {
		$aplf = $this->accrual_policy;
		if ( is_array($aplf) AND count($aplf) > 0 ) {
			foreach( $aplf as $ap_obj ) {
				if ( !isset($this->contributing_shift_policy[$ap_obj->getContributingShiftPolicy()]) ) {
					Debug::text('No contributing shift policy defined for accrual policy, skipping...', __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}
				
				if ( isset($this->dates['calculated']) AND count($this->dates['calculated']) > 0 ) {
					$first_date_stamp = key( array_slice( $this->dates['calculated'], 0, 1, TRUE ) );
					$last_date_stamp = key( array_slice( $this->dates['calculated'], -1, 1, TRUE ) );
					Debug::Text('  First Date Stamp: '. TTDate::getDate('DATE', $first_date_stamp) .' Last Date Stamp: '. TTDate::getDate('DATE', $last_date_stamp), __FILE__, __LINE__, __METHOD__, 10);

					foreach( $this->dates['calculated'] as $date_stamp => $tmp ) {
						if ( $ap_obj->getMinimumEmployedDays() == 0
								OR TTDate::getDays( ($date_stamp - $this->getUserObject()->getHireDate()) ) >= $ap_obj->getMinimumEmployedDays() ) {
							Debug::Text('  User has been employed long enough.', __FILE__, __LINE__, __METHOD__, 10);

							$inception_total_time = FALSE;
							if ( $ap_obj->isHourBasedLengthOfService() == TRUE ) {
								//For hour based length of services, we need to get all time that matches contributing shift policy back to their hire date.
								if ( $inception_total_time == FALSE ) { //Try to only call to the DB once for the entire range.
									$this->getUserDateTotalData( $this->getUserObject()->getHireDate(), TTDate::getMiddleDayEpoch( $last_date_stamp ) );

									//As an optimization, calculate inception total time from the hire date to the first date we calculated.
									//Then we can just add time from the first date to the current date being calcluated.
									$base_inception_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( $this->getUserObject()->getHireDate(), ( TTDate::getMiddleDayEpoch( $first_date_stamp ) - 86400 ), $this->contributing_shift_policy[$ap_obj->getContributingShiftPolicy()] ) );
								}
								$additional_inception_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getMiddleDayEpoch( $first_date_stamp ), TTDate::getMiddleDayEpoch( $date_stamp ), $this->contributing_shift_policy[$ap_obj->getContributingShiftPolicy()] ) );
								$inception_total_time = ( $base_inception_total_time + $additional_inception_total_time );
								Debug::Text('  Inception Total Time: '. $inception_total_time .' Base: '. $base_inception_total_time .' Additional: '. $additional_inception_total_time, __FILE__, __LINE__, __METHOD__, 10);
							}

							$user_date_total_rows = $this->filterUserDateTotalDataByContributingShiftPolicy( $date_stamp, $date_stamp, $this->contributing_shift_policy[$ap_obj->getContributingShiftPolicy()] );
							if ( is_array($user_date_total_rows) AND count($user_date_total_rows) > 0 ) {
								foreach( $user_date_total_rows as $udt_key => $udt_obj ) {
									//Need to check milestone after every UDT row so we can detect switching milestones quickly.
									//FIXME: Handle switching milestones at exactly the right second, even mid-UDT row.
									$milestone_obj = $ap_obj->getActiveMilestoneObject( $this->getUserObject(), $date_stamp, $inception_total_time );
									$accrual_balance = $ap_obj->getCurrentAccrualBalance( $this->getUserObject()->getId(), $ap_obj->getId() );

									//If Maximum time is set to 0, make that unlimited.
									if ( is_object($milestone_obj) AND ( $milestone_obj->getMaximumTime() == 0 OR $accrual_balance < $milestone_obj->getMaximumTime() ) ) {
										$accrual_amount = $ap_obj->calcAccrualAmount( $milestone_obj, $udt_obj->getTotalTime(), 0);

										if ( $accrual_amount > 0 ) {
											$new_accrual_balance = bcadd( $accrual_balance, $accrual_amount);

											//If Maximum time is set to 0, make that unlimited.
											if ( $milestone_obj->getMaximumTime() > 0 AND $new_accrual_balance > $milestone_obj->getMaximumTime() ) {
												$accrual_amount = bcsub( $milestone_obj->getMaximumTime(), $accrual_balance, 4 );
											}
											Debug::Text('	Min/Max Adjusted Accrual Amount: '. $accrual_amount .' Limits: Min: '. $milestone_obj->getMinimumTime() .' Max: '. $milestone_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10);

											//It would be nice to find a way to compact these accrual records,
											//as right now there could be many (hundreds) per day and it makes viewing the accrual balance difficult.
											//Not sure if that is really possible though, as we won't be able to link directly to UserDateTotalID's then
											//and that will make it impossible to figure out orphaned records.
											//Solution is to link to the object_type_id=5 (system total time) record for each day.
											if ( isset($accrual_compact_arr[(int)$ap_obj->getAccrualPolicyAccount()][(int)$ap_obj->getId()][$date_stamp]) ) {
												$accrual_compact_arr[(int)$ap_obj->getAccrualPolicyAccount()][(int)$ap_obj->getId()][$date_stamp] += $accrual_amount;
											} else {
												$accrual_compact_arr[(int)$ap_obj->getAccrualPolicyAccount()][(int)$ap_obj->getId()][$date_stamp] = $accrual_amount;
											}
											unset($accrual_amount, $accrual_balance, $new_accrual_balance);
										} else {
											Debug::Text('	Accrual Amount is 0...', __FILE__, __LINE__, __METHOD__, 10);
										}
									} else {
										Debug::Text('	Accrual Balance is outside Milestone Range. Or no milestone found. Skipping...', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							}
						} else {
							Debug::Text('  User has only been employed: '. TTDate::getDays( ($date_stamp - $this->getUserObject()->getHireDate()) ) .' Days, not enough.', __FILE__, __LINE__, __METHOD__, 10);
						}

						//Handled by deleteSystemTotalTime() instead, in case there are no accrual policies assigned anymore.
						//AccrualFactory::deleteOrphans( $this->getUserObject()->getId(), $date_stamp );
					}
				}
			}

			//Insert compacted Accrual records.
			if ( isset($accrual_compact_arr) AND is_array( $accrual_compact_arr ) AND count( $accrual_compact_arr ) > 0 ) {
				foreach( $accrual_compact_arr as $accrual_policy_account_id => $data1 ) {
					foreach( $data1 as $accrual_policy_id => $data2 ) {
						foreach( $data2 as $date_stamp => $total_time ) {
							$af = TTnew( 'AccrualFactory' );
							$af->setUser( $this->getUserObject()->getId() );
							$af->setType( 76 ); //Hour-Based Accrual Policy
							$af->setAccrualPolicyAccount( $accrual_policy_account_id );
							$af->setAccrualPolicy( $accrual_policy_id );
							$af->setUserDateTotalID( $this->new_system_user_date_total_id[TTDate::getMiddleDayEpoch($date_stamp)] ); //Link hour based accruals to just the system total time for each day.
							$af->setAmount( $total_time );
							$af->setTimeStamp( $date_stamp );
							$af->setEnableCalcBalance( TRUE );
							if ( $af->isValid() ) {
								$insert_id = $af->Save();
								Debug::Text('	Adding Accrual Record, ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}
				}
			}
			unset( $accrual_compact_arr, $data1, $data2, $accrual_policy_account_id, $accrual_policy_id, $date_stamp, $total_time );
		} else {
			Debug::text('No hour-based accrual policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		}
		
		//Calculate non-hour based accrual policies, those attached to pay formulas.
		if ( isset($this->new_user_date_total_ids) AND count($this->new_user_date_total_ids) > 0 ) {
			foreach( $this->new_user_date_total_ids as $new_user_date_total_id ) {
				if ( isset($this->user_date_total[$new_user_date_total_id]) ) {
					$udt_obj = $this->user_date_total[$new_user_date_total_id];
					//Debug::text('UDT ID: '. $udt_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

					//Skip System or Absence (Taken) records.
					//We have to skip absence taken so when the user enters in an absence schedule, it will create a object_type_id=50 record first
					//then a object_type_id=25 record, both are considered new and would duplicate the accrual entry otherwise.
					//The above wouldn't happen if you just entered in absence time directly on the timesheet, as the object_type_id=50 record
					//is already created by the user and not by CalculatePolicy, so it would naturally be skipped in that case.
					if ( $udt_obj->getObjectType() == 5 OR $udt_obj->getObjectType() == 50 ) {
						continue;
					}

					if ( $udt_obj->getPayCode() > 0 ) {
						$pay_code_id = $udt_obj->getPayCode();
						//Debug::text('UDT ID: '. $udt_obj->getID() .' Using Direct Pay Code ID: '. $pay_code_id, __FILE__, __LINE__, __METHOD__, 10);
					} elseif ( is_object( $udt_obj->getSourceObjectObject() ) AND $udt_obj->getSourceObjectObject()->getPayCode() > 0 ) {
						$pay_code_id = $udt_obj->getSourceObjectObject()->getPayCode();
						//Debug::text('UDT ID: '. $udt_obj->getID() .' Using Source Object Pay Code ID: '. $pay_code_id, __FILE__, __LINE__, __METHOD__, 10);
					} else {
						$pay_code_id = 0;
						//Debug::text('UDT ID: '. $udt_obj->getID() .' No Pay Code ID Defined: '. $pay_code_id, __FILE__, __LINE__, __METHOD__, 10);
					}
					
					if ( $pay_code_id > 0
							AND isset($this->pay_codes[$pay_code_id])
							AND isset($this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()])
							) {
						$pay_formula_policy_obj = $this->pay_formula_policy[$this->pay_codes[$pay_code_id]->getPayFormulaPolicy()];

						if ( $pay_formula_policy_obj->getAccrualPolicyAccount() > 0 AND $pay_formula_policy_obj->getAccrualRate() != 0 ) {
							$af = TTnew( 'AccrualFactory' );
							$af->setUser( $this->getUserObject()->getID() );
							$af->setAccrualPolicyAccount( $pay_formula_policy_obj->getAccrualPolicyAccount()  );
							$af->setTimeStamp( $udt_obj->getDateStamp() );
							$af->setUserDateTotalID( $udt_obj->getID() );

							$accrual_amount = bcmul( $udt_obj->getTotalTime(), $pay_formula_policy_obj->getAccrualRate() );
							if ( $accrual_amount > 0 ) {
								$af->setType(10); //Banked
							} else {
								$af->setType(20); //Used
							}
							$af->setAmount( $accrual_amount );
							$af->setEnableCalcBalance(TRUE);

							Debug::text('Adding Accrual Entry for: '. $accrual_amount .' Based on UDT key: '. $udt_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
							if ( $af->isValid() ) {
								$af->Save();
							}
						}
					} else {
						Debug::text('Pay Code not found or invalid: '. $pay_code_id, __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}

		} else {
			Debug::text('No non-hour based accrual policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		}
		
		return TRUE;
	}

	function getAccrualPolicy() {
		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$aplf->getByPolicyGroupUserIdAndType( $this->getUserObject()->getId(), 30 ); //Hour based only.
		if ( $aplf->getRecordCount() > 0 ) {
			Debug::text('Found accrual policy rows: '. $aplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $aplf as $ap_obj ) {
				$this->accrual_policy[$ap_obj->getId()] = $ap_obj;
			}

			return TRUE;
		}

		Debug::text('No accrual policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isEligibleForHoliday( $date_stamp, $holiday_policy_obj, $ignore_after_eligibility = FALSE ) {
		//Make sure the employee has been employed long enough according to labor standards
		//Also make sure that the employee hasn't been terminated on or before the holiday.
		if ( $this->getUserObject()->getHireDate() <= ( $date_stamp - ( $holiday_policy_obj->getMinimumEmployedDays() * 86400 ) )
				AND ( $this->getUserObject()->getTerminationDate() == '' OR ( $this->getUserObject()->getTerminationDate() != '' AND $this->getUserObject()->getTerminationDate() > $date_stamp )  ) ) {
			Debug::text('Employee has been employed long enough! Holiday Policy ID: '. $holiday_policy_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $holiday_policy_obj->getType() == 20 OR $holiday_policy_obj->getType() == 30 ) {
				if ( $holiday_policy_obj->getMinimumWorkedDays() > 0 AND $holiday_policy_obj->getMinimumWorkedPeriodDays() > 0 ) {
					if ( $holiday_policy_obj->getWorkedScheduledDays() == 1 ) { //Scheduled Days
						Debug::text('BEFORE: Using scheduled days!', __FILE__, __LINE__, __METHOD__, 10);

						//Use 365days as the upper limit.
						$this->getScheduleData( ( $date_stamp - ( 86400 * 365 ) ), ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ), $holiday_policy_obj->getMinimumWorkedPeriodDays(), array( 'a.date_stamp' => 'desc' ) );

						$scheduled_date_stamps_before = $this->getScheduleDates( $this->filterScheduleDataByDateAndDirection( $date_stamp, 10, 'desc', $holiday_policy_obj->getMinimumWorkedPeriodDays() ) );
						//Debug::Arr( (array)$scheduled_date_stamps_before, 'Scheduled DateStamps Before: ', __FILE__, __LINE__, __METHOD__, 10);
						Debug::Text('Scheduled DateStamps Before: '. count((array)$scheduled_date_stamps_before), __FILE__, __LINE__, __METHOD__, 10);

						//Get the date range from the schedules dates that we found.
						$calendar_date_range = $this->getDateRangeFromDateArray( (array)$scheduled_date_stamps_before );
					} elseif( $holiday_policy_obj->getWorkedScheduledDays() == 2 ) { //Holiday Week Days
						Debug::Text('Holiday Week Days Before: '. $holiday_policy_obj->getMinimumWorkedPeriodDays(), __FILE__, __LINE__, __METHOD__, 10);
						//Need to switch to weeks rather than days.
						$calendar_date_range = array( 'start_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( 86400 * ( 7 * $holiday_policy_obj->getMinimumWorkedPeriodDays() ) ) ), 'end_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ) );
					} else { //Calendar Days
						$calendar_date_range = array( 'start_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( 86400 * $holiday_policy_obj->getMinimumWorkedPeriodDays() ) ), 'end_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ) );
					}

					//Always get UserDateTotal data for the same date range so we can determine if they worked.
					Debug::text('BEFORE: Getting data for calendar days! Start: '. TTDate::getDate('DATE', $calendar_date_range['start_date'] ) .' End: '. TTDate::getDate('DATE', $calendar_date_range['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);
					$this->getUserDateTotalData( $calendar_date_range['start_date'], $calendar_date_range['end_date'] );
					unset($calendar_date_range);
				}
				
				if ( $holiday_policy_obj->getMinimumWorkedAfterDays() > 0 AND $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() > 0 ) {
					if ( $holiday_policy_obj->getWorkedAfterScheduledDays() == 1 ) { //Scheduled Days
						Debug::text('AFTER: Using scheduled days!', __FILE__, __LINE__, __METHOD__, 10);

						//Use 365days as the upper limit.
						$this->getScheduleData( ( TTDate::getMiddleDayEpoch( $date_stamp ) + 86400 ), time(), $holiday_policy_obj->getMinimumWorkedAfterPeriodDays(), array( 'a.date_stamp' => 'asc' ) );

						$scheduled_date_stamps_after = $this->getScheduleDates( $this->filterScheduleDataByDateAndDirection( $date_stamp, 10, 'asc', $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() ) );
						//Debug::Arr( (array)$scheduled_date_stamps_after, 'Scheduled DateStamps After: ', __FILE__, __LINE__, __METHOD__, 10);
						Debug::Text('Scheduled DateStamps After: '. count((array)$scheduled_date_stamps_after), __FILE__, __LINE__, __METHOD__, 10);

						//Get the date range from the schedules dates that we found.
						$calendar_date_range = $this->getDateRangeFromDateArray( (array)$scheduled_date_stamps_after );
					} elseif( $holiday_policy_obj->getWorkedScheduledDays() == 2 ) { //Holiday Week Days
						//Need to switch to weeks rather than days.
						$calendar_date_range = array( 'start_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) + 86400 ), 'end_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) + ( 86400 * ( 7 * $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() ) ) ) );
					} else { //Calendar days
						$calendar_date_range = array( 'start_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) + 86400 ), 'end_date' => ( TTDate::getMiddleDayEpoch( $date_stamp ) + ( 86400 * $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() ) ) );
					}

					//Always get UserDateTotal data for the same date range so we can determine if they worked. 
					Debug::text('AFTER: Getting data for calendar days!', __FILE__, __LINE__, __METHOD__, 10);
					$this->getUserDateTotalData( $calendar_date_range['start_date'], $calendar_date_range['end_date'] );
					unset($calendar_date_range);
				}

				$worked_before_days_count = 0;
				if ( $holiday_policy_obj->getMinimumWorkedDays() > 0 AND $holiday_policy_obj->getMinimumWorkedPeriodDays() > 0 ) {
					if ( isset($scheduled_date_stamps_before) AND $holiday_policy_obj->getWorkedScheduledDays() == 1 ) { //Scheduled Days
						$worked_before_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( $scheduled_date_stamps_before, FALSE, $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
					} elseif ( $holiday_policy_obj->getWorkedScheduledDays() == 2 ) {  //Holiday Week Days
						//Start/End date should reflect weeks, no days here.
						$worked_before_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getDateArray( ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( ($holiday_policy_obj->getMinimumWorkedPeriodDays() * 7) * 86400 ) ), ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ), TTDate::getDayOfWeek( $date_stamp ) ), FALSE, $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
					} else { //Calendar Days
						$worked_before_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( ( $date_stamp - ( $holiday_policy_obj->getMinimumWorkedPeriodDays() * 86400) ), ( $date_stamp - 86400 ), $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
					}
				}
				Debug::text('Employee has worked the prior: '. $worked_before_days_count .' days (Must be at least: '. $holiday_policy_obj->getMinimumWorkedDays() .')', __FILE__, __LINE__, __METHOD__, 10);

				$worked_after_days_count = 0;
				if ( $ignore_after_eligibility == TRUE ) {
					$worked_after_days_count = $holiday_policy_obj->getMinimumWorkedAfterDays();
					Debug::text('Ignoring worked after criteria...', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					if ( $holiday_policy_obj->getMinimumWorkedAfterDays() > 0 AND $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() > 0 ) {
						if ( isset($scheduled_date_stamps_after) AND $holiday_policy_obj->getWorkedAfterScheduledDays() == 1 ) { //Scheduled Days
							$worked_after_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( $scheduled_date_stamps_after, FALSE, $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
						} elseif ( $holiday_policy_obj->getWorkedScheduledDays() == 2 ) {  //Holiday Week Days
							//Start/End date should reflect weeks, no days here.
							$worked_after_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( TTDate::getDateArray( ( TTDate::getMiddleDayEpoch( $date_stamp ) + 86400 ), ( TTDate::getMiddleDayEpoch( $date_stamp ) + ( ($holiday_policy_obj->getMinimumWorkedPeriodDays() * 7) * 86400 ) ), TTDate::getDayOfWeek( $date_stamp ) ), FALSE, $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
						} else { //Calendar Days
							$worked_after_days_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( ($date_stamp + 86400), ( $date_stamp + ( $holiday_policy_obj->getMinimumWorkedAfterPeriodDays() * 86400) ), $this->contributing_shift_policy[$holiday_policy_obj->getEligibleContributingShiftPolicy()] ) ) );
						}
					}
					Debug::text('Employee has worked the following: '. $worked_after_days_count .' days (Must be at least: '. $holiday_policy_obj->getMinimumWorkedAfterDays() .')', __FILE__, __LINE__, __METHOD__, 10);
				}

				//Make sure employee has worked for a portion of those days.
				if ( $worked_before_days_count >= $holiday_policy_obj->getMinimumWorkedDays()
						AND $worked_after_days_count >= $holiday_policy_obj->getMinimumWorkedAfterDays() ) {
					Debug::text('Employee has worked enough prior and following days!', __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				} else {
					Debug::text('Employee has NOT worked enough days prior or following the holiday!', __FILE__, __LINE__, __METHOD__, 10);
				}

			} else {
				Debug::text('Standard Holiday Policy type, returning TRUE', __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		} else {
			Debug::text('Employee has NOT been employed long enough!', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('Not eligible for holiday: '. TTDate::getDate('DATE', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getHolidayTime( $date_stamp, $holiday_policy_obj ) {
		if ( $holiday_policy_obj->getType() == 30  ) { //Average
			if ( $holiday_policy_obj->getMinimumTime() > 0
					AND $holiday_policy_obj->getMaximumTime() > 0
					AND $holiday_policy_obj->getMinimumTime() == $holiday_policy_obj->getMaximumTime() ) {
				Debug::text('Min and Max times are equal.', __FILE__, __LINE__, __METHOD__, 10);
				return $holiday_policy_obj->getMinimumTime();
			}

			//Make sure we get all UserDateTotal data going back to the number of days to average the time over.
			$this->getUserDateTotalData( ( TTDate::getMiddleDayEpoch( $date_stamp ) - ( 86400 * $this->holiday_before_days ) ), ( TTDate::getMiddleDayEpoch( $date_stamp ) - 86400 ) );

			//Debug::text('Start Date: '. TTDate::getDate('DATE', ( $date_stamp - ( $holiday_policy_obj->getAverageTimeDays() * 86400) ) ) .' End: '. TTDate::getDate('DATE', ( $date_stamp - 86400 ) ), __FILE__, __LINE__, __METHOD__, 10);
			if ( $holiday_policy_obj->getAverageTimeWorkedDays() == TRUE ) {
				$last_days_worked_count = count( $this->getDayArrayUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( ( $date_stamp - ( $holiday_policy_obj->getAverageTimeDays() * 86400) ), ( $date_stamp - 86400 ), $this->contributing_shift_policy[$holiday_policy_obj->getContributingShiftPolicy()], array(20, 25, 30, 40, 100, 110 ) ) ) ); //Don't include Absence, Lunch, Break (Taken).
			} else {
				$last_days_worked_count = $holiday_policy_obj->getAverageDays();
			}
			Debug::text('Average time over days: '. $last_days_worked_count, __FILE__, __LINE__, __METHOD__, 10);

			$total_seconds_worked = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByContributingShiftPolicy( ( $date_stamp - ( $holiday_policy_obj->getAverageTimeDays() * 86400) ), ( $date_stamp - 86400 ), $this->contributing_shift_policy[$holiday_policy_obj->getContributingShiftPolicy()], array(20, 25, 30, 40, 100, 110 ) ) );

			if ( $last_days_worked_count > 0 ) {
				$avg_seconds_worked_per_day = bcdiv($total_seconds_worked, $last_days_worked_count);
				Debug::text('AVG hours worked per day: '. TTDate::getHours( $avg_seconds_worked_per_day ), __FILE__, __LINE__, __METHOD__, 10);
			} else {
				$avg_seconds_worked_per_day = 0;
			}

			if ( $holiday_policy_obj->getMaximumTime() > 0
					AND $avg_seconds_worked_per_day > $holiday_policy_obj->getMaximumTime() ) {
				$avg_seconds_worked_per_day = $holiday_policy_obj->getMaximumTime();
				Debug::text('AVG hours worked per day exceeds maximum regulars hours per day, setting to:'. ( ($avg_seconds_worked_per_day / 60) / 60 ), __FILE__, __LINE__, __METHOD__, 10);
			}

			if ( $avg_seconds_worked_per_day < $holiday_policy_obj->getMinimumTime() ) {
				$avg_seconds_worked_per_day = $holiday_policy_obj->getMinimumTime();
				Debug::text('AVG hours worked per day is less then minimum regulars hours per day, setting to:'. ( ($avg_seconds_worked_per_day / 60) / 60 ), __FILE__, __LINE__, __METHOD__, 10);
			}

			//Round to nearest 15mins.
			if ( (int)$holiday_policy_obj->getRoundIntervalPolicyID() != 0
					AND is_object($holiday_policy_obj->getRoundIntervalPolicyObject() ) ) {
				$avg_seconds_worked_per_day = TTDate::roundTime($avg_seconds_worked_per_day, $holiday_policy_obj->getRoundIntervalPolicyObject()->getInterval(), $holiday_policy_obj->getRoundIntervalPolicyObject()->getRoundType() );
				Debug::text('Rounding Stat Time To: '. $avg_seconds_worked_per_day, __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::text('NOT Rounding Stat Time!', __FILE__, __LINE__, __METHOD__, 10);
			}

			return $avg_seconds_worked_per_day;
		} else {
			return $holiday_policy_obj->getMinimumTime();
		}
	}

	function calculateHolidayPolicy( $date_stamp ) {
		$holiday_obj = $this->filterHoliday( $date_stamp, NULL, TRUE ); //Only consider holiday policies assigned to policy groups.
		if ( is_object($holiday_obj) ) {
			Debug::text(' Found Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

			//Check for conflicting/overridden records, so we don't double up on the time.
			//This policy could calculate 9.52hrs, but the user could override it to 9hrs, so if that happens simply skip calculating the holiday time again.
			if ( is_object( $holiday_obj->getHolidayPolicyObject() )
					AND $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() != FALSE
					AND is_object( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject() )
					AND $this->isConflictingUserDateTotal( $date_stamp, array(25, 50), (int)$holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCode() ) == FALSE ) {
				$holiday_time = 0;
				if ( $this->isEligibleForHoliday( $date_stamp, $holiday_obj->getHolidayPolicyObject() ) ) {
					Debug::text(' User is Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

					$holiday_time = $this->getHolidayTime( $date_stamp, $holiday_obj->getHolidayPolicyObject() );
					Debug::text(' User average time for Holiday: '. TTDate::getHours($holiday_time), __FILE__, __LINE__, __METHOD__, 10);
				} else {
					Debug::text(' User is not eligible for holiday (adding record with 0 time)...', __FILE__, __LINE__, __METHOD__, 10);
				}

				//Need to still record if holiday_time=0 as the user could be scheduled for 8hrs of Stat Holiday
				//but they aren't eligible to receive any holiday time, if we don't create UDT record with total_time=0
				//then the scheduled time of 8hrs will be used instead, which is incorrect.
				//This won't actually get saved, its just used to cause calculateScheduleTime() to ignore this day instead.
				if ( $holiday_time >= 0 ) {
					Debug::text(' Adding Holiday hours: '. TTDate::getHours($holiday_time) .'('.$holiday_time.')', __FILE__, __LINE__, __METHOD__, 10);
					if ( !isset( $this->user_date_total[$this->user_date_total_insert_id] ) ) {
						$udtf = TTnew( 'UserDateTotalFactory' );
						$udtf->setUser( $this->getUserObject()->getId() );
						$udtf->setDateStamp( $date_stamp );
						$udtf->setObjectType( 50 ); //Absence
						$udtf->setSourceObject( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyID() );
						$udtf->setPayCode( (int)$holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCode() );

						$udtf->setBranch( (int)$this->getUserObject()->getDefaultBranch() );
						$udtf->setDepartment( (int)$this->getUserObject()->getDefaultDepartment() );
						if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
							$udtf->setJob( (int)$this->getUserObject()->getDefaultJob() );
							$udtf->setJobItem( (int)$this->getUserObject()->getDefaultJobItem() );
						}

						$udtf->setTotalTime( $holiday_time );

						$udtf->setBaseHourlyRate( $this->getBaseHourlyRate( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp ) );
						$udtf->setHourlyRate( $this->getHourlyRate( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $udtf->getBaseHourlyRate() ) );
						$udtf->setHourlyRateWithBurden( $this->getHourlyRateWithBurden( $holiday_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayFormulaPolicy(), $udtf->getPayCode(), $date_stamp, $udtf->getHourlyRate() ) );

						$udtf->setEnableCalcSystemTotalTime(FALSE);
						$udtf->preSave(); //Call this so TotalTimeAmount is calculated immediately, as we don't save these records until later.

						if ( $this->isOverriddenUserDateTotalObject( $udtf ) == FALSE ) {
							//Don't save the record, just add it to the existing array, so it can be included in other calculations.
							//We will save these records at the end.
							$this->user_date_total[$this->user_date_total_insert_id] = $udtf;
							$this->user_date_total_insert_id--;
						}
					} else {
						Debug::text('ERROR: Duplicate starting ID for some reason! '. $this->user_date_total_insert_id, __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('No holiday time to utilize...', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('Overridden holiday time, skipping policy calculation...', __FILE__, __LINE__, __METHOD__, 10);
			}
			return TRUE;
		}

		Debug::text('No holiday policies to calculate...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterHoliday( $date_stamp, $holiday_policy_obj = NULL, $assigned_to_policy_group = NULL ) {
		$hlf = $this->holiday;
		if ( is_array( $hlf ) AND count( $hlf ) > 0 ) {
			foreach( $hlf as $h_obj ) {
				if ( TTDate::getMiddleDayEpoch( $h_obj->getDateStamp() ) == TTDate::getMiddleDayEpoch( $date_stamp ) ) {
					if (
							(
								$assigned_to_policy_group == NULL
								OR ( $assigned_to_policy_group == TRUE AND isset( $this->policy_group_holiday_policy_ids[$h_obj->getHolidayPolicyID()] ) )
								OR ( $assigned_to_policy_group == FALSE AND !isset( $this->policy_group_holiday_policy_ids[$h_obj->getHolidayPolicyID()] ) )
							)
							AND
							(
								$holiday_policy_obj == NULL OR ( is_object( $holiday_policy_obj ) AND $h_obj->getHolidayPolicyID() == $holiday_policy_obj->getId() )
							)
						) {
						$retarr = $h_obj; //Can only be one holiday per day.
					}
				} else {
					Debug::text('Holiday date does not match date parameter. Holiday: '.  TTDate::getDate('DATE+TIME', TTDate::getMiddleDayEpoch( $h_obj->getDateStamp() ) ) .' DateStamp: '.  TTDate::getDate('DATE+TIME', TTDate::getMiddleDayEpoch( $date_stamp ) ), __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found holidays that apply on date: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No holidays apply on date: '. TTDate::getDate('DATE+TIME', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getHolidayData( $start_date, $end_date ) {
		if ( count($this->holiday_policy) == 0 ) {
			Debug::text('No holiday policies, not checking for holidays...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		Debug::text('Holiday Initial: Search Start date: '. TTDate::getDate('DATE', $start_date ) .' End date: '. TTDate::getDate('DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10);

		//Keep in mind that when recalculating days, we typically search for holidays in the *future*
		//So the holiday_before_days settings defines the end date, and holiday_after_days defines the start date.
		$tmp_end_date = $end_date;
		if ( $this->holiday_before_days > 0 ) {
			$tmp_end_date = TTDate::getBeginWeekEpoch( ( $end_date + ( $this->holiday_before_days * 86400) ) );
		}

		//Don't look past the current real-time date, as we don't want to be recalculating holidays way into the future that haven't occurred yet.
		//For example Sept 1st Holiday (Labor Day) could cause holidays to be recalculated all the way to January 1st in the case of Alberta and 5 of 9 week day calculation.
		if ( $tmp_end_date > time() ) {
			Debug::text('Limiting Holiday search to current date...', __FILE__, __LINE__, __METHOD__, 10);
			$tmp_end_date = time();
		}

		$tmp_start_date = $start_date;
		if ( $this->holiday_after_days > 0 ) {
			$tmp_start_date = TTDate::getEndWeekEpoch( ( TTDate::getEndDayEpoch( $start_date ) - ( $this->holiday_before_days * 86400 ) + 3601 ) );
		}

		if ( $tmp_start_date < $start_date ) {
			$start_date = $tmp_start_date;
		}
		if ( $tmp_end_date > $end_date ) {
			$end_date = $tmp_end_date;
		}

		Debug::text('Holiday Search: Start date: '. TTDate::getDate('DATE', $start_date ) .' End date: '. TTDate::getDate('DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10);

		//We make sure there are holiday policies at the top of this function.
		$hlf = TTnew( 'HolidayListFactory' );
		$hlf->getByHolidayPolicyIdAndStartDateAndEndDate( array_keys( $this->holiday_policy ), $start_date, $end_date );
		if ( $hlf->getRecordCount() > 0 ) {
			Debug::text('Found holiday rows: '. $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $hlf as $h_obj ) {
				$this->holiday[$h_obj->getDateStamp()] = $h_obj;

				$this->addPendingCalculationDate( $h_obj->getDateStamp() ); //Add each holiday to the pending calculation list.
			}

			return TRUE;
		}

		Debug::text('No holiday rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getHolidayPolicy() {
		//Get Holiday policies and determine how many days we need to look ahead/behind in order
		//to recalculate the holiday eligilibility/time.
		$this->holiday_before_days = 0;
		$this->holiday_after_days = 0;

		//Need to be able to get holiday policies included in just contirbuting shift policies.
		//But we also need to be able to know if the policies are assigned to policy groups or not, as only those ones are calculated for absence time.
		//We can't get holiday policies until we get all contributing shifts, and we can't get contributing shifts until we get all holiday policies...
		$hplf = TTnew( 'HolidayPolicyListFactory' );
		//$hplf->getByPolicyGroupUserId( $this->getUserObject()->getId() );
		$hplf->getByPolicyGroupCompanyIdAndUserIdOrAssignedToContributingShiftPolicy( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
		if ( $hplf->getRecordCount() > 0 ) {
			Debug::text('Found holiday policy rows: '. $hplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $hplf as $hp_obj ) {
				$this->holiday_policy[$hp_obj->getId()] = $hp_obj;

				if ( $hp_obj->getMinimumWorkedPeriodDays() > $this->holiday_before_days ) {
					$this->holiday_before_days = $hp_obj->getMinimumWorkedPeriodDays();
				}
				if ( $hp_obj->getAverageTimeDays() > $this->holiday_before_days ) {
					$this->holiday_before_days = $hp_obj->getAverageTimeDays();
				}
				if ( $hp_obj->getMinimumWorkedAfterPeriodDays() > $this->holiday_after_days ) {
					$this->holiday_after_days = $hp_obj->getMinimumWorkedAfterPeriodDays();
				}

				if ( $hp_obj->getColumn('assigned_to_policy_group') == 1 ) {
					$this->policy_group_holiday_policy_ids[$hp_obj->getID()] = TRUE;
				}
			}

			return TRUE;
		}

		Debug::text('No holiday time policy rows...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPreviousDayByUserTotalData( $user_date_total_arr, $date_stamp ) {
		$day_arr = $this->getDayArrayUserDateTotalData( $user_date_total_arr );
		sort($day_arr);

		$retval = FALSE;
		foreach( $day_arr as $day ) {
			if ( $day < $date_stamp ) {
				$retval = $day;
			}
		}

		Debug::Text('Find day prior to: '. $date_stamp .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getDayArrayUserDateTotalData( $user_date_total_arr ) {
		$days = array();
		if ( is_array($user_date_total_arr) ) {
			foreach( $user_date_total_arr as $udt_obj ) {
				if ( $udt_obj->getTotalTime() > 0 ) {
					$days[] = $udt_obj->getDateStamp();
				}
			}
		}

		$days = array_unique($days);
		//Debug::Arr($days, 'Days with time: '. count($days), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('Days with time: '. count($days), __FILE__, __LINE__, __METHOD__, 10);
		return $days;
	}

	function getSumUserDateTotalData( $user_date_total_arr ) {
		$sum = 0;
		if ( is_array($user_date_total_arr) ) {
			foreach( $user_date_total_arr as $udt_obj ) {
				$sum += $udt_obj->getTotalTime();
			}

		}
		Debug::text('Sum Total: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
		return $sum;
	}

	//Returns shift data according to the pay period schedule criteria for use in determining which day punches belong to.
	function getShiftData( $user_date_total_arr, $epoch = NULL, $filter = NULL, $maximum_shift_time = NULL, $new_shift_trigger_time = NULL ) {
		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( $maximum_shift_time === NULL ) {
			$maximum_shift_time = $this->pay_period_schedule_obj->getMaximumShiftTime();
		}

		//Debug::text('User Date ID: '. $user_date_id .' User ID: '. $user_id .' TimeStamp: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $new_shift_trigger_time === NULL ) {
			$new_shift_trigger_time = $this->pay_period_schedule_obj->getNewDayTriggerTime();
		}

		Debug::text('UDT Rows: '. count($user_date_total_arr) .' Date: '. TTDate::getDate('DATE+TIME', $epoch) .'('.$epoch.') MaximumShiftTime: '. $maximum_shift_time .' New Shift Trigger: '. $new_shift_trigger_time .' Filter: '. $filter, __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($user_date_total_arr) ) {
			$shift = 0;
			$i = 0;
			$nearest_shift_id = 0;
			$nearest_punch_difference = FALSE;
			$prev_punch_obj = FALSE;

			foreach( $user_date_total_arr as $udt_key => $udt_obj ) {
				Debug::text('  Shift: '. $shift .' UDT ID: '. $udt_obj->getID() .' Object Type: '. $udt_obj->getObjectType() .' Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

				//Can't use PunchControl object total_time because the record may not be saved yet when editing
				//an already existing punch.
				//When editing, simply pass the existing PunchControl object to this function so we can
				//use it instead of the one in the database perhaps?
				$total_time = $udt_obj->getTotalTime();

				if ( $i > 0 AND isset($shift_data[$shift]['last_out']) ) {
					Debug::text('  Checking for new shift... This UDT ID: '. $udt_obj->getID() .' Last Out Time: '. TTDate::getDate('DATE+TIME', $user_date_total_arr[$shift_data[$shift]['last_out']]->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

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
					if ( 	(
									(
										//Make sure the two timestamps aren't at the exact same time, as switching from regular to overtime shouldn't cause a new shift to trigger.
										( $udt_obj->getStartTimeStamp() - $user_date_total_arr[$shift_data[$shift]['last_out']]->getEndTimeStamp() ) > 0
										AND ( $udt_obj->getStartTimeStamp() - $user_date_total_arr[$shift_data[$shift]['last_out']]->getEndTimeStamp() ) >= $new_shift_trigger_time
										AND $udt_obj->getStartType() == 10 //Make sure only normal punches can trigger new shifts.
									)
									OR
									(
										$this->pay_period_schedule_obj->getShiftAssignedDay() == 40
										//Only split shifts on NORMAL punches.
										AND $udt_obj->getStartType() == 10
										AND $user_date_total_arr[$shift_data[$shift]['last_out']]->getEndType() == 10
										AND TTDate::doesRangeSpanMidnight( $user_date_total_arr[$shift_data[$shift]['last_out']]->getEndTimeStamp(), $udt_obj->getStartTimeStamp(), TRUE ) == TRUE
									)
							)
						) {
						Debug::text('New Shift....', __FILE__, __LINE__, __METHOD__, 10);
						$shift++;
					}
				} elseif ( $i > 0
							AND isset($prev_punch_arr['time_stamp'])
							AND abs( ( $prev_punch_arr['time_stamp'] - $udt_obj->getStartTimeStamp() ) ) > $maximum_shift_time ) {
					Debug::text('	 New shift because two punch_control records exist and punch timestamp exceed maximum shift time.', __FILE__, __LINE__, __METHOD__, 10);
					$shift++;
				}

				if ( !isset($shift_data[$shift]['total_time']) ) {
					$shift_data[$shift]['total_time'] = 0;
				}

				$punch_day_epoch = TTDate::getBeginDayEpoch( $udt_obj->getStartTimeStamp() );
				if ( !isset($shift_data[$shift]['total_time_per_day'][$punch_day_epoch]) ) {
					$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] = 0;
				}

				//Determine which shift is closest to the given epoch.
				$punch_difference_from_epoch = abs( ( $epoch - $udt_obj->getStartTimeStamp() ) );
				if ( $nearest_punch_difference === FALSE OR $punch_difference_from_epoch <= $nearest_punch_difference ) {
					Debug::text('Nearest Shift Determined to be: '. $shift .' Nearest Punch Diff: '. (int)$nearest_punch_difference .' Punch Diff: '. $punch_difference_from_epoch, __FILE__, __LINE__, __METHOD__, 10);

					//If two punches have the same timestamp, use the shift that matches the passed punch control object, which is usually the one we are currently editing...
					//This is for splitting shifts at exactly midnight.
					if ( $punch_difference_from_epoch != $nearest_punch_difference
							OR ( $punch_difference_from_epoch == $nearest_punch_difference ) ) {
						Debug::text('Setting nearest shift...', __FILE__, __LINE__, __METHOD__, 10);
						$nearest_shift_id = $shift;
						$nearest_punch_difference = $punch_difference_from_epoch;
					}
				}

				//$shift_data[$shift]['user_date_total_ids'][] = $udt_obj->getID();
				$shift_data[$shift]['user_date_total_keys'][] = $udt_key;

				if ( $udt_obj->getDateStamp() != FALSE ) {
					$shift_data[$shift]['date_stamps'][] = $udt_obj->getDateStamp();
				}

				if ( !isset($shift_data[$shift]['span_midnight']) ) {
					$shift_data[$shift]['span_midnight'] = FALSE;
				}

				if ( !isset($shift_data[$shift]['first_in']) AND $udt_obj->getStartType() == 10 ) {
					//Debug::text('First In -- Punch ID: '. $udt_obj->getID() .' Punch Control ID: '. $udt_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $udt_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
					$shift_data[$shift]['first_in'] = $udt_key;
				}

				//Since UDT rows have both IN and OUT timestamps, need to handle both first_in and last_out in the same record.
				if ( $udt_obj->getEndTimeStamp() != '' ) {
					//Debug::text('Last Out -- Punch ID: '. $udt_obj->getID() .' Punch Control ID: '. $udt_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $udt_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['last_out'] = $udt_key;

					//Debug::text('Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
					$shift_data[$shift]['total_time'] += $total_time;

					//Check to see if the previous punch was on a different day then the current punch.
					if ( TTDate::doesRangeSpanMidnight( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp() ) == TRUE ) {
						Debug::text('Punch PAIR DOES span midnight', __FILE__, __LINE__, __METHOD__, 10);
						$shift_data[$shift]['span_midnight'] = TRUE;

						$total_time_for_each_day_arr = TTDate::calculateTimeOnEachDayBetweenRange( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp() );
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

				$prev_udt_obj = $udt_obj;
				$i++;
			}
			//Debug::Arr($shift_data, 'aShift Data:', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($shift_data) ) {
				Debug::text('Filtering if necessary...', __FILE__, __LINE__, __METHOD__, 10);

				//Loop through each shift to determine the day with the most time.
				foreach( $shift_data as $tmp_shift_key => $tmp_shift_data ) {
					krsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by day first
					arsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by total time per day.
					reset($shift_data[$tmp_shift_key]['total_time_per_day']);
					$shift_data[$tmp_shift_key]['day_with_most_time'] = key($shift_data[$tmp_shift_key]['total_time_per_day']);

					//$shift_data[$tmp_shift_key]['user_date_total_ids'] = array_unique( $shift_data[$tmp_shift_key]['user_date_total_ids'] );
					$shift_data[$tmp_shift_key]['user_date_total_keys'] = array_unique( $shift_data[$tmp_shift_key]['user_date_total_keys'] );
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
					if ( isset($shift_data['first_in']) ) {
						$first_in = $shift_data['first_in'];
					} elseif ( isset($shift_data['last_out']) ) {
						$first_in = $shift_data['last_out'];
					}

					if ( isset($shift_data['last_out']) ) {
						$last_out = $shift_data['last_out'];
					} elseif ( isset($shift_data['first_in']) ) {
						$last_out = $shift_data['first_in'];
					}

					//The check below must occur so if the user attempts to add an In punch that occurs AFTER the Out punch, this function
					//still returns the shift data, so the validation checks can occur in PunchControl factory.
					if ( $user_date_total_arr[$first_in]->getStartTimeStamp() > $user_date_total_arr[$last_out]->getEndTimeStamp() ) {
						//It appears that the first in punch has occurred after the OUT punch, so swap first_in and last_out, so we don't return FALSE in this case.
						//list( $user_date_total_arr[$first_in]->getStartTimeStamp(), $user_date_total_arr[$last_out]->getEndTimeStamp() ) = array( $user_date_total_arr[$last_out]->getEndTimeStamp(), $user_date_total_arr[$first_in]->getStartTimeStamp() );
						list( $first_in, $last_out ) = array( $last_out, $first_in );
					}


					if ( TTDate::isTimeOverLap($epoch, $epoch, ($user_date_total_arr[$first_in]->getStartTimeStamp() - $new_shift_trigger_time), ($user_date_total_arr[$last_out]->getEndTimeStamp() + $new_shift_trigger_time) ) == FALSE ) {
						Debug::Text('Nearest shift is outside the new shift trigger time... Epoch: '. $epoch .' First In: '. $first_in .' Last Out: '. $last_out .' New Shift Trigger: '. $new_shift_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

						return FALSE;
					}
					unset($first_in, $last_out);
				}

				Debug::Arr($shift_data, 'bShift Data:', __FILE__, __LINE__, __METHOD__, 10);
				return $shift_data;
			}
		}
		
		return FALSE;
	}

	function sortUserDateTotalDataByDateAndObjectTypeAndStartTimeStampAndID( $a, $b ) {
		//Sort order obtained from: getUserDateTotalData(), if changes are needed, change there too.
		//array( 'a.date_stamp' => 'asc', 'a.object_type_id' => 'asc', 'a.start_time_stamp' => 'asc', 'a.id' => 'asc' )
		if ( $a->getDateStamp() == $b->getDateStamp() ) {
			if ( $a->getObjectType() == $b->getObjectType() ) {
				if ( $a->getStartTimeStamp() == $b->getStartTimeStamp() ) {
					return ( $a->getID() < $b->getID() ) ? (-1) : 1;
				} else {
					return ( $a->getStartTimeStamp() < $b->getStartTimeStamp() ) ? (-1) : 1;
				}
			} else {
				return ( $a->getObjectType() < $b->getObjectType() ) ? (-1) : 1;
			}
		} else {
			return ( $a->getDateStamp() < $b->getDateStamp() ) ? (-1) : 1;
		}
	}
	function sortUserDateTotalData( $udtlf, $sort_function_name = 'sortUserDateTotalDataByDateAndObjectTypeAndStartTimeStampAndID' ) {
		if ( is_array($udtlf) AND $sort_function_name != '' ) {
			uasort( $udtlf, array( $this, $sort_function_name ) );
		}

		return $udtlf;
	}
	
	function filterUserDateTotalDataByContributingShiftPolicy( $start_date, $end_date, $contributing_shift_policy_obj, $object_type_ids = NULL, $additional_pay_code_ids = array() ) {
		if ( !is_object( $contributing_shift_policy_obj ) ) {
			Debug::text('ERROR: Contributing Shift Policy is not an object!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$udtlf = $this->user_date_total;
		if ( is_array($udtlf) AND count($udtlf) > 0 ) {
			Debug::text('Filtering user date total rows: '. count($udtlf), __FILE__, __LINE__, __METHOD__, 10);

			//Optimization, to avoid doing it in a loop.
			if ( !is_array( $start_date) ) {
				$start_date = TTDate::getMiddleDayEpoch( $start_date );
			}
			if ( $end_date != '' ) {
				$end_date = TTDate::getMiddleDayEpoch( $end_date );
			}

			$pay_code_ids = NULL;
			if ( isset($this->contributing_pay_codes_by_policy_id[$contributing_shift_policy_obj->getContributingPayCodePolicy()]) ) {
				$pay_code_ids = (array)$this->contributing_pay_codes_by_policy_id[$contributing_shift_policy_obj->getContributingPayCodePolicy()];
			}

			if ( is_array($additional_pay_code_ids) AND count($additional_pay_code_ids) > 0 ) {
				//Debug::Arr($additional_pay_code_ids, 'Adding additional Pay Code Ids: ', __FILE__, __LINE__, __METHOD__, 10);
				$pay_code_ids = array_merge( $pay_code_ids, (array)$additional_pay_code_ids );
			}

			//If object_type_ids includes worked time, we need to automatically add pay_code_id=0 so "AND" can be used on the matching below.
			//if ( $object_type_ids == NULL OR ( is_array( $object_type_ids ) AND in_array( 10, $object_type_ids ) ) ) { //Worked time.
			if ( is_array( $object_type_ids ) AND in_array( 10, $object_type_ids ) ) { //Worked time.
				$pay_code_ids[] = 0;
			}

			//Debug::Arr($object_type_ids, 'Object Type IDs: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($pay_code_ids, 'Pay Code IDs: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $udtlf as $udt_key => $udt_obj ) {
				if ( ( $object_type_ids == NULL OR in_array( $udt_obj->getObjectType(), $object_type_ids ) ) ) {
					if ( ( $pay_code_ids == NULL OR in_array( $udt_obj->getPayCode(), $pay_code_ids ) ) ) {
						if (
								(
									( !is_array($start_date) AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) >= $start_date AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) <= $end_date )
									OR
									( is_array($start_date) AND in_array( TTDate::getBeginDayEpoch( $udt_obj->getDateStamp() ), $start_date ) )
								)
							) {

							//FIXME: For some ./run.sh --filter MealBreakPolicyTest::testAutoAddMultipleBreakPolicyE
							//Creates UDT rows with no start timestamp but with a end time stamp, which causes problems.
							//if ( $udt_obj->getStartTimeStamp() == FALSE AND $udt_obj->getEndTimeStamp() != FALSE ) {
							//	Debug::Text('ID: '. $udt_obj->getID() .' Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
							//}
							
							//Handle contributing shift filters here.
							if (	getTTProductEdition() == TT_PRODUCT_COMMUNITY
									OR
									(
										$contributing_shift_policy_obj->isActive( $udt_obj->getDateStamp(), $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $this )
										AND $contributing_shift_policy_obj->isActiveFilterTime( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $this )
										AND $contributing_shift_policy_obj->isActiveDifferential( $udt_obj, $this->getUserObject() )
									)
								) {

								//Debug::text('Found: UDT ID: '. $udt_obj->getID() .' Date Stamp: '. TTDate::getDate('DATE', $udt_obj->getDateStamp() ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .'($'. (float)$udt_obj->getTotalTimeAmount().' Base Rate: '.(float)$udt_obj->getBaseHourlyRate().') Object Type ID: '. $udt_obj->getObjectType() .' ID: '. $udt_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
								//Handle partial shifts here.
								if ( $contributing_shift_policy_obj->getIncludePartialShift() == TRUE ) {
									$retarr[$udt_key] = $contributing_shift_policy_obj->getPartialUserDateTotalObject( $udt_obj, $this );
								} else {
									$retarr[$udt_key] = $udt_obj;
								}								
							} else {
								Debug::text('Skipping, due to filter date,dow,time,differential... UDT ID: '. $udt_obj->getID() .' Date Stamp: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .' Filter: Start Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $start_date ) ) .' End Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $end_date ) ), __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						//else {
							//Debug::text('Skipping, due to date. UDT Date Stamp: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .' Object Type: '. $udt_obj->getObjectType() .' Filter: Start Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $start_date ) ) .' End Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $end_date ) ), __FILE__, __LINE__, __METHOD__, 10);
						//}
					}
					//else {
						//Debug::Text('Skipping, due to pay_code_id. UDT Date Stamp: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .' Object Type: '. $udt_obj->getObjectType() .' Filter: Start Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $start_date ) ) .' End Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $end_date ) ), __FILE__, __LINE__, __METHOD__, 10);
					//}
				}
				//else {
					//Debug::Text('Skipping, due to object_type_id. UDT Date Stamp: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Total Time: '. $udt_obj->getTotalTime() .' Object Type: '. $udt_obj->getObjectType() .' Filter: Start Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $start_date ) ) .' End Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $end_date ) ), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}

			if ( isset($retarr) ) {
				Debug::text('Found UserDateTotal rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);

				return $retarr;
			} else {
				//Debug::Arr($pay_code_ids, 'Pay Code IDs: ', __FILE__, __LINE__, __METHOD__, 10);
				Debug::text('No UserDateTotal rows matched filter... Start Date: '. TTDate::getDate('DATE', $start_date) .' End Date: '. TTDate::getDate('DATE', $end_date), __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('No UserDateTotal rows available for matching...', __FILE__, __LINE__, __METHOD__, 10);
		}
		
		return FALSE;
	}

	function filterUserDateTotalDataByPayCodeIDs( $start_date, $end_date, $pay_code_ids = NULL ) {
		$udtlf = $this->user_date_total;
		if ( is_array($udtlf) AND count($udtlf) > 0 ) {
			foreach( $udtlf as $udt_key => $udt_obj ) {
				if ( TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $start_date )
						AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) <= TTDate::getMiddleDayEpoch( $end_date )
						AND ( $pay_code_ids == NULL OR in_array( $udt_obj->getPayCode(), (array)$pay_code_ids ) ) ) {
					$retarr[$udt_key] = $udt_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found user_date_total rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::Arr($pay_code_ids, 'No user_date_total rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterUserDateTotalDataByObjectTypeIDs( $start_date, $end_date, $object_type_ids = NULL ) {
		$udtlf = $this->user_date_total;
		if ( is_array($udtlf) AND count($udtlf) > 0 ) {
			foreach( $udtlf as $udt_key => $udt_obj ) {
				if ( TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $start_date )
						AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) <= TTDate::getMiddleDayEpoch( $end_date )
						AND ( $object_type_ids == NULL OR in_array( $udt_obj->getObjectType(), $object_type_ids ) ) ) {
					$retarr[$udt_key] = $udt_obj;
				}
				//else {
				//	Debug::text('Skipping, due to filter date,object_type_id... UDT Date Stamp: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) ) .' Pay Code ID: '. $udt_obj->getPayCode() .' Object Type: '. $udt_obj->getObjectType() .' Total Time: '. $udt_obj->getTotalTime() .' Filter: Start Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $start_date ) ) .' End Date: '. TTDate::getDate('DATE', TTDate::getMiddleDayEpoch( $end_date ) ), __FILE__, __LINE__, __METHOD__, 10);
				//}
			}

			if ( isset($retarr) ) {
				Debug::text('Found user_date_total rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No user_date_total rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function filterUserDateTotalDataByPunchTypeIDs( $start_date, $end_date, $punch_type_ids = NULL ) {
		$udtlf = $this->user_date_total;
		if ( is_array($udtlf) AND count($udtlf) > 0 ) {
			foreach( $udtlf as $udt_key => $udt_obj ) {
				//Debug::text('ID: '. $udt_obj->getID() .' Punch Control ID: '. $udt_obj->getPunchControlID() .' StartType: '. $udt_obj->getStartType() .' End Type: '. $udt_obj->getEndType(), __FILE__, __LINE__, __METHOD__, 10);
				if ( TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $start_date )
						AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) <= TTDate::getMiddleDayEpoch( $end_date )
						AND (
								( $punch_type_ids == NULL OR in_array( $udt_obj->getStartType(), $punch_type_ids ) )
								OR
								( $punch_type_ids == NULL OR in_array( $udt_obj->getEndType(), $punch_type_ids ) )
							)
					) {
					$retarr[$udt_key] = $udt_obj;
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Found user_date_total rows matched filter: '. count($retarr), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No user_date_total rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getShiftStartAndEndUserDateTotal( $start_date, $end_date ) {
		$udtlf = $this->user_date_total;
		if ( is_array($udtlf) AND count($udtlf) > 0 ) {
			$first_in = FALSE;
			$last_out = FALSE;
			foreach( $udtlf as $udt_key => $udt_obj ) {
				if ( TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $start_date )
						AND TTDate::getMiddleDayEpoch( $udt_obj->getDateStamp() ) <= TTDate::getMiddleDayEpoch( $end_date )
						AND $udt_obj->getObjectType() == 10 //Worked time.
						AND ( $udt_obj->getStartType() == 10 OR $udt_obj->getEndType() == 10 )
					) {
					
					//Debug::text('UDT ID: '. $udt_obj->getID() .' Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);
					if ( $udt_obj->getStartType() == 10 AND ( $first_in == FALSE OR $udt_obj->getStartTimeStamp() < $first_in ) ) {
						$first_in = $udt_obj->getStartTimeStamp();
						$retarr['start'] = $udt_obj;
					}
					if ( $udt_obj->getEndType() == 10 AND ( $last_out == FALSE OR $udt_obj->getEndTimeStamp() > $first_in ) ) {
						$last_out = $udt_obj->getEndTimeStamp();
						$retarr['end'] = $udt_obj;
					}
				}
			}

			if ( isset($retarr) ) {
				Debug::text('Shift Start: '. TTDate::getDate('DATE+TIME', $first_in ) .' End: '. TTDate::getDate('DATE+TIME', $last_out ), __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('No user_date_total rows match filter...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Grabs all user_date total data from DB for each date specified. 
	function getUserDateTotalData( $start_date = NULL, $end_date = NULL ) {
		$udtlf = TTNew('UserDateTotalListFactory');
		$filter_data = array(
								'user_id' => $this->getUserObject()->getId(),
								'start_date' => $start_date,
								'end_date' => $end_date,
								//'date' => $date_stamps,

								//This could be called several times, but exclude already obtained rows each time.
								'exclude_id' => array_keys( (array)$this->user_date_total ),
							);

		//If SORT order is changed, also change it in: sortUserDateTotalDataByDateAndObjectTypeAndStartTimeStampAndID()
		$udtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, NULL, NULL, NULL, array( 'a.date_stamp' => 'asc', 'a.object_type_id' => 'asc', 'a.start_time_stamp' => 'asc', 'a.id' => 'asc' ) );
		if ( $udtlf->getRecordCount() > 0 ) {
			Debug::text('Found UserDateTotal rows: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $udtlf as $udt_obj ) {
				$this->user_date_total[$udt_obj->getId()] = $udt_obj;
			}

			return TRUE;
		}

		Debug::text('No UserDateTotal rows... Start Date: '. TTDate::getDate('DATE', $start_date) .' End Date: '. TTDate::getDate('DATE', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	
	//Return dates that we have not already obtained data for.
	function getDateRangeFromDateArray( $date_arr ) {
		sort($date_arr);

		$retarr['start_date'] = reset($date_arr);
		$retarr['end_date'] = end($date_arr);

		Debug::text('Found Date Range: Start: '. TTDate::getDATE('DATE', $retarr['start_date'] ) .' End: '. TTDate::getDATE('DATE', $retarr['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}
	function getDatesToObtainDataFor( $date_stamp ) {
		$retarr = array();

		//Always get data for the entire week, since we need the date earlier than $date_stamp for calculations (ie: Weekly Overtime) later in the week
		//and when changing $date_stamp we have to recalculate all dates proceeding it until the end of the week anyways.
		$start_date = TTDate::getBeginWeekEpoch( $date_stamp, $this->start_week_day_id );

		if ( $this->getFlag('future_dates') == TRUE OR $this->getFlag('exception_future') == TRUE ) {
			$end_date = TTDate::getEndWeekEpoch( $date_stamp, $this->start_week_day_id );
		} else {
			$end_date = $this->getLastPendingDate();
			if ( $end_date == '' ) {
				//$end_date = $start_date;
				//If we use $start_date, we won't get data for days between the beginning of the week and $date_stamp.
				//Specifically if $date_stamp = 31-Oct-14 and start_date = 26-Oct-14, we need the data for 26-Oct to 31-Oct.
				$end_date = $date_stamp;
			}
		}

		Debug::text('Start: '. TTDate::getDATE('DATE+TIME', $start_date ) .' End: '. TTDate::getDATE('DATE+TIME', $end_date ) .'('. $end_date .')', __FILE__, __LINE__, __METHOD__, 10);

		$date_arr = TTDate::getDateArray( $start_date, $end_date );
		foreach( $date_arr as $tmp_date_stamp ) {
			if ( !isset($this->dates['data'][$tmp_date_stamp]) ) {
				$retarr[] = $tmp_date_stamp;
				$this->dates['data'][$tmp_date_stamp] = TRUE;
				Debug::text('Found date without data: '. TTDate::getDATE('DATE+TIME', $tmp_date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
			}
			//else {
				//Debug::text('Already have data for date: '. TTDate::getDATE('DATE+TIME', $tmp_date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
			//}
		}
		
		return $retarr;
	}
	
	//Gathers all required data to perform the calculations.
	function getRequiredData( $date_stamp ) {
		$date_arr = $this->getDatesToObtainDataFor( $date_stamp );
		if ( count($date_arr) > 0 ) {
			$date_range = $this->getDateRangeFromDateArray( $date_arr );
			Debug::text('Date Range: Start: '. TTDate::getDate('DATE+TIME', $date_range['start_date'] ) .' End: '. TTDate::getDate('DATE+TIME', $date_range['end_date'] ), __FILE__, __LINE__, __METHOD__, 10);

			$this->getSchedulePolicy(); //Must come before getScheduleData() so we can get the maximum start/stop window for getScheduleData().
			$this->getScheduleData( $date_range['start_date'], $date_range['end_date'] );

			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE ) {
				$this->getUserWageData( $date_range['start_date'], $date_range['end_date'] );
				$this->getCurrencyRateData( $date_range['start_date'], $date_range['end_date'] );
			}

			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE OR $this->getFlag('exception') == TRUE ) {
				$this->getUserDateTotalData( $date_range['start_date'], $date_range['end_date'] );
			}

			if ( $this->getFlag('undertime_absence') == TRUE ) {
				$this->getUnderTimeAbsenceTimePolicy();
			}

			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('exception') == TRUE ) {
				$this->getMealTimePolicy();
			}
			if ( $this->getFlag('break') == TRUE OR $this->getFlag('exception') == TRUE ) {
				$this->getBreakTimePolicy();
			}
			if ( $this->getFlag('regular') == TRUE ) {
				$this->getRegularTimePolicy();
			}
			if ( $this->getFlag('overtime') == TRUE ) {
				$this->getOverTimePolicy();
			}
			if ( $this->getFlag('premium') == TRUE ) {
				$this->getPremiumTimePolicy();
			}

			if ( $this->getFlag('accrual') == TRUE ) { //Must go before getContributingShiftPolicy() below.
				$this->getAccrualPolicy();
			}

			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE ) {
				$this->getPayCode(); //Needs to come before getContributingShiftPolicy, but after Reg/OT/Prem policies are obtained.
				$this->getPayFormulaPolicy();

				$this->getHolidayPolicy(); //Must come before getContributingShiftPolicy() as it adds additional contributing shift policies to the list.
				$this->getHolidayData( $date_range['start_date'], $date_range['end_date'] ); //This uses date_stamp as we need to find holidays in the past/future. Must come after getHolidayPolicy()

				$this->getContributingShiftPolicy(); //This adds additional HolidayPolicies to the list... But it can't come before getHolidayPolicy()
				$this->getContributingPayCodePolicy();
			}

			if ( $this->getFlag('exception') == TRUE ) {
				$this->getExceptionPolicy();
				$this->getPunchData( $date_range['start_date'], $date_range['end_date'] );
				$this->getExceptionData( $date_range['start_date'], $date_range['end_date'] );
			}
		} else {
			Debug::text('No dates to get required data for...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('Done collecting required data...', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function addCalculatedDate( $date_stamp ) {
		$date_stamp = TTDate::getBeginDayEpoch( $date_stamp );
		//Remove date from pending calculation first, then add it to the calculated date.
		if ( isset($this->dates['pending_calculation'][$date_stamp]) ) {
			unset($this->dates['pending_calculation'][$date_stamp]);
		}

		$this->dates['calculated'][$date_stamp] = TRUE;

		return TRUE;
	}
	function addPendingCalculationDate( $start_date, $end_date = NULL ) {
		if ( $start_date == '' AND $end_date == '' ) {
			return FALSE;
		}
		
		if ( $end_date == '' ) {
			if ( is_array($start_date) ) {
				$pending_dates = $start_date;
			} else {
				$pending_dates = array($start_date);
			}
		} else {
			$pending_dates = TTDate::getDateArray( $start_date, $end_date );
		}
		//Debug::Arr($pending_dates, 'Add Pending Dates: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text('Add Pending Dates: '. count($pending_dates), __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($pending_dates ) ) {
			foreach( $pending_dates as $tmp_date ) {
				$tmp_date = TTDate::getBeginDayEpoch($tmp_date);

				//Make sure we don't calculate dates twice in the same run.
				//  As when handling averaging or other holidays its possible they may get re-added.
				if ( !isset($this->dates['calculated'][$tmp_date]) ) {
					$this->dates['pending_calculation'][$tmp_date] = TRUE;
				}
			}
		}

		//Always sort pending dates so they are in chronological order.
		ksort($this->dates['pending_calculation']);

		return TRUE;
	}
	function getNextPendingDate() {
		//Debug::Arr($this->dates['pending_calculation'], 'Dates pending calculation still: ', __FILE__, __LINE__, __METHOD__, 10);

		reset($this->dates['pending_calculation']);
		$retval = key( $this->dates['pending_calculation'] );
		if ( $retval != '' ) {
			Debug::Text('Next Pending Date: '. TTDate::getDate('DATE+TIME', $retval), __FILE__, __LINE__, __METHOD__, 10);
			unset($this->dates['pending_calculation'][$retval]);
			return $retval;
		}

		return FALSE;
	}

	function getFirstPendingDate() {
		reset($this->dates['pending_calculation']);
		$retval = key( $this->dates['pending_calculation'] );
		Debug::Text('First Pending Date: '. TTDate::getDate('DATE+TIME', $retval), __FILE__, __LINE__, __METHOD__, 10);
		if ( $retval != '' ) {
			return $retval;
		}

		return FALSE;
	}
	function getLastPendingDate() {
		end($this->dates['pending_calculation']);
		$retval = key( $this->dates['pending_calculation'] );
		Debug::Text('Last Pending Date: '. TTDate::getDate('DATE+TIME', $retval), __FILE__, __LINE__, __METHOD__, 10);
		if ( $retval != '' ) {
			return $retval;
		}

		return FALSE;
	}
	
	private function _calculate( $date_stamp ) {
		$pay_period_id = PayPeriodListFactory::findPayPeriod( $this->getUserObject()->getId(), $date_stamp );
		if ( $pay_period_id > 0 ) {
			$this->pay_period_obj = $this->getPayPeriodObject( $pay_period_id );
			$this->pay_period_schedule_obj = $this->pay_period_obj->getPayPeriodScheduleObject();
			$this->start_week_day_id = $this->pay_period_schedule_obj->getStartWeekDay();
		} else {
			$this->pay_period_obj = NULL;
			$ppslf = TTNew('PayPeriodScheduleListFactory');
			$ppslf->getByUserId( $this->getUserObject()->getId() );
			if ( $ppslf->getRecordCount() == 1 ) {
				$this->pay_period_schedule_obj = $ppslf->getCurrent();
			} else {
				Debug::text('Pay Period Object not found for user: '. $this->getUserObject()->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$this->pay_period_schedule_obj = TTnew('PayPeriodScheduleFactory');
			}
			unset($ppslf);
			$this->start_week_day_id = $this->pay_period_schedule_obj->getStartWeekDay();
		}

		if ( is_object( $this->pay_period_schedule_obj )
				AND ( $this->pay_period_obj == NULL
						OR ( is_object( $this->pay_period_obj ) AND $this->pay_period_obj->getStatus() != 20 ) ) ) { //Check if pay period is closed.

			//Only deleteSystemTotalTime() if we can properly calculate it and add it back, which means other policies need to be calculated too.
			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('undertime_absence') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE OR $this->getFlag('schedule_absence') == TRUE ) {
				$this->deleteSystemTotalTime( $date_stamp );
			}

			//Add date to the list of calculated dates. Do this before other policies (ie: OT) can add the same date back to the list.
			$this->addCalculatedDate( $date_stamp );
			$this->getRequiredData( $date_stamp );

			//Add all days remaining in the week to be recalculated.
			if ( $this->getFlag('future_dates') == TRUE OR $this->getFlag('exception_future') == TRUE ) {
				$this->addPendingCalculationDate( TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $date_stamp ) + 86400 ) ), TTDate::getEndWeekEpoch( $date_stamp, $this->start_week_day_id ) );
			}

			if ( $this->getFlag('meal') == TRUE OR $this->getFlag('undertime_absence') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE OR $this->getFlag('schedule_absence') == TRUE ) {

				//Meal/Break calculation must go before any other policy, even before we get worked time, as this affects worked time.
				//We also need to calculate just the daily worked time for them.
				//This also needs to go before we get $maximum_daily_total_time, otherwise it will be off by the auto-deducted/auto-added time.
				//**Keep in mind regular time policies can include/exclude meal/break time depending on they want the time to be calculated.
				if ( $this->getFlag('meal') == TRUE ) {
					$this->calculateMealTimePolicy( $date_stamp );
				}

				if ( $this->getFlag('break') == TRUE ) {
					$this->calculateBreakTimePolicy( $date_stamp );
				}

				//Calculate holiday time before absences/regular time and maximum_daily_total as it creates absence time.
				if ( $this->getFlag('holiday') == TRUE ) {
					$this->calculateHolidayPolicy( $date_stamp );
				}

				//Calculate absence schedules after holidays, so they can be exclusive to one another.
				if ( $this->getFlag('schedule_absence') == TRUE ) {
					$this->calculateScheduleAbsence( $date_stamp );
				}

				//This must be before maximum_daily_total_time is calculated, in cases where they don't work at all on a day, undertime will still work.
				if ( $this->getFlag('undertime_absence') == TRUE ) {
					$this->calculateUnderTimeAbsencePolicy( $date_stamp );
				}

				//Get worked time+meal/break+absence as the total amount of time that can be split between Regular and Overtime as the maximum daily total time.
				//This has to include Absence Taken (50) rather than Absence (25) as it hasn't been calculated yet.
				//UndertimeAbsence creates object_type_id=25 records, so we do need to include those here.
				//$maximum_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 25, 50, 100, 110 ) ) );

				//Since we support override records now, and prior to this we delete all system time, we should include regular/overtime in this total.
				$maximum_daily_total_time = $this->getSumUserDateTotalData( $this->filterUserDateTotalDataByObjectTypeIDs( $date_stamp, $date_stamp, array( 10, 20, 25, 30, 50, 100, 110 ) ) );
				Debug::text('Maximum Daily Total Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);

				if ( $maximum_daily_total_time > 0 ) {
					//Calculate absence time before regular time, as Regular Time is exclusive to Absence time.
					if ( $this->getFlag('absence') == TRUE ) {
						$this->calculateAbsenceTimePolicy( $date_stamp );
					}

					if ( $this->getFlag('regular') == TRUE ) {
						$this->calculateRegularTimePolicy( $date_stamp, $maximum_daily_total_time );
						$this->calculateRegularTimeExclusivity();
					}

					if ( $this->getFlag('overtime') == TRUE ) {
						//  Once the first OT policy starts everything is OT after that in the remaining period (daily/weekly) until it resets again.
						$this->calculateOverTimePolicy( $date_stamp, $this->processTriggerTimeArray( $date_stamp, $this->getOverTimeTriggerArray( $date_stamp ) ), $maximum_daily_total_time );
						//$this->calculateOverTimeExclusivity();
					}

					if ( $this->getFlag('premium') == TRUE ) {
						//Needs to go before overtime, so average wages can be obtained that include premiums.
						//However then premiums can't include overtime?
						$this->calculatePremiumTimePolicy( $date_stamp, $maximum_daily_total_time );
					}

					if ( $this->getFlag('meal') == TRUE OR $this->getFlag('undertime_absence') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE OR $this->getFlag('schedule_absence') == TRUE ) {
						Debug::text('bMaximum Daily Total Time: '. $maximum_daily_total_time, __FILE__, __LINE__, __METHOD__, 10);
						$this->calculateSystemTotalTime( $date_stamp, $maximum_daily_total_time );
					}

					if ( $this->getFlag('overtime') == TRUE ) {
						//Make sure we do this after all policies have been calculated.
						$this->calculateOverTimeHourlyRates( $this->user_date_total );
					}
				} else {
					Debug::text('Maximum Daily Total Time is 0, skipping Regular/OT/Premium policies...', __FILE__, __LINE__, __METHOD__, 10);

					//Need to have system total time row even if it is 0.
					if ( $this->getFlag('meal') == TRUE OR $this->getFlag('undertime_absence') == TRUE OR $this->getFlag('break') == TRUE OR $this->getFlag('regular') == TRUE OR $this->getFlag('overtime') == TRUE OR $this->getFlag('premium') == TRUE OR $this->getFlag('accrual') == TRUE OR $this->getFlag('holiday') == TRUE OR $this->getFlag('schedule_absence') == TRUE ) {
						$this->calculateSystemTotalTime( $date_stamp, $maximum_daily_total_time );
					}
				}
			} else {
				Debug::text('Not calculating any time related policies due to flags...', __FILE__, __LINE__, __METHOD__, 10);
			}
			
			if ( $this->getFlag('exception') == TRUE ) {
				$this->calculateExceptionPolicy( $date_stamp );
			}
		} else {
			Debug::text('No Pay Period Object or Pay Period is Closed!', __FILE__, __LINE__, __METHOD__, 10);
		}

		//Calculate pending dates even if pay period doesn't exist or maximum daily time is 0 on some days.
		$next_pending_date_stamp = $this->getNextPendingDate();
		Debug::Text( 'Next Pending Date: '. TTDate::getDate('DATE+TIME', $next_pending_date_stamp), __FILE__, __LINE__, __METHOD__, 10);
		return $next_pending_date_stamp;
	}

	//Allow calculating up to one week at a time, as we always recalculate the remaining week anyways.
	//Allow no date_stamp to be passed so we just start from the first pending date instead.
	function calculate( $date_stamp = FALSE ) {
		//Debug::Arr( Debug::backTrace(), 'Calculate: ', __FILE__, __LINE__, __METHOD__, 10);
		
		if ( $date_stamp == '' ) {
			$date_stamp = $this->getNextPendingDate();
		}

		if ( is_array($date_stamp) OR $date_stamp == '' ) {
			Debug::Arr($date_stamp, 'Invalid DateStamp: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//If the currently logged in administrator is timezone GMT, and he edits an absence for a user in timezone PST
		//the date_stamp in epoch format is set in GMT timezone, then here the timezone switches to PST
		//and changes the date that is calculated.

		$this->setTimeZone(); //Set timezone to users timezone so dates/times are all calculated in the users timezone.

		//Start transaction to keep data consistent during the entire calculation process.
		//This may cause deadlocks if the date ranges are too long though.
		$this->getUserObject()->StartTransaction();

		$i = 0;
		do {
			//Use a while loop to avoid nested function call limitations.
			Debug::text('I: '. $i .' Calculating DateStamp: '. TTDate::getDate('DATE+TIME', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
			$date_stamp = $this->_calculate( $date_stamp );
			$i++;
		} while ( $date_stamp !== FALSE AND $i <= 366 ); //Don't exceed one year.

		//Make sure reverTimeZone() and Commit transaction are in the Save() function below, so we don't revert the timezone before we save the records.

		return TRUE;
	}

	//Keep saving all data in a separate function so we can do in-memory calculations if necessary.
	function Save() {
		Debug::text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
		//return $this->insertCompactUserDateTotal( $this->compactOutstandingUserDateTotalObjects() );
		$this->removeRedundantUserDateTotalObjects();

		$this->insertUserDateTotal( $this->user_date_total );

		if ( $this->getFlag('accrual') == TRUE ) {
			//This needs to reference inserted UDT rows, so it must go last.
			$this->calculateAccrualPolicy();
		}

		$this->getUserObject()->CommitTransaction();

		$this->revertTimeZone(); //Revert timezone back to the original.

		return TRUE;
	}
}
?>