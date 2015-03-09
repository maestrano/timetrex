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

class AccrualPolicyTest extends PHPUnit_Framework_TestCase {
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

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		//$dd->createPayStubAccount( $this->company_id );

		////$this->createPayStubAccrualAccount();
		//$dd->createPayStubAccountLink( $this->company_id );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );
		$user_obj = $this->getUserObject( $this->user_id );
		//Use a consistent hire date, otherwise its difficult to get things correct due to the hire date being in different parts or different pay periods.
		//Make sure it is not on a pay period start date though.
		$user_obj->setHireDate( strtotime('05-Mar-2001') );
		$user_obj->Save(FALSE);

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( TTDate::getBeginDayEpoch( TTDate::getBeginYearEpoch( $user_obj->getHireDate() ) ) );
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

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		$ppsf->setName( 'Semi-Monthly' );
		$ppsf->setDescription( '' );
		$ppsf->setType( 30 );
		$ppsf->setStartWeekDay( 0 );

		$anchor_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() )-(86400*(7*6) ) ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setPrimaryDayOfMonth( 1 );
		$ppsf->setSecondaryDayOfMonth( 16 );
		$ppsf->setPrimaryTransactionDayOfMonth( 20 );
		$ppsf->setSecondaryTransactionDayOfMonth( 5 );

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

	function createPayPeriods( $start_date = NULL ) {
		if ( $start_date == '' ) {
			$start_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() )-(86400*(7*6) ) ) );
		}

		$max_pay_periods = 192; //Make a lot of pay periods as we need to test 6 years worth of accruals for different milestones.

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					//$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-07') );
					$end_date = $start_date;
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

	function createAccrualPolicyAccount( $company_id, $type ) {
		$apaf = TTnew( 'AccrualPolicyAccountFactory' );

		$apaf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Bank Time
				$apaf->setName( 'Unit Test' );
				break;
			case 20: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Personal Time Off (PTO)/Vacation' );
				break;
			case 30: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Sick Time' );
				break;
		}

		if ( $apaf->isValid() ) {
			$insert_id = $apaf->Save();
			Debug::Text('Accrual Policy Account ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Accrual Policy Account!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}
	function createAccrualPolicy( $company_id, $type, $accrual_policy_account_id ) {
		$apf = TTnew( 'AccrualPolicyFactory' );

		$apf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Bank Time
				$apf->setName( 'Bank Time' );
				$apf->setType( 10 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 20: //Calendar Based: Check minimum employed days
				$apf->setName( 'Calendar: Minimum Employed' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 9999 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 30: //Calendar Based: Check milestone not applied yet.
				$apf->setName( 'Calendar: Milestone not applied' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 9999 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 40: //Calendar Based: Pay Period with one milestone
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 40: //Calendar Based: Pay Period with one milestone
				$apf->setName( 'Calendar: 1 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 50: //Calendar Based: Pay Period with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 60: //Calendar Based: Pay Period with 5 milestones
				$apf->setName( 'Calendar: 5 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 70: //Calendar Based: Pay Period with 5 milestones rolling over on January 1st.
				$apf->setName( 'Calendar: 5 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( FALSE );
				$apf->setMilestoneRolloverMonth( 1 );
				$apf->setMilestoneRolloverDayOfMonth( 1 );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 80: //Calendar Based: Pay Period with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 10 ); //Each Pay Period

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;


			case 200: //Calendar Based: Weekly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 0 ); //Sunday

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 210: //Calendar Based: Weekly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 40 ); //Weekly
				$apf->setApplyFrequencyDayOfWeek( 3 ); //Wed

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 300: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 310: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 15 );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 320: //Calendar Based: Monthly with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 ); //Monthly
				$apf->setApplyFrequencyDayOfMonth( 31 );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

			case 400: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 1 );
				$apf->setApplyFrequencyDayOfMonth( 1 );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 410: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyMonth( 6 );
				$apf->setApplyFrequencyDayOfMonth( 15 );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 420: //Calendar Based: Annually with 2 milestones
				$apf->setName( 'Calendar: 2 milestone' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 20 ); //Annually
				$apf->setApplyFrequencyHireDate( TRUE );

				$apf->setMilestoneRolloverHireDate( TRUE );

				$apf->setMinimumEmployedDays( 0 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;

		}

		if ( $apf->isValid() ) {
			$insert_id = $apf->Save();
			Debug::Text('Accrual Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			$apmf = TTnew( 'AccrualPolicyMilestoneFactory' );

			switch ( $type ) {
				case 20:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
				case 30:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 99 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
				case 40:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
				case 50:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*10 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
				case 60:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*10 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 2 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*15 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 3 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*20 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 4 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*25 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 5 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*30 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					break;
				case 60:
				case 70:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*10 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 2 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*15 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 3 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*20 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 4 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*25 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 5 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*30 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;

				case 80:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*6 );
					$apmf->setMaximumTime( (3600*8)*3 );
					$apmf->setRolloverTime( (3600*8)*2 );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*10 );
					$apmf->setMaximumTime( (3600*8)*5 );
					$apmf->setRolloverTime( (3600*8)*4 );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
				case 200:
				case 210:
				case 300:
				case 310:
				case 320:
				case 400:
				case 410:
				case 420:
					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 0 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*5 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}

					$apmf->setAccrualPolicy( $insert_id );
					$apmf->setLengthOfService( 1 );
					$apmf->setLengthOfServiceUnit( 40 );
					$apmf->setAccrualRate( (3600*8)*10 );
					$apmf->setMaximumTime( (3600*9999) );
					$apmf->setRolloverTime( (3600*9999) );

					if ( $apmf->isValid() ) {
						Debug::Text('Saving Milestone...', __FILE__, __LINE__, __METHOD__,10);
						$apmf->Save();
					}
					break;
			}

			return $insert_id;
		}

		Debug::Text('Failed Creating Accrual Policy!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function calcAccrualTime( $company_id, $accrual_policy_id, $start_date, $end_date, $day_multiplier = 1 ) {
		$start_date = TTDate::getMiddleDayEpoch( $start_date );
		$end_date = TTDate::getMiddleDayEpoch( $end_date );
		$total_days = TTDate::getDays( ($end_date-$start_date) );
		//$offset = 79200;
		$offset = ( (86400 * $day_multiplier) - 7200 );

		$apf = TTnew( 'AccrualPolicyFactory' );
		$aplf = TTnew( 'AccrualPolicyListFactory' );

		$aplf->getByIdAndCompanyId( (int)$accrual_policy_id, $company_id );
		if ( $aplf->getRecordCount() > 0 ) {
			foreach( $aplf as $ap_obj ) {
				$aplf->StartTransaction();

				$x=0;
				for( $i=$start_date; $i < $end_date; $i+=( 86400 * $day_multiplier ) ) { //Try skipping by two days to speed up this test.
					//Debug::Text('Recalculating Accruals for Date: '. TTDate::getDate('DATE+TIME', TTDate::getBeginDayEpoch( $i ) ), __FILE__, __LINE__, __METHOD__,10);
					$ap_obj->addAccrualPolicyTime( TTDate::getBeginDayEpoch( $i )+7201, $offset );
					//Debug::Text('----------------------------------', __FILE__, __LINE__, __METHOD__,10);

					$x++;
				}

				$aplf->CommitTransaction();
			}
		}

		return TRUE;
	}

	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = NULL ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getId();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text('&nbsp;&nbsp; Current Accrual Balance: '. $accrual_balance, __FILE__, __LINE__, __METHOD__,10);

		return $accrual_balance;
	}

	function getUserObject( $user_id ) {
		$ulf = TTNew( 'UserListFactory' );
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			return $ulf->getCurrent();
		}

		return FALSE;
	}


	/*
	 Tests:
		Calendar Based - Minimum Employed Days
		Calendar Based - 1st milestone high length of service.
		Calendar Based - PayPeriod Frequency (1 milestone)
		Calendar Based - PayPeriod Frequency (2 milestones)
		Calendar Based - PayPeriod Frequency (5 milestones)
	*/

	/**
	 * @group AccrualPolicy_testCalendarAccrualA
	 */
	function testCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( $hire_date+(86400*365*2) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 20, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, (0*3600) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualB
	 */
	function testCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( $hire_date+(86400*365*2) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 30, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, (0*3600) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualC
	 */
	function testCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( $hire_date+(86400*365*2) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 40, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, (40*3600) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualD
	 */
	function testCalendarAccrualD() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = TTDate::getBeginYearEpoch( $hire_date+(86400*365*2) );

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 50, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, TTDate::getBeginYearEpoch( $current_epoch ), TTDate::getEndYearEpoch( $current_epoch ) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, (80*3600) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualE
	 */
	function testCalendarAccrualE() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 60, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+7 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, (1080*3600) );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualF
	 */
	function testCalendarAccrualF() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 70, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+7 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 4038000 );
	}

	/**
	 * @group AccrualPolicy_testCalendarAccrualG
	 */
	function testCalendarAccrualG() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 80, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+5 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 144000 );
	}

	/**
	 * @group AccrualPolicy_testWeeklyCalendarAccrualA
	 */
	function testWeeklyCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 200, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+2 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 434733 );
	}


	/**
	 * @group AccrualPolicy_testWeeklyCalendarAccrualB
	 */
	function testWeeklyCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 210, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+2 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 431964 );
	}


	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualA
	 */
	function testMonthlyCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 300, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+2 years', $current_epoch), 10 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 432000 );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualB
	 */
	function testMonthlyCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 310, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+2 years', $current_epoch), 10 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 432000 );
	}

	/**
	 * @group AccrualPolicy_testMonthlyCalendarAccrualC
	 */
	function testMonthlyCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 320, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+2 years', $current_epoch) );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 432000 );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualA
	 */
	function testAnnualCalendarAccrualA() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 400, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+5 years', $current_epoch), 30 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 1296000 );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualB
	 */
	function testAnnualCalendarAccrualB() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 410, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+5 years', $current_epoch), 30 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 1296000 );
	}

	/**
	 * @group AccrualPolicy_testAnnualCalendarAccrualC
	 */
	function testAnnualCalendarAccrualC() {
		global $dd;

		$hire_date = $this->getUserObject( $this->user_id )->getHireDate();
		$current_epoch = $hire_date;

		$accrual_policy_account_id = $this->createAccrualPolicyAccount( $this->company_id, 10 );
		$accrual_policy_id = $this->createAccrualPolicy( $this->company_id, 420, $accrual_policy_account_id );
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									array($this->user_id),
									NULL,
									array( $accrual_policy_id ) );

		$this->calcAccrualTime( $this->company_id, $accrual_policy_id, $current_epoch, strtotime('+5 years', $current_epoch), 30 );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $accrual_policy_account_id );

		$this->assertEquals( $accrual_balance, 1296000 );
	}

}
?>