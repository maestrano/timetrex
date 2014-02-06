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
 * $Id: MA.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_MA extends PayrollDeduction_US {
/*
	protected $state_ma_filing_status_options = array(
														10 => 'Regular',
														20 => 'Head of Household',
														30 => 'Blind',
														40 => 'Head of Household and Blind'
									);
*/

	var $state_options = array(
								1325404800 => array( //01-Jan-12
													'rate' => 5.25,
													'allowance' => array( 3400, 1000 ), //1 = Base amount, 2 = Per Allowance multiplier
													'federal_tax_maximum' => 2000,
													'minimum_income' => 8000,
													),
								1230796800 => array( //01-Jan-09
													'rate' => 5.3,
													'allowance' => array( 3400, 1000 ), //1 = Base amount, 2 = Per Allowance multiplier
													'federal_tax_maximum' => 2000,
													'minimum_income' => 8000,
													),
								1136102400 => array( //01-Jan-06
													'rate' => 5.3,
													'standard_deduction' => array(
																			10 => 0,
																			20 => 2100,
																			30 => 2200,
																			40 => 2200
																			),
													'allowance' => array( 3850, 2850 ),
													'federal_tax_maximum' => 2000,
													'minimum_income' => 8000,
													)
								);

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = bcadd( $this->getAnnualEmployeeMedicare(), $this->getAnnualEmployeeSocialSecurity() );
		if ( $this->getDate() >= strtotime('01-Jan-2009') ) {
			$state_deductions = 0;
		} else {
			$state_deductions = $this->getStateStandardDeduction();
		}
		$state_allowance = $this->getStateAllowanceAmount();

		if ( $federal_tax > $this->getStateFederalTaxMaximum() ) {
			$federal_tax = $this->getStateFederalTaxMaximum();
		}
		Debug::text('Federal Tax: '. $federal_tax, __FILE__, __LINE__, __METHOD__, 10);

		$income = bcsub( bcsub( bcsub($annual_income, $federal_tax), $state_deductions), $state_allowance );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$maximum = $retarr['federal_tax_maximum'];

		Debug::text('Maximum State allowed Federal Tax: '. $maximum, __FILE__, __LINE__, __METHOD__, 10);

		return $maximum;
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

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['allowance'];
		if ( $this->getDate() >= strtotime('01-Jan-2009') ) {
			if ( $this->getStateAllowance() == 0 ) {
				$retval = 0;
			} else {
				$retval = bcadd($allowance_arr[0], bcmul( $this->getStateAllowance(), $allowance_arr[1] ) );
			}
		} else {
			if ( $this->getStateAllowance() == 0 ) {
				$retval = 0;
			} elseif ( $this->getStateAllowance() == 1 ) {
				$retval = $allowance_arr[0];
			} else {
				$retval = bcadd($allowance_arr[0], bcmul( bcsub( $this->getStateAllowance(), 1 ), $allowance_arr[1] ) );
			}
		}

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
			if ( $retarr == FALSE ) {
				return FALSE;
			}

			$rate = bcdiv( $retarr['rate'], 100 );

			$retval = bcmul( $annual_income, $rate);
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
