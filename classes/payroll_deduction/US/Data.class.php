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
* Other tax calculators:
	http://www.pensoft.com/freestuff/stateinformation.aspx
	http://www.yourmoneypage.com/withhold/fedwh1.php

	- List of tax table changes*****: http://www.tax-tables.com/withholding.html
		http://www.optimum-solutions.com/hris-news/state-payroll-and-tax-updates-for-2012

	- Federal/State tax information: http://www.payroll-taxes.com/state-tax.htm

//NOCHANGE-* means document was updated for the year, but no changes affected the formulas.
//Change every year usually
C:19-Dec-14				Federal          		- Google: Notice 1036 http://www.irs.gov/pub/irs-pdf/n1036.pdf
C:19-Dec-14-NOCHANGE-	'AL' => 'Alabama' 		- http://www.ador.state.al.us/Withholding/index.html *Withholding Tax Tables and Instructions
C:26-Dec-14				'CA' => 'California' 	- http://www.edd.ca.gov/Payroll_Taxes/Rates_and_Withholding.htm *PIT Withholding schedules
C:26-Dec-14				'CT' => 'Connecticut'	- http://www.ct.gov/drs/cwp/view.asp?a=1509&q=444766 *May have to search for the latest year... Form TPG-211 Withholding Calculation Rules Effective
C:26-Dec-14-NOCHANGE-	'ID' => 'Idaho',		- http://tax.idaho.gov/s-results-pub.cfm?doc=EPB00006&pkey=bus
C:26-Dec-14				'MN' => 'Minnesota',	- http://www.revenue.state.mn.us/businesses/withholding/Pages/Forms.aspx *2013 Minnesota Withholding Computer Formula
C:26-Dec-14-NOCHANGE-	'MO' => 'Missouri',		- http://www.dor.mo.gov/tax/business/withhold/ *Employers Tax Guide
C:26-Dec-14-NOCHANGE-	'NM' => 'New Mexico', 	- http://www.tax.newmexico.gov/Businesses/Wage-Withholding-Tax/Pages/Home.aspx *FYI-104 ***Often changes in Jan.
C:26-Dec-14-NOCHANGE-	'OH' => 'Ohio',			- http://www.tax.ohio.gov/employer_withholding.aspx *Withholding Tables/Income Tax Withholding Instructions
C:26-Dec-14				'OK' => 'Oklahoma',		- http://www.tax.ok.gov/btforms.html *OW-2, Oklahoma Income Tax Withholding Tables
C:26-Dec-14-NOCHANGE-	'VT' => 'Vermont',		- http://www.state.vt.us/tax/businesswithholding.shtml *Vermont Percentage Method Withholding Tables
C:26-Dec-14				'CO' => 'Colorado',		- https://www.colorado.gov/pacific/tax/withholding-payroll-tax-instructions-and-forms *Form: DR 1098
C:26-Dec-14-NOCHANGE-	'DE' => 'Delaware',		- http://revenue.delaware.gov/services/WITBk.shtml *http://revenue.delaware.gov/services/wit_folder/section17.shtml
C:26-Dec-14				'KS' => 'Kansas',		- http://www.ksrevenue.org/forms-btwh.html *Form: KW-100
C:26-Dec-14				'KY' => 'Kentucky', 	- http://revenue.ky.gov/wht/ *Standard Deduction adjusted each year in Computer Formula (Optional Withholding Method)
C:26-Dec-14-NOCHANGE-	'MD' => 'Maryland',		- http://business.marylandtaxes.com/taxinfo/withholding/default.asp - Use 1.25% LOCAL INCOME TAX tables, *minus 1.25%*, manually calculate each bracket. Use tax_table_bracket_calculator.ods. See MD.class.php for more information.
C:26-Dec-14				'ME' => 'Maine',		- http://www.state.me.us/revenue/forms/with/2013.htm -- Check each year on the right of the page.
C:26-Dec-14				'NY' => 'New York',		- http://www.tax.ny.gov/forms/withholding_cur_forms.htm *WATCH NYS=New York State, NYC=New York City. NYS-50-T.1 or .2
C:26-Dec-14				'NC' => 'North Carolina'- http://www.dornc.com/taxes/wh_tax/index.html *Income Tax Withholding Tables & Instructions for Employers, NC30
C:26-Dec-14				'ND' => 'North Dakota', - http://www.nd.gov/tax/indwithhold/pubs/guide/index.html *Withholding Rates and Instructions for Wages Paid in 2013
C:26-Dec-14				'OR' => 'Oregon',		- http://www.oregon.gov/DOR/BUS/Pages/payroll_updates.aspx *Search: Withholdings Tax Formulas 2013
C:26-Dec-14-NOCHANGE-	'MI' => 'Michigan',		- http://www.michigan.gov/taxes/0,4676,7-238-43531_61039---,00.html *Michigan Income Tax Withholding Guide 446-I
C:26-Dec-14				'IL' => 'Illinois',		- http://www.revenue.state.il.us/Businesses/TaxInformation/Payroll/index.htm *Booklet IL-700-T
C:26-Dec-14				'MA' => 'Massachusetts' - http://www.mass.gov/dor/individuals/taxpayer-help-and-resources/tax-guides/withholding-tax-guide.html#calculate *Circular M
C:26-Dec-14				'RI' => 'Rhode Island', - http://www.tax.state.ri.us/misc/software_developers.php *Percentage Method Withholding Tables

//Change less often
C:26-Dec-14-NOCHANGE-	'GA' => 'Georgia',		- http://dor.georgia.gov/tax-guides *Employers Tax Guide
C:26-Dec-14-NOCHANGE-	'HI' => 'Hawaii',		- http://tax.hawaii.gov/forms/a1_b1_5whhold/ *Employers Tax Guide (Booklet A)
C:26-Dec-14			'NE' => 'Nebraska',		- http://www.revenue.nebraska.gov/withhold.html *Nebraska  Circular EN, Income Tax Withholding on Wages
C:26-Dec-14-NOCHANGE-	'WI' => 'Wisconsin',	- http://www.revenue.wi.gov/forms/with/index.html *Pub W-166, Method "B" calculation

//Rarely change
C:26-Dec-14-NOCHANGE-*	'UT' => 'Utah',			- http://tax.utah.gov/withholding *PUB 14, Withholding Tax Guide
C:26-Dec-14-NOCHANGE-	'AZ' => 'Arizona',		- http://www.azdor.gov/Forms/Withholding.aspx *Form A4: Employees choose a straight percent to pick.
C:26-Dec-14				'AR' => 'Arkansas'		- http://www.dfa.arkansas.gov/offices/incomeTax/withholding/Pages/withholdingForms.aspx *Witholding Tax Formula ***They use a minus calculation, so we have to manually calculate each bracket ourselves. Use tax_table_bracket_calculator.ods
C:26-Dec-14-NOCHANGE-*	'DC' => 'D.C.', 		- http://otr.cfo.dc.gov/page/income-tax-withholding-instructions-and-tables *Form: FR-230
C:26-Dec-14				'IN' => 'Indiana',		- http://www.in.gov/dor/4006.htm#withholding *Departmental Notice #1 DN01
C:26-Dec-14-NOCHANGE-	'IA' => 'Iowa',			- https://tax.iowa.gov/iowa-withholding-tax-information-booklet *Iowa Withholding Tax Guide
C:26-Dec-14-NOCHANGE-	'LA' => 'Louisiana',	- http://www.revenue.louisiana.gov/sections/publications/tm.asp *R-1306
C:26-Dec-14-NOCHANGE-	'MS' => 'Mississippi',	- http://www.dor.ms.gov/taxareas/withhold/main.html *Pub 89-700
C:26-Dec-14-NOCHANGE-	'MT' => 'Montana',		- http://revenue.mt.gov/forbusinesses/Wage_Withholding_Tax/default.mcpx *Montana Witholding Tax Guide
C:26-Dec-14-NOCHANGE-	'NJ' => 'New Jersey',	- http://www.state.nj.us/treasury/taxation/freqqite.shtml *Withholding Rate Tables
C:26-Dec-14-NOCHANGE-	'PA' => 'Pennsylvania', - http://www.revenue.pa.gov/GeneralTaxInformation/Tax%20Types%20and%20Information/Pages/Employer-Withholding.aspx#.VJ3CWAuA *Rev 415 - Employer Withholding Information
C:26-Dec-14-NOCHANGE-	'SC' => 'South Carolina'- http://www.sctax.org/tax/withholding *Formula for Computing SC Withholding Tax WH-1603F
C:26-Dec-14-NOCHANGE- 	'VA' => 'Virginia',		- http://www.tax.virginia.gov/site.cfm?alias=WithholdingTax, http://www.tax.virginia.gov/forms.cfm?formtype=Withholding%20Tax *Employer Withholding Instructions
C:26-Dec-14-NOCHANGE-	'WV' => 'West Virginia',- http://www.wva.state.wv.us/wvtax/withholdingTaxForms.aspx *IT-100.1A

	'AK' => 'Alaska',		- NO STATE TAXES
	'FL' => 'Florida',		- NO STATE TAXES
	'NV' => 'Nevada',		- NO STATE TAXES
	'NH' => 'New Hampshire' - NO STATE TAXES
	'SD' => 'South Dakota',	- NO STATE TAXES
	'TN' => 'Tennessee',	- NO STATE TAXES
	'TX' => 'Texas',		- NO STATE TAXES
	'WA' => 'Washington',	- NO STATE TAXES
	'WY' => 'Wyoming'		- NO STATE TAXES

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_Data extends PayrollDeduction_Base {
	var $db = NULL;
	var $income_tax_rates = array();
	var $table = 'income_tax_rate_us';
	var $country_primary_currency = 'USD';

	var $federal_allowance = array(
									1420099200 => 4000.00, //01-Jan-15
									1388563200 => 3950.00, //01-Jan-14
									1357027200 => 3900.00, //01-Jan-13
									1325404800 => 3800.00, //01-Jan-12
									1293868800 => 3700.00, //01-Jan-11
									//01-Jan-10 - No Change
									1230796800 => 3650.00, //01-Jan-09
									1199174400 => 3500.00, //01-Jan-08
									1167638400 => 3400.00, //01-Jan-07
									1136102400 => 3300.00  //01-Jan-06
								);

	//http://www.ssa.gov/pressoffice/factsheets/colafacts2013.htm
	var $social_security_options = array(
									1420099200 => array( //2015
														'maximum_earnings' => 118500,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7254.00 //Employee
													),
									1388563200 => array( //2014
														'maximum_earnings' => 117000,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7254.00 //Employee
													),
									1357027200 => array( //2013
														'maximum_earnings' => 113700,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7049.40 //Employee
													),
									1325404800 => array( //2012
														'maximum_earnings' => 110100,
														'employee_rate' => 4.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 4624.20 //Employee
													),
									1293868800 => array( //2011 - Employer is still 6.2%
														'maximum_earnings' => 106800,
														'employee_rate' => 4.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 4485.60 //Employee
													),
									//2010 - No Change.
									1230796800 => array( //2009
														'maximum_earnings' => 106800,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6621.60 //Employee
													),
									1199174400 => array( //2008
														'maximum_earnings' => 102000,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6324.00 //Employee
													),
									1167638400 => array( //2007
														'maximum_earnings' => 97500,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6045.00 //Employee
													),
									1136102400 => array( //2006
														'maximum_earnings' => 94200,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 5840.40 //Employee
													)
								);

	var $federal_ui_options = array(
									1309503600 => array( //2011 (July 1st)
														'maximum_earnings' => 7000,
														'rate' => 6.0,
														'minimum_rate' => 0.6,
													),
									1136102400 => array( //2006
														'maximum_earnings' => 7000,
														'rate' => 6.2,
														'minimum_rate' => 0.8,
													)
								);

	var $medicare_options = array(
									//No changes in 2015.
									1388563200 => array( //2014
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0.90,
														'employee_threshold' => array(
																					10 => 200000, //Single
																					20 => 125000, //Married - Without Spouse Filing
																					30 => 250000, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 200000, //Threshold for Form 941
														),
									1357027200 => array( //2013
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0.90,
														'employee_threshold' => array(
																					10 => 200000, //Single
																					20 => 125000, //Married - Without Spouse Filing
																					30 => 250000, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 200000, //Threshold for Form 941
														),
									1136102400 => array( //2006
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0,
														'employee_threshold' => array(
																					10 => 0, //Single
																					20 => 0, //Married - Without Spouse Filing
																					30 => 0, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 0, //Threshold for Form 941
														),
								);

	/*
		10 => 'Single or HOH',
		20 => 'Married Without Spouse Filing',
		30 => 'Married With Spouse Filing',

		Calculation type is:
			10 = Percent
			20 = Amount
			30 = Amount less Percent of wages in excess.

		Wage Base is the maximum wage that is eligible for EIC.

	*/
	var $eic_options = array(
								1262332800 => array( //01-Jan-10
													10 => array(
																array( 'income' => 8970,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 16450, 'calculation_type' => 20, 'amount' => 1830 ),
																array( 'income' => 16450, 'calculation_type' => 30, 'amount' => 1830, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8970,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 21460, 'calculation_type' => 20, 'amount' => 1830 ),
																array( 'income' => 21460, 'calculation_type' => 30, 'amount' => 1830, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4485,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 10730, 'calculation_type' => 20, 'amount' => 915 ),
																array( 'income' => 10730, 'calculation_type' => 30, 'amount' => 915, 'percent' => 9.588  ),
																),
													),
								1238569200 => array( //01-Apr-09
													10 => array(
																array( 'income' => 8950,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 16420, 'calculation_type' => 20, 'amount' => 1826 ),
																array( 'income' => 16420, 'calculation_type' => 30, 'amount' => 1826, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8950,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 21420, 'calculation_type' => 20, 'amount' => 1826 ),
																array( 'income' => 21420, 'calculation_type' => 30, 'amount' => 1826, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4475, 'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 10710, 'calculation_type' => 20, 'amount' => 913 ),
																array( 'income' => 10710, 'calculation_type' => 30, 'amount' => 913, 'percent' => 9.588  ),
																),
													),
								1199174400 => array( //01-Jan-08
													10 => array(
																array( 'income' => 8580,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 15740, 'calculation_type' => 20, 'amount' => 1750 ),
																array( 'income' => 15740, 'calculation_type' => 30, 'amount' => 1750, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8580,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 18740, 'calculation_type' => 20, 'amount' => 1750 ),
																array( 'income' => 18740, 'calculation_type' => 30, 'amount' => 1750, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4290, 'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 9370, 'calculation_type' => 20, 'amount' => 875 ),
																array( 'income' => 9370, 'calculation_type' => 30, 'amount' => 875, 'percent' => 9.588  ),
																),
													),
							);

	function __construct() {
		global $db;

		$this->db = $db;

		return TRUE;
	}

	function getData() {
		global $cache;

		$country = $this->getCountry();
		$state = $this->getProvince();
		$district = $this->getDistrict();

		$epoch = $this->getDate();
		$federal_status = $this->getFederalFilingStatus();
		if ( empty($federal_status) ) {
			$federal_status = 10;
		}
		$state_status = $this->getStateFilingStatus();
		if ( empty($state_status) ) {
			$state_status = 10;
		}
		$district_status = $this->getDistrictFilingStatus();

		if ( $epoch == NULL OR $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		Debug::text('Using ('. $state .'/'. $district .') values from: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);

		$cache_id = $country.$state.$district.$epoch.$federal_status.$state_status.$district_status;

		if ( is_string( $cache->get($cache_id, $this->table ) ) ) {
			$this->income_tax_rates = unserialize( $cache->get($cache_id, $this->table ) );
		} else {
			$this->income_tax_rates = FALSE;
		}


		if ( $this->income_tax_rates === FALSE ) {
			//There were issues with this query when provincial taxes were updated but not federal
			//We need to basically make a union query that queries the latest federal taxes separate
			//from the provincial
			$query = 'select country,state,district,status,income,rate,constant,effective_date
						from '. $this->table .'
						where
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
														AND state is NULL
														AND district is NULL
														AND ( status = 0
															OR status = '. $federal_status .' )
													ORDER BY effective_date DESC
													LIMIT 1) )
							AND
							( ( country = '. $this->db->qstr($country).'
									and state is NULL
									and ( status = 0 OR status = '. $federal_status .') ) )
							OR
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
														AND state = '. $this->db->qstr($state) .'
														AND district is NULL
														AND ( status = 0
															OR status = '. $state_status .' )
													ORDER BY effective_date DESC
													LIMIT 1) )
							AND
							( country = '. $this->db->qstr($country).'
									and state = '. $this->db->qstr($state) .'
									and district is NULL
									and ( status = 0 OR status = '. $state_status .') )
							OR
								(
								effective_date = ( 	select effective_date
													from '. $this->table .'
													where effective_date <= '. $epoch .'
														AND country = '. $this->db->qstr($country).'
														AND state = '. $this->db->qstr($state) .'
														AND district = '. $this->db->qstr($district) .'
														AND ( status = 0
															OR status = '. $district_status .' )
													ORDER BY effective_date DESC
													LIMIT 1) )
							AND
							( country = '. $this->db->qstr($country).'
									and state = '. $this->db->qstr($state) .'
									and district = '. $this->db->qstr($district) .'
									and ( status = 0 OR status = '. $district_status .') )
						ORDER BY state desc, district desc, income asc, rate asc';

			//Debug::text('Query: '. $query , __FILE__, __LINE__, __METHOD__, 10);
			try {
				$rs = $this->db->Execute($query);
			} catch (Exception $e) {
				throw new DBError($e);
			}

			$rows = $rs->GetRows();

			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;
			$prev_type = NULL;
			foreach($rows as $key => $arr) {
				if ( $arr['district'] != NULL) {
					$type = 'district';
				} elseif ( $arr['state'] != NULL ) {
					$type = 'state';
				} else {
					$type = 'federal';
				}

				//Tax brackets can get carried over from Federal to State, to District if we don't clear them out each time the type changes.
				if ( $type != $prev_type ) {
					$prev_income = 0;
					$prev_rate = 0;
					$prev_constant = 0;
				}

				$this->income_tax_rates[$type][] = array(	'prev_income' => trim($prev_income),
															'income' => trim($arr['income']),
															'prev_rate' => ( bcdiv( trim($prev_rate), 100 ) ),
															'rate' => ( bcdiv( trim($arr['rate']), 100 ) ),
															'prev_constant' => trim($prev_constant),
															'constant' => trim($arr['constant']) );

				$prev_type = $type;
				$prev_income = $arr['income'];
				$prev_rate = $arr['rate'];
				$prev_constant = $arr['constant'];
			}

			if ( isset($this->income_tax_rates) AND is_array($this->income_tax_rates) ) {
				foreach( $this->income_tax_rates as $type => $brackets ) {
					$i = 0;
					$total_brackets = ( count($brackets) - 1 );
					foreach( $brackets as $key => $bracket_data ) {
						if ( $i == 0 ) {
							$first = TRUE;
						} else {
							$first = FALSE;
						}

						if ( $i == $total_brackets ) {
							$last = TRUE;
						} else {
							$last = FALSE;
						}

						$this->income_tax_rates[$type][$key]['first'] = $first;
						$this->income_tax_rates[$type][$key]['last'] = $last;

						$i++;
					}
				}
			}

			/*
			if ( isset($arr) ) {
				Debug::text('Using values from: '. TTDate::getDate('DATE+TIME', $arr['effective_date']) , __FILE__, __LINE__, __METHOD__, 10);
			}
			*/

			//Debug::Arr($this->income_tax_rates, 'Income Tax Rates: ', __FILE__, __LINE__, __METHOD__, 10);
			$cache->save(serialize($this->income_tax_rates), $cache_id, $this->table );
		}

		return $this;
	}

	function getRateArray($income, $type) {
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

			//Debug::text('Key: '. $key .' Value: '. $value .' Rate: '. $rate .' Constant: '. $constant .' Previous Value: '. $prev_value , __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_value AND $income <= $value) {
				//Debug::text('Found Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);

				return $this->income_tax_rates[$type][$key];
			} elseif ($i == $total_rates) {
				//Debug::text('Found Last Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $this->income_tax_rates[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return FALSE;
	}

	function getEICRateArray( $income, $type ) {
		Debug::text('Calculating '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);

		$eic_options = $this->getDataFromRateArray( $this->getDate(), $this->eic_options);
		if ( $eic_options == FALSE ) {
			Debug::text('aNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( isset($eic_options[$type]) ) {
			$rates = $eic_options[$type];
		} else {
			Debug::text('bNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( count($rates) == 0 ) {
			Debug::text('cNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = ( count($rates) - 1 );
		$i = 0;
		foreach ($rates as $key => $values) {
			$value = $values['income'];

			//Debug::text('Key: '. $key .' Income: '. $value , __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_value AND $income <= $value) {
				//Debug::text('Found Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $eic_options[$type][$key];
			} elseif ($i == $total_rates) {
				//Debug::text('Found Last Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $eic_options[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return FALSE;

	}

	function getFederalRate($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getFederalPreviousRate($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Previous Rate: '. $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_rate'];
	}

	function getFederalRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getFederalRateIncome($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getFederalConstant($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function getFederalAllowanceAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_allowance);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}


	function getStateRate($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getStatePreviousRate($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Previous Rate: '. $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_rate'];
	}

	function getStateRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getStateRateIncome($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getStateConstant($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function getStatePreviousConstant($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Previous Constant: '. $arr['prev_constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_constant'];
	}

	function getDistrictRate($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getDistrictRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getDistrictRateIncome($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getDistrictConstant($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	//Social Security
	function getSocialSecurityMaximumEarnings() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return $retarr['maximum_earnings'];
		}

		return FALSE;
	}

	function getSocialSecurityMaximumContribution( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return bcmul( $this->getSocialSecurityMaximumEarnings(), bcdiv( $this->getSocialSecurityRate( $type ), 100 ) );
		}

		return FALSE;
	}

	function getSocialSecurityRate( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return $retarr[$type.'_rate'];
		}

		return FALSE;
	}

	//Medicare
	function getMedicareRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}
	function getMedicareAdditionalEmployerThreshold() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( isset($retarr['employer_threshold']) ) {
			return $retarr['employer_threshold'];
		}

		return FALSE;
	}

	function getMedicareAdditionalThresholdRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( isset($retarr['employee_threshold_rate']) ) {
			return $retarr['employee_threshold_rate'];
		}

		return FALSE;
	}


	//Federal UI
	function getFederalUIRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			if ( $this->getStateUIRate() > bcsub( $retarr['rate'], $this->getFederalUIMinimumRate() ) ) {
				$retval = $this->getFederalUIMinimumRate();
			} else {
				$retval = ( $retarr['rate'] - $this->getStateUIRate() );
			}

			return $retval;
		}

		return FALSE;
	}

	function getFederalUIMinimumRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			return $retarr['minimum_rate'];
		}

		return FALSE;
	}

	function getFederalUIMaximumEarnings() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			return $retarr['maximum_earnings'];
		}

		return FALSE;
	}

	function getFederalUIMaximumContribution() {
		$retval = bcmul( $this->getFederalUIMaximumEarnings(), bcdiv( $this->getFederalUIRate(), 100 ) );
		if ( $retval != FALSE ) {
			return $retval;
		}

		return FALSE;
	}
}
?>
