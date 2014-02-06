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
 * $Id: NY_NYC.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_NY_NYC extends PayrollDeduction_US_NY {
/*
 														10 => 'Single',
														20 => 'Married',

Used to be:
														10 => 'Single',
														20 => 'Married - Spouse Works',
														30 => 'Married - Spouse does not Work',
														40 => 'Head of Household',
*/

	var $district_options = array(
								1136102400 => array(
													'standard_deduction' => array(
																				'10' => 5000.00,
																				'20' => 5500.00,
																				'30' => 5000.00,
																				'40' => 5000.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													)
								);

	function getDistrictPayPeriodDeductions() {
		return bcdiv($this->getDistrictTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getDistrictAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$district_deductions = $this->getDistrictStandardDeduction();
		$district_allowance = $this->getDistrictAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $district_deductions), $district_allowance );

		Debug::text('District Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getDistrictStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['standard_deduction'][$this->getDistrictFilingStatus()];

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getDistrictAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance = $retarr['allowance'][$this->getDistrictFilingStatus()];

		if ( $this->getDistrictAllowance() == 0 ) {
			$retval = 0;
		} else {
			$retval = bcmul( $this->getDistrictAllowance(), $allowance );
		}

		Debug::text('District Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);


		return $retval;
	}

	function getDistrictTaxPayable() {
		$annual_income = $this->getDistrictAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getDistrictRate($annual_income);
			$district_constant = $this->getData()->getDistrictConstant($annual_income);
			$district_rate_income = $this->getData()->getDistrictRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $district_rate_income ), $rate ), $district_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('District Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
