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
 * $Revision: 676 $
 * $Id: PayStubCalculationTest.php 676 2007-03-07 23:47:29Z ipso $
 * $Date: 2007-03-07 15:47:29 -0800 (Wed, 07 Mar 2007) $
 */
require_once('PHPUnit/Framework/TestCase.php');

class OverTimePolicyTest extends PHPUnit_Framework_TestCase {

	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

    public function __construct() {
        global $db, $cache, $profiler;
    }

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$this->absence_policy_id = $dd->createAbsencePolicy( $this->company_id, 10 );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);

		//$this->deleteAllSchedules();

        return TRUE;
    }

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = array(
			'total_gross' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross'),
			'total_deductions' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions'),
			'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Employer Total Contributions'),
			'net_pay' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Net Pay'),
			'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
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

		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
			$pseal_obj->Save();
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

		$anchor_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() )-(86400*(7*6) ) ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( (4*3600) );
		$ppsf->setMaximumShiftTime( (16*3600) );
		$ppsf->setShiftAssignedDay( 10 );

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
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();


			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					//$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-07') );
					$end_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() )-(86400*(7*6) ) ) );
				} else {
					$end_date = $end_date + ( (86400*14) );
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) , __FILE__, __LINE__, __METHOD__,10);

				$pps_obj->createNextPayPeriod( $end_date , (86400*3600) );
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

	function getCurrentPayPeriod( $epoch = NULL ) {
		if ( $epoch == '' ) {
			$epoch = time();
		}

		$this->getAllPayPeriods(); //This doesn't return the pay periods, just populates an array and returns TRUE.
		$pay_periods = $this->pay_period_objs;
		if ( is_array($pay_periods) ) {
			foreach( $pay_periods as $pp_obj ) {
				if ( $pp_obj->getStartDate() <= $epoch AND $pp_obj->getEndDate() >= $epoch ) {
					Debug::text('Current Pay Period... Start: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);

					return $pp_obj;
				}
			}
		}

		Debug::text('Current Pay Period not found! Epoch: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function createPunchData() {
		global $dd;

		$punch_date = $this->pay_period_objs[0]->getStartDate();
		$end_punch_date = $this->pay_period_objs[0]->getEndDate();
		$i=0;
		while ( $punch_date <= $end_punch_date ) {
			$date_stamp = TTDate::getDate('DATE', $punch_date );
			Debug::text('Creating Punch Data for: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);

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

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = array();

		//Get only system totals.
		$udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, $start_date, $end_date);
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach($udtlf as $udt_obj) {
				$user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

				$type_and_policy_id = $udt_obj->getType().(int)$udt_obj->getOverTimePolicyID();

				$date_totals[$user_date_stamp][] = array(
												'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
												'id' => $udt_obj->getId(),
												'user_date_id' => $udt_obj->getUserDateId(),
												'status_id' => $udt_obj->getStatus(),
												'type_id' => $udt_obj->getType(),
												'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
												'type_and_policy_id' => $type_and_policy_id,
												'branch_id' => (int)$udt_obj->getBranch(),
												'department_id' => $udt_obj->getDepartment(),
												'total_time' => $udt_obj->getTotalTime(),
												'name' => $udt_obj->getName(),
												//Override only shows for SYSTEM override columns...
												//Need to check Worked overrides too.
												'tmp_override' => $udt_obj->getOverride()
												);

			}
		}

		return $date_totals;
	}

	function createOverTimePolicy( $company_id, $type, $accrual_policy_id = NULL ) {
		$otpf = new OverTimePolicyFactory();
		$otpf->setCompany( $company_id );

		switch ( $type ) {
			//
			//Changing the OT rates will make a big difference is how these tests are calculated.
			//
			case 100:
				$otpf->setName( 'Daily (>8hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( (3600*8) );
				$otpf->setRate( '1.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 1') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );

				break;
			case 110:
				$otpf->setName( 'Daily (>9hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( (3600*9) );
				$otpf->setRate( '2.0' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 120:
				$otpf->setName( 'Daily (>10hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( (3600*10) );
				$otpf->setRate( '2.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 200:
				$otpf->setName( 'Weekly (>47hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*47) );
				$otpf->setRate( '1.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 210:
				$otpf->setName( 'Weekly (>59hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*59) );
				$otpf->setRate( '2.0' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 220:
				$otpf->setName( 'Weekly (>71hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*71) );
				$otpf->setRate( '2.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 230:
				$otpf->setName( 'Weekly (>31hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*31) );
				$otpf->setRate( '1.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 240:
				$otpf->setName( 'Weekly (>39hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*39) );
				$otpf->setRate( '2.0' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 250:
				$otpf->setName( 'Weekly (>47hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( (3600*47) );
				$otpf->setRate( '2.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;

			case 300:
				$otpf->setName( 'BiWeekly (>80hrs)' );
				$otpf->setType( 30 );
				$otpf->setTriggerTime( (3600*80) );
				$otpf->setRate( '1.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 310:
				$otpf->setName( 'BiWeekly (>84hrs)' );
				$otpf->setType( 30 );
				$otpf->setTriggerTime( (3600*84) );
				$otpf->setRate( '2.0' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 320:
				$otpf->setName( 'BiWeekly (>86hrs)' );
				$otpf->setType( 30 );
				$otpf->setTriggerTime( (3600*86) );
				$otpf->setRate( '2.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;

			case 500:
				$otpf->setName( 'Holiday' );
				$otpf->setType( 180 );
				$otpf->setTriggerTime( 0 );
				$otpf->setRate( '1.5' );
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;
			case 510:
				$otpf->setName( 'Holiday' );
				$otpf->setType( 180 );
				$otpf->setTriggerTime( 0 );
				$otpf->setRate( '4.0' ); //This should have the highest rate as it always takes precedance.
				$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				$otpf->setAccrualPolicyId( (int)$accrual_policy_id );
				$otpf->setAccrualRate( 1.0 );
				break;

		}

		if ( $otpf->isValid() ) {
			$insert_id = $otpf->Save();
			Debug::Text('Overtime Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Overtime Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function createHolidayPolicy( $company_id, $type ) {
		$hpf = new HolidayPolicyFactory();
		$hpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$hpf->setName( 'Default' );
				$hpf->setType( 10 );

				$hpf->setDefaultScheduleStatus( 10 );
				$hpf->setMinimumEmployedDays( 0 );
				$hpf->setMinimumWorkedPeriodDays( 0 );
				$hpf->setMinimumWorkedDays( 0 );
				$hpf->setAverageTimeDays( 10 );
				$hpf->setAverageTimeWorkedDays( TRUE );
				$hpf->setIncludeOverTime( TRUE );
				$hpf->setIncludePaidAbsenceTime( TRUE );
				$hpf->setForceOverTimePolicy( TRUE );

				$hpf->setMinimumTime( 0 );
				$hpf->setMaximumTime( 0 );

				$hpf->setAbsencePolicyID( $this->absence_policy_id );
				//$hpf->setRoundIntervalPolicyID( $data['round_interval_policy_id'] );

				break;
		}

		if ( $hpf->isValid() ) {
			$insert_id = $hpf->Save();
			Debug::Text('Holiday Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Holiday Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function createHoliday( $company_id, $type, $date, $holiday_policy_id ) {
		$hf = new HolidayFactory();

		switch ( $type ) {
			case 10:
				$hf->setHolidayPolicyId( $holiday_policy_id );
				$hf->setDateStamp( $date );
				$hf->setName( 'Test1' );

				break;
		}

		if ( $hf->isValid() ) {
			$insert_id = $hf->Save();
			Debug::Text('Holiday ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Holiday!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	/*
	 Tests:
		No Overtime
		Daily OverTime (3 levels)
		Weekly OverTime (3 Levels)
		BiWeekly OverTime (3 Levels)
		Combination Daily+Weekly (3 Levels)
		Combination Daily+Weekly+Holiday (3 Levels)
	*/
	function testNoOverTimePolicyA() {
		global $dd;

		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 110 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );

		return TRUE;
	}

	function testDailyOverTimePolicyA() {
		global $dd;

		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 110 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//print_r($policy_ids);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		return TRUE;
	}

	function testWeeklyOverTimePolicyA() {
		global $dd;

		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 200 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 210 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 220 );
		//print_r($policy_ids['overtime']);

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(1*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(3*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (11*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(4*86400+3601)) ;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (11*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1*3600) );

		//
		//Day of Week: 6
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(5*86400+3601) ) ;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (11*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1*3600) );

		return TRUE;
	}

	function testBiWeeklyOverTimePolicyA() {
		global $dd;

		//Test reaching the biweekly overtime in the first week, and part of it going into the second.
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 300 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 310 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 320 );
		//print_r($policy_ids['overtime']);

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		//Start two weeks ago...
		$start_epoch = $date_epoch = TTDate::getBeginWeekEpoch( time() );

		$current_pay_period_obj = $this->getCurrentPayPeriod( $date_epoch );
		if ( is_object($current_pay_period_obj) ) {
			$date_stamp = TTDate::getDate('DATE', $current_pay_period_obj->getStartDate() );
			$start_epoch = $date_epoch = TTDate::getBeginDayEpoch( $current_pay_period_obj->getStartDate() );
		} else {
			$date_stamp = TTDate::getDate('DATE', $date_epoch );
		}
		Debug::text('Using date stamp: '. TTDate::getDate('DATE+TIME', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(1*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(3*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(4*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );



		//
		//Day of Week: 1 - Beginning of next week...
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(5*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(6*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(7*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//print_r($policy_ids);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );

		//Overtime policies are sorted by id desc, so the we have to reverse the order.
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );


		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(8*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );

		//Overtime policies are sorted by id desc, so the we have to reverse the order.
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		return TRUE;
	}

	function testBiWeeklyOverTimePolicyB() {
		global $dd;

		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 300 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 310 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 320 );
		//print_r($policy_ids['overtime']);

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		//Start two weeks ago...
		$start_epoch = $date_epoch = TTDate::getBeginWeekEpoch( time() );

		$current_pay_period_obj = $this->getCurrentPayPeriod( $date_epoch );
		if ( is_object($current_pay_period_obj) ) {
			$date_stamp = TTDate::getDate('DATE', $current_pay_period_obj->getStartDate() );
			$start_epoch = $date_epoch = TTDate::getBeginDayEpoch( $current_pay_period_obj->getStartDate() );
		} else {
			$date_stamp = TTDate::getDate('DATE', $date_epoch );
		}
		Debug::text('Using date stamp: '. TTDate::getDate('DATE+TIME', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(1*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(3*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(4*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 6
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(5*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );


		//
		//Day of Week: 7
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(6*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );


		//
		//Day of Week: 1 - Beginning of next week...
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(7*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(8*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(9*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (14*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (14*3600) );

		return TRUE;
	}

	function testBiWeeklyOverTimePolicyC() {
		global $dd;

		//Test reaching the biweekly overtime just in the 2nd week.
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 300 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 310 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 320 );
		//print_r($policy_ids['overtime']);

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );

		//Start two weeks ago...
		$start_epoch = $date_epoch = TTDate::getBeginWeekEpoch( time() );

		$current_pay_period_obj = $this->getCurrentPayPeriod( $date_epoch );
		if ( is_object($current_pay_period_obj) ) {
			$date_stamp = TTDate::getDate('DATE', $current_pay_period_obj->getStartDate() + (7*86400+3601) );
			$start_epoch = $date_epoch = TTDate::getBeginDayEpoch( $current_pay_period_obj->getStartDate() + (7*86400+3601) );
		} else {
			$date_stamp = TTDate::getDate('DATE', $date_epoch );
		}
		Debug::text('Using date stamp: '. TTDate::getDate('DATE+TIME', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(1*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(3*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(4*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		//
		//Day of Week: 6
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(5*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );

		//
		//Day of Week: 7
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( $start_epoch )+(6*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (16*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (16*3600) );

		return TRUE;
	}

	function testDailyAndWeeklyOverTimePolicyA() {
		global $dd;

		//Daily
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 110 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 120 );

		//Weekly
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 230 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 240 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 250 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(1*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(3*86400+3601)) ;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Weekly Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][3] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(4*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Weekly Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][4] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Weekly Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][3] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Weekly Overtime 1
		//$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][4] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1*3600) );
		//Weekly Overtime 2
		//$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][3] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Overtime 1
		//$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['over_time_policy_id'], $policy_ids['overtime'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1*3600) );
		//Overtime 2
		//$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		//$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//
		//Day of Week: 6
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(5*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Weekly Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][5] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (4*3600) );
		//Weekly Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][4] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (6*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Weekly Overtime 2
		//$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][5] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1*3600) );
		//Weekly Overtime 3
		//$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][4] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Overtime 1
		//$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['over_time_policy_id'], $policy_ids['overtime'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1*3600) );
		//Overtime 2
		//$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		//$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		return TRUE;
	}

	function testHolidayAndDailyAndWeeklyOverTimePolicyA() {
		global $dd;

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		//Daily
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 110 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 120 );

		//Weekly
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 230 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 240 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 250 );

		//Holiday overtime policy
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 500 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );


		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Holiday Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 2
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(1*86400+3601) ) ;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 3
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(2*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 4
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(3*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//
		//Day of Week: 5
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(4*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Weekly Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][3] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['over_time_policy_id'], $policy_ids['overtime'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//
		//Day of Week: 6
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(5*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Weekly Overtime 1
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][4] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Weekly Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][3] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['over_time_policy_id'], $policy_ids['overtime'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//
		//Day of Week: 7
		//
		$date_epoch = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+(6*86400+3601) );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
 		//Weekly Overtime 2
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['over_time_policy_id'], $policy_ids['overtime'][5] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (4*3600) );
		//Weekly Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['over_time_policy_id'], $policy_ids['overtime'][4] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (6*3600) );
		//Overtime 3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['over_time_policy_id'], $policy_ids['overtime'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		return TRUE;
	}

	function testHolidayAndDailyOverTimePolicyA() {
		global $dd;

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		//Daily
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 110 );
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 120 );

		//Holiday overtime policy
		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 510 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									$policy_ids['overtime'],
									NULL,
									NULL,
									array($this->user_id) );


		//
		//Day of Week: 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Holiday Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 30 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );

		return TRUE;
	}
}
?>