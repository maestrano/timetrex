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
 * @package PayrollDeduction
 */
class PayrollDeduction_Base {
	var $data = array();

	function setCompany($company_id) {
		$this->data['company_id'] = $company_id;

		return TRUE;
	}
	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}

	function setUser($user_id) {
		$this->data['user_id'] = $user_id;

		return TRUE;
	}
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}

	function setCountry($country) {
		$this->data['country'] = strtoupper(trim($country));

		return TRUE;
	}
	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}

	function setProvince($province) {
		$this->data['province'] = strtoupper(trim($province));

		return TRUE;
	}
	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}

	function setDistrict($district) {
		$this->data['district'] = strtoupper(trim($district));

		return TRUE;
	}
	function getDistrict() {
		if ( isset($this->data['district']) ) {
			return $this->data['district'];
		}

		return FALSE;
	}

	//
	// Generic
	//

	function setUserValue1($value) {
		$this->data['user_value1'] = $value;

		return TRUE;
	}
	function getUserValue1() {
		if ( isset($this->data['user_value1']) ) {
			return $this->data['user_value1'];
		}

		return FALSE;
	}

	function setUserValue2($value) {
		$this->data['user_value2'] = $value;

		return TRUE;
	}
	function getUserValue2() {
		if ( isset($this->data['user_value2']) ) {
			return $this->data['user_value2'];
		}

		return FALSE;
	}

	function setUserValue3($value) {
		$this->data['user_value3'] = $value;

		return TRUE;
	}
	function getUserValue3() {
		if ( isset($this->data['user_value3']) ) {
			return $this->data['user_value3'];
		}

		return FALSE;
	}

	function setUserValue4($value) {
		$this->data['user_value4'] = $value;

		return TRUE;
	}
	function getUserValue4() {
		if ( isset($this->data['user_value4']) ) {
			return $this->data['user_value4'];
		}

		return FALSE;
	}

	function setDate($epoch) {
		$this->data['date'] = $epoch;

		return TRUE;
	}
	function getDate() {
		if ( isset($this->data['date']) ) {
			return $this->data['date'];
		}

		return FALSE;
	}

	function setAnnualPayPeriods($value) {
		$this->data['annual_pay_periods'] = $value;

		return TRUE;
	}
	function getAnnualPayPeriods() {
		if ( isset($this->data['annual_pay_periods']) ) {
			return $this->data['annual_pay_periods'];
		}

		return FALSE;
	}

	function getCountryPrimaryCurrency() {
		if ( isset($this->country_primary_currency) AND $this->country_primary_currency != '' ) {
			return $this->country_primary_currency;
		}

		return FALSE;
	}

	function getCountryPrimaryCurrencyID() {
		$iso_code = $this->getCountryPrimaryCurrency(); //ISO Code

		if ( $iso_code != '' AND is_numeric( $this->getCompany() ) ) {
			$clf = new CurrencyListFactory();
			$clf->getByCompanyIdAndISOCode( $this->getCompany(), $iso_code );
			if ( $clf->getRecordCount() > 0 ) {
				$currency_id = $clf->getCurrent()->getId();
				//Debug::Text('Country Primary Currency ID: '. $currency_id , __FILE__, __LINE__, __METHOD__, 10 );
				return $currency_id;
			}
		}

		Debug::Text('Country Primary Currency does not exist: '. $iso_code, __FILE__, __LINE__, __METHOD__, 10 );
		return FALSE;
	}

	//Set the user currency for calculations
	function setUserCurrency( $currency_id ) {
		//Debug::Text('Settitng currency for calculate income tax: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10 );

		$this->data['user_currency_id'] = $currency_id;

		return TRUE;
	}

	//Get the user currency for calculations
	function getUserCurrency() {
		if ( isset($this->data['user_currency_id']) ) {
			//Debug::Text('Currency income: '. $this->data['user_currency_id'], __FILE__, __LINE__, __METHOD__, 10);

			return $this->data['user_currency_id'];
		}

		//If no currency is set, return the country primary currency, so no conversion takes place.
		return FALSE;
	}

	function setGrossPayPeriodIncome($income) {
		//A = Annual Taxable Income
		//Debug::text('Setting gross pay period income: '. $income, __FILE__, __LINE__, __METHOD__, 10);
		$income = $this->convertToCountryCurrency( $income );
		//Debug::text('Setting converted gross pay period income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		$this->data['gross_pay_period_income'] = $income;

		return TRUE;
	}

	function getGrossPayPeriodIncome() {
		if ( isset($this->data['gross_pay_period_income']) ) {
			Debug::text('Gross Pay Period Income: I: '. $this->data['gross_pay_period_income'], __FILE__, __LINE__, __METHOD__, 10);

			return $this->data['gross_pay_period_income'];
		}

		return FALSE;
	}

	function setYearToDateGrossIncome($income) {
		$income = $this->convertToCountryCurrency( $income );

		$this->data['gross_ytd_income'] = $income;

		return TRUE;
	}

	function getYearToDateGrossIncome() {
		if ( isset($this->data['gross_ytd_income']) ) {
			Debug::text('YTD Gross Income: I: '. $this->data['gross_ytd_income'], __FILE__, __LINE__, __METHOD__, 10);

			return $this->data['gross_ytd_income'];
		}

		return FALSE;
	}

	//This function convert '$amount' from the user currency, to the country currency for calculations
	function convertToCountryCurrency($amount) {
		$user_currency_id = $this->getUserCurrency();
		$country_currency_id = $this->getCountryPrimaryCurrencyID();

		if ( $user_currency_id !== FALSE AND $country_currency_id !== FALSE ) {
			$retval = CurrencyFactory::convertCurrency( $this->getUserCurrency(), $this->getCountryPrimaryCurrencyID(), $amount);
		} else {
			//Conversion failed, return original amount.
			$retval = $amount;
		}

		return $retval;
	}

	//This function convert '$amount' from the country currency, to the user currency.
	function convertToUserCurrency($amount) {
		$user_currency_id = $this->getUserCurrency();
		$country_currency_id = $this->getCountryPrimaryCurrencyID();

		if ( $user_currency_id !== FALSE AND $country_currency_id !== FALSE ) {
			$retval =  CurrencyFactory::convertCurrency( $this->getCountryPrimaryCurrencyID(), $this->getUserCurrency(), $amount);
		} else {
			$retval = $amount;
		}

		return $retval;
	}

	protected function getDataFromRateArray($epoch, $arr) {
		if ( !is_array($arr) ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		krsort($arr);
		foreach( $arr as $date => $val ) {
			if ( $epoch >= $date ) {
				return $val;
			}
		}

		return FALSE;
	}

}
?>
