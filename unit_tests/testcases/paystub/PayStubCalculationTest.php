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

require_once('PHPUnit/Framework/TestCase.php');

class PayStubCalculationTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);
		
		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);

		$dd->createCurrency( $this->company_id, 10 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id );
		$this->createCompanyDeductions();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Create policies
		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Regular
		$this->policy_ids['pay_formula_policy'][110] = $dd->createPayFormulaPolicy( $this->company_id, 110 ); //Vacation
		$this->policy_ids['pay_formula_policy'][120] = $dd->createPayFormulaPolicy( $this->company_id, 120 ); //Bank
		$this->policy_ids['pay_formula_policy'][130] = $dd->createPayFormulaPolicy( $this->company_id, 130 ); //Sick
		$this->policy_ids['pay_formula_policy'][200] = $dd->createPayFormulaPolicy( $this->company_id, 200 ); //OT1.5
		$this->policy_ids['pay_formula_policy'][210] = $dd->createPayFormulaPolicy( $this->company_id, 210 ); //OT2.0
		$this->policy_ids['pay_formula_policy'][300] = $dd->createPayFormulaPolicy( $this->company_id, 300 ); //Prem1
		$this->policy_ids['pay_formula_policy'][310] = $dd->createPayFormulaPolicy( $this->company_id, 310 ); //Prem2

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][190] = $dd->createPayCode( $this->company_id, 190, $this->policy_ids['pay_formula_policy'][100] ); //Lunch
		$this->policy_ids['pay_code'][192] = $dd->createPayCode( $this->company_id, 192, $this->policy_ids['pay_formula_policy'][100] ); //Break
		$this->policy_ids['pay_code'][200] = $dd->createPayCode( $this->company_id, 200, $this->policy_ids['pay_formula_policy'][200] ); //OT1
		$this->policy_ids['pay_code'][210] = $dd->createPayCode( $this->company_id, 210, $this->policy_ids['pay_formula_policy'][210] ); //OT2
		$this->policy_ids['pay_code'][300] = $dd->createPayCode( $this->company_id, 300, $this->policy_ids['pay_formula_policy'][300] ); //Prem1
		$this->policy_ids['pay_code'][310] = $dd->createPayCode( $this->company_id, 310, $this->policy_ids['pay_formula_policy'][310] ); //Prem2
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900, $this->policy_ids['pay_formula_policy'][110] ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $this->policy_ids['pay_formula_policy'][120] ); //Bank
		$this->policy_ids['pay_code'][920] = $dd->createPayCode( $this->company_id, 920, $this->policy_ids['pay_formula_policy'][130] ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, array( $this->policy_ids['pay_code'][100] ) ); //Regular
		$this->policy_ids['contributing_pay_code_policy'][12] = $dd->createContributingPayCodePolicy( $this->company_id, 12, array( $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ) ); //Regular+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][14] = $dd->createContributingPayCodePolicy( $this->company_id, 14, array( $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][900] ) ); //Regular+Meal/Break+Absence
		$this->policy_ids['contributing_pay_code_policy'][20] = $dd->createContributingPayCodePolicy( $this->company_id, 20, array( $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][200], $this->policy_ids['pay_code'][210], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ) ); //Regular+OT+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][90] = $dd->createContributingPayCodePolicy( $this->company_id, 90, array( $this->policy_ids['pay_code'][900] ) ); //Absence
		$this->policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $this->policy_ids['pay_code'] ); //All Time

		$this->policy_ids['contributing_shift_policy'][12] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][20] = $dd->createContributingShiftPolicy( $this->company_id, 20, $this->policy_ids['contributing_pay_code_policy'][20] ); //Regular+OT+Meal/Break

		$this->policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][200] );
		$this->policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][210] );

		$this->policy_ids['premium'][] = $dd->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][20], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][] = $dd->createPremiumPolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][20], $this->policy_ids['pay_code'][310] );

		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									$this->policy_ids['overtime'], //OT
									$this->policy_ids['premium'], //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$this->createPunchData();

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);

		//$this->deleteAllSchedules();

        return TRUE;
    }

	function deleteUserWage( $user_id ) {
		$uwlf = TTnew( 'UserWageListFactory' );
		$uwlf->getByUserId( $user_id );
		if ( $uwlf->getRecordCount() > 0 ) {
			foreach ($uwlf as $uw_obj ) {
				$uw_obj->setDeleted(TRUE);
				if ( $uw_obj->isValid() ) {
					$uw_obj->Save();
				}
			}
		}
		return TRUE;
	}
	
	function createUserSalaryWage( $user_id, $rate, $effective_date, $wage_group_id = 0 ) {
		$uwf = TTnew( 'UserWageFactory' );

		$uwf->setUser( $user_id );
		$uwf->setWageGroup( $wage_group_id );
		$uwf->setType( 13 ); //BiWeekly
		$uwf->setWage(	$rate );
		$uwf->setWeeklyTime( ( 3600 * 40 ) );
		$uwf->setHourlyRate( 10.00 );
		$uwf->setEffectiveDate( $effective_date );

		if ( $uwf->isValid() ) {
			$insert_id = $uwf->Save();
			Debug::Text('User Wage ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating User Wage!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = array(
			'total_gross' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross'),
			'total_deductions' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions'),
			'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Employer Total Contributions'),
			'net_pay' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Net Pay'),
			'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
            'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation Accrual Release'),
			'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
            'cpp' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP'),
            'ei' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI'),
            'advanced_percent_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 2'),
            'advanced_percent_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 1'),
            'other2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other2'),
            'other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other'),
            
            
            );

		return TRUE;
	}

	function createPayStubAccounts() {
		Debug::text('Saving.... Employee Deduction - Other', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other');
		$pseaf->setOrder(290);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - Other2', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other2');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}
        
        Debug::text('Saving.... Employee Deduction - Custom1', __FILE__, __LINE__, __METHOD__, 10);
        $pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Custom1');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}
        
        Debug::text('Saving.... Employee Deduction - Custom2', __FILE__, __LINE__, __METHOD__, 10);
        $pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Custom2');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}
        

		Debug::text('Saving.... Employee Deduction - Advanced Percent 1', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Advanced Percent 1');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}
		Debug::text('Saving.... Employee Deduction - Advanced Percent 2', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Advanced Percent 2');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - EI', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('EI');
		$pseaf->setOrder(292);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - CPP', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('CPP');
		$pseaf->setOrder(293);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		/*
		//Do this in createPayStubEntryAccountLink() instead, otherwise we have to deal with multiple account link records.
		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() == 1 ) {
			$pseal_obj = $pseallf->getCurrent();
			Debug::text('PayStubEntryAccountLink ID: '. $pseal_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
			$pseal_obj->Save();
		} else {
			Debug::text('zzzPayStubEntryAccountLink ID: FAILED!', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		return TRUE;
	}

	function createCompanyDeductions() {

		//Test Wage Base amount
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Union Dues' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Union Dues') );
		$cdf->setUserValue1( 1 ); //10%
		$cdf->setUserValue2( 3000 );

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		//Test Wage Exempt Amount
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Union Dues2' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other') );
		$cdf->setUserValue1( 10 ); //10%
		//$cdf->setUserValue2( 0 );
		$cdf->setUserValue3( 78000 ); //Annual

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		//Test Advanced Percent Calculation maximum amount.
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Test Advanced Percent 1' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 1') );
		$cdf->setUserValue1( 1 ); //1%
		$cdf->setUserValue2( 2000 ); //Wage Base

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['regular_time'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}
		//Test Advanced Percent Calculation maximum amount.
		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'Test Advanced Percent 2' );
		$cdf->setCalculation( 15 );
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 2') );
		$cdf->setUserValue1( 1 ); //1%
		$cdf->setUserValue2( 2500 ); //Wage Base

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['regular_time'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}
        
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 30 );
			$cdf->setName( 'Test Custom Formula' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other2') );
			$cdf->setCompanyValue1( "(#custom_value1#+#custom_value2#+#custom_value3#+#custom_value4#+#custom_value5#+#custom_value6#+#custom_value7#+#custom_value8#+#custom_value9#+#custom_value10#)/100" );
			$cdf->setUserValue1( 10 );
			$cdf->setUserValue2( 20 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40 );
			$cdf->setUserValue5( 50 );
			$cdf->setUserValue6( 60 );
			$cdf->setUserValue7( 70 );
			$cdf->setUserValue8( 80 );
			$cdf->setUserValue9( 90 );
			$cdf->setUserValue10( 100 );

			if ( $cdf->isValid() ) {
				$cdf->Save(FALSE);

				$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_deductions'] ) );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}

			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 20 );
			$cdf->setName( 'Test Custom Formula 1' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Custom1') );
			$cdf->setCompanyValue1( "(#custom_value1#*((#custom_value2#+#custom_value3#)*#custom_value4#/#custom_value5#)+#custom_value6#/(#custom_value7#+#custom_value8#)+#custom_value9#+#custom_value10#)/100" );
			$cdf->setUserValue1( 45 );
			$cdf->setUserValue2( 20 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40 );
			$cdf->setUserValue5( 78.12 );
			$cdf->setUserValue6( 60 );
			$cdf->setUserValue7( 44.34 );
			$cdf->setUserValue8( 33 );
			$cdf->setUserValue9( 90 );
			$cdf->setUserValue10( 8 );

			if ( $cdf->isValid() ) {
				$cdf->Save(FALSE);

				$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_deductions'] ) );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}

			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setStatus( 10 );
			$cdf->setType( 20 );
			$cdf->setName( 'Test Custom Formula 2' );
			$cdf->setCalculation( 69 );
			$cdf->setCalculationOrder( 80 );
			$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Custom2') );
			$cdf->setCompanyValue1( "values(n) = (#custom_value1#+#custom_value2#+#custom_value3#+#custom_value4#+#custom_value5#+#custom_value6#+#custom_value7#+#custom_value8#+#custom_value9#+#custom_value10#)/n
									include_amounts(n)=(#include_pay_stub_amount#+#include_pay_stub_ytd_amount#)/n
									exclude_amounts(n)=(#exclude_pay_stub_amount#+#exclude_pay_stub_ytd_amount#)/n
									(values(2)+include_amounts(3)+exclude_amounts(4)+#employee_hourly_rate#)/100" );
			$cdf->setUserValue1( 0.23 );
			$cdf->setUserValue2( 1114.65 );
			$cdf->setUserValue3( 30 );
			$cdf->setUserValue4( 40.55 );
			$cdf->setUserValue5( 55.55 );
			$cdf->setUserValue6( 32.33 );
			$cdf->setUserValue7( 44.34 );
			$cdf->setUserValue8( 21 );
			$cdf->setUserValue9( 47 );
			$cdf->setUserValue10( 8 );
			if ( $cdf->isValid() ) {
				$cdf->Save(FALSE);

				$cdf->setIncludePayStubEntryAccount( array(
														//$this->pay_stub_account_link_arr['total_deductions'],
														//$this->pay_stub_account_link_arr['employer_contribution'],
														$this->pay_stub_account_link_arr['regular_time'],
														$this->pay_stub_account_link_arr['vacation_accrual'],
														$this->pay_stub_account_link_arr['advanced_percent_1'],
														$this->pay_stub_account_link_arr['cpp'],
														$this->pay_stub_account_link_arr['ei'],
													) );

				$cdf->setExcludePayStubEntryAccount( array(
														//$this->pay_stub_account_link_arr['vacation_accrual_release'],
														$this->pay_stub_account_link_arr['total_gross'],
														//$this->pay_stub_account_link_arr['other2'],
													) );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}
		}

		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'EI - Employee' );
		$cdf->setCalculation( 91 ); //EI Formula
		$cdf->setCalculationOrder( 90 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		$cdf = new CompanyDeductionFactory();
		$cdf->setCompany( $this->company_id );
		$cdf->setStatus( 10 ); //Enabled
		$cdf->setType( 10 ); //Tax
		$cdf->setName( 'CPP - Employee' );
		$cdf->setCalculation( 90 ); //CPP Formula
		$cdf->setCalculationOrder( 91 );
		$cdf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			$cdf->setIncludePayStubEntryAccount( array( $this->pay_stub_account_link_arr['total_gross'] ) );

			if ( $cdf->isValid() ) {
				$cdf->Save();
			}
		}

		return TRUE;
	}

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch() ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( (4*3600) );
		$ppsf->setMaximumShiftTime( (16*3600) );

		$ppsf->setEnableInitialPayPeriods( FALSE );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save(FALSE);
			Debug::Text('Pay Period Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			$ppsf->setUser( array($this->user_id) );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;

	}

	function createPayPeriods() {
		$max_pay_periods = 5;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-06') );
				} else {
					$end_date = $end_date + ( (86400*14) );
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);
				
				$pps_obj->createNextPayPeriod( $end_date , (86400*3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}

		}

		return TRUE;
	}

	function getAllPayPeriods() {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $this->pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				Debug::text('Pay Period... Start: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return TRUE;
	}

	function createPayStubAccrualAccount() {
		Debug::text('Saving.... Vacation Accrual', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(50);
		$pseaf->setName('Vacation Accrual');
		$pseaf->setOrder(400);

		if ( $pseaf->isValid() ) {
			$vacation_accrual_id = $pseaf->Save();

			Debug::text('Saving.... Earnings - Vacation Accrual Release', __FILE__, __LINE__, __METHOD__, 10);
			$pseaf = new PayStubEntryAccountFactory();
			$pseaf->setCompany( $this->company_id );
			$pseaf->setStatus(10);
			$pseaf->setType(10);
			$pseaf->setName('Vacation Accrual Release');
			$pseaf->setOrder(180);
			$pseaf->setAccrual($vacation_accrual_id);

			if ( $pseaf->isValid() ) {
				$pseaf->Save();
			}

			//unset($vaction_accrual_id);

			//Don't need this because we are doing it manually.
			Debug::text('Saving.... Vacation Accrual Deduction', __FILE__, __LINE__, __METHOD__, 10);
			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $this->company_id );
			$cdf->setStatus( 10 ); //Enabled
			$cdf->setType( 20 ); //Deduction
			$cdf->setName( 'Vacation Accrual' );
			$cdf->setCalculation( 10 );
			$cdf->setCalculationOrder( 50 );
			$cdf->setPayStubEntryAccount( $vacation_accrual_id );
			$cdf->setUserValue1( 4 );

			if ( $cdf->isValid() ) {
				Debug::text('bSaving.... Vacation Accrual Deduction', __FILE__, __LINE__, __METHOD__, 10);
				$cdf->Save(FALSE);

				$cdf->setIncludePayStubEntryAccount( array( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Total Gross') ) );

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}


		}

		return TRUE;
	}


	function getPayStubEntryArray( $pay_stub_id ) {
		//Check Pay Stub to make sure it was created correctly.
		$pself = new PayStubEntryListFactory();
		$pself->getByPayStubId( $pay_stub_id ) ;
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {
				$ps_entry_arr[$pse_obj->getPayStubEntryNameId()][] = array(
					'rate' => $pse_obj->getRate(),
					'units' => $pse_obj->getUnits(),
					'amount' => $pse_obj->getAmount(),
					'ytd_amount' => $pse_obj->getYTDAmount(),
					);
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return FALSE;
	}

	function createPunchData() {
		global $dd;

		$punch_date = $this->pay_period_objs[0]->getStartDate();
		$end_punch_date = $this->pay_period_objs[0]->getEndDate();
		$i=0;
		while ( $punch_date <= $end_punch_date ) {
			$date_stamp = TTDate::getDate('DATE', $punch_date );

			//$punch_full_time_stamp = strtotime($pc_data['date_stamp'].' '.$pc_data['time_stamp']);
			$dd->createPunchPair( 	$this->user_id,
										strtotime($date_stamp.' 08:00AM'),
										strtotime($date_stamp.' 11:00AM'),
										array(
												'in_type_id' => 10,
												'out_type_id' => 10,
												'branch_id' => 0,
												'department_id' => 0,
												'job_id' => 0,
												'job_item_id' => 0,
											)
									);
			$dd->createPunchPair( 	$this->user_id,
										strtotime($date_stamp.' 11:00AM'),
										strtotime($date_stamp.' 1:00PM'),
										array(
												'in_type_id' => 10,
												'out_type_id' => 20,
												'branch_id' => 0,
												'department_id' => 0,
												'job_id' => 0,
												'job_item_id' => 0,
											)
									);

			$dd->createPunchPair( 	$this->user_id,
										strtotime($date_stamp.' 2:00PM'),
										strtotime($date_stamp.' 6:00PM'),
										array(
												'in_type_id' => 20,
												'out_type_id' => 10,
												'branch_id' => 0,
												'department_id' => 0,
												'job_id' => 0,
												'job_item_id' => 0,
											)
									);

			$punch_date+=86400;
			$i++;
		}
		unset($punch_options_arr, $punch_date, $user_id);

	}

	function addPayStubAmendments() {
		//Regular FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 10 );
		$psaf->setUnits( 10 );

		$psaf->setDescription('Test Fixed PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//Regular percent PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Commission') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 20 );
		$psaf->setPercentAmount( 10 ); //10%
		$psaf->setPercentAmountEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time') );

		$psaf->setDescription('Test Percent PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}


		//Vacation Accrual Release percent PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation Accrual Release') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 20 );
		$psaf->setPercentAmount( 50 ); //50% - Leave some balance to check against.
		$psaf->setPercentAmountEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual') );

		$psaf->setDescription('Test Vacation Release Percent PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 2') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 1.99 );
		$psaf->setYTDAdjustment(TRUE);

		$psaf->setDescription('Test YTD PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Commission') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		//$psaf->setAmount( 0.09 );
		$psaf->setAmount( 1000 ); //Increase this so Union Dues are closer to the maximum earnings and are calculated to be less.
		$psaf->setYTDAdjustment(TRUE);

		$psaf->setDescription('Test YTD (2) PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Maximum EI contribution
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 700.00 );
		$psaf->setYTDAdjustment(TRUE);

		$psaf->setDescription('Test EI YTD PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Maximum CPP contribution
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 1900.00 );
		$psaf->setYTDAdjustment(TRUE);

		$psaf->setDescription('Test CPP YTD PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//YTD Adjustment FIXED PS amendment for testing Vacation Accrual totaling issues.
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setAmount( 99.01 );
		$psaf->setYTDAdjustment(TRUE);

		$psaf->setDescription('Test Vacation Accrual YTD PS Amendment');

		$psaf->setEffectiveDate( $this->pay_period_objs[0]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//
		// Add EARNING PS amendments for a pay period that has no Punch hours.
		// Include a regular time adjustment so we can test Wage Base amounts for some tax/deductions.

		//Regular FIXED PS amendment as regular time.
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 33.33 );
		$psaf->setUnits( 3 );

		$psaf->setDescription('Test Fixed PS Amendment (1)');

		$psaf->setEffectiveDate( $this->pay_period_objs[1]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		//Regular FIXED PS amendment as Bonus
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
		$psaf->setPayStubEntryNameId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus') );
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
		$psaf->setRate( 10 );
		$psaf->setUnits( 30 );

		$psaf->setDescription('Test Fixed PS Amendment (2)');

		$psaf->setEffectiveDate( $this->pay_period_objs[1]->getEndDate() );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		}

		return TRUE;
	}

	function createPayStub() {

		$cps = new CalculatePayStub();
		$cps->setUser( $this->user_id );
		$cps->setPayPeriod( $this->pay_period_objs[0]->getId() );
		$cps->calculate();

		//Pay stub for 2nd pay period
		$cps = new CalculatePayStub();
		$cps->setUser( $this->user_id );
		$cps->setPayPeriod( $this->pay_period_objs[1]->getId() );
		$cps->calculate();

		return TRUE;
	}

	function getPayStub( $pay_period_id = FALSE ) {
		if ( $pay_period_id == FALSE ) {
			$pay_period_id = $this->pay_period_objs[0]->getId();
		}

		$pslf = new PayStubListFactory();
		$pslf->getByUserIdAndPayPeriodId( $this->user_id, $pay_period_id );
		if ( $pslf->getRecordCount() > 0 ) {
			return $pslf->getCurrent()->getId();
		}

		return FALSE;
	}

	/**
	 * @group PayStubCalculation_testMain
	 */
	function testMain() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'premium_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 1'),
							'premium_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 2'),
							'bonus' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus'),
							'other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Commission'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'state_disability' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - Disability Insurance'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Medicare'),
							'union_dues' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Union Dues'),
							'advanced_percent_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 1'),
							'advanced_percent_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 2'),
							'deduction_other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other'),
							'ei' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'ei'),
							'cpp' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'cpp'),
							'employer_medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'employer_fica' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Social Security (FICA)'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub_id = $this->getPayStub();

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_accounts);
		//var_dump($pse_arr);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '2408.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '2408.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '451.50' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '451.50' );

		$this->assertEquals( $pse_arr[$pse_accounts['premium_1']][0]['amount'], '47.88' );
		$this->assertEquals( $pse_arr[$pse_accounts['premium_1']][0]['ytd_amount'], '47.88' );

		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['rate'], '10.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['units'], '10.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['amount'], '100.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'], '100.00' );

		//YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['other']][0]['amount'], '240.80' );
		$this->assertEquals( $pse_arr[$pse_accounts['other']][0]['ytd_amount'], '0.00' );
		//Fixed amount PS amendment
		$this->assertEquals( $pse_arr[$pse_accounts['other']][1]['amount'], '1000.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['other']][1]['ytd_amount'], '1240.80' );

		$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][0]['amount'], '10.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][1]['amount'], '1.99' );
		$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][1]['ytd_amount'], '11.99' );

		//Vacation accrual release
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '114.67' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '114.67' );

		//Vacation accrual deduction
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '99.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '130.33' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '-114.67' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '114.67' );

		//Union Dues - Should be 19.98 due to getting close to hitting Wage Base, because a YTD adjustment for Total Gross exists for around 1001.99.
		$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['amount'], '19.98' );
		$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'], '19.98' );

		//Advanced Percent
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'], '20.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'], '20.00' ); //Exceeds Wage Base

		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'], '24.08' );
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'], '24.08' ); //Not close to Wage Base.

		$this->assertEquals( $pse_arr[$pse_accounts['deduction_other']][0]['amount'], '37.29' );
		$this->assertEquals( $pse_arr[$pse_accounts['deduction_other']][0]['ytd_amount'], '37.29' );

		//EI
		$this->assertEquals( $pse_arr[$pse_accounts['ei']][0]['amount'], '700.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['ei']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['ei']][1]['amount'], '29.30' ); //HAS TO BE 29.30, as it reached maximum contribution.
		$this->assertEquals( $pse_arr[$pse_accounts['ei']][1]['ytd_amount'], '729.30' );

		//CPP
		$this->assertEquals( $pse_arr[$pse_accounts['cpp']][0]['amount'], '1900.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['cpp']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['cpp']][1]['amount'], '10.70' );
		$this->assertEquals( $pse_arr[$pse_accounts['cpp']][1]['ytd_amount'], '1910.70' );

		if ( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] >= 600
				AND $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] <= 800
				AND $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] == $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Federal Income Tax not within range! Amount: '. $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		}

		if ( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] >= 100
				AND $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] <= 300
				AND $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] == $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'State Income Tax not within range! Amount: '. $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		}

		if ( $pse_arr[$pse_accounts['medicare']][0]['amount'] >= 10
				AND $pse_arr[$pse_accounts['medicare']][0]['amount'] <= 100
				AND $pse_arr[$pse_accounts['medicare']][0]['amount'] == $pse_arr[$pse_accounts['medicare']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Medicare not within range!' );
		}

		if ( $pse_arr[$pse_accounts['state_disability']][0]['amount'] >= 2
				AND $pse_arr[$pse_accounts['state_disability']][0]['amount'] <= 50
				AND $pse_arr[$pse_accounts['state_disability']][0]['amount'] == $pse_arr[$pse_accounts['state_disability']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'State Disability not within range!' );
		}

		if ( $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] >= 10
				AND $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] <= 100
				AND $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] == $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Employer Medicare not within range!' );
		}

		if ( $pse_arr[$pse_accounts['employer_fica']][0]['amount'] >= 100
				AND $pse_arr[$pse_accounts['employer_fica']][0]['amount'] <= 250
				AND $pse_arr[$pse_accounts['employer_fica']][0]['amount'] == $pse_arr[$pse_accounts['employer_fica']][0]['amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Employer FICA not within range!' );
		}


		if ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] >= 3300
				AND $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] <= 3450
				AND ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount']+(1000+1.99) ) == $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Total Gross not within range!' );
		}

		if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 1300
				AND $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 1500
				AND ( bcadd($pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 2600)) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'Total Deductions not within range! Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		}

		if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 1800
				AND $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 2100
				AND bcsub($pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 1598.01) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE, 'NET PAY not within range!');
		}

		return TRUE;
	}

	/**
	 * @group PayStubCalculation_testMainCustomFormulas
	 */
	function testMainCustomFormulas() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$pse_accounts = array(
								'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
								'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
								'premium_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 1'),
								'premium_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 2'),
								'bonus' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus'),
								'other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Commission'),
								'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation Accrual Release'),
								'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
								'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
								'state_disability' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - Disability Insurance'),
								'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Medicare'),
								'union_dues' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Union Dues'),
								'advanced_percent_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 1'),
								'advanced_percent_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 2'),
								'deduction_other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other'),
								'ei' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'ei'),
								'cpp' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'cpp'),
								'employer_medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
								'employer_fica' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Social Security (FICA)'),
								'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
								'test_custom_formula' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other2'),
								'test_custom_formula_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Custom1'),
								'test_custom_formula_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Custom2'),
								);

			$pay_stub_id = $this->getPayStub();

			$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
			//var_dump($pse_accounts);
			//var_dump($pse_arr);

			$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '2408.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '2408.00' );

			$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '451.50' );
			$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '451.50' );

			$this->assertEquals( $pse_arr[$pse_accounts['premium_1']][0]['amount'], '47.88' );
			$this->assertEquals( $pse_arr[$pse_accounts['premium_1']][0]['ytd_amount'], '47.88' );

			$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['rate'], '10.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['units'], '10.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['amount'], '100.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'], '100.00' );

			//YTD adjustment
			$this->assertEquals( $pse_arr[$pse_accounts['other']][0]['amount'], '240.80' );
			$this->assertEquals( $pse_arr[$pse_accounts['other']][0]['ytd_amount'], '0.00' );
			//Fixed amount PS amendment
			$this->assertEquals( $pse_arr[$pse_accounts['other']][1]['amount'], '1000.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['other']][1]['ytd_amount'], '1240.80' );

			$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][0]['amount'], '10.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][0]['ytd_amount'], '0.00' );

			$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][1]['amount'], '1.99' );
			$this->assertEquals( $pse_arr[$pse_accounts['premium_2']][1]['ytd_amount'], '11.99' );

			//Vacation accrual release
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '114.67' );
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '114.67' );

			//Vacation accrual deduction
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '99.01' );
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '130.33' );
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '0.00' );

			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '-114.67' );
			$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '114.67' );

			//Union Dues - Should be 19.98 due to getting close to hitting Wage Base, because a YTD adjustment for Total Gross exists for around 1001.99.
			$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['amount'], '19.98' );
			$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'], '19.98' );

			//Advanced Percent
			$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'], '20.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'], '20.00' ); //Exceeds Wage Base

			$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'], '24.08' );
			$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'], '24.08' ); //Not close to Wage Base.

			$this->assertEquals( $pse_arr[$pse_accounts['deduction_other']][0]['amount'], '37.29' );
			$this->assertEquals( $pse_arr[$pse_accounts['deduction_other']][0]['ytd_amount'], '37.29' );

			//EI
			$this->assertEquals( $pse_arr[$pse_accounts['ei']][0]['amount'], '700.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['ei']][0]['ytd_amount'], '0.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['ei']][1]['amount'], '29.30' ); //HAS TO BE 29.30, as it reached maximum contribution.
			$this->assertEquals( $pse_arr[$pse_accounts['ei']][1]['ytd_amount'], '729.30' );

			//CPP
			$this->assertEquals( $pse_arr[$pse_accounts['cpp']][0]['amount'], '1900.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['cpp']][0]['ytd_amount'], '0.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['cpp']][1]['amount'], '10.70' );
			$this->assertEquals( $pse_arr[$pse_accounts['cpp']][1]['ytd_amount'], '1910.70' );

			//Custom formula deductions.
			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula']][0]['amount'], '5.50' );
			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula']][0]['ytd_amount'], '5.500' );

			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula_1']][0]['amount'], '12.51' );
			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula_1']][0]['ytd_amount'], '12.51' );

			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula_2']][0]['amount'], '35.40' );
			$this->assertEquals( $pse_arr[$pse_accounts['test_custom_formula_2']][0]['ytd_amount'], '35.40' );

			if ( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] >= 600
					AND $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] <= 800
					AND $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] == $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Federal Income Tax not within range! Amount: '. $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
			}

			if ( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] >= 100
					AND $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] <= 300
					AND $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] == $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'State Income Tax not within range! Amount: '. $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
			}

			if ( $pse_arr[$pse_accounts['medicare']][0]['amount'] >= 10
					AND $pse_arr[$pse_accounts['medicare']][0]['amount'] <= 100
					AND $pse_arr[$pse_accounts['medicare']][0]['amount'] == $pse_arr[$pse_accounts['medicare']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Medicare not within range!' );
			}

			if ( $pse_arr[$pse_accounts['state_disability']][0]['amount'] >= 2
					AND $pse_arr[$pse_accounts['state_disability']][0]['amount'] <= 50
					AND $pse_arr[$pse_accounts['state_disability']][0]['amount'] == $pse_arr[$pse_accounts['state_disability']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'State Disability not within range!' );
			}

			if ( $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] >= 10
					AND $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] <= 100
					AND $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] == $pse_arr[$pse_accounts['employer_medicare']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Employer Medicare not within range!' );
			}

			if ( $pse_arr[$pse_accounts['employer_fica']][0]['amount'] >= 100
					AND $pse_arr[$pse_accounts['employer_fica']][0]['amount'] <= 250
					AND $pse_arr[$pse_accounts['employer_fica']][0]['amount'] == $pse_arr[$pse_accounts['employer_fica']][0]['amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Employer FICA not within range!' );
			}


			if ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] >= 3300
					AND $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] <= 3450
					AND ( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount']+(1000+1.99) ) == $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Total Gross not within range!' );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 1300
					AND $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 1500
					AND ( bcadd($pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 2600)) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Total Deductions not within range! Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 1800
					AND $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 2100
					AND bcsub($pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 1598.01) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'NET PAY not within range!');
			}
		}

		return TRUE;
	}

	/**
	 * @group PayStubCalculation_testNoHoursPayStub
	 */
	function testNoHoursPayStub() {
		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'premium_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 1'),
							'premium_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Premium 2'),
							'bonus' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus'),
							'other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Commission'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'state_disability' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - Disability Insurance'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Medicare'),
							'union_dues' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Union Dues'),
							'advanced_percent_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 1'),
							'advanced_percent_2' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Advanced Percent 2'),
							'deduction_other' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'Other'),
							'ei' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'ei'),
							'cpp' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'cpp'),
							'employer_medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'employer_fica' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Social Security (FICA)'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);
		
		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['rate'], '33.33' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '99.99' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '2507.99' );

		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['rate'], '10.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['units'], '30.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['amount'], '300.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['bonus']][0]['ytd_amount'], '400.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['union_dues']][0]['ytd_amount'], '19.98' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '399.99' );

		//Check deductions.
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['amount'], '0.00' ); //Already Exceeded Wage Base, this should be 0!!
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_1']][0]['ytd_amount'], '20.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['amount'], '0.92' ); //Nearing Wage Base, this should be less than 1!!
		$this->assertEquals( $pse_arr[$pse_accounts['advanced_percent_2']][0]['ytd_amount'], '25.00' );

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 110
					AND $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 150
					AND ( bcadd($pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 3984.97)) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Total Deductions not within range! Total Deductions: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 225
					AND $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 290
					AND bcadd($pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 389.87) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'NET PAY not within range! Net Pay: '. $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount']);
			}
		} else {
			//If Community Edition without custom formulas these values all change.
			if ( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] >= 65
					AND $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] <= 80
					AND ( bcadd($pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], 3931.56)) == $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'Total Deductions not within range! Total Deductions: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
			}

			if ( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] >= 225
					AND $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] <= 350
					AND bcadd($pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], 443.28) == $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] ) {
				$this->assertTrue( TRUE );
			} else {
				$this->assertTrue( FALSE, 'NET PAY not within range! Net Pay: '. $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] .' YTD Amount: '. $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount']);
			}
		}

		return TRUE;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubA
	 */
	//Test basic salary calculation.
	function testSalaryPayStubA() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime('01-Jan-2001') );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[0]->getStartDate()-(86400) ) );

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							);

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'], '0.00' ); //MySQL returns NULL, so make sure we cast to float.
		//$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '1000.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '1000.00' );

		$this->assertEquals( count($pse_arr[$pse_accounts['regular_time']]), 1 );

		
		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['rate'], '33.33' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '99.99' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '1099.99' );

		$this->assertEquals( count($pse_arr[$pse_accounts['regular_time']]), 1 );
		return TRUE;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubB
	 */
	//Test advanced pro-rating salary calculation.
	function testSalaryPayStubB() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime('01-Jan-2001') );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[0]->getStartDate()-(86400) ) );
		$this->createUserSalaryWage( $this->user_id, 1500, ( $this->pay_period_objs[0]->getStartDate()+(86400*4) ) );
		$this->createUserSalaryWage( $this->user_id, 2000, ( $this->pay_period_objs[0]->getStartDate()+(86400*8) ) );

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							);

		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[0]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][0]['rate'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '48.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '857.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][1]['rate'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['units'], '32.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '428.57' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '0.00' );

		$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][2]['rate'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][2]['units'], '32.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][2]['amount'], '285.71' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][2]['ytd_amount'], '1571.42' );

		$this->assertEquals( count($pse_arr[$pse_accounts['regular_time']]), 3 );


		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['rate'], '33.33' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '99.99' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '1671.41' );

		$this->assertEquals( count($pse_arr[$pse_accounts['regular_time']]), 1 );
		
		return TRUE;
	}

	/**
	 * @group PayStubCalculation_testSalaryPayStubC
	 */
	//Test advanced pro-rating salary calculation.
	function testSalaryPayStubC() {
		$this->deleteUserWage( $this->user_id );

		//First Wage Entry
		$this->createUserSalaryWage( $this->user_id, 1, strtotime('01-Jan-2001') );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()-(86400) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*1) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*2) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*3) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*4) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*5) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*6) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*7) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*8) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*9) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*10) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*11) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*12) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*13) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*14) ) );
		$this->createUserSalaryWage( $this->user_id, 1000, ( $this->pay_period_objs[1]->getStartDate()+(86400*15) ) );


		//Create one punch in the next pay period so we can test pro-rating without any regular time.
		global $dd;
		$date_stamp = TTDate::getDate('DATE', $this->pay_period_objs[1]->getStartDate() );
		$dd->createPunchPair( 	$this->user_id,
									strtotime($date_stamp.' 08:00AM'),
									strtotime($date_stamp.' 11:00AM'),
									array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										)
								);

		$this->addPayStubAmendments();
		$this->createPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							);

		//Just check the final pay stub.
		$pay_stub_id = $this->getPayStub( $this->pay_period_objs[1]->getId() );
		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );
		//var_dump($pse_arr);

		for( $i = 0; $i <= 12; $i++ ) {
			$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][$i]['rate'], '0.00' );
			$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][$i]['units'], '0.00' );
			$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][$i]['amount'], '71.43' );
			$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][$i]['ytd_amount'], '0.00' );
		}

		$this->assertEquals( (float)$pse_arr[$pse_accounts['regular_time']][13]['rate'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][13]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][13]['amount'], '71.43' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][13]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][14]['rate'], '33.33' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][14]['units'], '3.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][14]['amount'], '99.99' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][14]['ytd_amount'], '1172.37' );

		$this->assertEquals( count($pse_arr[$pse_accounts['regular_time']]), 15 );

		return TRUE;
	}

}
?>
