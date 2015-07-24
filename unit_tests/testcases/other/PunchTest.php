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

class PunchTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

	public function setUp() {
		global $dd;
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);
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

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular

		$this->user_id = $dd->createUser( $this->company_id, 100 );
		
		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

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

	function createPayPeriodSchedule( $shift_assigned_day = 10, $maximum_shift_time = 57600, $new_shift_trigger_time = 14400 ) {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() ) - (86400 * (7 * 6) ) ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( $new_shift_trigger_time );
		$ppsf->setMaximumShiftTime( $maximum_shift_time );
		$ppsf->setShiftAssignedDay( $shift_assigned_day );

		$ppsf->setEnableInitialPayPeriods( FALSE );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save(FALSE);
			Debug::Text('Pay Period Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			$ppsf->setUser( array($this->user_id) );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;

	}

	function createPayPeriods( $initial_date = FALSE ) {
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					if ( $initial_date !== FALSE ) {
						$end_date = $initial_date;
					} else {
						//$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-07') );
						$end_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() ) - (86400 * (7 * 6) ) ) );
					}
				} else {
					$end_date = ( $end_date + ( (86400 * 14) ) );
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 * 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
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
				$mpf->setTriggerTime( (3600 * 5) );
				$mpf->setAmount( 3600 );
				$mpf->setStartWindow( (3600 * 4) );
				$mpf->setWindowLength( (3600 * 2) );
				break;
		}
		
		$mpf->setPayCode( $this->policy_ids['pay_code'][100] );

		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text('Meal Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function createRoundingPolicy( $company_id, $type ) {
		$ripf = TTnew( 'RoundIntervalPolicyFactory' );
		$ripf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //In
				$ripf->setName( '5min [1]' );
				$ripf->setPunchType( 40 ); //In
				$ripf->setRoundType( 30 ); //Up
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( (60 * 3) ); //3min
				$ripf->setStrict( FALSE );
				break;
			case 20: //Out
				$ripf->setName( '5min [2]' );
				$ripf->setPunchType( 50 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( (60 * 3) ); //3min
				$ripf->setStrict( FALSE );
				break;
			case 30: //Day total
				$ripf->setName( '15min [3]' );
				$ripf->setPunchType( 120 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 15) ); //15mins
				$ripf->setGrace( (60 * 3) ); //3min
				$ripf->setStrict( FALSE );
				break;
			case 40: //Lunch total
				$ripf->setName( '15min [4]' );
				$ripf->setPunchType( 100 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 15) ); //15mins
				$ripf->setGrace( (60 * 3) ); //3min
				$ripf->setStrict( FALSE );
				break;
			case 50: //Break total
				$ripf->setName( '15min [5]' );
				$ripf->setPunchType( 110 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 15) ); //15mins
				$ripf->setGrace( (60 * 3) ); //3min
				$ripf->setStrict( FALSE );
				break;
			case 110: //In - Static Time Condition
				$ripf->setName( '15min [6]' );
				$ripf->setPunchType( 40 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( 0 ); //0min
				$ripf->setStrict( FALSE );
/*
										'condition_type_id' => 'ConditionType',
										'condition_static_time' => 'ConditionStaticTime',
										'condition_static_total_time' => 'ConditionStaticTotalTime',
										'condition_start_window' => 'ConditionStartWindow',
										'condition_end_window' => 'ConditionEndWindow',
*/
				$ripf->setConditionType( 30 ); //Static Time
				$ripf->setConditionStaticTime( strtotime( '8:00 AM' ) );
				$ripf->setConditionStartWindow( 900 ); //15 Min
				$ripf->setConditionStopWindow( 900 ); //15 Min
				break;
			case 111: //In - Schedule Time Condition
				$ripf->setName( '15min [6b]' );
				$ripf->setPunchType( 40 ); //In
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( 0 ); //0min
				$ripf->setStrict( FALSE );

				$ripf->setConditionType( 10 ); //Static Time
				$ripf->setConditionStartWindow( 900 ); //15 Min
				$ripf->setConditionStopWindow( 900 ); //15 Min
				break;
			case 120: //Out - Static Time Condition
				$ripf->setName( '5min [7]' );
				$ripf->setPunchType( 50 ); //Out
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( 0 ); //3min
				$ripf->setStrict( FALSE );
				
				$ripf->setConditionType( 30 ); //Static Time
				$ripf->setConditionStaticTime( strtotime( '5:00 PM' ) );
				$ripf->setConditionStartWindow( 900 ); //15 Min
				$ripf->setConditionStopWindow( 900 ); //15 Min
				break;
			case 121: //Out - Static Time Condition
				$ripf->setName( '5min [7b]' );
				$ripf->setPunchType( 50 ); //Out
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //5mins
				$ripf->setGrace( 0 ); //3min
				$ripf->setStrict( FALSE );

				$ripf->setConditionType( 10 ); //Static Time
				$ripf->setConditionStartWindow( 900 ); //15 Min
				$ripf->setConditionStopWindow( 900 ); //15 Min
				break;
			case 130: //Day Total - Static Total Time Condition
				$ripf->setName( '15min [8]' );
				$ripf->setPunchType( 120 ); //Day Total
				$ripf->setRoundType( 10 ); //Down
				$ripf->setInterval( (60 * 5) ); //15mins
				$ripf->setGrace( 0 ); //3min
				$ripf->setStrict( FALSE );

				$ripf->setConditionType( 40 ); //Static Total Time
				$ripf->setConditionStaticTotalTime( (9 * 3600) );
				$ripf->setConditionStartWindow( 900 ); //15 Min
				$ripf->setConditionStopWindow( 900 ); //15 Min
				break;

		}

		if ( $ripf->isValid() ) {
			$insert_id = $ripf->Save();
			Debug::Text('Rounding Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Rounding Policy!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function createSchedulePolicy( $type, $meal_policy_id ) {
		$spf = TTnew( 'SchedulePolicyFactory' );
		$spf->setCompany( $this->company_id );

		switch ( $type ) {
			case 10: //Normal
				$spf->setName( 'Schedule Policy' );
				$spf->setAbsencePolicyID( 0 );
				$spf->setStartStopWindow( (3600 * 2) );
				break;
			case 20: //No Lunch
				$spf->setName( 'No Lunch' );
				$spf->setAbsencePolicyID( 0 );
				$spf->setStartStopWindow( (3600 * 2) );
				break;
		}

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save( FALSE );

			$spf->setMealPolicy( $meal_policy_id );
			
			Debug::Text('Schedule Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__, 10);

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
			Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
			$end_time = strtotime( $data['end_time'], $date_stamp ) ;
			Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME', $data['end_time']), __FILE__, __LINE__, __METHOD__, 10);
		}

		$sf->setStartTime( $start_time );
		$sf->setEndTime( $end_time );

		if ( $sf->isValid() ) {
			$sf->setEnableReCalculateDay(FALSE);
			$insert_id = $sf->Save();
			Debug::Text('Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__, 10);

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
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, array(5, 20, 30, 40, 100, 110), $start_date, $end_date);
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach($udtlf as $udt_obj) {
				$type_and_policy_id = $udt_obj->getObjectType().(int)$udt_obj->getPayCode();

				$date_totals[$udt_obj->getDateStamp()][] = array(
												'date_stamp' => $udt_obj->getDateStamp(),
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

	function getPunchDataArray( $start_date, $end_date ) {
		$plf = new PunchListFactory();

		$plf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $start_date, $end_date );
		if ( $plf->getRecordCount() > 0 ) {
			//Only return punch_control data for now
			$i = 0;
			$prev_punch_control_id = NULL;
			foreach( $plf as $p_obj ) {
				if ( $prev_punch_control_id == NULL OR $prev_punch_control_id != $p_obj->getPunchControlID() ) {
					$date_stamp = $p_obj->getPunchControlObject()->getDateStamp();
					$p_obj->setUser( $this->user_id );
					$p_obj->getPunchControlObject()->setPunchObject( $p_obj );

					$retarr[$date_stamp][$i] = array(
													'id' => $p_obj->getPunchControlObject()->getID(),
													'branch_id' => $p_obj->getPunchControlObject()->getBranch(),
													'date_stamp' => $date_stamp,
													//'user_date_id' => $p_obj->getPunchControlObject()->getUserDateID(),
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

	/**
	 * @group Punch_testDayShiftStartsBasicA
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicB
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicC
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicD
	 */
	function testDayShiftStartsBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicE
	 */
	function testDayShiftStartsBasicE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicF
	 */
	function testDayShiftStartsBasicF() {
		//Special test to fix a bug when there is a 2hr gap between punches, but a new shift is only triggered after 4hrs.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftStartsBasicG
	 */
	function testDayShiftStartsBasicG() {
		//Special test to handle 24hr shifts with no gaps between them for multiple days. (such as fire fighters/live-in care homes)
		global $dd;

		$this->createPayPeriodSchedule( 10, (25 * 3600), 0 ); //NewShiftTriggerTime=0
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) + (1 * 86400 + 3601) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$date_epoch3 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) + (2 * 86400 + 3601) );
		$date_stamp3 = TTDate::getDate('DATE', $date_epoch3 );
		$date_epoch4 = TTDate::getBeginDayEpoch( TTDate::getBeginWeekEpoch( time() ) + (3 * 86400 + 3601) );				
		$date_stamp4 = TTDate::getDate('DATE', $date_epoch4 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:00PM'),
								strtotime($date_stamp2.' 3:00PM'),
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
								strtotime($date_stamp2.' 3:00PM'),
								strtotime($date_stamp3.' 3:00PM'),
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
								strtotime($date_stamp3.' 3:00PM'),
								strtotime($date_stamp4.' 3:00PM'),
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

		//Date 1
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (24 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Date 2
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch2), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch2]) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );
		$this->assertEquals( $punch_arr[$date_epoch2][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch2][0]['shift_data']['punches'][1]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (24 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		//Date 3
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch3), TTDate::getEndDayEpoch($date_epoch3) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch3]) );
		$this->assertEquals( $date_epoch3, $punch_arr[$date_epoch3][0]['date_stamp'] );
		$this->assertEquals( $punch_arr[$date_epoch3][0]['shift_data']['punches'][0]['punch_control_id'], $punch_arr[$date_epoch3][0]['shift_data']['punches'][1]['punch_control_id'] ); //Make sure punch_control_id from both shifts DO match.

		$udt_arr = $this->getUserDateTotalArray( $date_epoch3, $date_epoch3 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch3][0]['object_type_id'] );
		$this->assertEquals( (24 * 3600), $udt_arr[$date_epoch3][0]['total_time'] );
		
		return TRUE;
	}

	
	/**
	 * @group Punch_testDayShiftEndsBasicA
	 */
	function testDayShiftEndsBasicA() {
		//  Test when shifts are assigned to the day they end on.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftEndsBasicB
	 */
	function testDayShiftEndsBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 20 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayShiftEndsBasicC
	 */
	function testDayShiftEndsBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 20 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}


	/**
	 * @group Punch_testDayMostWorkedBasicA
	 */
	function testDayMostWorkedBasicA() {
		//  Test when shifts are assigned to the day most worked on.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayMostWorkedBasicB
	 */
	function testDayMostWorkedBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayMostWorkedBasicC
	 */
	function testDayMostWorkedBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDayMostWorkedBasicD
	 */
	function testDayMostWorkedBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 30 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		//$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] ); //Since we don't save UDT records where total_time=0, don't check this anymore.
		//Instead check to make sure no records on that date exist at all.
		if ( isset($udt_arr[$date_epoch][0]['object_type_id']) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}

		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicA
	 */
	function testSplitAtMidnightBasicA() {
		//  Test when shifts are split at midnight.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicB
	 */
	function testSplitAtMidnightBasicB() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (6 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Date 2
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (2 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicC
	 */
	function testSplitAtMidnightBasicC() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (6 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Date 2
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (2 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicD
	 */
	function testSplitAtMidnightBasicD() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicE
	 */
	function testSplitAtMidnightBasicE() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testSplitAtMidnightBasicF
	 */
	function testSplitAtMidnightBasicF() {
		global $dd;

		$this->createPayPeriodSchedule( 40 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 ); //Start weeks on Monday so DST change doesn't affect this.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time(), 1 ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingA
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingB
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingC
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );

		$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//if ( TTDate::doesRangeSpanDST( $date_epoch, $date_epoch2 ) ) {
		//	$this->assertEquals( ((15.5 * 3600)+TTDate::getDSTOffset($date_epoch, $date_epoch2)), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//} else {
		//	$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] ); //If this is the week of the DST switchover, this can be off by one hour.
		//}

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingD
	 */
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
		if ( isset($udt_arr[$date_epoch][0]) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}
		//$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		//$this->assertEquals( (0 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingE
	 */
	function testPunchControlMatchingE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingF
	 */
	function testPunchControlMatchingF() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN and Lunch Out punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 1:30PM'),
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

		//Lunch IN and Normal Out punch more than maximum shift time apart.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 1:30PM'),
								strtotime($date_stamp2.' 2:00PM'), //Normal Out
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

		//Day 1
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (5.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Day 2
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch2), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);
		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (0.5 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchControlMatchingG
	 */
	function testPunchControlMatchingG() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN and Lunch Out punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:30PM'),
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

		//Lunch IN and Normal Out punch less than maximum shift time apart.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 1:30PM'),
								strtotime($date_stamp2.' 2:00PM'), //Normal Out
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

		//Day 1
		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (16 * 3600), $udt_arr[$date_epoch][0]['total_time'] );
		
		return TRUE;
	}


	/**
	 * @group Punch_testPunchEditingA
	 */
	function testPunchEditingA() {
		global $dd;

		$this->createPayPeriodSchedule( 20 ); //Day shift ends on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (13 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


		return TRUE;
	}

	/**
	 * @group Punch_testPunchEditingB
	 */
	function testPunchEditingB() {
		global $dd;

		$this->createPayPeriodSchedule( 20 ); //Day shift ends on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchEditingC
	 */
	function testPunchEditingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	/**
	 * @group testPunchEditingTimeZoneA
	 */
	function testPunchEditingTimeZoneA() {
		global $dd;

		//
		//Cause punch to switch days due to timezone, and not actually changing the dates.
		//  Make sure that UDT records follow the date properly too.
		//
		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.
		
		$this->createPayPeriodSchedule( 10 ); //Day shift starts on
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:30PM'),
								strtotime($date_stamp2.' 7:30AM'),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


		TTDate::setTimeZone('MST7MDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								//'time_stamp' => strtotime($date_stamp.' 11:00PM'),
								'time_stamp' => strtotime( date('Ymd H:i:s', $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'] ) ),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		if ( isset($punch_arr[$date_epoch]) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}

		$this->assertEquals( 2, count($punch_arr[$date_epoch2][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );


		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch2][0]['shift_data']['punches'][1]['id'],
						array(
								//'time_stamp' => strtotime($date_stamp.' 11:00PM'),
								'time_stamp' => strtotime( date('Ymd H:i:s', $punch_arr[$date_epoch2][0]['shift_data']['punches'][1]['time_stamp'] ) ),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		if ( isset($punch_arr[$date_epoch2]) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	/**
	 * @group Punch_testPunchEditingShiftDayChangeA
	 */
	function testPunchEditingShiftDayChangeA() {
		//Test moving shifts from one day to the next when punches are edited.
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:30PM'),
								strtotime($date_stamp.' 2:30PM'),
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
								strtotime($date_stamp2.' 12:30AM'), //Normal Out
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

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$this->assertEquals( 2, count($punch_arr[$date_epoch2][1]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch2][1]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch2, $punch_arr[$date_epoch2][1]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Day 1
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (1 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Day 2
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (2 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 11:30PM'),
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
		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time - Day1
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchEditingShiftDayChangeB
	 */
	function testPunchEditingShiftDayChangeB() {
		//Test moving shifts from one day to the next when punches are edited.
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		//Create just an IN punch.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 1:30PM'),
								strtotime($date_stamp.' 11:30PM'),
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
								strtotime($date_stamp2.' 12:30AM'), //Normal Out
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

		$date_epoch = TTDate::getBeginDayEpoch($date_epoch); //This accounts for DST.
		$date_epoch2 = TTDate::getBeginDayEpoch($date_epoch2); //This accounts for DST.
		$this->assertEquals( 4, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Edit punch to move out time into next day.
		$dd->editPunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id'],
						array(
								'time_stamp' => strtotime($date_stamp.' 2:30PM'),
								) );

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		//Make sure previous day has no totals, but new day has proper totals.
		if ( isset($punch_arr[$date_epoch2]) ) {
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
		//Total Time - Day1
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (1 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Total Time - Day2
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (2 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchEditingD
	 */
	function testPunchEditingD() {
		global $dd;

		$this->createPayPeriodSchedule( 30 ); //Day with most time worked
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (6 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (14 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchDeletingA
	 */
	function testPunchDeletingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (12 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//Delete punch
		$dd->deletePunch($punch_arr[$date_epoch][0]['shift_data']['punches'][1]['id']);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch2) );
		//print_r($punch_arr);

		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		if ( isset($udt_arr[$date_epoch][0]) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}
		//$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		//$this->assertEquals( (0 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testPunchDeletingB
	 */
	function testPunchDeletingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 ); //Day shift starts on.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time() ) + 86400 + 3600 ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (3 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (1.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (1.5 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationA
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationA2
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationB
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationC
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}
	/**
	 * @group Punch_testValidationD
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationE
	 */
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );


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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationF
	 */
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
		if ( isset($udt_arr[$date_epoch][0]) ) {
			$this->assertTrue( FALSE );
		} else {
			$this->assertTrue( TRUE );
		}
		//$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		//$this->assertEquals( (0 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testValidationG
	 */
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

	/**
	 * @group Punch_testRoundingA
	 */
	function testRoundingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 10 ); //In
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 20 ); //Out

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8.75 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingB
	 */
	function testRoundingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 30 ); //Day Total

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingC
	 */
	function testRoundingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 30 ); //Day Total
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 40 ); //Lunch Total

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingD
	 */
	function testRoundingD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 30 ); //Day Total
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 40 ); //Lunch Total
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 50 ); //Break Total

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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingConditionA
	 */
	function testRoundingConditionA() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches outside the condition, so no rounding takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 110 ); //In
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 120 ); //Out

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
								strtotime($date_stamp.' 8:17AM'),
								strtotime($date_stamp.' 4:43PM'),
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

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:17AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 4:43PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 30360, $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingConditionA2
	 */
	function testRoundingConditionA2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches inside the condition, so rounding takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 110 ); //In
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 120 ); //Out

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
								strtotime($date_stamp.' 8:12AM'),
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

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:10AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 5:10PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testRoundingConditionB
	 */
	function testRoundingConditionB() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches outside the condition, so rounding doesn't takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 130 ); //Day Total.
		//$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 120 ); //Out

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
								strtotime($date_stamp.' 8:12AM'),
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
								strtotime($date_stamp.' 5:31PM'),
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

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:12AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 5:31PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 33540, $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}
	/**
	 * @group Punch_testRoundingConditionB2
	 */
	function testRoundingConditionB2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches inside the condition, so round takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 130 ); //Day Total.

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
								strtotime($date_stamp.' 8:12AM'),
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:12AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 5:12PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}



	/**
	 * @group Punch_testRoundingConditionC
	 */
	function testRoundingConditionC() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches outside the condition, so no rounding takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 111 ); //In
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 121 ); //Out

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

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:17AM'),
								strtotime($date_stamp.' 4:43PM'),
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

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:17AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 4:43PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($punch_arr);
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( 26760, $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}
	/**
	 * @group Punch_testRoundingConditionC2
	 */
	function testRoundingConditionC2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return TRUE;
		}

		//Test punches inside the condition, so rounding takes place.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 111 ); //In
		$policy_ids['round'][] = $this->createRoundingPolicy( $this->company_id, 121 ); //Out

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

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																	) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:12AM'),
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

		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][0]['time_stamp'], strtotime($date_stamp.' 8:10AM') );
		$this->assertEquals( $punch_arr[$date_epoch][0]['shift_data']['punches'][1]['time_stamp'], strtotime($date_stamp.' 5:10PM') );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	/**
	 * @group Punch_testDST
	 */
	function testDSTFall() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
		//See US department of labor description: http://www.dol.gov/elaws/esa/flsa/hoursworked/screenER11.asp
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDST
	 */
	function testDSTFallB() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (9 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDST
	 */
	function testDSTSpring() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testDST
	 */
	function testDSTSpringB() {
		//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}


	/**
	 * @group Punch_testScheduleMatchingA
	 */
	function testScheduleMatchingA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingB
	 */
	function testScheduleMatchingB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 3, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingC
	 */
	function testScheduleMatchingC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingD
	 */
	function testScheduleMatchingD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch2][0]['type_id'] );
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (6.5 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch2]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingE
	 */
	function testScheduleMatchingE() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (7.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingF
	 */
	function testScheduleMatchingF() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
		$no_lunch_schedule_policy_id = $this->createSchedulePolicy( 20, -1 );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 10:00PM',
																	'end_time' => '7:00AM',
																	) );

		$this->createSchedule( $this->user_id, $date_epoch2, array(
																	'schedule_policy_id' => $no_lunch_schedule_policy_id, //No meal policy on this shift.
																	'start_time' => ' 7:30AM',
																	'end_time' => '4:30PM',
																	) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 10:00PM'),
								strtotime($date_stamp2.' 7:00AM'),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		//$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch]) );

		return TRUE;
	}

	/**
	 * @group Punch_testScheduleMatchingG
	 */
	function testScheduleMatchingG() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3600 ) );
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
		$no_lunch_schedule_policy_id = $this->createSchedulePolicy( 20, -1 );
		$this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $no_lunch_schedule_policy_id, //No meal policy on this shift.
																	'start_time' => ' 10:00PM',
																	'end_time' => '7:00AM',
																	) );

		$this->createSchedule( $this->user_id, $date_epoch2, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 7:30AM',
																	'end_time' => '4:30PM',
																	) );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp2.' 7:30AM'),
								strtotime($date_stamp2.' 4:30PM'),
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

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );
		$this->assertEquals( (8 * 3600), $udt_arr[$date_epoch2][0]['total_time'] );

		//$this->assertEquals( 1, count($udt_arr) );
		$this->assertEquals( 2, count($udt_arr[$date_epoch2]) );

		return TRUE;
	}

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleA
	 */
	function testDefaultPunchSettingsNoScheduleA() {
		//No defaults in station or employee profile.
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleB
	 */
	function testDefaultPunchSettingsNoScheduleB() {
		//Test with default branch/department set in employee profile
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
	
	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleD
	 */
	function testDefaultPunchSettingsNoScheduleD() {
		//Test with previous Normal punch.
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleE
	 */
	function testDefaultPunchSettingsNoScheduleE() {
		//Test with previous Break punch.
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleF
	 */
	function testDefaultPunchSettingsNoScheduleF() {
		//Test with previous Lunch punch.
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleG
	 */
	function testDefaultPunchSettingsNoScheduleG() {
		//Test with split shift.
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleGB
	 */
	function testDefaultPunchSettingsNoScheduleGB() {
		//Test with split shift (B)
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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleH
	 */
	function testDefaultPunchSettingsNoScheduleH() {
		//Test with duplicate punches at the exact same time, including seconds.
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
								strtotime($date_stamp.' 8:00:00 AM'),
								strtotime($date_stamp.' 1:00:57 PM'),
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
		$epoch = strtotime($date_stamp.' 1:00:57 PM');

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
	
	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleHB
	 */
	function testDefaultPunchSettingsNoScheduleHB() {
		//Test with duplicate punches at the exact same time, including seconds.
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
								strtotime($date_stamp.' 8:00:00 AM'),
								strtotime($date_stamp.' 1:00:23 PM'),
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
		$epoch = strtotime($date_stamp.' 1:00:23 PM');

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

	/**
	 * @group Punch_testDefaultPunchSettingsNoScheduleHC
	 */
	function testDefaultPunchSettingsNoScheduleHC() {
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
								strtotime($date_stamp.' 8:00:00 AM'),
								strtotime($date_stamp.' 1:00:23 PM'),
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
		$epoch = strtotime($date_stamp.' 1:00:23 PM');

		$ulf = TTNew('UserListFactory');
		$ulf->getById( $this->user_id );
		$user_obj = $ulf->getCurrent();

		$prev_punch_obj = TTnew( 'PunchListFactory' );
		$status_id = FALSE;
		$type_id = FALSE;

		//Test similar functionality to what the timeclock would use to avoid duplicate punches.
		//This is different than what the mobile app would do though, as the mobile app needs to be able to refresh its default punch settings immediately.
		$plf = TTnew( 'PunchListFactory' );
		$plf->getPreviousPunchByUserIDAndEpoch( $user_obj->getId(), $epoch );
		if ( $plf->getRecordCount() > 0 ) {
			$prev_punch_obj = $plf->getCurrent();

			$prev_punch_obj->setUser( $user_obj->getId() );

			$status_id = $prev_punch_obj->getNextStatus();
			$type_id = $prev_punch_obj->getNextType( $epoch ); //Detects breaks/lunches too.
		}

		//If previous punch actual time matches current punch time, we can skip the auto-status logic
		//  as its a duplicate punch and shouldn't have a different status. This way its more likely to get rejected as a duplicate.
		$this->assertEquals( $prev_punch_obj->getActualTimeStamp(), $epoch );
		$this->assertEquals( 1, $plf->getRecordCount() );
		$this->assertEquals( 10, $status_id ); //In

		return TRUE;
	}



	/**
	 * @group Punch_testDefaultPunchSettingsScheduleA
	 */
	function testDefaultPunchSettingsScheduleA() {
		//No defaults in station or employee profile.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleB
	 */
	function testDefaultPunchSettingsScheduleB() {
		//No defaults in station or employee profile.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleC
	 */
	function testDefaultPunchSettingsScheduleC() {
		//Test with previous Normal punch.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleD
	 */
	function testDefaultPunchSettingsScheduleD() {
		//Test with previous Break punch.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleE
	 */
	function testDefaultPunchSettingsScheduleE() {
		//Test with previous Lunch punch.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleF
	 */
	function testDefaultPunchSettingsScheduleF() {
		//Test with split shift.
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleFB
	 */
	function testDefaultPunchSettingsScheduleFB() {
		//Test with split shift (B)
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
	
	/**
	 * @group Punch_testDefaultPunchSettingsScheduleFC
	 */
	function testDefaultPunchSettingsScheduleFC() {
		//Test with split shift (C)
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleFD
	 */
	function testDefaultPunchSettingsScheduleFD() {
		//Test with split shift (D)
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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

	/**
	 * @group Punch_testDefaultPunchSettingsScheduleFE
	 */
	function testDefaultPunchSettingsScheduleFE() {
		//Test with split shift (E)
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
		$schedule_policy_id = $this->createSchedulePolicy( 10,  $meal_policy_id );
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
	
	/**
	 * @group Punch_testMaximumShiftTimeA
	 */
	function testMaximumShiftTimeA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 ); //Start weeks on Monday so DST switchover does cause problems.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time(), 1 ) + 86400 + 3600 ) );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch, 1), TTDate::getEndDayEpoch($date_epoch2, 1) );
		//print_r($punch_arr);
		
		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (15.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testMaximumShiftTimeB
	 */
	function testMaximumShiftTimeB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time(), 1 ) + 86400 + 3600 ) );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch, 1), TTDate::getEndDayEpoch($date_epoch2, 1) );
		//print_r($punch_arr);

		$this->assertEquals( 0, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 0, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );

		$this->assertEquals( 0, count($udt_arr) );

		return TRUE;
	}

	/**
	 * @group Punch_testMaximumShiftTimeC
	 */
	function testMaximumShiftTimeC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time(), 1 ) + 86400 + 3600 ) );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch, 1), TTDate::getEndDayEpoch($date_epoch2, 1) );
		//print_r($punch_arr);

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );

		$this->assertEquals( 2, count($punch_arr[$date_epoch][1]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][1]['shift_data']['punch_control_ids']) );

		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (25.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

	/**
	 * @group Punch_testMaximumShiftTimeD
	 */
	function testMaximumShiftTimeD() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time(), 1 );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = TTDate::getBeginDayEpoch( ( TTDate::getBeginWeekEpoch( time(), 1 ) + 86400 + 3600 ) );
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

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch, 1), TTDate::getEndDayEpoch($date_epoch2, 1) );
		//print_r($punch_arr);

		$this->assertEquals( 2, count($punch_arr[$date_epoch][0]['shift_data']['punches']) );
		$this->assertEquals( 1, count($punch_arr[$date_epoch][0]['shift_data']['punch_control_ids']) );
		//$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );
		$this->assertEquals( (13.5 * 3600), $udt_arr[$date_epoch][0]['total_time'] );

		return TRUE;
	}

}
?>