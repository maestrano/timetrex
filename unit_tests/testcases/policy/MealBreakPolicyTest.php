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

/**
 * @group MealBreakPolicy
 */
class MealBreakPolicyTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][190] = $dd->createPayCode( $this->company_id, 190 ); //Lunch
		$this->policy_ids['pay_code'][192] = $dd->createPayCode( $this->company_id, 192 ); //Break
		$this->policy_ids['pay_code'][300] = $dd->createPayCode( $this->company_id, 300 ); //Prem1
		$this->policy_ids['pay_code'][310] = $dd->createPayCode( $this->company_id, 310 ); //Prem2
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900 ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910 ); //Bank
		$this->policy_ids['pay_code'][920] = $dd->createPayCode( $this->company_id, 920 ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, array( $this->policy_ids['pay_code'][100] ) ); //Regular
		$this->policy_ids['contributing_pay_code_policy'][12] = $dd->createContributingPayCodePolicy( $this->company_id, 12, array( $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ) ); //Regular+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][14] = $dd->createContributingPayCodePolicy( $this->company_id, 14, array( $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][900] ) ); //Regular+Meal/Break+Absence
		$this->policy_ids['contributing_pay_code_policy'][90] = $dd->createContributingPayCodePolicy( $this->company_id, 90, array( $this->policy_ids['pay_code'][900] ) ); //Absence
		$this->policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $this->policy_ids['pay_code'] ); //All Time

		$this->policy_ids['contributing_shift_policy'][12] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$this->policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);

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

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = array();

		//Get only system totals.
		//$udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, $start_date, $end_date);
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, array(5, 20, 30, 40, 100, 110), $start_date, $end_date);
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach($udtlf as $udt_obj) {
				$user_date_stamp = TTDate::strtotime( $udt_obj->getColumn('user_date_stamp') );

				//$type_and_policy_id = $udt_obj->getType().(int)$udt_obj->getOverTimePolicyID();
				//$type_and_policy_id = $udt_obj->getObjectType().(int)$udt_obj->getObjectID();
				$type_and_policy_id = $udt_obj->getObjectType().(int)$udt_obj->getPayCode();

				$date_totals[$user_date_stamp][] = array(
												'date_stamp' => $udt_obj->getColumn('user_date_stamp'),
												'id' => $udt_obj->getId(),

												//Keep legacy status_id/type_id for now, so we don't have to change as many unit tests.
												'status_id' => $udt_obj->getStatus(),
												'type_id' => $udt_obj->getType(),
												'src_object_id' => $udt_obj->getSourceObject(),

												'object_type_id' => $udt_obj->getObjectType(),
												'pay_code_id' => $udt_obj->getPayCode(),

												'type_and_policy_id' => $type_and_policy_id,
												'branch_id' => (int)$udt_obj->getBranch(),
												'department_id' => $udt_obj->getDepartment(),
												'total_time' => $udt_obj->getTotalTime(),
												'name' => $udt_obj->getName(),

												'quantity' => $udt_obj->getQuantity(),
												'bad_quantity' => $udt_obj->getBadQuantity(),

												'hourly_rate' => $udt_obj->getHourlyRate(),
												//Override only shows for SYSTEM override columns...
												//Need to check Worked overrides too.
												'tmp_override' => $udt_obj->getOverride()
												);
			}
		}

		return $date_totals;
	}

	function createMealPolicy( $company_id, $type ) {
		$mpf = new MealPolicyFactory();
		$mpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 1hr lunch
				$mpf->setName( 'Normal' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( FALSE );
				$mpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 110: //AutoAdd 1hr
				$mpf->setName( 'AutoAdd 1hr' );
				$mpf->setType( 15 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( FALSE );
				$mpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 115: //AutoAdd 1hr
				$mpf->setName( 'AutoAdd 1hr' );
				$mpf->setType( 15 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( TRUE );
				$mpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 120: //AutoDeduct 1hr
				$mpf->setName( 'AutoDeduct 1hr' );
				$mpf->setType( 10 );
				$mpf->setTriggerTime( (3600*6) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( FALSE );
				$mpf->setPayCode( $this->policy_ids['pay_code'][100] );
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
			case 100: //Normal 15min break
				$bpf->setName( 'Normal' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( (3600*6) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( FALSE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 110: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*1) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( FALSE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 115: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Punch Time)' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*1) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( TRUE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;

			case 120: //AutoDeduct 15min
				$bpf->setName( 'AutoDeduct 15min' );
				$bpf->setType( 10 );
				$bpf->setTriggerTime( (3600*6) );
				$bpf->setAmount( 15*60 );
				$bpf->setIncludeBreakPunchTime( FALSE );
				$bpf->setIncludeMultipleBreaks( FALSE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;


			case 150: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both)' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*1) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( TRUE );
				$bpf->setIncludeMultipleBreaks( TRUE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 152: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [2]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*3) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( TRUE );
				$bpf->setIncludeMultipleBreaks( TRUE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 154: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [3]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*5) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( TRUE );
				$bpf->setIncludeMultipleBreaks( TRUE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
				break;
			case 156: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [4]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( (3600*10) );
				$bpf->setAmount( 60*15 );
				$bpf->setIncludeBreakPunchTime( TRUE );
				$bpf->setIncludeMultipleBreaks( TRUE );
				$bpf->setPayCode( $this->policy_ids['pay_code'][100] );
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

	/*
	 Tests:
		Meal:
			No Meal Policy at all.
			1x Normal Meal
			1x AutoAdd Meal
			1x AudoDeduct Meal
			1x AutoAdd Meal with Include Punch Time for Lunch.

		Break:
			No Break Policy at all.
			1x Normal Break
			1x AutoAdd Break
			1x AudoDeduct Break
			1x AutoAdd Break with Include Punch Time for Break.

			3x AutoAdd Break
			3x AudoDeduct Break
			3x AutoAdd Break with Include Punch Time for Break and Multiple
	*/
	function testNoMealPolicyA() {
		global $dd;

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);
		
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 5:00PM'),
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
		//Regular Time (Part 1)
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (4*3600) );
		//Regular Time (Part 2)
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testNormalMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 5:00PM'),
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
		//Regular Time (Part 1)
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (4*3600) );
		//Regular Time (Part 2)
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testAutoAddMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (0.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4*3600) );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (0.5*3600) );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], (0.5*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 7 );

		return TRUE;
	}

	function testAutoAddMealPolicyB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 847 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 953 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4.5*3600) );

		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 847 );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 953 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 7 );

		return TRUE;
	}

	function testAutoAddMealPolicyC() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 12:00PM'),
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
								strtotime($date_stamp.' 1:30PM'),
								strtotime($date_stamp.' 5:00PM'),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.5*3600) );

		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 1681 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 1919 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (3.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4.0*3600) );

		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 1681 );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 1919 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 7 );

		return TRUE;
	}

	function testAutoDeductMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		/*
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (-1*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9*3600) );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (-1*3600) );
		*/

		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		//Lunch Taken
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 100 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (-1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testAutoDeductMealPolicyB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									$policy_ids['meal'], //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:00PM'), //Less than 6hrs so no autodeduct occurs
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (5*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}


	//
	// Break Policy
	//


	function testNoBreakPolicyA() {
		global $dd;

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:15AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 20,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.75*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.25*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );

		return TRUE;
	}

	function testNormalBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:15AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 20,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (1.75*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2.25*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );


		return TRUE;
	}

	function testAutoAddBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:15AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 20,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 196 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 225 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 225 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 254 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1.75*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], (2.25*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 196 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 225 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], 225 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], 254 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 13 );

		return TRUE;
	}

	function testAutoAddBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:06AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 20,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 83 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 88 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 88 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 101 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1.9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], (2.25*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 83 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 88 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], 88 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], 101 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 13 );


		return TRUE;
	}

	function testAutoAddBreakPolicyC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:21AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 20,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:00PM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 20,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 187 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 227 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 227 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 259 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], (1.65*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], (2.25*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 187 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 227 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], 227 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], 259 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 13 );

		return TRUE;
	}

	function testAutoDeductBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 5:15PM'),
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
		/*
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (-0.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9.25*3600) );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (-0.25*3600) );
		*/

		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (9*3600) );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (-0.25*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	function testAutoDeductBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (5*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		return TRUE;
	}

	function testAutoAddMultipleBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:15AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:15PM'),
								strtotime($date_stamp.' 3:15PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 174 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 174 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 199 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 199 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 228 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 228 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], 299 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], 299 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], (1.75*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], (2.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], (3*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][13]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['total_time'], 174 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][14]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['total_time'], 174 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][15]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['total_time'], 199 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][16]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['total_time'], 199 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][17]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['total_time'], 228 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][18]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['total_time'], 228 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][19]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['total_time'], 299 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][20]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['total_time'], 299 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 21 );

		return TRUE;
	}

	function testAutoAddMultipleBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:06AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:21PM'),
								strtotime($date_stamp.' 3:15PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:15PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 151 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 159 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 180 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 188 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 198 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 226 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], 230 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], 288 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], (1.9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], (2.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], (2.9*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][13]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['total_time'], 151 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][14]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['total_time'], 159 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][15]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['total_time'], 180 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][16]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['total_time'], 188 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][17]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['total_time'], 198 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][18]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['total_time'], 226 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][19]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['total_time'], 230 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][20]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['total_time'], 288 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 21 );

		return TRUE;
	}

	function testAutoAddMultipleBreakPolicyC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);
		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:45AM'),
								strtotime($date_stamp.' 5:30PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 205 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 205 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 205 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 695 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 695 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 695 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], (6.75*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 205 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 205 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], 205 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], 695 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][13]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['total_time'], 695 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][14]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['total_time'], 695 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 15 );

		return TRUE;
	}

	function testAutoAddMultipleBreakPolicyD() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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
								strtotime($date_stamp.' 10:51AM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9.4*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 208 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 208 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 208 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 692 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 692 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 692 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], (6.65*3600) );

		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 208 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 208 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], 208 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], 692 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][13]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['total_time'], 692 );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][14]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['total_time'], 692 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 15 );

		return TRUE;
	}

	function testAutoAddMultipleBreakPolicyE() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									$policy_ids['break'], //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 10:00AM'),
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

		return TRUE;
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:06AM'),
								strtotime($date_stamp.' 12:00PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 12:06PM'),
								strtotime($date_stamp.' 2:15PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 2:21PM'),
								strtotime($date_stamp.' 4:30PM'),
								array(
											'in_type_id' => 30,
											'out_type_id' => 30,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 4:36PM'),
								strtotime($date_stamp.' 5:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['type_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 56 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 92 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 112 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][4]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 118 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][5]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], 127 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][6]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], 127 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][7]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], 187 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][8]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][8]['total_time'], 197 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][9]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][9]['total_time'], 212 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][10]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][10]['total_time'], 212 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][11]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][11]['total_time'], (0.9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][12]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][12]['total_time'], (1.9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][13]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][13]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][14]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][14]['total_time'], (2.15*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][15]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][15]['total_time'], (2.15*3600) );

		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][16]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][16]['total_time'], 56 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][17]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][17]['total_time'], 92 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][18]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][18]['total_time'], 112 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][19]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][19]['total_time'], 118 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][20]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][20]['total_time'], 127 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][21]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][21]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][21]['total_time'], 127 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][22]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][22]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][22]['total_time'], 187 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][23]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][23]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][23]['total_time'], 197 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][24]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][24]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][24]['total_time'], 212 );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][25]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][25]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][25]['total_time'], 212 );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 26 );
		
		return TRUE;
	}
}
?>
