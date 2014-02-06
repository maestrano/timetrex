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
 * $Revision: 8720 $
 * $Id: MD.class.php 8720 2012-12-29 01:06:58Z ipso $
 * $Date: 2012-12-28 17:06:58 -0800 (Fri, 28 Dec 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_MD extends PayrollDeduction_US {
	/*
								10 => TTi18n::gettext('Single'),
								20 => TTi18n::gettext('Married (Filing Jointly)'),
								30 => TTi18n::gettext('Married (Filing Separately)'),
								40 => TTi18n::gettext('Head of Household'),
	*/

	//
	//I don't think will ever be 100% accurate, because the tax brackets completely change for each county, based on the county percent.
	//We will need to have the county tax rate passed into this class so the proper calculations can be made.
	//
	var $state_options = array(
								//01-Jan-13: No Changes
								//01-Jan-12: No Changes
								//01-Jan-11: No Changes
								//01-Jan-10: No Changes
								//01-Jan-09: No Changes
								1199174400 => array( //2008
													'standard_deduction' => array(
																			'minimum' => 1500,
																			'maximum' => 2000,
																			'rate' => 0.15, //percent
																			),
													'allowance' => 3200
													),
								);

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		//$federal_tax = $this->getFederalTaxPayable();
		$standard_deduction = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		//Debug::text('Federal Annual Tax: '. $federal_tax, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Standard Deduction: '. $standard_deduction, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('State Allowance: '. $state_allowance, __FILE__, __LINE__, __METHOD__, 10);

		$income = bcsub( bcsub( $annual_income, $standard_deduction ), $state_allowance);

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		$county_rate = bcdiv( $this->getUserValue3(), 100);
		if ( !is_numeric($county_rate) OR $county_rate < 0 ) {
			$county_rate = 0;
		}
		Debug::text('County Rate: '. $county_rate, __FILE__, __LINE__, __METHOD__, 10);

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			//Modify rate/constant based on county rate, since it affects each tax bracket.

			//Calculate the constant modifier, based on the county_rate percent difference from the state rate.
			$constant_modifier = bcdiv( $county_rate, $rate ); //Percent that the constant needs to be modified by.
			$county_constant = bcmul( $state_constant, $constant_modifier );
			Debug::text('County: Rate: '. $county_rate .' Modifier Rate: '. $constant_modifier .' County Constant: '. $county_constant, __FILE__, __LINE__, __METHOD__, 10);

			$rate = bcadd( $rate, $county_rate );
			$state_constant = bcadd( $state_constant, $county_constant );

			Debug::text('Rate: '. $rate .' Constant: '. $state_constant .' Rate Income: '. $state_rate_income, __FILE__, __LINE__, __METHOD__, 10);
			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction_arr = $retarr['standard_deduction'];

		$retval = bcmul( $this->getAnnualTaxableIncome(), $deduction_arr['rate'] );

		if ( $retval < $deduction_arr['minimum']) {
			$retval = $deduction_arr['minimum'];
		}

		if ( $retval > $deduction_arr['maximum']) {
			$retval = $deduction_arr['maximum'];
		}

		Debug::text('State Standard Deduction Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
