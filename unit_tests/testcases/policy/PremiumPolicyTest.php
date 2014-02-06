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

class PremiumPolicyTest extends PHPUnit_Framework_TestCase {

	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;
	protected $branch_ids = NULL;
	protected $department_ids = NULL;

    public function __construct() {
        global $db, $cache, $profiler;
    }

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		TTDate::setTimeZone('PST8PDT');

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

		$this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

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


		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch() ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( (4*3600) );
		$ppsf->setMaximumShiftTime( (16*3600) );
		$ppsf->setShiftAssignedDay( 10 );
		//$ppsf->setContinuousTime( (4*3600) );

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
					$end_date = TTDate::getBeginYearEpoch( time() );
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

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = array();

		//Get only system totals.
		//$udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, $start_date, $end_date);
		$udtlf->getByCompanyIDAndUserIdAndStatusAndTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, array(10,20,40), $start_date, $end_date);
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
												'premium_policy_id' => $udt_obj->getPremiumPolicyID(),
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

	function createPremiumPolicy( $company_id, $type, $accrual_policy_id = NULL ) {
		$ppf = new PremiumPolicyFactory();
		$ppf->setCompany( $company_id );

		switch ( $type ) {
			case 90: //Basic Min/Max only.
				$ppf->setName( 'Min/Max Only' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( NULL );
				$ppf->setEndDate( NULL );

				$ppf->setStartTime( NULL );
				$ppf->setEndTime( NULL );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 7200 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 91: //Basic Min/Max only. as Advanced Type
				$ppf->setName( 'Min/Max Only' );
				$ppf->setType( 100 ); //Advanced Type.

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( NULL );
				$ppf->setEndDate( NULL );

				$ppf->setStartTime( NULL );
				$ppf->setEndTime( NULL );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 7200 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 100:
				$ppf->setName( 'Start/End Date Only' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( $this->pay_period_objs[0]->getStartDate()+86400 );
				$ppf->setEndDate( $this->pay_period_objs[0]->getStartDate()+(86400*3) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 110:
				$ppf->setName( 'Start/End Date+Effective Days' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( $this->pay_period_objs[0]->getStartDate()+86400 );
				$ppf->setEndDate( $this->pay_period_objs[0]->getStartDate()+(86400*3) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 1
							OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 1 ) {
					$ppf->setMon( TRUE );
				} else {
					$ppf->setMon( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 2
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 2) {
					$ppf->setTue( TRUE );
				} else {
					$ppf->setTue( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 3
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 3) {
					$ppf->setWed( TRUE );
				} else {
					$ppf->setWed( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 4
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 4) {
					$ppf->setThu( TRUE );
				} else {
					$ppf->setThu( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 5
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 5 ) {
					$ppf->setFri( TRUE );
				} else {
					$ppf->setFri( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 6
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 6) {
					$ppf->setSat( TRUE );
				} else {
					$ppf->setSat( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 0
						OR TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*3)) == 0) {
					$ppf->setSun( TRUE );
				} else {
					$ppf->setSun( FALSE );
				}

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 120:
				$ppf->setName( 'Time Based/Evening Shift w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('7:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 122:
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('6:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('3:00 AM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 123:
				$ppf->setName( 'Time Based/Weekend Day Shift w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('7:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('7:00 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( FALSE );
				$ppf->setTue( FALSE );
				$ppf->setWed( FALSE );
				$ppf->setThu( FALSE);
				$ppf->setFri( FALSE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 124: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('6:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('3:00 AM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 125: //Same as above type: 123, only Advanced type.
				$ppf->setName( 'Time Based/Weekend Day Shift w/Partial' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('7:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('7:00 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( FALSE );
				$ppf->setTue( FALSE );
				$ppf->setWed( FALSE );
				$ppf->setThu( FALSE );
				$ppf->setFri( FALSE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 126: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('10:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('12:00 AM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 127:
				$ppf->setName( 'Effective Days Only w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( FALSE );
				$ppf->setTue( FALSE );
				$ppf->setWed( FALSE );
				$ppf->setThu( FALSE);
				$ppf->setFri( FALSE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 20 ); //Always on holidays. This is key to test for a specific bug.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;

			case 130:
				$ppf->setName( 'Time Based/Evening Shift w/o Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('7:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( FALSE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 132:
				$ppf->setName( 'Time Based/Evening Shift w/o Partial+Span Midnight' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('6:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('3:00 AM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( FALSE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 140:
				$ppf->setName( 'Daily Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( (3600*5) );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 150:
				$ppf->setName( 'Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( (3600*9) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 160:
				$ppf->setName( 'Daily+Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( (3600*3) );
				$ppf->setWeeklyTriggerTime( (3600*9) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 170:
				$ppf->setName( 'Time+Daily+Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('7:00 PM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( (3600*5) );
				$ppf->setWeeklyTriggerTime( (3600*9) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 200:
				$ppf->setName( 'Branch Differential' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 210:
				$ppf->setName( 'Branch/Department Differential' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 212:
				$ppf->setName( 'Branch/Department Differential w/Minimum' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 3600 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;

			case 300:
				$ppf->setName( 'Meal Break' );
				$ppf->setType( 30 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setDailyTriggerTime( (3600*5) );
				$ppf->setMaximumNoBreakTime( (3600*5) );
				$ppf->setMinimumBreakTime(  1800 );

				$ppf->setMinimumTime( 1800 );
				$ppf->setMaximumTime( 1800 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;

			case 350:
				$ppf->setName( 'Minimum Shift Time' );
				$ppf->setType( 50 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setMinimumShiftTime( (4*3600) );
				$ppf->setMinimumTimeBetweenShift( (8*3600) );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );

				//$ppf->setBranchSelectionType( 20 );
				break;
			case 351:
				$ppf->setName( 'Minimum Shift Time+Differential' );
				$ppf->setType( 50 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setMinimumShiftTime( (4*3600) );
				$ppf->setMinimumTimeBetweenShift( (8*3600) );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );

				break;
			case 400:
				$ppf->setName( 'Holiday (Basic)' );
				$ppf->setType( 90 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumNoBreakTime( 0 );
				//$ppf->setMinimumBreakTime(  0 );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 410:
				$ppf->setName( 'Start/End Date+Effective Days+Always Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( $this->pay_period_objs[0]->getStartDate()+86400 );
				$ppf->setEndDate( $this->pay_period_objs[0]->getStartDate()+(86400*3) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( FALSE );
				$ppf->setTue( FALSE );
				$ppf->setWed( FALSE );
				$ppf->setThu( FALSE );
				$ppf->setFri( FALSE );
				$ppf->setSat( FALSE );
				$ppf->setSun( FALSE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 20 ); //Always on holidays

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 412:
				$ppf->setName( 'Start/End Date+Effective Days+Never Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( $this->pay_period_objs[0]->getStartDate()+86400 );
				$ppf->setEndDate( $this->pay_period_objs[0]->getStartDate()+(86400*3) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 30 ); //Never on holidays

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 414:
				$ppf->setName( 'Weekly+Never Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( (3600*40) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 30 ); //Never on Holiday

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;

			case 500:
				$ppf->setName( 'Daily Before/After Time 8-10hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( (8*3600) );
				$ppf->setMaximumDailyTriggerTime( (10*3600) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 501:
				$ppf->setName( 'Daily Before/After Time 10-11hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( (10*3600) );
				$ppf->setMaximumDailyTriggerTime( (11*3600) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 510:
				$ppf->setName( 'Weekly Before/After Time 20-30hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( (20*3600) );
				$ppf->setMaximumWeeklyTriggerTime( (30*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 511:
				$ppf->setName( 'Weekly Before/After Time 30-40hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( (30*3600) );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;

			case 520:
				$ppf->setName( 'Daily After 8/Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( (8*3600) );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 521:
				$ppf->setName( 'Daily After 8/Weekly After 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( (8*3600) );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( (40*3600) );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 522:
				$ppf->setName( 'Daily Before 8/Weekly After 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( (8*3600) );
				$ppf->setWeeklyTriggerTime( (40*3600) );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 523:
				$ppf->setName( 'Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 524:
				$ppf->setName( 'Daily Before 8/Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( (8*3600) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setIncludeHolidayType( 10 ); //No effect.

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 600:
				$ppf->setName( 'Last second of day' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime('12:00 AM') );
				$ppf->setEndTime( TTDate::parseDateTime('11:59 PM') );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 1 ) {
					$ppf->setMon( TRUE );
				} else {
					$ppf->setMon( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 2 ) {
					$ppf->setTue( TRUE );
				} else {
					$ppf->setTue( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 3 ) {
					$ppf->setWed( TRUE );
				} else {
					$ppf->setWed( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 4 ) {
					$ppf->setThu( TRUE );
				} else {
					$ppf->setThu( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 5 ) {
					$ppf->setFri( TRUE );
				} else {
					$ppf->setFri( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 6 ) {
					$ppf->setSat( TRUE );
				} else {
					$ppf->setSat( FALSE );
				}
				if ( TTDate::getDayOfWeek($this->pay_period_objs[0]->getStartDate()+(86400*2)) == 0 ) {
					$ppf->setSun( TRUE );
				} else {
					$ppf->setSun( FALSE );
				}

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 700:
				$ppf->setName( 'Advanced Active After + Differential' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( (3600*8) );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );


				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 723: //Same as 724
				$ppf->setName( 'Advanced Weekly Before 40A + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );


				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 724: //Same as 723
				$ppf->setName( 'Advanced Weekly Before 40B + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );


				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 729:
				$ppf->setName( 'Advanced Daily Before 8/Weekly Before 40 + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( (8*3600) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( (40*3600) );

				$ppf->setMon( TRUE );
				$ppf->setTue( TRUE );
				$ppf->setWed( TRUE );
				$ppf->setThu( TRUE );
				$ppf->setFri( TRUE );
				$ppf->setSat( TRUE );
				$ppf->setSun( TRUE );

				$ppf->setIncludePartialPunch( TRUE );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setIncludeMealPolicy( TRUE );

				$ppf->setRate( 1.0 );
				$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );


				$ppf->setExcludeDefaultBranch( FALSE );
				$ppf->setExcludeDefaultDepartment( FALSE );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
		}

		if ( $ppf->isValid() ) {
			$insert_id = $ppf->Save(FALSE);
			Debug::Text('Premium Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			switch ( $type ) {
				case 200:
					Debug::Text('Post Save Data...', __FILE__, __LINE__, __METHOD__,10);
					$ppf->setBranch( array($this->branch_ids[0]) );
					break;
				case 210:
				case 351:
					Debug::Text('Post Save Data...', __FILE__, __LINE__, __METHOD__,10);
					$ppf->setBranch( array($this->branch_ids[0]) );
					$ppf->setDepartment( array($this->department_ids[0]) );
					break;
				case 700:
					Debug::Text('Post Save Data...', __FILE__, __LINE__, __METHOD__,10);
					$ppf->setBranch( array($this->branch_ids[0]) );
					$ppf->setDepartment( array($this->department_ids[0]) );
					break;
				case 723:
					Debug::Text('Post Save Data...', __FILE__, __LINE__, __METHOD__,10);
					$ppf->setBranch( array($this->branch_ids[0]) );
					$ppf->setDepartment( array($this->department_ids[0]) );
					break;
				case 724: //Same as 729.
				case 729:
					Debug::Text('Post Save Data...', __FILE__, __LINE__, __METHOD__,10);
					$ppf->setBranch( array($this->branch_ids[1]) );
					$ppf->setDepartment( array($this->department_ids[1]) );
					break;
			}

			Debug::Text('Post Save...', __FILE__, __LINE__, __METHOD__,10);
			$ppf->Save();

			return $insert_id;
		}

		Debug::Text('Failed Creating Premium Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	/*
	 Tests:
		No Premium
		Min/Max time.
		Day Based
		Day Based+Effective Days
		Time Based w/No Partial punches
		Time Based w/Partial punches
		Daily Hour Based
		Weekly Hour Based
		Daily+Weekly Hour Based
		Time+Hour Based Premium
		Shift Differential Branch
		Shift Differential Department
		Shift Differential Branch+Department
		Shift Differential Job
		Shift Differential Task
		Shift Differential Job+Task
		Meal Break
		Advanced Time+Hour+Branch+Department+Job
	*/

	function testNoPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate();
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
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (0.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyC() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyD() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyE() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:15AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:30AM'),
								strtotime($date_stamp.' 8:45AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (0.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 900 );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 2700 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyF() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:30AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:00AM'),
								strtotime($date_stamp.' 11:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 1800 );

		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 5400 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyA2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (0.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyB2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyC2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyD2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyE2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:15AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:30AM'),
								strtotime($date_stamp.' 8:45AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (0.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 900 );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 2700 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testMinMaxPremiumPolicyF2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 8:30AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:00AM'),
								strtotime($date_stamp.' 11:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 1800 );

		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 5400 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testEffectiveDaysOnlyPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 127 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate();
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testEffectiveDaysOnlyPremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 127 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time = NONE

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}


	function testDatePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testEffectiveDatePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['premium_policy_id'], $policy_ids['premium'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00PM'),
								strtotime($date_stamp2.' 2:00AM'),
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyB2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00PM'),
								strtotime($date_stamp2.' 2:00AM'),
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyC() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00PM'),
								strtotime($date_stamp.' 5:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:30PM'),
								strtotime($date_stamp2.' 1:30AM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyC2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00PM'),
								strtotime($date_stamp.' 5:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:30PM'),
								strtotime($date_stamp2.' 1:30AM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyD() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 4:00AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyD2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 4:00AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyE() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00PM'),
								strtotime($date_stamp2.' 4:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (11*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (11*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyE2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00PM'),
								strtotime($date_stamp2.' 4:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (11*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (11*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyF() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 123 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'), //Friday evening
								strtotime($date_stamp2.' 9:00AM'), //Saturday morning.
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyF2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'), //Friday evening
								strtotime($date_stamp2.' 9:00AM'), //Saturday morning.
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	//Test creating punches in one timezone, then recalculating them in another timezone to make sure they are proper.
	function testTimeBasedPartialPremiumPolicyF3() {
		global $dd;

		TTDate::setTimeZone('PST8PDT');
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125 );
		
		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*6);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (12*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$udlf = TTNew('UserDateListFactory');
		$udlf->getByID( $udt_arr[$date_epoch][0]['user_date_id'] );
		unset($udt_arr);

		$user_date_id = $udlf->getCurrent()->getId();

		TTDate::setTimeZone('EST5EDT');
		$recalc_result = UserDateTotalFactory::reCalculateDay( $user_date_id, TRUE );
		TTDate::setTimeZone('PST8PDT');
		
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*6);
		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (12*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedPartialPremiumPolicyG() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 123 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*7);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'), //Sunday evening
								strtotime($date_stamp2.' 9:00AM'), //Monday morning.
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	function testTimeBasedPartialPremiumPolicyG2() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*7);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'), //Sunday evening
								strtotime($date_stamp2.' 9:00AM'), //Monday morning.
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (15*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedNoPartialPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 130 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testTimeBasedNoPartialPremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00PM'),
								strtotime($date_stamp.' 7:00PM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	function testTimeBasedNoPartialPremiumPolicyC() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:30PM'),
								strtotime($date_stamp.' 5:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00PM'),
								strtotime($date_stamp.' 10:00PM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	//Put a 5hr gap between the two punch pairs to signify a new shift starting, so premium does kick in.
	function testTimeBasedNoPartialPremiumPolicyD() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testTimeBasedNoPartialPremiumPolicyE() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 5:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00AM'),
								strtotime($date_stamp.' 11:00AM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testTimeBasedNoPartialPremiumPolicyF() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch+86400 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00PM'),
								strtotime($date_stamp2.' 3:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 5:00AM'),
								strtotime($date_stamp2.' 9:00AM'),
								array(
											'in_type_id' => 20,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}


	function testDailyHourPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 140 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testWeeklyHourPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 150 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testDailyWeeklyHourPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 160 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testTimeDailyWeeklyHourPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 170 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testBranchDifferentialPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 200 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 4:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time 1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (3*3600) );
		//Premium Time 2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (3*3600) );
		//Premium Time 3
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 6 );

		return TRUE;
	}

	function testBranchDepartmentDifferentialPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 210 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 4:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time 1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (3*3600) );
		//Premium Time 2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (3*3600) );
		//Premium Time 3
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 6 );

		return TRUE;
	}

	function testBranchDepartmentDifferentialPremiumPolicyB() {
		global $dd;

		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 212 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 4:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time 1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testMealPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 1800 );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testMealPremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:45PM'),
								strtotime($date_stamp.' 3:45PM'),
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

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (3*3600) );

		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4*3600) );
		//Premium Time4
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 6 );

		return TRUE;
	}

	function testMinimumShiftTimeA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 350 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 8:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00AM'),
								strtotime($date_stamp.' 1:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (4*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:30PM'),
								strtotime($date_stamp.' 11:30PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );


		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testMinimumShiftTimeB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 351 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 8:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00AM'),
								strtotime($date_stamp.' 1:30PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (4.5*3600) );
		//Regular Time1
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Regular Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:30PM'),
								strtotime($date_stamp.' 11:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6.5*3600) );
		//Regular Time1
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );


		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		//$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testHolidayPremiumPolicyA() {
		global $dd;

		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 400 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:45PM'),
								strtotime($date_stamp.' 3:45PM'),
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

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testHolidayPremiumPolicyB() {
		global $dd;

		$date_epoch1 = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp1 = TTDate::getDate('DATE', $date_epoch1 );

		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 400 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp1.' 6:00PM'),
								strtotime($date_stamp.' 2:00AM'),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch1][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch1][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], (8*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch1][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch1][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch1]), 3 );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'),
								strtotime($date_stamp2.' 2:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (6*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testHolidayDatePremiumPolicyA() {
		global $dd;

		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 410 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testHolidayDatePremiumPolicyB() {
		global $dd;

		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 412 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									$policy_ids['holiday'],
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


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

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	function testWeeklyHourNeverHolidayPremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 414 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day5
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (5*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day6
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*6);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testDailyHourBeforePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 500 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 501 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*0);
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
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testWeeklyHourBeforePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 510 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 511 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (7*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (6*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day5
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day6
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*6);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	function testLastSecondOfDayDatePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 600 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110 );
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00PM'),
								strtotime($date_stamp2.' 6:00AM'),
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testDailyAndWeeklyHourBeforeAfterPremiumPolicy() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 520 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 521 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 522 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day5
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day6
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*6);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (8*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	function testAdvancedActiveAfterWithDifferentialA() {
		global $dd;

		//Test to make sure active after Daily time includes all worked time, not just time matching the differential criteria.

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 700 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 9:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00AM'),
								strtotime($date_stamp.' 1:30PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:30PM'),
								strtotime($date_stamp.' 6:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (6*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}


	function testDSTA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


		//$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_epoch = strtotime('10-Mar-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('11-Mar-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00PM'),
								strtotime($date_stamp2.' 6:00AM'),
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
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testDSTB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );


		//$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_epoch = strtotime('02-Nov-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('03-Nov-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00PM'),
								strtotime($date_stamp2.' 6:00AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testDailyAndWeeklyBeforePremiumPolicyA() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 523 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 524 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (10*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (10*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 4:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (8*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:30AM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:00PM'),
								strtotime($date_stamp.' 3:30PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5.0*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (5.0*3600) );
		
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (0.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 6 );

		return TRUE;
	}

	function testDailyAndWeeklyBeforePremiumPolicyB() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 729 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 4:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:30AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:00PM'),
								strtotime($date_stamp.' 3:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[1],
											'department_id' => $this->department_ids[1],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5.0*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3.5*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (1.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testDailyAndWeeklyBeforePremiumPolicyC() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 724 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 2:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );


		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00AM'),
								strtotime($date_stamp.' 7:30AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (b)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:30AM'),
								strtotime($date_stamp.' 9:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[1],
											'department_id' => $this->department_ids[1],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (4.0*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (c)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:30AM'),
								strtotime($date_stamp.' 4:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[1],
											'department_id' => $this->department_ids[1],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (11.0*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8.5*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//

		return TRUE;
	}

	function testDailyAndWeeklyBeforePremiumPolicyD() {
		global $dd;

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723 );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 724 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day2
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*2);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*3);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 6:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (10*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (10*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day4
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*4);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 2:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (6*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );


		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (a)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:00AM'),
								strtotime($date_stamp.' 7:30AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (b)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:30AM'),
								strtotime($date_stamp.' 9:30AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[1],
											'department_id' => $this->department_ids[1],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (4.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.0*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (c)
		//
		$date_epoch = $this->pay_period_objs[0]->getStartDate()+(86400*5);
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:30AM'),
								strtotime($date_stamp.' 4:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[1],
											'department_id' => $this->department_ids[1],
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (11.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9.0*3600) );

		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1.5*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 40 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//

		return TRUE;
	}

}
?>