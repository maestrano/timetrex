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
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_NY extends PayrollDeduction_US {
/*
														10 => 'Single',
														20 => 'Married',

Used to be:
														10 => 'Single',
														20 => 'Married - Spouse Works',
														30 => 'Married - Spouse does not Work',
														40 => 'Head of Household',
*/

	var $state_options = array(
								1420099200 => array( // 01-Jan-2015
													'standard_deduction' => array(
																				'10' => 7350.00,
																				'20' => 7850.00,
																				'30' => 7850.00,
																				'40' => 7350.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													),
								1388563200 => array( // 01-Jan-2014
													'standard_deduction' => array(
																				'10' => 7250.00,
																				'20' => 7750.00,
																				'30' => 7750.00,
																				'40' => 7250.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													),
								1357027200 => array( // 01-Jan-2013
													'standard_deduction' => array(
																				'10' => 7150.00,
																				'20' => 7650.00,
																				'30' => 7650.00,
																				'40' => 7150.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													),
								1136102400 => array(
													'standard_deduction' => array(
																				'10' => 6975.00,
																				'20' => 7475.00,
																				'30' => 7475.00,
																				'40' => 6975.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													)
								);

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $state_deductions), $state_allowance );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( isset($retarr['standard_deduction'][$this->getStateFilingStatus()]) ) {
			$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction = 0;
		}

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( isset($retarr['allowance'][$this->getStateFilingStatus()]) ) {
			$allowance = $retarr['allowance'][$this->getStateFilingStatus()];
		} else {
			$allowance = 0;
		}

		if ( $this->getStateAllowance() == 0 ) {
			$retval = 0;
		} else {
			$retval = bcmul( $this->getStateAllowance(), $allowance );
		}

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);


		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
