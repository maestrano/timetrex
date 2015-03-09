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
 * @package PayrollDeduction\CR
 */
class PayrollDeduction_CR_Data extends PayrollDeduction_Base {
	var $db = NULL;
	var $income_tax_rates = array();
	var $table = 'income_tax_rate_cr';
	var $country_primary_currency = 'CRC';

	var $federal_allowance = array(
									1159678800 => 10560.00, //01-Oct-07
									1191214800 => 11520.00  //01-Oct-07
								);

	var $federal_filing = array(
									1159678800 => 15720.00, //01-Oct-07
									1191214800 => 17040.00  //01-Oct-07
								);

	function __construct() {
		global $db;

		$this->db = $db;

		return TRUE;
	}

	function getData() {
		global $cache;

		$country = $this->getCountry();

		$epoch = $this->getDate();
		$federal_status = $this->getFederalFilingStatus();
		if ( $federal_status == '' ) {
			$federal_status = 10;
		}

		if ($epoch == NULL OR $epoch == ''){
			$epoch = TTDate::getTime();
		}

		$cache_id = $country.$epoch.$federal_status;

		if ( is_string( $cache->get($cache_id, $this->table ) ) ) {
			$this->income_tax_rates = unserialize( $cache->get($cache_id, $this->table ) );
		} else {
			$this->income_tax_rates = FALSE;
		}


		if ( $this->income_tax_rates === FALSE ) {
			$query = 'select country,state,district,status,income,rate,constant,effective_date
						from '. $this->table .'
						where
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
													ORDER BY effective_date DESC
													LIMIT 1)
								)
							AND
							( country = '. $this->db->qstr($country).')
						ORDER BY effective_date desc, income asc, rate asc
					';

			//Debug::text('Query: '. $query , __FILE__, __LINE__, __METHOD__, 10);
			try {
				$rs = $this->db->Execute($query);
			} catch (Exception $e) {
				throw new DBError($e);
			}

			$rs = $rs->GetRows();

			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;
			foreach($rs as $key => $arr) {

				$type = 'federal';

				$this->income_tax_rates[$type][] = array('prev_income' => trim($prev_income), 'income' => trim($arr['income']), 'prev_rate' => ( bcdiv( trim($prev_rate), 100 ) ), 'rate' => ( bcdiv( trim($arr['rate']), 100 ) ), 'prev_constant' => trim($prev_constant), 'constant' => trim($arr['constant']) );

				$prev_income = $arr['income'];
				$prev_rate = $arr['rate'];
				$prev_constant = $arr['constant'];
			}

			if ( isset($arr) ) {
				Debug::text('bUsing values from: '. TTDate::getDate('DATE+TIME', $arr['effective_date']), __FILE__, __LINE__, __METHOD__, 10);
			}

			//var_dump($this->income_tax_rates);
			$cache->save(serialize($this->income_tax_rates), $cache_id, $this->table );
		}

		return $this;
	}

	function getFederalTaxTable($income) {
		$arr = $this->income_tax_rates['federal'];

		//Debug::Arr($arr, 'Federal tax table: ', __FILE__, __LINE__, __METHOD__, 10);
		return $arr;
	}

	function getFederalAllowanceAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_allowance);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}

	function getFederalFilingAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_filing);

		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}

}
?>
