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

class PunchDetectionTest extends PHPUnit_Framework_TestCase {

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

		//$this->absence_policy_id = $dd->createAbsencePolicy( $this->company_id, 10 );

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

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
		$max_pay_periods = 29;

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

	function createMealPolicy( $company_id, $type ) {
		$mpf = new MealPolicyFactory();
		$mpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 1hr lunch: Detect by Time Window
				$mpf->setName( 'Normal - Time Window' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setAutoDetectType( 10 );

				$mpf->setStartWindow( (3*3600) );
				$mpf->setWindowLength( (2*3600) );
				$mpf->setIncludeLunchPunchTime( FALSE );
				break;
			case 110: //Normal 1hr lunch: Detect by Punch Time
				$mpf->setName( 'Normal - Punch Time' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setAutoDetectType( 20 );

				$mpf->setMinimumPunchTime( (60*30) ); ///0.5hr
				$mpf->setMaximumPunchTime( (60*75) ); //1.25hr
				$mpf->setIncludeLunchPunchTime( FALSE );
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

	function createBreakPolicy( $company_id, $type ) {
		$bpf = new BreakPolicyFactory();
		$bpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 15min break: Detect by Time Window
				$bpf->setName( 'Normal' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( (3600*0.5) );
				$bpf->setAmount( 60*15 );
				$bpf->setAutoDetectType( 10 );

				$bpf->setStartWindow( (1*3600) );
				$bpf->setWindowLength( (1*3600) );

				$bpf->setIncludeBreakPunchTime( FALSE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				break;
			case 110: //Normal 15min break: Detect by Punch Time
				$bpf->setName( 'Normal' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( (3600*0.5) );
				$bpf->setAmount( 60*15 );
				$bpf->setAutoDetectType( 20 );

				$bpf->setMinimumPunchTime( (60*5) ); ///5min
				$bpf->setMaximumPunchTime( (60*25) ); //25min

				$bpf->setIncludeBreakPunchTime( FALSE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				break;
		}

		if ( $bpf->isValid() ) {
			$insert_id = $bpf->Save();
			Debug::Text('Break Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Break Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getPreviousPunch( $epoch ) {
		$plf = TTnew( 'PunchListFactory' );
		$plf->getPreviousPunchByUserIDAndEpoch( $this->user_id, $epoch );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::Text(' Found Previous Punch within Continuous Time from now...', __FILE__, __LINE__, __METHOD__,10);
			$prev_punch_obj = $plf->getCurrent();
			$prev_punch_obj->setUser( $this->user_id );

			return $prev_punch_obj;
		}
		Debug::Text(' Previous Punch NOT found!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	/*
	 Tests:
		- Normal In/Out punches in the middle of the day with no policies
		- Normal In/Out punches around midnight with no policies
		- Lunch punches with Time Window detection
		- Lunch punches with Punch Time detection
		- Break punches with Time Window detection
		- Break punches with Punch Time detection
	*/

	function testNoMealOrBreakA() {
		global $dd;

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 12:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 1:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testNoMealOrBreakB() {
		global $dd;

		//$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() )+86400+3600 );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00PM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 11:30PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp2.' 12:30AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp2.' 5:00AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testMealTimeWindowA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'],
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 12:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 20 ); //Lunch
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 1:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 20 ); //Lunch
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testMealTimeWindowB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'],
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:30AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Lunch
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 11:30AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Lunch
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testMealTimeWindowC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'],
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 3:30PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Lunch
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 4:30PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Lunch
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testMealPunchTimeWindowA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'],
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 12:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 1:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 20 ); //Lunch
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testMealPunchTimeWindowB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'],
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id) );

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 12:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 1:30PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakTimeWindowA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 9:30AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 30 ); //Break
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 9:45AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 30 ); //Break
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakTimeWindowB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 8:30AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 8:45AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakTimeWindowC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		//Check all normal punches within the time window of the previous normal punch. This triggered a bug before.
		$punch_time = strtotime($date_stamp.' 3:30PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 3:45PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakPunchTimeWindowA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:00AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:15AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 30 ); //Break
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakPunchTimeWindowB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:00AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:45AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakPunchTimeWindowC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:00AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:03AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		return TRUE;
	}

	function testBreakPunchTimeWindowD() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									$policy_ids['break']);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunch( $this->user_id, 10, 10, strtotime($date_stamp.' 8:00AM'), array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:00AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 10:15AM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 30 ); //Break
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 2:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal - Because when using punch time it can't be detected on the first out punch.
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 2:15PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 30 ); //Break
		$this->assertEquals( $punch_status_id, 10 ); //In
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_time = strtotime($date_stamp.' 5:00PM');
		$prev_punch_obj = $this->getPreviousPunch( $punch_time );
		$punch_type_id = $prev_punch_obj->getNextType( $punch_time );
		$punch_status_id = $prev_punch_obj->getNextStatus();
		$this->assertEquals( $punch_type_id, 10 ); //Normal
		$this->assertEquals( $punch_status_id, 20 ); //Out
		$dd->createPunch( $this->user_id, $punch_type_id, $punch_status_id, $punch_time, array('branch_id' => 0,'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 ), TRUE );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 3, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 6, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][2]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][3]['status_id'] );

		$this->assertEquals( 30, $punch_arr[$date_epoch][0]['shift_data']['punches'][4]['type_id'] );
		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][4]['status_id'] );

		$this->assertEquals( 10, $punch_arr[$date_epoch][0]['shift_data']['punches'][5]['type_id'] );
		$this->assertEquals( 20, $punch_arr[$date_epoch][0]['shift_data']['punches'][5]['status_id'] );

		return TRUE;
	}
}
?>
