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
 * $Revision: 2217 $
 * $Id: CAPayrollDeductionTest.php 2217 2009-10-31 22:48:21Z ipso $
 * $Date: 2009-10-31 15:48:21 -0700 (Fri, 31 Oct 2009) $
 */
require_once('PHPUnit/Framework/TestCase.php');

class CAPayrollDeductionTest2009 extends PHPUnit_Framework_TestCase {

    public $company_id = 1;

    public function __construct() {
        global $db, $cache;

		$this->tax_table_file = dirname(__FILE__).'/CAPayrollDeductionTest2009.csv';

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');

		TTDate::setTimeZone('PST');
    }

    public function setUp() {
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);
        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);
        return TRUE;
    }

	public function mf($amount) {
		return Misc::MoneyFormat($amount, FALSE);
	}

	//
	// January 2009
	//

	//
	// Don't forget to update the Federal Employment Credit in CA.class.php.
	//
	function testCSVFile() {
		$this->assertEquals( file_exists( $this->tax_table_file), TRUE);


		$test_rows = Misc::parseCSV( $this->tax_table_file, TRUE );

		$total_rows = count($test_rows)+1;
		$i = 2;
		foreach( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__,10);
			if ( $row['gross_income'] == '' AND $row['low_income'] != '' AND $row['high_income'] != '' ) {
				$row['gross_income'] = $row['low_income'] + ( ($row['high_income'] - $row['low_income']) / 2 );
			}
			if ( $row['country'] != '' AND $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setFederalTotalClaimAmount( $row['federal_claim'] ); //Amount from 2005, Should use amount from 2007 automatically.
				$pd_obj->setProvincialTotalClaimAmount( $row['provincial_claim'] );
				//$pd_obj->setWCBRate( 0.18 );

				$pd_obj->setEIExempt( FALSE );
				$pd_obj->setCPPExempt( FALSE );

				$pd_obj->setFederalTaxExempt( FALSE );
				$pd_obj->setProvincialTaxExempt( FALSE );

				$pd_obj->setYearToDateCPPContribution( 0 );
				$pd_obj->setYearToDateEIContribution( 0 );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->mf( $row['federal_deduction'] ) );
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, $i-1);
	}

	//
	// CPP/ EI
	//
	function testCA_2009a_BiWeekly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 2009 01-Jan-09: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-09'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 10100 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.32 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '585.32' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), '22.31' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerCPP() ), '22.31' );
	}

	function testCA_2009a_SemiMonthly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 2009 01-Jan-09: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-09'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 10100 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.23 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '585.23' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), '21.75' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerCPP() ), '21.75' );
	}

	function testCA_2009a_EI_LowIncome() {
		Debug::text('CA - EI - Beginning of 2009 01-Jan-09: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-09'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 10100 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.76 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '587.76' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeEI() ), '10.17' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerEI() ), '14.24' );
	}
}
?>