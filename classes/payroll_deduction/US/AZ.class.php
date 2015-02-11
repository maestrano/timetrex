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
class PayrollDeduction_US_AZ extends PayrollDeduction_US {

	function getStatePayPeriodDeductions() {
		return bcdiv( $this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateTaxPayable() {
		//Arizona is a percent of federal tax rate.
		//However after 01-Jul-10 it changed to a straight percent of gross.
		$annual_income = $this->getAnnualTaxableIncome();

		$rate = $this->getUserValue1();
		Debug::text('Raw Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);

		//Because of the change from a percent of federal rate to a gross rate,
		//add some checks so if an employee's amount isn't changed we default to the closest rate.
		if ( $rate >= 39.5 ) {
			$rate = 5.1;
		} elseif ( $rate >= 33.1 ) {
			$rate = 4.2;
		} elseif ( $rate >= 26.7 ) {
			$rate = 3.6;
		} elseif ( $rate >= 24.5 ) {
			$rate = 2.7;
		} elseif ( $rate >= 20.3 ) {
			$rate = 1.8;
		} elseif ( $rate >= 10.7 ) {
			$rate = 1.3;
		}
		Debug::text(' Adjusted Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
		$retval = bcmul( $annual_income, bcdiv( $rate, 100) );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
