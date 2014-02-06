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
 * $Id: NC.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_NC extends PayrollDeduction_US {
/*
	protected $state_nc_filing_status_options = array(
														10 => 'Single',
														20 => 'Married or Qualified Widow(er)',
														30 => 'Head of Household',
									);
*/
	var $state_options = array(
								//No changes for 01-Jan-09
								1136102400 => array(
													'standard_deduction' => array(
																				'10' => 3000.00,
																				'20' => 3000.00,
																				'30' => 4400.00,
																				),
													'allowance_cutoff' => array(
																				'10' => 60000.00,
																				'20' => 50000.00,
																				'30' => 80000.00,
																				),
													'allowance' => array(
																		1 => 2500,
																		2 => 2000
																		),
													)
								);

	function getStatePayPeriodDeductions() {
		return $this->RoundNearestDollar( bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() ) );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( bcsub($annual_income, $state_deductions), $state_allowance);

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateAllowanceCutOff() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$retval = $retarr['allowance_cutoff'][$this->getStateFilingStatus()];

		Debug::text('State Employee Allowance Cutoff: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( $this->getAnnualTaxableIncome() < $this->getStateAllowanceCutOff() ) {
			$allowance_arr = $retarr['allowance'][1];
		} else {
			$allowance_arr = $retarr['allowance'][2];
		}

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text('Allowances: '. $this->getStateAllowance() .' Allowance Multiplier: '. $allowance_arr .' State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			//$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			$retval = bcsub( bcmul( $annual_income, $rate), $state_constant);
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

}
?>
