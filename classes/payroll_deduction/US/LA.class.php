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


/*

 ** Formula partially based on: http://i2i.nfc.usda.gov/Publications/Tax_Formulas/State_City_County/taxla.html

 *Due to backwards compatibility user_value_3 is filing status, NOT user_value_1;

 10 = Single
 20 = Married Filing Jointly

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_LA extends PayrollDeduction_US {

	var $state_options = array(
								1136102400 => array(
													'allowance' => 4500,
													'dependant_allowance' => 1000,
													'allowance_rates' => array( //Personal exceptions
																				  10 => array(
																							  0 => array(12500, 2.1, 0),
																							  1 => array(12500, 3.7, 262.50),
																							  ),
																				  20 => array(
																							  0 => array(25000, 2.1, 0),
																							  1 => array(25000, 3.45, 525),
																							)
																				  ),
													)
								);


	function getStateFilingStatus() {
		if ( $this->getUserValue3() != '' ) {
			return $this->getUserValue3();
		}

		return 10; //Single
	}
	function setStateFilingStatus( $value ) {
		return $this->setUserValue3( $value );
	}

	function setStateAllowance($value) {
		return $this->setUserValue1( $value );
	}
	function getStateAllowance() {
		return $this->getUserValue1();
	}

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateTotalAllowanceAmount() {
		$retval = bcadd( $this->getStateAllowanceAmount(), $this->getStateDependantAllowanceAmount() );

		Debug::text('State Total Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getUserValue1(), $allowance_arr );

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['dependant_allowance'];

		$retval = bcmul( $this->getUserValue2(), $allowance_arr );

		Debug::text('State Dependant Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getDataByIncome( $income, $arr ) {
		if ( !is_array($arr) ) {
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = count($arr) - 1;
		$i=0;
		foreach( $arr as $key => $values ) {
			if ( $income > $prev_value AND $income <= $values[0]) {
				return $values;
			} elseif ($i == $total_rates) {
				return $values;
			}
			$prev_value = $values[0];
			$i++;
		}

		return FALSE;
	}

	function getStateTaxableAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$retval = 0;
		if ( $this->getStateTotalAllowanceAmount() > 0 AND isset($retarr['allowance_rates'][$this->getStateFilingStatus()]) ) {
			$standard_deduction_arr = $this->getDataByIncome( $this->getStateTotalAllowanceAmount(), $retarr['allowance_rates'][$this->getStateFilingStatus()] );
			//Debug::Arr($standard_deduction_arr, 'State Taxable Allowance: '. $this->getStateTotalAllowanceAmount(), __FILE__, __LINE__, __METHOD__, 10);

			$retval = bcadd( bcmul( $this->getStateTotalAllowanceAmount(), bcdiv( $standard_deduction_arr[1], 100 ) ), $standard_deduction_arr[2]) ;

			Debug::text('State Taxable Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
            $prev_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			Debug::text('Rate: '. $rate .' Constant: '. $state_constant .' Prev Rate Income: '. $prev_income, __FILE__, __LINE__, __METHOD__, 10);
			$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
			Debug::text('Inital State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

			$retval = bcsub( $retval, $this->getStateTaxableAllowanceAmount() );
			Debug::text('Final State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
