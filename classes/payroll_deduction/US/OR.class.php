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
 * $Id: OR.class.php 8720 2012-12-29 01:06:58Z ipso $
 * $Date: 2012-12-28 17:06:58 -0800 (Fri, 28 Dec 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
/*
 Need to manually calculate the brackets, as the brackets less than 50,000 include the allowance ( 188 ) in the constant.
 Exclude the allowance from each bracket, then getStateTaxPayable() will add $188 (allowance amount) to the constant if the annual income is less than 50,000.
 Check getStateTaxPayable() for the 50,000 setting.
*/
class PayrollDeduction_US_OR extends PayrollDeduction_US {
	var $original_filing_status = NULL;
	var $state_options = array(
								1357027200 => array( //01-Jan-13
												'standard_deduction' => array(
																			'10' => 2080,
																			'20' => 4160,
																			),
												'allowance' => 188,
												'federal_tax_maximum' => 6250,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6250,
																						125000 => 6250,
																						130000 => 5000,
																						135000 => 3750,
																						140000 => 2500,
																						145000 => 1250,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6250,
																						250000 => 6250,
																						260000 => 5000,
																						270000 => 3750,
																						280000 => 2500,
																						290000 => 1250,
																						290000 => 0,
																					 ),
																	),
												),
								1325404800 => array( //01-Jan-12
												'standard_deduction' => array(
																			'10' => 2025,
																			'20' => 4055,
																			),
												'allowance' => 183,
												'federal_tax_maximum' => 6100,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6100,
																						125000 => 6100,
																						130000 => 4850,
																						135000 => 3650,
																						140000 => 2400,
																						145000 => 1200,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6100,
																						250000 => 6100,
																						260000 => 4850,
																						270000 => 3650,
																						280000 => 2400,
																						290000 => 1200,
																						290000 => 0,
																					 ),
																	),
												),
								1262332800 => array( //01-Jan-10
												'standard_deduction' => array(
																			'10' => 1950,
																			'20' => 3900,
																			),
												'allowance' => 177,
												'federal_tax_maximum' => 5850
												),
								1230796800 => array( //01-Jan-09
												'standard_deduction' => array(
																			'10' => 1945,
																			'20' => 3895,
																			),
												'allowance' => 176,
												'federal_tax_maximum' => 5850
												),
								1167638400 => array(
 													'standard_deduction' => array(
																				'10' => 1870,
																				'20' => 3740,
																				),
													'allowance' => 165,
													'federal_tax_maximum' => 5500
													),
								1136102400 => array(
 													'standard_deduction' => array(
																				'10' => 0,
																				'20' => 0,
																				),
													'allowance' => 154,
													'federal_tax_maximum' => 4500
													)
								);

	private function getStateRateArray($input_arr, $income) {
		if ( !is_array($input_arr) ) {
			return 0;
		}

		$total_rates = count($input_arr) - 1;
		$prev_bracket=0;
		$i=0;
		foreach( $input_arr as $bracket => $value ) {
			Debug::text('Bracket: '. $bracket .' Value: '.$value, __FILE__, __LINE__, __METHOD__, 10);

			if ($income >= $prev_bracket AND $income < $bracket) {
				Debug::text('Found Bracket: '. $bracket  .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);

				return $value;
			} elseif ($i == $total_rates) {
				Debug::text('Found Last Bracket: '. $bracket .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				return $value;
			}

			$prev_bracket = $bracket;
			$i++;
		}

		return FALSE;
	}

	function getStatePayPeriodDeductions() {
		//IF exemptions are 3 or more, change filing status to married.
		$this->original_filing_status = $this->getStateFilingStatus();

		if ( $this->getStateFilingStatus() == 10 AND $this->getStateAllowance() >= 3 ) {
			Debug::text('Forcing to Married Filing Status from: '. $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10);
			$this->setStateFilingStatus(20); //Married tax rates.
		}
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();

		if ( $federal_tax > $this->getStateFederalTaxMaximum() ) {
			$federal_tax = $this->getStateFederalTaxMaximum();
		}

		$income = bcsub( bcsub( $annual_income, $federal_tax), $this->getStateStandardDeduction() );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$maximum = $retarr['federal_tax_maximum'];

		if ( isset($retarr['phase_out'][$this->getStateFilingStatus()]) ) {
			$phase_out_arr = $retarr['phase_out'][$this->getStateFilingStatus()];
			$phase_out_maximum = $this->getStateRateArray($phase_out_arr, $this->getAnnualTaxableIncome() );
			if ( $maximum > $phase_out_maximum ) {
				Debug::text('Maximum allowed Federal Tax exceeded phase out maximum of: '. $phase_out_maximum, __FILE__, __LINE__, __METHOD__, 10);
				$maximum = $phase_out_maximum;
			}
		}

		Debug::text('Maximum State allowed Federal Tax: '. $maximum, __FILE__, __LINE__, __METHOD__, 10);

		return $maximum;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( $this->original_filing_status == $this->getStateFilingStatus() AND isset($retarr['standard_deduction'][$this->getStateFilingStatus()]) ) {
			$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction = $retarr['standard_deduction'][10];
		}

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text('State Allowance Amount: '. $retval .' Allowances: '. $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			if ( $this->getDate() >= 1325404800 AND $annual_income < 50000 )  { //01-Jan-2011
				$state_array = $this->getDataFromRateArray($this->getDate(), $this->state_options);
				$state_constant += $state_array['allowance'];
			}
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		$retval = bcsub( $retval, $this->getStateAllowanceAmount() );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
