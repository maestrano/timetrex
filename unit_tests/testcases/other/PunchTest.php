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

class PunchTest extends PHPUnit_Framework_TestCase {

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
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );
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

	function createPayPeriodSchedule( $shift_assigned_day = 10 ) {
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
		$ppsf->setShiftAssignedDay( $shift_assigned_day );

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

	function createMealPolicy( $type_id ) {
		$mpf = TTnew( 'MealPolicyFactory' );

		$mpf->setCompany( $this->company_id );

		switch ( $type_id ) {
			case 10: //60min auto-deduct.
				$mpf->setName( '60min (AutoDeduct)' );
				$mpf->setType( 10 ); //AutoDeduct
				$mpf->setTriggerTime( (3600*5) );
				$mpf->setAmount( 3600 );
				$mpf->setStartWindow( (3600*4) );
				$mpf->setWindowLength( (3600*2) );
				break;
		}

		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text('Meal Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function createSchedulePolicy( $meal_policy_id ) {
		$spf = TTnew( 'SchedulePolicyFactory' );

		$spf->setCompany( $this->company_id );
		$spf->setName( 'Schedule Policy' );
		$spf->setMealPolicyID( $meal_policy_id );
		$spf->setOverTimePolicyID( 0 );
		$spf->setAbsencePolicyID( 0 );
		$spf->setStartStopWindow( (3600*2) );

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save();
			Debug::Text('Schedule Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function createSchedule( $user_id, $date_stamp, $data = NULL ) {
		$sf = TTnew( 'ScheduleFactory' );
		$sf->setCompany( $this->company_id );
		$sf->setUser( $user_id );
		//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate( $user_id, $date_stamp) );

		if ( isset($data['status_id']) ) {
			$sf->setStatus( $data['status_id'] );
		} else {
			$sf->setStatus( 10 );
		}

		if ( isset($data['schedule_policy_id']) ) {
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
		}

		if ( isset($data['absence_policy_id']) ) {
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
		}
		if ( isset($data['branch_id']) ) {
			$sf->setBranch( $data['branch_id'] );
		}
		if ( isset($data['department_id']) ) {
			$sf->setDepartment( $data['department_id'] );
		}

		if ( isset($data['job_id']) ) {
			$sf->setJob( $data['job_id'] );
		}

		if ( isset($data['job_item_id'] ) ) {
			$sf->setJobItem( $data['job_item_id'] );
		}

		if ( $data['start_time'] != '') {
			$start_time = strtotime( $data['start_time'], $date_stamp ) ;
		}
		if ( $data['end_time'] != '') {
			Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $date_stamp , __FILE__, __LINE__, __METHOD__,10);
			$end_time = strtotime( $data['end_time'], $date_stamp ) ;
			Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME',$data['end_time']) , __FILE__, __LINE__, __METHOD__,10);
		}

		$sf->setStartTime( $start_time );
		$sf->setEndTime( $end_time );

		if ( $sf->isValid() ) {
			$sf->setEnableReCalculateDay(FALSE);
			$insert_id = $sf->Save();
			Debug::Text('Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
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

	function getPunchDataArray( $start_date, $end_date ) {
		$plf = new PunchListFactory();

		$plf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $start_date, $end_date );
		if ( $plf->getRecordCount() > 0 ) {
			//Only return punch_control data for now
			$i=0;
			$prev_punch_control_id = NULL;
			foreach( $plf as $p_obj ) {
				if ( $prev_punch_control_id == NULL OR $prev_punch_control_id != $p_obj->getPunchControlID() ) {
					$date_stamp = $p_obj->getPunchControlObject()->getUserDateObject()->getDateStamp();
					$p_obj->setUser( $this->user_id );
					$p_obj->getPunchControlObject()->setPunchObject( $p_obj );

					$retarr[$date_stamp][$i] = array(
													'id' => $p_obj->getPunchControlObject()->getID(),
													'branch_id' =>$p_obj->getPunchControlObject()->getBranch(),
													'date_stamp' => $date_stamp,
													'user_date_id' => $p_obj->getPunchControlObject()->getUserDateID(),
													'shift_data' => $p_obj->getPunchControlObject()->getShiftData()
												   );

					$prev_punch_control_id = $p_obj->getPunchControlID();
					$i++;
				}

			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}
	/*
	 Tests:
		[PP Schedule Assigns shift to day they start on]
		- Basic In/Out punch in the middle of the day
		- Basic split shift in the middle of the day, with 3hr gap between them. (single shift)
		- Basic split shift in the middle of the day, with 6hr gap between them. (double shift)
		- In at 11:00PM on one day, and out at 2PM on another day.
		- In at 8PM, lunch out at 11:30PM, lunch in at 12:30 (next day), Out at 4AM. (single shift)

		[PP Schedule Assigns shift to day they end on]
		- In at 11:00PM on one day, and out at 2PM on another day.
		- In at 8PM, lunch out at 11:30PM, lunch in at 12:30 (next day), Out at 4AM. (single shift)

		- Advanced:
			- Many punches at the end of a day. Test to make sure they are on a specific date.
				Add more punches on the next day, test to make sure all punches in the shift change date.

		[PP Schedule Assigns shift to day they work most on]
		- In at 11:00PM on one day, and out at 2PM on another day.
		- In at 8PM, lunch out at 11:30PM, lunch in at 12:30 (next day), Out at 4AM. (single shift)

		[PP Schedule Split at midnight]
		- In at 11:00PM on one day, and out at 2PM on another day.
		- In at 8PM, lunch out at 11:30PM, lunch in at 12:30 (next day), Out at 4AM. (two shifts)
		- In at 8PM, lunch out at 10:30PM, lunch in at 11:30PM , Out at 4AM. (single shift)
		- In at exactly midnight, out at 8AM (one-shift)
		- In at 8PM out at exactly midnight (one-shift)

		- Advanced:
			- Many punches at the end of a day. Test to make sure they are on a specific date.
				Add more punches on the next day, test to make sure all punches in the shift change date.

		- Test punch control matching, but adding single punches at a time. Then across day boundaries
			then outside the new_shift_trigger time.


		- Test editing and deleting punches.
			- Basic Editing, changing the time by one hour.
				- Changing the In punch to a different day, causing the entire shift to be moved.

			- Deleting basic punches
			- Deleting punches that affect which day the shift falls on.


		- Validation tests:
			- Changing the in time to AFTER the out time. Check for validation error.
			- Changing the out time to BEFORE the in time. Check for validation error.
			- Trying to add punch inbetween two other existing punches in a pair.
			- Trying to add punch inbetween two other existing punches NOT in a pair, but that don't have any time between them (transfer punch)
			- Two punches of the same date/time but different status should succeed (transfer punches)
			- Two punches of the same date/time in same punch pair. Should fail.
			- Two punches of the same status/date/time in different punch pair. Should fail.

			- Test punch rounding, specifically lunch,break,day total rounding.
		--------------------------- DONE ABOVE THIS LINE --------------------------

		- Make sure we can't assign a punch to some random punch_control_id for another user/company.

	*/
	function testDayShiftStartsBasicA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayShiftStartsBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	function testDayShiftStartsBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 7:00AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 4:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) ); //Make sure there are only two punches per shift.
		$this->assertEquals( 2, count($punch_arr[$date_epoch][1]['shift_data']['punches']) ); //Make sure there are only two punches per shift.

		$this->assertNotEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts don't match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayShiftStartsBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayShiftStartsBasicE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00PM'),
								strtotime($date_stamp.' 11:30PM'), //Lunch Out
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
								strtotime($date_stamp2.' 12:30AM'),
								strtotime($date_stamp2.' 4:00AM'), //Lunch Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	//Special test to fix a bug when there is a 2hr gap between punches, but a new shift is only triggered after 4hrs.
	function testDayShiftStartsBasicF() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00AM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 4:00PM'),
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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00PM'),
								strtotime($date_stamp.' 9:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 3, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][2]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.
		$this->assertEquals( $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][2]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (9*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	//
	//  Test when shifts are assigned to the day they end on.
	//
	function testDayShiftEndsBasicA() {
		global $dd;

		$this->createPayPeriodSchedule( 20 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayShiftEndsBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 20 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );

		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testDayShiftEndsBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 20 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00PM'),
								strtotime($date_stamp.' 11:30PM'), //Lunch Out
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
								strtotime($date_stamp2.' 12:30AM'),
								strtotime($date_stamp2.' 4:00AM'), //Lunch Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.

		$this->assertEquals( 2, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch2][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch2][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}


	//
	//  Test when shifts are assigned to the day most worked on.
	//
	function testDayMostWorkedBasicA() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayMostWorkedBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00PM'),
								strtotime($date_stamp2.' 1:00AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testDayMostWorkedBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testDayMostWorkedBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//First punch pair
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:30PM'),
								strtotime($date_stamp.' 11:30PM'), //Lunch Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (3*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Second punch pair
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 12:30AM'),
								strtotime($date_stamp2.' 4:30AM'), //Lunch Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.

		$this->assertEquals( 2, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch2][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch2][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch2][0]['total_time'] );


		return TRUE;
	}

	//
	//  Test when shifts are split at midnight.
	//
	function testSplitAtMidnightBasicA() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testSplitAtMidnightBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Need to create the punches separately as createPunchPair won't split the punches.
		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 6:00PM'), array('branch_id' => $this->branch_id,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 10, 20, strtotime($date_stamp2.' 2:00AM'), array('branch_id' => $this->branch_id,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Make sure branch is the same across both punches
		$this->assertEquals( $punch_arr[$date_epoch][0]['branch_id'], $punch_arr[$date_epoch2][1]['branch_id'] );


		//Date 1
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		//Date 2
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][1]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Date 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (6*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Date 2
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (2*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testSplitAtMidnightBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Need to create the punches separately as createPunchPair won't split the punches.
		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 5:00PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 20, 20, strtotime($date_stamp.' 10:30PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 20, 10, strtotime($date_stamp.' 11:30PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 10, 20, strtotime($date_stamp2.' 2:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Date 1
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		//Date 2
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][2]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Date 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (6*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Date 2
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (2*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testSplitAtMidnightBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Need to create the punches separately as createPunchPair won't split the punches.
		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 5:30PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 20, 20, strtotime($date_stamp.' 11:30PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 20, 10, strtotime($date_stamp2.' 12:30AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 10, 20, strtotime($date_stamp2.' 2:30AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Date 1
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Date 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testSplitAtMidnightBasicE() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Need to create the punches separately as createPunchPair won't split the punches.
		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 4:00PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 10, 20, strtotime($date_stamp2.' 12:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Date 1
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Date 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testSplitAtMidnightBasicF() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 ); //Start weeks on Monday so DST change doesn't affect this.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time(), 1 )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Need to create the punches separately as createPunchPair won't split the punches.
		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 12:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );
		$dd->createPunch( $this->user_id, 10, 20, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Date 1
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Date 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchControlMatchingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:30AM'),
								NULL,
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
								NULL,
								strtotime($date_stamp.' 11:30AM'), //Lunch Out
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
								strtotime($date_stamp.' 12:30PM'),
								NULL,
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

		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 5:30PM'), //Lunch Out
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


		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchControlMatchingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:30AM'),
								NULL, //strtotime($date_stamp.' 11:30PM'), //Lunch Out
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
								NULL, //strtotime($date_stamp.' 8:30PM'),
								strtotime($date_stamp.' 4:30PM'), //Lunch Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchControlMatchingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 4:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 7:30PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getEndDayEpoch($date_epoch); //This accounts for DST.

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );

		$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//if ( TTDate::doesRangeSpanDST( $date_epoch, $date_epoch2 ) ) {
		//	$this->assertEquals( ((15.5*3600)+TTDate::getDSTOffset($date_epoch, $date_epoch2)), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//} else {
		//	$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//}

		return TRUE;
	}

	function testPunchControlMatchingD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00AM'),
								NULL,
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

		//Just create out punch, 17.5hrs later. Threshold is 16hrs. //This needs to be more than 1 hour outside the limit due to DST issues.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 7:30PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );

		$this->assertEquals( 1, count($punch_arr[$date_epoch][1]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][1]['shift_data']['punch_control_ids']) );

		$this->assertNotEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['id'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (0*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchControlMatchingE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp2.' 1:30AM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchEditingA() {
		global $dd;

		$this->createPayPeriodSchedule( 20 ); //Day shift ends on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 10:00PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (12*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 11:00PM'),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (13*3600), $udt_arr[$date_epoch][0]['total_time'] );


		return TRUE;
	}

	function testPunchEditingB() {
		global $dd;

		$this->createPayPeriodSchedule( 20 ); //Day shift ends on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 10:00PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (12*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp2.' 1:30AM'),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.

		//Make sure previous day has no totals, but new day has proper totals.
		if ( !isset($punch_arr[$date_epoch]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		if ( isset($punch_arr[$date_epoch2]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}
		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testPunchEditingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 1:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp2.' 1:00PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.
		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (12*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch2][0]['shift_data']['punches'][0]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 9:30PM'),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Make sure previous day has no totals, but new day has proper totals.
		if ( !isset($punch_arr[$date_epoch2]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		if ( isset($punch_arr[$date_epoch]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testPunchEditingD() {
		global $dd;

		$this->createPayPeriodSchedule( 30 ); //Day with most time worked
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 5:30PM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 11:30PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (6*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp2.' 7:30AM'),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.

		//Make sure previous day has no totals, but new day has proper totals.
		if ( !isset($punch_arr[$date_epoch]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		if ( isset($punch_arr[$date_epoch2]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}
		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (14*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testPunchDeletingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 10:00PM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (12*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Delete punch
		$dd->deletePunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id']);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (0*3600), $udt_arr[$date_epoch][0]['total_time'] );


		return TRUE;
	}

	function testPunchDeletingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 11:30PM'), //Normal Out
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

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 1:00AM'),
								NULL,
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

		//Just create out punch, 15.5hrs later. Threshold is 16hrs.
		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp2.' 2:30AM'), //Normal Out
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (3*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Delete first out punch, causing the totals to change, but nothing else.
		$dd->deletePunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id']);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.

		$this->assertEquals( 3, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (1.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Delete first in punch (last punch in pair), causing the totals to change, and the final two punches to switch days.
		$dd->deletePunch($punch_arr[$date_epoch][0]['shift_data']['punches'][0]['id']);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		if ( !isset($punch_arr[$date_epoch]) ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (1.5*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	function testValidationA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		//Edit punch to after Out time.
		$edit_punch_result = $dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][0]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 3:30PM'),
								) );


		//Make sure edit punch failed.
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationA2() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		//Edit punch to after Out time,
		//*that exceeds the new shift trigger time of 4hrs.*
		$edit_punch_result = $dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][0]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 8:00PM'),
								) );


		//Make sure edit punch failed.
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		//Edit punch to after Out time.
		$edit_punch_result = $dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 7:30AM'),
								) );


		//Make sure edit punch failed.
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Try to add another punch inbetween already existing punch pair.
		$edit_punch_result = $dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:00AM'),
								NULL,
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



		//Make sure edit punch failed.
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		//Add additional punch outside existing punch pair, so we can later edit it to fit inbetween punch pair.
		$edit_punch_result = $dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 4:00PM'),
								NULL,
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

		//Make sure adding 3rd punch succeeded
		if ( $edit_punch_result === TRUE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);

		//Edit punch to after Out time.
		$edit_punch_result = $dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][2]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 2:00PM'),
								) );


		//Make sure editing punch failed
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

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

		//Add additional punch pair with no gap between them.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );


		//Try to add additional punch between two punch pairs with no gap.
		$edit_punch_result = $dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								NULL,
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

		//Make sure adding 3rd punch succeeded
		if ( $edit_punch_result === FALSE ) {
			$this->assertTrue( TRUE );
		} else {
			$this->assertTrue( FALSE );
		}

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	function testValidationF() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								NULL,
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

		//Add additional punch pair with no gap between them.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								NULL,
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (0*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testValidationG() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
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

 		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 0, count($punch_arr[$date_epoch]) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		return TRUE;
	}

	function testRoundingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 10 ); //In
		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 20 ); //Out

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['round'],
									array( $this->user_id ) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:03AM'),
								strtotime($date_stamp.' 4:46PM'),
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

 		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:00AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 4:45PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8.75*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testRoundingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 30 ); //Day Total

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['round'],
									array( $this->user_id ) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:03AM'),
								NULL,
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
								NULL,
								strtotime($date_stamp.' 5:12PM'),
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

 		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:03AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 5:03PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (9*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testRoundingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 30 ); //Day Total
		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 40 ); //Lunch Total

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['round'],
									array( $this->user_id ) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:03AM'),
								NULL,
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
								NULL,
								strtotime($date_stamp.' 12:06PM'),
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
								strtotime($date_stamp.' 1:12PM'),
								NULL,
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

		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 5:07PM'),
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

 		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:03AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 12:06PM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['time_stamp'], strtotime($date_stamp.' 1:06PM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['time_stamp'], strtotime($date_stamp.' 5:03PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testRoundingD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 30 ); //Day Total
		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 40 ); //Lunch Total
		$policy_ids['round'][] = $dd->createRoundingPolicy( $this->company_id, 50 ); //Break Total

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['round'],
									array( $this->user_id ) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:03AM'),
								NULL,
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
								NULL,
								strtotime($date_stamp.' 12:06PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:12PM'),
								NULL,
								array(
											'in_type_id' => 30,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								NULL,
								strtotime($date_stamp.' 5:07PM'),
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

 		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:03AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 12:06PM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['time_stamp'], strtotime($date_stamp.' 1:06PM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['time_stamp'], strtotime($date_stamp.' 5:03PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
	//See US department of labor description: http://www.dol.gov/elaws/esa/flsa/hoursworked/screenER11.asp
	function testDSTFall() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = strtotime('02-Nov-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('03-Nov-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
								strtotime($date_stamp2.' 1:00AM'),
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
								strtotime($date_stamp2.' 1:30AM'),
								strtotime($date_stamp2.' 6:30AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (9*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}
	//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
	function testDSTFallB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = strtotime('02-Nov-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('03-Nov-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		//$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (9*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
	function testDSTSpring() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = strtotime('09-Mar-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('10-Mar-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
								strtotime($date_stamp2.' 1:00AM'),
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
								strtotime($date_stamp2.' 1:30AM'),
								strtotime($date_stamp2.' 6:30AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}
	//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
	function testDSTSpringB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = strtotime('09-Mar-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('10-Mar-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		//$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	function testScheduleMatchingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																  ) );

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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	function testScheduleMatchingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																  ) );

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

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:00PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][1]['date_stamp'] );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][1]['shift_data']['punches'][0]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	function testScheduleMatchingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( $date_epoch+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 11:00PM',
																	'end_time' => '8:00AM',
																  ) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00PM'),
								strtotime($date_stamp2.' 8:00AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (8*3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	function testScheduleMatchingD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( $date_epoch+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 11:00PM',
																	'end_time' => '8:00AM',
																  ) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 12:30AM'),
								strtotime($date_stamp2.' 8:00AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr) ); //Make sure only one day exists.
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( (6.5*3600), $udt_arr[$date_epoch2][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch2]) );

		return TRUE;
	}

	function testScheduleMatchingE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( $date_epoch+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch2, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 12:30AM',
																	'end_time' => '8:00AM',
																  ) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:30PM'),
								strtotime($date_stamp2.' 8:00AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr) ); //Make sure only one day exists.
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (7.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		//$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	
	//No defaults in station or employee profile.
	function testDefaultPunchSettingsNoScheduleA() {
		global $dd;
		
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 8:00AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		//var_dump($data);
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( 0, $data['branch_id'] );
		$this->assertEquals( 0, $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with default branch/department set in employee profile
	function testDefaultPunchSettingsNoScheduleB() {
		global $dd;
				
		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.
		
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 8:00AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		//var_dump($data);

		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}
	
	//
	//Test with default branch/department set in employee profile and station.
	//
	
	//Test with previous Normal punch.
	function testDefaultPunchSettingsNoScheduleD() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								NULL, //Out
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 5:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 20, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with previous Break punch.
	function testDefaultPunchSettingsNoScheduleE() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:15AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 30,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 9:30AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 30, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with previous Lunch punch.
	function testDefaultPunchSettingsNoScheduleF() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 1:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 20, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with split shift.
	function testDefaultPunchSettingsNoScheduleG() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 2:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with split shift (B)
	function testDefaultPunchSettingsNoScheduleGB() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, 0, 0 ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 2:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( 0, $data['branch_id'] );
		$this->assertEquals( 0, $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}






	//No defaults in station or employee profile.
	function testDefaultPunchSettingsScheduleA() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 );
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.
		
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		
		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => 0, //If no branch/department is specified in the schedule, use EE profile.
																	'department_id' => 0, 
																  ) );
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 8:00AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		//var_dump($data);
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//No defaults in station or employee profile.
	function testDefaultPunchSettingsScheduleB() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 );
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.
		
		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		
		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 8:00AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		//var_dump($data);
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[1], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[1], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}


	//Test with previous Normal punch.
	function testDefaultPunchSettingsScheduleC() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
				
		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								NULL, //Out
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 5:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 20, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with previous Break punch.
	function testDefaultPunchSettingsScheduleD() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 9:15AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 30,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 9:30AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 30, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with previous Lunch punch.
	function testDefaultPunchSettingsScheduleE() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 20,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 1:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 20, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}


	//Test with split shift.
	function testDefaultPunchSettingsScheduleF() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, $this->tmp_branch_id[0], $this->tmp_department_id[0] ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 2:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[1], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[1], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with split shift (B)
	function testDefaultPunchSettingsScheduleFB() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, 0, 0 ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 2:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[1], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[1], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}
	
	//Test with split shift (C)
	function testDefaultPunchSettingsScheduleFC() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, 0, 0 ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '1:00PM',
																	'branch_id' => $this->tmp_branch_id[0],
																	'department_id' => $this->tmp_department_id[0], 
																  ) );

		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 2:00PM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		/*
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		*/
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 8:00AM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with split shift (D)
	function testDefaultPunchSettingsScheduleFD() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, 0, 0 ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '1:00PM',
																	'branch_id' => $this->tmp_branch_id[0],
																	'department_id' => $this->tmp_department_id[0], 
																  ) );

		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 2:00PM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								FALSE, //strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 1:00PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 10, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}

	//Test with split shift (E)
	function testDefaultPunchSettingsScheduleFE() {
		global $dd;

		$this->tmp_branch_id[] = $this->branch_id; 
		$this->tmp_branch_id[] = $dd->createBranch( $this->company_id, 20 ); 
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 10 );
		$this->tmp_department_id[] = $dd->createDepartment( $this->company_id, 20 ); 
		$this->user_id = $dd->createUser( $this->company_id, 10, 0, 0, 0 ); //Non-Admin user.

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '1:00PM',
																	'branch_id' => $this->tmp_branch_id[0],
																	'department_id' => $this->tmp_department_id[0], 
																  ) );

		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 2:00PM',
																	'end_time' => '5:00PM',
																	'branch_id' => $this->tmp_branch_id[1],
																	'department_id' => $this->tmp_department_id[1], 
																  ) );
		
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								NULL, //strtotime($date_stamp.' 1:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->tmp_branch_id[0],
											'department_id' => $this->tmp_department_id[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$epoch = strtotime($date_stamp.' 2:10PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();
		
		$plf = TTNew('PunchFactory');
		
		$data = $plf->getDefaultPunchSettings( $user_obj, $epoch );
		
		$this->assertEquals( 20, $data['status_id'] ); //In/Out
		$this->assertEquals( 10, $data['type_id'] ); //Normal/Lunch/Break
		
		$this->assertEquals( $this->tmp_branch_id[0], $data['branch_id'] );
		$this->assertEquals( $this->tmp_department_id[0], $data['department_id'] );
		$this->assertEquals( 0, $data['job_id'] );
		$this->assertEquals( 0, $data['job_item_id'] );
		
		return TRUE;
	}
	

	function testMaximumShiftTimeA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 4:30PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (15.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testMaximumShiftTimeB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 0, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 0, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );

		$this->assertEquals( 0, count($udt_arr) );

		return TRUE;
	}

	function testMaximumShiftTimeC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create two punch pairs with the minimum time between shifts, so they both fall on the same day, but are considered two separate shifts.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 2:30PM'),
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
								strtotime($date_stamp.' 6:30PM'),
								strtotime($date_stamp2.' 6:30AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );

		$this->assertEquals( 2, count($punch_arr[$date_epoch][1]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][1]['shift_data']['punch_control_ids']) );

		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (25.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	function testMaximumShiftTimeD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create two punch pairs with LESS than the minimum time between shifts, so they both fall on the same day, but are considered ONE shift and therefore fails.
		//However the last punch must be more than 16hrs away from the previous OUT punch (2:30PM)
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00AM'),
								strtotime($date_stamp.' 2:30PM'),
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
								strtotime($date_stamp.' 6:15PM'),
								strtotime($date_stamp2.' 8:00AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( (13.5*3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

}
?>