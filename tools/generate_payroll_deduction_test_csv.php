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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'classes/payroll_deduction/PayrollDeduction.class.php');

if ( $argc < 2 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: generate_payroll_deduction_test_csv.php [country_code] [date]\n";
	echo $help_output;
} else {
	$country = strtoupper($argv[1]);
	$effective_date = strtotime($argv[2]);

	$cf = new CompanyFactory();
	$province_arr = $cf->getOptions('province');

	if ( !isset($province_arr[$country]) ) {
		echo "Country does not have any province/states.\n";
	}
	ksort($province_arr[$country]);

	$pay_periods = 26;
	$static_test_data = array(
					   'CA' => array(
									'income' => array(
														192, //5000/year
														384, //10000/year
														961, //25000/year
														1923, //50000,
														3846, //100000,
														9615, //250000
														),
									'federal_claim' => array(0,100), //Use lowest non-zero value.
									'provincial_claim' => array(0,100), //Use lowest non-zero value.
									),
					   'US' => array(
									'income' => array(
														192, //5000/year
														384, //10000/year
														961, //25000/year
														1923, //50000,
														3846, //100000,
														9615, //250000
														),
									'filing_status' => array(10,20,30),
									'allowance' => array(0,1,2,3,5),
									)
						);

	$test_data = array();

	if ( $country != '' AND isset($province_arr[$country]) AND $effective_date != '' ) {
		foreach( $province_arr[$country] as $province_code => $province ) {
			//echo "Province: $province_code\n";

			//Get all tax rates for each province.
			switch ( $country ) {
				case 'US':
					$table = 'income_tax_rate_us';
					$province_name = 'state';

					$query = 'select * from '. $table .'
								where country = ? and '.$province_name .' = ? and effective_date >= ?
								order by effective_date asc, status, income';

					break;
				case 'CR':
					$table = 'income_tax_rate_cr';
					$province_name = 'state';

					$query = 'select * from '. $table .'
								where country = ? and '.$province_name .' = ? and effective_date >= ?
								order by effective_date asc, status, income';

					break;
				case 'CA':
					$table = 'income_tax_rate';
					$province_name = 'province';

					$query = 'select * from '. $table .'
								where country = ? and '.$province_name .' = ? and effective_date >= ?
								order by effective_date asc, income';

					break;
			}
			//var_dump($query);
			$ph = array(
						'country' => $country,
						'province' => $province_code,
						'effective_date' => $effective_date,
						);

			$result = $db->Execute($query, $ph);

			//echo 'Tax Bracket Rows: '. $result->RecordCount() ."\n";
			if ( $country == 'US' ) { //US
				if ( $result->RecordCount() == 0 ) {
					//Use static test rates.
					$test_data[$country][$province_code] = $static_test_data[$country];
				} else {
					//Always include the same income brackets for testing, AS WELL as one to test each individual bracket.
					$test_data[$country][$province_code] = $static_test_data[$country];

					$i=1;
					$prev_income = NULL;
					$prev_status = NULL;
					$prev_province = NULL;
					foreach( $result as $tax_row ) {
						//Test $100 less then the first bracket, and $100 more then all other brackets for each status.
						$income = round($tax_row['income'] / $pay_periods);
						$variance = round(100 / $pay_periods);

						if ( $prev_income == NULL OR $prev_income > $income ) {
							//echo "First bracket! $country $province ".$tax_row['income']." T: ". ($tax_row['income']-$variance) ."\n";
							$test_data[$country][$province_code]['income'][] = $income-$variance;
							$test_data[$country][$province_code]['filing_status'][] = $tax_row['status'];
						}

						$test_data[$country][$province_code]['income'][] = $income+$variance;
						$test_data[$country][$province_code]['filing_status'][] = $tax_row['status'];
						$test_data[$country][$province_code]['allowance'] = $static_test_data[$country]['allowance'];

						$test_data[$country][$province_code]['income'] = array_unique($test_data[$country][$province_code]['income']);
						$test_data[$country][$province_code]['filing_status'] = array_unique($test_data[$country][$province_code]['filing_status']);

						$prev_income = $income;
						$prev_status = $tax_row['status'];
						$prev_province = $province_code;
						$i++;
						unset($income);
					}
				}

				foreach( $test_data[$country][$province_code]['filing_status'] as $filing_status ) {
					foreach( $test_data[$country][$province_code]['allowance'] as $allowance ) {
						foreach( $test_data[$country][$province_code]['income'] as $income ) {
							$pd_obj = new PayrollDeduction( $country, $province_code);
							$pd_obj->setDate( $effective_date);
							$pd_obj->setAnnualPayPeriods( $pay_periods );

							$pd_obj->setFederalFilingStatus( $filing_status );
							$pd_obj->setFederalAllowance( $allowance );

							$pd_obj->setStateFilingStatus( $filing_status );
							$pd_obj->setStateAllowance( $allowance );

							$pd_obj->setFederalTaxExempt( FALSE );
							$pd_obj->setProvincialTaxExempt( FALSE );

							switch ($province_code) {
								case 'GA':
									$pd_obj->setUserValue3( $allowance );
									break;
								case 'IN':
								case 'IL':
								case 'VA':
									$pd_obj->setUserValue1( $allowance );
									break;
							}

							if ( $province_code == 'GA' ) {
								Debug::text('Setting UserValue3: '. $allowance, __FILE__, __LINE__, __METHOD__,10);
								$pd_obj->setUserValue3( $allowance );
							}


							$pd_obj->setGrossPayPeriodIncome( $income );

							$retarr[] = array(
											'country' => $country,
											'province' => $province_code,
											'date' => date('m/d/y', $effective_date),
											'pay_periods' => $pay_periods,
											'filing_status' => $filing_status,
											'allowance' => $allowance,
											'gross_income' => $income,
											'federal_deduction' => Misc::MoneyFormat($pd_obj->getFederalPayPeriodDeductions(), FALSE),
											'provincial_deduction' => Misc::MoneyFormat($pd_obj->getStatePayPeriodDeductions(), FALSE)
											);
						}
					}
				}
			} elseif ( $country == 'CA' ) { //Canada
				if ( $result->RecordCount() == 0 ) {
					//Use static test rates.
					$test_data[$country][$province_code] = $static_test_data[$country];
				} else {
					$test_data[$country][$province_code] = $static_test_data[$country];

					$i=1;
					$prev_income = NULL;
					$prev_status = NULL;
					$prev_province = NULL;
					foreach( $result as $tax_row ) {
						//Test $100 less then the first bracket, and $100 more then all other brackets for each status.
						$income = round($tax_row['income'] / $pay_periods);
						$variance = round(100 / $pay_periods);

						if ( $prev_income == NULL OR $prev_income > $income ) {
							//echo "First bracket! $country $province ".$tax_row['income']." T: ". ($tax_row['income']-$variance) ."\n";
							$test_data[$country][$province_code]['income'][] = $income-$variance;
						}

						$test_data[$country][$province_code]['income'][] = $income+$variance;
						$test_data[$country][$province_code]['federal_claim'] = $static_test_data[$country]['federal_claim'];
						$test_data[$country][$province_code]['provincial_claim'] = $static_test_data[$country]['provincial_claim'];

						$test_data[$country][$province_code]['income'] = array_unique($test_data[$country][$province_code]['income']);

						$prev_income = $income;
						$prev_status = ( isset($tax_row['status']) ) ? $tax_row['status'] : NULL;
						$prev_province = $province_code;
						$i++;
						unset($income);
					}
				}

				foreach( $test_data[$country][$province_code]['provincial_claim'] as $provincial_claim ) {
					foreach( $test_data[$country][$province_code]['federal_claim'] as $federal_claim ) {
						foreach( $test_data[$country][$province_code]['income'] as $income ) {
							$pd_obj = new PayrollDeduction( $country, $province_code);
							$pd_obj->setDate( $effective_date);
							$pd_obj->setAnnualPayPeriods( $pay_periods );
							$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.

							$pd_obj->setFederalTotalClaimAmount( $federal_claim );
							$pd_obj->setProvincialTotalClaimAmount( $provincial_claim );

							$pd_obj->setEIExempt( FALSE );
							$pd_obj->setCPPExempt( FALSE );

							$pd_obj->setFederalTaxExempt( FALSE );
							$pd_obj->setProvincialTaxExempt( FALSE );

							$pd_obj->setYearToDateCPPContribution( 0 );
							$pd_obj->setYearToDateEIContribution( 0 );

							$pd_obj->setGrossPayPeriodIncome( $income );

							$retarr[] = array(
											'country' => $country,
											'province' => $province_code,
											'date' => date('m/d/y', $effective_date),
											'pay_periods' => $pay_periods,
											'federal_claim' => $pd_obj->getFederalTotalClaimAmount(),
											'provincial_claim' => $pd_obj->getProvincialTotalClaimAmount(),
											'gross_income' => $income,
											'federal_deduction' => Misc::MoneyFormat($pd_obj->getFederalPayPeriodDeductions(), FALSE),
											'provincial_deduction' => Misc::MoneyFormat($pd_obj->getProvincialPayPeriodDeductions(), FALSE)
											);
						}
					}
				}
			}
		}

		//generate column array.
		$column_keys = array_keys($retarr[0]);
		foreach( $column_keys as $column_key ) {
			$columns[$column_key] = $column_key;
		}

		//var_dump($test_data);
		//var_dump($retarr);
		echo Misc::Array2CSV( $retarr, $columns, FALSE, $include_header = TRUE );
	}
}
//Debug::Display();
?>
