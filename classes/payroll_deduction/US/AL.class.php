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
 * $Revision: 8371 $
 * $Id: AL.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_AL extends PayrollDeduction_US {
/*
	protected $state_al_filing_status_options = array(
														10 => 'Status "S" Claiming $1500',
														20 => 'Status "M" Claiming $3000',
														30 => 'Status "0"',
														40 => 'Head of Household'
														50 => 'Status "MS"'
									);
*/

	var $state_options = array(
								1373353200 => array( //13-Jul-09
													'standard_deduction_rate' => 0,
													'standard_deduction_maximum' => array(
																				'10' => array(
																							//1 = Income
																							//2 = Reduce By
																							//3 = Reduce by for every amount over the prev income level.
																							//4 = Previous Income
																							0 => array(20499, 2500, 0, 0, 0),
																							1 => array(30000, 2500, 25, 500, 20499),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'20' => array(
																							0 => array(20499, 7500, 0, 0, 0),
																							1 => array(30000, 7500, 175, 500, 20499),
																							2 => array(30000, 4000, 0, 0, 30000)
																							),
																				'30' => array(
																							0 => array(20499, 2500, 0, 0, 0),
																							1 => array(30000, 2500, 25, 500, 20000),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'40' => array(
																							0 => array(20499, 4700, 0, 0, 0),
																							1 => array(30000, 4700, 135, 500, 20499),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'50' => array(
																							0 => array(10249, 3750, 0, 0, 0),
																							1 => array(15000, 3750, 88, 250, 10249),
																							2 => array(15000, 2000, 0, 0, 15000)
																							),
																				),
													'personal_deduction' => array(
																				'10' => 1500,
																				'20' => 3000,
																				'30' => 0,
																				'40' => 3000,
																				'50' => 1500,
																				),

													'dependant_allowance' => array(
																				0 => array(20000, 1000),
																				1 => array(100000, 500),
																				2 => array(100000, 300)
																				)
													),
								1167638400 => array(
													'standard_deduction_rate' => 0,
													'standard_deduction_maximum' => array(
																				'10' => array(
																							//1 = Income
																							//2 = Reduce By
																							//3 = Reduce by for every amount over the prev income level.
																							//4 = Previous Income
																							0 => array(20000, 2500, 0, 0, 0),
																							1 => array(30000, 2500, 25, 500, 20000),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'20' => array(
																							0 => array(20000, 7500, 0, 0, 0),
																							1 => array(30000, 7500, 175, 500, 20000),
																							2 => array(30000, 4000, 0, 0, 30000)
																							),
																				'30' => array(
																							0 => array(20000, 2500, 0, 0, 0),
																							1 => array(30000, 2500, 25, 500, 20000),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'40' => array(
																							0 => array(20000, 4700, 0, 0, 0),
																							1 => array(30000, 4700, 135, 500, 20000),
																							2 => array(30000, 2000, 0, 0, 30000)
																							),
																				'50' => array(
																							0 => array(10000, 3750, 0, 0, 0),
																							1 => array(15000, 3750, 88, 250, 10000),
																							2 => array(15000, 2000, 0, 0, 15000)
																							),
																				),
													'personal_deduction' => array(
																				'10' => 1500,
																				'20' => 3000,
																				'30' => 0,
																				'40' => 3000,
																				'50' => 1500,
																				),

													'dependant_allowance' => array(
																				0 => array(20000, 1000),
																				1 => array(100000, 500),
																				2 => array(100000, 300)
																				)
													),
								1136102400 => array(
													'standard_deduction_rate' => 20,
													'standard_deduction_maximum' => array(
																				'10' => 2000,
																				'20' => 4000,
																				'30' => 2000,
																				'40' => 2000,
																				'50' => 2000,
																				),
													'personal_deduction' => array(
																				'10' => 1500,
																				'20' => 3000,
																				'30' => 0,
																				'40' => 3000,
																				'50' => 1500
																				),

													'dependant_allowance' => 300
													)
								);

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$standard_deduction = $this->getStateStandardDeduction();
		$personal_deduction = $this->getStatePersonalDeduction();
		$dependant_allowance = $this->getStateDependantAllowanceAmount();

		Debug::text('Federal Annual Tax: '. $federal_tax, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Standard Deduction: '. $standard_deduction, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Personal Deduction: '. $personal_deduction, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Dependant Allowance: '. $dependant_allowance, __FILE__, __LINE__, __METHOD__, 10);

		$income = bcsub( bcsub( bcsub( bcsub( $annual_income, $standard_deduction), $personal_deduction), $dependant_allowance), $federal_tax);

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getDataByIncome( $income, $arr ) {
		if ( !is_array($arr) ) {
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = count($arr) - 1;
		$i=0;
		foreach( $arr as $key => $values ) {
			if ($this->getAnnualTaxableIncome() > $prev_value AND $this->getAnnualTaxableIncome() <= $values[0]) {
				return $values;
			} elseif ($i == $total_rates) {
				return $values;
			}
			$prev_value = $values[0];
			$i++;
		}

		return FALSE;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( $this->getDate() >= strtotime('01-Jan-2007') ) {
			Debug::text('Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10);
			$deduction_arr = $this->getDataByIncome( $this->getAnnualTaxableIncome(), $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()] );

			if ( $deduction_arr[3] > 0 ) {
				Debug::text('Complex Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10);
				//Find out how far we're over the previous income level.
				$deduction = bcsub( $deduction_arr[1], bcmul( ceil( bcdiv( bcsub($this->getAnnualTaxableIncome(), $deduction_arr[4]), $deduction_arr[3] ) ), $deduction_arr[2] ) );
			} else {
				Debug::text('Basic Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10);
				$deduction = $deduction_arr[1];
			}
		} else {
			Debug::text('Standard Deduction Forumla (OLD)', __FILE__, __LINE__, __METHOD__, 10);
			$rate = bcdiv( $retarr['standard_deduction_rate'], 100 );

			$deduction = bcmul( $this->getAnnualTaxableIncome(), $rate);

			if ( $deduction >= $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()] ) {
				$deduction = $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()];
			}
		}

		Debug::text('Standard Deduction: '. $deduction .' Filing Status: '. $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStatePersonalDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['personal_deduction'][$this->getStateFilingStatus()];

		Debug::text('Personal Deduction: '. $deduction .' Filing Status: '. $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( $this->getDate() >= strtotime('01-Jan-2007') ) {
			$allowance_arr = $this->getDataByIncome( $this->getAnnualTaxableIncome(), $retarr['dependant_allowance'] );
			$allowance = $allowance_arr[1];
		} else {
			$allowance = $retarr['dependant_allowance'];
		}

		$retval = bcmul($allowance, $this->getStateAllowance() );

		Debug::text('State Dependant Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
            $prev_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			Debug::text('Rate: '. $rate .' Constant: '. $state_constant .' Prev Rate Income: '. $prev_income, __FILE__, __LINE__, __METHOD__, 10);
			$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
