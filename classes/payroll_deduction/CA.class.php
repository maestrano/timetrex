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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA extends PayrollDeduction_CA_Data {
	function setFederalTotalClaimAmount($value) {
		//TC
		$this->data['federal_total_claim_amount'] = $value;

		return TRUE;
	}
	function getFederalTotalClaimAmount() {
		//Check to make sure the claim amount is at the minimum,
		//as long as it is NOT 0. (outside country)

		//Check claim amount from the previous year, so if the current year setting matches
		//that exactly, we know to use the current year value instead.
		//This helps when the claim amount decreases.
		//Also check next years amount in case the amount gets increased then they try to calculate pay stubs in the previous year.
		$previous_year = ( TTDate::getBeginYearEpoch( $this->getDate() ) - 86400 );
		$next_year = ( TTDate::getEndYearEpoch( $this->getDate() ) + 86400 );

		if ( $this->data['federal_total_claim_amount'] > 0 ) {
			if ( $this->getBasicFederalClaimCodeAmount() > 0
					AND (
							$this->data['federal_total_claim_amount'] < $this->getBasicFederalClaimCodeAmount()
							OR
							$this->data['federal_total_claim_amount'] == $this->getBasicFederalClaimCodeAmount( $previous_year )
							OR
							$this->data['federal_total_claim_amount'] == $this->getBasicFederalClaimCodeAmount( $next_year )
						)
				) {
				Debug::text('Using Basic Federal Claim Code Amount: '. $this->getBasicFederalClaimCodeAmount() .' (Previous Amount: '. $this->data['federal_total_claim_amount'] .') Date: '. TTDate::getDate('DATE', $this->getDate() ), __FILE__, __LINE__, __METHOD__, 10);
				return $this->getBasicFederalClaimCodeAmount();
			}
		}

		return $this->data['federal_total_claim_amount'];
	}

	function setProvincialTotalClaimAmount($value) {
		//TCP
		$this->data['provincial_total_claim_amount'] = $value;

		return TRUE;
	}
	function getProvincialTotalClaimAmount() {
		//Check to make sure the claim amount is at the minimum,
		//as long as it is NOT 0. (outside country)

		//Check claim amount from the previous year, so if the current year setting matches
		//that exactly, we know to use the current year value instead.
		//This helps when the claim amount decreases.
		//Also check next years amount in case the amount gets increased then they try to calculate pay stubs in the previous year.
		$previous_year = ( TTDate::getBeginYearEpoch( $this->getDate() ) - 86400 );
		$next_year = ( TTDate::getEndYearEpoch( $this->getDate() ) + 86400 );

		if ( $this->data['provincial_total_claim_amount'] > 0 ) {
			if ( $this->getBasicProvinceClaimCodeAmount() > 0
					AND (
							$this->data['provincial_total_claim_amount'] < $this->getBasicProvinceClaimCodeAmount()
							OR
							$this->data['provincial_total_claim_amount'] == $this->getBasicProvinceClaimCodeAmount( $previous_year )
							OR
							$this->data['provincial_total_claim_amount'] == $this->getBasicProvinceClaimCodeAmount( $next_year )
						)
				) {
				Debug::text('Using Basic Provincial Claim Code Amount: '. $this->getBasicProvinceClaimCodeAmount() .' (Previous Amount: '. $this->data['provincial_total_claim_amount'] .') Date: '. TTDate::getDate('DATE', $this->getDate() ), __FILE__, __LINE__, __METHOD__, 10);
				return $this->getBasicProvinceClaimCodeAmount();
			}
		}

		return $this->data['provincial_total_claim_amount'];
	}

	function setFederalAdditionalDeduction($value) {
		if ($value >= 0) {
			$this->data['additional_deduction'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getFederalAdditionalDeduction() {
		if ( isset($this->data['additional_deduction']) ) {
			return $this->data['additional_deduction'];
		}

		return FALSE;
	}

	function setUnionDuesAmount($value) {
		$this->data['union_dues_amount'] = $value;

		return TRUE;
	}
	function getUnionDuesAmount() {
		if ( isset($this->data['union_dues_amount']) ) {
			return $this->data['union_dues_amount'];
		}

		return 0;
	}

	function setCPPExempt($value) {
		$this->data['cpp_exempt'] = $value;

		return TRUE;
	}
	function getCPPExempt() {
		if ( isset($this->data['cpp_exempt']) ) {
			return $this->data['cpp_exempt'];
		}

		return FALSE;
	}

	function setYearToDateCPPContribution($value) {
		if ( $value > 0 ) {
			$this->data['cpp_year_to_date_contribution'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getYearToDateCPPContribution() {
		if (isset($this->data['cpp_year_to_date_contribution'])) {
			return $this->data['cpp_year_to_date_contribution'];
		}

		return 0;
	}

	function setEIExempt($value) {
		$this->data['ei_exempt'] = $value;

		return TRUE;
	}
	function getEIExempt() {
		//Default to true
		if ( isset($this->data['ei_exempt']) ) {
			return $this->data['ei_exempt'];
		}

		return FALSE;
	}

	function setYearToDateEIContribution($value) {
		if ( $value > 0 ) {
			$this->data['ei_year_to_date_contribution'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getYearToDateEIContribution() {
		if (isset($this->data['ei_year_to_date_contribution'])) {
			return $this->data['ei_year_to_date_contribution'];
		}

		return 0;
	}

	function setWCBRate($value) {
		$this->data['wcb_rate'] = $value;

		return TRUE;
	}
	function getWCBRate() {
		//Divide rate by 100 so its not a percent anymore.
		return bcdiv( $this->data['wcb_rate'], 100 );

		return TRUE;
	}

	function setFederalTaxExempt($value) {
		$this->data['federal_tax_exempt'] = $value;

		return TRUE;
	}
	function getFederalTaxExempt() {
		//Default to true
		if ( isset($this->data['federal_tax_exempt']) ) {
			return $this->data['federal_tax_exempt'];
		}

		return FALSE;
	}

	function setProvincialTaxExempt($value) {
		$this->data['provincial_tax_exempt'] = $value;

		return TRUE;
	}
	function getProvincialTaxExempt() {
		//Default to true
		if ( isset($this->data['provincial_tax_exempt']) ) {
			return $this->data['provincial_tax_exempt'];
		}

		return FALSE;
	}

	function setEnableCPPAndEIDeduction($value) {
		$this->data['enable_cpp_and_ei_deduction'] = $value;

		return TRUE;
	}
	function getEnableCPPAndEIDeduction() {
		//Default to true
		if ( isset($this->data['enable_cpp_and_ei_deduction']) ) {
			return $this->data['enable_cpp_and_ei_deduction'];
		}

		return FALSE;
	}


	function getPayPeriodTaxDeductions() {
		/*
			T = [(T1 + T2) / P] + L
		*/

		$T1 = $this->getFederalTaxPayable();
		$T2 = $this->getProvincialTaxPayable();
		$P = $this->getAnnualPayPeriods();
		$L = $this->getFederalAdditionalDeduction();

		//$T = (($T1 + $T2) / $P) + $L;
		$T = bcadd( bcdiv( bcadd( $T1, $T2), $P ), $L);

		Debug::text('T: '. $T, __FILE__, __LINE__, __METHOD__, 10);

		return $T;
	}

	function getFederalPayPeriodDeductions() {
		return bcdiv( $this->getFederalTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getProvincialPayPeriodDeductions() {
		return bcdiv( $this->getProvincialTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getAnnualTaxableIncome() {
		/*
		A = [P * (I - F - F2 - U1)] - HD - F1
			if the result is negative T = L

			//Take into account non-periodic payments such as one-time bonuses/vacation payout.
			//Must include bonus amount for pay period, as well as YTD bonus amount.
        */

		$A = 0;
		$P = $this->getAnnualPayPeriods();
		$I = $this->getGrossPayPeriodIncome();
		$F = 0;
		$F2 = 0;
		$U1 = $this->getUnionDuesAmount();
		$HD = 0;
		$F1 = 0;
		Debug::text('P: '. $P, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('I: '. $I, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('U1: '. $U1, __FILE__, __LINE__, __METHOD__, 10);

		//$A = ($P * ($I - $F - $F2 - $U1) ) - $HD - $F1;
		$A = bcsub( bcsub( bcmul($P, bcsub( bcsub( bcsub($I, $F), $F2 ), $U1 ) ), $HD ), $F1 );
		Debug::text('A: '. $A, __FILE__, __LINE__, __METHOD__, 10);

		return $A;
	}

	function getFederalBasicTax() {
		/*
		T3 = (R * A) - K - K1 - K2 - K3
			if the result is negative, $0;

        R = Federal tax rate applicable to annual taxable income
		*/

		$T3 = 0;
		$A = $this->getAnnualTaxableIncome();
		$R = $this->getData()->getFederalRate( $A );
		$K = $this->getData()->getFederalConstant( $A );
		$TC = $this->getFederalTotalClaimAmount();
		$K1 = bcmul( $this->getData()->getFederalLowestRate(), $TC );
		if ( $this->getEnableCPPAndEIDeduction() == TRUE ) {
			$K2 = $this->getFederalCPPAndEITaxCredit();
		} else {
			$K2 = 0; //Do the deduction at the Company Tax Deduction level instead.
		}

		$K3 = 0;

		if ( $this->getDate() >= strtotime('01-Jul-06') ) {
			$K4 = $this->getFederalEmploymentCredit();
		} else {
			$K4 = 0;
		}

		Debug::text('A: '. $A, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('R: '. $R, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K: '. $K, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('TC: '. $TC, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K1: '. $K1, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K2: '. $K2, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K3: '. $K3, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K4: '. $K4, __FILE__, __LINE__, __METHOD__, 10);

		//$T3 = ($R * $A) - $K - $K1 - $K2 - $K3 - $K4;
		$T3 = bcsub( bcsub( bcsub( bcsub( bcsub( bcmul($R, $A), $K ), $K1 ), $K2), $K3), $K4);

		if ($T3 < 0) {
			$T3 = 0;
		}

		Debug::text('T3: '. $T3, __FILE__, __LINE__, __METHOD__, 10);
		return $T3;
	}

	function getFederalTaxPayable() {
		//If employee is federal tax exempt, return 0 dollars.
		if ( $this->getFederalTaxExempt() == TRUE ) {
			Debug::text('Federal Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10);
			return 0;
		}

		/*
		T1= (T3 - LCF)*
			* If the result is negative, substitute $0

        LCF = The lesser of:
			i) $750 and
            ii) 15% of amount deducted for the year of accusistion.
		*/

		$T3 = $this->getFederalBasicTax();
		$LCF = 0; //Ignore 15% for now.

		//$T1 = ($T3 - $LCF);
		$T1 = bcsub($T3, $LCF);

		if ($T1 < 0) {
			$T1 = 0;
		}

		Debug::text('T1: '. $T1, __FILE__, __LINE__, __METHOD__, 10);
		return $T1;

	}

	function getFederalEmploymentCredit() {
		/*
		  K4 = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		$tmp1_K4 = bcmul( $this->getData()->getFederalLowestRate(), $this->getAnnualTaxableIncome() );
		$tmp2_K4 = bcmul( $this->getData()->getFederalLowestRate(), $this->getData()->getFederalEmploymentCreditAmount() );

		if ( $tmp2_K4 < $tmp1_K4 ) {
			$K4 = $tmp2_K4;
		} else {
			$K4 = $tmp1_K4;
		}

		Debug::text('K4: '. $K4, __FILE__, __LINE__, __METHOD__, 10);
		return $K4;
	}

	function getProvincialEmploymentCredit() {
		/*
		  K4P = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		$K4P = 0;
		if ( $this->getProvince() == 'YT' AND $this->getDate() >= strtotime('01-Jan-2013') ) { //Yukon only currently.
			$tmp1_K4P = bcmul( $this->getData()->getProvincialLowestRate(), $this->getAnnualTaxableIncome() );
			$tmp2_K4P = bcmul( $this->getData()->getProvincialLowestRate(), $this->getData()->getFederalEmploymentCreditAmount() ); //This matches the federal employment credit amount currently.

			if ( $tmp2_K4P < $tmp1_K4P ) {
				$K4P = $tmp2_K4P;
			} else {
				$K4P = $tmp1_K4P;
			}
		}

		Debug::text('K4P: '. $K4P, __FILE__, __LINE__, __METHOD__, 10);
		return $K4P;
	}

	function getFederalCPPTaxCredit() {
		/*
		  K2_CPP = [(0.16 * (P * C, max $1801.80))
		*/

		$C = $this->getEmployeeCPP();
		$P = $this->getAnnualPayPeriods();

		$P_times_C = bcmul($P, $C);
		if ($P_times_C > $this->getCPPEmployeeMaximumContribution() ) {
			$P_times_C = $this->getCPPEmployeeMaximumContribution();
		}
		Debug::text('P_times_C: '. $P_times_C, __FILE__, __LINE__, __METHOD__, 10);

		//$K2_CPP = ($this->getData()->getFederalLowestRate() * $P_times_C);
		$K2_CPP = bcmul( $this->getData()->getFederalLowestRate(), $P_times_C);

		/*
		$K2_CPP = ($this->getData()->getFederalLowestRate() * ($P * $C) );

		if ($K2_CPP > $this->cpp_employee_maximum_contribution ) {
			$K2_CPP = $this->cpp_employee_maximum_contribution;
		}
		*/

		Debug::text('K2_CPP: '. $K2_CPP, __FILE__, __LINE__, __METHOD__, 10);

		return $K2_CPP;
	}

	function getFederalEITaxCredit() {
		/*
		  K2_EI = [(0.16 * (P * C, max $819))
		*/

		$C = $this->getEmployeeEI();
		$P = $this->getAnnualPayPeriods();

		//$P_times_C = ($P * $C);
		$P_times_C = bcmul($P, $C);
		/*
		if ($P_times_C > $this->ei_employee_maximum_contribution) {
			$P_times_C = $this->ei_employee_maximum_contribution;
		}
		*/
		if ($P_times_C > $this->getEIEmployeeMaximumContribution() ) {
			$P_times_C = $this->getEIEmployeeMaximumContribution();
		}

		Debug::text('P_times_C: '. $P_times_C, __FILE__, __LINE__, __METHOD__, 10);

		//$K2_EI = ($this->getData()->getFederalLowestRate() * $P_times_C);
		$K2_EI = bcmul($this->getData()->getFederalLowestRate(), $P_times_C);

		/*
		$K2_CPP = ($this->getData()->getFederalLowestRate() * ($P * $C) );

		if ($K2_CPP > $this->cpp_employee_maximum_contribution ) {
			$K2_CPP = $this->cpp_employee_maximum_contribution;
		}
		*/
		Debug::text('K2_EI: '. $K2_EI, __FILE__, __LINE__, __METHOD__, 10);

		return $K2_EI;
	}

	function getFederalCPPAndEITaxCredit() {
		//$K2 = $this->getFederalCPPTaxCredit() + $this->getFederalEITaxCredit();
		$K2 = bcadd($this->getFederalCPPTaxCredit(), $this->getFederalEITaxCredit() );

		Debug::text('K2: '. $K2, __FILE__, __LINE__, __METHOD__, 10);

		return $K2;
	}

	function getProvincialTaxPayable() {
		//If employee is provincial tax exempt, return 0 dollars.
		if ( $this->getProvincialTaxExempt() == TRUE ) {
			Debug::text('Provincial Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10);
			return 0;
		}

		/*
		T2 = T4 + V1 + V2 - S - LCP
			if the result is negative, T2 = 0
		*/

		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$V2 = $this->getAdditionalProvincialSurtax();
		$S = $this->getProvincialTaxReduction();
		$LCP = 0;

		//$T2 = $T4 + $V1 + $V2 - $S - $LCP;
		$T2 = bcsub( bcsub( bcadd( bcadd($T4, $V1), $V2), $S), $LCP);

		if ($T2 < 0) {
			$T2 = 0;
		}

		Debug::text('T2: '. $T2, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('T4: '. $T4, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('V1: '. $V1, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('V2: '. $V2, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('S: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		return $T2;
	}

	function getProvincialBasicTax() {
		/*
		  	T4 = (V * A) - KP - K1P - K2P - K3P
		*/

		$A = $this->getAnnualTaxableIncome();
		$V = $this->getData()->getProvincialRate( $A );
		$KP = $this->getData()->getProvincialConstant( $A );
		$TCP = $this->getProvincialTotalClaimAmount();
		$K1P = bcmul( $this->getData()->getProvincialLowestRate(), $TCP );
		if ( $this->getEnableCPPAndEIDeduction() == TRUE ) {
			$K2P = $this->getProvincialCPPAndEITaxCredit();
		} else {
			$K2P = 0; //Use the Company Deduction Exclude funtionality instead.
		}
		$K3P = 0;
		$K4P = $this->getProvincialEmploymentCredit();

		Debug::text('A: '. $A, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('V: '. $V, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('KP: '. $KP, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('TCP: '. $TCP, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K1P: '. $K1P, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K2P: '. $K2P, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K3P: '. $K3P, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('K4P: '. $K4P, __FILE__, __LINE__, __METHOD__, 10);

		//$T4 = ($V * $A) - $KP - $K1P - $K2P - $K3P;
		$T4 = bcsub( bcsub( bcsub( bcsub( bcsub( bcmul($V, $A), $KP), $K1P ), $K2P ), $K3P ), $K4P );

		if ($T4 < 0) {
			$T4 = 0;
		}

		Debug::text('T4: '. $T4, __FILE__, __LINE__, __METHOD__, 10);

		return $T4;
	}

	function getProvincialTaxReduction() {

		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$Y = 0;
		$S = 0;

		Debug::text('No Specific Province: '. $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10);

		Debug::text('aS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text('bS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		return $S;
	}

	function getProvincialSurtax() {
		/*
			V1 =
			For Ontario
				Where T4 <= 4016
				V1 = 0

				Where T4 > 4016 <= 5065
				V1 = 0.20 * ( T4 - 4016 )

				Where T4 > 5065
				V1 = 0.20 * (T4 - 4016) + 0.36 * (T4 - 5065)

		*/

		$T4 = $this->getProvincialBasicTax();
		$V1 = 0;

		Debug::text('V1: '. $V1, __FILE__, __LINE__, __METHOD__, 10);

		return $V1;
	}

	function getAdditionalProvincialSurtax() {
		/*
			V2 =

			Where A < 20,000
			V2 = 0

			Where A >
		*/

		$A = $this->getAnnualTaxableIncome();
		$V2 = 0;

		Debug::text('V2: '. $V2, __FILE__, __LINE__, __METHOD__, 10);

		return $V2;
	}


	function getProvincialCPPTaxCredit() {
		/*
		  K2P_CPP = [(0.0605 * (P * C, max $1801.80))
			0.0605 is the lowest income tax rate
		*/

		$C = $this->getEmployeeCPP();
		$P = $this->getAnnualPayPeriods();

		//$P_times_C = ($P * $C);
		$P_times_C = bcmul($P, $C);
		if ($P_times_C > $this->getCPPEmployeeMaximumContribution()) {
			$P_times_C = $this->getCPPEmployeeMaximumContribution();
		}

		Debug::text('C: '. $C, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('P_times_C: '. $P_times_C, __FILE__, __LINE__, __METHOD__, 10);

		$K2P_CPP = bcmul( $this->getData()->getProvincialLowestRate(), $P_times_C );

		Debug::text('K2P_CPP: '. $K2P_CPP, __FILE__, __LINE__, __METHOD__, 10);

		return $K2P_CPP;
	}

	function getProvincialEITaxCredit() {
		/*
		  K2P_EI = [(0.0605 * (P * C, max $1801.80))
			0.0605 is the lowest income tax rate
		*/

		$C = $this->getEmployeeEI();
		$P = $this->getAnnualPayPeriods();

		//$P_times_C = ($P * $C);
		$P_times_C = bcmul($P, $C);
		if ($P_times_C > $this->getEIEmployeeMaximumContribution() ) {
			$P_times_C = $this->getEIEmployeeMaximumContribution();
		}
		Debug::text('C: '. $C, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('P_times_C: '. $P_times_C, __FILE__, __LINE__, __METHOD__, 10);

		$K2P_EI = bcmul( $this->getData()->getProvincialLowestRate(), $P_times_C );

		Debug::text('K2P_EI: '. $K2P_EI, __FILE__, __LINE__, __METHOD__, 10);

		return $K2P_EI;
	}

	function getProvincialCPPAndEITaxCredit() {
		//$K2P = $this->getProvincialCPPTaxCredit() + $this->getProvincialEITaxCredit();
		$K2P = bcadd( $this->getProvincialCPPTaxCredit(), $this->getProvincialEITaxCredit() );

		Debug::text('K2P: '. $K2P, __FILE__, __LINE__, __METHOD__, 10);

		return $K2P;
	}

	function getEmployeeCPP() {
		/*
			C = The lesser of
				i) $1801.80 - D; and
				ii) 0.495 * [I - (3500 / P)
					if the result is negative, C = 0
		*/

		//If employee is CPP exempt, return 0 dollars.
		if ( $this->getCPPExempt() == TRUE ) {
			return 0;
		}

		$D = $this->getYearToDateCPPContribution();
		$P = $this->getAnnualPayPeriods();
		$I = $this->getGrossPayPeriodIncome();

		Debug::text('D: '. $D, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('P: '. $P, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('I: '. $I, __FILE__, __LINE__, __METHOD__, 10);

		//$tmp1_C = $this->cpp_employee_maximum_contribution - $D;
		//$tmp1_C = $this->getCPPEmployeeMaximumContribution() - $D;
		$tmp1_C = bcsub( $this->getCPPEmployeeMaximumContribution(), $D);

		//$tmp2_C = $this->cpp_employee_rate * ($I - ($this->cpp_basic_exemption / $P) );
		//$tmp2_C = $this->getCPPEmployeeRate() * ($I - ($this->getCPPBasicExemption() / $P) );
		$tmp2_C = bcmul( $this->getCPPEmployeeRate(), bcsub($I, bcdiv($this->getCPPBasicExemption(), $P) ) );

		Debug::text('Tmp1_C: '. $tmp1_C, __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Tmp2_C: '. $tmp2_C, __FILE__, __LINE__, __METHOD__, 10);

		if ($tmp1_C < $tmp2_C) {
			$C = $tmp1_C;
		} else {
			$C = $tmp2_C;
		}

		if ($C < 0) {
			$C = 0;
		}

		Debug::text('C: '. $C, __FILE__, __LINE__, __METHOD__, 10);

		return $C;
	}

	function getEmployerCPP() {
		//EmployerCPP is the same as EmployeeCPP
		return $this->getEmployeeCPP();
	}

	function getEmployeeEI() {
		/*
			EI = the lesser of
				i) 819 - D; and
                ii) 0.021 * I, maximum of 819
					round the resulting amount in ii) to the nearest $0.01
		*/

		//If employee is EI exempt, return 0 dollars.
		if ( $this->getEIExempt() == TRUE ) {
			return 0;
		}

		$D = $this->getYearToDateEIContribution();
		$I = $this->getGrossPayPeriodIncome();
		/*
		$tmp1_EI = $this->ei_employee_maximum_contribution - $D;
		$tmp2_EI = $this->ei_employee_rate * $I;
		if ($tmp2_EI > $this->ei_employee_maximum_contribution) {
			$tmp2_EI = $this->ei_employee_maximum_contribution;
		}
		*/

		Debug::text('Employee EI Rate: '. $this->getEIEmployeeRate() .' I: '. $I, __FILE__, __LINE__, __METHOD__, 10);
		//$tmp1_EI = $this->getEIEmployeeMaximumContribution() - $D;
		$tmp1_EI = bcsub( $this->getEIEmployeeMaximumContribution(), $D);
		//$tmp2_EI = $this->getEIEmployeeRate() * $I;
		$tmp2_EI = bcmul( $this->getEIEmployeeRate(), $I);
		if ($tmp2_EI > $this->getEIEmployeeMaximumContribution() ) {
			$tmp2_EI = $this->getEIEmployeeMaximumContribution();
		}

		if ($tmp1_EI < $tmp2_EI) {
			$EI = $tmp1_EI;
		} else {
			$EI = $tmp2_EI;
		}

		if ($EI < 0) {
			$EI = 0;
		}

		Debug::text('Employee EI: '. $EI, __FILE__, __LINE__, __METHOD__, 10);

		return $EI;
	}

	function getEmployerEI() {
		//$EI = $this->getEmployeeEI() * $this->ei_employer_rate;
		//$EI = $this->getEmployeeEI() * $this->getEIEmployerRate();
		$EI = bcmul( $this->getEmployeeEI(), $this->getEIEmployerRate() );

		Debug::text('Employer EI: '. $EI .' Rate: '. $this->getEIEmployerRate(), __FILE__, __LINE__, __METHOD__, 10);

		return $EI;
	}

	function getPayPeriodEmployeeTotalDeductions() {
		return bcadd( bcadd($this->getPayPeriodTaxDeductions(), $this->getEmployeeCPP() ), $this->getEmployeeEI() );
	}

	function getPayPeriodEmployeeNetPay() {
		return bcsub( $this->getGrossPayPeriodIncome(), $this->getPayPeriodEmployeeTotalDeductions() );
	}

	function getEmployerWCB() {
		if ( $this->getWCBRate() != FALSE AND $this->getWCBRate() > 0 ) {
			//$WCB = $this->getGrossPayPeriodIncome() * $this->getWCBRate();
			$WCB = bcmul( $this->getGrossPayPeriodIncome(), $this->getWCBRate() );

			Debug::text('Employer WCB: '. $WCB .' Rate: '. $this->getWCBRate(), __FILE__, __LINE__, __METHOD__, 10);

			return $WCB;
		}

		return FALSE;
	}

	/*
		Use this to get all useful values.
	*/
	function getArray() {

		$array = array(
						'gross_pay' => $this->getGrossPayPeriodIncome(),
						'federal_tax' => $this->getFederalPayPeriodDeductions(),
						'provincial_tax' => $this->getProvincialPayPeriodDeductions(),
						'total_tax' => $this->getPayPeriodTaxDeductions(),
						'employee_cpp' => $this->getEmployeeCPP(),
						'employer_cpp' => $this->getEmployerCPP(),
						'employee_ei' => $this->getEmployeeEI(),
						'employer_ei' => $this->getEmployerEI(),
						'employer_wcb' => $this->getEmployerWCB(),
						'federal_additional_deduction' => $this->getFederalAdditionalDeduction(),
						//'net_pay' => $this->getPayPeriodNetPay()
						);

		Debug::Arr($array, 'Deductions Array:', __FILE__, __LINE__, __METHOD__, 10);

		return $array;
	}
}
?>