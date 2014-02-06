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
 * $Id: CAPayrollDeductionTest.php 2217 2008-10-31 22:48:21Z ipso $
 * $Date: 2008-10-31 15:48:21 -0700 (Fri, 31 Oct 2008) $
 */
require_once('PHPUnit/Framework/TestCase.php');

class CAPayrollDeductionTest2008 extends PHPUnit_Framework_TestCase {

    public $company_id = NULL;

    public function __construct() {
        global $db, $cache;

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');

		$this->company_id = PRIMARY_COMPANY_ID;

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
	// July 2008
	//
	//
	// BC - Provincial Taxes
	//
	function testBC_2008b_BiWeekly_Claim1_MedIncome() {
		Debug::text('BC - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jul-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 9189 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2774.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '159.15' );
	}

	function testBC_2008b_BiWeekly_Claim5_MedIncome() {
		Debug::text('BC - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jul-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 16427.01 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2774.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '144.87' );
	}

	//
	// January 2008
	//
	function testCA_2008a_BasicClaimAmount() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8128 ); //Amount from 2005, Should use amount from 2007 automatically.
		$pd_obj->setProvincialTotalClaimAmount( 8858 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2770.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '430.21' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '165.26' );
	}

	function testCA_2008a_BasicClaimAmountB() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 9189 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2770.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '430.21' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '165.26' );

	}

	function testCA_2008a_BiWeekly_Claim1_LowIncome() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 589.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '589.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '22.18' );
	}

	function testCA_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2407.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2407.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '350.35' );
	}

	function testCA_2008a_BiWeekly_Claim1_HighIncome() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7199.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '7199.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '1649.83' );
	}

	function testCA_2008a_BiWeekly_Claim5_LowIncome() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 16334.01 ); //Claim Code5 midpoint
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 815.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '815.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '14.97' );
	}

	function testCA_2008a_BiWeekly_Claim5_HighIncome() {
		Debug::text('CA - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 16334.01 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7199.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '7199.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '1610.98' );
	}

	function testCA_2008a_SemiMonthly_Claim1_LowIncome() {
		Debug::text('CA - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 615.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '615.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '20.80' ); //One penny off
	}

	function testCA_2008a_SemiMonthly_Claim1_MedIncome() {
		Debug::text('CA - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2720.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2720.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '404.28' ); //One penny off
	}

	function testCA_2008a_SemiMonthly_Claim1_HighIncome() {
		Debug::text('CA - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7781.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '7781.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '1782.12' ); //One penny off
	}

	//
	// CPP/ EI
	//
	function testCA_2008a_BiWeekly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
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

	function testCA_2008a_SemiMonthly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
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

	function testCA_2008a_EI_LowIncome() {
		Debug::text('CA - EI - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
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

	//
	// BC - Provincial Taxes
	//
	function testBC_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('BC - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 9189 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2774.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '165.68' );
	}

	function testBC_2008a_BiWeekly_Claim5_MedIncome() {
		Debug::text('BC - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','BC');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 16427.01 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2774.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '150.79' );
	}

	//
	// AB - Provincial Taxes
	//
	function testAB_2008a_BiWeekly_Claim0_LowIncome() {
		Debug::text('AB - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','AB');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1422.00 );

		Debug::text('Fed Ded: '. $pd_obj->getFederalPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1422.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '133.37' ); //133.37
	}

	function testAB_2008a_BiWeekly_Claim1_LowIncome() {
		Debug::text('AB - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','AB');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 16161 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1422.00 );

		Debug::text('Fed Ded: '. $pd_obj->getFederalPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1422.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '71.21' ); //Should be 71.21
	}

	function testAB_2008a_BiWeekly_Claim5_LowIncome() {
		Debug::text('AB - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','AB');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 24435 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1422.00 );

		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1422.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '39.39' ); //Should be 39.39
	}

	//
	// SK - Provincial Taxes
	//
	function testSK_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('SK - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','SK');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8945.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2840.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2840.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '289.56' );
	}

	function testSK_2008a_SemiMonthly_Claim1_MedIncome() {
		Debug::text('SK - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','SK');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8945.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2824.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2824.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '280.85' );
	}

	//
	// MB - Provincial Taxes
	//
	function testMB_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('MB - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','MB');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8034 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2754.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2754.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '294.17' );
	}

	function testMB_2008a_SemiMonthly_Claim1_MedIncome() {
		Debug::text('MB - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','MB');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8034 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2705.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2705.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '272.32' );
	}

	//
	// ON - Provincial Taxes
	//
	function testON_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('ON - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','ON');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8681 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2749.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2749.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '209.40' );
	}

	function testON_2008a_SemiMonthly_Claim1_MedIncome() {
		Debug::text('ON - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','ON');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 8681 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2830.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2830.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '210.59' ); //214.00
	}


	//
	// PEI - Provincial Taxes
	//
	function testPE_2008a_BiWeekly_Claim1_MedIncome() {
		Debug::text('PE - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','PE');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 14254 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2060 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2060.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '170.96' );
	}


	function testPE_2008a_BiWeekly_Claim1_HighIncome() {
		Debug::text('PE - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','PE');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2759 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2759.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '300.76' ); //298.75
	}

	function testPE_2008a_BiWeekly_Claim1_MedIncomeB() {
		Debug::text('PE - BiWeekly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','PE');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2725 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2725.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '281.75' ); //281.74
	}

	function testPE_2008a_SemiMonthly_Claim1_MedIncome() {
		Debug::text('PE - SemiMonthly - Beginning of 2008 01-Jan-08: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA','PE');
		$pd_obj->setDate(strtotime('01-Jan-08'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9600 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2763.00 );


		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2763.00' );
		Debug::text('Prov Ded: '. $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__,10);
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '288.09' ); //285.90
	}
}
?>