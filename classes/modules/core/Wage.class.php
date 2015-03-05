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
class Wage {
	var $user_id = NULL;
	var $pay_period_id = NULL;
	var $advance = FALSE;

	var $user_date_total_arr = NULL;

	var $user_obj = NULL;
	var $user_tax_obj = NULL;
	var $user_wage_obj = NULL;
	var $user_pay_period_total_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;

	var $pay_period_obj = NULL;
	var $pay_period_schedule_obj = NULL;

	var $labor_standard_obj = NULL;
	var $holiday_obj = NULL;

	function __construct($user_id, $pay_period_id) {
		$this->user_id = $user_id;
		$this->pay_period_id = $pay_period_id;

		return TRUE;
	}

	function getUser() {
		return $this->user_id;
	}

	function getPayPeriod() {
		return $this->pay_period_id;
	}

	function getAdvance() {
		if ( isset($this->advance) ) {
			return $this->advance;
		}

		return FALSE;
	}
	function setAdvance($bool) {
		$this->advance = $bool;

		return TRUE;
	}

	//Because this class doesn't extend the original Factory class, we have to duplicate the getGenericObject() code here.
	function getUserObject() {
		if ( isset($this->user_obj) AND is_object($this->user_obj) AND $this->getUser() == $this->user_obj->getID() ) {
			return $this->user_obj;
		} else {
			$lf = TTnew( 'UserListFactory' );
			$lf->getById( $this->getUser() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->user_obj = $lf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}

	function getPayPeriodObject() {
		if ( isset($this->pay_period_obj) AND is_object($this->pay_period_obj) AND $this->getPayPeriod() == $this->pay_period_obj->getID() ) {
			return $this->pay_period_obj;
		} else {
			$lf = TTnew( 'PayPeriodListFactory' );
			$lf->getById( $this->getPayPeriod() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_period_obj = $lf->getCurrent();
				return $this->pay_period_obj;
			}

			return FALSE;
		}
	}

	function getPayPeriodScheduleObject() {
		$pay_period_schedule_id = 0;
		if ( is_object( $this->getPayPeriodObject() ) ) {
			$pay_period_schedule_id = $this->getPayPeriodObject()->getPayPeriodSchedule();
		}
		if ( isset($this->pay_period_schedule_obj) AND is_object($this->pay_period_schedule_obj) AND $pay_period_schedule_id == $this->pay_period_schedule_obj->getID() ) {
			return $this->pay_period_schedule_obj;
		} else {
			$lf = TTnew( 'PayPeriodScheduleListFactory' );
			$lf->getById( $pay_period_schedule_id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_period_schedule_obj = $lf->getCurrent();
				return $this->pay_period_schedule_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryAccountLinkObject() {
		$company_id = 0;
		if ( is_object( $this->getUserObject() ) ) {
			$company_id = $this->getUserObject()->getCompany();
		}

		if ( isset($this->pay_stub_entry_account_link_obj) AND is_object($this->pay_stub_entry_account_link_obj) AND $company_id == $this->pay_stub_entry_account_link_obj->getCompany() ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$lf = TTnew( 'PayStubEntryAccountLinkListFactory' );
			$lf->getByCompanyId( $company_id );
			if ( $lf->getRecordCount() == 1 ) {
				$this->pay_stub_entry_account_link_obj = $lf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	static function getRemittanceDueDate($transaction_epoch, $avg_monthly_remittance) {
		Debug::text('Transaction Date: '. TTDate::getDate('DATE+TIME', $transaction_epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $transaction_epoch > 0 ) {
			if ( $avg_monthly_remittance < 15000 ) {
				Debug::text('Regular Monthly', __FILE__, __LINE__, __METHOD__, 10);
				//15th of the month FOLLOWING transaction_epoch.
				$due_date = mktime(0, 0, 0, ( date('n', $transaction_epoch) + 1), 15, date('y', $transaction_epoch) );
			} elseif ( $avg_monthly_remittance >= 15000 AND $avg_monthly_remittance < 49999.99 ) {
				Debug::text('Accelerated Threshold 1', __FILE__, __LINE__, __METHOD__, 10);
				/*
				Amounts you deduct or withhold from remuneration paid in the first 15 days of the month
				are due by the 25th of the same month. Amounts you withhold from the 16th to the end of
				the month are due by the 10th day of the following month.
				*/
				if ( date('j', $transaction_epoch) <= 15 ) {
					$due_date = mktime(0, 0, 0, date('n', $transaction_epoch), 25, date('y', $transaction_epoch) );
				} else {
					$due_date = mktime(0, 0, 0, ( date('n', $transaction_epoch) + 1), 10, date('y', $transaction_epoch) );
				}
			} elseif ( $avg_monthly_remittance > 50000) {
				Debug::text('Accelerated Threshold 2', __FILE__, __LINE__, __METHOD__, 10);
				/*
				Amounts you deduct or withhold from remuneration you pay any time during the month are due by the third working day (not counting Saturdays, Sundays, or holidays) after the end of the following periods:

				* from the 1st through the 7th day of the month;
				* from the 8th through the 14th day of the month;
				* from the 15th through the 21st day of the month; and
				* from the 22nd through the last day of the month.
				*/
				return FALSE;
			}
		} else {
			$due_date = FALSE;
		}

		return $due_date;
	}

	function getWage($seconds, $rate ) {
		if ( $seconds == '' OR empty($seconds) ) {
			return 0;
		}

		if ( $rate == '' OR empty($rate) ) {
			return 0;
		}

		return bcmul( TTDate::getHours( $seconds ), $rate );
	}

	function getMaximumPayPeriodWage( $user_wage_obj ) {
		Debug::text('Absolute Maximum Pay Period NO Advance: User Wage ID: '. $user_wage_obj->getId() .' Annual Wage: '. $user_wage_obj->getAnnualWage() .' Annual Pay Periods: '. $this->getPayPeriodScheduleObject()->getAnnualPayPeriods(), __FILE__, __LINE__, __METHOD__, 10);
		$maximum_pay_period_wage = bcdiv( $user_wage_obj->getAnnualWage(), $this->getPayPeriodScheduleObject()->getAnnualPayPeriods() );
		Debug::text('Absolute Maximum Pay Period Wage: '. $maximum_pay_period_wage, __FILE__, __LINE__, __METHOD__, 10);

		return $maximum_pay_period_wage;
	}

	function getPayStubAmendmentEarnings() {
		//Get pay stub amendments here.
		$psalf = TTnew( 'PayStubAmendmentListFactory' );

		if ( $this->getAdvance() == TRUE ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text('Pay Stub Amendment Advance Earnings: '. $advance_pos_sum, __FILE__, __LINE__, __METHOD__, 10);

			$full_pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text('Pay Stub Amendment Full Earnings: '. $full_pos_sum, __FILE__, __LINE__, __METHOD__, 10);
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$pos_sum = $advance_pos_sum + ($full_pos_sum / 2);
			$pos_sum = bcadd( $advance_pos_sum, bcdiv( $full_pos_sum, 2 ) );
		} else {
			//$pay_period_end_date =
			$pos_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 10, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;

		Debug::text('Pay Stub Amendment Total Earnings: '. $pos_sum, __FILE__, __LINE__, __METHOD__, 10);

		return $pos_sum;
	}

	function getPayStubAmendmentDeductions() {
		//Get pay stub amendments here.
		$psalf = TTnew( 'PayStubAmendmentListFactory' );

		if ( $this->getAdvance() == TRUE ) {
			//For advances, any PS amendment effective BEFORE the advance end date is considered in full.
			//Any AFTER the advance end date, is considered half.

			//$pay_period_end_date = $this->getPayPeriodObject()->getAdvanceEndDate();
			$advance_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getAdvanceEndDate() );
			Debug::text('Pay Stub Amendment Advance Deductions: '. $advance_neg_sum, __FILE__, __LINE__, __METHOD__, 10);

			$full_neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getAdvanceEndDate(), $this->getPayPeriodObject()->getEndDate() );
			Debug::text('Pay Stub Amendment Full Deductions: '. $full_neg_sum, __FILE__, __LINE__, __METHOD__, 10);
			//Take the full amount of PS amendments BEFORE the advance end date, and half of any AFTER the advance end date.
			//$neg_sum = $advance_neg_sum + ($full_neg_sum / 2);
			$neg_sum = bcadd( $advance_neg_sum, bcdiv( $full_neg_sum, 2 ) );
		} else {
			//$pay_period_end_date =
			$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
		}
		//$neg_sum = $psalf->getAmountSumByUserIdAndTypeIdAndTaxExemptAndAuthorizedAndStartDateAndEndDate( $this->getUser(), 20, FALSE, TRUE, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() )*-1;


		Debug::text('Pay Stub Amendment Total Deductions: '. $neg_sum, __FILE__, __LINE__, __METHOD__, 10);

		return bcmul( $neg_sum, -1 );
	}

	function getRawGrossWage() {
		$wage = 0;

		$udt_arr = $this->getUserDateTotalArray();
		if ( isset($udt_arr['entries']) AND count($udt_arr['entries']) > 0 ) {
			foreach( $udt_arr['entries'] as $udt ) {
				if ( isset($udt['amount']) ) {
					$wage += $udt['amount'];
				}
			}
		}

		Debug::text('Raw Gross Wage: '. $wage, __FILE__, __LINE__, __METHOD__, 10);
		return $wage;
	}

	function getGrossWage() {

		$wage = $this->getRawGrossWage();

		Debug::text('Gross Wage (NOT incl amendments) $'. $wage, __FILE__, __LINE__, __METHOD__, 10);

		return $wage;
	}

	function getUserDateTotalArray() {
		if ( isset($this->user_date_total_arr) ) {
			return $this->user_date_total_arr;
		}

		//If the user date total array isn't set, set it now, and return its value.
		return $this->setUserDateTotalArray();
		//return FALSE;
	}
	
	function setUserDateTotalArray() {
		//Loop through unique UserDateTotal rows... Adding entries to pay stubs.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$udtlf->getByUserIdAndPayPeriodIdAndEndDate( $this->getUser(), $this->getPayPeriod(), $this->getPayPeriodObject()->getEndDate() );

		$calculate_salary = FALSE;

		$dock_absence_time = 0;
		$paid_absence_time = 0;
		$dock_absence_amount = 0;
		$paid_absence_amount = 0;
		$prev_wage_effective_date = 0;
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach( $udtlf as $udt_obj ) {
				Debug::text('User Total Row... Object Type: '. $udt_obj->getObjectType() .' PayCode ID: '. $udt_obj->getPayCode() .' Amount: '. $udt_obj->getTotalTimeAmount() .' Hourly Rate: '. $udt_obj->getColumn('hourly_rate') .' Pay Code Type: '. $udt_obj->getColumn('pay_code_type_id') .' User Wage ID: '. $udt_obj->getColumn('user_wage_id'), __FILE__, __LINE__, __METHOD__, 10);

				if ( $udt_obj->getColumn('pay_code_type_id') == 10 ) { //Paid
					if ( $udt_obj->getObjectType() == 25 ) { //Absence
						Debug::text('User Total Row... Absence Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

						if ( is_object( $udt_obj->getPayCodeObject() )
								AND ( $udt_obj->getPayCodeObject()->getType() == 10 OR $udt_obj->getPayCodeObject()->getType() == 12 )
								AND $udt_obj->getPayCodeObject()->getPayStubEntryAccountID() != '') { //Paid
							Debug::text('Paid Absence Time: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);

							$pay_stub_entry = (int)$udt_obj->getPayCodeObject()->getPayStubEntryAccountID();
							$total_time = $udt_obj->getTotalTime();
							$rate = $udt_obj->getColumn('hourly_rate');
							$amount = $udt_obj->getTotalTimeAmount();

							//Debug::text('Paid Absence Info: '. $udt_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10);
							Debug::text('cPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);

							$paid_absence_time = bcadd($paid_absence_time, $udt_obj->getTotalTime() );
							$paid_absence_amount = bcadd( $paid_absence_amount, $amount);

							//Make sure we add the amount below. Incase there are two or more
							//entries for a paid absence in the same user_wage_id on one pay stub.
							if ( !isset($paid_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
								$paid_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] = 0;
							}
							$paid_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] = bcadd($paid_absence_amount_arr[$udt_obj->getColumn('user_wage_id')], $amount );

							//Some paid absences are over and above employees salary, so we need to track them separately.
							//So we only reduce the salary of the amount of regular paid absences, not "Paid (Above Salary)" absences.
							if ( !isset($reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
								$reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] = 0;
							}
							if ( $udt_obj->getColumn('pay_code_type_id') == 10 ) {
								$reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] = bcadd($reduce_salary_absence_amount_arr[$udt_obj->getColumn('user_wage_id')], $amount );
							}
						}
					} else {
						//Check if they are a salary user...
						//Use WORKED time to calculate regular time. Not just regular time.
						//user_wage_id is only needed for default wages, so it doesn't take into account pay formulas at all.
						if ( $udt_obj->getColumn('user_wage_type_id') > 10 ) { //Salaried
							//Salary
							Debug::text('Strict Salary Wage: Reduce Regular Pay By: Dock Time: '. $dock_absence_time .' and Paid Absence: '. $paid_absence_time, __FILE__, __LINE__, __METHOD__, 10);

							$calculate_salary = TRUE;

							if ( !isset($salary_regular_time[$udt_obj->getColumn('user_wage_id')]) ) {
								$salary_regular_time[$udt_obj->getColumn('user_wage_id')] = 0;
							}

							//Only include regular time units in salary calculation.
							if ( $udt_obj->getObjectType() == 20 ) { //Regular Time
								$salary_regular_time[$udt_obj->getColumn('user_wage_id')] += $udt_obj->getTotalTime();
							}
						} else {
							//Hourly
							Debug::text('Hourly Wage', __FILE__, __LINE__, __METHOD__, 10);
							$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountId();
							$total_time = $udt_obj->getTotalTime();
							$rate = $udt_obj->getColumn('hourly_rate');
							$amount = $udt_obj->getTotalTimeAmount();
							Debug::text('aPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				} elseif ( $udt_obj->getColumn('pay_code_type_id') == 12 ) { //Paid Above
					//This is typically but not always overtime/premium time.
					if ( is_object( $udt_obj->getPayCodeObject() ) AND $udt_obj->getColumn('hourly_rate') != 0 ) {
						Debug::text('Paid (Above Salary) Time... Rate: '. $udt_obj->getColumn('hourly_rate'), __FILE__, __LINE__, __METHOD__, 10);
						$pay_stub_entry = $udt_obj->getPayCodeObject()->getPayStubEntryAccountId();
						$total_time = $udt_obj->getTotalTime();
						$rate = $udt_obj->getColumn('hourly_rate');
						$amount = $udt_obj->getTotalTimeAmount();
						Debug::text('bPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
					} else {
						Debug::text('  NOT Paid Time Policy: ', __FILE__, __LINE__, __METHOD__, 10);
					}
				} elseif ( $udt_obj->getColumn('pay_code_type_id') == 30 ) { //Dock
					$dock_absence_time = bcadd( $dock_absence_time, $udt_obj->getTotalTime() );
					$rate = $udt_obj->getColumn('hourly_rate');
					$amount = $this->getWage( $udt_obj->getTotalTime(), $rate );
					$dock_absence_amount = bcadd( $dock_absence_amount, $amount );

					//Make sure we account for multiple dock absence policies, for the same wage entry in the same pay period.
					if ( isset($dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')]) ) {
						$dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] += $amount;
					} else {
						$dock_absence_amount_arr[$udt_obj->getColumn('user_wage_id')] = $amount;
					}

					Debug::text('DOCK Absence Time.. Adding: '. $udt_obj->getTotalTime() .' Total: '. $dock_absence_time .' Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
					unset($rate);
				}

				if ( isset($pay_stub_entry) AND $pay_stub_entry != '' ) {
					Debug::text('zPay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
					$ret_arr['entries'][] = array(	'user_wage_id' => $udt_obj->getColumn('user_wage_id'),
													'pay_stub_entry' => $pay_stub_entry,
													'total_time' => $total_time,
													'amount' => $amount,
													'rate' => $rate,
													'description' => NULL,
												);
				}
				unset($pay_stub_entry, $amount, $total_time, $rate);
			}

			if ( $calculate_salary == TRUE ) {
				//When the employee is salary and their wage changes in the middle of the pay period and they don't have any regular time worked
				//in any one of the periods that either wage is effective, the period without any regular time was not being paid before.
				//Therefore we moved the salary calcuations to the very end and if there is any regular time in the entire pay period
				//we simply loop through all salaried wages and calculate the pro-rated amounts. Even if no regular time exists in one of the wage periods.
				Debug::text('Calculating Salary...', __FILE__, __LINE__, __METHOD__, 10);

				//Get all wages that apply in this period so we can determine pro-rating for salaries.
				$uwlf = TTNew('UserWageListFactory');
				$uwlf->getDefaultWageGroupByUserIdAndStartDateAndEndDate( $this->getUser(), $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate() );
				if ( $uwlf->getRecordCount() > 0 ) {
					foreach( $uwlf as $uw_obj ) {
						$description = NULL;
						if ( $uw_obj->getType() != 10 ) {
							if ( isset($dock_absence_amount_arr[$uw_obj->getID()]) ) {
								$dock_absence_wage = abs( $dock_absence_amount_arr[$uw_obj->getID()] ); //Make sure the dock absence wage is always a positive, since we subtract is below.
							} else {
								$dock_absence_wage = 0;
							}
							if ( isset($reduce_salary_absence_amount_arr[$uw_obj->getID()]) ) {
								$paid_absence_wage = abs( $reduce_salary_absence_amount_arr[$uw_obj->getID()] ); //Make sure the dock absence wage is always a positive, since we subtract is below.
							} else {
								$paid_absence_wage = 0;
							}
							Debug::text('Wage ID: '. $uw_obj->getID() .' Dock Absence Wage: '. $dock_absence_wage .' Paid Absence Wage: '. $paid_absence_wage, __FILE__, __LINE__, __METHOD__, 10);

							$maximum_wage_salary = UserWageFactory::proRateSalary( $this->getMaximumPayPeriodWage( $uw_obj ), $uw_obj->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );

							$amount = bcsub( $maximum_wage_salary, bcadd( $dock_absence_wage, $paid_absence_wage ) );
							//Include time if we have it, otherwise use 0.
							$total_time = ( isset($salary_regular_time[$uw_obj->getID()]) ) ? $salary_regular_time[$uw_obj->getID()] : 0; //Dont minus dock/paid absence time. Because its already not included.
							$rate = NULL;
							$pay_stub_entry = $this->getPayStubEntryAccountLinkObject()->getRegularTime();
							unset($dock_absence_wage, $paid_absence_wage);
							
							$salary_dates = UserWageFactory::proRateSalaryDates( $uw_obj->getEffectiveDate(), $prev_wage_effective_date, $this->getPayPeriodObject()->getStartDate(), $this->getPayPeriodObject()->getEndDate(), $this->getUserObject()->getTerminationDate() );
							if ( is_array($salary_dates) ) {
								$description = TTi18n::getText('Prorate Salary:').' '. TTDate::getDate('DATE', $salary_dates['start_date'] ) .' - '. TTDate::getDate('DATE', $salary_dates['end_date'] ) .' ('. $salary_dates['percent'].'%)';
							}

							if ( isset($pay_stub_entry) AND $pay_stub_entry != '' ) {
								Debug::text('  Pay Stub Entry Account ID: '. $pay_stub_entry .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
								$ret_arr['entries'][] = array(	'user_wage_id' => $udt_obj->getColumn('user_wage_id'),
																'pay_stub_entry' => $pay_stub_entry,
																'total_time' => $total_time,
																'amount' => $amount,
																'rate' => $rate,
																'description' => $description,
															);
							}

							$prev_wage_effective_date = $uw_obj->getEffectiveDate();

							unset($pay_stub_entry, $amount, $total_time, $rate);
						}
					}
				}
				unset($uwlf, $uw_obj);
			}
		} else {
			Debug::text('NO UserDate Total entries found.', __FILE__, __LINE__, __METHOD__, 10);
		}

		$ret_arr['other']['paid_absence_time'] = $paid_absence_time;
		$ret_arr['other']['dock_absence_time'] = $dock_absence_time;

		$ret_arr['other']['paid_absence_amount'] = $paid_absence_amount;
		$ret_arr['other']['dock_absence_amount'] = $dock_absence_amount;

		if ( isset($ret_arr) ) {
			Debug::Arr($ret_arr, 'UserDateTotal Array', __FILE__, __LINE__, __METHOD__, 10);
			return $this->user_date_total_arr = $ret_arr;
		}

		return FALSE;
	}
}
?>