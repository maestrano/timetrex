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
class PayrollDeduction_CA_Data extends PayrollDeduction_Base {
	var $db = NULL;
	var $income_tax_rates = array();
	var $table = 'income_tax_rate';
	var $country_primary_currency = 'CAD';

	/*
		Claim Code Basic Amounts
	*/
	var $basic_claim_code_options = array(
							1420099200 => array( //01-Jan-2015:
										'CA' => 11327, //Federal
										'BC' => 9938,
										'AB' => 18214,
										'SK' => 15639,
										'MB' => 9134,
										'QC' => 0,
										'ON' => 9863,
										'NL' => 8767,
										'NB' => 9633,
										'NS' => 8481,
										'PE' => 7708,
										'NT' => 13900,
										'YT' => 11327,
										'NU' => 12781,
										),
							1388563200 => array( //01-Jan-2014:
										'CA' => 11138, //Federal
										'BC' => 9869,
										'AB' => 17787,
										'SK' => 15378,
										'MB' => 9134,
										'QC' => 0,
										'ON' => 9670,
										'NL' => 8578,
										'NB' => 9472,
										'NS' => 8481,
										'PE' => 7708,
										'NT' => 13668,
										'YT' => 11138,
										'NU' => 12567,
										),
							1357027200 => array( //01-Jan-2013:
										'CA' => 11038, //Federal
										'BC' => 10276,
										'AB' => 17593,
										'SK' => 15241,
										'MB' => 8884,
										'QC' => 0,
										'ON' => 9574,
										'NL' => 8451,
										'NB' => 9388,
										'NS' => 8481,
										'PE' => 7708,
										'NT' => 13546,
										'YT' => 11038,
										'NU' => 12455,
										),
							1325404800 => array( //01-Jan-2012:
										'CA' => 10822, //Federal
										'BC' => 11354,
										'AB' => 17282,
										'SK' => 14942,
										'MB' => 8634,
										'QC' => 0,
										'ON' => 9405,
										'NL' => 8237,
										'NB' => 9203,
										'NS' => 8481,
										'PE' => 7708,
										'NT' => 13280,
										'YT' => 10822,
										'NU' => 12211,
										),
							1309503600 => array( //01-Jul-2011: Some of these are only changed for the last 6mths in the year.
										'CA' => 10527, //Federal
										'BC' => 11088,
										'AB' => 16977,
										'SK' => 14535,
										'MB' => 8634,
										'QC' => 0,
										'ON' => 9104,
										'NL' => 7989,
										'NB' => 8953,
										'NS' => 8731,
										'PE' => 7708,
										'NT' => 12919,
										'YT' => 10527,
										'NU' => 11878,
										),
							1293868800 => array( //01-Jan-2011
										'CA' => 10527, //Federal
										'BC' => 11088,
										'AB' => 16977,
										'SK' => 13535,
										'MB' => 8134,
										'QC' => 0,
										'ON' => 9104,
										'NL' => 7989,
										'NB' => 8953,
										'NS' => 8231,
										'PE' => 7708,
										'NT' => 12919,
										'YT' => 10527,
										'NU' => 11878,
										),
							1262332800 => array( //01-Jan-2010
										'CA' => 10382, //Federal
										'BC' => 11000,
										'AB' => 16825,
										'SK' => 13348,
										'MB' => 8134,
										'QC' => 0,
										'ON' => 8943,
										'NL' => 7833,
										'NB' => 8777,
										'NS' => 8231,
										'PE' => 7708,
										'NT' => 12740,
										'YT' => 10382,
										'NU' => 11714,
										),
							1238569200 => array( //01-Apr-09
										'CA' => 10375, //Federal
										'BC' => 9373,
										'AB' => 16775,
										'SK' => 13269,
										'MB' => 8134,
										'QC' => 0,
										'ON' => 8881,
										'NL' => 7778,
										'NB' => 8134,
										'NS' => 7981,
										'PE' => 7708,
										'NT' => 12664,
										'YT' => 10375,
										'NU' => 11644,
										),
							1230796800 => array( //01-Jan-09
										'CA' => 10100, //Federal
										'BC' => 9373,
										'AB' => 16775,
										'SK' => 13269,
										'MB' => 8134,
										'QC' => 0,
										'ON' => 8881,
										'NL' => 7778,
										'NB' => 8134,
										'NS' => 7981,
										'PE' => 7708,
										'NT' => 12664,
										'YT' => 10100,
										'NU' => 11644,
										),
							1199174400 => array( //01-Jan-08
										'CA' => 9600, //Federal
										'BC' => 9189,
										'AB' => 16161,
										'SK' => 8945,
										'MB' => 8034,
										'QC' => 0,
										'ON' => 8681,
										'NL' => 7566,
										'NB' => 8395,
										'NS' => 7731,
										'PE' => 7708,
										'NT' => 12355,
										'YT' => 9600,
										'NU' => 11360,
										),
							1183273200 => array( //01-Jul-07
										'CA' => 8929, //Federal
										'BC' => 9027,
										'AB' => 15435,
										'SK' => 8778,
										'MB' => 7834,
										'QC' => 0,
										'ON' => 8553,
										'NL' => 7558,
										'NB' => 8239,
										'NS' => 7481,
										'PE' => 7708,
										'NT' => 12125,
										'YT' => 8929,
										'NU' => 11149,
										),
							1167638400 => array( //01-Jan-07
										'CA' => 8929, //Federal
										'BC' => 9027,
										'AB' => 15435,
										'SK' => 8778,
										'MB' => 7834,
										'QC' => 0,
										'ON' => 8553,
										'NL' => 7410,
										'NB' => 8239,
										'NS' => 7481,
										'PE' => 7412,
										'NT' => 12125,
										'YT' => 8929,
										'NU' => 11149,
										),
							1151737200 => array( //01-Jul-06
										'CA' => 8639, //Federal
										'BC' => 8858,
										'AB' => 14999,
										'SK' => 8589,
										'MB' => 7734,
										'QC' => 0,
										'ON' => 8377,
										'NL' => 7410,
										'NB' => 8061,
										'NS' => 7231,
										'PE' => 7412,
										'NT' => 11864,
										'YT' => 8328,
										'NU' => 10909,
										),
							1136102400 => array( //01-Jan-06
										'CA' => 9039, //Federal
										'BC' => 8858,
										'AB' => 14799,
										'SK' => 8589,
										'MB' => 7734,
										'QC' => 0,
										'ON' => 8377,
										'NL' => 7410,
										'NB' => 8061,
										'NS' => 7231,
										'PE' => 7412,
										'NT' => 11864,
										'YT' => 8328,
										'NU' => 10909,
										),
									);

	/*
		CPP settings
	*/
	var $cpp_options = array(
							1420099200 => array( //2015
										'maximum_pensionable_earnings' => 53600,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2479.95
										),
							1388563200 => array( //2014
										'maximum_pensionable_earnings' => 52500,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2425.50
										),
							1357027200 => array( //2013
										'maximum_pensionable_earnings' => 51100,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2356.20
										),
							1325404800 => array( //2012
										'maximum_pensionable_earnings' => 50100,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2306.70
										),
							1293868800 => array( //2011
										'maximum_pensionable_earnings' => 48300,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2217.60
										),
							1262332800 => array( //2010
										'maximum_pensionable_earnings' => 47200,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2163.15
										),
							1230796800 => array( //2009
										'maximum_pensionable_earnings' => 46300,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2118.60
										),
							1199174400 => array( //2008
										'maximum_pensionable_earnings' => 44900,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 2049.30
										),
							1167638400 => array( //2007
										'maximum_pensionable_earnings' => 43700,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 1989.90
										),
							1136102400 => array( //2006
										'maximum_pensionable_earnings' => 42100,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 1910.70
										),
							1104566400 => array( //2005
										'maximum_pensionable_earnings' => 41100,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 1861.20
										),
							1072944000 => array( //2004
										'maximum_pensionable_earnings' => 40500,
										'basic_exemption' => 3500,
										'employee_rate' => 0.0495,
										'employee_maximum_contribution' => 1831.50
										),
							);

	/*
		EI settings
	*/
	var $ei_options = array(
							1420099200 => array( //2015
										'maximum_insurable_earnings' => 49500,
										'employee_rate' => 0.0188,
										'employee_maximum_contribution' => 930.60,
										'employer_rate' => 1.4
										),
							1388563200 => array( //2014
										'maximum_insurable_earnings' => 48600,
										'employee_rate' => 0.0188,
										'employee_maximum_contribution' => 913.68,
										'employer_rate' => 1.4
										),
							1357027200 => array( //2013
										'maximum_insurable_earnings' => 47400,
										'employee_rate' => 0.0188,
										'employee_maximum_contribution' => 891.12,
										'employer_rate' => 1.4
										),
							1325404800 => array( //2012
										'maximum_insurable_earnings' => 45900,
										'employee_rate' => 0.0183,
										'employee_maximum_contribution' => 839.97,
										'employer_rate' => 1.4
										),
							1293868800 => array( //2011
										'maximum_insurable_earnings' => 44200,
										'employee_rate' => 0.0178,
										'employee_maximum_contribution' => 786.76,
										'employer_rate' => 1.4
										),
							1262332800 => array( //2010
										'maximum_insurable_earnings' => 43200,
										'employee_rate' => 0.0173,
										'employee_maximum_contribution' => 747.36,
										'employer_rate' => 1.4
										),
							1230796800 => array( //2009
										'maximum_insurable_earnings' => 42300,
										'employee_rate' => 0.0173,
										'employee_maximum_contribution' => 731.79,
										'employer_rate' => 1.4
										),
							1199174400 => array( //2008
										'maximum_insurable_earnings' => 41100,
										'employee_rate' => 0.0173,
										'employee_maximum_contribution' => 711.03,
										'employer_rate' => 1.4
										),
							1167638400 => array( //2007
										'maximum_insurable_earnings' => 40000,
										'employee_rate' => 0.0180,
										'employee_maximum_contribution' => 720.00,
										'employer_rate' => 1.4
										),
							1136102400 => array( //2006
										'maximum_insurable_earnings' => 39000,
										'employee_rate' => 0.0187,
										'employee_maximum_contribution' => 729.30,
										'employer_rate' => 1.4
										),
							1104566400 => array( //2005
										'maximum_insurable_earnings' => 39000,
										'employee_rate' => 0.0195,
										'employee_maximum_contribution' => 760.50,
										'employer_rate' => 1.4
										),
							1072944000 => array( //2004
										'maximum_insurable_earnings' => 39900,
										'employee_rate' => 0.0198,
										'employee_maximum_contribution' => 722.20,
										'employer_rate' => 1.4
										),
							);

	/*
		Federal employment credit
	*/
	var $federal_employment_credit_options = array(
							1420099200 => array( //2015
										'credit' => 1146,
										),
							1388563200 => array( //2014
										'credit' => 1127,
										),
							1357027200 => array( //2013
										'credit' => 1117,
										),
							1325404800 => array( //2012
										'credit' => 1095,
										),
							1293868800 => array( //2011
										'credit' => 1065,
										),
							1262332800 => array( //2010
										'credit' => 1051,
										),
							1230796800 => array( //2009
										'credit' => 1044,
										),
							1199174400 => array( //2008
										'credit' => 1019,
										),
							1167638400 => array( //2007
										'credit' => 1000,
										),
							1136102400 => array( //2006
										'credit' => 500,
										),
							);

	/*
		Provincial tax reduction
	*/
	var $provincial_tax_reduction_options = array(
							'BC' => array(
									1435734000 => array( //2015 (Jul 1)
														'income1' => 19673,
														'income2' => 31567.74,
														'amount' => 452,
														'rate' => 0.038,
														),
									1420099200 => array( //2015
														'income1' => 18327,
														'income2' => 31202,
														'amount' => 412,
														'rate' => 0.032,
														),
									1388563200 => array( //2014
														'income1' => 18200,
														'income2' => 30981.25,
														'amount' => 409,
														'rate' => 0.032,
														),
									1357027200 => array( //2013
														'income1' => 18181,
														'income2' => 30962.25,
														'amount' => 409,
														'rate' => 0.032,
														),
									1325404800 => array( //2012
														'income1' => 17913,
														'income2' => 30506.75,
														'amount' => 403,
														'rate' => 0.032,
														),
									1293868800 => array( //2011
														'income1' => 17493,
														'income2' => 29805.50,
														'amount' => 394,
														'rate' => 0.032,
												),
									1262332800 => array( //2010
														'income1' => 17354,
														'income2' => 29541.50,
														'amount' => 390,
														'rate' => 0.032,
												),
									1230796800 => array( //2009
														'income1' => 17285,
														'income2' => 29441.25,
														'amount' => 389,
														'rate' => 0.032,
												),
									1199174400 => array( //2008
														'income1' => 16946,
														'income2' => 28852.25,
														'amount' => 381,
														'rate' => 0.032,
												),
									1183273200 => array( //2007 (July)
														'income1' => 16646,
														'income2' => 28364.75,
														'amount' => 375,
														'rate' => 0.032,
												),
									1167638400	=> array( //2007 (Jan)
														'income1' => 16646,
														'income2' => 27062.67,
														'amount' => 375,
														'rate' => 0.032,
												),
									1136102400 => array( //2006
														'income1' => 16336,
														'income2' => 26558.22,
														'amount' => 368,
														'rate' => 0.032,
												),
									1104566400 => array( //2005
														'income1' => 16000,
														'income2' => 26000,
														'amount' => 360,
														'rate' => 0.032,
												),
									),
							'ON' => array(
									1420099200 => array( //2015
														'amount' => 228,
														),
									1388563200 => array( //2014
														'amount' => 223,
														),
									1357027200 => array( //2013
														'amount' => 221,
														),
									1325404800 => array( //2012
														'amount' => 217,
														),
									1293868800 => array( //2011
														'amount' => 210,
												),
									1262332800 => array( //2010
														'amount' => 206,
												),
									1230796800 => array( //2009
														'amount' => 205,
												),
									1199174400 => array( //2008
														'amount' => 201,
												),
									1167638400	=> array( //2007
														'amount' => 198,
												),
									1136102400 => array( //2006
														'amount' => 194,
												),
									),
							);

	/*
		Provincial surtax
	*/
	var $provincial_surtax_options = array(
							'ON' => array(
									1420099200 => array( //2015
														'income1' => 4418,
														'income2' => 5654,
														'rate1' => 0.20,
														'rate2' => 0.36,
														),
									1388563200 => array( //2014
														'income1' => 4331,
														'income2' => 5543,
														'rate1' => 0.20,
														'rate2' => 0.36,
														),
									1357027200 => array( //2013
														'income1' => 4289,
														'income2' => 5489,
														'rate1' => 0.20,
														'rate2' => 0.36,
														),
									1325404800 => array( //2012
														'income1' => 4213,
														'income2' => 5392,
														'rate1' => 0.20,
														'rate2' => 0.36,
														),
									1293868800 => array( //2011
														'income1' => 4078,
														'income2' => 5219,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									1262332800 => array( //2010
														'income1' => 4006,
														'income2' => 5127,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									1230796800 => array( //2009
														'income1' => 4257,
														'income2' => 5370,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									1199174400 => array( //2008
														'income1' => 4162,
														'income2' => 5249,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									1167638400	=> array( //2007
														'income1' => 4100,
														'income2' => 5172,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									1136102400 => array( //2006
														'income1' => 4016,
														'income2' => 5065,
														'rate1' => 0.20,
														'rate2' => 0.36,
												),
									),
							);

	function __construct() {
		global $db;

		$this->db = $db;

		return TRUE;
	}

	/*
		Claim Code Functions
	*/
	function getBasicClaimCodeData( $date ) {
		foreach( $this->basic_claim_code_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return FALSE;
	}
	function getBasicFederalClaimCodeAmount( $date = FALSE ) {
		if ( $date == '' ) {
			$date = $this->getDate();
		}
		
		$data = $this->getBasicClaimCodeData( $date );

		if ( isset($data['CA']) ) {
			return $data['CA'];
		}

		return FALSE;
	}

	function getBasicProvinceClaimCodeAmount( $date = FALSE ) {
		if ( $date == '' ) {
			$date = $this->getDate();
		}

		$data = $this->getBasicClaimCodeData( $date );

		if ( isset($data[$this->getProvince()]) ) {
			return $data[$this->getProvince()];
		}

		return FALSE;
	}

	/*
		Provincial tax/surtax reduction functions
	*/
	function getProvincialTaxReductionData( $date, $province ) {
		if ( isset($this->provincial_tax_reduction_options[$province]) ) {
			foreach( $this->provincial_tax_reduction_options[$province] as $effective_date => $data ) {
				if ( $date >= $effective_date ) {
					return $data;
				}
			}
		}

		return FALSE;
	}
	function getProvincialSurTaxData( $date, $province ) {
		if ( isset($this->provincial_surtax_options[$province]) ) {
			foreach( $this->provincial_surtax_options[$province] as $effective_date => $data ) {
				if ( $date >= $effective_date ) {
					return $data;
				}
			}
		}

		return FALSE;
	}

	/*
		Federal Employment Credit functions
	*/
	function getFederalEmploymentCreditData( $date ) {
		foreach( $this->federal_employment_credit_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return FALSE;
	}
	function getFederalEmploymentCreditAmount() {
		$data = $this->getFederalEmploymentCreditData( $this->getDate() );

		Debug::text('Date: '. $this->getDate() .' Credit: '. $data['credit'], __FILE__, __LINE__, __METHOD__, 10);
		return $data['credit'];
	}

	/*
		CPP functions
	*/
	function getCPPData( $date ) {
		foreach( $this->cpp_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return FALSE;
	}

	function getCPPMaximumEarnings() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['maximum_pensionable_earnings'];
	}

	function getCPPBasicExemption() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['basic_exemption'];
	}

	function getCPPEmployeeRate() {
		$data = $this->getCPPData( $this->getDate() );

		Debug::text('Date: '. $this->getDate() .' Rate: '. $data['employee_rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $data['employee_rate'];
	}

	function getCPPEmployeeMaximumContribution() {
		$data = $this->getCPPData( $this->getDate() );

		return $data['employee_maximum_contribution'];
	}

	/*
		EI functions
	*/
	function getEIData( $date ) {
		foreach( $this->ei_options as $effective_date => $data ) {
			if ( $date >= $effective_date ) {
				return $data;
			}
		}

		return FALSE;
	}
	function getEIMaximumEarnings() {
		$data = $this->getEIData( $this->getDate() );

		return $data['maximum_insurable_earnings'];
	}

	function getEIEmployeeRate() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employee_rate'];
	}

	function getEIEmployeeMaximumContribution() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employee_maximum_contribution'];
	}

	function getEIEmployerRate() {
		$data = $this->getEIData( $this->getDate() );

		return $data['employer_rate'];
	}

	function getData() {
		global $cache;

		$country = $this->getCountry();
		$province = $this->getProvince();
		$epoch = $this->getDate();

		if ($epoch == NULL OR $epoch == ''){
			$epoch = TTDate::getTime();
		}

		Debug::text('bUsing ('. $province .') values from: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);

		$cache_id = $country.$province.$epoch;

		if ( is_string( $cache->get($cache_id, $this->table ) ) ) {
			$this->income_tax_rates = unserialize( $cache->get($cache_id, $this->table ) );
			Debug::text('Using Cached Income Tax Data!', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$this->income_tax_rates = FALSE;
		}


		if ( $this->income_tax_rates === FALSE ) {
			//There were issues with this query when provincial taxes were updated but not federal
			//We need to basically make a union query that queries the latest federal taxes separate
			//from the provincial
			$query = 'select country,province,income,rate,constant,effective_date
						from '. $this->table .'
						where
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
														AND province is NULL
													ORDER BY effective_date DESC
													LIMIT 1)
								)
							AND
							( country = '. $this->db->qstr($country).'
									and province is NULL)
							OR
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
														AND province = '. $this->db->qstr($province) .'
													ORDER BY effective_date DESC
													LIMIT 1)
								)
							AND
							( country = '. $this->db->qstr($country).'
									and province = '. $this->db->qstr($province) .')
						ORDER BY province desc, income asc, rate asc
					';
			//Debug::text('Query: '. $query , __FILE__, __LINE__, __METHOD__, 10);
			try {
				$rs = $this->db->Execute($query);
			} catch (Exception $e) {
				throw new DBError($e);
			}

			$rs = $rs->GetRows();

			foreach($rs as $key => $arr) {
				if ( $arr['province'] == NULL ) {
					$type = 'federal';
				} else {
					$type = 'provincial';
				}

				$this->income_tax_rates[$type][] = array('income' => trim($arr['income']), 'rate' => ( bcdiv( trim($arr['rate']), 100 ) ), 'constant' => trim($arr['constant']) );
			}
			Debug::text('bUsing values from: '. TTDate::getDate('DATE+TIME', $arr['effective_date']), __FILE__, __LINE__, __METHOD__, 10);

			//var_dump($this->income_tax_rates);
			$cache->save(serialize($this->income_tax_rates), $cache_id, $this->table );
		}

		return $this;
	}

	private function getRateArray($income, $type) {
		Debug::text('Calculating '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($this->income_tax_rates[$type]) ) {
			$rates = $this->income_tax_rates[$type];
		} else {
			Debug::text('aNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( count($rates) == 0 ) {
			Debug::text('bNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = ( count($rates) - 1 );
		$i = 0;
		foreach ($rates as $key => $values) {
			$value = $values['income'];
			$rate = $values['rate'];
			$constant = $values['constant'];

			Debug::text('Value: '. $value .' Rate: '. $rate .' Constant: '. $constant .' Previous Value: '. $prev_value, __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_value AND $income <= $value) {
				Debug::text('Found Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);

				return $this->income_tax_rates[$type][$key];
			} elseif ($i == $total_rates) {
				Debug::text('Found Last Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $this->income_tax_rates[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return FALSE;
	}

	function getFederalLowestRate() {
		$arr = $this->getRateArray(1, 'federal');
		Debug::text('Federal Lowest Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getFederalRate($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getFederalConstant($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function getProvincialLowestRate() {
		$arr = $this->getRateArray(1, 'provincial');
		Debug::text('Provincial Lowest Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getProvincialRate($income) {
		$arr = $this->getRateArray($income, 'provincial');
		Debug::text('Provincial Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getProvincialConstant($income) {
		$arr = $this->getRateArray($income, 'provincial');
		Debug::text('Provincial Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function test3() {
		Debug::text('Sub-Class Test3: '. __CLASS__, __FILE__, __LINE__, __METHOD__, 10);
		return 'blah3';
	}
}
?>
