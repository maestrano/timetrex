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
 * $Id: BC.class.php 8720 2012-12-29 01:06:58Z ipso $
 * $Date: 2012-12-28 17:06:58 -0800 (Fri, 28 Dec 2012) $
 */

/**
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_BC extends PayrollDeduction_CA {
	function getProvincialTaxReduction() {

		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$Y = 0;
		$S = 0;

		Debug::text('BC Specific - Province: '. $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10);
		$tax_reduction_data = $this->getProvincialTaxReductionData( $this->getDate(), $this->getProvince() );
		if ( is_array($tax_reduction_data) ) {
			if ( $A <= $tax_reduction_data['income1'] ) {
				Debug::text('S: Annual Income less than: '. $tax_reduction_data['income1'], __FILE__, __LINE__, __METHOD__, 10);
				if ( $T4 > $tax_reduction_data['amount'] ) {
					$S = $tax_reduction_data['amount'];
				} else {
					$S = $T4;
				}
			} elseif ( $A > $tax_reduction_data['income1'] AND $A <= $tax_reduction_data['income2'] ) {
				Debug::text('S: Annual Income less than '. $tax_reduction_data['income2'], __FILE__, __LINE__, __METHOD__, 10);

				$tmp_S = bcsub( $tax_reduction_data['amount'], bcmul( bcsub( $A, $tax_reduction_data['income1'] ), $tax_reduction_data['rate'] ) );
				Debug::text('Tmp_S: '. $tmp_S, __FILE__, __LINE__, __METHOD__, 10);

				if ( $T4 > $tmp_S ) {
					$S = $tmp_S;
				} else {
					$S = $T4;
				}
				unset($tmp_S);
			}
		}
		Debug::text('aS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text('bS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		return $S;
	}
}
?>
