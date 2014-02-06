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
 * $Revision: 2273 $
 * $Id: USPayrollDeductionTest.php 2273 2008-12-08 21:58:11Z root $
 * $Date: 2008-12-08 13:58:11 -0800 (Mon, 08 Dec 2008) $
 */
require_once('PHPUnit/Framework/TestCase.php');

class USPayrollDeductionTest2006 extends PHPUnit_Framework_TestCase {

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
	//
	//
	// 2006
	//
	//
	//

	//
	// US - Federal Taxes
	//

	function testUS_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
	}

	function testUS_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
	}

	function testUS_2006a_BiWeekly_Married_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '31.15' );
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '97.50' ); //97.50
	}

	function testUS_2006a_SemiMonthly_Married_LowIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '52.92' ); //52.92
	}

	function testUS_2006a_SemiMonthly_Single_MedIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '299.42' ); //299.42
	}

	function testUS_2006a_SemiMonthly_Single_HighIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_3Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.25' ); //56.25
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_5Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 5 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '20.21' ); //20.21
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_8AllowancesA() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '0.00' ); //0.00
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1300.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '8.96' ); //8.96
	}


	//
	// US Social Security
	//
	function testUS_2006a_SocialSecurity() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '62.00' );
	}

	function testUS_2006a_SocialSecurity_Max() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 5839.40 ); //5840.40

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '1.00' );
	}

	function testUS_2006a_Medicare() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '14.50' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '14.50' );
	}

	function testUS_2006a_FederalUI_NoState() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '62.00' );
	}

	function testUS_2006a_FederalUI_NoState_Max() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 0 );
		$pd_obj->setStateUIWageBase( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 433 ); //434
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '1.00' );
	}

	function testUS_2006a_FederalUI_State_Max() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 187.30 ); //188.30
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '1.00' );
	}

	//
	// State Income Taxes
	//

	//
	// MO
	//
	function testMO_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '31.00' ); //31.00
	}

	function testMO_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '33.00' ); //33.00
	}

	function testMO_2006a_SemiMonthly_Single_LowIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '97.50' ); //97.50
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '29.00' ); //33.00
	}

	function testMO_2006a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1300.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '8.96' ); //8.96
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '31.00' ); //31.00
	}

	function testMO_2006a_SemiMonthly_Married_HighIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '601.08' ); //601.08
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '202.00' ); //202.00
	}

	function testMO_2006a_StateUI() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '26.90' );
		$this->assertEquals( $this->mf( $pd_obj->getStateEmployerUI() ), '35.10' );
	}

	function testMO_2006a_StateUI_Max() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 187.30 ); //188.30
		$pd_obj->setYearToDateStateUIContribution( 385.10 ); //386.10

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '1.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStateEmployerUI() ), '1.00' );
	}

	//
	// CA
	//
	function testCA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '17.70' ); //17.70
	}

	function testCA_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '9.29' ); //9.29
	}

	function testCA_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '148.52' ); //148.52
	}

	//
	// NY
	//
	function testNY_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '33.71' ); //17.70
	}

	function testNY_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '32.58' ); //29.54
	}

	function testNY_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '213.29' ); //213.29
	}

	//
	// NY - NYC
	//
	function testNY_NYC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'NYC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setDistrictFilingStatus( 10 ); //Single
		$pd_obj->setDistrictAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '33.71' ); //17.70
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '21.19' ); //21.19
	}
	function testNY_NYC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'NYC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setDistrictFilingStatus( 20 ); //Married
		$pd_obj->setDistrictAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '32.58' ); //29.54
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '20.48' ); //20.48
	}

	function testNY_NYC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'NYC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setDistrictFilingStatus( 20 ); //Married
		$pd_obj->setDistrictAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '213.29' ); //213.29
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '125.04' ); //125.04
	}

	//
	// NY - Yonkers
	//
	function testNY_Yonkers_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'YONKERS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '33.71' ); //17.70
	}

	function testNY_Yonkers_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'YONKERS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '32.58' ); //29.54
	}

	function testNY_Yonkers_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NY', 'YONKERS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '213.29' ); //213.29
	}

	//
	// IL
	//
	function testIL_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1(1); //Line 1 on form
		$pd_obj->setUserValue2(1); //Line 2 on form

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '26.54' ); //26.54
	}

	function testIL_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1(2); //Line 1 on form
		$pd_obj->setUserValue2(3); //Line 2 on form

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '21.92' ); //21.92
	}

	function testIL_2006a_SemiMonthly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1(2); //Line 1 on form
		$pd_obj->setUserValue2(3); //Line 2 on form

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '111.25' ); //21.25
	}

	//
	// PA
	//
	function testPA_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','PA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '30.70' ); //30.70
	}

	//
	// OH
	//
	function testOH_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OH');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '27.40' ); //27.40
	}

	function testOH_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OH');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '25.08' ); //25.08
	}

	function testOH_2006a_SemiMonthly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OH');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '184.52' ); //184.52
	}

	//
	// MI
	//
	function testMI_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '34.05' ); //34.05
	}

	function testMI_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '24.15' ); //24.15
	}

	//
	// GA
	//
	function testGA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '34.23' ); //34.23
	}

	function testGA_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2(3); //Employee/Spouse
		$pd_obj->setUserValue3(3); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '8.08' ); //8.08
	}

	function testGA_2006a_BiWeekly_Single_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '212.08' ); //212.08
	}

	function testGA_2006a_BiWeekly_MarriedSeparate_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married Separately
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.38' ); //38.38
	}

	function testGA_2006a_BiWeekly_MarriedOneIncome_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married OneIncome
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '29.92' ); //29.92
	}

	function testGA_2006a_BiWeekly_MarriedTwoIncome_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //Married OneIncome
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.38' ); //38.38
	}

	function testGA_2006a_BiWeekly_Head_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','GA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 50 ); //Head
		$pd_obj->setUserValue2(1); //Employee/Spouse
		$pd_obj->setUserValue3(1); //Dependant

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '31.54' ); //31.54
	}


	//
	// NJ
	//

	function testNJ_2006a_BiWeekly_RateA_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NJ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Rate A
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '15.38' ); //15.38
	}

	function testNJ_2006a_BiWeekly_RateB_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NJ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Rate B
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '15.38' ); //15.38
	}

	function testNJ_2006a_BiWeekly_RateC_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NJ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Rate B
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '15.96' ); //15.38
	}


	function testNJ_2006a_SemiMonthly_RateA_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NJ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Rate A
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '160.00' ); //160
	}

	function testNJ_2006a_SemiMonthly_RateD_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NJ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //Rate D
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '132.42' ); //132.42
	}

	//
	// NC
	//
	function testNC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '50.00' ); //50.00
	}

	function testNC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '51.00' ); //51.00
	}

	function testNC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '229.00' ); //229.00
	}

	//
	// VA
	//
	function testVA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 0 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.97' ); //38.97
	}

	function testVA_2006a_BiWeekly_BlindAllowance_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 2 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '35.43' ); //35.43
	}

	function testVA_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 4 ); //Allowance
		$pd_obj->setUserValue2( 4 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '195.79' ); //192.79
	}

	//
	// MA
	//
/*
	function testMA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '45.00' ); //45.00
	}

	function testMA_2006a_BiWeekly_Head_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Head of Household
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '40.72' ); //40.72
	}

	function testMA_2006a_BiWeekly_Blind_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Blind
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '40.51' ); //40.51
	}

	function testMA_2006a_SemiMonthly_Single_HighIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Regular
		$pd_obj->setStateAllowance( 4 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '601.08' ); //601.08
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '183.94' ); //183.94
	}

	function testMA_2006a_SemiMonthly_Blind_HighIncome() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MA');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Regular
		$pd_obj->setStateAllowance( 4 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '601.08' ); //601.08
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '179.08' ); //179.08
	}
*/
	//
	// IN
	//
	function testIN_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IN');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 1 ); //Dependant Allowance

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '30.73' ); //30.73
	}


	function testIN_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IN');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 4 ); //Allowance
		$pd_obj->setUserValue2( 4 ); //Dependant allowance

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '121.83' ); //121.83
	}

	//
	// IN - Counties
	//
	function testIN_ALL_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IN', 'ALL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 1 ); //Dependant Allowance
		$pd_obj->setUserValue3( 1.25 ); //County Rate

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '30.73' ); //30.73
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '11.30' ); //11.30
	}

	//
	// AZ
	//
	function testAZ_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AZ');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 10 ); //Percent of Federal

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1250.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1250.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '139.04' ); //139.04
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '125.00' ); //13.90
	}

	//
	// MD
	//
	function testMD_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MD', 'ALL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 7.68 ); //County Tax Percent - Allegany
		$pd_obj->setUserValue2( 1 ); //Allowances

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '70.89' ); //70.89
	}

	function testMD_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MD', 'ALL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 7.37 ); //County Tax Percent - Dorchester
		$pd_obj->setUserValue2( 8 ); //Allowances

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ), '288.66' ); //288.66
	}

	//
	// WI
	//
	function testWI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '51.95' ); //51.92
	}

	function testWI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '49.11' ); //49.08
	}

	function testWI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '245.28' ); //245.25
	}

	//
	// MN
	//
	function testMN_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MN');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '43.13' ); //43.13
	}

	function testMN_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MN');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '34.05' ); //34.05
	}

	function testMN_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MN');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '165.15' ); //165.15
	}

	//
	// CO
	//
	function testCO_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '37.00' ); //37.13
	}

	function testCO_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '28.00' ); //27.96
	}

	function testCO_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CO');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '121.00' ); //120.77
	}

	//
	// AL
	//
/*
	function testAL_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); // State "S"
		$pd_obj->setUserValue2( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.58' ); //120.58
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.09' ); //36.09
	}

	function testAL_2006a_BiWeekly_Single_MediumIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 2083 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.70' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '75.88' );
	}


	function testAL_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setUserValue2( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.58' ); //120.58
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '28.78' ); //26.86
	}

	function testAL_2006a_BiWeekly_Married_MediumIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setUserValue2( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 2083 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.70' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '60.05' );
	}

	function testAL_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AL');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setUserValue2( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '137.98' ); //135.90
	}
*/
	//
	// SC
	//
	function testSC_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','SC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '346.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '13.07' ); //13.07
	}

	function testSC_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','SC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '58.46' ); //58.46
	}

	function testSC_2006a_SemiMonthly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','SC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '253.21' ); //253.21
	}

	//
	// KY
	//
	function testKY_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '346.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '8.90' ); //8.90
	}

	function testKY_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '46.53' ); //46.53
	}

	function testKY_2006a_SemiMonthly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KY');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '220.24' ); //220.33
	}

	//
	// OR
	//
	function testOR_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '73.87' ); //73.88
	}

	function testOR_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 3 ); //Should switch to married tax tables.

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '43.41' ); //42.82
	}

	function testOR_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '55.25' ); //55.05
	}

	function testOR_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '270.46' ); //266.91
	}

	//
	// OK
	//
	function testOK_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OK');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '46.00' ); //46.00
	}

	function testOK_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OK');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '31.00' ); //31.00
	}

	function testOK_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','OK');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '198.00' ); //198.00
	}

	//
	// CT
	//
	function testCT_2006a_BiWeekly_StatusA_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '20.08' ); //20.08
	}

	function testCT_2006a_BiWeekly_StatusB_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //"B"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '3.63' ); //3.63
	}

	function testCT_2006a_BiWeekly_StatusC_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //"C"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0.58' ); //0.58
	}

	function testCT_2006a_BiWeekly_StatusD_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //"D"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '42.31' ); //42.31
	}

	function testCT_2006a_BiWeekly_StatusE_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 50 ); //"E"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0.00' ); //0.00
	}

	function testCT_2006a_BiWeekly_StatusF_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 60 ); //"F"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '16.96' ); //16.75
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeA() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1500.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1500.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '60.00' ); //60.00
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '82.50' ); //82.50
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeC() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 2500.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2500.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '116.67' ); //116.67
	}

	function testCT_2006a_BiWeekly_StatusA_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','CT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '191.67' ); //191.67
	}

	//
	// IA
	//
	function testIA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IA');
		$pd_obj->setDate(strtotime('02-Apr-06')); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.09' ); //38.09
	}

	function testIA_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IA');
		$pd_obj->setDate(strtotime('02-Apr-06')); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.55' ); //36.55
	}

	function testIA_2006a_BiWeekly_Single_LowIncomeC() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IA');
		$pd_obj->setDate(strtotime('02-Apr-06')); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '29.03' ); //29.03
	}

	function testIA_2006a_BiWeekly_Single_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','IA');
		$pd_obj->setDate(strtotime('02-Apr-06')); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '201.85' ); //201.85
	}

	//
	// MS
	//
	function testMS_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '39.81' ); //39.81
	}

	function testMS_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Spouse doesn't work
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '35.38' ); //35.38
	}

	function testMS_2006a_BiWeekly_LowIncomeC() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //HoH
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '37.69' ); //37.69
	}

	function testMS_2006a_SemiMonthly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 6000 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '176.46' ); //176.46
	}

	//
	// AR
	//
	function testAR_2006a_BiWeekly_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '39.23' ); //39.23
	}

	function testAR_2006a_BiWeekly_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.92' ); //36.92
	}

	function testAR_2006a_BiWeekly_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','AR');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Month

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '243.75' ); //243.75
	}

	//
	// KS
	//
	function testKS_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '39.42' ); //39.42
	}

	function testKS_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '23.89' ); //23.89
	}

	function testKS_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','KS');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '154.13' ); //154.13
	}

	//
	// NM
	//
	function testNM_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NM');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.12' ); //36.12
	}

	function testNM_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NM');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '14.83' ); //14.71
	}

	function testNM_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NM');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '118.24' ); //116.47
	}

	//
	// WV
	//
	function testWV_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WV');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.00' ); //36.00
	}

	function testWV_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WV');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Two Earners
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.00' ); //36.00
	}

	function testWV_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','WV');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '189.00' ); //189
	}

	//
	// NE
	//
	function testNE_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '39.97' ); //39.97
	}
/*
	function testNE_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '26.08' ); //26.08
	}

	function testNE_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','NE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '195.77' ); //195.77
	}
*/
	//
	// ID
	//
	function testID_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ID');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '61.00' ); //61.00
	}

	function testID_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ID');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '30.00' ); //30.00
	}

	function testID_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ID');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '182.00' ); //182.00
	}

	//
	// ME
	//
	function testME_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ME');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '54.00' ); //54.00
	}

	function testME_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ME');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '21.00' ); //21.00
	}

	function testME_2006a_BiWeekly_Married_LowIncomeB() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ME');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Two incomes
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '43.00' ); //43.00
	}

	function testME_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ME');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '188.00' ); //188.00
	}

	//
	// HI
	//
	function testHI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','HI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '60.92' ); //60.92
	}

	function testHI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','HI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '46.20' ); //46.20
	}

	function testHI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','HI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '244.99' ); //244.99
	}

	//
	// RI
	//
	function testRI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','RI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '33.68' ); //33.68
	}

	function testRI_2006a_BiWeekly_Single_HighIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','RI');
		$pd_obj->setDate(strtotime('01-Jul-06'));
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 5833.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5833' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '1125.83' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '312.71' );
	}

	function testRI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','RI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '23.58' ); //23.58
	}

	function testRI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','RI');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '121.11' ); //121.11
	}

	//
	// MT
	//
	function testMT_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '44.00' ); //44.00
	}

	function testMT_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','MT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '184.00' ); //184.00
	}

	//
	// DE
	//
	function testDE_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '34.00' ); //34.00
	}

	function testDE_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '23.35' ); //23.35
	}

	function testDE_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DE');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '167.17' ); //167.17
	}

	//
	// ND
	//
	function testND_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ND');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '18.17' ); //18.17
	}

	function testND_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ND');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '11.47' ); //11.47
	}

	function testND_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','ND');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '62.34' ); //62.34
	}

	//
	// VT
	//
	function testVT_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '32.33' ); //32.33
	}

	function testVT_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '20.35' ); //20.35
	}

	function testVT_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','VT');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '111.60' ); //111.60
	}

	//
	// DC
	//
	function testDC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '56.06' ); //56.06
	}

	function testDC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '54.18' ); //54.18
	}

	function testDC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('US','DC');
		$pd_obj->setDate(strtotime('01-Jan-06'));
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '263.41' ); //263.41
	}
}
?>
